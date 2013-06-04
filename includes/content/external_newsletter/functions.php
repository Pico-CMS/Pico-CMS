<?php

function EN_GetButton($component_id, $filename)
{
	$old_path    = 'includes/content/external_newsletter/buttons/'.$filename;
	$storage_dir = 'includes/storage/external_newsletter/'.$component_id.'/';
	$new_path    = $storage_dir . $filename;

	if (is_file($old_path)) 
	{
		// attempt to move this button
		$can_write = Pico_StorageDir($storage_dir);
		if ($can_write) { @rename($old_path, $new_path); @chmod($new_path, 0666); }
	}

	if (is_file($new_path)) { return $new_path; }
	if (is_file($old_path)) { return $old_path; }
	return false;
}

?>