<?php

require_once('includes/content/blog2/functions.php');

$blog_entries    = DB_PREFIX . 'pico_blog_entries';

$additional_info = $db->result('SELECT `additional_info` FROM `'.DB_CONTENT.'` WHERE `instance_id`=?', $instance_id);
$settings        = unserialize($additional_info);
if (!is_array($settings)) { $settings = array(); }

if ($settings['hide_on_subpages'] == 1)
{
	global $params;
	if ((isset($params[1])) and (strlen($params[1]) > 0)) { return; }
}

$blog_page     = $db->result('SELECT `page_id` FROM `'.DB_CONTENT_LINKS.'` WHERE `component_id`=?', $settings['blog']);
$blog_alias    = $db->result('SELECT `alias` FROM `'.DB_PAGES_TABLE.'` WHERE `page_id`=?', $blog_page); 
$layout        = $settings['layout'];

// get entries
unset($entries);
$today = mktime(0,0,0, date('m'), date('d'), date('Y'));

$query_vals = array();
$query_str  = 'SELECT * FROM `'.$blog_entries.'` WHERE `component_id`=? AND `published`=? AND `scheduled_date` < ?';
$query_vals[] = $settings['blog'];
$query_vals[] = 1;
$query_vals[] = time();

if ($settings['upcoming'] == 1) {
	$query_str .= ' AND `date` >= ?';
	$query_vals[] = $today;
}
elseif ($settings['past'] == 1) {
	$query_str .= ' AND `date` < ?';
	$query_vals[] = $today;
}

if ($settings['num_entries'] < 0) {
	$query_str .= ' ORDER BY RAND()';
}
elseif ($settings['upcoming'] == 1) {
	$query_str .= ' ORDER BY `date` ASC';
}
else {
	$query_str .= ' ORDER BY `date` DESC';
}

array_unshift($query_vals, $query_str); // put query string as first param

$entries = call_user_func_array(array($db, 'force_multi_assoc'), $query_vals);

$show    = abs($settings['num_entries']); // might be negative for random

$show_tags = trim($settings['show_tags']);
$display_tags = array();
if (strlen($show_tags) > 0) {
	$display_tags = explode(',', $show_tags);
}

if (strlen($settings['title']) > 0) {
	$link = $body->url($blog_alias);
	echo '<div class="main_title"><a href="'.$link.'">'.$settings['title'].'</a></div>';
}

$shown = 0;

if ( (is_array($entries)) and (sizeof($entries) > 0) )
{
	
	foreach ($entries as $e)
	{
		//echo '<pre>'.print_r(, true).'</pre>';
		$entry_tags = unserialize($e['tags']);

		if (sizeof($display_tags) != 0)
		{
			$show_entry = false;
			for ($x = 0; $x < sizeof($entry_tags); $x++) {
				$tag = $entry_tags[$x];
				if (in_array($tag, $display_tags)) {
					$show_entry = true;
					break;
				}
			}
		}
		else
		{
			$show_entry = true;
		}

		if ($show_entry)
		{
			// entry, layout, blog alias, comment layout. no comments in a feed.
			$output = trim(Blog2_ShowEntry($e, $layout, $blog_alias, ''));
			
			echo '<div class="blog_entry">'.$output.'</div>';
			
			$shown++;
			if ($shown == $show) {
				break;
			}
		}
		
	}
}

if ($shown == 0) {
	echo '<p class="no_posts">'.$settings['no_post'].'</p>';
}
?>