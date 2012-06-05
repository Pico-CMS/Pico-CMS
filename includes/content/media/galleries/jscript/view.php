<?php

// get all the files
require_once('includes/content/media/functions.php');

$gallery_settings  = gallery_get_settings($component_id); // loads the settings as well as defaults if needed
$media_files       = DB_PREFIX . 'pico_media_files';

$images  = $db->force_multi_assoc('SELECT * FROM `'.$media_files.'` WHERE `instance_id`=? ORDER BY `position` ASC', $instance_id);

$image_output = '';
$thumb_output = '';

if ( (sizeof($images) > 0) and (is_array($images)) )
{
	$counter = 0;
	$row_num = 0;
	foreach ($images as $image)
	{
		$image_file = get_gallery_image($image['file_id']);
		$thumb_file = get_gallery_thumb($image['file_id']);
		
		if ($image_file != false)
		{
			$image_path  = $body->url($image_file);
			$thumb_path  = $body->url($thumb_file);
			$url         = $image['url'];
			$description = (strlen($image['description']) > 0) ? $image['description'] : '';
			$title       = (strlen($image['title']) > 0) ? $image['title'] : '';
			
			$extra = ($counter == 0) ? 'style="display: block"' : 'style="display: none"';
			
			//$description = nl2br($description);
			$description = str_replace('[link]', '<a href="'.$url.'">', $description);
			$description = str_replace('[/link]', '</a>', $description);
			
			$image_output .= '<div class="jscript_image jscript_image_'.$component_id.'" '.$extra.' id="jscript_'.$image['file_id'].'">
					<input type="hidden" id="jscript_image_id_'.$component_id.'_'.$counter.'" value="'.$image['file_id'].'" />
					<div class="title">'.$title.'</div>
					<div class="description">'.$description.'</div>
					<img src="'.$image_path.'" />
				</div>';
			
			if ($gallery_settings['num_thumbnails'] > 0)
			{
				if ($counter % $gallery_settings['num_thumbnails'] == 0)
				{
					$thumb_output .= '<div class="jscript_thumbrow_'.$component_id.'" id="jscript_thumbrow_'.$row_num.'" '.$extra.'>';
				}
				
				$active = ($counter == 0) ? ' active' : '';
				
				$thumb_contents = ($gallery_settings['thumbnail_display'] == 'images') ? '<img src="'.$thumb_path.'" />' : ($counter+1);
				$thumb_output .= '<div class="thumbnail jscript_thumbnail_'.$component_id.$active.'" onclick="JScriptG_ShowImage('.$image['file_id'].', '.$component_id.', 1)" id="jscript_thumb_'.$image['file_id'].'">'.$thumb_contents.'</div>';
				
				if (($counter % $gallery_settings['num_thumbnails'] == 0) and ($counter > 0))
				{
					$thumb_output .= '</div>';
					$row_num++;
				}
			}
			
			$counter++;
		}
	}
	
	if ($gallery_settings['num_thumbnails'] > 0)
	{
		if ($counter % $gallery_settings['num_thumbnails'] != 0)
		{
			$thumb_output .= '</div>';
		}
	}
}

$num_images = sizeof($images);

if ( ($gallery_settings['num_thumbnails'] > 0) and ($num_images > $gallery_settings['num_thumbnails']) )
{
	$thumbnail_output = '<div class="thumbnails">
		<div class="previous" onclick="JScriptG_ShowThumbPrevious('.$component_id.')"><img src="'.$body->url('includes/content/media/galleries/jscript/previous.png').'" /></div>
		<div class="next" onclick="JScriptG_ShowThumbNext('.$component_id.')"><img src="'.$body->url('includes/content/media/galleries/jscript/next.png').'" /></div>
		'.$thumb_output.'
	</div>';
}
elseif ($gallery_settings['num_thumbnails'] > 0)
{
	// thumbnails only
	$thumbnail_output = '<div class="thumbnails">' . $thumb_output . '</div>';
}
else
{
	$thumbnail_output = '';
}



if ( ($num_images > 1) and ($gallery_settings['image_controls'] == 'yes') )
{
	$controls  = '<div class="arrow_prev" onclick="JscriptG_ShowPrevImage('.$component_id.')"></div>';
	$controls .= '<div class="arrow_next" onclick="JscriptG_ShowNextImage('.$component_id.')"></div>';
	$enable_rotate = true;
}
else
{
	$controls = '';
	$enable_rotate = false;
}

$rotate = ($gallery_settings['rotate_images'] == 'yes') ? 1 : 0;
$delay  = $gallery_settings['rotate_delay'] * 1000;
if ( (!is_numeric($delay)) or ($delay < 2000) ) { $delay = 2000; }


echo '<div class="jscript_gallery" id="jscript_gallery_'.$component_id.'">
	'.$controls.'
	<input type="hidden" class="jscript_gallery_locator" name="component_id" value="'.$component_id.'" />
	<input type="hidden" id="jscript_rotate_'.$component_id.'" value="'.$rotate.'" />
	<input type="hidden" id="jscript_speed_'.$component_id.'" value="'.$delay.'" />
	<input type="hidden" id="jscript_num_images_'.$component_id.'" value="'.$num_images.'" />

	<div class="images">'.$image_output.'</div>
	'.$thumbnail_output.'
</div>';

?>