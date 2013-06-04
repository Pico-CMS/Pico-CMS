<?php

require_once('includes/content/blog2/functions.php');

$options = $db->result('SELECT `additional_info` FROM `'.DB_CONTENT.'` WHERE `instance_id`=?', $instance_id);

$settings = unserialize($options);
if (!is_array($settings)) { $settings = array(); }

$choose_blog = '';
$blogs = $db->force_multi_assoc('SELECT `component_id`, `description` FROM `'.DB_COMPONENT_TABLE.'` WHERE `folder`=?', 'blog2');

$blog_drop = '<select name="settings[blog]"><option value=""></option>';
if ( (is_array($blogs)) and (sizeof($blogs) > 0) )
{
	foreach($blogs as $blog)
	{
		$selected = ($blog['component_id'] == $settings['blog']) ? 'selected="selected"' : '';
		$blog_drop .= '<option value="'.$blog['component_id'].'" '.$selected.'>'.$blog['description'].'</option>';
	}
}
$blog_drop .= '</select>';
?>
<div class="ap_overflow">
	<div style="float: left">
	<h3>Settings</h3>
	<form id="blogfeed_settings" method="post" action="<?=$body->url('includes/content/blog2_feed/submit.php')?>" onsubmit="BlogFeed_Update(this); return false">
		<input type="hidden" name="instance_id"  value="<?=$instance_id?>" />
		<input type="hidden" name="component_id" value="<?=$component_id?>" />
		<input type="hidden" name="page_action"  value="update" />
		<table border="0" cellpadding="2" cellspacing="1" class="admin_list">
		<tr class="a">
			<td class="bold">Blog Source</td>
			<td><?=$blog_drop?></td>
		</tr>
		<tr class="b">
			<td class="bold">Title</td>
			<td><input type="text" class="ap_text" name="settings[title]" value="<?=$settings['title']?>" /></td>
		</tr>
		<tr class="a">
			<td class="bold">Number of Entries</td>
			<td><input type="text" class="ap_text_sm" name="settings[num_entries]" value="<?=$settings['num_entries']?>" /> Use negative numbers for random</td>
		</tr>
		<tr class="b">
			<td class="bold">Only Show Tags</td>
			<td><input type="text" class="ap_text" name="settings[show_tags]" value="<?=$settings['show_tags']?>" /></td>
		</tr>
		<tr class="a">
			<td class="bold">Show RSS</td>
			<td><input type="checkbox"  name="settings[show_rss]" value="1" <?=($settings['show_rss'] == 1) ? 'checked="checked"' : ''?> /></td>
		</tr>
		<tr class="b">
			<td class="bold">Upcoming</td>
			<td><input type="checkbox"  name="settings[upcoming]" value="1" <?=($settings['upcoming'] == 1) ? 'checked="checked"' : ''?> /></td>
		</tr>
		<tr class="a">
			<td class="bold">Previous</td>
			<td><input type="checkbox"  name="settings[past]" value="1" <?=($settings['past'] == 1) ? 'checked="checked"' : ''?> /></td>
		</tr>
		</tr>
		<tr class="b">
			<td class="bold">No posts message</td>
			<td>
				<textarea name="settings[no_post]" class="ap_textarea_lg"><?=htmlspecialchars($settings['no_post'])?></textarea>
			</td>
		</tr>
		<tr class="a">
			<td class="bold">Layout</td>
			<td>
				<textarea name="settings[layout]" class="ap_textarea_lg"><?=htmlspecialchars($settings['layout'])?></textarea><br />
				<?=Blog2_GetLayoutDescHTML()?>
			</td>

		</tr>
		</table>
		<input type="submit" value="Update" />
	</form>
	</div>
</div>