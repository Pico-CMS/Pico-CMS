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

$author_dd = '<select onchange="Blog2_InsertAuthor(this)" style="max-width: 400px"><option value="">Find Author...</option>';
$authors = $db->force_multi_assoc('SELECT distinct(`author`) as `author` FROM `'.$blog_entries.'` WHERE `component_id`=? ORDER BY `author` ASC', 
	$component_id
);

if (is_array($authors))
{
	foreach ($authors as $a)
	{
		$author = trim($a['author']);
		if (strlen($author) > 0)
		{
			$author_dd .= '<option value="'.htmlspecialchars($author).'">'.htmlspecialchars($author).'</option>';
		}
	}
}

$author_dd .= '</select>';

$save_form = <<<HTML
<button onclick="Blog2_Preview(); return false" class="co_button co_button4">Preview</button>
<button onclick="Blog2_Save(0); return false" class="co_button co_button3">Save</button>
<button onclick="Blog2_Save(1); return false" class="co_button co_button2">Save &amp; Publish</button>
<button onclick="Blog2_Home(); return false" class="co_button co_button1">Back to All Posts</button>
HTML;

$entry_categories = array();

$entry_categories_results = $db->force_multi_assoc('SELECT * FROM `'.$blog_category_links.'` WHERE `post_id`=?', $entry_info['post_id']);
if (is_array($entry_categories_results))
{
	foreach ($entry_categories_results as $result)
	{
		$category_id   = $result['category_id'];
		$category_name = $db->result('SELECT `title` FROM `'.$blog_categories.'` WHERE `category_id`=?', $category_id);
		$entry_categories[$category_id] = $category_name;
	}
}

$entry_category_ids = array_keys($entry_categories);
$current_categories = (sizeof($entry_categories) > 0) ? implode(', ', $entry_categories) : 'None Selected';

?>
<!--table border="0" cellpadding="0" cellspacing="0" class="blog_tablist">
<tr>
	<td><div class="tab_button" onclick="Blog2_TabActivate('blog_content')">Content</div></td>
	<td><div class="tab_button" onclick="Blog2_TabActivate('blog_options')">Options</div></td>
	<td><div class="tab_button" onclick="Blog2_TabActivate('blog_saves')">History</div></td>
	<td><div class="tab_button" onclick="Blog2_TabActivate('blog_image')">Image</div></td>
	<td><div class="tab_button" onclick="Blog2_TabActivate('blog_comments')">Comments</div></td>
</tr>
</table-->

<ul id="blog_edit_sidebar">
	<li id="bes_1" class="click blog_sidebar_item active" onclick="Blog2_TabActivate(this, 'blog_content')">Edit Post</li>
	<li id="bes_2" class="click blog_sidebar_item" onclick="Blog2_TabActivate(this, 'blog_options')">Advanced</li>
	<li id="bes_3" class="click blog_sidebar_item" onclick="Blog2_TabActivate(this, 'blog_saves')">Saves</li>
	<li id="bes_4" class="click blog_sidebar_item" onclick="Blog2_TabActivate(this, 'blog_image')">Image</li>
	<li id="bes_5" class="click blog_sidebar_item" onclick="Blog2_TabActivate(this, 'blog_comments')">Comments</li>
</ul>

<div id="blog_status"></div>

<form method="post" id="blog_content_form" style="height: auto" action="<?=$body->url('includes/content/blog2/submit.php')?>" onsubmit="return false">
<input type="hidden" name="page_action" value="edit_story" />
<input type="hidden" name="entry_id" value="<?=$entry_id?>" />
<input type="hidden" name="blog_entry_text" value="" />
<input type="hidden" name="component_id" id="component_id" value="<?=$component_id?>" />

<div class="tabbed_content" style="display: block" id="blog_content">
	<textarea name="blog_story" id="blog_story"><?=$entry_info['post']?></textarea>
</div>
<div class="tabbed_content" id="blog_options">
	<div class="ap_overflow" style="height: 425px">
		<h3 class="blog_choice">Advanced</h3>
		
		<table border="0" cellpadding="2" cellspacing="1" class="blog_options admin_list">
		<tr class="a" style="width: 120px">
			<td class="left">Title</td>
			<td colspan="2"><input type="text" class="ap_text" name="title" value="<?=htmlspecialchars($entry_info['title'])?>" /></td>
		</tr>
		<tr class="b">
			<td class="left">Secondary Title</td>
			<td colspan="2"><input type="text" class="ap_text" name="by_line" value="<?=htmlspecialchars($entry_info['by_line'])?>" /></td>
		</tr>
		<tr class="a">
			<td class="left">Author</td>
			<td colspan="2">
				<input type="text" class="ap_text" id="blog_author" name="author" value="<?=htmlspecialchars($entry_info['author'])?>" /><br />
				Find Author <?=$author_dd?>
			</td>
		</tr>
		<tr class="b">
			<td class="left">Story Date</td>
			<td colspan="2">
				<?=Blog2_DateDropdowns('date', $entry_info['date'])?><br />
				Date which displays with the post.
			</td>
		</tr>
		<tr class="a">
			<td class="left">Do not post until...</td>
			<td colspan="2">
				<?=Blog2_DateDropdowns('scheduled_date', $entry_info['scheduled_date'])?><br />
				Date on which the post will appear on your blog.
			</td>
		</tr>
		<tr class="b">
			<td class="left">Tags</td>
			<td colspan="2">
				<?=Blog2_GetTagForm($component_id, $entry_id)?>
			</td>
		</tr>
		<tr class="a">
			<td class="left">Categories</td>
			<td colspan="2">
				<div id="blog_category_list"><?=$current_categories?></div>
				<input type="hidden" name="categories" value="<?=implode(',', $entry_category_ids)?>" id="blog_entry_categories" />
				<div class="click" onclick="Blog2_ChooseCategories(<?=$component_id?>)">
				<img src="<?=$body->url('includes/icons/edit.png')?>" title="Choose Categories" class="click icon" /> Choose Categories</div>
				<div class="blog_subbox" style="display: none" id="blog_category_box"></div>
			</td>
			<!--td>
				<img src="<?=$body->url('includes/icons/plus.png')?>" title="Add Category" class="click icon" onclick="Blog2_AddCategory()" />
				<img src="<?=$body->url('includes/icons/edit.png')?>" title="Rename Category" class="click icon" onclick="Blog2_RenameCategory()" />
				<img src="<?=$body->url('includes/icons/delete.png')?>" title="Delete Category" class="click icon" onclick="Blog2_DeleteCategory()" />
			</td-->
		</tr>
		<tr class="b">
			<td class="left">Published</td>
			<td colspan="2"><input type="checkbox" name="published" id="publish2" value="1" <?=$pcheck?> onclick="Blog2_Publish(this)" /></td>
		</tr>
		<tr class="a">
			<td class="left">Allow Comments</td>
			<td colspan="2"><input type="checkbox" name="allow_comments" value="1" <?=$ccheck?> /></td>
		</tr>
		<tr class="b">
			<td class="left">Related post #1</td>
			<td colspan="2"><table><tr><td><?=Blog2_GetPostsDropdown($component_id, 'related1', $entry_info['related1'])?></td><td><button onclick="Blog2_Suggest(1)">Suggest</button></td></tr></table></td>
		</tr>
		<tr class="a">
			<td class="left">Related post #2</td>
			<td colspan="2"><table><tr><td><?=Blog2_GetPostsDropdown($component_id, 'related2', $entry_info['related2'])?></td><td><button onclick="Blog2_Suggest(2)">Suggest</button></td></tr></table></td>
		</tr>
		<tr class="b">
			<td class="left">Related post #3</td>
			<td colspan="2"><table><tr><td><?=Blog2_GetPostsDropdown($component_id, 'related3', $entry_info['related3'])?></td><td><button onclick="Blog2_Suggest(3)">Suggest</button></td></tr></table></td>
		</tr>
		</table>
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
<?=$save_form?>

</form>