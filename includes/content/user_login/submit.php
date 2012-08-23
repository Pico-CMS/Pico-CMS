<?php
chdir('../../../');
require_once('core.php');
if (USER_ACCESS < 3) { exit(); }

$action = $_REQUEST['page_action'];

if ($action == 'update')
{
	$component_id = $_POST['component_id'];
	$settings     = $_POST['settings'];
	
	foreach ($settings as $key=>$val)
	{
		if (is_string($val))
		{
			$settings[$key] = trim(stripslashes($val));
		}
	}
	
	$result = $db->result('UPDATE `'.DB_CONTENT.'` SET `additional_info`=? WHERE `component_id`=?', serialize($settings), $component_id);
	if ($result === FALSE) { echo $db->error; }
	exit();
}

?>