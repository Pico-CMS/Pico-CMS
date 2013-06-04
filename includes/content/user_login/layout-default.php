<?php
$request = basename($_SERVER['REQUEST_URI']);
if ($request == basename(__FILE__)) { echo 'You cannot access this file directly'; exit(); }

$u_text = (strlen($settings['username_label']) > 0) ? trim($settings['username_label']) : 'Username';
$p_text = (strlen($settings['password_label']) > 0) ? trim($settings['password_label']) : 'Username';

?>

<form method="post" action="<?=$_SERVER['REQUEST_URI']?>" class="login_form">
<p class="login-title"><?=$settings['intro-text']?></p>
<input type="hidden" name="component_id" value="<?=$component_id?>"/>
<input type="hidden" name="page_action" value="loggin_in"/>
	<table border="0" cellpadding="0" cellspacing="0" class="login_form">
		<tr>
			<td><?=$u_text?></td>
			<td><input type="text" class="text" name="login_username" id="login_username" /></td>
		</tr>
		<tr>
			<td><?=$p_text?></td>
			<td><input type="password" class="text" name="login_password" /></td>
		</tr>
	</table>
	<input type="submit" class="submit" value="Log In" />
</form>

<form method="post" action="<?=$_SERVER['REQUEST_URI']?>" class="forgot_password_form">
<p class="login-title"><?=$settings['forgot-pwd-text']?></p>
<input type="hidden" name="component_id" value="<?=$component_id?>"/>
<input type="hidden" name="page_action" value="forgot_password"/>
	<table border="0" cellpadding="0" cellspacing="0" class="login_form">
		<tr>
			<td>E-mail Address</td>
			<td><input type="text" class="text" name="email_address" /></td>
		</tr>
	</table>
	<input type="submit" class="submit" value="Reset Password" />
</form>
