<?php
require_once('includes/content/user_login/functions.php');
$additional_info  = $db->result('SELECT `additional_info` FROM `'.DB_CONTENT.'` WHERE `component_id`=?', $component_id);
$settings         = unserialize($additional_info);
if (!is_array($settings)) { $settings = array(); }
?>

<div class="ap_overflow">
	<form method="post" action="<?=$body->url('includes/content/user_login/submit.php')?>" onsubmit="UL_UpdateSettings(this); return false">
		<input type="hidden" name="page_action" value="update" />
		<input type="hidden" name="component_id" value="<?=$component_id?>" />
		<table border="0" cellpadding="2" cellspacing="1" class="admin_list">
		<tr class="a">
			<td>Intro Text</td>
			<td><textarea class="ap_textarea" name="settings[intro-text]"><?=$settings['intro-text']?></textarea></td>
		</tr>
		<tr class="b">
			<td>Failed Login Text</td>
			<td><textarea class="ap_textarea" name="settings[failed-text]"><?=$settings['failed-text']?></textarea></td>
		</tr>
		<tr class="a">
			<td>Forgot Password Text</td>
			<td><textarea class="ap_textarea" name="settings[forgot-pwd-text]"><?=$settings['forgot-pwd-text']?></textarea></td>
		</tr>
		<tr class="b">
			<td>Password Reset</td>
			<td>
				<textarea class="ap_textarea" name="settings[pwd-reset]"><?=$settings['pwd-reset']?></textarea>
			</td>
		</tr>
		<tr class="a">
			<td>New Password E-mail</td>
			<td>
				<b>Subject:</b> <input type="text" name="settings[new-pwd-email-subject]" value="<?=$settings['new-pwd-email-subject']?>" /><br />
				<textarea class="ap_textarea" name="settings[new-pwd-email]"><?=$settings['new-pwd-email']?></textarea><br />
				FIRST_NAME, LAST_NAME, EMAIL_ADDRESS, PASSWORD
			</td>
		</tr>
		<tr class="b">
			<td>Successful Login Redirect</td>
			<td>
				<?=UL_GetPagesDropdown("redirect",$settings['redirect'])?> or link: <input type="text" name="settings[custom_login_url]" value="<?=$settings['custom_login_url']?>" />
			</td>
		</tr>
		<tr class="a">
			<td>Expired Login Redirect</td>
			<td>
				<?=UL_GetPagesDropdown("expired",$settings['expired'])?> or link: <input type="text" name="settings[custom_expired_url]" value="<?=$settings['custom_expired_url']?>" />
			</td>
		</tr>
		</table>
		<input type="submit" value="Update" />
	</form>
</div>