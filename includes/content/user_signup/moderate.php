<?php
chdir('../../../');
require_once('core.php');

if (USER_ACCESS < 2)
{
	echo 'You are not logged in or do not have sufficient privileges to access this, please log in and try again or use the 
	admin panel';
	exit();
}

require_once('includes/content/user_signup/functions.php');

$action = $_GET['action'];

if ($action == 'approve')
{
	$user_id   = $_GET['user_id'];
	$user_info = $db->assoc('SELECT * FROM `'.DB_USER_TABLE.'` WHERE `id`=?', $user_id);

	if ($user_info['user_active'] == 0)
	{
		$db->run('UPDATE `'.DB_USER_TABLE.'` SET `user_active`=? WHERE `id`=?', 1, $user_id);
		if (function_exists('Pico_SendAccountWelcomeEmail'))
		{
			Pico_SendAccountWelcomeEmail($user_id);
		}
	}
	
	echo 'User has been activated';
	exit();
}
elseif ($action == 'deny')
{
	$user_id   = $_GET['user_id'];
	$user_info = $db->assoc('SELECT * FROM `'.DB_USER_TABLE.'` WHERE `id`=?', $user_id);

	if (($user_info['user_active'] == 0) and ($user_info['access'] == 1))
	{
		$user_profile_id = Pico_GetUserProfileId($user_id);
		
		if (function_exists('Pico_SendAccountDeclinedEmail'))
		{
			Pico_SendAccountDeclinedEmail($user_id);
		}

		$table = DB_PREFIX . 'user_profile_values_' . $user_profile_id;
		$db->run('DELETE FROM `'.DB_USER_TABLE.'` WHERE `id`=?', $user_id);
		$db->run('DELETE FROM `'.$table.'` WHERE `user_id`=?', $user_id);
		echo 'User has been removed';
	}
	else
	{
		echo 'User already active or removed';
	}
	
	exit();
}
?>