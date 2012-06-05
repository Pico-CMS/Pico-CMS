<?php
chdir('../../../../../');
require_once('core.php');
require_once('includes/content/media/functions.php');
$media_files      = DB_PREFIX . 'pico_media_files';
$media_categories = DB_PREFIX . 'pico_media_categories';
$instance_id      = $_GET['instance_id'];
$component_id     = $_GET['component_id'];



$gallery_options = gallery_get_base_options($component_id);

$flash_settings  = gallery_get_settings($component_id); // loads the settings as well as defaults if needed
//print_r($flash_settings);
$categories  = $db->force_multi_assoc('SELECT * FROM `'.$media_categories.'` WHERE `component_id`=? ORDER BY `position` ASC', $component_id);
$cat_counter = 0;

if ( (sizeof($categories) > 0) and (is_array($categories)) )
{
	foreach ($categories as $category)
	{
		$cat_name = $category['title'];
		$xml .= '<category id="'.$category['category_id'].'" title="'.$cat_name.'">' ."\n";
		
		// get all the images
		
		$images = $db->force_multi_assoc('SELECT * FROM `'.$media_files.'` WHERE `instance_id`=? AND `category_id`=? ORDER BY `position` ASC', $instance_id, $category['category_id']);
		if ( (sizeof($images) > 0) and (is_array($images)) )
		{
			foreach ($images as $image)
			{
				$image_file = get_gallery_image($image['file_id']);
				$thumb_file = get_gallery_thumb($image['file_id']);
				
				$image_path  = $body->url($image_file);
				$thumb_path  = $body->url($thumb_file);
				
				$title = (strlen($image['title']) > 0) ? $image['title'] : '';
				$url   = (strlen($image['url']) > 0) ? $image['url'] : '';
				
				if (strlen($title) == 0) { $title = ' '; } // this is a hard space: alt+0160
				
				$xml .=  <<<XML
	<image id="$image[file_id]">
		<thumb>$thumb_path</thumb>
		<pic>$image_path</pic>
		<title>$title</title>
		<url>$url</url>
	</image>

XML;
			}
		}
		
		$xml .= "\t</category>\n";
	}
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<slideshow autoplay="<?=$flash_settings['auto_play']?>" delay="<?=$flash_settings['delay']?>" useThumbnails="<?=$flash_settings['showThumbnails']?>">
<?=$xml?>
</slideshow>
