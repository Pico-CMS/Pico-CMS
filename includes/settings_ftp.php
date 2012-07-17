<?php
if ($_POST['page_action'] == 'update_ftp')
{
	chdir('../');
	require_once('core.php');
	if ( (!defined('USER_ACCESS')) or (USER_ACCESS < 3) ) { exit(); }
	
	include('includes/ftp/ftp_class.php');
	$ftp_error = array();
	$ftp_settings = $_POST['settings'];
	
	ob_start(); // surpress normal ftp class output
	$ftp = new ftp(TRUE);
	$ftp->Verbose = FALSE;
	$ftp->LocalEcho = FALSE;
	
	if (strlen($ftp_settings['ftp_port']) > 0)
	{
		$ftp_settings['ftp_port'] = (int) $ftp_settings['ftp_port'];
	}
	else
	{
		$port = 21;
	}
	
	if(!$ftp->SetServer($ftp_settings['ftp_host'], $ftp_settings['ftp_port'], TRUE)) 
	{
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
			if (!$ftp->login($ftp_settings['ftp_username'], $ftp_settings['ftp_password'])) {
				$ftp->quit();
				$ftp_error[] = "Login failed\n";
			}
		}
	}
	
	if (sizeof($ftp_error) == 0)
	{
		$path = $ftp_settings['ftp_path'];
		// see if we have the right path
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
				// good to go!
				
				$ftp_log = ob_get_contents();
				ob_end_clean();
				
				//echo "FTP Connection Successful. Settings Saved";
				
				Pico_Setting('ftp_host', $ftp_settings['ftp_host']);
				Pico_Setting('ftp_port', $ftp_settings['ftp_port']);
				Pico_Setting('ftp_path', $ftp_settings['ftp_path']);
				Pico_Setting('ftp_username', $ftp_settings['ftp_username']);
				Pico_Setting('ftp_password', $ftp_settings['ftp_password']);
				
				exit();
			}
		}
	}
	else
	{
		$ftp_log = ob_get_contents();
		ob_end_clean();
		
		echo "There were errors connecting:\n";
		
		foreach ($ftp_error as $e)
		{
			echo $e;
		}
		
		/*
		echo "Log:\n===========\n";
		echo $ftp_log;*/
		
		echo "Settings not saved";
		
		exit();
	}
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
	<tr>
		<td colspan="2" style="text-align: center">
			<input type="submit" name="submit_btn" value="Save" />
		</td>
	</tr>
	</table>
	</form>
	<?=$back?>
</div>