<?php
if (isset($_GET['refresh']))
{
	$entry_id = $_GET['entry_id'];
	chdir('../../../');
	require_once('core.php');
}

if ( (!defined('USER_ACCESS')) or (USER_ACCESS < 3) )
{
	exit();
}

if (!isset($entry_info))
{
	$blog_options    = DB_PREFIX . 'pico_blog_options';
	$blog_entries    = DB_PREFIX . 'pico_blog_entries';
	$blog_categories = DB_PREFIX . 'pico_blog_categories';
	$blog_comments   = DB_PREFIX . 'pico_blog_comments';

	$entry_info = $db->assoc('SELECT * FROM `'.$blog_entries.'` WHERE `post_id`=?', $entry_id);
}

$date1 = ($entry_info['last_saved_date'] != 0) ? date('h:i:sa m/d/Y', $entry_info['last_saved_date']) : 'n/a';
$date2 = ($entry_info['last_saved_date'] != 0) ? date('h:i:sa m/d/Y', $entry_info['auto_saved_date']) : 'n/a';

?>
<h3 class="blog_choice">Restore Previous/Auto Save</h3>

<p>Last User-Saved Post - <?=$date1?></p>
<p class="center"><button onclick="Blog2_RestoreEntry('last_user_saved_post')">Use This Draft</button></p>
<textarea id="last_user_saved_post"><?=$entry_info['last_saved_post']?></textarea>

<p>Last Auto-Saved Post - <?=$date2?></p>
<p class="center"><button onclick="Blog2_RestoreEntry('last_auto_saved_post')">Use This Draft</button></p>
<textarea id="last_auto_saved_post"><?=$entry_info['auto_saved_post']?></textarea>