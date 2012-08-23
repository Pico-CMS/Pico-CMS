<?php
chdir('../');
require_once('core.php');
if (USER_ACCESS < 3) { exit(); }

$page_action = (is_numeric($_GET['edit'])) ? 'edit_user' : 'add_user';

if ($page_action == 'edit_user')
{
	$user_id   = $_GET['edit'];
	$user_info = $db->assoc('SELECT * FROM `'.DB_USER_TABLE.'` WHERE `id`=?', $user_id);
	$extra = '<input type="hidden" name="user_id" value="'.$user_id.'" />';
	$disabled = '';
	$default_user_access = $user_info['access'];
	$pwd = '000000';
}
else
{
	$user_info = '';
	$disabled = 'disabled="disabled"';
	$default_user_access = USER_ACCESS;
	$pwd = '';
	$user_id = 0;
}

// get all the groups that have a profile
$group_table = DB_PREFIX . 'pico_groups';
$user_profile_groups = $db->force_multi_assoc('SELECT * FROM `'.$group_table.'` WHERE `profile_id` != ?', 0);
echo $db->error;
$user_group_dd = '';
if ( (is_array($user_profile_groups)) and (sizeof($user_profile_groups) > 0) )
{
	foreach ($user_profile_groups as $group)
	{
		$selected = '';
		if ($user_id != 0)
		{
			$profile_id = Pico_GetUserProfileId($user_id);
			if ($profile_id == $group['profile_id']) { $selected = 'selected="selected"'; }
		}
		$user_group_dd .= '<option value="'.$group['group_id'].'" '.$selected.'>'.$group['name'].'</option>';
	}
}


?>

<div class="ap_overflow" style="width: 479px">
<form method="post" action="<?=$body->url('includes/ap_actions.php')?>" onsubmit="Pico_AddUserSubmit(this); return false" id="user_form">
<input type="hidden" name="ap_action" value="<?=$page_action?>" />

<table border="0" cellpadding="2" cellspacing="1">
<tr>
	<td colspan="2" class="title">Required Information</td>
</tr>
<?php
if ($page_action == 'add_user')
{
?>
<tr>
	<td>Username</td>
	<td><input type="text" name="username" class="ap_text float_left" onchange="Pico_VerifyUsername()" /><div id="username_indicator" class="indicator"></div></td>
</tr>
<?php
}
?>
<tr>
	<td>Password<br /><span class="mini">6+ Characters</td>
	<td><input type="password" name="password" value="<?=$pwd?>" class="ap_text float_left" onchange="Pico_VerifyPassword()" /><div id="password_indicator" class="indicator"></div></td>
</tr>
<tr>
	<td>Confirm</td>
	<td><input type="password" name="confirm" value="<?=$pwd?>" class="ap_text float_left" onchange="Pico_VerifyConfirm(true)" /><div id="confirm_indicator" class="indicator"></div></td>
</tr>
<tr>
	<td>E-mail Address</td>
	<td><input type="text" name="email_address" class="ap_text float_left" value="<?=$user_info['email_address']?>" onchange="Pico_VerifyEmail(true)" /><div id="email_indicator" class="indicator"></div></td>
</tr>
<tr>
	<td>User Access</td>
	<td><?=UserAccessDrop('access', USER_ACCESS, $default_user_access)?></td>
</tr>
<tr>
	<td colspan="2" class="title">Optional Information</td>
</tr>
<tr>
	<td>First Name</td>
	<td><input type="text" name="first_name" class="ap_text" value="<?=$user_info['first_name']?>" /></td>
</tr>
<tr>
	<td>Last Name</td>
	<td><input type="text" name="last_name" class="ap_text" value="<?=$user_info['last_name']?>" /></td>
</tr>
<?php
if (($page_action == 'edit_user') and ($user_info['user_active'] == 0))
{
	$exp = $user_info['registration_active'];
	$mo = ( (is_numeric($exp)) and ($exp != 0) ) ? date('m', $exp) : '';
	$da = ( (is_numeric($exp)) and ($exp != 0) ) ? date('d', $exp) : '';
	$yr = ( (is_numeric($exp)) and ($exp != 0) ) ? date('y', $exp) : '';
?>
<tr>
	<td>Expiration Date</td>
	<td>
		MM: <input type="text" size="2" maxlength="2" name="expiration[month]" value="<?=$mo?>" /> 
		DD: <input type="text" size="2" maxlength="2" name="expiration[day]" value="<?=$da?>" /> 
		YY: <input type="text" size="2" maxlength="2" name="expiration[year]" value="<?=$yr?>" />
	</td>
</tr>
<?php
}
?>
<tr>
	<td colspan="2" class="title">Profile Information</td>
</tr>
<tr>
	<td>User Group/Profile</td>
	<td>
		<select name="user_profile" onchange="Pico_LoadUserGroupProfile(<?=$user_id?>, this.value)">
			<option value=""></option>
			<?=$user_group_dd?>
		</select>
	</td>
</tr>
</table>
<div id="user_group_profile_info"></div>

<?=$extra?>
<input type="submit" name="submitbtn" value="<?=PrettyVar($page_action)?>" <?=$disabled?> />

</form>
</div>