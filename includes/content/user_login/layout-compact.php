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
<input type="text" class="text" name="login_username" id="login_username" dummytext="<?=$u_text?>" />
<input type="text" class="text" name="login_password" pwd="yes" dummytext="<?=$p_text?>" />
<input type="submit" class="submit" value="Log In" />
</form>