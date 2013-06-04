<?php

$request = basename($_SERVER['REQUEST_URI']);
if ($request == basename(__FILE__)) { echo 'You cannot access this file directly'; exit(); }

$p            = Pico_Cleanse($_POST, true);
$result       = CF_Process($p, $page_fields, $component_id);

if ( (is_bool($result)) and ($result == TRUE) )
{
	// save data in table
	if (($current_page+1) == $num_pages)
	{
		// verify captcha

		if ($settings['recaptcha']['use_recaptcha'] == 1) {
			$resp = recaptcha_check_answer($settings['recaptcha']['prv_key'], $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);
			$captcha_verified = $resp->is_valid;
		}
		else {
			$captcha = new Captcha($instance_id, $db, getenv('REMOTE_ADDR')); // generates a new captcha, or restores it if we are verifying
			$captcha_verified = $captcha->Verify($_POST['verify']);
		}

		if ($captcha_verified)
		{
			$entry_id = CF_SaveTempContact($component_id, $current_page, $p);
			header('Location:' . $_SERVER['REQUEST_URI']); // redirect to here.
		}
		else
		{
			$error     = 'You did not enter the verification image correctly. Please try again';
			$form_data = $p['fields'];
		}
	}
	else
	{
		// just save
		$entry_id = CF_SaveTempContact($component_id, $current_page, $p);
		header('Location:' . $_SERVER['REQUEST_URI']); // redirect to here.
	}
}
else
{
	$error = $result;
	$form_data = $p['fields'];
}

?>