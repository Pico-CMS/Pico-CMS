<?php

$additional_info = $db->result('SELECT `additional_info` FROM `'.DB_COMPONENT_TABLE.'` WHERE `component_id`=?', $component_id);
$settings        = unserialize($additional_info);
if (!is_array($settings)) { $settings = array(); }

?>

<div class="ap_overflow">
	<form method="post" action="<?=$body->url('includes/content/quotes/submit.php')?>" onsubmit="Q_SaveSettings(this); return false" />
		<input type="hidden" name="component_id" id="component_id" value="<?=$component_id?>" />
		<input type="hidden" name="page_action" value="update_options" />
		<table border="0" cellpadding="2" cellspacing="1" class="admin_list">
		<tr>
			<th colspan="2">Additional Settings</th>
		</tr>
		<tr class="a">
			<td>Remove Quotation Marks</td>
			<td>
				<input type="hidden" name="settings[remove_quotes]" value="0" />
				<input type="checkbox" name="settings[remove_quotes]" value="1" <?=($settings['remove_quotes'] == 1) ? 'checked="checked"' : ''?> />
			</td>
		</tr>
		</table>
		<input type="submit" value="Save" />
	</form>
</div>