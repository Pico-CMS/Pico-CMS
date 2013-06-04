<?php
chdir('../../../');
require_once('core.php');
if (USER_ACCESS < 3) { exit(); }

$quote_table = DB_PREFIX . 'quote_table';
$action      = $_REQUEST['page_action'];

$fields     = $db->assoc('SHOW COLUMNS FROM `'.$quote_table.'`');
$all_fields = array();
foreach ($fields as $f)
{
	$all_fields[] = $f['Field'];
}

if (!in_array('website', $all_fields))
{
	$db->run('ALTER TABLE `'.$quote_table.'` ADD COLUMN `website` VARCHAR(255)');
}

if (!in_array('website_url', $all_fields))
{
	$db->run('ALTER TABLE `'.$quote_table.'` ADD COLUMN `website_url` VARCHAR(255)');
}

if (($action == 'edit_quote') or ($action == 'add_quote'))
{
	$post = Pico_Cleanse($_POST);
	
	if (strlen($post['quote']) > 0)
	{
		if ($action == 'add_quote')
		{
			$db->run('INSERT INTO `'.$quote_table.'` (`quote`, `who`, `instance_id`, `website`, `website_url`) VALUES (?,?,?,?,?)',
				$post['quote'], $post['who'], $post['instance_id'], $post['website'], $post['website_url']
			);
		}
		else
		{
			$db->run('UPDATE `'.$quote_table.'` SET `quote`=?, `who`=?, `website`=?, `website_url`=? WHERE `id`=?',
				$post['quote'], $post['who'], $post['website'], $post['website_url'], $post['quote_id']
			);
		}
	}
}
elseif ($action == 'delete')
{
	$id = $_GET['quote_id'];
	$db->run('DELETE FROM `'.$quote_table.'` WHERE `id`=?', $id);
}
elseif ($action == 'update_options')
{
	$settings = Pico_Cleanse($_POST['settings']);
	$component_id = $_POST['component_id'];
	$db->run('UPDATE `'.DB_COMPONENT_TABLE.'` SET `additional_info`=? WHERE `component_id`=?', serialize($settings), $component_id);
}
?>