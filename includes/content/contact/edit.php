<?php

if ($_GET['reload'] == 1)
{
	chdir('../../../');
	$component_id = $_GET['component_id'];
	require_once('core.php');
}

require_once('includes/content/contact/functions.php');

$contact_table = DB_PREFIX . 'pico_contact_form';
$history_table = DB_PREFIX . 'pico_contact_history';
$temp_history  = DB_PREFIX . 'pico_contact_temp_history';

if ( (!defined('USER_ACCESS')) or (USER_ACCESS < 0) ) { exit(); }

$table_query = <<<SQL
CREATE TABLE IF NOT EXISTS `$contact_table` (
	`component_id` BIGINT(11) NOT NULL,
	`recipient_address` VARCHAR(255) NOT NULL,
	`from_subject` VARCHAR(255) NOT NULL,
	`complete_message` TEXT,
	`fields` BLOB,
	`layout` VARCHAR(10),
	`submit_button` TEXT,
	`preview_message` TEXT,
	PRIMARY KEY (`component_id`)
);
SQL;
$new_table = $db->run($table_query);

$db->run(<<<SQL
CREATE TABLE IF NOT EXISTS `$history_table` (
	`entry_id` BIGINT(11) AUTO_INCREMENT,
	`component_id` BIGINT(11) NOT NULL,
	`timestamp` BIGINT(11) NOT NULL,
	`log_data` blob,
	`attachments` longblob,
	PRIMARY KEY (`entry_id`)
);
SQL
);

$db->run(<<<SQL
CREATE TABLE IF NOT EXISTS `$temp_history` (
	`entry_id` BIGINT(11) AUTO_INCREMENT,
	`start_time` BIGINT(11) NOT NULL,
	`component_id` BIGINT(11) NOT NULL,
	`session_id` VARCHAR(100) NOT NULL,
	`current_page` INT(4) NOT NULL,
	`answers` LONGBLOB NOT NULL,
	PRIMARY KEY (`entry_id`)
);
SQL
);

unset($error);

if (!function_exists('Pico_StorageDir')) {
	$error = 'Missing Pico Function: Pico_StorageDir(). Please ensure that your Pico is up to date.';
}
else
{
	$storage_dir = 'contact/'. $component_id .'/';
	$writable = Pico_StorageDir($storage_dir); // makes sure the folder exists and is writable
	if (!$writable) {
		$error = 'Storage directory is not writable (includes/storage/'.$storage_dir.'). Cannot continue.';
	}
}

if (isset($error)) {
	echo $error;
	return;
}

$settings = CF_GetSettings($component_id);

?>
<div class="ap_overflow">
<h3>Settings</h3>
<form id="cf_settings" method="post" style="height: auto" action="<?=$body->url('includes/content/contact/submit.php')?>" onsubmit="CF_UpdateSettings(this); return false">
<input type="hidden" name="component_id" id="component_id" value="<?=$component_id?>" />
<input type="hidden" name="page_action"  value="update" />
<table border="0" cellpadding="2" cellspacing="1" class="admin_list">
<tr class="a">
	<td class="bold">From Subject</td>
	<td><input type="text" class="ap_text" name="from_subject" value="<?=$settings['from_subject']?>" /></td>
</tr>
<tr class="b">
	<td class="bold">Recipient Address</td>
	<td><input type="text" class="ap_text" name="recipient_address" value="<?=$settings['recipient_address']?>" /></td>
</tr>
<tr class="a">
	<td colspan="2">
		<p class="bold">Form Preview Message</p>
		<input type="hidden" name="preview_message" value="" />
		<textarea class="ap_textarea_lg" name="preview_message_editor"><?=$settings['preview_message']?></textarea>
		<br />The above is what the end-user will see before they submit the form
	</td>
</tr>
<tr class="b">
	<td colspan="2">
		<p class="bold">Form Interim Message</p>
		<input type="hidden" name="interim_message" value="" />
		<textarea class="ap_textarea_lg" name="interim_message_editor"><?=$settings['interim_message']?></textarea>
		<br />The above is what the end-user will see on pages 2+ as they are completing the form.
	</td>
</tr>
<tr class="a">
	<td colspan="2">
		<p class="bold">Form Complete Message</p>
		<input type="hidden" name="complete_message" value="" />
		<textarea class="ap_textarea_lg" name="complete_message_editor"><?=$settings['complete_message']?></textarea>
		<br />The above is what the end-user will see when they successfully complete the form
	</td>
</tr>
<tr class="b">
	<td class="bold">Contact Form Layout</td>
	<td>
		<select name="layout">

			<option value="classic" <?=($settings['layout']=='classic') ? 'selected="selected"' : '' ?>>Classic</option>
			<?php
				$layout_dir = 'includes/content/contact/layouts/';
				$layout_files = array();
				if (is_dir($layout_dir)) {
				    if ($dh = opendir($layout_dir)) {
				        while (($file = readdir($dh)) !== false) {
				            if (substr($file, 0, 1) != '.') {
				            	$layout_files[] = $file;
				            }
				        }
				        closedir($dh);
				    }
				}

				//print_r($layout_files);	

				for ($x = 0; $x < sizeof($layout_files); $x++) {
					$layout = str_replace('.php', '', $layout_files[$x]);
					echo '<option value="'.$layout.'" '.(($settings['layout']==$layout) ? 'selected="selected"' : '').'>' . ucfirst($layout) . '</option>';
				}
			?>
		</select>
	</td>
</tr>
<tr class="a">
	<td class="bold">Message Format</td>
	<td>
		<select name="message_format">
			<option value="txt" <?=($settings['message_format']=='txt') ? 'selected="selected"' : '' ?>>Plain Text</option>
			<option value="html" <?=($settings['message_format']=='html') ? 'selected="selected"' : '' ?>>HTML</option>
		</select>
	</td>
</tr>
<tr class="b">
	<td class="bold">Send User Response</td>
	<td>
		<input type="hidden" name="send_user_copy" value="0" />
		<input type="checkbox" name="send_user_copy" value="1" <?=($settings['send_user_copy']==1) ? 'checked="checked"' : '' ?> /> 
		Check this box if you want to send the user an automated response e-mail.
	</td>
</tr>
<tr class="a">
	<td class="bold">User Response Email</td>
	<td>
		Subject: <br /><input type="text" class="ap_text" name="copy_subject" value="<?=$settings['copy_subject']?>" /><br />
		Message: <br />
		<textarea class="ap_textarea" name="copy_message"><?=$settings['copy_message']?></textarea>
	</td>
</tr>
<tr>
	<td colspan="2">
		<h3>Captcha Settings</h3>
	</td>
</tr>
<tr class="a">
	<td class="bold">Use ReCaptcha</td>
	<td>
		<input type="checkbox" value="1" name="recaptcha[use_recaptcha]" <?=($settings['recaptcha']['use_recaptcha'] == 1) ? 'checked="checked"' : ''?> />
	</td>
</tr>
<tr class="b">
	<td class="bold">ReCaptcha Style</td>
	<td>
		<select name="recaptcha[style]">
			<option value="red" <?=($settings['recaptcha']['style']=='red') ? 'selected="selected"' : '' ?>>Red</option>
			<option value="white" <?=($settings['recaptcha']['style']=='white') ? 'selected="selected"' : '' ?>>White</option>
			<option value="blackglass" <?=($settings['recaptcha']['style']=='blackglass') ? 'selected="selected"' : '' ?>>Black</option>
			<option value="clean" <?=($settings['recaptcha']['style']=='clean') ? 'selected="selected"' : '' ?>>Clean</option>
		</select>
	</td>
</tr>
<tr class="a">
	<td class="bold">Public Key</td>
	<td>
		<input type="text" value="<?=$settings['recaptcha']['pub_key']?>" name="recaptcha[pub_key]" />
	</td>
</tr>
<tr class="b">
	<td class="bold">Private Key</td>
	<td>
		<input type="text" value="<?=$settings['recaptcha']['prv_key']?>" name="recaptcha[prv_key]" />
	</td>
</tr>
<tr>
	<td colspan="2">
		<h3>Buttons</h3>
		<p>To upload a button, choose which button type you'd like to upload then click the Browse button to select your image. Be sure to click the Update button</p>
	</td>
</tr>
<tr class="a">
	<td class="bold">Button Upload</td>
	<td>
		<table border="0" cellpadding="1" cellspacing="0">
		<tr><td>
			Select button to upload...<br />
			<select id="cf_button_type">
				<option value="submit">Submit</option>
				<option value="submit_hover">Submit - Hover</option>
				<option value="continue">Continue</option>
				<option value="continue_hover">Continue - Hover</option>
			</select>
		</td><td>
			<?php
			$upload_path = $body->url('includes/upload.php');
			$uploader    = new Uploader($upload_path, 'CF_SubmitButton', '', '.jpg, .png, .gif', 'Image Files (jpg/png/gif)', '000000', 'bbde9c');
			echo $uploader->Output();
			?>
			</td>
		</tr>
		</table>
	</td>
</tr>
<tr class="b">
	<td class="bold">Submit Button</td>
	<td>
	<?php
	$output = CF_ShowButtonEditForm('submit', $component_id, $settings['buttons']);
	echo $output;
	?>
	</td>
</tr>
<tr class="a">
	<td class="bold">Submit Button - Hover</td>
	<td>
	<?php
	$output = CF_ShowButtonEditForm('submit_hover', $component_id, $settings['buttons']);
	echo $output;
	?>
	</td>
</tr>
<tr class="b">
	<td class="bold">Continue Button</td>
	<td>
	<?php
	$output = CF_ShowButtonEditForm('continue', $component_id, $settings['buttons']);
	echo $output;
	?>
	</td>
</tr>
<tr class="a">
	<td class="bold">Continue Button - Hover</td>
	<td>
	<?php
	$output = CF_ShowButtonEditForm('continue_hover', $component_id, $settings['buttons']);
	echo $output;
	?>
	</td>
</tr>
</table>
<input type="submit" value="Save" class="co_button" />
</form>
</div>