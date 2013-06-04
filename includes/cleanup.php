<?php
require_once('includes/ftp.class2.php');

$settings_table = DB_PREFIX . 'pico_settings';

$ftp = Pico_ConnectFTP();
if (!is_object($ftp)) { exit($ftp); }

$ftp->deleteRecursive('install');
if (is_dir('install')) {
	$ftp_error[] = 'Could not delete install folder';
}

$ftp->chmod('includes/config.php', 0644);
if (is_writable('includes/config.php'))
{
	$ftp_error[] = "Error chmod'ing config file";
}

if (sizeof($ftp_error) == 0)
{
	echo 'Cleanup successful. You may now close this window.';
}
else
{
	foreach ($ftp_error as $error)
	{
		echo '<div class="error">'.$error.'</div>';
	}
}
?>