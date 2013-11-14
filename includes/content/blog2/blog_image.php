<?php
if (isset($_GET['refresh']))
{
	$entry_id = $_GET['entry_id'];
	chdir('../../../');
	require_once('core.php');

	$blog_entries    = DB_PREFIX . 'pico_blog_entries';
	$entry_info = $db->assoc('SELECT * FROM `'.$blog_entries.'` WHERE `post_id`=?', $entry_id);
}

require_once('includes/content/media/functions.php');

if ( (!defined('USER_ACCESS')) or (USER_ACCESS < 3) )
{
	exit();
}

$_output = <<<HTML
<div class="ap_overflow" style="height: 425px">
<input type="hidden" id="blog_entry_id" value="$entry_id" />
<h3 class="blog_choice">Image</h3>
<table border="0" cellpadding="2" cellspacing="1" class="blog_options admin_list">
HTML;

$uploader      = new Uploader($body->url('includes/upload.php'), 'Blog2_ImageUploaded', '', '.jpg, .png, .gif', 'Image Files (jpg/png/gif)', '000000', 'ffffff', 'blog');
$uploader_html = $uploader->Output();

$blog_entries = DB_PREFIX . 'pico_blog_entries';
$saved_image  = $db->result('SELECT `story_image` FROM `'.$blog_entries.'` WHERE `post_id`=?', $entry_id);

$storage_dir  = 'includes/storage/blog/images/' . $entry_id .'/';
$full_file    = $storage_dir . $saved_image;

if (is_file($full_file))
{
	$preview_file  = md5('preview_'.$saved_image) . '.png';
	$preview_image = $storage_dir . $preview_file;
	
	if (is_file($preview_file))
	{
		if (filemtime($full_file) > filemtime($preview_image))
		{
			@unlink($preview_image);
		}
	}
	
	if (!is_file($preview_image))
	{
		make_new_image_ws($full_file, $preview_image, 200, 150);
	}

	$image_url = $body->url($preview_image);

	$rand = rand(10000, 99999);
	$caption = htmlspecialchars($entry_info['image_caption']);
	
	$_output .= <<<HTML
<tr class="a">
	<td class="left">Current Image</td>
	<td class="right center">
		<p><img src="$image_url?rand=$rand" /></p>
		<button onclick="Blog2_DeleteImage(); return false">Remove This Image</button>
	</td>
</tr>
<tr class="b">
	<td class="left">Upload New Image</td>
	<td class="right center">
		<p>Upload a new image by clicking the button below, this will replace your current image</p>
		$uploader_html
	</td>
</tr>
<tr class="a">
	<td class="left">Caption</td>
	<td class="right center">
		<input type="text" class="ap_text" name="image_caption" value="$caption" />
	</td>
</tr>
HTML;
}
else
{
	$_output .= <<<HTML
<tr class="a">
	<td class="left">Upload Image</td>
	<td class="right center">
		<p>Upload a new image by clicking the button below</p>
		$uploader_html
	</td>
</tr>
HTML;
}

$_output .= '</table></div>';

echo $_output;

?>