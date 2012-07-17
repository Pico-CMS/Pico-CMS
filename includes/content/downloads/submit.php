<?php
chdir('../../../');
require_once('core.php');
if (USER_ACCESS < 3) { exit(); }

$download_files = DB_PREFIX . 'download_files';
$action         = $_REQUEST['page_action'];
$instance_id    = $_GET['instance_id'];

if ($action == 'add')
{
	$filename    = urldecode($_GET['filename']);
	$source      = 'includes/tmp/'.$filename;
	
	if (file_exists($source))
	{
		// get next position
		//$position     = $db->result('SELECT `position` FROM `'.$download_files.'` WHERE `instance_id`=? ORDER BY `position` DESC LIMIT 1', $instance_id);
		//$new_position = ($position === FALSE) ? 0 : $position + 1;
		
		$new_position = 0;
		$db->run('UPDATE `'.$download_files.'` SET `position`=(`position`+1) WHERE `instance_id`=?', $instance_id);
		
		$file_id = $db->insert('INSERT INTO `'.$download_files.'` (`file_name`, `position`, `instance_id`) VALUES (?,?,?)', $filename, $new_position, $instance_id);
		if ($file_id === FALSE)
		{
			echo $db->error;
			exit();
		}
		$new_dir = 'includes/content/downloads/storage/'.$file_id;
		mkdir($new_dir);
		chmod($new_dir, 0777);
		$new_file = $new_dir . '/' . $filename;
		rename($source, $new_file);
		chmod($new_file, 0777);
	}
	exit();
}
elseif ($action == 'delete')
{
	$file_id = $_GET['file_id'];
	$position = $db->result('SELECT `position` FROM `'.$download_files.'` WHERE `file_id`=?', $file_id);
	$filename = $db->result('SELECT `file_name` FROM `'.$download_files.'` WHERE `file_id`=?', $file_id);
	if ($postition === FALSE) { exit(); }
	
	$db->run('UPDATE `'.$download_files.'` SET `position`=(`position`-1) WHERE `instance_id`=? AND `position`>?', $instance_id, $position);
	$db->run('DELETE FROM `'.$download_files.'` WHERE `file_id`=?', $file_id);
	
	$folder    = 'includes/content/downloads/storage/'.$file_id;
	$full_file = $folder . '/' . $filename;
	
	unlink($full_file);
	rmdir($folder);
	exit();
}
elseif ($action == 'move')
{
	$direction = $_GET['direction'];
	$file_id = $_GET['file_id'];
	
	$position = $db->result('SELECT `position` FROM `'.$download_files.'` WHERE `file_id`=?', $file_id);
	if ($postition === FALSE) { exit(); }
	
	$new_position = ($direction == 'up') ? $position - 1: $position + 1;
	$move_id = $db->result('SELECT `file_id` FROM `'.$download_files.'` WHERE `position`=? AND `instance_id`=?', $new_position, $instance_id);
	
	if (is_numeric($move_id))
	{
		$db->run('UPDATE `'.$download_files.'` SET `position`=? WHERE `file_id`=?', $new_position, $file_id);
		$db->run('UPDATE `'.$download_files.'` SET `position`=? WHERE `file_id`=?', $position, $move_id);
	}
	
	exit();
}
elseif ($action == 'update_html_desc')
{
	$html     = trim(stripslashes($_POST['html_description']));
	$title    = trim(stripslashes($_POST['title']));
	
	$file_id  = $_POST['file_id'];
	$result = $db->run('UPDATE `'.$download_files.'` SET `html_description`=?, `description`=? WHERE `file_id`=?', $html, $title, $file_id);
	exit();
}
?>