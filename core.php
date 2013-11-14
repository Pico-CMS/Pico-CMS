<?php
/* core.php
 *
 * main file for inclusion into any page, script, or external source to establish 
 * database connection, establish constants, and verify/prepare session
 */
$request = basename($_SERVER['REQUEST_URI']);
if ($request == 'core.php') { echo 'You cannot access this file directly'; exit(); }

if (file_exists('includes/config.php'))
{
	require_once('includes/config.php');
	require_once('includes/functions.php');
	require_once('includes/database.class.php');
	require_once('includes/body.class.php');
	require_once('includes/upload.class.php');
	require_once('includes/image.class.php');
}
else
{
	session_start();
	header('Cache-control: private');
	require_once('install/install.php');
	exit();
}


// establish a connection

$db = new DataBase($config['dbhost'], $config['dbuser'], $config['dbpass'], $config['dbname']);

if ($db->connected == FALSE)
{
	echo $db->error;
	exit();
}
else
{
	define('DB_PREFIX', $config['dbprefix']);
	define('DB_USER_TABLE', $config['dbprefix'] . 'pico_users');
	define('DB_PAGES_TABLE', $config['dbprefix'] . 'pico_pages');
	define('DB_CONTENT_LINKS', $config['dbprefix'] . 'pico_content_links');
	define('DB_COMPONENT_TABLE', $config['dbprefix'] . 'pico_components');
	define('DB_CONTENT', $config['dbprefix'] . 'pico_content');
	define('DB_LINKS', $config['dbprefix'] . 'pico_links');
	define('DB_DROPDOWN', $config['dbprefix'] . 'pico_dropdown');
	define('DB_TRANSACTION_LOG', $config['dbprefix'] . 'pico_payment_transactions');
	define('PICO_SETTINGS', $config['dbprefix'] . 'pico_settings');
	define('PICO_SESSIONS', $config['dbprefix'] . 'pico_sessions');
	define('PICO_AUTHOR_ACCESS', $config['dbprefix'] . 'pico_author_access');
	
	define('ADMIN_EMAIL', $config['admin_email']);
	define('ADMIN_FROM', $config['admin_from']);

	// set default timezone
	$timezone = trim(Pico_Setting('default_timezome'));
	if (strlen($timezone) == 0) { $timezone = 'America/New_York'; }
	@date_default_timezone_set($timezone);

	if (defined('CURRENT_PAGE'))
	{
		// this is here in case we are defining the current page id before referencing core, such as 
		// style.php, or javascript.php, or maybe some sort of javascript we need somewheres
		$current_alias = $db->result('SELECT `alias` FROM `'.DB_PAGES_TABLE.'` WHERE `page_id`=?', CURRENT_PAGE);
		if ($current_alias != FALSE)
		{
			define('CURRENT_ALIAS', $current_alias);
		}
	}
	else
	{
		$request = $_SERVER['REQUEST_URI'];
		if (substr($request, 0, strlen($config['domain_path'])) == $config['domain_path'])
		{
			$request = substr($request, strlen($config['domain_path']));
		}

		list($keep, $foo) = explode('?', $request);
		list($keep, $foo) = explode('#', $keep);

		$parts = explode('/', $keep); // Break into an array
		// Lets look at the array of items we have:
		$params = array();
		foreach ($parts as $part)
		{
			if (strlen($part) > 0)
			{
				$params[] = $part;
			}
		}

		$reserved_page_names = array('logout', 'login', 'cleanup');
		
		if (isset($params[0]))
		{
			$bool = ($params[0] == 'print') ? TRUE : FALSE;
			
			$ca = ($bool) ? $params[1] : $params[0];

			// see if this alias is legit
			$check = $db->result('SELECT count(1) FROM `'.DB_PAGES_TABLE.'` WHERE `alias`=?', $ca);
			if (($check == 1) or (in_array($ca, $reserved_page_names)))
			{
				define('CURRENT_ALIAS', $ca);
			}
			else
			{
				if (strlen($ca) == 0)
				{
					// show home page
					$default_id    = $db->result('SELECT `page_id` FROM `'.DB_PAGES_TABLE.'` WHERE `is_default`=1');
					$default_alias = $db->result('SELECT `alias` FROM `'.DB_PAGES_TABLE.'` WHERE `is_default`=1');
					if ($default_id != FALSE)
					{
						define('CURRENT_PAGE', $default_id);
						define('CURRENT_ALIAS', $default_alias);
					}
				}
				else
				{
					// this is in case core gets included
					$request = trim($_SERVER['REQUEST_URI'], '/');
					list($path, $foo) = explode('?', $request);

					if ((!is_file($path)) and (!in_array($path, $reserved_page_names)))
					{
						$_404_page = Pico_Setting('404_page_id');

						if (is_numeric($_404_page))
						{
							$_404_alias = $db->result('SELECT `alias` FROM `'.DB_PAGES_TABLE.'` WHERE `page_id`=?', $_404_page);
						}
						
						if ((!is_numeric($_404_page)) or (!is_string($_404_alias)))
						{
							// default headers
							header('HTTP/1.0 404 Not Found');
						    echo "<h1>404 Not Found</h1>";
						    echo "The page that you have requested could not be found.";
						    exit();
						}

						header('Location: ' . $config['domain_path'] . $_404_alias);
						exit();
					}
				}
			}
			define('PRINTER_FRIENDLY', $bool);
			
			if (PRINTER_FRIENDLY)
			{
				array_shift($params);
			}
		}
		else
		{
			define('PRINTER_FRIENDLY', FALSE);
			// get the default page
			$default_alias = $db->result('SELECT `alias` FROM `'.DB_PAGES_TABLE.'` WHERE `is_default`=1');
			if ($default_alias != FALSE)
			{
				define('CURRENT_ALIAS', $default_alias);
			}
		}
	}
}

if ( (defined('CURRENT_ALIAS')) and (!defined('CURRENT_PAGE')) )
{
	$page_details = $db->assoc('SELECT * FROM `'.DB_PAGES_TABLE.'` WHERE `alias`=?', CURRENT_ALIAS);
	if ($page_details != false)
	{
		define('CURRENT_PAGE', $page_details['page_id']);
	}
	else
	{
		// whatever was keyed in does not exist
		$default_id = $db->result('SELECT `page_id` FROM `'.DB_PAGES_TABLE.'` WHERE `is_default`=1');
		if ($default_id != FALSE)
		{
			define('CURRENT_PAGE', $default_id);
		}
	}
}

if ($params[0] == 'login')
{
	include('includes/login.php');
}
else
{
	$pico_user_id = Pico_VerifySession();
	if ($pico_user_id == 0)
	{
		define('USER_ACCESS', 0);
	}
	else
	{
		$pico_user_access = $db->result('SELECT `access` FROM `'.DB_USER_TABLE.'` WHERE `id`=?', $pico_user_id);
		define('USER_ACCESS', $pico_user_access);
		define('USER_ID', $pico_user_id);
	}


	// start a session
	session_start();
	header('Cache-control: private');

	// start a body for primary output

	$body = new Body();
	$body->base_url = $config['domain_path'];
	define('PICO_BASEPATH', $config['domain_path']);
}
?>