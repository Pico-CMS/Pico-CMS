<?php
if (isset($_GET['instance_id']))
{
	chdir('../../../');
	require_once('core.php');
	require_once('includes/content/media/functions.php');
	$instance_id  = $_GET['instance_id'];
	$component_id = $_GET['component_id'];
	$category_id  = $_GET['category_id'];
	$media_files      = DB_PREFIX . 'pico_media_files';
	$media_categories = DB_PREFIX . 'pico_media_categories';
	$gallery_options  = gallery_get_base_options($component_id);
	
}

if (USER_ACCESS < 3) { exit(); }

if ($gallery_options['categories'] == TRUE)
{
	echo '<button class="co_button co_button1" onclick="MG_ShowCategories()">Back to Categories</button>';
	$images = $db->force_multi_assoc('SELECT * FROM `'.$media_files.'` WHERE `instance_id`=? AND `category_id`=? ORDER BY `position` ASC', $instance_id, $category_id);
}
else
{
	$images = $db->force_multi_assoc('SELECT * FROM `'.$media_files.'` WHERE `instance_id`=? ORDER BY `position` ASC', $instance_id);
}
$image_preview = '';
if ( (sizeof($images) > 0) and (is_array($images)) )
{
	$counter = 0;
	foreach ($images as $image)
	{
		$_file = 'includes/storage/media/source/'.$image['file_id'] . '_thumb.jpg';
		if (is_file($_file))
		{
			// get rid of old thumbnails 
			list($w, $h) = getimagesize($_file);
			if (($w == 100) and ($h == 100)) { @unlink($_file); }
		}

		if (!file_exists($_file))
		{
			$source = 'includes/storage/media/source/'.$image['file_id'] . '.' . $image['extension'];
			make_new_image_ws($source, $_file, 60, 45);
		}

		$image_path = $body->url($_file);
		
		$id    = $image['file_id'];
		$left  = $body->url('includes/icons/left-arrow.png');
		$right = $body->url('includes/icons/right-arrow.png');
		
		$delete = '<img src="'.$body->url('includes/icons/delete.png').'" class="click" title="Delete Image" onclick="MG_DeleteImage('.$id.')" />';
		$top    = '<img src="'.$body->url('includes/icons/arrow-up.png').'" class="click" title="Move to Top" onclick="MG_FileTop('.$id.')" />';
		$edit   = '<img src="'.$body->url('includes/icons/edit.png').'" class="click" title="Edit Image" onclick="MG_EditImage('.$id.')" />';
		
		$description = strip_tags($image[description]);
		$table = '';

		if ($gallery_options['title'])
		{
			$value = htmlspecialchars($image['title']);
			$table .= <<<HTML
<tr><td class="left">Title</td><td class="right"><input type="text" name="title" file_id="$id" class="text" value="$value" /></td></tr>
HTML;
		}

		if ($gallery_options['url'])
		{
			$value = htmlspecialchars($image['url']);
			$table .= <<<HTML
<tr><td class="left">Link</td><td class="right"><input type="text" name="url" class="text" file_id="$id" value="$value" /></td></tr>
HTML;
		}

		if ($gallery_options['description'])
		{
			$table .= <<<HTML
<tr><td class="left">Description</td><td class="right"><div class="description" onclick="MG_EditDescription($id)" id="mg_desc_$id">$description</div></td></tr>
HTML;
		}

		$image_preview .= <<<HTML
<div class="image_holder" id="gallery_edit_$id" gallery_id="$id">
	<table border="0" cellpadding="0" cellspacing="1">
	<tr>
		<td width="70">
			<img src="$image_path" class="mover" />
		</td>
		<td width="30">$delete</td>
		<td>
			<table class="mg_edit_table" cellpadding="0" cellspacing="0" border="0">$table</table>
		</td>
	</tr>
	</table>
</div>
HTML;
	}
}

echo $image_preview;

$mg_is_html = ($gallery_options['is_html']) ? 1 : 0;

if (!is_numeric($category_id)) { $category_id = 0; }
$swf_path = $body->url('includes/content/media/FileUploader.swf');

$upload_path = $body->url('includes/upload.php');
$uploader    = new Uploader($upload_path, 'MG_AddFile', 'MG_RefreshImages', '.jpg, .png, .gif', 'Image Files (jpg/png/gif)', '000000', 'ffffff');
?>

<div id="gallery_aux_window"></div>
<div id="gallery_edit_status"></div>

<input type="hidden" id="instance_id" value="<?=$instance_id?>" />
<input type="hidden" id="mg_is_html" value="<?=$mg_is_html?>" />
<input type="hidden" id="category_id" value="<?=$category_id?>" />
<div class="clear"></div>

<h3>Upload Images</h3>
<?=$uploader->Output()?>