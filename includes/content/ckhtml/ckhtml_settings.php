<?php
$request = basename($_SERVER['REQUEST_URI']);
if ($request == basename(__FILE__)) { echo 'You cannot access this file directly'; exit(); }

$live_edit = ($component_settings['live_edit'] == 1) ? 'checked="checked"' : '';

$html = <<<HTML
<table class="admin_list" cellpadding="2" cellspacing="1" border="0">
<tr class="a">
	<td>Live Edit Mode</td>
	<td><input type="checkbox" name="settings[live_edit]" value="1" $live_edit /></td>
</tr>
</table>
HTML;
?>
<div class="ap_overflow">
	<h3>Additional Settings</h3>
	<?=Pico_GetSettingsForm($component_id, $html)?>
</div>