<?php
include('core.php');
ob_start();
header('Content-type: text/html; charset=utf-8');

if (CURRENT_ALIAS == 'logout')
{
	include('includes/logout.php');
}
elseif (CURRENT_ALIAS == 'cleanup')
{
	include('includes/cleanup.php');
}
elseif (PRINTER_FRIENDLY)
{
	include('includes/print.php');
	return;
}
elseif (CURRENT_ALIAS == 'help')
{
	include('includes/help.php');
}
//elseif other restricted keywords
else
{
	// find the page
	if (defined('CURRENT_PAGE'))
	{
		// get all the groups this user is in
		$user_groups  = array();
		$groups_table = DB_PREFIX . 'pico_groups';
		$all_groups   = $db->force_multi_assoc('SELECT * FROM `'.$groups_table.'`');
		
		if ( (is_array($all_groups)) and (sizeof($all_groups) > 0) )
		{
			foreach ($all_groups as $group)
			{
				$users = (sizeof($group['users']) > 0) ? explode(',', $group['users']) : array();
				if (in_array(USER_ID, $users))
				{
					$user_groups[] = $group['group_id'];
				}
			}
		}
		
		$page_details = $db->assoc('SELECT * FROM `'.DB_PAGES_TABLE.'` WHERE `page_id`=?', CURRENT_PAGE);
		if ($page_details != false)
		{
			$page_groups = (sizeof($page_details['groups']) > 0) ? explode(',', $page_details['groups']) : array();
			$user_is_in_group = FALSE;
			
			if (sizeof($page_groups) > 0)
			{
				foreach ($page_groups as $group)
				{
					if (in_array($group, $user_groups))
					{
						$user_is_in_group = TRUE;
					}
				}
			}
			
			// process the page theme and output it, output will get grabbed by the OB
			$body_title = (strlen($page_details['www_title']) > 0) ? $page_details['www_title'] : $page_details['name'];
			$body->set_title(0, $body_title);

			if ((USER_ACCESS >= $page_details['user_access']) or ($user_is_in_group))
			{
				// process the page
				
				$body_theme = 'themes/'.$page_details['theme'].'/body.php';
				if (file_exists($body_theme))
				{
					include($body_theme);
				}
			}
			else
			{
				$current_login_page = Pico_Setting('pico_login_page');
				if (!is_numeric($current_login_page)) { $current_login_page = 0; }
				
				if ($current_login_page != 0)
				{
					// make sure this page exists
					$page_info = $db->assoc('SELECT * FROM `'.DB_PAGES_TABLE.'` WHERE `page_id`=?', $current_login_page);
					if (is_array($page_info))
					{
						$alias = $page_info['alias'];
						$redirect = $body->url($alias);
						header('Location: '  . $redirect);
						exit(); // redirect user to the saved page
					}
				}
				include('includes/login.php');
			}
		}
		else
		{
			echo '<div class="error">There was an error processing this page</div>';
		}
	}
	else
	{
		echo '<div class="error">There is no default page established</div>';
	}
}

$normal_output = ob_get_contents();
ob_end_clean();

if (defined('STATIC_HTML'))
{
	// this is for things like blog feeds, xml, etc so we can still use SEO friendly URLs but not be restricted to using html below
	echo STATIC_HTML;
	exit();
}

$body_classes  = $body->get_classes();
$class_text    = implode(' ', $body_classes);
$thumbnail_url = $body->get_thumbnail();
$social_html   = '';

if (strlen($thumbnail_url) > 0)
{
	$social_html .= '<link rel="img_src" href="'.$thumbnail_url.'" />' .
	"\n\t" . '<meta property="og:image" content="'.$thumbnail_url.'" />';
}

if (strlen($body->social_title) > 0)
{
	$t = str_replace('"', '\\"', $body->social_title);
	$social_html .= "\n\t" . '<meta property="og:title" content="'.$t.'" />';
}

if (strlen($body->social_desc) > 0)
{
	$d = str_replace('"', '\\"', $body->social_desc);
	$social_html .= "\n\t" . '<meta property="og:description" content="'.$d.'" />';
}

$meta_desc     = (strlen($body->meta_desc) > 0) ? $body->meta_desc : $page_details['description'];
$meta_keywords = (strlen($body->get_meta_keywords()) > 0) ? $body->get_meta_keywords() : $page_details['keywords'];


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title><?=Pico_GetPageTitle()?></title>
	<link href="<?=$body->url('site/style.php?page_id='.CURRENT_PAGE)?>" type="text/css" rel="stylesheet" />
	<script type="text/javascript">
	var CURRENT_PAGE = '<?=CURRENT_PAGE?>';
	var CURRENT_ALIAS = '<?=CURRENT_ALIAS?>';
	var BASE_URL = '<?=$body->base_url?>';
	<?php
	if (USER_ACCESS > 1):
	?>
	/* global variables */
	var REQUEST_URI = '<?=$_SERVER['REQUEST_URI']?>';
	<?php
	endif;
	?>
	</script>
	<script type="text/javascript" src="<?=$body->url('site/javascript.php' . ((USER_ACCESS > 1) ? '?mode=reload' : ''))?>"></script>
	<?php
	if (strlen($meta_desc) > 0) {
		echo '<meta name="description" content="'.$meta_desc.'" />' . "\n";
	}
	if (strlen($meta_keywords) > 0) {
		echo '<meta name="keywords" content="'.$meta_keywords.'" />' . "\n";
	}
	?>
	<?=Pico_Setting('html_head')?>
	<?=$body->get_head()?>
	<?=$social_html?>
</head>
<body class="<?=$class_text?>">
<?=$normal_output?>
<?php
if (USER_ACCESS > 2)
{
?>
<div id="admin_controller">
<?php include('includes/admin_panel.php');?>
</div>
<?php
}

if (USER_ACCESS > 1)
{
?>
<div id="action_panel">
	<div id="ap_title_bg">
		<div id="ap_title"></div>
		<div id="ap_close"><img src="<?=$body->url('includes/icons/close.png')?>" class="click" alt="Close" onclick="Pico_CloseAP()" /></div>
		<div class="clear"></div>
	</div>
	<div id="ap_content"></div>
</div>
<?php
}
?>
<?=Pico_GetClosingBody()?>
</body>
</html>