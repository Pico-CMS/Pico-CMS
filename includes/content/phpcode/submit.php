<?php
chdir('../../../');
require_once('core.php');

$content      = stripslashes($_POST['php_data']);
$instance_id  = $_POST['instance_id'];
$component_id = $_POST['component_id'];

if (USER_ACCESS < 2) { exit(); }
if ((USER_ACCESS == 2) and (!Pico_HasAuthorAccess(USER_ID, $instance_id))) { exit(); }

$result = $db->run('UPDATE `'.DB_CONTENT.'` SET `content`=? WHERE `instance_id`=?', $content, $instance_id);
?>