<?php

$ftp = Pico_ConnectFTP();
if (!is_object($ftp)) { echo "Error cleaning up install: " . $ftp; exit(); }

@$ftp->deleteRecursive('install');
@$ftp->chmod('.htaccess', 0644);
@$ftp->chmod('includes/config.php', 0644);
@$ftp->chmod('.htaccess', 0644);

if (file_exists('install'))
{
	$ftp_error[] = 'Could not delete install folder';
}

if (is_writable('includes'))
{
	$ftp_error[] = "Error chmod'ing includes folder\n";
}

// chmod htaccess and config file

if (is_writable('.htaccess'))
{
	$ftp_error[] = "Error chmod'ing .htaccess file\n";
}

if (is_writable('includes/config.php'))
{
	$ftp_error[] = "Error chmod'ing config file\n";
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
