<?php

// processes a request from the contact form, returns TRUE or $error
function CF_Process(&$post, $fields, $component_id)
{
	global $db, $body;
	$log_data = array();
	$data     = $post['fields'];
	$settings = CF_GetSettings($component_id);
	
	$counter     = 0;
	$message     = '';
	$invalid     = false;
	
	foreach ($fields as $field)
	{
		// go thru fields
		$required      = ($field['required'] == 'required') ? TRUE : FALSE;
		$value         = $data[$counter];
		$field_invalid = false;
		
		if ($required)
		{
			if (is_array($value))
			{
				if (sizeof($value) == 0)
				{
					$error .= 'Missing required field: ' . $field['name'] . '<br />';
					$invalid = true;
					$field_invalid = true;
				}
			}
			else
			{
				if ( (strlen($value) == 0) and ($field['type'] != 'file') )
				{
					$error .= 'Missing required field: ' . $field['name'] . '<br />';
					$invalid = true;
					$field_invalid = true;
				}
			}
		}
		
		if (!$field_invalid)
		{

			if ($field['type'] == 'text')
			{
				$regex = $field['pattern'];
				switch($regex)
				{
					case 'email':
						//$regexp = "/^[^0-9][A-z0-9_]+([.][A-z0-9_]+)*[@][A-z0-9_]+([.][A-z0-9_]+)*[.][A-z]{2,4}$/";
						$regexp = '/^[0-9a-zA-Z]([-.\w]*[0-9a-zA-Z])*@([0-9a-zA-Z][-\w]*[0-9a-zA-Z]\.)+[a-zA-Z]{2,9}$/';
						break;
					case 'alpha':
						$regexp = '/[A-z\s]/';
						break;
					case 'numeric':
						$regexp = '/[0-9]/';
						break;
					case 'alpha_numeric':
						$regexp = '/[A-z0-9\s]/';
						break;
					case 'phone':
						$regexp = '/[0-9\-\(\)\s]/';
						break;
					default:
						$regexp = '/.*/';
						break;
				}

				if (($required) or (strlen($value) > 0))
				{
					if (preg_match($regexp, $value))
					{
						if ($regex == 'email')
						{
							$replyTo = $value;
						}
					}
					else
					{
						$error .= 'Invalid ' . $field['name'] . '<br />';
						$invalid = true;
					}
				}
				
				$k = $field['name'];
				$v = $value;
			}
			elseif ($field['type'] == 'textarea')
			{
				$k = $field['name'];
				$v = $value;
			}
			elseif ($field['type'] == 'select')
			{
				$k = $field['name'];
				$v = $value;
				
				$options = $field['options'];
				foreach ($options as $option)
				{
					$len = strlen($v);
					if (substr($option, 0, $len+1) == $value . '|')
					{
						$custom_address = substr($option, $len+1);
					}
				}
			}
			elseif ($field['type'] == 'file')
			{
				$check = $_FILES['fields']['error'][$counter];
				$name  = $_FILES['fields']['name'][$counter];
				$tmp   = $_FILES['fields']['tmp_name'][$counter];

				if ($check == 0)
				{
					$new_name = rand(1000,9999) . '_' . $name;  // prefix with random number to ensure unique filename
					$storage_dir = 'includes/storage/contact/'.$component_id.'/upload/';
					if (Pico_StorageDir($storage_dir))
					{
						$dest = $storage_dir . $new_name;
						@move_uploaded_file($tmp, $dest);
						@chmod($dest, 0666);
						$post['fields'][$counter] = $new_name;
					}
					elseif ($required)
					{
						$error .= 'Error uploading file ('.$field['name'].'). Please contact the site administrator<br />';
					}
				}
				elseif ($required)
				{
					$error .= 'Error attaching file ('.$field['name'].')<br />';
					$invalid = true;
				}

				$k = $field['name'];
				$v = $name;
			}
			elseif ($field['type'] == 'checkbox')
			{
				$k = $field['name'];
				$v = ($value == 'on') ? 'Yes' : 'No';
			}
			elseif ($field['type'] == 'check_list')
			{
				$k = $field['name'];
				array_shift($value); // this gets rid of our "foo" entry so that we can garantee a field has been passed!
				$v = (sizeof($value) > 0) ? implode(', ', $value) : '';
			}
			elseif ($field['type'] == 'double_list')
			{
				$k = $field['name'];
				$col1 = $field['options'][0];
				$col2 = $field['options'][1];
				array_shift($value[1]); // get rid of "foo"
				array_shift($value[2]);

				$v = $col1 .': ' . implode(',', $value[1]) . '; ' . $col2 . ': ' . implode(',', $value[2]);
			}
			elseif ($field['type'] == 'dir_dropdown')
			{
				$k = $field['name'];
				$v = $value;
			}
			elseif ($field['type'] == 'terms')
			{
				if ($value != 'true')
				{
					$error .= 'Please ensure that you have read and agree to the '.$field['name'].'<br />';
					$invalid = true;
				}
				$v = null;
			}
			elseif ($field['type'] == 'scale')
			{
				$k = $field['name'];
				$v = '';

				// see if we have any options
				$options = $field['options'];
				if (sizeof($options) == 0) { $options[] = ''; }

				for ($x = 0; $x < sizeof($options); $x++)
				{
					$col = $options[$x];
					if (strlen($col) > 0) { $v .= $col . ': '; }
					$v .= $value[$x];
					if (strlen($col) > 0) { $v .= '; '; }
				}
			}
			
			if ($v != null)
			{
				$caption = (strlen($field['caption']) > 0) ? '('.$field['caption'].')' : '';
				$message .= $k . ' '.$caption.': ' . $v ."\n\n";
				$log_data[$k] = $v;
			}
		}
		
		$counter++;
	}

	if (!$invalid)
	{
		return TRUE;
	}
	
	return $error;
}

function CF_GetDirectoryFields($component_id, $dir_component_id)
{
	global $db;
	$contact_table = DB_PREFIX . 'pico_contact_form';
	
	$field_data = $db->result('SELECT `fields` FROM `'.$contact_table.'` WHERE `component_id`=?', $component_id);
	$field_data = unserialize($field_data);
	
	$addl_info = $db->result('SELECT `additional_info` FROM `'.DB_COMPONENT_TABLE.'` WHERE `component_id`=?', $dir_component_id);
	$data = unserialize($addl_info);
	
	$output = '';
	
	$fields = $data['fields'];
	if (sizeof($fields) > 0)
	{
		$output .= '<select name="field[directory_field]">';
		$output .= '<option value=""></option>';
		foreach ($fields as $field_id => $field)
		{
			$selected = ($field_data['directory_field'] == $field_id) ? 'selected="selected"' : '';
			$output .= '<option value="'.$field_id.'" '.$selected.'>'.$field['name'].'</option>';
		}
		$output .= '</select>';
	}
	
	return $output;
}

function CF_ShowButtonEditForm($button_name, $component_id, $buttons = null)
{
	if (!is_array($buttons)) { $buttons = array(); }
	global $body;

	$preview_file = 'includes/storage/contact/'.$component_id.'/buttons/'.$buttons[$button_name];
	if (is_file($preview_file)) {
		$img_url = $body->url($preview_file);
		$preview = <<<HTML
<table>
<tr><td>Current Image:</td><td>
	<img src="$img_url" />
</td></tr>
</table>
HTML;
	}
	else
	{
		$preview = '';
	}

	$output = <<<HTML
<table border="0" cellpadding="1" cellspacing="0">
<tr>
	<td>
		<input type="checkbox" name="rbuttons[$button_name]" value="1" /> Remove <br />
		<input type="text" readonly="readonly" name="buttons[$button_name]" id="cfbutton_$button_name" value="" />
	</td>
	<td>
		<div id="preview_$button_name">$preview</div>
	</td>
</tr>
</table>
HTML;

	return $output;
}

function CF_MigrateButtons($component_id)
{
	global $db, $body;
	$contact_table = DB_PREFIX . 'pico_contact_form';

	$fields     = $db->assoc('SHOW COLUMNS FROM `'.$contact_table.'`');
	$all_fields = array();

	foreach ($fields as $f)
	{
		$all_fields[] = $f['Field'];
	}

	if (!in_array('buttons', $all_fields))
	{
		$db->run('ALTER TABLE `'.$contact_table.'` ADD COLUMN `buttons` BLOB');
	}

	$button = $db->result('SELECT `submit_button` FROM `'.$contact_table.'` WHERE `component_id`=?', $component_id);
	// see if this button exists
	$button_src = 'includes/content/contact/storage/buttons/' . $button;
	if (is_file($button_src))
	{
		$storage_dir = 'includes/storage/contact/'.$component_id.'/buttons/';
		$writable = Pico_StorageDir($storage_dir);

		if ($writable)
		{
			$new_path = $storage_dir . $button;
			@rename($button_src, $new_path);
			if (!is_file($new_path)) {
				return FALSE;
			}
			else {
				// update database
				$buttons = $db->result('SELECT `buttons` FROM `'.$contact_table.'` WHERE `component_id`=?', $component_id);
				$buttons = unserialize($buttons);
				if (!is_array($buttons)) { $buttons = array(); }
				$buttons['submit'] = $button;
				$db->run('UPDATE `'.$contact_table.'` SET `buttons`=? WHERE `component_id`=?', serialize($buttons), $component_id);
				return TRUE;
			}
		}
	}
	// no buttons to migrate, return true
	return TRUE;
}


// save contact form data
function CF_SaveTempContact($component_id, $page, $data)
{
	global $db;
	$temp_history = DB_PREFIX . 'pico_contact_temp_history';

	// see if we have an entry
	$session_id = session_id();
	$entry_id = $db->result('SELECT `entry_id` FROM `'.$temp_history.'` WHERE `component_id`=? AND `session_id`=?', $component_id, $session_id);

	if (is_numeric($entry_id))
	{
		// update
		$saved_data = $db->assoc('SELECT * FROM `'.$temp_history.'` WHERE `entry_id`=?', $entry_id);
		$answers = unserialize($saved_data['answers']);
		$answers[$page] = $data['fields'];
		$db->run('UPDATE `'.$temp_history.'` SET `answers`=?, `current_page`=? WHERE `entry_id`=?',
			serialize($answers), $page+1, $entry_id
		);
	}
	else
	{
		$answers = array();
		$answers[$page] = $data['fields'];
		// insert
		$entry_id = $db->insert('INSERT INTO `'.$temp_history.'` (`start_time`, `component_id`, `session_id`, `current_page`, `answers`) VALUES (?,?,?,?,?)',
			time(), $component_id, $session_id, $page+1, serialize($answers)
		);
	}

	return $entry_id;
}

function CF_CompleteForm($component_id, $fields_by_page)
{
	global $db;
	$temp_history = DB_PREFIX . 'pico_contact_temp_history';
	$entry        = $db->assoc('SELECT * FROM `'.$temp_history.'` WHERE `component_id`=? AND `session_id`=?', $component_id, session_id());
	$answers      = unserialize($entry['answers']);
	$settings     = CF_GetSettings($component_id);

	if (is_array($entry))
	{
		// at this point we should have everything filled out with the data we want, we just need to format it to send out and log
		$email      = array();
		$email_raw  = array();
		$log_data   = array();
		$log_attach = array();
		// key = title, val = response

		$no_email = array('info', 'break', 'group'); // field types that dont get sent 
		$attachments = array();

		$page_counter = 0;
		foreach ($fields_by_page as $page_fields)
		{
			// go thru page_fields, see if we have any groups, and mash them into a single dimensional array
			$new_page_fields = array();

			for ($x = 0; $x < sizeof($page_fields); $x++)
			{
				$f = $page_fields[$x];
				$new_page_fields[] = $f;
				
				if ($f['type'] == 'group')
				{
					$children = $f['children'];
					if (sizeof($children) > 0) {
						foreach ($children as $child) {
							$new_page_fields[] = $child;
						}
					}
				}
			}

			$page_fields = $new_page_fields;

			ksort($answers[$page_counter], SORT_NUMERIC);
			foreach ($page_fields as $field)
			{
				unset($title, $value);

				if ($field['type'] == 'group') {
					$email_raw[$field['name']] = '';
				}

				if (!in_array($field['type'], $no_email)) {
					$title    = $field['name'];
					$val      = array_shift($answers[$page_counter]);

					if ($field['type'] == 'select')
					{
						foreach ($field['options'] as $option)
						{
							$len = strlen($val);
							if (substr($option, 0, $len+1) == $val . '|')
							{
								$custom_address = substr($option, $len+1);
							}
						}
					}
					elseif ($field['type'] == 'check_list')
					{
						array_shift($val); // get rid of "foo"
					}
					elseif ($field['type'] == 'double_list')
					{
						$col1 = $val[1];
						$col2 = $val[2];
						array_shift($col1); // get rid of "foo"
						array_shift($col2); // get rid of "foo"

						$col1title = $field['options'][0];
						$col2title = $field['options'][1];

						$val = $col1title .': ' . implode(',', $col1) . '; ' . $col2title . ': ' . implode(',', $col2);
					}
					elseif ($field['type'] == 'scale')
					{
						$options = $field['options'];
						if (sizeof($options) == 0) { $options[] = ''; }
						$v = '';

						for ($x = 0; $x < sizeof($options); $x++)
						{
							$col = $options[$x];
							if (strlen($col) > 0) { $v .= $col . ': '; }
							$v .= $val[$x];
							if (strlen($col) > 0) { $v .= '; '; }
						}

						$val = $v;
					}

					$value = (is_array($val)) ? implode("\n", $val) : $val;

					// check for "reply to"
					if (preg_match('/^[0-9a-zA-Z]([-.\w]*[0-9a-zA-Z])*@([0-9a-zA-Z][-\w]*[0-9a-zA-Z]\.)+[a-zA-Z]{2,9}$/', $value))
					{
						$replyTo = $value;
					}

					if ($field['type'] == 'file')
					{
						$parts = explode('_', $value);
						array_shift($parts);
						$actual_file_name = implode('_', $parts); // files have ####_ prefixed on them, but dont need to be included in the name

						$storage_dir = getcwd() . '/includes/storage/contact/'.$component_id.'/upload/';
						$full_file   = $storage_dir . $val;

						if (is_file($full_file))
						{
							$attachments[] = array(
								'name' => $actual_file_name,
								'path' => $full_file
							);

							$log_attach[$value] = base64_encode(file_get_contents($full_file));
							$email_raw[$field['name']] = $actual_file_name;
						}
					}
					else
					{
						$log_data[$title]  = $value;
						$email_raw[$title] = $value;
					}
				}
			}
			$page_counter++;
		}

		require_once('includes/class.phpmailer.php');
		$mail = new PHPMailer();
		$mail->From = ADMIN_EMAIL;
		$mail->FromName = ADMIN_FROM;
		$recipient = (isset($custom_address)) ? $custom_address : $settings['recipient_address']; // TODO: Custom address
		$mail->AddAddress($recipient);
		

		if (isset($replyTo)) { $mail->AddReplyTo($replyTo); }
		
		$mail->Subject = $settings['from_subject'];

		// log data
		$history_table = DB_PREFIX . 'pico_contact_history';
		
		$history_entry_id = $db->insert('INSERT INTO `'.$history_table.'` (`component_id`, `timestamp`, `log_data`, `attachments`) VALUES (?,?,?,?)', 
			$component_id, time(), serialize($log_data), serialize($log_attach)
		);

		if ($settings['message_format'] == 'html')
		{
			$mail->IsHTML(true);
			$mail->Body = '<p>Someone has contacted you using your form at ' . ADMIN_FROM .'.</p>';
			$mail->Body .= '<table border="1" cellpadding="2" cellspacing="0">';

			foreach ($email_raw as $k => $v)
			{
				//$k = $line[0];
				//$v = $line[1];
				if (strlen($v) == 0) {
					$mail->Body .= "<tr><td style=\"font-weight: bold\" colspan=\"2\">$k</td></tr>";
				}
				else {
					$mail->Body .= "<tr><td style=\"font-weight: bold\">$k</td><td>$v</td></tr>";
				}
				
			}
			$mail->Body .= '</table>';
		}
		else
		{
			$mail->IsHTML(false);
			$mail->Body = 'Someone has contacted you using your form at ' . ADMIN_FROM .'.' . "\n\n";
			foreach ($email_raw as $k => $v)
			{
				//$k = $line[0];
				//$v = $line[1];

				$mail->Body .= $k . ': ' . $v . "\n";
			}
		}

		

		if (sizeof($attachments) > 0)
		{
			foreach ($attachments as $attachment)
			{
				//attach to email
				$mail->AddAttachment($attachment['path'], $attachment['name']);
			}
		}

		$db->run('DELETE FROM `'.$temp_history.'` WHERE `entry_id`=?', $entry['entry_id']);

		// email user
		if ($mail->Send())
		{
			if (sizeof($attachments) > 0)
			{
				foreach ($attachments as $attachment)
				{
					//attach to email
					@unlink($attachment['path']);
				}
			}

			if (($settings['send_user_copy'] == 1) and (isset($replyTo)))
			{
				$copy = new PHPMailer();
				$copy->From = ADMIN_EMAIL;
				$copy->FromName = ADMIN_FROM;
				$copy->AddAddress($replyTo);
				$copy->Subject = $settings['copy_subject'];
				$copy->Body = $settings['copy_message'];
				$copy->Send();
			}

			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

}

function CF_GetSettings($component_id)
{
	$contact_table  = DB_PREFIX . 'pico_contact_form';
	global $db;

	if (isset($_GLOBALS['contact_form_settings_'.$component_id])) 
	{
		// cache
		return $_GLOBALS['contact_form_settings_'.$component_id];
	}
	else
	{
		$settings = $db->assoc('SELECT * FROM `'.$contact_table.'` WHERE `component_id`=?', $component_id);
		$settings['buttons'] = unserialize($settings['buttons']);
		if (!is_array($settings['buttons'])) { $settings['buttons'] = array(); }
		$settings['recaptcha'] = unserialize($settings['recaptcha']);
		if (!is_array($settings['recaptcha'])) { $settings['recaptcha'] = array(); }

		$_GLOBALS['contact_form_settings_'.$component_id] = $settings;
		return $settings;
	}
}

function CF_GetFields($component_id)
{
	if (isset($_GLOBALS['contact_form_fields_'.$component_id])) 
	{
		// cache
		return $_GLOBALS['contact_form_fields_'.$component_id];
	}
	else 
	{
		$contact_table  = DB_PREFIX . 'pico_contact_form';
		global $db;

		$fields   = $db->result('SELECT `fields` FROM `'.$contact_table.'` WHERE `component_id`=?', $component_id);
		$fields   = unserialize($fields);
		if (!is_array($fields)) { $fields = array(); }
		$_GLOBALS['contact_form_fields_'.$component_id] = $fields;
		return $fields;
	}
}

function CF_GetFormButton($component_id, $isEnd = FALSE)
{
	$settings = CF_GetSettings($component_id);

	$output   = '';
	global $body;

	if ($isEnd) {
		$file1 = 'submit';
		$file2 = 'submit_hover';
	}
	else {
		$file1 = 'continue';
		$file2 = 'continue_hover';
	}

	$submit = $buttons['submit'];
	$button_file = 'includes/storage/contact/'.$component_id.'/buttons/'.$settings['buttons'][$file1];

	if (is_file($button_file))
	{
		$mouseover = '';
		$hover_file = 'includes/storage/contact/'.$component_id.'/buttons/'.$settings['buttons'][$file2];
		if (is_file($hover_file)) {
			$mouseover = 'onmouseover="this.src=\''.$body->url($hover_file).'\'" onmouseout="this.src=\''.$body->url($button_file).'\'"';
		}
		$output .= '<input type="image" src="'.$body->url($button_file).'" '.$mouseover.' />';
	}
	else
	{
		$output .= '<input type="submit" class="submit" value="Continue" />';
	}

	return $output;
}
?>