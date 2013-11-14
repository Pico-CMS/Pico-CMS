<?php

$ftp_port  = (int) $_POST['ftp']['port'];
$ftp_user  = (string) $_POST['ftp']['username'];
$ftp_pass  = (string) $_POST['ftp']['password'];
$ftp_path  = (string) $_POST['ftp']['path'];
$ftp_host  = (string) $_POST['ftp']['host'];
$ftp_ok	   = (string) $_POST['ftp']['ok'];
$ftp_error = array();

if($ftp_ok == "skip")
{
	$_SESSION['FTP_OK'] = TRUE;
	$_SESSION['install_step'] = 2;
}

require_once('includes/ftp.class2.php');
$secure = ($_POST['ftp']['secure'] == 1) ? TRUE : FALSE;
$url = 'ftp://' . $ftp_user . ':' . $ftp_pass . '@' . $ftp_host . ':' . $ftp_port . $ftp_path;

try
{
	$ftp = new Ftp($url, $secure);
}
catch (Exception $e)
{
	$error_msg = $e->getMessage();
	$ftp_error[] = "Error connecting to ftp: $error_msg";
}

if (is_object($ftp))
{
	if (!$ftp->fileExists('pico.findme'))
	{
		$ftp_error[] = 'Could not find pico.findme, please ensure that the FTP path you specified was valid';
	}
	else
	{
		// extra folders that need to be made because GIT doesn't like empty folders
		$extra_folders = array(
			'themes',
		);

		$writable_folders = array(
			'upload',
			'includes/tmp',
			'includes/storage',
		);

		$writable_files = array(
			'includes/config.blank.php',
			'site/javascript.cache',
		);

		$all_folders = array_merge($extra_folders, $writable_folders);

		foreach ($all_folders as $folder)
		{
			if (!is_dir($folder))
			{
				// make the dir
				if (!$ftp->tryMkdir($folder))
				{
					$ftp_error[] = 'Error creating folder: ' .$folder;
				}
			}
			
			if (in_array($folder, $writable_folders))
			{
				$ftp->chmod($folder, 0777);
				if (!is_writable($folder))
				{
					$ftp_error[] = 'Not writable: '.$folder;
				}
			}
		}
		
		foreach ($writable_files as $file)
		{
			$ftp->chmod($file, 0666);
			if (!is_writable($file))
			{
				$ftp_error[] = 'Not writable: '.$file;
			}
		}
	}
}

if (sizeof($ftp_error) == 0)
{
	$ftp_success = TRUE;
	$ftp_secure = ($secure) ? 1 : 0;

	$_SESSION['SAVE_SETTINGS']['ftp_host'] = $ftp_host;
	$_SESSION['SAVE_SETTINGS']['ftp_port'] = $ftp_port;
	$_SESSION['SAVE_SETTINGS']['ftp_username'] = $ftp_user;
	$_SESSION['SAVE_SETTINGS']['ftp_password'] = $ftp_pass;
	$_SESSION['SAVE_SETTINGS']['ftp_path'] = $ftp_path;
	$_SESSION['SAVE_SETTINGS']['ftp_sftp'] = $ftp_secure;
	$_SESSION['SAVE_SETTINGS']['pico_build_version'] = 1093;
}

?>
