<?php

require_once('includes/content/blog2_nav/functions.php');
$options = $db->result('SELECT `additional_info` FROM `'.DB_CONTENT.'` WHERE `instance_id`=?', $instance_id);
$settings = unserialize($options);
if (!is_array($settings)) { $settings = array(); }

$sections = array(
	'this_month',
	'archives',
	'categories',
	'tags',
	'all',
	'latest_posts',
	'yearly',
	'author',
	'future',
	'search',
);

$show = array();

foreach ($sections as $section)
{
	if ($settings[$section] == 'enabled')
	{
		$show[$section] = $settings[$section . '_position'];
	}
}

asort($show, SORT_NUMERIC);

if (sizeof($show) > 0)
{
	foreach ($show as $section=>$position)
	{
		Blognav2_ShowSection($settings['blog'], $section, $settings[$section . '_label'], $settings);
	}
}
?>