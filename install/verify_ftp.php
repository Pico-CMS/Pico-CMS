<?php

$port      = (int) $_POST['ftp']['port'];
$username  = (string) $_POST['ftp']['username'];
$password  = (string) $_POST['ftp']['password'];
$path      = (string) $_POST['ftp']['path'];
$host      = (string) $_POST['ftp']['host'];
$ftp_ok	   = (string) $_POST['ftp']['ok'];
$ftp_error = array();

if($ftp_ok == "skip")
{
	$_SESSION['FTP_OK'] = TRUE;
	$_SESSION['install_step'] = 2;
}

ob_start(); // surpress normal ftp class output

$ftp = new ftp(TRUE);
$ftp->Verbose = FALSE;
$ftp->LocalEcho = FALSE;
if(!$ftp->SetServer($host, $port, TRUE)) {
	$ftp->quit();
	$ftp_error[] = "Setting server failed\n";
}
else
{
	if (!$ftp->connect()) {
		$ftp_error[] = "Cannot connect\n";
	}
	else
	{
		if (!$ftp->login($username, $password)) {
			$ftp->quit();
			$ftp_error[] = "Login failed\n";
		}
	}
}

if (!isset($error))
{
	if (!$ftp->chdir($path))
	{
		$ftp_error[] = "Invalid path: $path\n";
	}
	else
	{

		$ftp->nlist("-la");
		$list = $ftp->rawlist(".", "-lA");
		
		if (sizeof($list) > 0)
		{
			foreach($list as $k=>$v)
			{
				// more readable list stats
				$list[$k]=$ftp->parselisting($v);
			}
		}
		
		// see if pico.findme is in here
		
		$correct_path = FALSE;
		
		foreach ($list as $file)
		{
			if ($file['name'] == 'pico.findme')
			{
				$correct_path = TRUE;
				break;
			}
		}
		
		if ($correct_path)
		{
			$writable_folders = array(
				'upload',
				'includes',
				'includes/content/blog/storage',
				'includes/content/external_newsletter/buttons',
				'includes/content/media/upload',
				'includes/content/media/files',
				'includes/content/media/galleries/enhanced_fader/files',
				'includes/content/media/galleries/marquee/files',
				'includes/content/media/galleries/multiple/files',
				'includes/content/media/galleries/slide/files',
				'includes/content/media/galleries/portfolio/files',
				'includes/content/media/galleries/simple/files',
				'includes/content/media/galleries/plain_img/files',
				'includes/content/media/galleries/horizontal_menu/files',
				'includes/content/media/galleries/grid_ajax/files',
				'includes/content/media/galleries/pane_viewer/files',
				'includes/content/media/galleries/project/files',
				'includes/content/media/galleries/jscript/files',
				'includes/content/linkmenu/fonts',
				'includes/content/contact/upload',
				'includes/content/contact/storage',
				'includes/content/contact/storage/buttons',
				'includes/uploader/thumbnails',
				'includes/uploader/tmp',
				'includes/tmp',
			);
			
			$writable_files = array(
				'includes/config.blank.php',
				'site/javascript.cache',
				'.htaccess',
			);
			
			foreach ($writable_folders as $folder)
			{
				if (!is_dir($folder))
				{
					// make the dir
					if (!$ftp->mkdir($folder))
					{
						$ftp_error[] = 'Error creating folder: ' .$folder;
					}
				}
				
				if (!$ftp->chmod($folder, 0777))
				{
					$ftp_error[] = 'Unable to CHMOD '.$folder;
				}
			}
			
			foreach ($writable_files as $file)
			{
				if (!$ftp->chmod($file, 0666))
				{
					$ftp_error[] = 'Unable to CHMOD '.$file;
				}
			}
		}
		else
		{
			$ftp_error[] = 'Could not find pico.findme, please ensure that the FTP path you specified was valid';
		}

		$ftp->quit();
	}
}

$ftp_log = ob_get_contents();
ob_end_clean();

if ( (sizeof($ftp_error) == 0) and ($correct_path) )
{
	$_SESSION['FTP_OK'] = TRUE;
	$_SESSION['FTP_INFORMATION'] = array(
		'host' => $host,
		'port' => $port,
		'username' => $username,
		'password' => $password,
		'path' => $path,
	);
}

?>