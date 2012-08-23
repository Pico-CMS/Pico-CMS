<?php
chdir('../');
require_once('core.php');
if (USER_ACCESS < 3) { exit(); }

if (isset($_GET['search']))
{
	$search = urldecode($_GET['search']);
	$search = '%' . $search . '%';
	$users = $db->force_multi_assoc('SELECT * FROM `'.DB_USER_TABLE.'` WHERE `username` LIKE ? OR `email_address` LIKE ? OR `first_name` LIKE ? OR `last_name` LIKE ?',
		$search, $search, $search, $search
	);
	
	echo '<p>Found '.sizeof($users) .' users</p>';
}
else
{
	$page = $_GET['page'];
	if (!is_numeric($page)) { $page = 0; }

	$per_page = 25;
	$limit = $page * $per_page;

	$total_users = $db->result('SELECT count(1) FROM '.DB_USER_TABLE);
	$num_pages = ceil($total_users / $per_page);

	echo '<p>Browse by page: ';

	for ($x = 0; $x < $num_pages; $x++)
	{
		echo '<span class="click" onclick="Pico_ManageUsers('.$x.')">'.($x+1).'</span> ';
	}

	echo '</p><div class="clear"></div>';

	$users = $db->force_multi_assoc('SELECT * FROM `'.DB_USER_TABLE.'` ORDER BY `access` DESC, `username` ASC LIMIT '.$limit.', '.$per_page);
}

echo '<p>Search for user: <input type="text" id="user_search" value="" /> <input type="submit" value="Search" onclick="Pico_UserSearch()" /></p>';

$table = '';
if (sizeof($users) > 0)
{
	foreach ($users as $user)
	{
		$delete = '';
		if (($user['access'] <= USER_ACCESS) and (USER_ID != $user['id']))
		{
			$delete = '<img src="'.$body->url('includes/icons/delete.png').'" class="click icon" onclick="Pico_DeleteUser('.$user['id'].')" />';
		}
		
		$edit = '';
		if ($user['access'] <= USER_ACCESS)
		{
			$edit = '<img src="'.$body->url('includes/icons/edit.png').'" class="click icon" onclick="Pico_AddUser('.$user['id'].')" />';
		}
		
		$table .= '
<tr>
	<td>'.$user['username'].'</td>
	<td>'.AccessLevel($user['access']).'</td>
	<td>'.Stamp($user['last_login']).'</td>
	<td>'.$user['last_ip'].'</td>
	<td>'.$user['email_address'].'</td>
	<td>'.$user['first_name'].'</td>
	<td>'.$user['last_name'].'</td>
	<td>'.$delete.$edit.'</td>
</tr>';
	}
}
?>
<table id="manage_users" cellpadding="2" cellspacing="1">
<tr>
	<th>Username</th>
	<th>Access</th>
	<th>Last Login</th>
	<th>IP Address</th>
	<th>E-mail Address</th>
	<th>First Name</th>
	<th>Last Name</th>
	<th>Actions</th>
</tr>
<?=$table?>
</table>

