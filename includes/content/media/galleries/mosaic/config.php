<?php

if (isset($component_id))
{
	$data = $db->result('SELECT `additional_info` FROM `'.DB_COMPONENT_TABLE.'` WHERE `component_id`=?', $component_id);
	$gallery_options = unserialize($data);
}
else
{
	$gallery_options = array();
}

$options = array();
$options['type'] = 'Mosaic Gallery'; // what you call your content
$options['text_description'] = 'Simple Image Gallery.'; // a brief description of what your content does, any restrictions, etc
$options['swf_width']    = (isset($gallery_options['swf_width'])) ? $gallery_options['swf_width'] : 400;
$options['swf_height']   = (isset($gallery_options['swf_height'])) ? $gallery_options['swf_height'] : 300;
$options['img_width']    = $options['swf_width'];
$options['img_height']   = $options['swf_height'];
$options['thumb_width']  = 30;
$options['thumb_height'] = 30;
$options['categories']   = true;
$options['description']  = false;
$options['title']        = true;
$options['url']          = true;

if (isset($body))
{
	$galleryURL  = urlencode($body->url('includes/content/media/galleries/mosaic/gallery.php?component_id='.$component_id.'&instance_id='.$instance_id));
	$options['flashvars'] = 'xmlURL='.$galleryURL;
	
	//echo urldecode($galleryURL);
	
}

$settings = array(
	'auto_play'=>array(
		'name'=>'Auto Play',
		'type'=>'select',
		'values'=>array('true', 'false'),
		'default'=>'true',
	),
	'delay'=>array(
		'name'=>'Delay',
		'type'=>'text',
		'default'=>'5',
	),
	'swf_width'=>array(
		'name'=>'Width',
		'type'=>'text',
		'default'=>'400',
	),
	'swf_height'=>array(
		'name'=>'Height',
		'type'=>'text',
		'default'=>'300',
	),
	'imageUploadMode'=>array(
		'name'=>'Image Upload Mode',
		'type'=>'select',
		'values'=>array('crop', 'pad', 'pad_black'),
		'default'=>'crop',
	),
	'showThumbnails'=>array(
		'name'=>'Show Thumbnails',
		'type'=>'select',
		'values'=>array('yes', 'no'),
		'default'=>'yes',
	),
);
?>