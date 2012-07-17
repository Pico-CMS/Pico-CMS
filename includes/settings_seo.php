<?php
if ( (!defined('USER_ACCESS')) or (USER_ACCESS < 3) ) { exit(); }
?>
<div class="ap_overflow">
	<h3>Seo and Analytics</h3>
	<form method="post" action="<?=$body->url('includes/ap_actions.php')?>" onsubmit="Pico_SaveSettings(this); return false">
	<input type="hidden" name="ap_action" value="settings" />
	
	<table border="0" cellpadding="0" cellspacing="2" class="admin_list">
	<tr class="a">
		<td>Use Google Analytics?</td>
		<td>
			<input type="hidden" name="settings[use_google_analytics]" value="0" />
			<input type="checkbox" name="settings[use_google_analytics]" value="1" <?=(Pico_Setting('use_google_analytics') == 1) ? 'checked="checked"' : ''?> />
		</td>
	</tr>
	<tr class="b">
		<td>Google UA Code</td>
		<td><input type="text" name="settings[ga_code]" value="<?=Pico_Setting('ga_code')?>" /></td>
	</tr>
	<tr class="a">
		<td>Default Meta Keywords</td>
		<td><textarea class="ap_textarea" name="settings[default_meta_keywords]"><?=Pico_Setting('default_meta_keywords')?></textarea></td>
	</tr>
	<tr class="b">
		<td>Default Meta Description</td>
		<td><textarea class="ap_textarea" name="settings[default_meta_desc]"><?=Pico_Setting('default_meta_desc')?></textarea></td>
	</tr>
	</table>
	
	<input type="submit" name="submit_btn" value="Save" />
	</form>
	
	<?=$back?>
</div>