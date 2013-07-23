<?php
$request = basename($_SERVER['REQUEST_URI']);
if ($request == basename(__FILE__)) { echo 'You cannot access this file directly'; exit(); }

require_once('includes/captcha.class.php');
$captcha = new Captcha('component_' . $component_id, $db, getenv('REMOTE_ADDR'));
$captcha_img = '<img src="'.$body->url($captcha->Image()).'" />';

echo <<<HTML
	<tr><td>First Name</td><td><input type="text" class="text" name="first_name" /></td></tr>
	<tr><td>Last Name</td><td><input type="text" class="text" name="last_name" /></td></tr>
	<tr><td>E-mail Address</td><td><input type="text" class="text" name="email" /></td></tr>
	<tr><td>Verify</td><td>$captcha_img<br /><input type="text" class="text" name="verify" /></td></tr>
	</table>
	$signup_button
HTML;
?>