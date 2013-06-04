<?php
require_once('includes/content/user_login/functions.php');
$additional_info  = $db->result('SELECT `additional_info` FROM `'.DB_CONTENT.'` WHERE `component_id`=?', $component_id);
$settings         = unserialize($additional_info);
if (!is_array($settings)) { $settings = array(); }
$page_name         = $db->result('SELECT `alias` FROM `'.DB_PAGES_TABLE.'` WHERE `page_id`=?', $settings['redirect']);
$expired_page_name = $db->result('SELECT `alias` FROM `'.DB_PAGES_TABLE.'` WHERE `page_id`=?', $settings['expired']);

if ($_POST['page_action'] == "loggin_in")
{
	if ($_POST['component_id'] == $component_id)
	{
		$username = $_POST['login_username'];
		$password = md5($_POST['login_password']);
		$user_id = $db->result('SELECT `id` FROM `'.DB_USER_TABLE.'` WHERE `username`=? AND `password`=?', $username, $password);
		if ($user_id != FALSE)
		{
			// see if a session exists
			
			if (isset($_COOKIE['keep_session']))
			{
				// log them out
				$session_data = unserialize(base64_decode($_COOKIE['keep_session']));
				$db->run('UPDATE `'.DB_USER_TABLE.'` SET `session_id`=? WHERE `session_id`=?', '', $session_data['session_id']);
				
				setcookie('keep_session', '', time() - 3600, '/', CookieDomain());
				setcookie(session_name(), '', time() - 42000, '/');
				session_destroy();
				session_start();
			}
			
			// see if this person is expired
			$user_info = $db->assoc('SELECT * FROM `'.DB_USER_TABLE.'` WHERE `id`=?', $user_id);
			if ($user_info['user_active'] == 0)
			{
				if ($user_info['registration_active'] < time())
				{
					// redirect them
					$location = (strlen($settings['custom_expired_url']) > 0) ? $settings['custom_expired_url'] : $body->url($expired_page_name);
					
					$_SESSION['expired_registration'] = TRUE;
					$_SESSION['expired_user_id'] = $user_id;
					
					header('Location: ' . $location);
					return;
				}
			}
			
			if (function_exists('Pico_LogUserIn'))
			{
				// use pico login
				Pico_LogUserIn($user_id);
			}
			else
			{
				// use "old" login
				UL_LogUserIn($user_id);
			}
			
			if (strlen($settings['custom_login_url']) > 0)
			{
				header('Location: ' . $settings['custom_login_url']);
				exit();
			}
			else
			{
				header('Location: ' . $body->url($page_name));
				exit();
			}
		}
		else
		{
			echo '<p class="error">'.$settings['failed-text'].'</p>';
		}
	}
}
elseif ($_POST['page_action'] == "forgot_password")
{
	$email = trim(strip_tags(stripslashes($_POST['email_address'])));
	// get user id
	$user_id = $db->result('SELECT `id` FROM `'.DB_USER_TABLE.'` WHERE `email_address` LIKE ? LIMIT 1', $email);
	if (!is_numeric($user_id))
	{
		echo '<p class="error">We were unable to find an account with that e-mail address.</p>';
	}
	else
	{
		$new_password = strtolower(generate_text());
		$db->run('UPDATE `'.DB_USER_TABLE.'` SET `password`=? WHERE `id`=?',
			md5($new_password), $user_id
		);
		
		$user_info = $db->assoc('SELECT * FROM `'.DB_USER_TABLE.'` WHERE `id`=?', $user_id);
		
		$message = $settings['new-pwd-email'];
		$message = str_replace('PASSWORD', $new_password, $message);
		$message = str_replace('FIRST_NAME', $user_info['first_name'], $message);
		$message = str_replace('LAST_NAME', $user_info['last_name'], $message);
		$message = str_replace('EMAIL_ADDRESS', $user_info['email_address'], $message);
		
		// send user e-mail
		Pico_SendUserEmail($email, $settings['new-pwd-email-subject'], $message);
		echo '<p class="pwd-reset">'.$settings['pwd-reset'].'</p>';
		return;
	}
}
if (!defined("USER_ID"))
{
	if ($settings['compact_form'] == 1)
	{
		include('includes/content/user_login/layout-compact.php');
	}
	else
	{
		include('includes/content/user_login/layout-default.php');
	}
}
else
{
	// redirect user
	if ($settings['redirect_user'] == 1)
	{
		if (USER_ACCESS < 3)
		{
			if (strlen($settings['custom_login_url']) > 0)
			{
				header('Location: ' . $settings['custom_login_url']);
				exit();
			}
			else
			{
				if ($page_name != CURRENT_ALIAS)
				{
					header('Location: ' . $body->url($page_name));
					exit();
				}
			}
		}
		return;
	}

	if ($settings['show_logout_text'])
	{
		$username = $db->result('SELECT `username` FROM `'.DB_USER_TABLE.'` WHERE `id`=?', USER_ID);
		$logout_link = $body->url('logout');

		echo <<<HTML
<div class="logout_win">
	<p class="current">You are logged in as $username</p>
	<p class="logout_link"><a href="$logout_link">Log Out</a></p>
</div>
HTML;
	}
}

?>