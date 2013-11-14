<?php

require_once('includes/content/media/functions.php');
$media_files      = DB_PREFIX . 'pico_media_files';
$media_categories = DB_PREFIX . 'pico_media_categories';
unset($_GET['instance_id']);
?>
<input type="hidden" id="instance_id" value="<?=$instance_id?>" />
<input type="hidden" id="component_id" value="<?=$component_id?>" />
<?php

echo '<div id="mg_preview_window">';

$gallery_options = gallery_get_base_options($component_id);
if ($gallery_options == false)
{
	echo '<p>There was an error accessing this gallery. Please remove it (Content > Remove Content > "Completely Remove") 
	and be sure to select a gallery style before completing installation</p></div>';
	return;
}

if ($gallery_options['categories'] == FALSE)
{
	// no categories
	include('includes/content/media/image_list.php');
}
else
{
	// show categories
	
	//check categories make sure we have positioning...
	$check = $db->result('SELECT count(1) FROM `'.$media_categories.'` WHERE `component_id`=? AND `position`=0', $component_id);
	if ($check > 1)
	{
		$position = 0;
		$categories = $db->force_multi_assoc('SELECT * FROM `'.$media_categories.'` WHERE `component_id`=? ORDER BY `title` ASC', $component_id);
		foreach ($categories as $category)
		{
			$id = $category['category_id'];
			$db->run('UPDATE `'.$media_categories.'` SET `position`=? WHERE `category_id`=?', $position, $id);
			$position++;
		}
	}
	
	// show all categories
	
	//echo '<div id="gallery_categories">';
	include('includes/content/media/gallery_categories.php');
	//echo '</div>';
}

echo '</div>';
?>