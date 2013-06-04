<?php
chdir('../../../');
require_once('core.php');

if (USER_ACCESS < 3) { exit(); }

$twitter_table = DB_PREFIX . 'twitter_data';

$action = $_REQUEST['page_action'];
if ($action == 'update_options')
{
	$component_id = $_POST['component_id'];
	$new_options  = $_POST['settings'];

	$additional_info = $db->result('SELECT `additional_info` FROM `'.DB_COMPONENT_TABLE.'` WHERE `component_id`=?', $component_id);
	$settings        = unserialize($additional_info);
	$twitter_access  = $settings['twitter_access'];

	if (!is_array($settings)) { $settings = array(); }
		
	foreach ($new_options as $key=>$val)
	{
		$new_options[$key] = stripslashes($val);
	}

	if ($_POST['twitter_reauth'] != 1)
	{
		$new_options['twitter_access'] = $twitter_access;
	}
	
	$db->run('UPDATE `'.DB_COMPONENT_TABLE.'` SET `additional_info`=? WHERE `component_id`=?', serialize($new_options), $component_id);
	$db->run('DELETE FROM `'.$twitter_table.'` WHERE `component_id`=?', $component_id);
	exit();
}
?>