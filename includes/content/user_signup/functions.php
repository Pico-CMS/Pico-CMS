<?php


function US_SendModeratorEmail($user_id, $moderators)
{
	global $body, $db;
	$user_details = US_GetUserDetails($user_id);
	$lines = '';

	foreach ($user_details as $k=>$v)
	{
		$lines .= "$k: $v\n";
	}

	if (sizeof($moderators) > 0)
	{
		foreach ($moderators as $moderator_id)
		{
			$mi = $db->assoc('SELECT * FROM `'.DB_USER_TABLE.'` WHERE `id`=?', $moderator_id);

			$email_txt = '';

			if (strlen($mi['first_name']) > 0) { $email_txt .= $mi['first_name'] . ",\n\n"; }

			$ok_url  = 'http://' . $_SERVER['SERVER_NAME'] . $body->url('includes/content/user_signup/moderate.php?action=approve&user_id='.$user_id);
			$bad_url = 'http://' . $_SERVER['SERVER_NAME'] . $body->url('includes/content/user_signup/moderate.php?action=deny&user_id='.$user_id);

			$email_txt .= <<<HTML
A new user has signed up on your site and is awaiting approval:
$lines

<p style="margin: 10px 0">To approve this user, <a href="$ok_url">click here</a></p>
To remove this user, <a href="$bad_url">click here</a>
HTML;

			US_SendUserEmail($mi['email_address'], 'New User Signup', $email_txt, true);
		}
	}
}

// gets a readable array of info 
function US_GetUserDetails($user_id)
{
	global $db;
	$user_profile_id = Pico_GetUserProfileId($user_id);
	$profile_info    = Pico_GetProfileFieldData($user_profile_id);

	$table = DB_PREFIX . 'user_profile_values_' . $user_profile_id;

	$base_info = $db->assoc('SELECT * FROM `'.DB_USER_TABLE.'` WHERE `id`=?', $user_id);
	$more_info = $db->assoc('SELECT * FROM `'.$table.'` WHERE `user_id`=?', $user_id);

	$return = array();
	$return['First Name']     = $base_info['first_name'];
	$return['Last Name']      = $base_info['last_name'];
	$return['E-mail Address'] = $base_info['email_address'];

	if (sizeof($profile_info) > 0)
	{
		foreach ($profile_info as $item)
		{
			$return[$item['name']] = $more_info['field_' . $item['id']];
		}
	}

	return $return;
}

function US_SendUserEmail($to, $subject, $message, $html = false)
{
	require_once('includes/class.phpmailer.php');
	
	$mail = new PHPMailer();
	$mail->From = ADMIN_EMAIL;
	$mail->FromName = ADMIN_FROM;
	
	$mail->AddAddress($to);

	if ($html) 
	{ 
		$message = nl2br($message); 
		$mail->IsHTML(true);
	}
	
	$mail->Subject = $subject;
	$mail->Body    = $message;
	$mail->Send();
}
?>

