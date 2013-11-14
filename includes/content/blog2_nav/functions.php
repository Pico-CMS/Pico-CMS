<?php

if (!function_exists('in_arrayi'))
{
	function in_arrayi($needle, $haystack) {
		return in_array(strtolower($needle), array_map('strtolower', $haystack));
	}
}

function Blognav2_ShowSection($blog_id, $section, $label, $settings)
{
	// get the blog page
	global $db, $body, $params;
	
	$blog_options    = DB_PREFIX . 'pico_blog_options';
	$blog_entries    = DB_PREFIX . 'pico_blog_entries';
	$blog_categories = DB_PREFIX . 'pico_blog_categories';
	$blog_comments   = DB_PREFIX . 'pico_blog_comments';
	
	$section_html = '';
	
	$options    = $db->assoc('SELECT * FROM `'.$blog_options.'` WHERE `component_id`=?', $blog_id);
	$blog_page  = $db->result('SELECT `page_id` FROM `'.DB_CONTENT_LINKS.'` WHERE `component_id`=?', $blog_id);
	$blog_alias = $db->result('SELECT `alias` FROM `'.DB_PAGES_TABLE.'` WHERE `page_id`=?', $blog_page); 
	
	$section_html .= '<div class="'.$section.'">';
	$section_html .= '<div class="top"></div>';
	$section_html .= '<div class="bg">';
	$section_html .= '<div class="title">'.$label.'</div>';
	
	if ($section == 'all')
	{
		if ($options['hide_expired'] == 1)
		{
			$today = mktime(0,0,0, date('m'), date('d'), date('Y'));
			$entries = $db->force_multi_assoc('SELECT * FROM `'.$blog_entries.'` WHERE `component_id`=? AND `date` >= ? AND `published`=? AND `scheduled_date` <= ? ORDER BY `date` DESC', $blog_id, $today, 1, time());
		}
		else
		{
			$entries = $db->force_multi_assoc('SELECT * FROM `'.$blog_entries.'` WHERE `component_id`=? AND `published`=? AND `scheduled_date` <= ? ORDER BY `date` DESC', $blog_id, 1, time());
		}
		
		if ( (is_array($entries)) and (sizeof($entries) > 0) )
		{
			if ($settings['all_view'] == 'dropdown')
			{
				$section_html .= '<select onchange="Blog_ShowLink(this)">';
				$section_html .= '<option value=""></option>';
				foreach ($entries as $entry)
				{
					$link = $body->url($blog_alias . '/' . $entry['alias']);
					$section_html .= '<option value="'.$link.'">'.$entry['title'].'</option>';
				}
				$section_html .= '</select>';
			}
			else
			{
				$section_html .= '<ul>';
				foreach ($entries as $entry)
				{
					$active = ($params[1] == $entry['alias']) ? 'class="blognav_active"' : '';
					$link = $body->url($blog_alias . '/' . $entry['alias']);
					$section_html .= '<li '.$active.'><a href="'.$link.'">'.$entry['title'].'</a></li>';
				}
				$section_html .= '</ul>';
			}
		}
	}
	elseif ($section == 'this_month')
	{
		if ($options['hide_expired'] == 1)
		{
			$timestamp = time();
		}
		else
		{
			$timestamp = mktime(0,0,0, date('n'), 1, date('Y'));
		}
		
		$entries = $db->force_multi_assoc('SELECT * FROM `'.$blog_entries.'` WHERE `component_id`=? AND `date` >= ? AND `published`=? AND `scheduled_date` <= ? ORDER BY `date` DESC', $blog_id, $timestamp, 1, time());
		if ( (is_array($entries)) and (sizeof($entries) > 0) )
		{
			if ($settings['this_month_view'] == 'dropdown')
			{
				$section_html .= '<select onchange="Blog_ShowLink(this)">';
				$section_html .= '<option value=""></option>';
				foreach ($entries as $entry)
				{
					$link = $body->url($blog_alias . '/' . $entry['alias']);
					$section_html .= '<option value="'.$link.'">'.$entry['title'].'</option>';
				}
				$section_html .= '</select>';
			}
			else
			{
				$section_html .= '<ul>';
				foreach ($entries as $entry)
				{
					$link = $body->url($blog_alias . '/' . $entry['alias']);
					$section_html .= '<li><a href="'.$link.'">'.$entry['title'].'</a></li>';
				}
				$section_html .= '</ul>';
			}
		}
	}
	elseif ($section == 'categories')
	{
		$categories = $db->force_multi_assoc('SELECT * FROM `'.$blog_categories.'` WHERE `component_id`=? ORDER BY `title` ASC', $blog_id);
		
		$all_categories = array();
		if ( (is_array($categories)) and (sizeof($categories) > 0) )
		{
			foreach ($categories as $entry)
			{
				$check = sizeof(Blog2_FindPostsByCategory($blog_id, $entry['category_id']));

				//$check = $db->result('SELECT count(*) FROM `'.$blog_entries.'` WHERE `category`=? AND `published`=1 AND `scheduled_date` <= ?', $entry['category_id'], time());
				if ($check > 0)
				{
					$link = $body->url($blog_alias . '/category/' . $entry['alias']);
					$all_categories[] = array(
						'link'=>$link,
						'title'=>$entry['title']
					);
					//$section_html .= '<li><a href="'.$link.'">'.$entry['title'].'</a></li>';
				}
			}
		}
		// see if we have uncategoried...
		
		/*
		$check = $db->result('SELECT count(*) FROM `'.$blog_entries.'` WHERE `category`=0 AND `published`=? AND `scheduled_date` <= ? AND `component_id`=?', 1, time(), $blog_id);
		if ( (int) $check > 0)
		{
			$link = $body->url($blog_alias . '/category/uncategorized');
			$all_categories[] = array(
				'link'=>$link,
				'title'=>'Uncategorized'
			);
		}*/
		
		if ($settings['categories_view'] == 'dropdown')
		{
			$section_html .= '<select onchange="Blog_ShowLink(this)">';
			$section_html .= '<option value="">Choose a category</option>';
			foreach ($all_categories as $entry)
			{
				$link  = $entry['link'];
				$title = $entry['title'];
				$section_html .= '<option value="'.$link.'">'.$title.'</option>';
			}
			$section_html .= '</select>';
		}
		else
		{
			$section_html .= '<ul>';
			foreach ($all_categories as $entry)
			{
				$link  = $entry['link'];
				$title = $entry['title'];
				$section_html .= '<li><a href="'.$link.'">'.$title.'</a></li>';
			}
			$section_html .= '</ul>';
		}
	}
	elseif ($section == 'tags')
	{
		$all_entries = $db->force_multi_assoc('SELECT `tags` FROM `'.$blog_entries.'` WHERE `component_id`=? AND `published`=? AND `scheduled_date` <= ? ORDER BY `date` DESC', $blog_id, 1, time());
		$all_tags    = array();
		$tag_count   = array();
		$max_tag     = 1;
		
		if ( (is_array($all_entries)) and (sizeof($all_entries) > 0) )
		{
			foreach ($all_entries as $entry)
			{
				$tags = unserialize($entry['tags']);
				
				foreach ($tags as $tag)
				{
					$tag = trim($tag);
					$tag = str_replace('#', '', $tag);
					$key = PageNameToAlias($tag);
						
					if (strlen($tag) > 0)
					{
						if (!in_arrayi($tag, $all_tags))
						{
							$all_tags[] = $tag;
							$tag_count[$key] = 1;
						}
						else
						{
							$tag_count[$key]++;
							
							if ($tag_count[$key] > $max_tag)
							{
								$max_tag = $tag_count[$key];
							}
						}
					}
				}
			}
		}
		if (sizeof($all_tags) > 0)
		{
			// LIST EM
			
			$all_tags = asorti($all_tags);
			
			
			if (sizeof($tag_count) > 50)
			{
				// only show top 50
				$top50    = $tag_count;
				arsort($top50, SORT_NUMERIC);
				$top50 = array_slice($top50, 0, 50);
				//$section_html .= '<pre>'.print_r($top50, true).'</pre>';
				$top50 = array_keys($top50);
			}
			else
			{
				$top50 = array_keys($tag_count);
			}
			
			if ($settings['tags_view'] == 'dropdown')
			{
				$section_html .= '<select onchange="Blog_ShowLink(this)">';
				$section_html .= '<option value="">Choose a tag</option>';
				foreach ($all_tags as $tag)
				{
					$key = PageNameToAlias($tag);
					$link = $body->url($blog_alias . '/tag/' . $key);
					$section_html .= '<option value="'.$link.'">'.$tag.' ('.$tag_count[$key].')</option>';
				}
				$section_html .= '</select>';
			}
			elseif ($settings['tags_view'] == 'dynamic')
			{
				foreach ($all_tags as $tag)
				{
					if (in_array(PageNameToAlias($tag), $top50))
					{
						$key   = PageNameToAlias($tag);
						$link  = $body->url($blog_alias . '/tag/' . $key);
						$count = $tag_count[$key];
						
						$p = $count / $max_tag;
						
						$max_font = floor($max_tag / 2);
						if ($max_font > 18) { $max_font = 18; } // max size
						
						$font_size = 10 + floor($max_font * $p);
						
						$section_html .= '<span style="font-size: '.$font_size.'px"><a href="'.$link.'">'.$tag.'</a></span> ';
					}
				}
			}
			else
			{
				$section_html .= '<ul>';
				foreach ($all_tags as $tag)
				{
					$key  = PageNameToAlias($tag);
					$link = $body->url($blog_alias . '/tag/' . $key);
					$section_html .= '<li><a href="'.$link.'">'.$tag.'</a></li>';
				}
				$section_html .= '</ul>';
			}
		}
		
		
	}
	elseif ($section == 'archives')
	{
		$all_entries = $db->force_multi_assoc('SELECT `date` FROM `'.$blog_entries.'` WHERE `component_id`=? AND `published`=? AND `scheduled_date` <= ? ORDER BY `date` DESC', $blog_id, 1, time());
		$include = array();
		if ( (is_array($all_entries)) and (sizeof($all_entries) > 0) )
		{
			foreach ($all_entries as $entry)
			{
				$date = $entry['date'];
				$timestamp = mktime(0,0,0, date('n', $date), 1, date('Y', $date));
				
				if (!in_array($timestamp, $include))
				{
					$include[] = $timestamp;
				}
			}
		}
		// should already be sorted
		if (sizeof($include) > 0)
		{
			if ($settings['archives_view'] == 'dropdown')
			{
				$section_html .= '<select onchange="Blog_ShowLink(this)">';
				$section_html .= '<option value="">Choose a date</option>';
				foreach($include as $date)
				{
					$month = date('n', $date);
					$year  = date('Y', $date);
					$link = $body->url($blog_alias . '/date/' . $year . '/' . $month);
					$section_html .= '<option value="'.$link.'">'.date('F Y', $date).'</option>';
				}
				$section_html .= '</select>';
			}
			else
			{
				$section_html .= '<ul>';
				foreach($include as $date)
				{
					$month = date('n', $date);
					$year  = date('Y', $date);
					$link = $body->url($blog_alias . '/date/' . $year . '/' . $month);
					$section_html .= '<li><a href="'.$link.'">'.date('F Y', $date).'</a></li>';
				}
				$section_html .= '</ul>';
			}
		}
	}
	elseif ($section == 'latest_posts')
	{
		$entries = $db->force_multi_assoc('SELECT * FROM `'.$blog_entries.'` WHERE `component_id`=? AND `published`=1 AND `scheduled_date` <= ? ORDER BY `date` DESC LIMIT 5', $blog_id, time());
		if ( (is_array($entries)) and (sizeof($entries) > 0) )
		{
			if ($settings['latest_posts_view'] == 'dropdown')
			{
				$section_html .= '<select onchange="Blog_ShowLink(this)">';
				$section_html .= '<option value=""></option>';
				foreach ($entries as $entry)
				{
					$link = $body->url($blog_alias . '/' . $entry['alias']);
					$section_html .= '<option value="'.$link.'">'.$entry['title'].'</option>';
				}
				$section_html .= '</select>';
			}
			else
			{
				$section_html .= '<ul>';
				foreach ($entries as $entry)
				{
					$link = $body->url($blog_alias . '/' . $entry['alias']);
					$section_html .= '<li><a href="'.$link.'">'.$entry['title'].'</a></li>';
				}
				$section_html .= '</ul>';
			}
		}
	}
	elseif ($section == 'yearly')
	{
		$all_entries = $db->force_multi_assoc('SELECT `date` FROM `'.$blog_entries.'` WHERE `component_id`=? AND `published`=? AND `scheduled_date` <= ? ORDER BY `date` DESC', $blog_id, 1, time());
		$include = array();
		if ( (is_array($all_entries)) and (sizeof($all_entries) > 0) )
		{
			
			foreach ($all_entries as $entry)
			{
				$date = $entry['date'];
				$timestamp = mktime(0,0,0, 1, 1, date('Y', $date));
				
				if (!in_array($timestamp, $include))
				{
					$include[] = $timestamp;
				}
			}
		}
		// should already be sorted
		if (sizeof($include) > 0)
		{
			if ($settings['yearly_view'] == 'dropdown')
			{
				$section_html .= '<select onchange="Blog_ShowLink(this)">';
				$section_html .= '<option value="">Choose By Year</option>';
				foreach($include as $date)
				{
					$year  = date('Y', $date);
					$link = $body->url($blog_alias . '/year/' . $year);
					$section_html .= '<option value="'.$link.'">'.$year.'</option>';
				}
				$section_html .= '</select>';
			}
			else
			{
				$section_html .= '<ul>';
				foreach($include as $date)
				{
					$year  = date('Y', $date);
					$link = $body->url($blog_alias . '/year/' . $year);
					$section_html .= '<li><a href="'.$link.'">'.date('Y', $date).'</a></li>';
				}
				$section_html .= '</ul>';
			}
		}
	}
	elseif ($section == 'author')
	{
		$authors = $db->force_multi_assoc('SELECT DISTINCT(`author`) as `author` FROM `'.$blog_entries.'` WHERE `component_id`=? AND `published`=? AND `scheduled_date` <= ? ORDER BY `author` ASC', $blog_id, 1, time());
		if (is_array($authors))
		{
			if ($settings['author_view'] == 'dropdown')
			{
				$section_html .= '<select onchange="Blog_ShowLink(this)">';
				$section_html .= '<option value=""></option>';
				foreach ($authors as $a)
				{
					$author = $a['author'];
					$link = $body->url($blog_alias . '/author/' . $author);
					$section_html .= '<option value="'.$link.'">'.$author.'</option>';
				}
				$section_html .= '</select>';
			}
			else
			{
				$section_html .= '<ul>';
				foreach ($authors as $a)
				{
					$author = $a['author'];
					$link = $body->url($blog_alias . '/author/' . $author);
					$section_html .= '<li><a href="'.$link.'">'.$author.'</a></li>';
				}
				$section_html .= '</ul>';
			}
		}
	}
	elseif ($section == 'future')
	{
		$today = mktime(0,0,0, date('m'), date('d'), date('Y'));
		$entries = $db->force_multi_assoc('SELECT * FROM `'.$blog_entries.'` WHERE `component_id`=? AND `published`=1 AND `date`>=? AND `scheduled_date` <= ? ORDER BY `date` ASC', $blog_id, $today, time());
		if ( (is_array($entries)) and (sizeof($entries) > 0) )
		{
			if ($settings['latest_posts_view'] == 'dropdown')
			{
				$section_html .= '<select onchange="Blog_ShowLink(this)">';
				$section_html .= '<option value=""></option>';
				foreach ($entries as $entry)
				{
					$link = $body->url($blog_alias . '/' . $entry['alias']);
					$section_html .= '<option value="'.$link.'">'.$entry['title'].'</option>';
				}
				$section_html .= '</select>';
			}
			else
			{
				$section_html .= '<ul>';
				foreach ($entries as $entry)
				{
					$link = $body->url($blog_alias . '/' . $entry['alias']);
					$section_html .= '<li><a href="'.$link.'">'.$entry['title'].'</a></li>';
				}
				$section_html .= '</ul>';
			}
		}
	}
	elseif ($section == 'search')
	{
		$section_html .= <<<HTML
<form method="post" action="" onsubmit="Blog_Search($blog_id); return false">
	<input type="hidden" id="navsearch_alias_$blog_id" value="$blog_alias" />
	<table border="0" cellpadding="0" cellspacing="0" class="search_table">
	<tr>
		<td class="searchbox"><input type="text" id="navsearch_$blog_id" /></td>
		<td class="searchsubmit"><button onclick="Blog_Search($blog_id); return false">Go</button></td>
	</tr>
	</table>
	<input type="submit" style="display: none; height: 0; width: 0" />
</form>
HTML;
	}
	
	$section_html .= '</div>';
	$section_html .= '<div class="bottom"></div>';
	$section_html .= '</div>';
	echo $section_html;
}
?>