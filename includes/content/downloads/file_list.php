<?php

require_once('includes/content/media/functions.php');

$files = $db->force_multi_assoc('SELECT * FROM `'.$download_files.'` WHERE `instance_id`=? ORDER BY `position` ASC', $instance_id);
$counter = 0;

if ( (is_array($files)) and (sizeof($files) > 0) )
{
	foreach ($files as $file)
	{
		$class = ($counter %2 == 0) ? 'file_a' : 'file_b';
		$counter++;
		$ext = strtolower(array_pop(explode('.', $file['file_name'])));
		
		if ($ext == 'jpg')
		{
			$filename = md5($file['file_name']) . '.jpg';
			$full_file = 'includes/content/downloads/thumbs/' . $filename;
			if (!file_exists($full_file))
			{
				$source = 'includes/content/downloads/storage/'.$file['file_id'].'/'.$file['file_name'];
				make_new_image_ws($source, $full_file, 30, 30);
			}
			$extra = '<img src="'.$body->url($full_file).'" hspace="3" />';
		}
		else
		{
			$extra = '';
		}
		$up     = '<img class="click icon" title="Move Up" src="'.$body->url('includes/icons/arrow-up.png').'"   onclick="DL_MoveFile('.$file['file_id'].', \'up\')" />';
		$down   = '<img class="click icon" title="Move Down" src="'.$body->url('includes/icons/arrow-down.png').'" onclick="DL_MoveFile('.$file['file_id'].', \'down\')" />';
		$delete = '<img class="click icon" title="Delete" src="'.$body->url('includes/icons/delete.png').'"     onclick="DL_DeleteFile('.$file['file_id'].')" />';
		$desc   = '<img class="click icon" title="Edit HTML Description" src="'.$body->url('includes/icons/content.png').'" onclick="DL_EditDescription('.$file['file_id'].')" />';
		$description = $file['description'];
		$link = $body->url('includes/content/downloads/download.php?id='.$file['file_id']);
		
		if (strlen($file['description']) > 0)
		{
			$title = $file['description'] . ' ('.$file['file_name'].')';
		}
		else
		{
			$title = $file['file_name'];
		}
		
		echo '<div class="'.$class.'"><table border="0" cellpadding="0" cellspacing="0"><tr><td>'.$extra.'</td><td>'.$up.$down.$delete.$desc.'<a href="'.$link.'">'.$title.'</a> Downloads: '.$file['num_downloads'].'</td></tr></table></div>';
	}
}
else
{
	echo $db->error;
}
?>