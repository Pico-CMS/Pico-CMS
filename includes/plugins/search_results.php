<?php

if (is_file('includes/content/directory/functions.php'))
{
	require_once('includes/content/directory/functions.php');
}

function has_url($results, $url)
{
	if (sizeof($results) > 0)
	{
		foreach ($results as $r)
		{
			if ($r['url'] == $url)
			{
				return true;
			}
		}
	}
	return false;
}

function highlight_text($input, $search)
{
	$input = strip_tags($input);
	$regex = "/.{0,50}($search).{0,50}/i";
	preg_match($regex, $input, $matches);
	
	if (isset($matches[1]))
	{
		$matched = $matches[1];
		$text    = $matches[0];
		list($before, $after) = explode($search, $text);

		$space_before = (substr($before, -1) == ' ') ? TRUE : FALSE;
		$space_after = (substr($after, 0, 1) == ' ') ? TRUE : FALSE;

		$before_words = explode(' ', $before);
		array_shift($before_words);

		$after_words = explode(' ', $after);
		array_pop($after_words);

		$new_phrase = '';
		if (sizeof($before_words) > 0) { $new_phrase .= implode(' ', $before_words); }
		if ($space_before) { $new_phrase .= ' '; }
		$new_phrase .= '<b>' . $matched . '</b>';
		if ($space_after) { $new_phrase .= ' '; }
		if (sizeof($after_words) > 0) { $new_phrase .= implode(' ', $after_words); }

		$new_phrase = trim($new_phrase);

		return $new_phrase;
	}
	else
	{
		return '';
	}
}

global $params;
$search_phrase = strip_tags(urldecode($params[1]));
if (!is_string($search_phrase)) { return; }

if (strlen($search_phrase) < 3) { return; }

echo '<h2>Search results for "'.$search_phrase.'"</h2>';

// go through ckhtml

$all_results = array();

$results = $db->force_multi_assoc('SELECT * FROM `'.DB_CONTENT.'` WHERE `content` LIKE ?', '%'.$search_phrase.'%');
if ( (is_array($results)) and (sizeof($results) > 0) )
{
	foreach ($results as $result)
	{
		$component_info = $db->assoc('SELECT * FROM `'.DB_COMPONENT_TABLE.'` WHERE `component_id`=?', $result['component_id']);
		if ($component_info['folder'] == 'ckhtml')
		{
			if ($component_info['view_setting'] == 0)
			{
				// is a static text component on a page
				$page_id    = $db->result('SELECT `page_id` FROM `'.DB_CONTENT_LINKS.'` WHERE `component_id`=? LIMIT 1', $result['component_id']);
				$page_alias = $db->result('SELECT `alias` FROM `'.DB_PAGES_TABLE.'` WHERE `page_id`=?', $page_id);
				$page_name  = $db->result('SELECT `name` FROM `'.DB_PAGES_TABLE.'` WHERE `page_id`=?', $page_id);
				$url = $body->url($page_alias);
				
				if (has_url($all_results, $url) == false)
				{
					$all_results[] = array(
						'url'=>$url,
						'page_name' => $page_name,
						'content' => $result['content']
					);
				}
			}
			elseif ($component_info['view_setting'] == 1)
			{
				// dynamic component, we need to find the page
				
				$pages = $db->force_multi_assoc('SELECT `page_id` FROM `'.DB_PAGES_TABLE.'`');
				foreach ($pages as $page)
				{
					$instance_id = GenerateInstanceID($result['component_id'], 1, $page['page_id'], null);
					if ($result['instance_id'] == $instance_id)
					{
						$page_alias = $db->result('SELECT `alias` FROM `'.DB_PAGES_TABLE.'` WHERE `page_id`=?', $page['page_id']);
						$page_name  = $db->result('SELECT `name` FROM `'.DB_PAGES_TABLE.'` WHERE `page_id`=?', $page['page_id']);
						
						$ht = highlight_text($result['content'], $search_phrase);
						$url = $body->url($page_alias);
						
						if (strlen($ht) > 0)
						{
							if (has_url($all_results, $url) == false)
							{
								$all_results[] = array(
									'url'=>$url,
									'page_name' => $page_name,
									'content' => $ht
								);
							}
						}
						break;
					}
				}
			}
		}
	}
}

// go through blog
$blog_entries    = DB_PREFIX . 'pico_blog_entries';
$results = $db->force_multi_assoc('SELECT * FROM `'.$blog_entries.'` WHERE `post` LIKE ? AND `published`=?', '%'.$search_phrase.'%', 1);
if ( (is_array($results)) and (sizeof($results) > 0) )
{
	foreach ($results as $result)
	{
		$process = true;
		if ((isset($result['scheduled_date'])) and (time() < $result['scheduled_date'])) {
			$process = false;
		}

		if ($process) {
			$component_info = $db->assoc('SELECT * FROM `'.DB_COMPONENT_TABLE.'` WHERE `component_id`=?', $result['component_id']);
			$page_id        = $db->result('SELECT `page_id` FROM `'.DB_CONTENT_LINKS.'` WHERE `component_id`=? LIMIT 1', $result['component_id']);
			
			if (is_numeric($page_id))
			{
				$page_alias = $db->result('SELECT `alias` FROM `'.DB_PAGES_TABLE.'` WHERE `page_id`=?', $page_id);
				$page_name  = $db->result('SELECT `name` FROM `'.DB_PAGES_TABLE.'` WHERE `page_id`=?', $page_id);
				$url = $body->url($page_alias . '/' . $result['alias']);
				
				if (has_url($all_results, $url) == false)
				{
					$all_results[] = array(
						'url'=> $url,
						'page_name' => $page_name . ' - ' . $result['title'],
						'content' => highlight_text($result['post'], $search_phrase)
					);
				}
			}
		}
	}
}

// go through directory

$directories = $db->force_multi_assoc('SELECT `component_id` FROM `'.DB_COMPONENT_TABLE.'` WHERE `folder`=?', 'directory');
if ( (is_array($directories)) and (sizeof($directories) > 0) )
{
	foreach ($directories as $d)
	{
		$_component_id   = $d['component_id'];
		$directory_table = DB_PREFIX . 'directory_' . $_component_id;
		$additional_info = $db->result('SELECT `additional_info` FROM `'.DB_COMPONENT_TABLE.'` WHERE `component_id`=?', $_component_id);
		$data    = unserialize($additional_info);
		$fields  = $data['fields'];
		$options = $data['options'];
		
		// get what page this directory is on
		$page_id = $db->result('SELECT `page_id` FROM `'.DB_CONTENT_LINKS.'` WHERE `component_id`=? LIMIT 1', $_component_id);
		if (is_numeric($page_id))
		{
			$page_alias = $db->result('SELECT `alias` FROM `'.DB_PAGES_TABLE.'` WHERE `page_id`=?', $page_id);
			$page_name  = $db->result('SELECT `name` FROM `'.DB_PAGES_TABLE.'` WHERE `page_id`=?', $page_id);
			
			if (sizeof($fields) > 0)
			{
				$search_fields = array();
				$title_field = $options['title']; // browse by title and any large text areas

				$search_fields[] = $title_field;

				foreach ($fields as $f)
				{
					if ($f['type'] == 'html')
					{
						$search_fields[] = $f['field_name'];
					}
				}

				// search by $search_fields
				if (sizeof($search_fields) > 0)
				{
					foreach ($search_fields as $field)
					{
						$directory_search_results = $db->force_multi_assoc('SELECT * FROM `'.$directory_table.'` WHERE `'.$field.'` LIKE ?', '%'.$search_phrase.'%');
						if (is_array($directory_search_results))
						{
							foreach ($directory_search_results as $result)
							{
								// see if this field has any output

								$full_output    = trim(DR_ProcessLayout($data['fields'], $result, $options['full_layout'], $options));
								$preview_output = trim(DR_ProcessLayout($data['fields'], $result, $options['prev_layout'], $options));
								$dr_alias       = DR_Alias($result[$title_field]);

								// show the detail page if we have output
								if (strlen($full_output) > 0)
								{
									$url = $body->url($page_alias . '/details/' . $dr_alias . '/' . $result['entry_id']);
									if (has_url($all_results, $url) == false)
									{
										$all_results[] = array(
											'url'=> $url,
											'page_name' => $page_name . ' - ' . $result[$title_field],
											'content' => highlight_text($result[$field], $search_phrase)
										);
									}
								}
								elseif (strlen($preview_output) > 0) // else show the page that summarizes it if we have output
								{
									$url = $body->url($page_alias);
									if (has_url($all_results, $url) == false)
									{
										$all_results[] = array(
											'url'=> $url,
											'page_name' => $page_name,
											'content' => highlight_text($result[$field], $search_phrase)
										);
									}
								}
							}
						}
					}
				}
			}
		}
	}
}

// go thru shopping cart
$item_table = DB_PREFIX . 'pico_cart_items';
$items = $db->force_multi_assoc("SELECT * FROM `$item_table` WHERE `title` LIKE ? OR `description` LIKE ?", "%$search_phrase%", "%$search_phrase%");
if (is_array($items)) {
	require_once('includes/content/shopping_cart/functions.php');
	foreach ($items as $item) {

		$page_id    = $db->result('SELECT `page_id` FROM `'.DB_CONTENT_LINKS.'` WHERE `component_id`=? LIMIT 1', $item['component_id']);
		$page_name  = $db->result('SELECT `name` FROM `'.DB_PAGES_TABLE.'` WHERE `page_id`=?', $page_id);
		$url        = Cart_GetItemLink($item['entry_id']);

		$highlight_text = highlight_text($item['title'], $search_phrase);
		if (empty($highlight_text)) { $highlight_text = highlight_text($item['description'], $search_phrase); }

		$all_results[] = array(
			'url'=> $url,
			'page_name' => $page_name . ' - ' . $item['title'],
			'content' => $highlight_text
		);
	}
}

if (sizeof($all_results) > 0)
{
	$results = (sizeof($all_results) == 1) ? 'result' : 'results';
	echo '<p class="found_results">Found ' . sizeof($all_results) . ' ' . $results.'</p>';
	
	foreach ($all_results as $result)
	{
		$content = strip_tags($result['content'], '<b>');
		
		$words = explode(' ', $content);
		while (sizeof($words) > 25)
		{
			array_pop($words);
		}
		
		$blurb = implode(' ', $words);
	
		echo '<div class="search_result">
			<div class="title"><a href="'.$result['url'].'">'.$result['page_name'].'</a></div>
			<div class="blurb">'.$blurb.'...</div>
		</div>';
	}
}

?>
