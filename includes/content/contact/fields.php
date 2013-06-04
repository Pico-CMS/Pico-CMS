<?php

$request = basename($_SERVER['REQUEST_URI']);
if ($request == basename(__FILE__)) { echo 'You cannot access this file directly'; exit(); }

$contact_table = DB_PREFIX . 'pico_contact_form';

$fields = $db->result('SELECT `fields` FROM `'.$contact_table.'` WHERE `component_id`=?', $component_id);
$fields = unserialize($fields);
if (!is_array($fields)) { $fields = array(); }

if ($edit_group) {
	$group_title = $fields[$parent_id]['name'];
	$fields = $fields[$parent_id]['children'];
}
else {
	$parent_id = -1;
}

$field_text = '';
$counter = 0;

if (isset($edit_field))
{
	$field_id    = $edit_field;
	$page_action = 'edit_field_post';
}
else
{
	$field_id    = -1;
	$page_action = 'add_field';
	$field_data  = array();
}

if (sizeof($fields) > 0)
{
	foreach ($fields as $field)
	{
		if ($counter == $field_id)
		{
			$field_data = $field;
		}

		$class  = ($counter % 2 == 0) ? 'cfield_a' : 'cfield_b';
		$edit   = '<img src="'.$body->url('includes/icons/edit.png').'"         class="icon click" onclick="CF_EditField('.$component_id.', '.$counter.', '.$parent_id.')" title="Edit" />';
		$delete = '<img src="'.$body->url('includes/icons/delete.png').'"       class="icon click" onclick="CF_DeleteField('.$component_id.', '.$counter.', '.$parent_id.')" title="Delete" />';
		$up     = '<img src="'.$body->url('includes/icons/arrow-up.png').'"     class="icon click" onclick="CF_MoveField('.$component_id.', '.$counter.', '.$parent_id.', \'up\')" title="Up" />';
		$down   = '<img src="'.$body->url('includes/icons/arrow-down.png').'"   class="icon click" onclick="CF_MoveField('.$component_id.', '.$counter.', '.$parent_id.', \'down\')" title="Down" />';
		
		$copy   = ($field['type'] == 'group') ? '<img src="'.$body->url('includes/icons/plus.png').'" class="icon click" onclick="CF_CopyGroup('.$component_id.', '.$counter.')" title="Copy" />' : '';

		$name = ($field['type'] == 'group') ? '<span class="cf_group" onclick="CF_EditGroupFields('.$component_id.', '.$counter.')">'.$field['name'].'</span>' : $field['name'];
		$field_text .= '<div class="'.$class.'"><table><tr><td>'.$edit.$delete.$up.$down . '</td><td>' . $name . '</td><td>'.$copy.'</td></tr></table></div>';
		$counter++;
	}
}

if ($fields_only) {
	if ($edit_group) { echo '<div class="bold">Edit Group - '.$group_title.'</div><p class="click" onclick="CF_EditGroupBack('.$component_id.')">Back</p>'; }
	echo $field_text;
	return;
}

$possible_fields = array(
	'text'=> array(
		'type_title' => 'Text - Single Line',
		'use_pattern' => TRUE,
		'use_options' => FALSE,
		'option_text' => ''
	), 
	'textarea' => array(
		'type_title' => 'Text - Multi-Line',
		'use_pattern' => FALSE,
		'use_options' => FALSE,
		'option_text' => ''
	), 
	'select' => array(
		'type_title' => 'Choice List',
		'use_pattern' => FALSE,
		'use_options' => TRUE,
		'option_text' => 'Each choice on its own line.<br />If you want this choice to determine an alternate recipient, put each choice on its own line, followed by a 
		 (pipe) and then the e-mail address. Example: Help|help@website.com'
	), 
	'check_list' => array(
		'type_title' => 'Checkbox List',
		'use_pattern' => FALSE,
		'use_options' => TRUE,
		'option_text' => 'Put each choice on its own line.'
	), 
	'double_list' => array(
		'type_title' => 'Double Checkbox List',
		'use_pattern' => FALSE,
		'use_options' => TRUE,
		'option_text' => 'First 2 lines for the column names, followed by each choice on its own line.'
	), 
	'checkbox' => array(
		'type_title' => 'Single Checkbox',
		'use_pattern' => FALSE,
		'use_options' => FALSE,
		'option_text' => ''
	), 
	'file' => array(
		'type_title' => 'File Attachment',
		'use_pattern' => FALSE,
		'use_options' => FALSE,
		'option_text' => ''
	), 
	'dir_dropdown' => array(
		'type_title' => 'Directory Dropdown',
		'use_pattern' => FALSE,
		'use_options' => FALSE,
		'option_text' => ''
	),
	'terms' => array(
		'type_title' => 'Terms of Use',
		'use_pattern' => FALSE,
		'use_options' => TRUE,
		'option_text' => 'Include your terms of use in this box'
	), 
	'info' => array(
		'type_title' => 'Info Field',
		'use_pattern' => FALSE,
		'use_options' => FALSE,
		'option_text' => 'Include your info in this box'
	), 
	'scale' => array(
		'type_title' => 'Scale (1 to 5)',
		'use_pattern' => FALSE,
		'use_options' => TRUE,
		'option_text' => 'If you need multiple answers to this question put a heading name on each line below'
	), 
);

// don't show when we are editing a group
if ($edit_group != true) {
	$possible_fields['break'] = array(
		'type_title' => 'Page Break (for multiple pages)',
		'use_pattern' => FALSE,
		'use_options' => FALSE,
		'option_text' => ''
	);
	$possible_fields['group'] = array(
		'type_title' => 'Question Group',
		'use_pattern' => FALSE,
		'use_options' => FALSE,
		'option_text' => ''
	);
}



$cf_html = '';
if (!is_array($field_data)) { $field_data = array(); }

$field_type    = $field_data['type']; // default to text
$disp_cfop     = ($possible_fields[$field_type]['use_options']) ? 'table-row' : 'none';
$disp_pattern  = ($possible_fields[$field_type]['use_pattern']) ? 'table-row' : 'none';
$disp_ddrop    = ($field_data['type'] == 'dir_dropdown') ? 'table-row' : 'none';

$ptitle = ($page_action == 'edit_field_post') ? 'Edit Field' : 'Add New Field';

?>

<table border="0" cellpadding="0" cellspacing="1" style="height: 100%; width: 100%">
<tr>
	<td valign="top">
		<h3><?=$ptitle?></h3>
		<form id="cf_field_form" method="post" action="<?=$body->url('includes/content/contact/submit.php')?>" onsubmit="CF_AddField(this); return false">
		<input type="hidden" name="page_action"  value="<?=$page_action?>" />
		<input type="hidden" name="component_id" value="<?=$component_id?>" />
		<input type="hidden" name="field_id"     value="<?=$field_id?>" />
		<input type="hidden" name="parent_id"    value="<?=$parent_id?>" />
		<table border="0" cellpadding="2" cellspacing="1" class="cform_fields admin_list">
		<tr class="a">
			<td class="bold">Field Name</td>
			<td><input type="text" name="field[name]" class="ap_text" value="<?=$field_data['name']?>" /></td>
		</tr>
		<tr class="b">
			<td class="bold">Field Caption</td>
			<td><textarea class="ap_textarea cf_textarea" name="field[caption]"><?=htmlspecialchars($field_data['caption'])?></textarea></td>
		</tr>
		<tr class="a">
			<td class="bold">Required?</td>
			<td><input type="checkbox" name="field[required]" value="required" <?=($field_data['required'] == 'required') ? 'checked="checked"' : ''?> /></td>
		</tr>
		<tr class="b">
			<td class="bold">Type</td>
			<td>
				<select name="field[type]" onchange="CF_CheckForPattern(this);">
					<option value="">Please choose a field type...</option>
				<?php
				foreach ($possible_fields as $type_name => $f)
				{
					$selected    = ($field_data['type'] == $type_name) ? 'selected="selected"' : '';
					$use_pattern = ($f['use_pattern']) ? 1 : 0;
					$use_options = ($f['use_options']) ? 1 : 0;
					$disp        = ($field_data['type'] == $type_name) ? 'block' : 'none';

					echo '<option '.$selected.' value="'.$type_name.'">'.$f['type_title'].'</option>';

					$cf_html .= '<input type="hidden" id="cf_' . $type_name .'_use_pattern" value="'.$use_pattern.'" />';
					$cf_html .= '<input type="hidden" id="cf_' . $type_name .'_use_cfop" value="'.$use_options.'" />';
					$cf_html .= '<div style="display: '.$disp.'" class="cf_op" id="cfop_'.$type_name.'">'.$f['option_text'].'</div>';
				}
				?>
				</select>
			</td>
		</tr>
		<tr class="a" id="cf_pattern" style="display: <?=$disp_pattern?>">
			<td class="bold">Pattern</td>
			<td>
				<div id="pattern_regex">
					<select name="field[pattern]">
						<option <?=($field_data['pattern'] == '') ? 'selected="selected"' : ''?>              value="">None</option>
						<option <?=($field_data['pattern'] == 'email') ? 'selected="selected"' : ''?>         value="email">E-Mail</option>
						<option <?=($field_data['pattern'] == 'alpha') ? 'selected="selected"' : ''?>         value="alpha">Alpha</option>
						<option <?=($field_data['pattern'] == 'numeric') ? 'selected="selected"' : ''?>       value="numeric">Numeric</option>
						<option <?=($field_data['pattern'] == 'alpha_numeric') ? 'selected="selected"' : ''?> value="alpha_numeric">Alpha-Numeric</option>
						<option <?=($field_data['pattern'] == 'phone') ? 'selected="selected"' : ''?>         value="phone">Phone</option>
					</select>
				</div>
				<!--div id="pattern_regex2" style="display: <?=$disp_pattern2?>">N/A</div-->
			</td>
		</tr>
		
		<?php

			// get directories
			$directories = $db->force_multi_assoc('SELECT * FROM `'.DB_COMPONENT_TABLE.'` WHERE `folder`=?', 'directory');
			
			$dd_dropdown = '<select name="field[directory_source]" onchange="CF_LoadDirectoryFields('.$component_id.', this.value)">';
			$dd_dropdown .= '<option value=""></option>';
			if (is_array($directories))
			{
				foreach ($directories as $directory)
				{
					$selected = ($field_data['directory_source'] == $directory['component_id']) ? 'selected="selected"' : '';
					$dd_dropdown .= '<option value="'.$directory['component_id'].'" '.$selected.'>'.$directory['description'].'</option>';
				}
			}
			$dd_dropdown .= '</select>';
			
			$extra = (is_numeric($field_data['directory_source'])) ? CF_GetDirectoryFields($component_id, $field_data['directory_source']) : 'Please choose a directory';
			
			echo '<tr class="dir_dropdown a" style="display: '.$disp_ddrop.'"><td class="bold">Directory Source</td><td>'.$dd_dropdown.'</td></tr>';
			echo '<tr class="dir_dropdown b" style="display: '.$disp_ddrop.'"><td class="bold">Directory Field</td><td><span id="cf-directory-field">'.$extra.'</span></td></tr>';
			
			// fields
		
		?>
		<tr class="a" id="cf_options_fields" style="display: <?=$disp_cfop?>">
			<td class="bold">Options/Fields</td>
			<td>
				<div id="cfop_box">
					<textarea class="ap_textarea" name="field[options]"><?php if (is_array($field_data['options'])) { echo implode("\n", $field_data['options']); }?></textarea>
					<?=$cf_html?>
				</div>
			</td>
		</tr>
		</table>
		
		<input type="submit" value="<?=$ptitle?>" class="co_button" />
		</form>
		<?php
		if ($page_action == 'edit_field_post') {
			echo '<button class="co_button co_button2" onclick="CF_ReloadFieldArea('.$component_id.')">Cancel</button>';
		}
		?>
	</td>
	<td valign="top" width="270" height="100%">
		<div id="cf_fieldlist">
			<?php
			if ($edit_group) { echo '<div class="bold">Edit Group - '.$group_title.'</div><p class="click" onclick="CF_EditGroupBack('.$component_id.')">Back</p>'; }
 			?>
			<?=$field_text?>
		</div>
	</td>
</tr>
</table>