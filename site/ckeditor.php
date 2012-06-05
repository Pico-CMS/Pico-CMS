<?php
header('Content-type: text/css');
chdir('../');

$page_id = $_GET['page_id'];
if (!is_numeric($page_id))
{
	unset($page_id);
}
else
{
	define('CURRENT_PAGE', $page_id);
}

require_once('core.php');

$page_theme = $db->result('SELECT `theme` FROM `'.DB_PAGES_TABLE.'` WHERE `page_id`=?', CURRENT_PAGE);
$theme_url = $body->base_url . 'themes/'.$page_theme.'/';

ReadTheme('themes/ckeditor.css', $body->base_url . 'themes/');
ReadTheme('themes/'.$page_theme.'/ckeditor.css', $theme_url);

?>