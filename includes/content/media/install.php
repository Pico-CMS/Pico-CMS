<?php
$media_files      = DB_PREFIX . 'pico_media_files';
$media_categories = DB_PREFIX . 'pico_media_categories';

$sql = $db->run(<<<SQL
CREATE TABLE IF NOT EXISTS `$media_files` (
	`file_id` BIGINT(11) NOT NULL auto_increment,
	`instance_id` VARCHAR(32) NOT NULL,
	`category_id` BIGINT(11) NOT NULL DEFAULT 0,
	`title` VARCHAR(255) NOT NULL DEFAULT '',
	`description` TEXT NOT NULL DEFAULT '',
	`extension` VARCHAR(5) NOT NULL DEFAULT '',
	`url` VARCHAR(255) NOT NULL DEFAULT '',
	`position` BIGINT(11) NOT NULL,
	PRIMARY KEY(`file_id`));
SQL
);

$sql = $db->run(<<<SQL
CREATE TABLE IF NOT EXISTS `$media_categories` (
	`category_id` BIGINT(11) NOT NULL auto_increment,
	`component_id` BIGINT(11) NOT NULL DEFAULT 0,
	`title` VARCHAR(255) NOT NULL DEFAULT '',
	`description` VARCHAR(255) NOT NULL DEFAULT '',
	`position` BIGINT(11) NOT NULL,
	`html` TEXT,
	PRIMARY KEY(`category_id`));
SQL
);

$style_drop = '<select name="gallery_style" onchange="MG_PreviewStyle(this)"><option value="">Select a Gallery</option>';
$style_dir  = 'includes/content/media/galleries/';

$entries = '';

if ($h = opendir($style_dir))
{
    while (false !== ($file = readdir($h)))
	{
        $full_file = $style_dir . $file;
		if  ( (is_dir($full_file)) and (strlen($file) > 2) )
		{
			$config_file = $full_file . '/config.php';
			if (file_exists($config_file))
			{
				include($config_file);
				$style_drop .= '<option value="'.$file.'">'.$options['type'].'</option>';
				$preview_file = $full_file . '/preview.jpg';

				$preview = (is_file($preview_file)) ? $body->url($preview_file) : $body->url('includes/content/media/no-preview.jpg');

				$entries .= <<<HTML
<div class="mg_install_preview">
	<div class="image"><img src="$preview" /></div>
	<div class="title"><input type="radio" onchange="MG_PreviewStyle(this.value)" name="gallery_style" value="$file" /> $options[type]</div>
</div>
HTML;
			}
		}
    }
}

$style_drop .= '</select>';
?>
<h3>Choose a Gallery</h3>
<div id="gallery_picker" class="ap_overflow">
	<form method="post" action="<?=$body->url('includes/content/media/submit.php')?>" onsubmit="MG_Install(this); return false">
		<input type="hidden" name="page_action" value="install" />
		<input type="hidden" name="component_id" value="<?=$component_id?>" />
		<input type="hidden" name="instance_id" value="<?=$instance_id?>" />
		<input type="hidden" name="location" value="<?=$location?>" />
		<?=$entries?>
		<input name="submitbtn" class="co_button co_button1" type="submit" value="Next" />
	</form>
</div>
<div id="mg_preview_style"></div>


