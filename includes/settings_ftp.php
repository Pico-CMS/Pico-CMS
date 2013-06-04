<?php
if ($_POST['page_action'] == 'update_ftp')
{
	chdir('../');
	require_once('core.php');
	if ( (!defined('USER_ACCESS')) or (USER_ACCESS < 3) ) { exit(); }

	$ftp_settings = $_POST['settings'];
	$path         = $ftp_settings['ftp_path'];

	include('includes/ftp.class2.php');

	$url = 'ftp://' . $ftp_settings['ftp_username'] . ':' . $ftp_settings['ftp_password'] . '@' . $ftp_settings['ftp_host'] . ':' . $ftp_settings['ftp_port'] . $path;
	$secure = ($ftp_settings['ftp_sftp'] == 1) ? TRUE : FALSE;

	try
	{
		$ftp = new Ftp($url, $secure);
	}
	catch (Exception $e)
	{
		$error_msg = $e->getMessage();
		echo "Error connecting to ftp: $error_msg";
		exit();
	}
	
	if (!$ftp->fileExists('pico.findme'))
	{
		echo "Invalid Pico Installation Path: $path\n";
		exit();
	}

	Pico_Setting('ftp_host', $ftp_settings['ftp_host']);
	Pico_Setting('ftp_port', $ftp_settings['ftp_port']);
	Pico_Setting('ftp_path', $ftp_settings['ftp_path']);
	Pico_Setting('ftp_username', $ftp_settings['ftp_username']);
	Pico_Setting('ftp_password', $ftp_settings['ftp_password']);

	echo "FTP Connection Successful. Settings Saved";
	exit();
}

if ( (!defined('USER_ACCESS')) or (USER_ACCESS < 3) ) { exit(); }
?>
<div class="ap_overflow">
	<h3>FTP Settings</h3>
	<p>Pico needs these settings to be accurate so that it can maintain file structures for you and apply remote updates</p>
	
	<form method="post" action="<?=$body->url('includes/settings_ftp.php')?>" onsubmit="Pico_SaveSettings(this); return false;">
	<input type="hidden" name="page_action" value="update_ftp" />
	<table border="0" cellpadding="0" cellspacing="2" class="admin_list">
	<tr class="a">
		<td>Host</td>
		<td><input type="text" name="settings[ftp_host]" value="<?=Pico_Setting('ftp_host')?>" /></td>
	</tr>
	<tr class="b">
		<td>Port</td>
		<td><input type="text" name="settings[ftp_port]" value="<?=Pico_Setting('ftp_port')?>" /></td>
	</tr>
	<tr class="a">
		<td>Username</td>
		<td><input type="text" name="settings[ftp_username]" value="<?=Pico_Setting('ftp_username')?>" /></td>
	</tr>
	<tr class="b">
		<td>Password</td>
		<td><input type="text" name="settings[ftp_password]" value="<?=Pico_Setting('ftp_password')?>" /></td>
	</tr>
	<tr class="a">
		<td>Path to Pico</td>
		<td><input type="text" name="settings[ftp_path]" value="<?=Pico_Setting('ftp_path')?>" /></td>
	</tr>
	<tr class="b">
		<td>Use SFTP</td>
		<td><input type="checkbox" name="settings[ftp_sftp]" value="1" <?=(Pico_Setting('ftp_sftp') == 1) ? 'checked="checked"' : ''?> /></td>
	</tr>
	<tr>
		<td colspan="2" style="text-align: center">
			<input type="submit" name="submit_btn" value="Save" />
		</td>
	</tr>
	</table>
	</form>
	<?=$back?>
</div>