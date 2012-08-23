<?php
chdir('../');
require_once('core.php');
if (USER_ACCESS < 3) { exit(); }

$user_id = $_GET['user_id'];
if (!is_numeric($user_id)) { exit('Invalid user id'); }
$group_id = $_GET['group_id'];
if (!is_numeric($group_id)) { exit('Invalid group id'); }

if ( ($group_id == 0) and ($user_id != 0) )
{
	$profile_id = Pico_GetUserProfileId($user_id);
}
else
{
	$group_table = DB_PREFIX . 'pico_groups';
	$profile_id  = $db->result('SELECT `profile_id` FROM `'.$group_table.'` WHERE `group_id`=?', $group_id);
}

if (!is_numeric($profile_id))
{
	exit();
}
else
{
	// get user data
	$profile_table = DB_PREFIX . 'user_profile_values_' . $profile_id;
	$user_data = $db->assoc('SELECT * FROM `'.$profile_table.'` WHERE `user_id`=?', $user_id);
	if (!is_array($user_data)) { $user_data = array(); }
}

// get user data

$profile_data = Pico_GetProfileFieldData($profile_id, $user_data);
if (!is_array($profile_data)) { exit(); }
$output = '<input type="hidden" name="save_profile" value="yes" /><table>';

foreach ($profile_data as $item)
{
	if ($item['type'] != 'info')
	{
		$required = ($item['required'] == 1) ? ' *' : '';
		$caption  = ( (strlen($item['caption']) > 0) and ($item['type'] != 'terms') ) ? '<div class="caption">'.$item['caption'].'</div>' : '';
		$output .= '<tr>
			<td class="left">'.$item['name'].$required.'</td>
			<td class="right">'.$item['html'].$caption.'</td>
		</tr>';
	}
	else
	{
		$output .= '<tr><td colspan="2" class="caption">'.$item['options'].'</td></tr>';
	}
}
$output .= '</table>';

echo $output;

?>