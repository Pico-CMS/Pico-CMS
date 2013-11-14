<?php

require_once('includes/content/blog2/functions.php');

$blog_entries    = DB_PREFIX . 'pico_blog_entries';
$blog_categories = DB_PREFIX . 'pico_blog_categories';
$blog_comments   = DB_PREFIX . 'pico_blog_comments';

global $params;

// OPTIONS ===========================================

$settings = Blog2_GetSettings($component_id);

$layouts['full']  = $settings['full_layout'];
$layouts['short'] = $settings['short_layout'];
$start_entries    = 0; // where each page will start
$bottom_nav       = '';
$comment_layout   = trim($settings['comment_layout']);

if (!is_numeric($show)) { $show = 3; }

// END: OPTIONS ======================================

if ($params[1] == 'tag') // tags, category, etc
{
	$tag     = urldecode($params[2]);
	$entries = Blog2_FindPostsByTag($component_id, $tag);
	$tag     = str_replace('-', ' ', $tag);

	$num_entries        = $settings['section_show']['tags'];
	$show_layout        = $settings['section_layout']['tags'];
	$page_param_counter = 3;
	
	//$body->title = $tag . ' - ' .  ( (defined('SITE_TITLE')) ? SITE_TITLE : $body->title);
	$body->set_title(1, ucwords($tag));
	echo '<h2>Entries tagged "'.$tag.'"</h2>';
}
elseif ($params[1] == 'category')
{
	$cat_alias = $params[2];
	$category_id = ($cat_alias == 'uncategorized') ? 0 : $db->result('SELECT `category_id` FROM `'.$blog_categories.'` WHERE `alias`=? AND `component_id`=?', $cat_alias, $component_id);
	
	if (!is_numeric($category_id))
	{
		echo 'Invalid category: ' . $cat_alias;
		return;
	}
	$category_name = Blog2_GetCategory($category_id);
	$entries       = Blog2_FindPostsByCategory($component_id, $category_id);
	
	$num_entries        = $settings['section_show']['categories'];
	$show_layout        = $settings['section_layout']['categories'];
	$page_param_counter = 3;
	
	//$body->title = $category_name . ' - ' .  ( (defined('SITE_TITLE')) ? SITE_TITLE : $body->title);
	$body->set_title(1, ucwords($category_name));
	echo '<h2>'.$category_name.'</h2>';
}
elseif ($params[1] == 'date')
{
	$year  = $params[2];
	$month = $params[3];
	
	$start = mktime(0,0,0, $month, 1, $year);
	
	$num_entries        = $settings['section_show']['archives'];
	$show_layout        = $settings['section_layout']['archives'];
	$entries            = Blog2_FindPostsByDate($component_id, $year, $month);
	$page_param_counter = 4;
	
	//$body->title = date('F Y', $start) . ' - ' .  ( (defined('SITE_TITLE')) ? SITE_TITLE : $body->title);
	$body->set_title(1, date('F Y', $start));
	echo '<h2>'.date('F Y', $start).'</h2>';
}
elseif ($params[1] == 'author')
{
	$num_entries        = $settings['section_show']['author'];
	$show_layout        = $settings['section_layout']['author'];
	$author             = urldecode($params[2]);
	$entries            = Blog2_FindPostsByAuthor($component_id, $author);
	$page_param_counter = 3;
	
	//$body->title = $author . ' - ' .  ( (defined('SITE_TITLE')) ? SITE_TITLE : $body->title);
	$body->set_title(1, $author);
	echo '<h2>'.$author.'</h2>';
}
elseif ($params[1] == 'year')
{
	if ($settings['section_layout']['yearly'] == 'month') 
	{
		$year = $params[2];
		$start = mktime(0,0,0, 1, 1, $year);
		$end   = mktime(23,59,59, 12, 31, $year);

		echo '<h1 class="blog-yearly">'.$year.'</h1>';

		$entries = $db->force_multi_assoc('SELECT * FROM `'.$blog_entries.'` WHERE `date`>=? AND `date`<=? AND `component_id`=? AND `published`=1 ORDER BY `date` ASC', $start, $end, $component_id);
		$last_mo = '';
		if (sizeof($entries) > 0) {
			echo '<ul class="datelist">';
			foreach ($entries as $entry) {
				$mo = date('m', $entry['date']);
				if ($mo != $last_mo) {
					$last_mo = $mo;
					$month   = date('F', $entry['date']);
					$year    = date('Y', $entry['date']);

					$link = $body->url(CURRENT_ALIAS  . "/date/$year/$mo");

					echo '<li><a href="'.$link.'">'.$month.'</a></li>';
				}
			}
			echo '</ul>';
		}
		return;
	}
	else
	{
		$show_layout        = $settings['section_layout']['archives'];
		$num_entries        = $settings['section_show']['archives'];
		$year               = $params[2];
		$entries            = Blog2_FindPostsByYear($component_id, $year);
		$page_param_counter = 3;
		
		$body->set_title(1, $year);
		echo '<h2>'.$year.'</h2>';
	}
}
elseif ($params[1] == 'feed')
{
	$blog_title = $db->result('SELECT `description` FROM `'.DB_COMPONENT_TABLE.'` WHERE `component_id`=?', $component_id);
	$rss        = Blog2_ShowRssFeed($component_id, $blog_title);
	define('STATIC_HTML', $rss);
	return;
}
elseif ($params[1] == 'search')
{
	$phrase  = urldecode($params[2]);
	if (strlen($phrase) < 3) {
		echo '<p class="no_results">No search results found</p>';
		return;
	}
	$entries = Blog2_FindPostsBySearchPhrase($component_id, $phrase);

	$show_layout = $settings['section_layout']['search'];
	$num_entries = $settings['section_show']['search'];

	$page_param_counter = 3;

	echo '<h2>Search Results for: '.$phrase.'</h2>';
	if (sizeof($entries) == 0) {
		echo '<p class="no_results">No search results found</p>';
	}
}
elseif ($params[1] == 'blog-preview')
{
	// get all entries
	
	$entries     = $db->force_multi_assoc('SELECT * FROM `'.$blog_entries.'` WHERE `post_id` < 0 LIMIT 1');
	$show_layout = 'full';
	$num_entries = 1;
	$page_param_counter = 1;
}
elseif ((isset($params[1])) and ($params[1] != 'page'))
{
	// see if we have a blog with this story
	$entries = $db->force_multi_assoc('SELECT * FROM `'.$blog_entries.'` WHERE `component_id`=? AND `published`=1 AND `alias`=? AND `scheduled_date` < ? LIMIT 1', $component_id, $params[1], time());
	$num_entries = 1;
	$show_layout = 'full'; // always full
	$body->set_title(1, $entries[0]['title']);

	$words = explode(' ', strip_tags($entries[0]['post']));
	$desc  = implode(' ', array_slice($words, 0, 50));
	$desc  = str_replace("\n", '', $desc);
	$desc  = str_replace("\r", '', $desc);
	$desc  = trim($desc);

	$body->set_social($entries[0]['title'], $desc);

	$meta_description = strip_tags($entries[0]['post']);
	$words = explode(' ', $meta_description);
	$meta_description = '';

	while ($word = array_shift($words))
	{
		if (strlen($meta_description . $word) < 160)
		{
			$meta_description .= $word . ' ';
		}
		else
		{
			break;
		}
	}

	$meta_description = trim($meta_description);
	$body->meta_description($meta_description);
}
else
{
	// get all entries
	
	$desc = $db->result('SELECT  `description` FROM `'.DB_COMPONENT_TABLE.'` WHERE `component_id`=?', $component_id);
	//echo '<h2>'.$desc.'</h2>';

	//echo '<pre>'.print_r($settings['display_mode'], true).'</pre>';
	
	$entries     = $db->force_multi_assoc('SELECT * FROM `'.$blog_entries.'` WHERE `component_id`=? AND `published`=1 AND `scheduled_date` < ?' . Blog2_GetSettingsSort($component_id, 'main'), $component_id, time());
	$show_layout = $settings['section_layout']['main'];
	$num_entries = $settings['section_show']['main'];
	$page_param_counter = 1;
}

// for pagination

if (!isset($show_layout)) { $show_layout == 'full'; } // default layout
if (!is_numeric($num_entries)) { $num_entries = 3; }

$ppc      = $page_param_counter;
$page_num = ($params[$ppc] == 'page') ? ($params[$ppc+1]) - 1: 0;
$limit    = $num_entries;
$start    = $page_num * $limit;

$total_entries = sizeof($entries);
// output found entries to screen
if ( (is_array($entries)) and (sizeof($entries) > 0) )
{
	$entries = array_slice($entries, $start, $limit);
	foreach ($entries as $e)
	{
		$output = Blog2_ShowEntry($e, $layouts[$show_layout], CURRENT_ALIAS, $comment_layout);
		echo '<div class="blog_entry">'.$output.'</div>';
	}
}

// show back/next as needed

if (($settings['show_bottom_nav'] == 1) and ($total_entries > $limit))
{
	// check for previous
	
	// get base url
	$parts = array();
	for ($x = 0; $x < $ppc; $x++)
	{
		$parts[] = $params[$x];
	}

	if (sizeof($parts) == 0) {
		$parts[] = CURRENT_ALIAS;
	}
	
	$parts[] = 'page';
	
	$base_url = implode('/', $parts);
	
	$end = $start + $limit;
	
	$bottom_nav = '<div class="bottom-nav">';
	if ($end < $total_entries)
	{
		// show next
		$bottom_nav .= '<div class="older_posts"><a href="'.$body->url($base_url . '/' . ($page_num+2)).'">&laquo; Older Posts</a></div>';
	}
	if ($start > 0)
	{
		// show back
		$bottom_nav .= '<div class="older_posts"><a href="'.$body->url($base_url . '/' . ($page_num)).'">Newer Posts &raquo;</a></div>';
	}
	$bottom_nav .= '</div>';
	
	echo $bottom_nav;
}


?>