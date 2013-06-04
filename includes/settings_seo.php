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
	<tr class="a">
		<td>Site Title</td>
		<td><input type="text" name="settings[global_site_title]" value="<?=htmlspecialchars(Pico_Setting('global_site_title'))?>" /></td>
	</tr>
	<tr class="b">
		<td>&lt;title&gt;</td>
		<td>
			<input type="hidden" name="settings[title_show_1]" value="0" />
			<input type="checkbox" name="settings[title_show_1]" value="1" <?=(Pico_Setting('title_show_1')==1) ? 'checked="checked"' : ''?> /> Include Site Title<br />
			<input type="hidden" name="settings[title_show_2]" value="0" />
			<input type="checkbox" name="settings[title_show_2]" value="1" <?=(Pico_Setting('title_show_2')==1) ? 'checked="checked"' : ''?> /> Include Page Title<br />
			<input type="hidden" name="settings[title_show_3]" value="0" />
			<input type="checkbox" name="settings[title_show_3]" value="1" <?=(Pico_Setting('title_show_3')==1) ? 'checked="checked"' : ''?> /> Include Component Title<br />
		</td>
	</tr>
	<tr class="a">
		<td>Title Separator (ex: ' | ')</td>
		<td><input type="text" name="settings[title_separator]" value="<?=htmlspecialchars(Pico_Setting('title_separator'))?>" /></td>
	</tr>
	<tr class="b">
		<td>Component Title...</td>
		<td>
			<input type="radio" name="settings[title_part3_option]" value="1" <?=(Pico_Setting('title_part3_option')==1) ? 'checked="checked"' : ''?> /> Overwrites Page Title<br />
			<input type="radio" name="settings[title_part3_option]" value="2" <?=(Pico_Setting('title_part3_option')==2) ? 'checked="checked"' : ''?> /> Appends Page Title<br />
		</td>
	</tr>
	</table>
	
	<input type="submit" name="submit_btn" value="Save" />
	</form>
	
	<?=$back?>
</div>