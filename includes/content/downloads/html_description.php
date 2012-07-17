<?php
chdir('../../../');
require_once('core.php');
if (USER_ACCESS < 3) { exit(); }

$file_id = $_GET['id'];
$download_files = DB_PREFIX . 'download_files';

$info = $db->assoc('SELECT * FROM `'.$download_files.'` WHERE `file_id`=?', $file_id);
$html = $info['html_description'];
?>
<h3>Edit Details - <?=$info['file_name']?></h3>
<p class="click" onclick="DL_Refresh()">[Back]</p>

<form method="post" style="height: auto" action="<?=$body->url('includes/content/downloads/submit.php')?>" onsubmit="DL_HTML_Submit(this); return false">
<input type="hidden" name="page_action" value="update_html_desc" />
<input type="hidden" name="file_id" value="<?=$file_id?>" />
<input type="hidden" name="html_description" value="" />

<p><b>Title:</b> <input type="text" name="title" value="<?=$info['description']?>" /></p>
<b>Description</b><br />
<textarea id="dl_html_desc"><?=$html?></textarea><br />
<input type="submit" value="Update" />
</form>