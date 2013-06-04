<?php
chdir('../../../');
require_once('core.php');
require_once('includes/content/blog2/functions.php');

if ( (!defined('USER_ACCESS')) or (USER_ACCESS < 3) )
{
	exit();
}

$blog_options    = DB_PREFIX . 'pico_blog_options';
$blog_entries    = DB_PREFIX . 'pico_blog_entries';
$blog_categories = DB_PREFIX . 'pico_blog_categories';
$blog_comments   = DB_PREFIX . 'pico_blog_comments';

$entry_id = $_GET['id'];
$entry_info = $db->assoc('SELECT * FROM `'.$blog_entries.'` WHERE `post_id`=?', $entry_id);

/*
$tags = unserialize($entry_info['tags']);
if (!is_array($tags)) { $tags = array(); }

$saved_tags = $tags;

$tags = implode(', ', $tags);*/




$pcheck = ($entry_info['published'] == 1) ? 'checked="checked"' : '';
$ccheck = ($entry_info['allow_comments'] == 1) ? 'checked="checked"' : '';
$component_id = $entry_info['component_id'];

$save_table = <<<HTML
<table border="0" cellpadding="0" cellspacing="0">
<tr>
	<td style="width: 450px"><div id="blog_status"></div></td>
	<td>
		<input type="checkbox" name="published_front" id="publish1" value="1" $pcheck onclick="Blog2_Publish(this) "/> Publish 
		<button onclick="Blog2_Save(); return false">Save</button>
		<button onclick="Blog2_Home(); return false">Back</button>
	</td>
</tr>
</table>
HTML;

$save_table2 = <<<HTML
<table border="0" cellpadding="0" cellspacing="0">
<tr>
	<td style="width: 450px"><div id="blog_status2"></div></td>
	<td>
		<button onclick="Blog2_Save(); return false">Save</button>
		<button onclick="Blog2_Home(); return false">Back</button>
	</td>
</tr>
</table>
HTML;

?>
<table border="0" cellpadding="0" cellspacing="0" class="blog_tablist">
<tr>
	<td><div class="tab_button" onclick="Blog2_TabActivate('blog_content')">Content</div></td>
	<td><div class="tab_button" onclick="Blog2_TabActivate('blog_options')">Options</div></td>
	<td><div class="tab_button" onclick="Blog2_TabActivate('blog_saves')">History</div></td>
	<td><div class="tab_button" onclick="Blog2_TabActivate('blog_image')">Image</div></td>
	<td><div class="tab_button" onclick="Blog2_TabActivate('blog_comments')">Comments</div></td>
</tr>
</table>

<form method="post" id="blog_content_form" style="height: auto" action="<?=$body->url('includes/content/blog2/submit.php')?>" onsubmit="return false">
<input type="hidden" name="page_action" value="edit_story" />
<input type="hidden" name="entry_id" value="<?=$entry_id?>" />
<input type="hidden" name="blog_entry_text" value="" />
<input type="hidden" name="component_id" id="component_id" value="<?=$component_id?>" />

<div class="tabbed_content" style="display: block" id="blog_content">
	<textarea name="blog_story" id="blog_story"><?=$entry_info['post']?></textarea>
	<?=$save_table?>
</div>
<div class="tabbed_content" id="blog_options">
	<div class="ap_overflow" style="height: 425px">
		<h3 class="blog_choice">Options</h3>
		
		<table border="0" cellpadding="2" cellspacing="1" class="blog_options admin_list">
		<tr class="a" style="width: 120px">
			<td>Title</td>
			<td colspan="2"><input type="text" class="ap_text" name="title" value="<?=htmlspecialchars($entry_info['title'])?>" /></td>
		</tr>
		<tr class="b">
			<td>Secondary Title</td>
			<td colspan="2"><input type="text" class="ap_text" name="by_line" value="<?=htmlspecialchars($entry_info['by_line'])?>" /></td>
		</tr>
		<tr class="a">
			<td>Author</td>
			<td colspan="2"><input type="text" class="ap_text" name="author" value="<?=htmlspecialchars($entry_info['author'])?>" /></td>
		</tr>
		<tr class="b">
			<td>Image Caption</td>
			<td colspan="2"><input type="text" class="ap_text" name="image_caption" value="<?=htmlspecialchars($entry_info['image_caption'])?>" /></td>
		</tr>
		<tr class="a">
			<td>Story Date</td>
			<td colspan="2"><?=Blog2_DateDropdowns('date', $entry_info['date'])?></td>
		</tr>
		<tr class="b">
			<td>Do not post until...</td>
			<td colspan="2"><?=Blog2_DateDropdowns('scheduled_date', $entry_info['scheduled_date'])?></td>
		</tr>
		<tr class="a">
			<td>Tags</td>
			<td colspan="2">
				<?=Blog2_GetTagForm($component_id, $entry_id)?>
			</td>
		</tr>
		<tr class="b">
			<td>Category</td>
			<td width="10">
				<div id="blog_category"><?=Blog2_get_category_drop('category', $component_id, $entry_info['category'])?></div>
			</td>
			<td>
				<img src="<?=$body->url('includes/icons/plus.png')?>" title="Add Category" class="click icon" onclick="Blog2_AddCategory()" />
				<img src="<?=$body->url('includes/icons/edit.png')?>" title="Rename Category" class="click icon" onclick="Blog2_RenameCategory()" />
				<img src="<?=$body->url('includes/icons/delete.png')?>" title="Delete Category" class="click icon" onclick="Blog2_DeleteCategory()" />
			</td>
		</tr>
		<tr class="a">
			<td>Published</td>
			<td colspan="2"><input type="checkbox" name="published" id="publish2" value="1" <?=$pcheck?> onclick="Blog2_Publish(this)" /></td>
		</tr>
		<tr class="b">
			<td>Allow Comments</td>
			<td colspan="2"><input type="checkbox" name="allow_comments" value="1" <?=$ccheck?> /></td>
		</tr>
		<tr class="a">
			<td>Related post #1</td>
			<td colspan="2"><?=Blog2_GetPostsDropdown($component_id, 'related1', $entry_info['related1'])?></td>
		</tr>
		<tr class="b">
			<td>Related post #2</td>
			<td colspan="2"><?=Blog2_GetPostsDropdown($component_id, 'related2', $entry_info['related2'])?></td>
		</tr>
		<tr class="a">
			<td>Related post #3</td>
			<td colspan="2"><?=Blog2_GetPostsDropdown($component_id, 'related3', $entry_info['related3'])?></td>
		</tr>
		</table>
		<?=$save_table2?>
		</form>
	</div>
</div>
<div class="tabbed_content" id="blog_saves">
	<div class="ap_overflow" style="height: 425px">
		<div id="blog_restore">
		<?php
		include('includes/content/blog2/blog_restore.php');
		?>
		</div>
	</div>
</div>
<div class="tabbed_content" id="blog_image">
	<div id="blog_image">
		<?php
		include('includes/content/blog2/blog_image.php');
		?>
	</div>
</div>
<div class="tabbed_content" id="blog_comments">
	<div id="blog_admin_comments">
		<?php
		include('includes/content/blog2/blog_comments.php');
		?>
	</div>
</div>