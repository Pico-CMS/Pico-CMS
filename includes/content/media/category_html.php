<?php
$category_id = $_REQUEST['category_id'];
chdir('../../../');
require_once('core.php');
if (USER_ACCESS < 3) { exit(); }

$media_files      = DB_PREFIX . 'pico_media_files';
$media_categories = DB_PREFIX . 'pico_media_categories';

$html = $db->result('SELECT `html` FROM `'.$media_categories.'` WHERE `category_id`=?', $category_id);

?>
<button class="co_button co_button2" onclick="MG_DestroyCK(); MG_ShowCategories()">Back</button>
<form method="post" action="<?=$body->url('includes/content/media/submit.php')?>" onsubmit="MG_UpdateCategoryHTML(this); return false">

<input type="hidden" name="category_id" value="<?=$category_id?>" />
<input type="hidden" name="html" value="" />
<input type="hidden" name="page_action" value="update_category_html" />
<textarea id="category_html"><?=$html?></textarea>
<br />
<input type="submit" class="co_button co_button1" value="Save" />
</form>