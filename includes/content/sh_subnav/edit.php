<?php
$additional_info  = $db->result('SELECT `additional_info` FROM `'.DB_CONTENT.'` WHERE `instance_id`=?', $instance_id);
$settings         = unserialize($additional_info);
if (!is_array($settings)) { $settings = array(); }
?>
<div class="ap_overflow">
	<form method="post" action="<?=$body->url('includes/content/sh_subnav/submit.php')?>" onsubmit="new Ajax.Form(this); return false" style="height: auto">
	<input type="hidden" name="page_action" value="update" />
	<input type="hidden" name="component_id" value="<?=$component_id?>" />
	<h3>Settings</h3>
	<table border="0" cellpadding="2" cellspacing="1" class="admin_list">
	<tr class="a">
		<td>Static Title</td>
		<td><input type="text" name="settings[title]" value="<?=$settings['title']?>" /></td>
	</tr>
	<tr class="b">
		<td>Main Nav</td>
		<td><input type="checkbox" name="settings[main_nav]" value="1" <?=(($settings['main_nav']==1)?'checked="checked"' : '')?>/></td>
	</tr>
	<tr class="a">
		<td>Dropdown</td>
		<td><input type="checkbox" name="settings[dropdown]" value="1" <?=(($settings['dropdown']==1)?'checked="checked"' : '')?>/></td>
	</tr>
	<tr class="b">
		<td>Test Mode</td>
		<td><input type="checkbox" name="settings[test_mode]" value="1" <?=(($settings['test_mode']==1)?'checked="checked"' : '')?>/></td>
	</tr>
	<tr class="a">
		<td>Show Section Title</td>
		<td><input type="checkbox" name="settings[show_section_title]" value="1" <?=(($settings['show_section_title']==1)?'checked="checked"' : '')?>/></td>
	</tr>
	</table>
	<input type="submit" value="Update" />
	</form>
	
	<p><span class="click" onclick="Pico_SiteHeirarchy()">Go To Site Hierarchy</span></p>
</div>