<?php
chdir('../');
require_once('core.php');
if (USER_ACCESS < 3) { exit(); }

$browseform = '';

if (isset($_GET['search']))
{
	$search = urldecode($_GET['search']);
	$s      = $search;
	$search = '%' . $search . '%';
	$users  = $db->force_multi_assoc('SELECT * FROM `'.DB_USER_TABLE.'` WHERE `username` LIKE ? OR `email_address` LIKE ? OR `first_name` LIKE ? OR `last_name` LIKE ?',
		$search, $search, $search, $search
	);
	
	$browseform = 'Found '.sizeof($users) .' users';
}
else
{
	$page = $_GET['page'];
	if (!is_numeric($page)) { $page = 0; }

	$per_page = 10;
	$limit = $page * $per_page;

	$total_users = $db->result('SELECT count(1) FROM '.DB_USER_TABLE);
	$num_pages = ceil($total_users / $per_page);

	$browseform .= 'Browse by page: <select onchange="Pico_ManageUsers(this.value)">';

	for ($x = 0; $x < $num_pages; $x++)
	{
		$selected = ($page == $x) ? 'selected="selected"' : '';
		$browseform .= '<option value="'.$x.'" '.$selected.'>'.($x+1).'</option>';
	}

	$browseform .= '</select><div class="clear"></div>';

	$users = $db->force_multi_assoc('SELECT * FROM `'.DB_USER_TABLE.'` ORDER BY `access` DESC, `username` ASC LIMIT '.$limit.', '.$per_page);
}

echo <<<HTML
<table border="0" cellpadding="0" width="100%" cellspacing="1">
<tr>
	<td width="50%">Search for user: <input type="text" id="user_search" value="$s" /> <input type="submit" value="Search" onclick="Pico_UserSearch()" /></td>
	<td width="50%">$browseform</td>
</tr>
HTML;

$table = '';
if (sizeof($users) > 0)
{
	$counter = 0;
	foreach ($users as $user)
	{
		$delete = '';
		$class = ($counter % 2 == 0) ? 'a' : 'b'; $counter++;
		if (($user['access'] <= USER_ACCESS) and (USER_ID != $user['id']))
		{
			$delete = '<img src="'.$body->url('includes/icons/delete.png').'" title="Delete User" class="click icon" onclick="Pico_DeleteUser('.$user['id'].')" />';
		}
		
		$edit = '';
		if ($user['access'] <= USER_ACCESS)
		{
			$edit = '<img src="'.$body->url('includes/icons/edit.png').'" title="Edit User" class="click icon" onclick="Pico_AddUser('.$user['id'].')" />';
		}

		$actions  = $delete . $edit;
		$inactive = (($user['user_active'] == 0) && ($user['registration_active'] == null)) ? TRUE : FALSE;
		if ($inactive) { 
			$class   .= ' inactive'; 
			$actions .= '<img src="'.$body->url('includes/icons/info.png').'" title="Activate User" class="click icon" onclick="Pico_ActivateUser('.$user['id'].')" />';
		}

		$table .= '
<tr class="'.$class.'">
	<td>'.$user['username'].'</td>
	<td>'.AccessLevel($user['access']).'</td>
	<td>'.Stamp($user['last_login']).'<br /> '.$user['last_ip'].'</td>
	<td>'.$user['email_address'].'</td>
	<td>'.$user['first_name'].'</td>
	<td>'.$user['last_name'].'</td>
	<td>'.$actions.'</td>
</tr>';
	}
}
?>
<table id="manage_users" cellpadding="2" cellspacing="1" class="admin_list" style="background: #fff">
<tr>
	<th>Username</th>
	<th>Access</th>
	<th>Last Login</th>
	<th>E-mail Address</th>
	<th>First Name</th>
	<th>Last Name</th>
	<th>Actions</th>
</tr>
<?=$table?>
</table>

