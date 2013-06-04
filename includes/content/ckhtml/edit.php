<?php
$content = $db->result('SELECT `content` FROM `'.DB_CONTENT.'` WHERE `instance_id`=?', $instance_id);
if ($content == FALSE) { $content = ''; }
//$content = str_replace("<br />", "", $content);
?>
<form method="post" id="ck_form" action="<?=$body->url('includes/content/ckhtml/submit.php')?>">
<textarea id="ck_text" style="display: none; width: 1px; height: 1px; overflow: hidden;"><?=$content?></textarea>
<input type="hidden" name="instance_id" value="<?=$instance_id?>" />
<input type="hidden" name="component_id" value="<?=$component_id?>" />
<input type="hidden" name="ck_html" value="" />
</form>

<button class="co_button co_button1" onclick="CK_Save()">Save</button>
<button class="co_button co_button2" onclick="CK_Save(1)">Save &amp; Close</button>
<!--input type="submit" value="Update" class="co_button co_button1" /-->