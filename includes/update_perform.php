<?php
chdir('../');
require_once('core.php');
if (USER_ACCESS < 4) { exit(); }

clearstatcache();

if (!is_array($_POST['update']))
{
	echo 'Please choose 1 or more options before updating';
	exit();
}

function file_perms($file, $octal = true)
{
    if(!file_exists($file)) return false;
    $perms = fileperms($file);
    $cut = $octal ? 2 : 3;
    return substr(decoct($perms), $cut);
}

function ResetPerms($ftp, $perms)
{
	if (!is_object($ftp)) { return; }
	
	if (sizeof($perms) > 0)
	{
		foreach ($perms as $path=>$mode)
		{
			if (file_exists($path))
			{
				$ftp->chmod($path, octdec($mode));
			}
		}
	}
}

// try to establish an FTP connection to use as needed
$ftp = Pico_ConnectFTP();
$ftp_connected = (is_object($ftp)) ? TRUE : FALSE;

if (!$ftp_connected)
{
	exit('Unable to connect to FTP');
}

foreach ($_POST['update'] as $component_id)
{
	// update this component
	if ($component_id == 0)
	{
		Pico_UpdateCoreFiles($ftp);
	}
	else
	{
		// query the pico update server for files for this component

		

		$post = array();
		$post['update_action'] = 'get_component';
		$post['component_id'] = $component_id;

		$output = Pico_QueryUpdateServer($post);
		
		$xml              = simplexml_load_string($output);
		$component_folder = (string) $xml->component_folder;
		$new_version      = (string) $xml->version;
		$base_path        = 'includes/content/'.$component_folder.'/';

		$post = array();
		$post['update_action']     = 'get_component';
		$post['component_id']      = $component_id;
		$post['component_version'] = trim(file_get_contents($base_path . 'version.txt'));

		$old_version_info = simplexml_load_string(Pico_QueryUpdateServer($post));

		// go thru each file returned
		$original_perms = array(); // use this variable to reset permissions of whatever folders/files we are manipulating back to what they were when done
		
		// we will use these variables to perform our various update actions
		$dirs    = array(); // parent directories of all files and sub folders in this component
		$entries = array();

		$new_component_files = array();
		
		//print_r($xml);
		
		foreach ($xml->files->file as $file)
		{
			$name    = (string) $file->name;
			$type    = (string) $file->type;
			$perms   = (string) $file->perms;
			$content = (string) $file->contents;

			$new_component_files[] = $name;
			
			$content = ($type == 'file') ? base64_decode($content) : $content;
			
			$entries[] = array(
				'name'    => $name,
				'type'    => $type,
				'perms'   => $perms,
				'content' => $content,
			); // for later, so we dont have to iterate over stupid XML, array is nicer
			
			$full_path = $base_path . $name;
			
			$parent_dir = ($type == 'file') ? dirname($name) : $name;
			
			// check to make sure that file is writable (or directory)
			// chmod as needed
			
			if ($parent_dir != '.')
			{
				if (!in_array($parent_dir, $dirs)) { $dirs[] = $parent_dir; }
			}
		}
		
		// put version file in here just to be safe
		
		$entries[] = array(
			'name'    => 'version.txt',
			'type'    => 'file',
			'perms'   => file_perms($base_path . 'version.txt'),
			'content' => $new_version,
		);
		
		// we should now have an array of parent directors in $dirs
		// first, make sure base component folder is writable
		if (!is_writable($base_path))
		{
			//$perms = substr(sprintf('%o', fileperms($base_path)), -4); // ftp style perms.. ie 0644
			$perms = file_perms($base_path);
			
			$original_perms[$base_path] = $perms; // reset for later
			@$ftp->chmod($base_path, 0777);
		}
		
		if (!is_writable($base_path))
		{ 
			ResetPerms($ftp, $original_perms); 
			exit('Unable to write to folder: ' . $base_path .'. Halting.');
		}
		
		// we need to go through each directory, and folder by folder in each directory string make sure that folder exists and is writable
		foreach ($dirs as $parent_dir)
		{
			$parts = explode('/', $parent_dir);
			$current_dir = $base_path;
			
			for ($x=0; $x<sizeof($parts); $x++)
			{
				$parent = $current_dir; // parent should ALWAYS exist because we are starting at the base and moving further in
				$current_dir .= $parts[$x] . '/';
				
				if (!file_exists($current_dir))
				{
					// get parent perms
					if (!is_writable($parent))
					{
						$perms = file_perms($parent);
						$original_perms[$parent] = $perms; // reset for later
						@$ftp->chmod($parent, 0777);
					}
					
					if (!is_writable($parent)) { ResetPerms($ftp, $original_perms); exit('Unable to write to folder: ' . $parent_dir .'. Halting.'); }
					
					// make new folder
					mkdir($current_dir);
					chmod($current_dir, 0777); // we will set this to $perms later or 755 if null
				}
				else
				{
					if (!is_writable($current_dir))
					{
						$perms = file_perms($current_dir);
						$original_perms[$current_dir] = $perms; // reset for later
						@$ftp->chmod($current_dir, 0777);
					}
					
					if (!is_writable($current_dir)) { ResetPerms($ftp, $original_perms); exit('Unable to write to folder: ' . $current_dir .'. Halting.'); }
				}
			}
		}
		
		// ok, if we got through this far we should have all folders existing and writable as needed. 
		// Go through the FILES and make sure they are writable only if they exists
		
		foreach ($entries as $entry)
		{
			if ($entry['type'] == 'file')
			{
				$full_path = $base_path . $entry['name'];
				
				if (file_exists($full_path))
				{
				
					if (!is_writable($full_path))
					{
						$perms = file_perms($full_path);
						$original_perms[$full_path] = $perms; // reset for later
						@$ftp->chmod($full_path, 0666);
					}
					
					if (!is_writable($full_path))
					{
						ResetPerms($ftp, $original_perms);
						exit('Unable to write to file: ' . $full_path .'. Halting.');
					}
				}
			}
		}
		
		// ok, we're this far now. all folders are writable, all files could be wrote to (or made if needed)
		// go through $entries, write/make each file (which will include the new version)
		
		$new_file_list = array(); // for comparison
		
		foreach ($entries as $entry)
		{
			if ($entry['type'] == 'file')
			{
				$full_path = $base_path . $entry['name'];
				
				$h = fopen($full_path, 'w');
				fwrite($h, $entry['content']);
				fclose($h);
				
				$new_file_list[] = $entry['name'];
			}
		}
		
		// because i'm dumb, we need to remove any file that was in the old version, that isn't in the new version

		foreach ($old_version_info->files->file as $file)
		{
			$name = (string) $file->name;

			if (!in_array($name, $new_component_files))
			{
				if (file_exists($base_path . $name))
				{
					// delete it
					@$ftp->deleteRecursive($base_path . $name);
				}
			}
		}
		
		// reset perms
		ResetPerms($ftp, $original_perms);
		
		// apply new perms
		foreach ($entries as $entry)
		{
			$full_path = $base_path . $entry['name'];
			
			$perms = $entry['perms'];
			
			@$ftp->chmod($full_path, octdec($perms));
			//chmod($full_path, $perms);
		}
		
		
		// done
		}
}


// because i'm lazy and want to touch as few files as possible this is the "best" way to make this work
// this is to update the core files
function Pico_UpdateCoreFiles($ftp)
{
	$ftp_connected = (is_object($ftp)) ? TRUE : FALSE;
	
	$post = array();
	$post['update_action'] = 'get_latest_build';
	
	$original_perms = array();
	
	$output = Pico_QueryUpdateServer($post);
	$xml    = simplexml_load_string($output);
	
	$build_version = (string) $xml->latest_build_version;
	
	if ($xml->success == 'ERROR')
	{
		echo 'Error processing request: ' . $xml->errormsg;
		exit();
	}
	else
	{
		// verify that we have the access to edit these files
		foreach ($xml->files->file as $file)
		{
			$action   = (string) $file->action;
			$type     = (string) $file->type;
			$filename = (string) $file->filename;
			
			// only files can be edited, and we only need to check for update if its an edit, 
			// if its a new or deleted file we will add or delete as needed
			
			if ($type != 'sql')
			{
				// make sure parent folder is writable
				if ($type == 'file')
				{
					$parent_folder = dirname($filename);
				}
				else
				{
					$parts = explode('/', $file);
					array_pop($parts);
					$parent_folder = implode('/', $parts);
				}

				// folder should exist, if it doesn't, create it

				
				if (strlen($parent_folder) == 0)
				{
					$parent_folder = './';
				}

				if (!is_dir($parent_folder))
				{
					@$ftp->mkDirRecursive($parent_folder);
				}

				if (!is_dir($parent_folder))
				{
					exit('Parent folder does not exist: ' . $parent_folder);
				}
				
				if (!is_writable($parent_folder))
				{
					$perms = file_perms($parent_folder);
					$original_perms[$parent_folder] = $perms; // reset for later
					@$ftp->chmod($parent_folder, 0777);
				}
				
				if (!is_writable($parent_folder))
				{
					ResetPerms($ftp, $original_perms);
					exit('Unable to write to folder: ' . $parent_folder . '('.$filename.')');
				}
				
				if ( (($action == 'edit') or ($action == 'add')) and (is_file($filename)) )
				{
					// make sure FILE is writable
					if (!is_writable($filename))
					{
						$perms = file_perms($filename);
						$original_perms[$filename] = $perms; // reset for later
						@$ftp->chmod($filename, 0666);
					}
					
					if (!is_writable($filename))
					{
						ResetPerms($ftp, $original_perms);
						exit('Unable to write to file: ' . $filename);
					}
				}
			}
		}
		
		// we got this far, files should be good to go
		$file_counter = 0;
		
		foreach ($xml->files->file as $file)
		{
			$action   = (string) $file->action;
			$type     = (string) $file->type;
			$filename = (string) $file->filename;
			$contents = (string) $file->contents;
			
			if ($type == 'file')
			{
				if (($action == 'add') or ($action == 'edit'))
				{
					$h = fopen($filename, 'w');
					fwrite($h, base64_decode($contents));
					fclose($h);
				}
				elseif ($action == 'delete')
				{
					@unlink($filename);
				}
			}
			elseif ($type == 'dir')
			{
				if ($action == 'delete')
				{
					@rmdir($filename);
				}
			}
			
			if ( ($action == 'delete') and (file_exists($filename)) )
			{
				ResetPerms($ftp, $original_perms);
				exit("File failed to delete: " . $filename);
			}
			
			$file_counter++;
		}
		
		ResetPerms($ftp, $original_perms);
		
		// now apply perms as needed
		
		foreach ($xml->files->file as $file)
		{
			$action   = (string) $file->action;
			$filename = (string) $file->filename;
			$perms    = (string) $file->perms;
			
			if ($ftp_connected)
			{
				$ftp->chmod($path, octdec($perms));
			}
			elseif (is_writable($filename))
			{
				chmod($path, octdec($perms));
			}
		}
		
		Pico_Setting('pico_build_version', $build_version);
		
		//exit("GTG!");
	}
}

?>