<?php
require_once('includes/functions.php');
require_once('includes/database.class.php');
require_once('includes/ftp.class2.php');

if (!isset($_SESSION['SAVE_SETTINGS']))
{
	$_SESSION['SAVE_SETTINGS'] = array(); // we will use this array to save settings for later
}

function eval_include($file)
{
	ob_start();
	eval('?>' . file_get_contents($file));
	$html = ob_get_contents();
	ob_end_clean();
	return $html;
}

$body = '';

if (isset($_SESSION['DATABASE_INFO']))
{
	// establish connection
	$db = new DataBase($_SESSION['DATABASE_INFO']['host'], $_SESSION['DATABASE_INFO']['username'], $_SESSION['DATABASE_INFO']['password'], $_SESSION['DATABASE_INFO']['name']);
	$GLOBALS['db'] = $db;
	define('DB_PREFIX', $_SESSION['DATABASE_INFO']['prefix']);
}

if (isset($_POST['page_action']))
{
	$action = $_POST['page_action'];

	if ($action == 'verify_db')
	{
		$host = trim($_POST['db']['host']);
		$user = trim($_POST['db']['username']);
		$pass = trim($_POST['db']['password']);
		$name = trim($_POST['db']['name']);
		
		$db = new DataBase($host, $user, $pass, $name);
		
		if ($db->connected == FALSE)
		{
			$body .= '<div class="error">'.$db->error.'</div>';
		}
		else
		{
			$_SESSION['install_step'] = 2;
			$_SESSION['DATABASE_INFO'] = array(
				'host'     => $host,
				'username' => $user,
				'password' => $pass,
				'name'     => $name,
				'prefix'   => trim($_POST['db']['prefix']),
			);

			header('Location: ' . $_SERVER['REQUEST_URI']);
			exit(); // reload page
		}
	}

	if ($action == 'verify_ftp')
	{
		include('install/verify_ftp.php');
		if ($ftp_success)
		{
			$_SESSION['install_step'] = 3;
			header('Location: ' . $_SERVER['REQUEST_URI']);
			exit(); // reload page
		}
		else
		{
			foreach ($ftp_error as $error)
			{
				$body .= '<div class="error">'.$error.'</div>';
			}
		}
	}
	
	if ($action == 'additional_settings')
	{
	
		// install SQL
		
		$sql = file_get_contents('install/pico-install.sql');
		$sql = str_replace('PREFIX_', $_SESSION['DATABASE_INFO']['prefix'], $sql);
		$commands = explode('>', $sql);
		foreach ($commands as $com)
		{
			$db->run($com);
		}
		
		// add admin user
		
		$settings = $_POST['settings'];
		
		$user_table = str_replace('PREFIX_', $_SESSION['DATABASE_INFO']['prefix'], 'PREFIX_pico_users');
		$db->run('INSERT INTO `'.$user_table.'` (`username`, `password`, `access`, `email_address`) VALUES (?,?,?,?)',
			stripslashes($settings['username']), md5(stripslashes($settings['password'])), 5, $settings['email']
		);
		
		$domain = str_replace('www.', '', $_SERVER['SERVER_NAME']);
		
		// rewrite config file
		
		$config = file_get_contents('includes/config.blank.php');
		$config = str_replace('[[HOST]]', $_SESSION['DATABASE_INFO']['host'], $config);
		$config = str_replace('[[DB_NAME]]', $_SESSION['DATABASE_INFO']['name'], $config);
		$config = str_replace('[[DB_USER]]', $_SESSION['DATABASE_INFO']['username'], $config);
		$config = str_replace('[[DB_PASS]]', $_SESSION['DATABASE_INFO']['password'], $config);
		$config = str_replace('[[DB_PREFIX]]', $_SESSION['DATABASE_INFO']['prefix'], $config);
		$config = str_replace('[[ADMIN_EMAIL]]', stripslashes($settings['site_email']), $config);
		$config = str_replace('[[ADMIN_FROM]]', stripslashes($settings['site_from']), $config);
		$config = str_replace('[[DOMAIN]]', $domain, $config);
		$config = str_replace('[[DOMAIN_PATH]]', $_SERVER['REQUEST_URI'], $config);
		$config = str_replace('[[INSTALL_PATH]]', getcwd() . '/', $config);
		$config = str_replace('[[SITE_TITLE]]', stripslashes($settings['site_name']), $config);

		$settings_table = DB_PREFIX . 'pico_settings';
		foreach ($_SESSION['SAVE_SETTINGS'] as $key=>$val)
		{
			$db->run('INSERT INTO `'.$settings_table.'` (`keyfield`, `keyvalue`) VALUES (?,?)',
				$key, $val
			);
		}
		
		$h = fopen('includes/config.blank.php', 'w');
		fwrite($h, $config);
		fclose($h);

		// rename config file with ftp, use exit calls here cause it SHOULDN'T ever happen

		$s = $_SESSION['SAVE_SETTINGS'];
		$url = 'ftp://' . $s['ftp_username'] . ':' . $s['ftp_password'] . '@' . $s['ftp_host'] . ':' . $s['ftp_port'] . $s['ftp_path'];

		try
		{
			$ftp = new Ftp($url, $s['ftp_sftp']);
		}
		catch (Exception $e)
		{
			$error_msg = $e->getMessage();
			exit("Error connecting to ftp: $error_msg");
		}

		if (!$ftp->tryRename('includes/config.blank.php', 'includes/config.php'))
		{
			exit("Unable to rename config file");
		}
		
		$_SESSION['install_step'] = 4;
	}
}

$step = (isset($_SESSION['install_step'])) ? $_SESSION['install_step'] : 1;

if ($step == 1)
{
	$body .= eval_include('install/step1.tpl');
}

if ($step == 2)
{
	$body .= eval_include('install/step2.tpl');
}

if ($step == 3)
{
	$body .= eval_include('install/step3.tpl');
}

if ($step == 4)
{
	$body .= eval_include('install/step4.tpl');
}

// prep the template file for output
$page_html = eval_include('install/install.tpl');

echo str_replace('BODY_CONTENT', $body, $page_html);
?>
