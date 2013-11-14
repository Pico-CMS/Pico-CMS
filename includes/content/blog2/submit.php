<?php
chdir('../../../');
require_once('core.php');
if (USER_ACCESS < 3) { exit(); }

require_once('includes/content/blog2/functions.php');

$action = $_REQUEST['page_action'];

if ($action == 'get_categories')
{
	$component_id = $_GET['component_id'];
	$form_url     = $body->url('includes/content/blog2/submit.php');

	$categories = $db->force_multi_assoc('SELECT * FROM `'.$blog_categories.'` WHERE `component_id`=? ORDER BY `title` ASC', $component_id);
	$cat_html   = array();

	$counter = 0;

	$cat_drop = '<option value="">...</option>';

	if (is_array($categories))
	{
		foreach ($categories as $category)
		{
			$class = ($counter % 2 == 0) ? 'a' : 'b';
			$counter++;
			$cat_html[] = '<div class="entry '.$class.'" category_id="'.$category['category_id'].'" onclick="Blog2_SelectCategory(this)">'.$category['title'].'</div>';

			$cat_drop .= '<option value="'.$category['category_id'].'">'.$category['title'].'</option>';
		}
	}

	if (sizeof($cat_html) > 22)
	{
		// we will need to split
		$half = ceil(sizeof($cat_html) / 2);
		if ($half < 22) { $half = 22; }

		$col1 = implode('', array_slice($cat_html, 0, $half));
		$col2 = implode('', array_slice($cat_html, $half));
	}
	else
	{
		$col1 = implode('', $cat_html);
		$col2 = '';
	}

	echo <<<HTML
<div class="col center">
	<h3>Add New Category</h3>
	<input type="text" name="category" id="new_category" value="" /><br />
	<button onclick="Blog2_AddNewCategory($component_id)">Add</button>

	<hr />

	<h3>Rename Category</h3>
	<select name="category" id="rename_category_id" onchange="Blog2_LoadRename(this)">$cat_drop</select><br />
	<input type="text" name="rename_category" id="rename_category" value="" /><br />
	
	<button onclick="Blog2_RenameCategory($component_id)">Rename</button>

	<hr />

	<h3>Delete Category</h3>
	<select id="delete_category_id" name="category">$cat_drop</select><br />
	<button onclick="Blog2_DeleteCategory($component_id)">Delete</button>

	<div style="margin-top: 50px;">
	<button onclick="Blog2_CloseCategories()">Close</button>
	</div>
</div>
<div class="col">$col1</div>
<div class="col">$col2</div>
<div class="clear"></div>

HTML;

	exit();
}

if ($action == 'blog_settings')
{
	$component_id = $_POST['component_id'];
	$settings     = Pico_Cleanse($_POST['settings']);
	$db->run('UPDATE `'.DB_COMPONENT_TABLE.'` SET `additional_info`=? WHERE `component_id`=?', serialize($settings), $component_id);
}

if ($action == 'start_post')
{
	$title = trim(stripslashes($_POST['title']));
	$date  = trim(stripslashes($_POST['date']));
	$component_id = $_POST['component_id'];
	
	$check = $db->result('SELECT count(1) FROM `'.$blog_entries.'` WHERE `alias` =? AND `component_id`=?', PageNameToAlias($title), $component_id);
	
	if ($check > 0)
	{
		exit('1|That title is already in use, please change your title and try again');
	}
	elseif (strlen($title) == 0)
	{
		exit('1|You must specify a unique title');
	}
	else
	{
		$date_ts = strtotime($date);
		$alias   = PageNameToAlias($title);
		
		$new_story = $db->insert('INSERT INTO `'.$blog_entries.'` (`component_id`, `date`, `title`, `tags`, `post`, `alias`, `published`) VALUES (?,?,?,?,?,?,?)', 
			$component_id, $date_ts, $title, '', '', $alias, 0
		);

		if (!is_numeric($new_story)) {
			echo '1|Error adding blog post: ' . $db->error;
			exit();
		}
		
		exit('0|'.$new_story);
	}
}
elseif ($action == 'draft')
{
	// prepare to autosave
	$blog_entry_text = trim(stripslashes($_POST['blog_entry_text']));
	
	$entry_id = $_POST['entry_id'];
	
	//$auto_saved_post = $db->result('SELECT `auto_saved_post` FROM `'.$blog_entries.'` WHERE `post_id`=?', $entry_id);
	$current_post    = $db->result('SELECT `post` FROM `'.$blog_entries.'` WHERE `post_id`=?', $entry_id);
	$difference      = abs(strlen($blog_entry_text) - strlen($current_post));
	
	if ($difference >= 100)
	{
		// auto save
		$db->run('UPDATE `'.$blog_entries.'` SET `auto_saved_post`=?, `auto_saved_date`=? WHERE `post_id`=?',
			$blog_entry_text, time(), $entry_id
		);
		
		echo 'Post Autosaved';
	}
}
elseif ( ($action == 'edit_story') or ($action == 'publish') or ($action == 'preview' ) )
{
	$component_id   = $_POST['component_id'];
	$date           = Blog2_TimeStampFromHTML($_POST['date']);
	$scheduled_date = Blog2_TimeStampFromHTML($_POST['scheduled_date']);
	$title          = trim(stripslashes($_POST['title']));
	$story          = stripslashes($_POST['blog_entry_text']);
	$entry_id       = $_POST['entry_id'];
	$published      = ($_POST['published'] == 1) ? 1 : 0;
	$allow_comments = ($_POST['allow_comments'] == 1) ? 1 : 0;
	$author         = trim(stripslashes($_POST['author']));
	$by_line        = trim(stripslashes($_POST['by_line']));
	$caption        = trim(stripslashes($_POST['image_caption']));
	$related1       = $_POST['related1'];
	$related2       = $_POST['related2'];
	$related3       = $_POST['related3'];

	if ($action == 'preview') 
	{ 
		$story_image = $db->result('SELECT `story_image` FROM `'.$blog_entries.'` WHERE `post_id`=?', $entry_id);
		$entry_id  = -1 * $entry_id; 
		$published = 0;

		$db->run('DELETE FROM `'.$blog_entries.'` WHERE `post_id` < ?', 0);
		$db->run('INSERT INTO `'.$blog_entries.'` (`post_id`, `story_image`, `component_id`, `date`, `title`, `tags`, `post`, `alias`, `published`) VALUES (?,?,?,?,?,?,?,?,?)', 
			$entry_id, $story_image, $component_id, time(), $title, '', '', '', 1
		);
	}
	elseif ($action == 'publish')
	{
		$published = 1;
	}
	
	$tags = explode(',', $_POST['tags']);
	if (sizeof($tags) > 0)
	{
		foreach ($tags as $key=>$val)
		{
			$tags[$key] = stripslashes(trim($val));
		}
	}
	$tags = serialize($tags);
	
	$current_info = $db->assoc('SELECT * FROM `'.$blog_entries.'` WHERE `post_id`=?', $entry_id);
	
	// save post
	
	$last_saved_post = $current_info['post'];
	$last_saved_date = $current_info['saved_date'];
	
	$allow_comments  = (isset($_POST['allow_comments'])) ? $_POST['allow_comments'] : 0;
	
	$update_story = $db->run('UPDATE `'.$blog_entries.'` SET `date`=?, `scheduled_date`=?, `saved_date`=?, `tags`=?, `post`=?, `published`=?, `allow_comments`=?, `last_saved_post`=?, `last_saved_date`=?, `author`=?, `by_line`=?, `image_caption`=?, `related1`=?, `related2`=?, `related3`=? WHERE `post_id`=?',
		$date, $scheduled_date, time(), $tags, $story, $published, $allow_comments, $last_saved_post, $last_saved_date, $author, $by_line, $caption, $related1, $related2, $related3, $entry_id
	);

	// update categories

	$categories = trim($_POST['categories'], ', ');
	$category_ids = explode(',', $categories);

	$db->run('DELETE FROM `'.$blog_category_links.'` WHERE `post_id`=?', $entry_id);

	if (sizeof($category_ids) > 0)
	{
		foreach ($category_ids as $category_id)
		{
			$check = $db->result('SELECT count(1) FROM `'.$blog_categories.'` WHERE `category_id`=?', $category_id);

			if ($check != 0)
			{
				$db->run('INSERT INTO `'.$blog_category_links.'` (`post_id`, `category_id`) VALUES (?,?)',
					$entry_id, $category_id
				);
			}
		}
	}
	
	// change title (if needed)
	
	if ($current_info['title'] != $title)
	{
		$check = $db->result('SELECT count(1) FROM `'.$blog_entries.'` WHERE `alias` =? AND `component_id`=?', PageNameToAlias($title), $component_id);
		if ( ($check > 0) or (strlen($title) == 0) )
		{
			//if ($check === FALSE) { echo $db->error; exit(); }
			echo 'Post saved, but the title you entered is invalid.';
			exit();
		}
		else
		{
			$db->run('UPDATE `'.$blog_entries.'` SET `title`=?, `alias`=? WHERE `post_id`=?',
				$title, PageNameToAlias($title), $entry_id
			);
		}
	}
	
	echo 'Post saved.';
	exit();
}

if ($action == 'delete_entry')
{
	$entry_id = $_GET['entry_id'];
	if (is_numeric($entry_id))
	{
		$db->run('DELETE FROM `'.$blog_entries.'` WHERE `post_id`=?', $entry_id);
	}
	exit();
}

if ($action == 'add_category')
{
	$component_id = $_REQUEST['component_id'];
	
	$title = stripslashes($_REQUEST['category']);
	$alias = PageNameToAlias($title);
	
	$check = $db->result('SELECT count(1) FROM `'.$blog_categories.'` WHERE `alias`=? AND `component_id`=?', $alias, $component_id);
	if ($check == 1)
	{
		echo 'That category already exists';
		exit();
	}
	elseif (strlen($alias) == 0)
	{
		echo 'Invalid category name';
		exit();
	}
	else
	{
		$new_category = $db->insert('INSERT INTO `'.$blog_categories.'` (`title`, `component_id`, `alias`) VALUES (?,?,?)', 
			$title, $component_id, $alias
		);
	}
	
	exit();
}

if ($action == 'reload_category')
{
	$component_id = $_GET['component_id'];
	echo Blog2_get_category_drop('category', $component_id, '');
}

if ($action == 'edit_category')
{
	$category_id  = $_GET['category_id'];
	$new_name     = urldecode($_GET['category']);
	$new_alias    = PageNameToAlias($new_name);
	
	$old_alias = $db->result('SELECT `alias` FROM `'.$blog_categories.'` WHERE `category_id`=?', $category_id);
	$check = $db->result('SELECT count(1) FROM `'.$blog_categories.'` WHERE `alias`=?', $new_alias);
	
	if ( ($check == 1) and ($old_alias != $new_alias) )
	{
		echo 'That category already exists';
		exit();
	}
	elseif (strlen($new_alias) == 0)
	{
		echo 'Invalid category name';
		exit();
	}
	else
	{
		$db->run('UPDATE `'.$blog_categories.'` SET `title`=?, `alias`=? WHERE `category_id`=?', $new_name, $new_alias, $category_id);
	}
	
	exit();
}

if ($action == 'delete_category')
{
	$category_id  = $_GET['category_id'];
	$component_id = $db->result('SELECT `component_id` FROM `'.$blog_categories.'` WHERE `category_id`=?', $category_id);
	
	$db->run('DELETE FROM `'.$blog_categories.'` WHERE `category_id`=?', $category_id);
	$db->run('DELETE FROM `'.$blog_category_links.'` WHERE `category_id`=?', $category_id);
	exit();
}

if ($action == 'blog_image')
{
	$filename = urldecode($_GET['filename']);
	$entry_id = $_GET['entry_id'];

	// added for build 1037+: uses new upload methodology
	
	$saved_image   = $db->result('SELECT `story_image` FROM `'.$blog_entries.'` WHERE `post_id`=?', $entry_id);
	$base          = 'includes/tmp/';
	$uploaded_file = $base . $filename;
	
	if (!is_file($uploaded_file)) { exit ('File not found: ' . $uploaded_file); }
	
	$storage_dir  = 'blog/images/' . $entry_id .'/';
	$storage_file = 'includes/storage/' . $storage_dir . $filename;
	
	$writable = Pico_StorageDir($storage_dir); // makes sure the folder exists and is writable
	if (!$writable) { exit('Unable to write to blog/images folder'); }
	
	rename($uploaded_file, $storage_file);
	$db->run('UPDATE `'.$blog_entries.'` SET `story_image`=? WHERE `post_id`=?', $filename, $entry_id);
	
	$old_image = $base . $storage_dir . $saved_image;
	if ( (is_file($old_image)) and ($saved_image != $filename) ) { @unlink($old_image); }
	exit();
}

if ($action == 'delete_blog_image')
{
	$entry_id = $_GET['entry_id'];
	
	$saved_image  = $db->result('SELECT `story_image` FROM `'.$blog_entries.'` WHERE `post_id`=?', $entry_id);
	$storage      = 'includes/content/blog2/storage/';
	$full_file    = $storage . $entry_id . '/' .$saved_image;
	$preview_file = $storage . $entry_id . '/' . md5('preview_'.$saved_image) . '.png';
	
	if (is_file($full_file)) { if (Pico_IsWritable($full_file, true)) { unlink($full_file); } }
	if (is_file($preview_file)) { if (Pico_IsWritable($preview_file, true)) { unlink($preview_file); } }
	
	$db->run('UPDATE `'.$blog_entries.'` SET `story_image`=? WHERE `post_id`=?', '', $entry_id);
	exit();
}

if ($action == 'delete_comment')
{
	$comment_id = $_GET['comment_id'];
	$db->run('DELETE FROM `'.$blog_comments.'` WHERE `comment_id`=? LIMIT 1', $comment_id);
	exit();
}

if ($action == 'approve_comment')
{
	$comment_id = $_GET['comment_id'];
	$db->run('UPDATE `'.$blog_comments.'` SET `active`=? WHERE `comment_id`=?', 1, $comment_id);
	exit();
}

if ($action == 'load_layout')
{
	$layout = $_GET['layout'];
	if ($layout == 'full_layout')
	{
		$output = <<<HTML
<h2>{LINK,{TITLE}}</h2>
<div class="date">{DATE,F j, Y}</div>
<div class="post">{STORY}</div>
<div class="info">Posted in: {CATEGORIES}{if:NUM_TAGS > 0} - Tagged ({NUM_TAGS}): {TAGS}{/if}</div>
{if:RELATED}<div class="related">Related Posts: {RELATED}</div>{/if}
{if:SHARETHIS}<p>{SHARETHIS}</p>{/if}
{if:COMMENTS_ENABLED}{COMMENTS}{/if}
HTML;
	}
	elseif ($layout == 'short_layout')
	{
		$output = <<<HTML
<h2>{LINK,{TITLE}}</h2>
{if:IMAGE}<div class="image">{IMAGE,240,180,pad}</div>{/if}
<div class="date">{DATE,F j, Y}</div>
<div class="post">{STORY,25}</div>
<div class="info">Posted in: {CATEGORY_LINK,{CATEGORY_NAME}}{if:NUM_TAGS > 0} - Tagged ({NUM_TAGS}): {TAGS}{/if}</div>
HTML;
	}
	if ($layout == 'comment_layout')
	{
		$output = <<<HTML
<div class="blog_comment">
<div class="info">Posted by {NAME} on {DATE,F j, Y} | {REPLY}</div>
<div class="message">{MESSAGE}</div>
</div>
HTML;
	}
	
	echo $output;
}

if ($action == 'suggest')
{
	$entry_id = $_GET['entry_id'];
	$result   = $_GET['result'];

	$points_per_tag  = 1;
	$points_per_word = 1;
	$points_per_cat  = 5;

	$points = array();

	$entry_info  = $db->assoc('SELECT * FROM `'.$blog_entries.'` WHERE `post_id`=?', $entry_id);
	$entry_words = explode(' ', strtolower(trim($entry_info['title'])));
	$entry_tags  = unserialize($entry_info['tags']);
	// go through all the other posts

	$other_posts = $db->force_multi_assoc('SELECT * FROM `'.$blog_entries.'` WHERE `post_id` !=? AND `component_id`=? AND `published`=1', $entry_id, $entry_info['component_id']);
	if (is_array($other_posts))
	{
		foreach($other_posts as $post)
		{
			$pt_counter = 0;
			$id = $post['post_id'];

			// get word points
			$post_words = explode(' ', strtolower(trim($post['title'])));
			if (is_array($post_words))
			{
				foreach ($post_words as $word)
				{
					if (in_array($word, $entry_words)) { $pt_counter += $points_per_word; }
				}
			}
			
			// get category points
			if ($post['category'] == $entry_info['category']) { $pt_counter += $points_per_cat; }

			// get tag points
			$post_tags = unserialize($post['tags']);
			if (is_array($post_tags))
			{
				foreach ($post_tags as $tag)
				{
					if (in_array($tag, $entry_tags)) { $pt_counter += $points_per_tag; }
				}
			}

			if ($pt_counter > 0)
			{
				$points[] = array(
					'id'  => $id,
					'pts' => $pt_counter
				);
			}
			
		}
	}

	usort($points, 'Blog2_PointSort');

	if (isset($points[$result-1]))
	{
		echo $points[$result-1]['id'];
	}

	exit();
}

if ($action == 'preview')
{

}

?>