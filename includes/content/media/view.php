<?php

require_once('includes/content/media/functions.php');
gallery_checkupgrade();

$additional_info = $db->result('SELECT `additional_info` FROM `'.DB_COMPONENT_TABLE.'` WHERE `component_id`=?', $component_id);
$gallery_options = unserialize($additional_info);
$config_file     = 'includes/content/media/galleries/'.$gallery_options['gallery_style'].'/config.php';
if (!is_file($config_file)) { return; } // this will happen during install when we havent selected a gallery yet
include($config_file);

echo '<input type="hidden" name="gtype" value="'.$gallery_options['gallery_style'].'" />';

$view_file = 'includes/content/media/galleries/'.$gallery_options['gallery_style'].'/view.php';
if (is_file($view_file))
{
	include($view_file);
}
else
{
	$swf_path = $body->url('includes/content/media/galleries/'.$gallery_options['gallery_style'].'/gallery.swf');
	$wmode = (isset($gallery['options'])) ? $gallery['options'] : 'transparent';
?>
<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" width="<?=$options['swf_width']?>" height="<?=$options['swf_height']?>" align="middle">
	<param name="allowScriptAccess" value="sameDomain" />
	<param name="allowFullScreen" value="false" />
	<param name="menu" value="false" />
	<param name="wmode" value="<?=$wmode?>" />
	<param name="movie" value="<?=$swf_path?>" />
	<param name="quality" value="high" />
	<param name="scale" value="noscale" />
	<param name="salign" value="lt" />
	<param name="flashvars" value="<?=$options['flashvars']?>" />
	<embed src="<?=$swf_path?>" scale="noscale" salign="lt" flashvars="<?=$options['flashvars']?>" menu="false"  allowScriptAccess="sameDomain" allowFullScreen="true" quality="high" wmode="<?=$wmode?>" width="<?=$options['swf_width']?>" height="<?=$options['swf_height']?>" align="middle" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />
</object>
<?php
}
?>