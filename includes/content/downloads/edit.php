<?php

if ($_GET['refresh'] == 1)
{
	$instance_id = $_GET['instance_id'];
	chdir('../../../');
	require_once('core.php');
}

if ( (USER_ACCESS < 3) or (!defined('USER_ACCESS')) ) { exit(); }

$download_files = DB_PREFIX . 'download_files';
$sql = $db->run(<<<SQL
CREATE TABLE IF NOT EXISTS `$download_files` (
	`file_id` BIGINT(11) NOT NULL auto_increment,
	`file_name` TEXT NOT NULL,
	`description` VARCHAR(255),
	`position` BIGINT(11) NOT NULL,
	`instance_id` VARCHAR(32),
	`num_downloads` BIGINT(11) NOT NULL DEFAULT '0',
	`html_description` LONGTEXT,
	PRIMARY KEY(`file_id`));
SQL
);

$upload_path = $body->url('includes/upload.php');
$uploader = new Uploader($upload_path, 'DL_FileUploaded', 'DL_FilesUploaded', '.*', 'All Files', '000000', 'ffffff');

?>
<input type="hidden" id="instance_id" value="<?=$instance_id?>" />
<input type="hidden" id="component_id" value="<?=$component_id?>" />
<div class="ap_overflow" id="file_list">
	<h3>Upload New File</h3>
	<?=$uploader->Output()?>
	<h3>Existing Files</h3>
	<?php include('includes/content/downloads/file_list.php'); ?>
</div>
<!--button onclick="DL_Close()">Close</button-->