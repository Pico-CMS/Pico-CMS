<?php
chdir('../');
require_once('core.php');
if (USER_ACCESS < 4) { exit(); }

// go thru components
$components = GetContentDirs();
$components_to_check = array();
$component_versions = array();

// find all components that contain a "version.txt" file
foreach ($components as $path)
{
	$dir = dirname($path);
	$version_file = $dir . '/' . 'version.txt';
	if (is_file($version_file))
	{
		$components_to_check[]       = $path;
		$parts                       = explode('/', $path);
		$folder                      = $parts[2];
		$version                     = trim(file_get_contents($version_file));
		$component_versions[$folder] = $version;
	}
}

$component_output = '';
// query update server to see if there is a new version

// for pico core files
$build_version = Pico_Setting('pico_build_version');
if (!is_numeric($build_version))
{
	// TODO: Take this out and make it part of the install process
	$build_version = 1017;
	Pico_Setting('pico_build_version', $build_version);
}

$values = array();
array_push($values, "update_action=get_latest_build_version");
array_push($values, "build_version=$build_version");
$curl_post_line = implode('&', $values);

$output = Pico_QueryUpdateServer($curl_post_line);

$xml = simplexml_load_string($output);
$has_update = FALSE;

if ($xml->success == 'ERROR')
{
	echo 'Error processing request: ' . $xml->errormsg;
	exit();
}
else
{
	$up_to_date = (string) $xml->up_to_date;
	$bad_files  = array();
	if ($up_to_date == 'FALSE')
	{
		$has_update = TRUE;
		// well then we need to see if we can update!
		foreach ($xml->files->file as $file)
		{
			$action       = (string) $file->action;
			$filename     = (string) $file->filename;
			
			// only files can be edited, and we only need to check for update if its an edit, 
			// if its a new or deleted file we will add or delete as needed
			if (($action == 'edit') and (is_file($filename)))
			{
				
				$previous_md5 = (string) $file->previous_md5;
				
				// get the md5 of this file
				$current_contents = file_get_contents($filename);
				$contents      = str_replace("\n", '', $current_contents);
				$contents      = str_replace("\r", '', $contents);
				$current_md5   = md5($contents);
				
				if ($previous_md5 != $current_md5)
				{
					$bad_files[] = $filename;
				}
				
			}
		}
		
		$build_version = (string) $xml->latest_build_version;
		
		if (sizeof($bad_files) > 0)
		{
			$bfo = '';
			foreach ($bad_files as $bf)
			{
				$bfo .= '<div>'.$bf.'</div>';
			}
		
			$component_output .= <<<HTML
<div class="no_update">
	<div class="name"><span class="bold">Update unavailable for Pico Core Files</span></div>
	<div class="version"><span class="bold">New Version:</span> $build_version</div>
	<div class="info">
		An update was found but one or more files could not be verified as compatible for updating. 
		Too see a list of files that are conflicting with this update <span class="click" onclick="Pico_ShowBadFiles(0)">click here</span>
		<div class="bad_files" id="bad_files_0" style="display: none">
			$bfo
		</div>
		<table border="0" cellpadding="0" cellspacing="0">
		<tr><td><input type="checkbox" name="update[]" value="0" /> </td><td>Force Update (NOT recommended)</td></tr>
		</table>
	</div>
</div>
HTML;
		}
		else
		{
			$change_log = '';
			foreach ($xml->logs->log as $log)
			{
				$note = (string) $log->note;
				$date = (string) $log->date;
				$build = (string) $log->build;
				
				$change_log .= '<div><span class="bold">Build '.$build.'</span> ('.date('m/d/y', $date).'): '.nl2br(trim($note)).'</div>';
			}
			
			$component_output .= <<<HTML
<div class="can_update">
	<div class="name"><span class="bold">Update available for Pico Core Files</span></div>
	<div class="version"><span class="bold">New Build Version:</span> $build_version </div>
	<div class="changelog">
		$change_log
	</div>
	<table border="0" cellpadding="0" cellspacing="0">
	<tr><td><input type="checkbox" name="update[]" value="0" /> </td><td>Include in Update</td></tr>
	</table>
</div>
HTML;
		}
	}
}

// for components
if (sizeof($components_to_check) > 0)
{
	$values = array();
	$components_to_update = array();
	
	array_push($values, "update_action=get_versions");
	foreach ($components_to_check as $path)
	{
		$parts = explode('/', $path);
		$folder = $parts[2];
		array_push($values, "components[]=$folder");
	}
	
	$curl_post_line = implode('&', $values);
	$output = Pico_QueryUpdateServer($curl_post_line);
	
	$xml = simplexml_load_string($output);
	if ($xml == FALSE)
	{
		echo 'Error connecting to Pico update server: ' . $output;
		exit();
	}
	
	if ($xml->success == 'ERROR')
	{
		echo 'Error processing request: ' . $xml->errormsg;
		exit();
	}
	
	// else finish processing
	$components = $xml->components;
	foreach ($components->component as $component)
	{
		$name    = (string) $component->name;
		$folder  = (string) $component->folder;
		$version = (string) $component->version;
		$id      = (string) $component->component_id;
		
		if ($version != $component_versions[$folder])
		{
			$components_to_update[] = array(
				'name' => $name,
				'folder' => $folder,
				'new_version' => $version,
				'id' => $id,
				'old_version' => $component_versions[$folder]
			);
		}
	}
	
	// if there is, get list of files/md5s for that version
	if (sizeof($components_to_update) > 0)
	{
		$has_update = TRUE;
		foreach ($components_to_update as $component)
		{
			$values = array();
			$values[] = "update_action=get_md5_list";
			$values[] = "component_id=" . $component['id'];
			$values[] = "from_version=" . $component['old_version'];
			$curl_post_line = implode('&', $values);
			
			$output = Pico_QueryUpdateServer($curl_post_line);
			
			// if everything matches, ask the user if he wants to update
			$xml = simplexml_load_string($output);
			if ($xml->success == 'ERROR')
			{
				echo 'Error processing request: ' . $xml->errormsg;
				exit();
			}
			
			$can_update = TRUE;
			$bad_files = array();
			
			foreach ($xml->files->file as $file)
			{
				$filename = (string) $file->filename;
				$md5      = (string) $file->md5;
				
				$full_path = 'includes/content/'.$component['folder'] .'/' . $filename;
				if (is_file($full_path))
				{
					$contents = file_get_contents($full_path);
					$contents = str_replace("\n", '', $contents);
					$contents = str_replace("\r", '', $contents);
					
					$check_md5 = md5($contents);
					if ($check_md5 != $md5)
					{
						$can_update = FALSE;
						$bad_files[] = $full_path;
					}
				}
			}
			
			if ($can_update == TRUE)
			{
				$log = '';
				foreach ($xml->changelog->log as $log)
				{
					$date    = (string) $log->date;
					$version = (string) $log->version;
					$note    = (string) $log->note;
					
					$date = date('m/d/y', $date);
					$log .= '<div class="entry">'.$date.' (version '.$version.'): '.nl2br($note).'</div>';
				}
				
				$id = $component['id'];
			
				$component_output .= <<<HTML
<div class="can_update">
	<div class="name"><span class="bold">Update available for:</span> $component[name]</div>
	<div class="version"><span class="bold">New Version:</span> $component[new_version]</div>
	<div class="changelog">
		$log
	</div>
	<table border="0" cellpadding="0" cellspacing="0">
	<tr><td><input type="checkbox" name="update[]" value="$id" /> </td><td>Include in Update</td></tr>
	</table>
</div>
HTML;
			}
			else
			{
				$bfo = '';
				foreach ($bad_files as $bf)
				{
					$bfo .= '<div>'.$bf.'</div>';
				}
				$component_output .= <<<HTML
<div class="no_update">
	<div class="name"><span class="bold">Update unavailable for:</span> $component[name]</div>
	<div class="version"><span class="bold">New Version:</span> $component[new_version]</div>
	<div class="info">
		An update was found but one or more files could not be verified as compatible for updating. 
		Too see a list of files that are conflicting with this update <span class="click" onclick="Pico_ShowBadFiles($component[id])">click here</span>
		<div class="bad_files" id="bad_files_$component[id]" style="display: none">
			$bfo
		</div>
	</div>
</div>
HTML;
			}
		}
	}
	
	$action_url = $body->url('includes/update_perform.php');
	
	if ($has_update)
	{
		echo <<<HTML
<form method="post" action="$action_url" onsubmit="Pico_PerformUpdate(this); return false">
$component_output
<br />
<input type="submit" value="Perform Update" name="submit_btn" />
</form>
HTML;
	}
	else
	{
		echo <<<HTML
<p>There are no updates to perform</p>
HTML;
	}
}

?>