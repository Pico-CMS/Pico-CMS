<?php
chdir('../../');
require_once('core.php');
require_once('includes/uploader/folderthumb.php');
$base = 'upload/';

if (isset($_GET['path']))
{
	$path = urldecode($_GET['path']);
}

if (strlen($path) > 0)
{
	$_SESSION['browse_last_folder_path'] = $path;
	// show back
	$parts = explode('/', $path);
	array_pop($parts);
	array_pop($parts);

	$back = (sizeof($parts) > 0) ? implode('/', $parts) . '/' : '';
	
	if (strlen($back) > 0)
	{
		echo '<div class="browse_back" onclick="Browser_LoadFolder(\''.$back.'\')">Back</div>';
	}
}


// list all the folders

$full_path = $base . $path;

echo '<input type="hidden" id="browser_current_path" value="'.$path.'" />';

$dirs = array();
if ($handle = opendir($full_path))
{
    while (false !== ($file = readdir($handle)))
	{
        if ($file != "." && $file != "..")
		{
			$full_file = $full_path . $file;
			if (is_dir($full_file))
			{
				$dirs[] = $file;
			}
		}
	}
	closedir($handle);
}

natcasesort($dirs);

//echo getcwd();

if (sizeof($dirs) > 0)
{
	foreach ($dirs as $dir)
	{
		$link = $path . $dir . '/';
		$num_files = get_num_files($link);
		$delete = ($num_files == 0) ? '<div class="delete" onclick="Browse_DeleteFolder(\''.$path.'\', \''.$dir.'\')"></div>' : '';
		
		if ($_GET['mode'] == 'image')
		{
			$folder_thumb = basename(folder_thumb($link));
			$folder_thumb = 'includes/storage/ckhtml/thumbnails/'.$folder_thumb;
			$image = (is_file($folder_thumb)) ? '<img src="'.$body->url($folder_thumb).'" />' : '';

			echo <<<HTML
<div class="folder_container">
	<div class="browse_folder" onclick="Browser_LoadFolder('$link')">
		<div class="thumb">$image</div>
	</div>
	<div class="dirname" onclick="Browser_RenameFolder('$path', '$dir')">$dir</div>
	$delete
</div>
HTML;
		}
		else
		{
			
			$file      = ($num_files == '1') ? 'file' : 'files';

			echo <<<HTML
<div class="folder_container">
	<div class="browse_folder" onclick="Browser_LoadFolder('$link')">
		<div class="thumb_text">$num_files $file</div>
	</div>
	<div class="dirname" onclick="Browser_RenameFolder('$path', '$dir')">$dir</div>
	$delete
</div>
HTML;
		}
	}
}
else
{
	echo 'No subfolders';
}
?>