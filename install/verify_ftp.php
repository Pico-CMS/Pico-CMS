<?php

$ftp_ok    = FALSE;
$port      = (int) $_POST['ftp']['port'];
$username  = (string) $_POST['ftp']['username'];
$password  = (string) $_POST['ftp']['password'];
$path      = (string) $_POST['ftp']['path'];
$host      = (string) $_POST['ftp']['host'];
$ftp_error = array();

ob_start(); // surpress normal ftp class output

$ftp = new ftp(TRUE);
$ftp->Verbose = FALSE;
$ftp->LocalEcho = FALSE;
if(!$ftp->SetServer($host, $port, TRUE)) {
	$ftp->quit();
	$ftp_error[] = "Seting server failed\n";
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
			if (!$ftp->chmod('upload', 0777))
			{
				$ftp_error[] = "Error chmod'ing upload folder\n";
			}
			if (!$ftp->chmod('includes', 0777))
			{
				$ftp_error[] = "Error chmod'ing includes folder\n";
			}
			if (!$ftp->chmod('includes/content/blog/storage', 0777))
			{
				$ftp_error[] = "Error chmod'ing blog storage folder\n";
			}
			if (!$ftp->chmod('includes/content/external_newsletter/buttons', 0777))
			{
				$ftp_error[] = "Error chmod'ing external newsletter buttons\n";
			}
			if (!$ftp->chmod('includes/content/media/upload', 0777))
			{
				$ftp_error[] = "Error chmod'ing media upload folder\n";
			}
			if (!$ftp->chmod('includes/content/media/files', 0777))
			{
				$ftp_error[] = "Error chmod'ing media files folder\n";
			}
			if (!$ftp->chmod('includes/content/media/galleries/enhanced_fader/files', 0777))
			{
				$ftp_error[] = "Error chmod'ing enhanced fader files folder\n";
			}
			if (!$ftp->chmod('includes/content/media/galleries/marquee/files', 0777))
			{
				$ftp_error[] = "Error chmod'ing marquee files folder\n";
			}
			if (!$ftp->chmod('includes/content/media/galleries/multiple/files', 0777))
			{
				$ftp_error[] = "Error chmod'ing multiple files folder\n";
			}
			if (!$ftp->chmod('includes/content/media/galleries/slide/files', 0777))
			{
				$ftp_error[] = "Error chmod'ing slide files folder\n";
			}
			if (!$ftp->chmod('includes/content/media/galleries/portfolio/files', 0777))
			{
				$ftp_error[] = "Error chmod'ing portfolio files folder\n";
			}
			if (!$ftp->chmod('includes/content/media/galleries/simple/files', 0777))
			{
				$ftp_error[] = "Error chmod'ing simple files folder\n";
			}
			if (!$ftp->chmod('includes/content/media/galleries/plain_img/files', 0777))
			{
				$ftp_error[] = "Error chmod'ing plain img files folder\n";
			}
			if (!$ftp->chmod('includes/content/media/galleries/horizontal_menu/files', 0777))
			{
				$ftp_error[] = "Error chmod'ing horizontal menu files folder\n";
			}
			if (!$ftp->chmod('includes/content/media/galleries/grid_ajax/files', 0777))
			{
				$ftp_error[] = "Error chmod'ing horizontal menu files folder\n";
			}
			if (!$ftp->chmod('includes/content/media/galleries/pane_viewer/files', 0777))
			{
				$ftp_error[] = "Error chmod'ing horizontal menu files folder\n";
			}
			if (!$ftp->chmod('includes/content/media/galleries/project/files', 0777))
			{
				$ftp_error[] = "Error chmod'ing horizontal menu files folder\n";
			}
			if (!$ftp->chmod('includes/content/media/galleries/jscript/files', 0777))
			{
				$ftp_error[] = "Error chmod'ing horizontal menu files folder\n";
			}
			if (!$ftp->chmod('includes/content/linkmenu/fonts', 0777))
			{
				$ftp_error[] = "Error chmod'ing linkmenu fonts folder\n";
			}
			if (!$ftp->chmod('includes/content/contact/upload', 0777))
			{
				$ftp_error[] = "Error chmod'ing contact upload folder\n";
			}
			if (!$ftp->chmod('includes/content/contact/storage', 0777))
			{
				$ftp_error[] = "Error chmod'ing contact storage folder\n";
			}
			if (!$ftp->chmod('includes/content/contact/storage/buttons', 0777))
			{
				$ftp_error[] = "Error chmod'ing contact button upload folder\n";
			}
			if (!$ftp->chmod('includes/config.blank.php', 0777))
			{
				$ftp_error[] = "Error chmod'ing config file\n";
			}
			if (!$ftp->chmod('site/javascript.cache', 0666))
			{
				$ftp_error[] = "Error javascript cache file\n";
			}
			if (!$ftp->chmod('includes/uploader/thumbnails', 0777))
			{
				$ftp_error[] = "Error chmod'ing uploader thumbnails folder\n";
			}
			if (!$ftp->chmod('includes/uploader/tmp', 0777))
			{
				$ftp_error[] = "Error chmod'ing uploader tmp folder\n";
			}
			if (!$ftp->chmod('includes/tmp', 0777))
			{
				$ftp_error[] = "Error chmod'ing tmp folder\n";
			}
			if (!$ftp->chmod('.htaccess', 0777))
			{
				$ftp_error[] = "Error chmod'ing htacces install file\n";
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



//print_r($list);


?>