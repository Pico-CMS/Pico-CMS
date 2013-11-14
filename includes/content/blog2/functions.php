<?php

require_once('includes/content/blog2/layout.class.php');

$blog_entries        = DB_PREFIX . 'pico_blog_entries';
$blog_categories     = DB_PREFIX . 'pico_blog_categories';
$blog_comments       = DB_PREFIX . 'pico_blog_comments';
$blog_category_links = DB_PREFIX . 'pico_blog_category_links';

function Blog2_GetSettings($component_id)
{
	if (!isset($GLOBALS['blog_settings_'.$component_id]))
	{
		global $db;
		$data     = $db->result('SELECT `additional_info` FROM `'.DB_COMPONENT_TABLE.'` WHERE `component_id`=?', $component_id);
		$settings = unserialize($data);
		if (!is_array($settings)) { $settings = array(); }

		$GLOBALS['blog_settings_'.$component_id] = $settings;
	}

	return $GLOBALS['blog_settings_'.$component_id];
}

function Blog2_ShowEntry($entry, $layout, $page_alias, $comment_layout = '')
{
	global $body, $db;
	$blog_entries        = DB_PREFIX . 'pico_blog_entries';
	$blog_categories     = DB_PREFIX . 'pico_blog_categories';
	$blog_comments       = DB_PREFIX . 'pico_blog_comments';
	$blog_category_links = DB_PREFIX . 'pico_blog_category_links';

	// get blog settings
	$blog_settings = Blog2_GetSettings($entry['component_id']);
	
	$entry_id = abs($entry['post_id']);
	
	$layout_obj = new Layout($layout);
	$layout_obj->AddVar('id', 'num'); #
	$layout_obj->AddVar('num_tags', 'num'); #
	$layout_obj->AddVar('comments_enabled', 'bool'); #
	$layout_obj->AddVar('title', 'text'); #
	$layout_obj->AddVar('category_name', 'text'); #
	$layout_obj->AddVar('comments', 'text'); #
	$layout_obj->AddVar('tags', 'text'); #
	$layout_obj->AddVar('story', 'text'); #
	$layout_obj->AddVar('sharethis', 'text'); #
	$layout_obj->AddVar('date', 'date'); #
	$layout_obj->AddVar('image', 'image'); #
	$layout_obj->AddVar('link', 'link'); #
	$layout_obj->AddVar('prev', 'link'); #
	$layout_obj->AddVar('next', 'link'); #
	$layout_obj->AddVar('category_link', 'deprecated'); // was link, but making it text for backwards compatibility
	$layout_obj->AddVar('secondary_title', 'text'); #
	$layout_obj->AddVar('related', 'text'); #
	$layout_obj->AddVar('author', 'text'); #
	$layout_obj->AddVar('num_comments', 'num'); #
	$layout_obj->AddVar('caption', 'text'); #
	$layout_obj->AddVar('categories', 'text'); #
	
	// categories ====================
	
	$categories      = array();
	$post_categories = $db->force_multi_assoc('SELECT * FROM `'.$blog_category_links.'` WHERE `post_id`=?', $entry_id);
	$category_links  = array();
	$category_text   = '';

	if (is_array($post_categories))
	{
		foreach ($post_categories as $category)
		{
			$id   = $category['category_id'];
			$name = $db->result('SELECT `title` FROM `'.$blog_categories.'` WHERE `category_id`=?', $id);

			$categories[$id] = $name;
		}	

		natcasesort($categories);

		foreach ($categories as $id => $name)
		{
			$cat_alias = $db->result('SELECT `alias` FROM `'.$blog_categories.'` WHERE `category_id`=?', $id);
			$link = '<a class="category_link" href="'.$body->url($page_alias . '/category/' . $cat_alias).'">'.$name.'</a>';
			$category_links[] = $link;
		}

		$category_text = implode(', ', $category_links);
	}
	
	
	// image ====================
	
	if (strlen($entry['story_image']) > 0)
	{
		$file = $entry['story_image'];
		$full_image = 'includes/storage/blog/images/'.$entry_id.'/'.$file;
		
		if (is_file($full_image))
		{
			$entry['image'] = $full_image;
		}
	}
	
	// tags ====================
	
	$tags = unserialize($entry['tags']);
	if (!is_array($tags)) { $tags = array(); }
	$tag_data = array();

	$tag_list = '';
	$tag_string = '';
	
	if (sizeof($tags) > 0)
	{
		foreach ($tags as $tag)
		{
			$tag = trim($tag);
			if (strlen($tag) > 0)
			{
				$tag_data[] = '<a href="'.$body->url($page_alias . '/tag/'. PageNameToAlias($tag)).'">'.$tag.'</a>';
				$tag_list .= $tag.', ';
			}
		}
		$tag_string = implode(', ', $tag_data);
	}
	
	$entry['story']         = $entry['post']; // fixes for layout
	$entry['id']            = $entry_id; 
	$entry['category_name'] = ''; // deprecated 
	$entry['category_link'] = $category_text; // deprecated 
	$entry['categories']    = $category_text;
	$entry['tags']          = $tag_string; 
	$entry['link']          = $body->url($page_alias . '/' . $entry['alias']);
	$entry['num_tags']      = sizeof($tag_data);
	$entry['comments_enabled'] = (($entry['allow_comments'] == 1) and ($blog_settings['allow_comments'] == 1)) ? TRUE : FALSE;
	$entry['sharethis']        = Pico_Setting('share_this');
	$entry['secondary_title']  = $entry['by_line'];
	$entry['caption']          = $entry['image_caption'];
	
	if ($blog_settings['allow_comments'] != 0)
	{
		// Livefyre comments
		if ($blog_settings['use_livefyre'] == 1)
		{
			$site_id               = $blog_settings['lf_siteid'];
			$entry['comments']     = ($entry['allow_comments'] == 1) ? '<div id="comment_container_'.$entry_id.'">'. Blog2_LiveFyre($entry_id, $tag_list, $site_id) .'</div>' : '';
			$entry['num_comments'] = <<<HTML
<span class="livefyre-commentcount" data-lf-site-id="$site_id" data-lf-article-id="post-$entry_id">0</span>
HTML;
		}
		// Pico comments
		else
		{
			$entry['comments']     = ($entry['allow_comments'] == 1) ? '<div id="comment_container_'.$entry_id.'">'.Blog2_GetComments($entry_id, $comment_layout) . Blog2_CommentForm($entry_id).'</div>' : '';
			$entry['num_comments'] = $db->result('SELECT count(1) FROM `'.$blog_comments.'` WHERE `post_id`=? AND `active`=?', $entry_id, 1);
		}
	}
	else
	{
		$entry['comments'] = '';
		$entry['num_comments'] = '';
	}
	
	// get related posts
	$related = array();
	for ($x = 1; $x <= 3; $x++)
	{
		$_id = $entry['related' . $x];
		if ($_id != 0)
		{
			$related_info = $db->assoc('SELECT * FROM `'.$blog_entries.'` WHERE `post_id`=?', $_id);
			if (($related_info['published'] == 1) and (time() > $related_info['scheduled_date']))
			{
				$related_img = '';
				$file        = $related_info['story_image'];
				$full_image  = 'includes/storage/blog/images/'.$related_info['post_id'].'/'.$file;
		
				if (is_file($full_image))
				{
					$layout_obj->AddVar('related_image_'.$x, 'image'); 
					$entry['related_image_'.$x] = $full_image;
					$related_img = '<span class="related_img">{RELATED_IMAGE_'.$x.',48,36}</span>';
				}

				$related[] = $related_img . '<a href="'.$body->url($page_alias . '/' . $related_info['alias']).'">'.$related_info['title'].'</a>';
			}
		}
	}
	
	$entry['related'] = implode(', ', $related);

	// need prev and next
	$prev = $db->assoc('SELECT * FROM `'.$blog_entries.'` WHERE `component_id`=? AND `published`=1 AND `date`<? ORDER BY `date` DESC LIMIT 1', $entry['component_id'], $entry['date']);
	$next = $db->assoc('SELECT * FROM `'.$blog_entries.'` WHERE `component_id`=? AND `published`=1 AND `date`>? ORDER BY `date` ASC LIMIT 1', $entry['component_id'], $entry['date']);
	
	if (is_array($prev)) {
		$prev_link  = $body->url($page_alias.'/'.$prev['alias']);
		$entry['prev'] = $prev_link;
	}

	if (is_array($next)) {
		$next_link  = $body->url($page_alias.'/'.$next['alias']);
		$entry['next'] = $next_link;
	}
	
	return $layout_obj->Output($entry);
}

function Blog2_LiveFyre($entry_id, $tag_list, $site_id)
{
	global $db, $body;

	$tag_list = trim($tag_list, ', ');

	if (!isset($GLOBAS['livefyre_added']))
	{
		$body->add_head(<<<HTML
<script type="text/javascript" src="//zor.livefyre.com/wjs/v3.0/javascripts/livefyre.js"></script>
<script type="text/javascript" src="//zor.livefyre.com/wjs/v1.0/javascripts/CommentCount.js"></script>
HTML
);
		$GLOBAS['livefyre_added'] = true;
	}

	$blog_entries    = DB_PREFIX . 'pico_blog_entries';
	$blog_entry = $db->assoc('SELECT * FROM `'.$blog_entries.'` WHERE `post_id`=?', $entry_id);

	$title = addslashes($blog_entry['title']);

	$full_url = 'http://'.$_SERVER['SERVER_NAME'].$body->url(CURRENT_ALIAS.'/'.$blog_entry['alias']);
	$output = <<<EOD
<!-- START: Livefyre Embed -->
<div id="livefyre-comments-$entry_id"></div>
<script type="text/javascript">
(function () {
    var articleId = 'post-$entry_id';
    fyre.conv.load
    (
    	{}, 
	    [{
	        el: 'livefyre-comments-$entry_id',
	        network: "livefyre.com",
	        siteId: "$site_id",
	        articleId: articleId,
	        signed: false,
	        collectionMeta: 
	        {
	            articleId: articleId,
	            url: '$full_url',
	            tags: '$tag_list',
	            title: '$title'
	        }
	    }],
    	function() {}
    );
}());
</script>
<!-- END: Livefyre Embed -->
EOD;
	
	return $output;	
}

function Blog2_GetComments($entry_id, $comment_layout, $parent = 0, $all = false)
{
	global $db, $body;
	$blog_comments = DB_PREFIX . 'pico_blog_comments';
	
	if ($all)
	{
		$user_comments = $db->force_multi_assoc('SELECT * FROM `'.$blog_comments.'` WHERE `post_id`=? AND `parent`=? ORDER BY `date` DESC', $entry_id, $parent);
	}
	else
	{
		$user_comments = $db->force_multi_assoc('SELECT * FROM `'.$blog_comments.'` WHERE `post_id`=? AND `active`=? AND `parent`=? ORDER BY `date` DESC', $entry_id, 1, $parent);
	}
	
	$output = '';
	
	if ( (is_array($user_comments)) and (sizeof($user_comments) > 0) ) 
	{
		foreach ($user_comments as $comment)
		{
			$layout_obj = new Layout($comment_layout);
			$layout_obj->AddVar('name', 'text');
			$layout_obj->AddVar('message', 'text');
			$layout_obj->AddVar('date', 'date');
			$layout_obj->AddVar('reply', 'text');
			$layout_obj->AddVar('email', 'text');
			
			// name ==========================.
			
			$url = $comment['url'];
			if (sizeof($url) > 0)
			{
				if (substr($url, 0, 7) != 'http://') { $url = 'http://'.$url; }
				$name = '<a href="'.$url.'" target="_blank">'.$comment['name'].'</a>';
			}
			else
			{
				$name = $comment['name'];
			}
			
			$ld = array();
			$ld['name']    = $name;
			$ld['date']    = $comment['date'];
			$ld['message'] = nl2br($comment['message']);
			$ld['reply']   = '<span class="click reply" onclick="Blog_ShowCommentForm(this, '.$entry_id.', '.$comment['comment_id'].')">Reply</span>';
			$ld['email']   = (USER_ACCESS > 2) ? $comment['email'] : '';
			
			$output .= '<div class="comment_entry">';
			$output .= '<div id="blogcomment_'.$comment['comment_id'].'"></div>';
			
			$output .= $layout_obj->Output($ld);
			$output .= Blog2_GetComments($entry_id, $comment_layout, $comment['comment_id'], $all);
			// see if there are any children
			
			$output .= '</div>';
		}
	}
	
	return $output;
}

function Blog2_get_category_drop($name, $component_id, $selected = 0)
{
	$blog_categories = DB_PREFIX . 'pico_blog_categories';
	global $db;
	$return = '<select name="'.$name.'" id="blog_cat_'.$component_id.'">';
	
	$categories = $db->force_multi_assoc('SELECT * FROM `'.$blog_categories.'` WHERE `component_id`=? ORDER BY `title` ASC', $component_id);
	$end_categories = array();
	$end_categories[0] = 'None';
	
	if ( (is_array($categories)) and (sizeof($categories) > 0) )
	{
		foreach($categories as $cat)
		{
			$end_categories[$cat['category_id']] = $cat['title'];
		}
	}
	
	foreach ($end_categories as $id=>$val)
	{
		$seltext = ($selected == $id) ? 'selected="selected"' : '';
		$return .= '<option value="'.$id.'" '.$seltext.'>'.$val.'</option>';
	}
	$return .= '</select>';
	return $return;
}

function Blog2_GetCategory($category_id)
{
	if ($category_id == 0) { return 'Uncategorized'; }
	global $db;
	$blog_categories = DB_PREFIX . 'pico_blog_categories';
	$category = $db->result('SELECT `title` FROM `'.$blog_categories.'` WHERE `category_id`=?', $category_id);
	return $category;
}

function Blog2_get_blog_comments($post_id, $layout, $parent = 0, $all = false)
{
	global $db, $body;
	$blog_comments = DB_PREFIX . 'pico_blog_comments';
	
	if ($all)
	{
		$user_comments = $db->force_multi_assoc('SELECT * FROM `'.$blog_comments.'` WHERE `post_id`=? AND `parent`=? ORDER BY `date` DESC', $post_id, $parent);
	}
	else
	{
		$user_comments = $db->force_multi_assoc('SELECT * FROM `'.$blog_comments.'` WHERE `post_id`=? AND `active`=? AND `parent`=? ORDER BY `date` DESC', $post_id, 1, $parent);
	}
	$output = '';
	
	if ( (is_array($user_comments)) and (sizeof($user_comments) > 0) ) 
	{
		foreach ($user_comments as $comment)
		{
			$url = $comment['url'];
			if (sizeof($url) > 0)
			{
				if (substr($url, 0, 7) != 'http://') { $url = 'http://'.$url; }
				$name = '<a href="'.$url.'" target="_blank">'.$comment['name'].'</a>';
			}
			else
			{
				$name = $comment['name'];
			}
			
			$date    = date('m/d/y', $comment['date']);
			$message = nl2br($comment['message']);
			
			if ($comment['active'] == 0)
			{
				$message = '<span class="inactive">'.$message.'</span>';
			}
			
			
			$_entry = $layout;
			$_entry = str_replace('NAME', $name, $_entry);
			
			preg_match('/\{DATE,([^\}]+)\}/', $_entry, $matches);
			if (isset($matches[1]))
			{
				$format = $matches[1];
				$date   = date($format, $comment['date']);
				$_entry = preg_replace('/\{DATE,[^\}]+}/', $date, $_entry);
			}
			
			$_entry = str_replace('DATE', $date, $_entry);
			$_entry = str_replace('MESSAGE', $message, $_entry);
			$_entry = str_replace('REPLY', '<span class="click" onclick="Blog_Reply('.$post_id.', '.$comment['comment_id'].')">Reply</span>', $_entry);
			
			$output .= '<div class="comment_entry">';
			$output .= '<div id="blogcomment_'.$comment['comment_id'].'"></div>';
			if (USER_ACCESS > 2)
			{
				$actions = '<img class="icon click" title="Delete Comment" onclick="Blog2_DeleteComment('.$post_id.', '.$comment['comment_id'].')" src="'.$body->url('includes/icons/delete.png').'" />';
				if ($comment['active'] == 0)
				{
					$actions .= '<img class="icon click" title="Approve Comment" onclick="Blog2_ApproveComment('.$post_id.', '.$comment['comment_id'].')" src="'.$body->url('includes/icons/ok.png').'" />';
				}
				$_entry = str_replace('EMAIL', $comment['email'], $_entry);
				$_entry = str_replace('ACTIONS', $actions, $_entry);
				//$output .= '<img class="icon click" onclick="Blog_DeleteComment('.$post_id.', '.$comment['comment_id'].')" src="'.$body->url('includes/icons/delete.png').'" />';
			}
			$output .= $_entry;
			
			// see if there are any children
			$output .= Blog2_get_blog_comments($post_id, $layout, $comment['comment_id']);
			
			$output .= '</div>';
		}
	}
	return $output;
}

function Blog2_FindPostsBySearchPhrase($component_id, $phrase)
{
	global $db;
	$tag = strtolower($tag);
	$blog_entries = DB_PREFIX . 'pico_blog_entries';

	$search = '%' .  $phrase . '%';
	
	$entries = $db->force_multi_assoc('SELECT * FROM `'.$blog_entries.'` WHERE (`component_id`=? AND `published`=1 AND `scheduled_date` < ?) AND ((`post` LIKE ?) OR (`title` LIKE ?))' . Blog2_GetSettingsSort($component_id, 'search'), 
		$component_id, time(), $search, $search);

	// now lets go thru and make sure what was found was not found in html source
	$return_entries = array();
	if (is_array($entries)) {
		foreach ($entries as $entry) {
			if (stristr($entry['title'], $phrase)) {
				$return_entries[] = $entry;
			}
			elseif (stristr(strip_tags($entry['post']), $phrase)) {
				$return_entries[] = $entry;
			}
		}
	}

	return $return_entries;
}

function Blog2_FindPostsByTag($component_id, $tag)
{
	global $db;
	$tag = strtolower($tag);
	$blog_entries = DB_PREFIX . 'pico_blog_entries';
	
	$all_entries = $db->force_multi_assoc('SELECT * FROM `'.$blog_entries.'` WHERE `component_id`=? AND `published`=1 AND `scheduled_date` < ?' . Blog2_GetSettingsSort($component_id, 'tags'), $component_id, time());
	$entries = array();
	
	if ( (is_array($all_entries)) and (sizeof($all_entries) > 0) )
	{
		foreach ($all_entries as $entry)
		{
			$tags = unserialize($entry['tags']);
			if (sizeof($tags) > 0)
			{
				foreach ($tags as $key=>$val)
				{
					$val = str_replace('#', '', $val);
					$val = PageNameToAlias($val);
					$tags[$key] = trim(strtolower($val));
				}
			}
			if (in_array($tag, $tags)) { $entries[] = $entry; }
		}
	}
	
	return $entries;
}

function Blog2_FindPostsByCategory($component_id, $category_id)
{
	global $db;
	$blog_entries = DB_PREFIX . 'pico_blog_entries';
	$blog_category_links = DB_PREFIX . 'pico_blog_category_links';

	$entries  = array();
	$post_ids = $db->force_multi_assoc('SELECT * FROM `'.$blog_category_links.'` WHERE `category_id`=?', $category_id);

	if (is_array($post_ids))
	{
		foreach ($post_ids as $p)
		{
			$post_id = $p['post_id'];
			$entry = $db->assoc('SELECT * FROM `'.$blog_entries.'` WHERE `post_id`=? AND `published`=1 AND `scheduled_date` < ?' . Blog2_GetSettingsSort($component_id, 'categories'), $post_id, time());
			if (is_array($entry)) { $entries[] = $entry; }
		}
	}
	
	return $entries;
}

function Blog2_FindPostsByDate($component_id, $year, $month)
{
	global $db;
	$blog_entries = DB_PREFIX . 'pico_blog_entries';
	
	$start    = mktime(0,0,0, $month, 1, $year);
	$last_day = date('t', $start);
	$end      = mktime(23,59,59, $month, $last_day, $year);
	
	$entries = $db->force_multi_assoc('SELECT * FROM `'.$blog_entries.'` WHERE `date`>=? AND `date`<=? AND `component_id`=? AND `published`=1 AND `scheduled_date` < ?' . Blog2_GetSettingsSort($component_id, 'archives'), $start, $end, $component_id, time());
	return $entries;
}

function Blog2_FindPostsByAuthor($component_id, $author)
{
	global $db;
	$blog_entries = DB_PREFIX . 'pico_blog_entries';
	$entries = $db->force_multi_assoc('SELECT * FROM `'.$blog_entries.'` WHERE `author` LIKE ? AND `component_id`=? AND `published`=1 AND `scheduled_date` < ?' . Blog2_GetSettingsSort($component_id, 'author'), $author, $component_id, time());
	return $entries;
}

function Blog2_FindPostsByYear($component_id, $year)
{
	global $db;
	$blog_entries = DB_PREFIX . 'pico_blog_entries';
	
	$start = mktime(0,0,0, 1, 1, $year);
	$end   = mktime(23,59,59, 12, 31, $year);
	$entries = $db->force_multi_assoc('SELECT * FROM `'.$blog_entries.'` WHERE `date`>=? AND `date`<=? AND `component_id`=? AND `published`=1 AND `scheduled_date` < ?' . Blog2_GetSettingsSort($component_id, 'archives'), $start, $end, $component_id, time());
	
	return $entries;
}

function Blog2_ShowRssFeed($component_id, $blog_title)
{
	global $db, $body;
	$blog_entries = DB_PREFIX . 'pico_blog_entries';
	
	$items = '';
	$rss_entries = $db->force_multi_assoc('SELECT * FROM `'.$blog_entries.'` WHERE `component_id`=? AND `published`=1 AND `scheduled_date` < ? ORDER BY `date` DESC LIMIT 3', $component_id, time());
	if ( (is_array($rss_entries)) and (sizeof($rss_entries) > 0) )
	{
		//DATE, STORY, TITLE, CATEGORY, TAGS, LINK
		foreach ($rss_entries as $entry)
		{
			$title    = htmlspecialchars($entry['title']);
			$date     = date('r', $entry['date']);
			$pub_ts   = $entry['date'];
			$blog_url = 'http://' . $_SERVER['SERVER_NAME'] . $body->url(CURRENT_ALIAS);
			$feed_url = 'http://' . $_SERVER['SERVER_NAME'] . $body->url(CURRENT_ALIAS . '/feed');
			$link     = 'http://' . $_SERVER['SERVER_NAME'] . $body->url(CURRENT_ALIAS . '/' . $entry['alias']);
			$story    = $entry['post'];
			
			// remove styling, rss feeds don't need styling (also other attributes that show up that don't need to be here)
			$story = preg_replace('|style=\".*?\"|si', '', $story);
			$story = preg_replace('|onclick=\".*?\"|si', '', $story);
			$story = preg_replace('|data-mce-href=\".*?\"|si', '', $story);
			$story = preg_replace('|data-screen-name=\".*?\"|si', '', $story);
			$story = preg_replace('|_mce_href=\".*?\"|si', '', $story);
			$story = preg_replace('|courtesty=\".*?\"|si', '', $story);
			$story = preg_replace('|_fcksavedurl=\".*?\"|si', '', $story);
			$story = preg_replace('|image=\".*?\"|si', '', $story);
			$story = preg_replace('|of=\".*?\"|si', '', $story);
			
			// relative images need to have full path
			$num_matches = preg_match_all('/\<img.*?src="(\/.*?)"/', $story, $matches);
			
			for ($x = 0; $x < $num_matches; $x++)
			{
				$img_link = $matches[1][$x];
				$story = str_replace($img_link, 'http://' . $_SERVER['SERVER_NAME'] . $img_link, $story);
			}
			
			// fix links
			$num_matches = preg_match_all('/\<a.*?(href=".*?")/', $story, $matches);
			for ($x = 0; $x < $num_matches; $x++)
			{
				$matched_text = $matches[1][$x];
				
				list ($foo, $url) = explode('=', $matched_text);
				$url = trim($url, '"');
				
				if (substr($url, 0, 4) != 'http')
				{
					$extra = (substr($url, 0, 1) != '/') ? '/' : '';
					$new_url = 'http://' . $_SERVER['SERVER_NAME'] . $extra . $url;
					$replace = 'href="'.$new_url.'"';
					$story = str_replace($matched_text, $replace, $story);
				}
			}
			
			
			$story = Blog2_StripTags($story);
			$story = htmlspecialchars($story);


			$pub_date  = date('r', $pub_ts);
			
			$items .= <<<RSS
		<item>
			<title>$title</title>         
			<link>$link</link>
			<description>$story</description>
			<guid>$link</guid>
			<pubDate>$pub_date</pubDate>
		</item>

RSS;
		}
	}
	
	$site_name = $_SERVER['SERVER_NAME'];
	$pub_date  = date('r', $pub_ts);
	
	header('Content-type: text/xml');
	
	$page_text = <<<RSS
<?xml version="1.0" ?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
	<channel>
		<atom:link href="$feed_url" rel="self" type="application/rss+xml" />
		<title>$blog_title</title>
		<link>$blog_url</link>
		<description>Website blog for $site_name</description>
$items
	</channel>
</rss> 
RSS;
	
	return $page_text;
}

function Blog2_StripTags($text)
{
	$text = preg_replace(
		array(
		  // Remove invisible content
			'@<head[^>]*?>.*?</head>@siu',
			'@<iframe[^>]*?>.*?</iframe>@siu',
			'@<style[^>]*?>.*?</style>@siu',
			'@<script[^>]*?.*?</script>@siu',
			'@<object[^>]*?.*?</object>@siu',
			'@<embed[^>]*?.*?</embed>@siu',
			'@<applet[^>]*?.*?</applet>@siu',
			'@<noframes[^>]*?.*?</noframes>@siu',
			'@<noscript[^>]*?.*?</noscript>@siu',
			'@<noembed[^>]*?.*?</noembed>@siu',
		),
		array(
			' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' '
		), 
		$text
	);
	return $text;
} 

function Blog2_LayoutSection($section_name, $sel = '')
{
	$dropdown = '<select name="settings[section_layout]['.$section_name.']">';
	$dropdown .= '<option value="full" '.(($sel == 'full') ? 'selected="selected"' : '').'>Full</option>';
	$dropdown .= '<option value="short" '.(($sel == 'short') ? 'selected="selected"' : '').'>Short</option>';

	if ($section_name == 'yearly') 
	{
		$dropdown .= '<option value="month" '.(($sel == 'month') ? 'selected="selected"' : '').'>Month</option>';
	}
	
	$dropdown .= '</select>';
	return $dropdown;
}

function Blog2_DisplayOption($section_name, $sel = '')
{
	$dropdown = '<select name="settings[display_mode]['.$section_name.']">';
	$dropdown .= '<option value="asc" '.(($sel == 'asc') ? 'selected="selected"' : '').'>Ascending</option>';
	$dropdown .= '<option value="desc" '.(($sel == 'desc') ? 'selected="selected"' : '').'>Descending</option>';
	$dropdown .= '<option value="upcoming" '.(($sel == 'upcoming') ? 'selected="selected"' : '').'>Upcoming</option>';
	$dropdown .= '</select>';
	return $dropdown;
}

function Blog2_GetPostsDropdown($component_id, $name, $sel = 0)
{
	global $db;
	
	$blog_entries = DB_PREFIX . 'pico_blog_entries';
	$entries      = $db->force_multi_assoc('SELECT * FROM `'.$blog_entries.'` WHERE `component_id`=? AND `published`=? ORDER BY `date` DESC', $component_id, 1);
	
	$dropdown .= '<select name="'.$name.'" style="width: 350px"><option value="0"></option>';
	if (is_array($entries))
	{
		foreach ($entries as $entry)
		{
			$selected = ($entry['post_id'] == $sel) ? 'selected="selected"' : '';
			
			$t = date('m/d/y', $entry['date']) . ' - ' . $entry['title'];
			$dropdown .= '<option value="'.$entry['post_id'].'" '.$selected.'>'.$t.'</option>';
		}
	}
	
	$dropdown .= '</select>';
	return $dropdown;
}

function Blog2_DateDropdowns($name, $timestamp = 0)
{
	$day    = '<select name="'.$name.'[day]">';
	$month  = '<select name="'.$name.'[month]">';
	$year   = '<select name="'.$name.'[year]">';
	$hour   = '<select name="'.$name.'[hour]">';
	$minute = '<select name="'.$name.'[minute]">';
	
	if ($timestamp == 0)
	{
		$timestamp = time();
	}
	
	for ($x = 1; $x <=12; $x++)
	{
		$mo = date('F', mktime(0,0,1, $x, 1, date('Y')));
		$selected = (($timestamp != 0) and (date('n', $timestamp) == $x)) ? 'selected="selected"' : '';
		$month .= '<option value="'.$x.'" '.$selected.'>'.$mo.'</option>';
	}
	$month .= '</select>';
	
	for ($x = 1; $x <=31; $x++)
	{
		$selected = (($timestamp != 0) and (date('j', $timestamp) == $x)) ? 'selected="selected"' : '';
		$day .= '<option value="'.$x.'" '.$selected.'>'.$x.'</option>';
	}
	$day .= '</select>';
	
	for ($x = -5; $x < 3; $x++)
	{
		$yr = date('Y') + $x;
		$selected = (($timestamp != 0) and (date('Y', $timestamp) == $yr)) ? 'selected="selected"' : '';
		$year .= '<option value="'.$yr.'" '.$selected.'>'.$yr.'</option>';
	}
	$year .= '</select>';
	
	for ($x = 0; $x < 24; $x++)
	{
		$hr = date('H', mktime($x, 0, 0, date('m'), date('d'), date('Y')));
		$selected = (($timestamp != 0) and (date('G', $timestamp) == $x)) ? 'selected="selected"' : '';
		$hour .= '<option value="'.$x.'" '.$selected.'>'.$hr.'</option>';
	}
	$hour .= '</select>';
	
	for ($x = 0; $x < 59; $x++)
	{
		$min = $x;
		$selected = (($timestamp != 0) and (date('i', $timestamp) == $min)) ? 'selected="selected"' : '';
		$minute .= '<option value="'.$min.'" '.$selected.'>'.str_pad($min, 2, '0', STR_PAD_LEFT).'</option>';
	}
	$minute .= '</select>';
	
	
	$output = <<<HTML
$month/$day/$year, $hour : $minute
HTML;

	return $output;
}

function Blog2_TimeStampFromHTML($html_field)
{
	if ( (!is_array($html_field)) or (sizeof($html_field) == 0) ) { return 0; }
	$mo = $html_field['month'];
	$da = $html_field['day'];
	$yr = $html_field['year'];
	$mi = $html_field['minute'];
	$hr = $html_field['hour'];
	
	$ts = @mktime($hr, $mi, 0, $mo, $da, $yr);
	if (!is_numeric($ts)) { $ts = 0; }
	
	return $ts;
}

function Blog2_CommentForm($entry_id, $comment_id = 0)
{
	return '<div class="add_comment" onclick="Blog_ShowCommentForm(this, '.$entry_id.', '.$comment_id.')">Add Comment</div>';
}

function Blog2_GetTagForm($component_id, $entry_id)
{
	global $db, $body;
	$blog_entries    = DB_PREFIX . 'pico_blog_entries';

	$current_tags = $db->result('SELECT `tags` FROM `'.$blog_entries.'` WHERE `post_id`=?', $entry_id);
	$current_tags = unserialize($current_tags);
	if (!is_array($current_tags)) { $current_tags = array(); }

	if (sizeof($current_tags) > 0)
	{
		foreach ($current_tags as &$t)
		{
			$t = trim(strtolower($t));
		}
	}

	$all_tags = array();
	$entries = $db->force_multi_assoc('SELECT * FROM `'.$blog_entries.'` WHERE `component_id`=?', $component_id);
	if (is_array($entries))
	{
		foreach ($entries as $entry)
		{
			$tags = unserialize($entry['tags']);
			if (!is_array($tags)) { $tags = array(); }

			if (sizeof($tags) > 0)
			{
				foreach ($tags as $tag)
				{
					$tag = trim(strtolower($tag));
					if (strlen($tag) > 0)
					{
						if (!isset($all_tags[$tag])) { $all_tags[$tag] = 0; }
						$all_tags[$tag]++;
					}
				}
			}
		}
	}

	$current_taglist = implode(', ', $current_tags);
	$output = '<div><textarea name="tags" class="ap_textarea" id="blog_tags">'.$current_taglist.'</textarea></div>';

	if (sizeof($all_tags) == 0) { return $output; }
	@arsort($all_tags, SORT_NUMERIC);
	$top5 = array_slice($all_tags, 0, 10, true);
	
	$output .= '<div class="click" onclick="Blog2_UpdateTags()">
		<img src="'.$body->url('includes/icons/edit.png').'" title="Change" class="click icon" /> Find More Tags</div>';

	$output .= '<div id="blog2_tagbox" style="display: none">';
	$output .= '<div class="tag_col">
		<div class="title">Select Tags</div>
		<p>Click on a tag to add it to your entry</p>
		<div class="title">Most Popular</div>';

	$counter = 0;

	foreach ($top5 as $tag => $count)
	{
		$class = ($counter % 2 == 0) ? 'a' : 'b'; $counter++;
		$output .= '<div class="tag_entry '.$class.'" tag_name="'.$tag.'">'.$tag.' ('.$count.')</div>';
	}

	$output .= '<div class="title"><button onclick="BLog2_CloseTags()">Close</button></div>';

	$output .= '</div>';

	$num_cols   = 2;
	$other      = array_slice($all_tags, 10, null, true);
	@ksort($other);
	$other_tags = array_keys($other);
	$per_col    = ceil(sizeof($other) / $num_cols);

	for ($y = 0; $y < $num_cols; $y++)
	{
		$output .= '<div class="tag_col">';
		for ($x = 0; $x < $per_col; $x++)
		{
			if (sizeof($other) == 0) { break; }

			$tag   = array_shift($other_tags);
			$count = array_shift($other);

			$class = ($counter % 2 == 0) ? 'a' : 'b'; $counter++;

			$output .= '<div class="tag_entry '.$class.'" tag_name="'.$tag.'">'.$tag.' ('.$count.')</div>';
		}
		$output .= '</div>';
	}

	$output .= '<div class="clear"></div></div>';
	return $output;
}

if (!function_exists('Pico_StorageDir'))
{
	function Pico_StorageDir($directory)
	{
		$base = 'includes/storage/';
		$full_dir = $base . $directory;

		if ((is_dir($full_dir)) and (is_writable($full_dir)))
		{
			return true;
		}
		else
		{
			// get parent dir
			$parts = explode('/', $directory);
			array_pop($parts);

			if (sizeof($parts) == 0) { return false; }
			$parent_directory = implode('/', $parts);

			if (Pico_StorageDir($parent_directory))
			{
				// log into ftp, make directory if needed, chmod

				$ftp = Pico_ConnectFTP();
				if (!is_dir($full_dir)) { @$ftp->mkdir($full_dir); }
				@$ftp->chmod($full_dir, 0777);
				$ftp->quit;

				if ((is_dir($full_dir)) and (is_writable($full_dir)))
				{
					return true;
				}
				else
				{
					return false;
				}
			}
			else
			{
				return false;
			}
		}
	}
}

function Blog2_GetSettingsSort($component_id, $view_type)
{
	$settings = Blog2_GetSettings($component_id);
	$switch   = $settings['display_mode'][$view_type];

	switch ($switch)
	{
		case 'upcoming':
			$today = mktime(0,0,0, date('m'), date('d'), date('y'));
			$r = ' AND `date`>="'.$today.'" ORDER BY `date` ASC';
			break;
		case 'ascending':
			$r = ' ORDER BY `date` ASC';
			break;
		default:
			$r = ' ORDER BY `date` DESC';
			break;
	}

	return $r;
}

function Blog2_GetLayoutDescHTML()
{
	return <<<HTML
<ul class="variable_list">
	<li>{TITLE}
		<div class="variable_tooltip">The title of the post</div></li>
	<li>{SECONDARY_TITLE}
		<div class="variable_tooltip">Sub-title of the post</div></li>
	<li>{LINK,linked text}
		<div class="variable_tooltip">Link to an individual post, displayed as "linked text"<br />ex: {LINK,{TITLE}} or {LINK,Read More}</div></li>
	<li>{STORY[,num words]}
		<div class="variable_tooltip">Main post text, with optional number of words</div></li>
	<li>{DATE[,flags]}
		<div class="variable_tooltip">Shows the post date, with optional flags to specify how the date should be formatted</div></li>
	<li>{ID}
		<div class="variable_tooltip">Internal Post ID</div></li>
	<li>{CATEGORIES}
		<div class="variable_tooltip">A list-link of all the categories this post is in</div></li>
	<li>{TAGS}
		<div class="variable_tooltip">Tag link list</div></li>
	<li>{NUM_TAGS}
		<div class="variable_tooltip">Number of tags for a given post</div></li>
	<li>{RELATED}
		<div class="variable_tooltip">A linked list of related posts to this post</div></li>
	<li>{COMMENTS_ENABLED}
		<div class="variable_tooltip">TRUE/FALSE for if comments are enabled<br />ex: {if:COMMENTS_ENABLED}...{/if}</div></li>
	<li>{COMMENTS}
		<div class="variable_tooltip">Post comments placement</div></li>
	<li>{SHARETHIS}
		<div class="variable_tooltip">Will display the "Share This" code configured in Pico settings</div></li>
	<li>{IMAGE,width,height,display mode}
		<div class="variable_tooltip">Will display the image for the post, at a specified width, height, and mode.
		<ul>
			<li>Width - Integer</li>
			<li>Height - Integer</li>
			<li>Display Mode - "pad" or "crop"</li>
			<li>If width or height are zero, the image will be resized to match that width and height. 
			If width and height are omitted or both zero, the original image size will be displayed;</li>
		</ul>
		<br />
		ex: {IMAGE,100,50,pad} will show an image that's 100x50 with display mode of "pad"
		</div>
	</li>
	<li>{CAPTION}
		<div class="variable_tooltip">Image Caption</div></li>
</ul>
HTML;
}

function Blog2_PointSort($a, $b)
{
	if ($a['pts'] < $b['pts'])
	{
		return 1;
	}
	elseif ($a['pts'] > $b['pts'])
	{
		return -1;
	}
	else
	{
		return 0;
	}
}
?>