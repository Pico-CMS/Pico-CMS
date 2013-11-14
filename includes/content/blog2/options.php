<?php
require_once('includes/content/blog2/functions.php');
//$options = $db->assoc('SELECT * FROM `'.$blog_options.'` WHERE `component_id`=?', $component_id);

$data     = $db->result('SELECT `additional_info` FROM `'.DB_COMPONENT_TABLE.'` WHERE `component_id`=?', $component_id);
$settings = unserialize($data);
if (!is_array($settings)) { $settings = array(); }

/*
$show_short_layout = unserialize($options['show_short_layout']);
if (!is_array($show_short_layout)) { $show_short_layout = array(); }

$image_settings = unserialize($options['image_settings']);*/


?>
<div class="ap_overflow">
<form method="post" action="<?=$body->url('includes/content/blog2/submit.php')?>" onsubmit="Blog2_UpdateOptions(this); return false" id="blog_options_form">
<input type="hidden" name="component_id" value="<?=$component_id?>" />
<input type="hidden" name="page_action" value="blog_settings" />
<h3 class="blog_choice">Blog Options</h3>
<table border="0" cellpadding="2" cellspacing="1" class="admin_list">
<tr class="a">
	<td class="bold" width="170">Show RSS Link?</td>
	<td>
		<select name="settings[show_rss]">
			<option value="0" <?=($settings['show_rss']==0)? 'selected="selected"' :''?>>No</option>
			<option value="1" <?=($settings['show_rss']==1)? 'selected="selected"' :''?>>Yes - Top</option>
			<option value="2" <?=($settings['show_rss']==2)? 'selected="selected"' :''?>>Yes - Bottom</option>
		</select>
	</td>
</tr>
<tr class="b">
	<td class="bold">Display Bottom Nav</td>
	<td>
		<select name="settings[show_bottom_nav]">
			<option value="0" <?=($settings['show_bottom_nav']==0)? 'selected="selected"' :''?>>No</option>
			<option value="1" <?=($settings['show_bottom_nav']==1)? 'selected="selected"' :''?>>Yes</option>
		</select>
	</td>
</tr>
<tr class="a">
	<td colspan="2">
		<h3>Layout Settings</h3>
		<p>You can use the following variables in your layout, hover over a variable for more info</p>
		<?=Blog2_GetLayoutDescHTML()?>
	</td>
</tr>
<tr class="b">
	<td class="bold">Full Layout<br />
		<button onclick="Blog2_LoadDefaultLayout('full_layout'); return false">Load Default</button>
	</td>
	<td>
		<textarea name="settings[full_layout]" id="full_layout" class="ap_textarea_lg"><?=htmlspecialchars($settings['full_layout'])?></textarea><br />
	</td>
</tr>
<tr class="a">
	<td class="bold">Short Layout<br />
		<button onclick="Blog2_LoadDefaultLayout('short_layout'); return false">Load Default</button>
	</td>
	<td>
		<textarea name="settings[short_layout]" id="short_layout" class="ap_textarea_lg"><?=htmlspecialchars($settings['short_layout'])?></textarea><br />
	</td>
</tr>
<tr class="b">
	<td class="bold">Section Layout</td>
	<td>
		<table border="0" cellpadding="2" cellspacing="1" class="blog_section_settings">
		<tr class="a">
			<td>Main Page</td>
			<td><?=Blog2_LayoutSection('main', $settings['section_layout']['main'])?></td>
			<td># entries: <input size="2" type="text" name="settings[section_show][main]" value="<?=$settings['section_show']['main']?>" /></td>
			<td><?=Blog2_DisplayOption('main', $settings['display_mode']['main'])?></td>
		</tr>
		<tr class="b">
			<td>Archives</td>
			<td><?=Blog2_LayoutSection('archives', $settings['section_layout']['archives'])?></td>
			<td># entries: <input size="2" type="text" name="settings[section_show][archives]" value="<?=$settings['section_show']['archives']?>" /></td>
			<td><?=Blog2_DisplayOption('archives', $settings['display_mode']['archives'])?></td>
		</tr>
		<tr class="a">
			<td>Tags</td>
			<td><?=Blog2_LayoutSection('tags', $settings['section_layout']['tags'])?></td>
			<td># entries: <input size="2" type="text" name="settings[section_show][tags]" value="<?=$settings['section_show']['tags']?>" /></td>
			<td><?=Blog2_DisplayOption('tags', $settings['display_mode']['tags'])?></td>
		</tr>
		<tr class="b">
			<td>Categories</td>
			<td><?=Blog2_LayoutSection('categories', $settings['section_layout']['categories'])?></td>
			<td># entries: <input size="2" type="text" name="settings[section_show][categories]" value="<?=$settings['section_show']['categories']?>" /></td>
			<td><?=Blog2_DisplayOption('categories', $settings['display_mode']['categories'])?></td>
		</tr>
		<tr class="a">
			<td>Author</td>
			<td><?=Blog2_LayoutSection('author', $settings['section_layout']['author'])?></td>
			<td># entries: <input size="2" type="text" name="settings[section_show][author]" value="<?=$settings['section_show']['author']?>" /></td>
			<td><?=Blog2_DisplayOption('author', $settings['display_mode']['author'])?></td>
		</tr>
		<tr class="b">
			<td>Search</td>
			<td><?=Blog2_LayoutSection('search', $settings['section_layout']['search'])?></td>
			<td># entries: <input size="2" type="text" name="settings[section_show][search]" value="<?=$settings['section_show']['search']?>" /></td>
			<td><?=Blog2_DisplayOption('search', $settings['display_mode']['search'])?></td>
		</tr>
		<tr class="a">
			<td>Yearly</td>
			<td><?=Blog2_LayoutSection('yearly', $settings['section_layout']['yearly'])?></td>
			<td># entries: <input size="2" type="text" name="settings[section_show][yearly]" value="<?=$settings['section_show']['yearly']?>" /></td>
			<td><?=Blog2_DisplayOption('yearly', $settings['display_mode']['yearly'])?></td>
		</tr>
		</table>
	</td>
</tr>
<tr class="a">
	<td colspan="2">
		<h3>Comment Settings</h3>
		<p>You can use the following variables in your layout</p>
		<ul class="variable_list">
			<li>{NAME}
				<div class="variable_tooltip">Name of the poster</div></li>
			<li>{DATE,date flags}
				<div class="variable_tooltip">Date of the comment, displayed as date flags<br />ex: {DATE,F, j Y} would display something like November 28, <?=date('Y')?></div></li>
			<li>{EMAIL}
				<div class="variable_tooltip">E-mail address of the poster, available as admin only</div></li>
			<li>{MESSAGE}
				<div class="variable_tooltip">Message text</div></li>
			<li>{REPLY}
				<div class="variable_tooltip">Link for commenter to reply to a comment</div></li>
		</ul>
	</td>
</tr>
<tr class="b">
	<td class="bold">Comment Layout<br />
		<button onclick="Blog2_LoadDefaultLayout('comment_layout'); return false">Load Default</button>
	</td>
	<td>
		<textarea name="settings[comment_layout]" id="comment_layout" class="ap_textarea_lg"><?=$settings['comment_layout']?></textarea><br />
	</td>
</tr>
<tr class="a">
	<td class="bold">Comments Enabled?</td>
	<td>
		<select name="settings[allow_comments]">
			<option value="0" <?=($settings['allow_comments']==0)? 'selected="selected"' :''?>>No</option>
			<option value="1" <?=($settings['allow_comments']==1)? 'selected="selected"' :''?>>Yes - Automatically Approve</option>
			<option value="2" <?=($settings['allow_comments']==2)? 'selected="selected"' :''?>>Yes - Moderation Required</option>
		</select>
	</td>
</tr>
<tr class="b">
	<td class="bold">Moderator E-mail Address</td>
	<td>
		<input type="text" name="settings[moderator_address]" class="ap_text" value="<?=$settings['moderator_address']?>" />
	</td>
</tr>
<tr class="a">
	<td class="bold">Use Livefyre for comments</td>
	<td>
		<input type="hidden" name="settings[use_livefyre]" value="0" />
		<input type="checkbox" name="settings[use_livefyre]" value="1" <?=($settings['use_livefyre'] == 1) ? 'checked="checked"' : ''?> />
	</td>
</tr>
<tr class="b">
	<td class="bold">Livefyre Site ID</td>
	<td>
		<input type="text" name="settings[lf_siteid]" class="ap_text" value="<?=$settings['lf_siteid']?>" />
	</td>
</tr>
</table>

<input class="co_button co_button1" type="submit" name="submitbtn" value="Update" />
</form>
</div>