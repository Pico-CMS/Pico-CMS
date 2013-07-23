<?php
/**
*   CFLayout
*
*   The purpose of this class is to provide a base layout for the contact form. 
*	Additional layouts will extend this class to provide their own means of styling the contact form
*/
Class stacked Extends CFLayout
{
	/**
     * Provides starting HTML output for contact form (ie: starting a table)
     * @return string
     */
	function get_header()
	{
		$output = '';

		if ($this->total_pages > 1)
		{
			$x = $this->current_page + 1;
			$y = $this->total_pages;
			$output .= '<p class="page_indicator">Page <span class="num">'.$x.'</span> of <span class="num">'.$y.'</span></p>';
		}
		
		$output .= '<p class="required_desc">* - Indicates a required field</p>';
		
		return $output;
	}

	/**
     * Provides ending HTML output for contact form (ie: ending a table)
     * @return string
     */
	function get_footer()
	{
		return '';
	}

	/**
     * Formats the HTML output of a given field
     * @param int $counter - Incremental counter for generating classes or identifying a field numerically (starts at 0)
     * @param string $field_type  - The type of field
     * @param string $field_title - The user entered title of the field
     * @param bool $required  - Determine if this field is required or not
     * @param string $options - User entered options field
     * @param string $caption - User entered caption
     * @param string $value - Default value
     * @return string HTML output
     */
	function get_field($counter, $field_type, $field_title, $required, $pagenum, $options = array(), $caption = '', $value = null)
	{
		$class = ($counter % 2 == 0) ? 'a' : 'b';
		$_caption = (strlen($caption) > 0) ? '<div class="caption">'.$caption.'</div>' : '';
		$req = ($required) ? ' *' : '';

		$value = (is_string($value)) ? htmlspecialchars($value) : $value;

		switch ($field_type)
		{
			case 'text':
				$field = '<input type="text" class="text" name="fields['.$counter.']" value="'.$value.'" />';
				break;
			case 'textarea':
				$field = '<textarea class="textarea" name="fields['.$counter.']">'.$value.'</textarea>';
				break;
			case 'checkbox':
				$field  = '<input type="hidden" name="fields['.$counter.']" value="off" />';
				$field .= '<input type="checkbox" value="on" name="fields['.$counter.']" '.(($value == 'on')?'checked="checked"':'').' />';
				break;
			case 'check_list':
				$field = '<input type="hidden" name="fields['.$counter.'][]" value="foo" />'; // so we always have SOMETHING in this array
				$x = 0;
				
				if (sizeof($options) > 0)
				{
					foreach ($options as $option)
					{
						if (!is_array($value)) { $value = array(); }
						$field .= '<input type="checkbox" name="fields['.$counter.'][]" value="'.$option.'" '.( ( (is_array($options)) and (in_array($option, $value)) )   ?'checked="checked"':'').' />'.$option.'<br />';
						$x++;
					}
				}
				break;
			case 'file': 
				$field = '<input type="hidden" name="fields['.$counter.']" value="" /><input type="file" name="fields['.$counter.']" />';
				break;
			case 'terms':
				$field = '<textarea class="terms" readonly="readonly">'.implode("\n", $options).'</textarea>
				<table border="0" cellpadding="0" cellspacing="0" class="terms">
				<tr>
					<td><input type="checkbox" name="fields['.$counter.']" value="true" /></td>
					<td class="terms_caption">'.$caption.'</td>
				</tr>
				</table>';
				$caption = $_caption = ''; //erase the caption cause we're using it here
				break;
			case 'group':
			case 'info':
				//$field = implode("\n", $options);
				$field = '';
				break;
			case 'scale':

				$c1 = ($value == 1) ? 'checked="checked"' : '';
				$c2 = ($value == 2) ? 'checked="checked"' : '';
				$c3 = ($value == 3) ? 'checked="checked"' : '';
				$c4 = ($value == 4) ? 'checked="checked"' : '';
				$c5 = ($value == 5) ? 'checked="checked"' : '';

				$field = <<<HTML
<table class="scale" cellpadding="0" cellspacing="0" border="0">
<tr>
	<td class="num">1.</td><td class="button"><input type="radio" name="fields[$counter]" value="1" $c1 /></td>
	<td class="num">2.</td><td class="button"><input type="radio" name="fields[$counter]" value="2" $c2 /></td>
	<td class="num">3.</td><td class="button"><input type="radio" name="fields[$counter]" value="3" $c3 /></td>
	<td class="num">4.</td><td class="button"><input type="radio" name="fields[$counter]" value="4" $c4 /></td>
	<td class="num">5.</td><td class="button"><input type="radio" name="fields[$counter]" value="5" $c5 /></td>
</tr>
</table>
HTML;
				break;
			case 'select':
				$field = '<div class="select"><select name="fields['.$counter.']">';
			
				//$field .= '<option value="">'.$field_title.$req.'</option>';
				if (!$required) { $field .= '<option value=""></option>'; } // add optional blank field
				
				if ((sizeof($options) > 0) and (is_array($options)))
				{
					foreach ($options as $option)
					{
						if (strstr($option, '|'))
						{
							list($option, $email) = explode('|', $option);
						}
						$field .= '<option value="'.$option.'" '.(($value == $option)? 'selected="selected"': '').'>'.$option.'</option>';
					}
				}
				$field .= '</select></div>';
				break;
			case 'dir_dropdown':
				$dir_source = $options['directory_source'];
				$dir_field  = $options['directory_field'];
				$field = '';
				
				if ( (is_numeric($dir_source)) and (is_numeric($dir_field)) )
				{
					global $db;
					$addl_info = $db->result('SELECT `additional_info` FROM `'.DB_COMPONENT_TABLE.'` WHERE `component_id`=?', $dir_source);
					$directory_data    = unserialize($addl_info);
					$directory_options = $directory_data['options'];
					
					$dir_fields   = $directory_data['fields'];
					$linked_field = $dir_fields[$dir_field];
					$field_name   = $linked_field['field_name'];
					
					// get all values in the database with field_name
					$directory_table = DB_PREFIX . 'directory_' . $dir_source;
					$asc_or_desc = (in_array($directory_options['asc_or_desc'], array('ASC', 'DESC'))) ? $directory_options['asc_or_desc'] : 'ASC';
					$results = $db->force_multi_assoc('SELECT `entry_id`, `'.$field_name.'` FROM `'.$directory_table.'` ORDER BY `'.$directory_options['browse_by'].'` ' . $asc_or_desc);

					if (is_array($results))
					{
						$field .= '<select name="fields['.$counter.']">';
						//$field .= '<option value="">'.$field_title.$req.'</option>';
						if (!$required) { $field .= '<option value=""></option>'; } // add optional blank field
						foreach ($results as $result)
						{
							$val = str_replace('"', '\\"', $result[$field_name]);
							$selected = '';
							
							// can use "link" or the field's name in the URL we are making to auto select
							if (($params[1] == 'link') or ($params[1] == $field_name))
							{
								if ($params[2] == $result['entry_id'])
								{
									$selected = 'selected="selected"';
								}
							}
							else
							{
								if ($data[$counter] == $val)
								{
									$selected = 'selected="selected"';
								}
							}
							
							$field .= '<option value="'.$val.'" '.$selected.'>'.$result[$field_name].'</option>';
						}
						$field .= '</select>';
					}
				}
				break;
			case 'double_list':
				$field = '<input type="hidden" name="fields['.$counter.'][1][]" value="foo" />';
				$field .= '<input type="hidden" name="fields['.$counter.'][2][]" value="foo" />';
				$field .= '<table class="double_list" cellpadding="0" cellspacing="0" border="0">';

				$col1 = array_shift($options);
				$col2 = array_shift($options);
				$field .= '<tr><th>'.$col1.'</th><th>'.$col2.'</th></tr>';
				for ($x = 0; $x < sizeof($options); $x++)
				{
					$option = $options[$x];
					$field .= '<tr><td>';
					$field .= '<input type="checkbox" name="fields['.$counter.'][1][]" value="'.$option.'" '. (( (is_array($value)) and(in_array($option, $value[1])) ) ? 'checked="checked"':'').' />'.$option.'<br />';
					$field .= '</td><td>';
					$field .= '<input type="checkbox" name="fields['.$counter.'][2][]" value="'.$option.'" '. (( (is_array($value)) and(in_array($option, $value[2])) ) ? 'checked="checked"':'').' />'.$option.'<br />';
					$field .= '</td></tr>';
				}
				$field .= '</table>';
				break;
			default:
				$field = '';
				break;
		}

		if (!isset($output))
		{
			$req = ($required) ? '*' : '';
			$output = <<<HTML
<div class="stack $class">
	<div class="name">$field_title $req</div>
	$_caption
	<div class="field">$field</div>
</div>
HTML;
		}
		return $output;
	}

	/**
     * Formats the HTML for the captcha
     * @param string $captcha_image - Full path to captcha image
     * @return string HTML output
     */
	function get_captcha($captcha_img)
	{
		$output = <<<HTML
<div class="captcha">
	<div class="name">Image Verification</div>
	<img src="$captcha_img" />
	<div class="caption">Please type the phrase above into the box below</div>
	<input class="captcha-input" type="text" name="verify" />
</div>
HTML;
		return $output;
	}

	/**
     * Uses ReCaptcha
     * @param string $public_key - ReCaptcha Public Key
     * @return string HTML output
     */
	function get_recaptcha($public_key)
	{
		$recaptcha = recaptcha_get_html($public_key);
		$output = <<<HTML
<div class="captcha">
	<div class="name">Image Verification</div>
	$recaptcha
	</div>
</div>
HTML;
		return $output;
	}
}
?>