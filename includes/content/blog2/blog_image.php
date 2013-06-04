<?php
if (isset($_GET['refresh']))
{
	$entry_id = $_GET['entry_id'];
	chdir('../../../');
	require_once('core.php');
}

require_once('includes/content/media/functions.php');

if ( (!defined('USER_ACCESS')) or (USER_ACCESS < 3) )
{
	exit();
}

echo '<div class="ap_overflow" style="height: 425px">';

$uploader = new Uploader($body->url('includes/upload.php'), 'Blog2_ImageUploaded', '', '.jpg, .png, .gif', 'Image Files (jpg/png/gif)', '000000', 'ffffff', 'blog');

$blog_entries = DB_PREFIX . 'pico_blog_entries';
$saved_image = $db->result('SELECT `story_image` FROM `'.$blog_entries.'` WHERE `post_id`=?', $entry_id);

$storage_dir  = 'includes/storage/blog/images/' . $entry_id .'/';
$full_file = $storage_dir . $saved_image;

if (is_file($full_file))
{
	$preview_file  = md5('preview_'.$saved_image) . '.png';
	$preview_image = $storage_dir . $preview_file;
	
	if (is_file($preview_file))
	{
		if (filemtime($full_file) > filemtime($preview_image))
		{
			unlink($preview_image);
		}
	}
	
	if (!is_file($preview_image))
	{
		make_new_image_ws($full_file, $preview_image, 500, 250);
	}
	
	echo '<div><h3 class="blog_choice">Current Image</h3><img src="'.$body->url($preview_image).'" /></div>';
	echo '<button onclick="Blog2_DeleteImage(); return false">Delete Image</button>';
}

?>
<input type="hidden" id="blog_entry_id" value="<?=$entry_id?>" />

<h3 class="blog_choice">New Image</h3>
To upload a new image for this post, click the button below</p>
<?=$uploader->Output()?>
</div>
<?php

/*
require_once('includes/content/blog2/functions.php');

$blog_entries = DB_PREFIX . 'pico_blog_entries';
$storage      = 'includes/content/blog2/storage/';



if (!is_dir($storage . $entry_id))
{
	mkdir($storage . $entry_id);
	chmod($storage . $entry_id, 0777);
}

// legacy: this part here is to convert the old way of doing images to the NEW way of doing images
$check = 'includes/content/blog2/storage/' . $entry_id .'.jpg';
if (is_file($check))
{
	$filename = basename($check);
	$db->run('UPDATE `'.$blog_entries.'` SET `story_image`=? WHERE `post_id`=?', $filename, $entry_id);
	
	$new_file = $storage . $entry_id . '/' . $filename;
	rename($check, $new_file);
	chmod($new_file, 0666);
}

//$upload_path = $body->url('includes/content/blog2/upload.php');
$uploader = new Uploader('', 'Blog_ImageUploaded', '', '.jpg, .png, .gif', 'Image Files (jpg/png/gif)', '000000', 'ffffff', 'blog');

?>
<div class="ap_overflow" style="height: 425px">
	<input type="hidden" id="blog_entry_id" value="<?=$entry_id?>" />
	<?php
	$saved_image = $db->result('SELECT `story_image` FROM `'.$blog_entries.'` WHERE `post_id`=?', $entry_id);
	
	$full_file = $storage . $entry_id . '/' .$saved_image;
	if (is_file($full_file))
	{
		$preview_file  = md5('preview_'.$saved_image) . '.png';
		$preview_image = $storage . $entry_id . '/' . $preview_file;
		if (!is_file($preview_image))
		{
			require_once('includes/content/media/functions.php');
			make_new_image_ws($full_file, $preview_image, 500, 250);
		}
		
		echo '<div><h3 class="blog_choice">Current Image</h3><img src="'.$body->url($preview_image).'" /></div>';
		
		blog_check_image_dir($entry_id);
		
	}
	?>
	
	<p>
	<h3 class="blog_choice">New Image</h3>
	To upload a new image for this post, click the button below</p>
	<?=$uploader->Output()?>
	<br />
	<button onclick="Blog_DeleteImage(); return false">Delete Image</button>
</div>
*/