<?php
$data   = $db->result('SELECT `additional_info` FROM `'.DB_COMPONENT_TABLE.'` WHERE `component_id`=?', $component_id);
$options = unserialize($data);
if (!is_array($options)) { $options = array(); }

if (sizeof($options) == 0)
{
	echo 'Please configure this plugin before continuing';
	return;
}

if (strlen($options['submit_button']) > 0)
{
	$file = 'includes/content/external_newsletter/buttons/'.$options['submit_button'];
	if (is_file($file))
	{
		$rollover_file = 'includes/content/external_newsletter/buttons/'.$options['submit_button_rollover'];
		$extra = (is_file($rollover_file)) ? 'onmouseover="this.src=\''.$body->url($rollover_file).'\'" onmouseout="this.src=\''.$body->url($file).'\'"' : '';
		$signup_button = '<input type="image" class="submit" src="'.$body->url($file).'" '.$extra.' />';
	}
}

if (!isset($signup_button))
{
	$signup_text = (strlen($options['submit_button_text']) > 0) ? $options['submit_button_text'] : 'Sign Up';
	$signup_button = (is_file('site/images/signup.png')) ? '<input type="image" src="'.$body->url('site/images/signup.png').'" />' : '<input type="submit" name="submitbtn" value="'.$signup_text.'" />';
}

?>
<form method="post" action="<?=$body->url('includes/content/external_newsletter/submit.php')?>" onsubmit="EN_Signup(this); return false">
<input type="hidden" name="component_id" value="<?=$component_id?>" />
<input type="hidden" name="page_action" value="signup" />

<?php

if (sizeof($options['lists']) > 1)
{
	$lists     = $options['lists'];
	$listnames = $options['listnames'];	
	for ($x = 0; $x < sizeof($options['lists']); $x++)
	{
		echo '
		<div class="newsletter_choice">
		<table border="0" cellpadding="0" cellspacing="0">
		<tr><td><input type="checkbox" name="selected_lists['.$x.']" value="1" /></td><td>'.$listnames[$x].'</td></tr>
		</table>
		</div>';
	}
}

echo '<div class="clear"></div>';
echo '<table border="0" cellpadding="0" cellspacing="0" class="newsletter_signup">';

$signup_text    = (strlen($options['signup_box_text']) > 0) ? $options['signup_box_text'] : 'sign up for email updates';
$email_box_text = $options['email_box_text'];
$name_box_text  = $options['name_box_text'];

if ($options['layout'] == 'full') {
require_once('includes/captcha.class.php');
$captcha = new Captcha('component_' . $component_id, $db, getenv('REMOTE_ADDR'));
$captcha_img = '<img src="'.$body->url($captcha->Image()).'" />';
?>
<tr><td>First Name</td><td><input type="text" name="first_name" /></td></tr>
<tr><td>Last Name</td><td><input type="text" name="last_name" /></td></tr>
<tr><td>E-mail Address</td><td><input type="text" name="email" /></td></tr>
<tr><td>Verify</td><td><?=$captcha_img?><br /><input type="text" name="verify" /></td></tr>
</table>
<?php
echo $signup_button;
} 
elseif ($options['layout'] == 'short_name') 
{

?>
<tr>
	<td class="signup_text"><?=$signup_text?></td>
	<td>
		<input type="text" class="text" name="email" value="<?=$email_box_text?>" onfocus="this.value=''" /><br />
		<input type="text" class="text" name="first_name" value="<?=$name_box_text?>" onfocus="this.value=''" />
	</td>
	<td><?=$signup_button?></td>
</tr>
</table>
<?php
}
else 
{


?>
<tr>
	<td class="signup_text"><?=$signup_text?></td>
	<td><input type="text" class="text" name="email" value="<?=$email_box_text?>" onfocus="this.value=''" /></td>
	<td><?=$signup_button?></td>
</tr>
</table>
<?php
}
?>

</form>