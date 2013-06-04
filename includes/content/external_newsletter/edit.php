<?php
if ($_GET['refresh'] == 1)
{
	chdir('../../../');
	require_once('core.php');
	$component_id = $_GET['component_id'];
}

if ( (USER_ACCESS < 3) or (!defined('USER_ACCESS')) ) { exit(); }

$data   = $db->result('SELECT `additional_info` FROM `'.DB_COMPONENT_TABLE.'` WHERE `component_id`=?', $component_id);
$options = unserialize($data);
if (!is_array($options)) { $options = array(); }

require_once('includes/content/external_newsletter/fields.php');
require_once('includes/content/external_newsletter/functions.php');

$lists     = $options['lists'];
$listnames = $options['listnames'];
if (!is_array($lists)) { $lists = array(); }
?>
<div class="ap_overflow">
<form method="post" action="<?=$body->url('includes/content/external_newsletter/submit.php')?>" onsubmit="EN_UpdateOptions(this); return false" style="height: auto" />
	<input type="hidden" name="component_id" id="component_id" value="<?=$component_id?>" />
	<input type="hidden" name="page_action" value="update_options" />
	<table border="0" cellpadding="2" cellspacing="1" class="admin_list">
	<tr class="b">
		<td>Newsletter Portal</td>
		<td colspan="2">
			<select name="options[newsletter_portal]">
				<option <?=($options['newsletter_portal'] == 'mc') ? 'selected="selected"' : ''?> value="mc">Mail Chimp</option>
				<option <?=($options['newsletter_portal'] == 'cc') ? 'selected="selected"' : ''?> value="cc">Constant Contact</option>
				<option <?=($options['newsletter_portal'] == 'ic') ? 'selected="selected"' : ''?> value="ic">iContact</option>
			</select>
		</td>
	</tr>
	<tr class="a">
		<td>Layout</td>
		<td colspan="2">
			<select name="options[layout]">
				<option <?=($options['layout'] == 'full') ? 'selected="selected"' : ''?> value="full">Full</option>
				<option <?=($options['layout'] == 'short') ? 'selected="selected"' : ''?> value="short">Short</option>
				<option <?=($options['layout'] == 'short_name') ? 'selected="selected"' : ''?> value="short_name">Short with name</option>
			</select>
		</td>
	</tr>
	<tr class="b">
		<td>Title Text</td>
		<td colspan="2">
			<input type="text" name="options[title_text]" value="<?=$options['title_text']?>" />
		</td>
	</tr>
	<tr class="a">
		<td>Signup Complete Text</td>
		<td colspan="2">
			<input type="text" name="options[signup_complete_text]" value="<?=$options['signup_complete_text']?>" />
		</td>
	</tr>
	<tr class="b">
		<td>Signup Box Text</td>
		<td colspan="2">
			<input type="text" name="options[signup_box_text]" value="<?=$options['signup_box_text']?>" />
		</td>
	</tr>
	<tr class="a">
		<td>Email Box Text</td>
		<td colspan="2">
			<input type="text" name="options[email_box_text]" value="<?=$options['email_box_text']?>" />
		</td>
	</tr>
	<tr class="b">
		<td>Name Box Text</td>
		<td colspan="2">
			<input type="text" name="options[name_box_text]" value="<?=$options['name_box_text']?>" />
		</td>
	</tr>
	<tr class="a">
		<td>Submit Button Text</td>
		<td colspan="2">
			<input type="text" name="options[submit_button_text]" value="<?=$options['submit_button_text']?>" />
		</td>
	</tr>
	<tr class="b">
		<td>Custom Submit Button</td>
		<td colspan="2">
			<?php

			$upload_path = $body->url('includes/upload.php');
			$uploader = new Uploader($upload_path, 'EN_SubmitButton', '', '.jpg, .png, .gif', 'Image Files (jpg/png/gif)', '000000', 'cccccc');
			
			if (strlen($options['submit_button']) > 0)
			{
				$file = EN_GetButton($component_id, $options['submit_button']);
				if ($file != false)
				{
					echo '<img src="'.$body->url($file).'" />';
				}
			}
			
			echo '<p><input type="checkbox" name="remove_button" value="1" /> Remove Uploaded Image</p>';
			echo $uploader->Output();
			echo '<br /><input type="text" readonly="readonly" name="options[submit_button]" id="newsletter_submit_button" value="'.$options['submit_button'].'" />';
			?>
		</td>
	</tr>
	<tr class="a">
		<td>Custom Submit Button (Rollover)</td>
		<td colspan="2">
			<?php

			$upload_path = $body->url('includes/upload.php');
			$uploader = new Uploader($upload_path, 'EN_SubmitButtonRollover', '', '.jpg, .png, .gif', 'Image Files (jpg/png/gif)', '000000', 'BBDE9C');
			
			if (strlen($options['submit_button']) > 0)
			{
				$file = EN_GetButton($component_id, $options['submit_button_rollover']);
				if ($file != false)
				{
					echo '<img src="'.$body->url($file).'" />';
				}
			}
			
			echo '<p><input type="checkbox" name="remove_button_rollover" value="1" /> Remove Uploaded Image</p>';
			echo $uploader->Output();
			echo '<br /><input type="text" readonly="readonly" name="options[submit_button_rollover]" id="newsletter_submit_button_rollover" value="'.$options['submit_button_rollover'].'" />';
			?>
		</td>
	</tr>
<?php

$counter = 0;

if (!isset($options['newsletter_portal']))
{
	echo '</table><input type="submit" value="Update" name="submitbtn" /></form></div>';
	return;
}
else
{
	// show extra fields
	$selected_fields = $fields[$options['newsletter_portal']];
	foreach ($selected_fields as $key => $info)
	{
		$class = ($counter % 2 == 0) ? 'a' : 'b'; $counter++;
		?>
	<tr class="<?=$class?>">
		<td><?=$info['name']?></td>
		<td colspan="2"><div><?=$info['desc']?></div><input type="text" name="options[<?=$key?>]" value="<?=$options[$key]?>" /></td>
	</tr>
		<?php
	}
}

for ($x = 0; $x < sizeof($lists)+1; $x++)
{
	$class = ($counter % 2 == 0) ? 'a' : 'b'; $counter++;
	?>
	<tr class="<?=$class?>">
		<td>List Number/Name <?=$x+1?></td>
		<td><input type="text" name="options[lists][]" value="<?=$lists[$x]?>" /></td>
		<td><input type="text" name="options[listnames][]" value="<?=$listnames[$x]?>" /></td>
	</tr>
	<?php
}
?>
	
	</table>
	<input type="submit" value="Update" name="submitbtn" />
</form>
</div>
