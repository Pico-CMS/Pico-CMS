<?php
chdir('../../../');
require_once('core.php');

if (USER_ACCESS < 2) { exit(); }
$instance_id  = $_POST['instance_id'];
if ((USER_ACCESS == 2) and (!Pico_HasAuthorAccess(USER_ID, $instance_id))) { exit(); }

$content      = stripslashes($_POST['ck_html']);
$component_id = $_POST['component_id'];
$result = $db->run('UPDATE `'.DB_CONTENT.'` SET `content`=? WHERE `instance_id`=?', $content, $instance_id);
?>