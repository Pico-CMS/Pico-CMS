<?php

function folder_thumb($folder)
{
	$base   = 'upload/';

	$full_path = $base . $folder;
	$files = array();

	if (is_dir($full_path))
	{
		// open the dir, get up to 4 images, make thumbnail for folder
		if ($handle = opendir($full_path))
		{
			while (false !== ($file = readdir($handle)))
			{
				$full_file = $full_path . '/' . $file;
				if (is_file($full_file))
				{
					$extension = strtolower(array_pop(explode('.', $full_file)));
					$allowed = array('jpg', 'jpeg', 'png', 'gif');
					if (in_array($extension, $allowed))
					{
						$files[] = $file;
					}
				}
			}
			closedir($handle);
		}
		
		// see if we have a cached_file
		
		$cached_location = 'includes/storage/ckhtml/thumbnails/folder-' . md5(serialize($files)) . '.png';
		if (file_exists($cached_location))
		{
			return $cached_location;
		}
		
		$positions = array(
			array(0,0),
			array(51, 0),
			array(0, 31),
			array(51, 31)
		);

	
		// create thumbnail resource
		// this is the container for the 4 different mini thumbnails
		$new_im = imagecreatetruecolor(101, 61);

		// add transparency
		$trans_colour = imagecolorallocatealpha($new_im, 255, 255, 255, 127);
		imagesavealpha($new_im, true);
		imagefill($new_im, 0, 0, $trans_colour);

		if (sizeof($files) == 0) { return FALSE; }

		$num_found = 0;
		
		for ($x = 0; $x < sizeof($files); $x++)
		{
			$source    = $full_path . '/' . $files[$x];
			$pos       = $positions[$num_found];

			$mini_file = 'includes/storage/ckhtml/thumbnails/mini-' . md5($source) . '.png';
			if (!is_file($mini_file)) {
				$img = new Image($source);
				$result = $img->GetPNG(50, 30, $mini_file);
			}

			if (is_file($mini_file)) {
				$num_found++;
				$mini_thumb = imagecreatefromstring(file_get_contents($mini_file));
				imagecopyresampled($new_im, $mini_thumb, $pos[0], $pos[1], 0, 0, 50, 30, 50, 30);

				if ($num_found == 4) { break; }
			}
		}
		
		imagepng($new_im, $cached_location, 5, PNG_NO_FILTER);
		return $cached_location;
	}

}

function get_num_files($folder)
{
	$base   = 'upload/';

	$full_path = $base . $folder;
	$files = array();
	$counter = 0;

	if (is_dir($full_path))
	{
		// open the dir, get up to 4 images, make thumbnail for folder
		if ($handle = opendir($full_path))
		{
			while (false !== ($file = readdir($handle)))
			{
				if (($file != '.') and ($file != '..'))
				{
					$full_file = $full_path . '/' . $file;
					if (is_file($full_file))
					{
						$counter++;
					}
				}
			}
		}
	}
	
	return $counter;
}

?>