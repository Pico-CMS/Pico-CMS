<?php
chdir('../../../');
require_once('core.php');
if (USER_ACCESS < 3) { exit(); }
require_once('includes/content/contact/functions.php');

$action = $_REQUEST['page_action'];
$contact_table = DB_PREFIX . 'pico_contact_form';
$history_table = DB_PREFIX . 'pico_contact_history';

if ($action == 'update')
{
	$component_id = $_POST['component_id'];
	
	// update table if needed
	
	$fields     = $db->assoc('SHOW COLUMNS FROM `'.$contact_table.'`');
	$all_fields = array();
	foreach ($fields as $f)
	{
		$all_fields[] = $f['Field'];
	}
	
	if (!in_array('layout', $all_fields))
	{
		$db->run('ALTER TABLE `'.$contact_table.'` ADD COLUMN `layout` VARCHAR(10)');
	}
	if (!in_array('preview_message', $all_fields))
	{
		$db->run('ALTER TABLE `'.$contact_table.'` ADD COLUMN `preview_message` TEXT');
	}
	if (!in_array('interim_message', $all_fields))
	{
		$db->run('ALTER TABLE `'.$contact_table.'` ADD COLUMN `interim_message` TEXT');
	}
	if (!in_array('buttons', $all_fields))
	{
		$db->run('ALTER TABLE `'.$contact_table.'` ADD COLUMN `buttons` BLOB');
	}
	if (!in_array('message_format', $all_fields))
	{
		$db->run('ALTER TABLE `'.$contact_table.'` ADD COLUMN `message_format` VARCHAR(5)');
	}
	if (!in_array('recaptcha', $all_fields))
	{
		$db->run('ALTER TABLE `'.$contact_table.'` ADD COLUMN `recaptcha` BLOB');
	}
	if (in_array('submit_button', $all_fields))
	{
		$success = CF_MigrateButtons($component_id);
	}
	if (!in_array('send_user_copy', $all_fields))
	{
		$db->run('ALTER TABLE `'.$contact_table.'` ADD COLUMN `send_user_copy` TINYINT(1) NOT NULL DEFAULT 0');
	}
	if (!in_array('copy_message', $all_fields))
	{
		$db->run('ALTER TABLE `'.$contact_table.'` ADD COLUMN `copy_message` TEXT');
	}
	if (!in_array('copy_subject', $all_fields))
	{
		$db->run('ALTER TABLE `'.$contact_table.'` ADD COLUMN `copy_subject` TEXT');
	}
	
	$recipient_address = stripslashes($_POST['recipient_address']);
	$from_subject      = stripslashes($_POST['from_subject']);
	$complete_message  = stripslashes($_POST['complete_message']);
	$layout            = stripslashes($_POST['layout']);
	$preview_message   = stripslashes($_POST['preview_message']);
	$interim_message   = stripslashes($_POST['interim_message']);
	$message_format    = stripslashes($_POST['message_format']);
	$send_user_copy    = stripslashes($_POST['send_user_copy']);
	$copy_message      = Pico_Cleanse($_POST['copy_message']);
	$copy_subject      = Pico_Cleanse($_POST['copy_subject']);
	$recaptcha         = Pico_Cleanse($_POST['recaptcha']);

	// see if current buttons exist
	$saved_buttons = $db->result('SELECT `buttons` FROM `'.$contact_table.'` WHERE `component_id`=?', $component_id);
	$buttons = unserialize($saved_buttons);
	if (!is_array($buttons)) { $buttons = array(); }
	
	// buttons, added 11/26/12
	$pbuttons = $_POST['buttons'];
	foreach ($pbuttons as $button_type => $button_val)
	{
		// check to see if removed
		if ($_POST['rbuttons'][$button_type] == 1)
		{
			$buttons[$button_type] = '';
		}
		else
		{
			$uploaded_file = 'includes/tmp/'. $button_val;
			if (is_file($uploaded_file))
			{
				$ext = strtolower(array_pop(explode('.', $button_val)));
				$save_dir = 'includes/storage/contact/'.$component_id.'/buttons/';
				$writable = Pico_StorageDir($save_dir);
				if ($writable) {
					
					$saved_file = $save_dir . $button_type . '.' . $ext;
					if (is_file($saved_file)) { @unlink($saved_file); }
					@rename($uploaded_file, $saved_file);
					@chmod($saved_file, 0666);

					if ((is_file($saved_file)) and (is_writable($saved_file)))
					{
						$buttons[$button_type] = basename($saved_file);
					}
				}
			}
		}
	}
	
	$check = $db->result('SELECT count(*) FROM `'.$contact_table.'` WHERE `component_id`=?', $component_id);
	if ( (int) $check === 0)
	{
		$insert = $db->insert('INSERT INTO `'.$contact_table.'` (`recipient_address`, `from_subject`, `complete_message`, `component_id`, 
			`layout`, `buttons`, `preview_message`, `interim_message`, `message_format`, `recaptcha`, `send_user_copy`, `copy_message`, `copy_subject`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)',
			$recipient_address, $from_subject, $complete_message, $component_id, $layout, serialize($buttons), $preview_message, 
			$interim_message, $message_format, serialize($recaptcha), $send_user_copy, $copy_message, $copy_subject
		);
	}
	else
	{

		$update = $db->run('UPDATE `'.$contact_table.'` SET `recipient_address`=?, `from_subject`=?, `complete_message`=?, `layout`=?, `buttons`=?, `preview_message`=?, 
			`interim_message`=?, `message_format`=?, `recaptcha`=?, `send_user_copy`=?, `copy_message`=?, `copy_subject`=? WHERE `component_id`=?', 
			$recipient_address, $from_subject, $complete_message, $layout, serialize($buttons), $preview_message, $interim_message, $message_format, 
			serialize($recaptcha), $send_user_copy, $copy_message, $copy_subject, $component_id
		);
	}
	
	exit();
}

if ($action == 'move_field')
{
	$component_id = $_GET['component_id'];
	$field_id     = $_GET['field_id'];
	$parent_id    = $_GET['parent_id'];
	$direction    = $_GET['direction'];
	
	$fields = $db->result('SELECT `fields` FROM `'.$contact_table.'` WHERE `component_id`=?', $component_id);
	$fields = unserialize($fields);
	if (!is_array($fields)) { $fields = array(); }
	
	/*
	$max = sizeof($fields);
	$new_position = ($direction == 'up') ? $field_id - 1 : $field_id + 1;

	if ( ($new_position >= 0) and ($new_position < $max) )
	{
		// its ok to swap
		$temp = $fields[$field_id];
		$fields[$field_id] = $fields[$new_position];
		$fields[$new_position]= $temp;
	}*/

	$new_position = ($direction == 'up') ? $field_id - 1 : $field_id + 1;

	if ($parent_id == -1)
	{
		if (isset($fields[$new_position])) {
			$temp = $fields[$field_id];
			$fields[$field_id] = $fields[$new_position];
			$fields[$new_position]= $temp;
		}
	}
	else
	{
		if (isset($fields[$parent_id]['children'][$new_position])) {
			$temp = $fields[$parent_id]['children'][$field_id];
			$fields[$parent_id]['children'][$field_id] = $fields[$parent_id]['children'][$new_position];
			$fields[$parent_id]['children'][$new_position]= $temp;
		}
	}

	
	$update = $db->run('UPDATE `'.$contact_table.'` SET `fields`=? WHERE `component_id`=?', serialize($fields), $component_id);
	exit();
}

if ($action == 'delete_field')
{
	$component_id = $_GET['component_id'];
	$field_id     = $_GET['field_id'];
	$parent_id    = $_GET['parent_id'];
	$fields = $db->result('SELECT `fields` FROM `'.$contact_table.'` WHERE `component_id`=?', $component_id);
	$fields = unserialize($fields);
	if (!is_array($fields)) { $fields = array(); }
	
	if ($parent_id != -1) 
	{
		$child_fields = $fields[$parent_id]['children'];
		$new_fields = array();
		for ($x = 0; $x < sizeof($child_fields); $x++)
		{
			if ($x != $field_id)
			{
				$new_fields[] = $child_fields[$x];
			}
		}
		$fields[$parent_id]['children'] = $new_fields;
		$new_fields = $fields;
	}
	else
	{
		$new_fields = array();
		for ($x = 0; $x < sizeof($fields); $x++)
		{
			if ($x != $field_id)
			{
				$new_fields[] = $fields[$x];
			}
		}
	}

	print_r($new_fields);
	
	$update = $db->run('UPDATE `'.$contact_table.'` SET `fields`=? WHERE `component_id`=?', serialize($new_fields), $component_id);
	exit();
}


if ($action == 'edit_field')
{
	$component_id = $_GET['component_id'];
	$edit_field   = $_GET['field_id'];
	$parent_id    = $_GET['parent_id'];

	if ((is_numeric($parent_id)) and ($parent_id >= 0)) {
		$edit_group = true;
	}

	include('includes/content/contact/fields.php');
	exit();
}

if ($action == 'update_fields')
{
	$component_id = $_GET['component_id'];
	if ($_GET['full'] == 'no') {
		$fields_only = true;	
	}
	include('includes/content/contact/fields.php');
	exit();
}

if ($action == 'edit_group_fields')
{
	$component_id = $_GET['component_id'];
	$parent_id    = $_GET['field_id'];
	$edit_group   = true;

	if ($_GET['full'] == 'no') {
		$fields_only = true;	
	}	
	include('includes/content/contact/fields.php');
	exit();
}

if ($action == 'edit_field_post')
{
	$field_id = $_POST['field_id'];
	$action   = 'add_field';
}
if ($action == 'add_field')
{
	$component_id = $_POST['component_id'];
	$fields = $db->result('SELECT `fields` FROM `'.$contact_table.'` WHERE `component_id`=?', $component_id);
	$fields = unserialize($fields);
	if (!is_array($fields)) { $fields = array(); }
	
	$new_field = array();
	$info = $_POST['field'];
	$name = stripslashes($info['name']);
	if (strlen($name) > 0)
	{
		$new_field['name']     = $name;
		$new_field['caption']  = stripslashes($info['caption']);
		$new_field['type']     = $info['type'];
		$new_field['pattern']  = $info['pattern'];
		$new_field['required'] = $info['required'];
		$new_field['directory_source'] = $info['directory_source'];
		$new_field['directory_field']  = $info['directory_field'];
		
		$options = explode("\n", stripslashes($info['options']));
		
		$new_field['options'] = array();
		foreach ($options as $o)
		{
			$o = trim($o);
			if (strlen($o) > 0)
			{
				$new_field['options'][] = $o;
			}
		}

		if ((is_numeric($_POST['parent_id'])) and ($_POST['parent_id'] >= 0)) {
			$parent_id = $_POST['parent_id'];
			if (!isset($fields[$parent_id]['children'])) {
				$fields[$parent_id]['children'] = array();
			}

			if (isset($field_id))
			{
				$fields[$parent_id]['children'][$field_id] = $new_field;
			}
			else
			{
				$fields[$parent_id]['children'][] = $new_field;
			}
		}
		else
		{
			if (isset($field_id))
			{
				$fields[$field_id] = $new_field;
			}
			else
			{
				$fields[] = $new_field;
			}
		}
		
		$update = $db->run('UPDATE `'.$contact_table.'` SET `fields`=? WHERE `component_id`=?', serialize($fields), $component_id);
	}
	exit();
}

if ($action == 'clear_download')
{
	$component_id = $_GET['component_id'];
	$storage = 'includes/content/contact/storage/' . $component_id . '/';
	if (file_exists($storage))
	{
		// see if there are any files in this folder, if so, delete
		if (is_dir($storage))
		{
			if ($dh = opendir($storage))
			{
				while (($file = readdir($dh)) !== false)
				{
					$full_file = $storage . $file;
					if (filetype($full_file) == 'file')
					{
						unlink($full_file);
					}
				}
				closedir($dh);
			}
		}
		rmdir($storage);
	}
}

if ($action == 'clear_history')
{
	$component_id = $_GET['component_id'];
	$db->run('DELETE FROM `'.$history_table.'` WHERE `component_id`=?', $component_id);
}

if ($action == 'get_directory_fields')
{
	$dir_component_id = $_GET['dir_component_id'];
	$component_id     = $_GET['component_id'];
	
	$output = CF_GetDirectoryFields($component_id, $dir_component_id);
	echo $output;
}

// for previewing uploaded images before they are uploaded
if ($action == 'preview_button')
{
	$filename     = urldecode($_GET['filename']);
	$component_id = $_GET['component_id'];
	$base_dir     = 'includes/tmp/';

	$preview_file = $base_dir . $filename;
	if (!is_file($preview_file)) {
		exit('0|File could not be uploaded');
	}
	else
	{
		$image = $body->url($preview_file);
		exit('1|<table><tr><td>Preview:</td><td><img src="'.$image.'" /></td></tr></table>');
	}
}

if ($action == 'copy_group')
{
	$field_id     = $_GET['field_id'];
	$component_id = $_GET['component_id'];
	$new_name     = urldecode($_GET['new_name']);

	$fields = $db->result('SELECT `fields` FROM `'.$contact_table.'` WHERE `component_id`=?', $component_id);
	$fields = unserialize($fields);
	if (!is_array($fields)) { $fields = array(); }

	$copy_field = $fields[$field_id];

	if (strlen($new_name) > 0) {
		$copy_field['name'] = $new_name;
	}
	
	$fields[] = $copy_field;
	$update = $db->run('UPDATE `'.$contact_table.'` SET `fields`=? WHERE `component_id`=?', serialize($fields), $component_id);
}

?>