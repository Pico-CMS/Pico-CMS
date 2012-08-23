<?php
require_once('includes/functions.php');
require_once('includes/database.class.php');

function eval_include($file)
{
	ob_start();
	eval('?>' . file_get_contents($file));
	$html = ob_get_contents();
	ob_end_clean();
	return $html;
}

$body = '';

if (isset($_POST['page_action']))
{
	$action = $_POST['page_action'];
	
	if ($action == 'verify_ftp')
	{
		include('install/verify_ftp.php');
		if ($_SESSION['FTP_OK'] == TRUE)
		{
			$_SESSION['install_step'] = 2;
		}
		else
		{
			foreach ($ftp_error as $error)
			{
				$body .= '<div class="error">'.$error.'</div>';
			}
		}
	}
	
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
			$_SESSION['install_step'] = 3;
			$_SESSION['DATABASE_INFO'] = array(
				'host'     => $host,
				'username' => $user,
				'password' => $pass,
				'name'     => $name,
				'prefix'   => trim($_POST['db']['prefix']),
			);
		}
	}
	
	if ($action == 'additional_settings')
	{
		// establish connection
		$db = new DataBase($_SESSION['DATABASE_INFO']['host'], $_SESSION['DATABASE_INFO']['username'], $_SESSION['DATABASE_INFO']['password'], $_SESSION['DATABASE_INFO']['name']);
	
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
		
		$h = fopen('includes/config.blank.php', 'w');
		fwrite($h, $config);
		fclose($h);
		
		rename('includes/config.blank.php', 'includes/config.php');
		
		// rewrite htaccess file
		
		$htaccess = file_get_contents('install/install.htaccess');
		$htaccess = str_replace('[[PATH]]', $_SERVER['REQUEST_URI'], $htaccess);
		
		$h = fopen('.htaccess', 'w');
		fwrite($h, $htaccess);
		fclose($h);
		
		// save FTP information
		
		$settings_table = str_replace('PREFIX_', $_SESSION['DATABASE_INFO']['prefix'], 'PREFIX_pico_settings');
		$db->run('INSERT INTO `'.$settings_table.'` (`keyfield`, `keyvalue`) VALUES (?,?)', 'host', $_SESSION['FTP_INFORMATION']['host']);
		$db->run('INSERT INTO `'.$settings_table.'` (`keyfield`, `keyvalue`) VALUES (?,?)', 'port', $_SESSION['FTP_INFORMATION']['port']);
		$db->run('INSERT INTO `'.$settings_table.'` (`keyfield`, `keyvalue`) VALUES (?,?)', 'username', $_SESSION['FTP_INFORMATION']['username']);
		$db->run('INSERT INTO `'.$settings_table.'` (`keyfield`, `keyvalue`) VALUES (?,?)', 'password', $_SESSION['FTP_INFORMATION']['password']);
		$db->run('INSERT INTO `'.$settings_table.'` (`keyfield`, `keyvalue`) VALUES (?,?)', 'path', $_SESSION['FTP_INFORMATION']['path']);
		$db->run('INSERT INTO `'.$settings_table.'` (`keyfield`, `keyvalue`) VALUES (?,?)', 'version', '1.0');
		
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
	Pico_Setting('pico_build_version', 1037);
}



// prep the template file for output
$page_html = eval_include('install/install.tpl');

echo str_replace('BODY_CONTENT', $body, $page_html);
?>
