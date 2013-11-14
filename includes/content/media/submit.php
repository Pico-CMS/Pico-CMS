<?php
chdir('../../../');
require_once('core.php');
require_once('includes/content/media/functions.php');
if (USER_ACCESS < 3) { exit(); }

$media_files      = DB_PREFIX . 'pico_media_files';
$media_categories = DB_PREFIX . 'pico_media_categories';

$action = $_REQUEST['page_action'];

if ($action == 'install')
{
	$component_id  = $_POST['component_id'];
	$gallery_style = $_POST['gallery_style'];
	
	$gallery_options = array(
		'gallery_style' => $gallery_style
	);
	
	$db->run('UPDATE `'.DB_COMPONENT_TABLE.'` SET `additional_info`=? WHERE `component_id`=?', serialize($gallery_options), $component_id);
	echo $db->query;
	exit();
}

if ($action == 'add')
{
	$instance_id  = $_GET['instance_id'];
	$component_id = $_GET['component_id'];
	$category_id  = $_GET['category_id'];
	$source_file  = 'includes/tmp/' . urldecode($_GET['filename']);
	
	// get new position
	
	if ((file_exists($source_file)) and (Pico_StorageDir('includes/storage/media/source/')))
	{
		$gallery_config = gallery_get_base_options($component_id);
		if ($gallery_config['categories'] == TRUE)
		{
			$check = $db->result('SELECT `position` FROM `'.$media_files.'` WHERE `instance_id`=? AND `category_id`=? ORDER BY `position` DESC LIMIT 1', $instance_id, $category_id);
		}
		else
		{
			$check = $db->result('SELECT `position` FROM `'.$media_files.'` WHERE `instance_id`=? ORDER BY `position` DESC LIMIT 1', $instance_id);
		}
		
		$position     = ($check === FALSE) ? 0 : $check+1;
		$ext          = file_extension($source_file);
		$new_image_id = $db->insert('INSERT INTO `'.$media_files.'` (`instance_id`, `position`, `extension`, `category_id`) VALUES (?,?,?,?)', $instance_id, $position, $ext, $category_id);
		
		if ($new_image_id === FALSE) { exit(); }
		
		// images get converted to JPGs when they are uploaded
		
		$new_source_image = 'includes/storage/media/source/'.$new_image_id.'.'.$ext;
		rename($source_file, $new_source_image);
	}
	else
	{
		echo 'File not uploaded: ' . basename($source_file);
	}
	exit();
}

if ($action == 'delete')
{
	$image_id    = $_GET['image_id'];
	$instance_id = $_GET['instance_id'];
	$current_position = (int) $db->result('SELECT `position` FROM `'.$media_files.'` WHERE `file_id`=?', $image_id);
	$category_id      = (int) $db->result('SELECT `category_id` FROM `'.$media_files.'` WHERE `file_id`=?', $image_id);
	
	$db->run('DELETE FROM `'.$media_files.'` WHERE `file_id`=? LIMIT 1', $image_id);
	$db->run('UPDATE `'.$media_files.'` SET `position`=(`position`-1) WHERE `position`>? AND `instance_id`=? AND `category_id`=?', $current_position, $instance_id, $category_id);
	exit();
}

function getclass($name)
{
	$key = 'class_' . $name;
	
	if (isset($GLOBALS[$key]))
	{
		$GLOBALS[$key]++;
	}
	else
	{
		$GLOBALS[$key] = 0;
	}

	$counter = $GLOBALS[$key];
	$class = ($counter % 2 == 0) ? 'a' : 'b';
	return $class;
}

if ($action == 'edit')
{
	$image_id     = $_GET['image_id'];
	$component_id = $_GET['component_id'];
	$image_path   = $body->url('includes/content/media/files/'.$image_id . '_thumb.jpg');
	$image_info   = $db->assoc('SELECT * FROM `'.$media_files.'` WHERE `file_id`=?', $image_id);

	$data = $db->result('SELECT `additional_info` FROM `'.DB_COMPONENT_TABLE.'` WHERE `component_id`=?', $component_id);
	$gallery_options = unserialize($data);
	$config_file     = 'includes/content/media/galleries/'.$gallery_options['gallery_style'].'/config.php';
	include($config_file);
	
	// get possible categories
	$category_drop = '<select name="category">';
	//$category_drop = '<select name="category"><option value="0">None</option>';
	$categories = $db->force_multi_assoc('SELECT * FROM `'.$media_categories.'` WHERE `component_id`=?', $component_id);

	if ( (is_array($categories)) and (sizeof($categories) > 0) )
	{
		foreach ($categories as $category)
		{
			$name = $category['title'];
			$id   = $category['category_id'];
			$selected = ($image_info['category_id'] == $id) ? 'selected="selected"' : '';
			$category_drop .= '<option value="'.$id.'" '.$selected.'>'.$name.'</option>';
		}
	}
	$category_drop .= '</select>';
	
	$upload_path = $body->url('includes/content/media/upload.php');
	$uploader = new Uploader($upload_path, 'MG_CustomThumbnail', '', '.jpg, .png, .gif', 'Image Files (jpg/png/gif)', '000000', 'cccccc');
	
	if ($options['is_html'] == true)
	{
		// html box, make it pretty!
		$extra = '<input type="hidden" name="description" value="" />';
		$description = '<td colspan="2">Description<br /><textarea name="image_description" id="image_description">'.htmlspecialchars($image_info['description']).'</textarea></td>';
	}
	else
	{
		$extra = '';
		$description = '<td>Description</td><td><textarea name="description" class="large">'.htmlspecialchars($image_info['description']).'</textarea></td>';
	}
	
?>
<input type="hidden" id="category_id" value="<?=$image_info['category_id']?>" />
	<table border="0" cellpadding="2" cellspacing="1" class="admin_list">
	<tr class="b"><td valign="top">
		<h3>Preview</h3>
			<img src="<?=$image_path?>" />
	</td><td valign="top">
		<h3>Custom Thumbnails</h3>
		<?=$uploader->Output()?>
	</td><td width="150" style="text-align: center" valign="top">
		<h3>More Options</h3>
		<button onclick="MG_DeleteImage(<?=$image_id?>)">Delete</button> <button onclick="MG_ReloadImages()">Back</button>
	</td>
	</tr>
	</table>
	<form method="post" action="<?=$body->url('includes/content/media/submit.php')?>" style="height: auto" onsubmit="MG_Updatefile(this); return false" />
		<input type="hidden" name="file_id" id="image_id" value="<?=$image_id?>" />
		<input type="hidden" name="page_action" value="edit_image" />
		<?=$extra?>
		
		<table border="0" cellpadding="2" cellspacing="1" class="admin_list">
			<?= ($options['title']) ? '<tr class="'.getclass('mg_edit_table').'"><td>Title</td><td><input class="large" type="text" name="title" value="'.$image_info['title'].'" />' : '' ?>
			<?= ($options['description']) ? '<tr class="'.getclass('mg_edit_table').'">'.$description.'</tr>' : '' ?>
			<?= ($options['url']) ? '<tr class="'.getclass('mg_edit_table').'"><td>Link</td><td><input class="large" type="text" name="url" value="'.$image_info['url'].'" />' : '' ?>
			<?= ($options['categories']) ? '<tr class="'.getclass('mg_edit_table').'"><td>Category</td><td>'.$category_drop.'</td>' : '<input type="hidden" name="category" value="0" />' ?>
		</table>
		<input type="submit" name="submitbtn" value="Update Image" />
	</form>
	<div class="clear"></div>
<?php
	
	exit();
}

if ($action == 'edit_image')
{
	$file_id     = $_POST['file_id'];
	$description = stripslashes($_POST['description']);
	$title       = stripslashes($_POST['title']);
	$url         = stripslashes($_POST['url']);
	$category    = stripslashes($_POST['category']);
	
	$image_info = $db->assoc('SELECT * FROM `'.$media_files.'` WHERE `file_id`=?', $file_id);
	
	if ($category != $image_info['category_id'])
	{
		// then the image is moving, and must be repositioned
		
		$position = $image_info['position'];
		$db->run('UPDATE `'.$media_files.'` SET `position`=(`position`-1) WHERE `position`>? AND `category_id`=?', $position, $image_info['category_id']);
		
		// get new position
		$new_max = $db->result('SELECT `position` FROM `'.$media_files.'` WHERE `category_id`=? ORDER BY `position` DESC LIMIT 1', $category);
		$new_position = (is_numeric($new_max)) ? $new_max + 1 : 0;
	}
	else
	{
		$new_position = $image_info['position'];
	}
	
	$db->run('UPDATE `'.$media_files.'` SET `description`=?, `title`=?, `url`=?, `category_id`=?, `position`=? WHERE `file_id`=? LIMIT 1', $description, $title, $url, $category, $new_position, $file_id);
	exit();
}

if ($action == 'update_options')
{
	$component_id = $_POST['component_id'];
	$data = $db->result('SELECT `additional_info` FROM `'.DB_COMPONENT_TABLE.'` WHERE `component_id`=?', $component_id);
	$gallery_options = unserialize($data);
	
	foreach($_POST as $key=>$val)
	{
		if(strlen($key) > 1)
		{
			$gallery_options[$key] = stripslashes($val);
		}
	}
	$db->run('UPDATE `'.DB_COMPONENT_TABLE.'` SET `additional_info`=? WHERE `component_id`=?', serialize($gallery_options), $component_id);
	exit();
}

if ($action == 'add_category')
{
	$component_id = $_POST['component_id'];
	$title        = stripslashes($_POST['category_name']);
	$db->run('UPDATE `'.$media_categories.'` SET `position`=(`position`+1) WHERE `component_id`=?', $component_id);
	
	$db->run('INSERT INTO `'.$media_categories.'` (`component_id`, `title`, `position`) VALUES (?,?,?)', $component_id, $title, 0);
	exit();
}

if ($action == 'edit_category')
{
	$category_id = $_POST['category_id'];
	$title       = stripslashes($_POST['category_name']);
	$db->run('UPDATE `'.$media_categories.'` SET `title`=? WHERE `category_id`=?', $title, $category_id);
	exit();
}

if ($action == 'delete_category')
{
	$category = $_GET['category'];
	
	$check = $db->result('SELECT count(1) FROM `'.$media_files.'` WHERE `category_id`=?', $category);
	if ($check > 0)
	{
		echo 'A category must be empty before being removed. Please delete/move all images to another category and try again';
		exit();
	}
	
	$position     = $db->result('SELECT `position` FROM `'.$media_categories.'` WHERE `category_id`=?', $category);
	$component_id = $db->result('SELECT `component_id` FROM `'.$media_categories.'` WHERE `category_id`=?', $category);

	$db->run('UPDATE `'.$media_categories.'` SET `position`=(`position`-1) WHERE `component_id`=? AND `position` > ?', $component_id, $position);
	$db->run('DELETE FROM `'.$media_categories.'` WHERE `category_id`=?', $category);
	exit();
}

if ($action == 'move_category')
{
	$category  = $_GET['category'];
	$direction = $_GET['direction'];
	
	$position     = $db->result('SELECT `position` FROM `'.$media_categories.'` WHERE `category_id`=?', $category);
	$component_id = $db->result('SELECT `component_id` FROM `'.$media_categories.'` WHERE `category_id`=?', $category);
	$new_position = ($direction == 'up') ? $position - 1 : $position + 1;
	
	$move_id = $db->result('SELECT `category_id` FROM `'.$media_categories.'` WHERE `component_id`=? AND `position`=?', $component_id, $new_position);
	if (is_numeric($move_id))
	{
		$db->run('UPDATE `'.$media_categories.'` SET `position`=? WHERE `category_id`=?', $new_position, $category);
		$db->run('UPDATE `'.$media_categories.'` SET `position`=? WHERE `category_id`=?', $position, $move_id);
	}
}

if ($action == 'custom_thumbnail')
{
	$component_id = $_GET['component_id'];
	$file_id      = $_GET['image_id'];
	$filename     = urldecode($_GET['file']);
	
	$data            = $db->result('SELECT `additional_info` FROM `'.DB_COMPONENT_TABLE.'` WHERE `component_id`=?', $component_id);
	$gallery_options = unserialize($data);
	
	$thumb_path = 'includes/content/media/galleries/'.$gallery_options['gallery_style'].'/files/'.$file_id.'_thumb.jpg';
	$old_thumb  = 'includes/content/media/upload/'.$filename;
	
	unlink($thumb_path);
	rename($old_thumb, $thumb_path);
}

if ($action == 'top_file')
{
	$file_id    = $_GET['image_id'];
	$image_info = $db->assoc('SELECT * FROM `'.$media_files.'` WHERE `file_id`=?', $file_id);
	$position   = $image_info['position'];
	
	$db->run('UPDATE `'.$media_files.'` SET `position`=(`position`+1) WHERE `instance_id`=? AND `category_id`=? AND `position`<?',
		$image_info['instance_id'], $image_info['category_id'], $position
	);
	
	$db->run('UPDATE `'.$media_files.'` SET `position`=? WHERE `file_id`=?', 0, $file_id);
}

if ($action == 'set_order')
{
	$component_id = $_GET['instance_id'];
	$file_id      = $_GET['component_id'];
	$order        = $_GET['order'];

	$count = $db->result('SELECT count(1) FROM `'.$media_files.'` WHERE `category_id`=? AND `instance_id`=?', $category_id, $instance_id);
	$order = explode(',', $order); 
	if ((!is_array($order)) or (sizeof($order) == 0)) { exit(); }

	$all_check = 0;
	foreach ($order as $file_id)
	{
		$check = $db->result('SELECT count(1) FROM `'.$media_files.'` WHERE `category_id`=? AND `instance_id`=? AND `file_id`=?', $category_id, $instance_id, $file_id);
		$all_check += $check;
	}

	// we use the above checks to make sure count == all_check, if it does we will re-order the files, else that means the form has changed and we error

	if ($all_check == $count)
	{
		for ($x = 0; $x < sizeof($order); $x++)
		{
			$file_id = $order[$x];
			$db->run('UPDATE `'.$media_files.'` SET `position`=? WHERE `file_id`=?', $x, $file_id);
		}
	}
	else
	{
		echo 'Unable to save file order.';
	}
}

if ($action == 'change')
{
	$field    = $_GET['field'];
	$entry_id = $_GET['id'];
	$value    = stripslashes(urldecode($_GET['value']));

	$db->run('UPDATE `'.$media_files.'` SET `'.$field.'`=? WHERE `file_id`=?',
		$value, $entry_id
	);
}

if ($action == 'edit_desc')
{
	$file_id = $_GET['file_id'];
	$file_info = $db->assoc('SELECT * FROM `'.$media_files.'` WHERE `file_id`=?', $file_id);
	$desc = htmlspecialchars($file_info['description']);
	$url  = $body->url('includes/content/media/submit.php');

	echo <<<HTML
<form method="post" action="$url" id="mg_desc_form" style="height: auto">
<input type="hidden" name="page_action" value="save_description" />
<input type="hidden" name="file_id" value="$file_id" />
<textarea class="mg_edit_desc" name="mg_description" id="mg_description">$desc</textarea>
</form>
<div class="clear"></div>

<button class="mg_close_desc one" onclick="MG_CloseDescription(0)">Save</button>
<button class="mg_close_desc two" onclick="MG_CloseDescription(1)">Save &amp; Close</button>
<button class="mg_close_desc three" onclick="MG_CloseDescription(2)">Cancel</button>
HTML;
}

if ($action == 'save_description')
{
	$post = Pico_Cleanse($_POST);
	$db->run('ALTER TABLE `'.$media_files.'` CHANGE  `description` `description` TEXT');
	$db->run('UPDATE `'.$media_files.'` SET `description`=? WHERE `file_id`=?', $post['mg_description'], $post['file_id']);

	echo strip_tags($post['mg_description']);
}

if ($action == 'update_category_html')
{
	Pico_CheckTable($media_categories, 'html', 'TEXT');
	$post = Pico_Cleanse($_POST);
	$db->run('UPDATE `'.$media_categories.'` SET `html`=? WHERE `category_id`=?', $post['html'], $post['category_id']);
}
?>