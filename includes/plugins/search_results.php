<?php
if (is_file('includes/content/directory/functions.php'))
{
	require_once('includes/content/directory/functions.php');
}

function has_url(&$results, $url)
{
	if (sizeof($results) > 0)
	{
		foreach ($results as &$r)
		{
			if ($r['url'] == $url)
			{
				$r['times_matched']++;
				return true;
			}
		}
	}
	return false;
}

function sort_search_results($a, $b)
{
	if ($a['times_matched'] > $b['times_matched'])
	{
		return -1;
	}
	elseif ($b['times_matched'] > $a['times_matched'])
	{
		return 1;
	}
	else
	{
		return 0;
	}
}

function boldify($input, $search)
{
	$return = preg_replace("/($search)/i", "<b>$1</b>", $input);
	return $return;
}

function highlight_text($input, $search)
{
	$input = strip_tags($input);
	$regex = "/(.{0,50}$search.{0,50})/i";
	preg_match($regex, $input, $matches);
	
	if (isset($matches[1]))
	{
		$matched = $matches[1];
		$words = explode(' ', $matched);
		
		if (sizeof($words) > 1)
		{
			// get rid of potentially partial words
			$check = array_pop($words);
			
			// make sure we didnt pop off the search word
			if (stristr($check, $search))
			{
				array_push($words, $check);
			}
			
			$check = array_shift($words);
			// make sure we didnt shift off the search word
			if (stristr($check, $search))
			{
				array_unshift($words, $check);
			}
			
			$new_phrase = implode(' ', $words);
			$return_var = preg_replace("/($search)/i", "<b>$1</b>", $new_phrase);
			return $return_var;
		}
		else
		{
			return '';
		}
	}
	else
	{
		return '';
	}
}

global $params;
$search_phrase = strip_tags(urldecode($params[1]));
$orig_search   = $search_phrase;
if (!is_string($search_phrase)) { return; }

if (strlen($search_phrase) < 3) { return; }

echo '<h2>Search results for "'.$search_phrase.'"</h2>';

$search_words = explode(' ', $search_phrase);

$all_results = array();

foreach ($search_words as $word)
{
	$search_phrase = $word;
	// go through ckhtml
	
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
							'content' => $result['content'],
							'times_matched' => 1
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
							
							$ht = $result['content'];
							$url = $body->url($page_alias);
							
							if (strlen($ht) > 0)
							{
								if (has_url($all_results, $url) == false)
								{
									$all_results[] = array(
										'url'=>$url,
										'page_name' => $page_name,
										'content' => $ht,
										'times_matched' => 1
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
	$results = $db->force_multi_assoc('SELECT * FROM `'.$blog_entries.'` WHERE `post` LIKE ?', '%'.$search_phrase.'%');
	if ( (is_array($results)) and (sizeof($results) > 0) )
	{
		foreach ($results as $result)
		{
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
						'content' => $result['post'],
						'times_matched' => 1
					);
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
			$_component_id = $d['component_id'];
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
				
				//echo '<pre>'.print_r($fields, true).'</pre>';
				//echo '<pre>'.print_r($options, true).'</pre>';
				
				if (sizeof($fields) > 0)
				{
					$search_fields = array();
					$title_field = $options['title']; // browse by title and any large text areas
					foreach ($fields as $f)
					{
						if ($f['type'] == 'html')
						{
							$search_fields[] = $f['field_name'];
						}
					}
					
					// search by title
					$directory_search_results = $db->force_multi_assoc('SELECT * FROM `'.$directory_table.'` WHERE `'.$title_field.'` LIKE ?', '%'.$search_phrase.'%');
					if ( (is_array($directory_search_results)) and (sizeof($directory_search_results) > 0) )
					{
						foreach ($directory_search_results as $result)
						{
							// see if this field has any output
							$output = trim(DR_ProcessLayout($data['fields'], $result, $data['options']['prev_layout'], $data['options']));
							if (strlen($output) > 0)
							{
								$tval = $result[$title_field];
								$tval = strtolower($tval);
								$tval = preg_replace('/[^\w\d\s]/', '', $tval);
								$tval = str_replace(' ', '-', $tval);
								
								$url = $body->url($page_alias . '/details/' . $tval . '/' . $result['entry_id']);
								if (has_url($all_results, $url) == false)
								{
									$all_results[] = array(
										'url'=> $url,
										'page_name' => $page_name . ' - ' . $result[$title_field],
										'content' => $result[$title_field],
										'times_matched' => 1
									);
								}
							}
						}
					}
					
					// search by $search_fields
					if (sizeof($search_fields) > 0)
					{
						foreach ($search_fields as $field)
						{
							$directory_search_results = $db->force_multi_assoc('SELECT * FROM `'.$directory_table.'` WHERE `'.$field.'` LIKE ?', '%'.$search_phrase.'%');
							if ( (is_array($directory_search_results)) and (sizeof($directory_search_results) > 0) )
							{
								foreach ($directory_search_results as $result)
								{
									// see if this field has any output
									$output = trim(DR_ProcessLayout($data['fields'], $result, $data['options']['prev_layout'], $data['options']));
									if (strlen($output) > 0)
									{
										$tval = $result[$title_field];
										$tval = strtolower($tval);
										$tval = preg_replace('/[^\w\d\s]/', '', $tval);
										$tval = str_replace(' ', '-', $tval);
										
										$url = $body->url($page_alias . '/details/' . $tval . '/' . $result['entry_id']);
										if (has_url($all_results, $url) == false)
										{
											$all_results[] = array(
												'url'=> $url,
												'page_name' => $page_name . ' - ' . $result[$title_field],
												'content' => $result[$field],
												'times_matched' => 1
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
}

usort($all_results, 'sort_search_results');

// sort the results

if (sizeof($all_results) > 0)
{
	
	$output = '';
	$num_results = 0;
	
	foreach ($all_results as $result)
	{
		$content = $result['content'];
		$content = strip_tags($content);
		
		$disp = array();
		
		foreach ($search_words as $search_word)
		{
			//$content = str_ireplace($search_word, '<b>'.$search_word.'</b>', $content);
			$content = boldify($content, $search_word);
		} // search words are now bold
		
		// we only want up to 5 words before and after each found word
		
		$words = explode(' ', $content);
		$highlighted_words = array(); // find each highlighted word and save its "index"

		for ($x = 0; $x < sizeof($words); $x++)
		{
			$word = $words[$x];
			if (substr($word, 0, 3) == '<b>')
			{
				$highlighted_words[] = $x;
			}
		}
		
		// now go thru each word again, see if its within range of a highlighted_word "index", and add it to the result string
		
		$result_string = '';
		
		for ($x = 0; $x < sizeof($words); $x++)
		{
			foreach ($highlighted_words as $index)
			{
				if (abs($index - $x) <= 3)
				{
					$word = $words[$x];
					$result_string .= $word . ' ';
					break;
				}
				
				
				if ($x - $index == 3)
				{
					$result_string .= '...';
				}
			}
		}
		
		if (strlen($result_string) > 0)
		{
			$num_results++;
			$output .= '<div class="result">
				<div class="title"><a href="'.$result['url'].'">'.$result['page_name'].'</a></div>
				<div class="blurb">...'.$result_string.'...</div>
			</div>';
		
		}
	}
	
	$results = ($num_results == 1) ? 'result' : 'results';
	echo 'Found ' . $num_results. ' ' . $results;
	
	echo $output;
}


?>
