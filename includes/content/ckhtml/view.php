<?php
$content = $db->result('SELECT `content` FROM `'.DB_CONTENT.'` WHERE `instance_id`=?', $instance_id);
if ($content == FALSE) { $content = ''; }

if (((USER_ACCESS > 3) or ((USER_ACCESS == 2) and (Pico_HasAuthorAccess(USER_ID, $instance_id)))) and ($component_settings['live_edit'] == 1))
{
	$edit_url = $body->url('includes/content/ckhtml/submit.php');
	$id = 'ckhtml_'.$component_id;
	echo <<<HTML
<div id="$id" contenteditable="true">$content</div>
<script>
	CKEDITOR.disableAutoInline = true;
	var editor = CKEDITOR.inline('$id', { toolbar: 'inline', picoSavePath: '$edit_url', picoComponentId: $component_id, picoInstanceId: '$instance_id' });
</script>
HTML;
}
else
{
	echo $content;
}

?>