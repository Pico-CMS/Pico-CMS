<?php

if ($_GET['reload'] == 1)
{
	$component_id = $_GET['component_id'];
	chdir('../../../');
	require_once('core.php');
}

if ((!defined('USER_ACCESS')) or (USER_ACCESS < 3)) { exit(); }

$group_payment_settings = DB_PREFIX . 'user_group_payment_settings';
$db->run(<<<SQL
CREATE TABLE IF NOT EXISTS `$group_payment_settings` (
	`entry_id` BIGINT(11) AUTO_INCREMENT,
	`component_id` BIGINT(11) NOT NULL,
	`group_id` BIGINT(11) NOT NULL,
	`duration` BIGINT(11) NOT NULL,
	`cost` FLOAT,
	`display` TINYINT(1) NOT NULL DEFAULT 1,
	`description` TEXT,
	PRIMARY KEY (`entry_id`)
)
SQL
);

$additional_info  = $db->result('SELECT `additional_info` FROM `'.DB_CONTENT.'` WHERE `component_id`=?', $component_id);
$settings         = unserialize($additional_info);
if (!is_array($settings)) { $settings = array(); }

// get all the groups
$group_table  = DB_PREFIX . 'pico_groups';
$groups = $db->force_multi_assoc('SELECT * FROM `'.$group_table.'` ORDER BY `name` ASC');

$output = '';
if (is_array($groups))
{
	$output .= '<table border="0" cellpadding="2" cellspacing="1" class="admin_list">';
	$output .= '<tr><th>Group</th><th>Enabled</th><th>Actions</th></tr>';
	
	$enabled_groups = unserialize($settings['groups']);
	if (!is_array($enabled_groups)) { $enabled_groups = array(); }
	
	$counter = 0;
	foreach ($groups as $group)
	{
		$edit = (in_array($group['group_id'], $enabled_groups)) ? '<img src="'.$body->url('includes/icons/content.png').'" title="Payment Settings" class="icon click" onclick="US_PaymentSettings('.$component_id.', '.$group['group_id'].')" />' : '';
	
		$class   = ($counter % 2 == 0) ? 'a' : 'b'; $counter++;
		$checked = (in_array($group['group_id'], $enabled_groups)) ? 'checked="checked"' : '';
		$output .= '<tr class="'.$class.'">
			<td>'.$group['name'].'</td>
			<td><input type="checkbox" name="settings[groups][]" value="'.$group['group_id'].'" '.$checked.' /></td>
			<td>'.$edit.'</td>
		</tr>';
	}
	
	$output .= '</table>';
}

?>
<div class="ap_overflow">
	<p>From here you can configure what User Groups you will allow access to.</p>
	<form method="post" action="<?=$body->url('includes/content/user_signup/submit.php')?>" onsubmit="US_UpdateSettings(this); return false">
		<input type="hidden" name="page_action" value="update" />
		<input type="hidden" name="component_id" value="<?=$component_id?>" />
		<h3>Groups Configuration</h3>
		<?=$output?>

		<h3>Signup Settings</h3>

		<table border="0" cellpadding="2" cellspacing="1" class="admin_list">
		<tr class="a">
			<td class="bold">Intro Text</td>
			<td><textarea name="settings[intro_text]" class="ap_textarea"><?=htmlspecialchars($settings['intro_text'])?></textarea></td>
		</tr>
		<tr class="b">
			<td class="bold">Step 1 - Signup Form</td>
			<td><textarea name="settings[step1]" class="ap_textarea"><?=htmlspecialchars($settings['step1'])?></textarea></td>
		</tr>
		<tr class="a">
			<td class="bold">Step 2 - Make Payment</td>
			<td><textarea name="settings[step2]" class="ap_textarea"><?=htmlspecialchars($settings['step2'])?></textarea></td>
		</tr>
		<tr class="b">
			<td class="bold">Step 3 - Complete</td>
			<td><textarea name="settings[step3]" class="ap_textarea"><?=htmlspecialchars($settings['step3'])?></textarea></td>
		</tr>
		<tr class="a">
			<td class="bold">Invoice Description</td>
			<td>
				<input type="text" class="ap_text" name="settings[invoice_description]" value="<?=$settings['invoice_description']?>" />
				<p class="small">What will be shown to the user when completing their transaction (in PayPal)</p>
			</td>
		</tr>
		<tr class="b">
			<td class="bold">Redirection Link</td>
			<td>
				<input type="text" class="ap_text" name="settings[redirection_link]" value="<?=$settings['redirection_link']?>" />
				<p class="small">Where users will be sent upon successful registration</p>
			</td>
		</tr>
		<tr class="a">
			<td class="bold">Log user in when registered?</td>
			<td><input type="checkbox" name="settings[log_user_in]" value="1" <?=($settings['log_user_in'] == 1)? 'checked="checked"' : ''?>></td>
		</tr>
		<tr class="b">
			<td class="bold">Require Moderator Approval?</td>
			<td><input type="checkbox" name="settings[moderator_approval]" value="1" <?=($settings['moderator_approval'] == 1)? 'checked="checked"' : ''?>></td>
		</tr>
		<tr class="a">
			<td class="bold">Moderators to notify</td>
			<td>
				<?php
					$users = $db->force_multi_assoc('SELECT * FROM `'.DB_USER_TABLE.'` WHERE `access` > ? ORDER BY `username` ASC', 2);
					foreach ($users as $user)
					{
						$checked = (in_array($user['id'], $settings['moderators'])) ? 'checked="checked"' : '';
						echo '<input type="checkbox" name="settings[moderators][]" value="'.$user['id'].'" '.$checked.' /> ' . $user['username'] . '<br />';
					}
				?>
			</td>
		</tr>
		<tr class="b">
			<td class="bold">Approval Email</td>
			<td>
				<div class="bold">Subject</div>
				<input type="text" class="ap_text" name="settings[approval_email_subject]" value="<?=$settings['approval_email_subject']?>" />
				<div class="bold">Message</div>
				<textarea name="settings[approval_email_message]" class="ap_textarea"><?=htmlspecialchars($settings['approval_email_message'])?></textarea><br />
				Variables: FIRST_NAME, LAST_NAME
				<p class="small">The email the user will get when his/her account is approved</p>
			</td>
		</tr>
		<tr class="a">
			<td class="bold">Decline/Delete Email</td>
			<td>
				<div class="bold">Subject</div>
				<input type="text" class="ap_text" name="settings[delete_email_subject]" value="<?=$settings['delete_email_subject']?>" />
				<div class="bold">Message</div>
				<textarea name="settings[delete_email_message]" class="ap_textarea"><?=htmlspecialchars($settings['delete_email_message'])?></textarea><br />
				Variables: FIRST_NAME, LAST_NAME
				<p class="small">The email the user will get when his/her account is denied</p>
			</td>
		</tr>
		</table>
		<input type="submit" value="Update" />
	</form>
</div>