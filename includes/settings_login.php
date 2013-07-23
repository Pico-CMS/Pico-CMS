<?php
if ( (!defined('USER_ACCESS')) or (USER_ACCESS < 3) ) { exit(); }

// get all the pages in the CMS
$pages = $db->force_multi_assoc('SELECT * FROM `'.DB_PAGES_TABLE.'` ORDER BY `name` ASC');
$dropdown = '';
if ( (sizeof($pages) > 0) and (is_array($pages)) )
{
	$current_login_page  = Pico_Setting('pico_login_page');
	$current_login_page2 = Pico_Setting('404_page_id');
	if (!is_numeric($current_login_page)) { $current_login_page = 0; }
	
	foreach($pages as $page)
	{
		$selected   = ($current_login_page  == $page['page_id']) ? ' selected="selected"' : '';
		$selected2  = ($current_login_page2 == $page['page_id']) ? ' selected="selected"' : '';

		$dropdown .= '<option value="'.$page['page_id'].'"'.$selected.'>'.$page['name'].'</option>';
		$dropdown2 .= '<option value="'.$page['page_id'].'"'.$selected2.'>'.$page['name'].'</option>';
	}
}
?>
<div class="ap_overflow">
	<h3>Page Settings</h3>
	<form method="post" action="<?=$body->url('includes/ap_actions.php')?>" onsubmit="Pico_SaveSettings(this); return false">
	<input type="hidden" name="ap_action" value="settings" />
	
	<table border="0" cellpadding="0" cellspacing="2" class="admin_list">
	<tr class="a">
		<td>External Login Page</td>
		<td>
			Redirect vistiors to the following page when they try to access a restricted area<br />
			<select name="settings[pico_login_page]">
				<option value="0">PICO Login Page</option>
				<?=$dropdown?>
			</select>
		</td>
	</tr>
	<tr class="b">
		<td>404 Page</td>
		<td>
			Redirect vistiors to the following page when they try to access a page or file that doesn't exist<br />
			<select name="settings[404_page_id]">
				<option value="0">None</option>
				<?=$dropdown2?>
			</select>
		</td>
	</tr>

	</table>
	
	<input type="submit" name="submit_btn" value="Save" />
	</form>
	
	<?=$back?>
</div>