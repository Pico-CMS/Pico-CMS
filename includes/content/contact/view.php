<?php

require_once('includes/content/contact/functions.php');
require_once('includes/content/contact/recaptchalib.php');
require_once('includes/content/contact/baselayout.class.php'); // base layout class
require_once('includes/captcha.class.php');

// Setup =====================================================================================

$settings = CF_GetSettings($component_id);
$fields   = CF_GetFields($component_id);
$captcha  = new Captcha($instance_id, $db, getenv('REMOTE_ADDR')); // generates a new captcha, or restores it if we are verifying
$buttons  = $settings['buttons'];

// make sure contact form has some fields
if (sizeof($fields) == 0) { echo 'Please configure this component before using it'; return; }

$output = '<div id="contact_anchor_'.$component_id.'"></div>'; // for output

if ($settings['layout'] == 'classic') {
	$layout = 'CFLayout';
}
else {
	// include layout class file for fields
	$layout = $settings['layout'];
	require_once('includes/content/contact/layouts/'.$layout.'.php');
}

// End: Setup =================================================================================

// see if this contact form has any pagebreaks

$temp_history = DB_PREFIX . 'pico_contact_temp_history';
$current_page = $db->result('SELECT `current_page` FROM `'.$temp_history.'` WHERE `component_id`=? AND `session_id`=?', $component_id, session_id());
if (!is_numeric($current_page)) { $current_page = 0; }

$field_data     = array(); // default
$fields_by_page = array();
$num_pages      = 0;
foreach ($fields as $f)
{
	if ($f['type'] == 'break') { $num_pages++; } // increase by page
	$fields_by_page[$num_pages][] = $f;
}
$num_pages++; // final page increment

$page_fields = $fields_by_page[$current_page];

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

// See if this form is being submitted

if ($_POST['submit_contact_form'] == $component_id) 
{
	$include = true;
	include('includes/content/contact/submit_contact.php');
}

if (($settings['recaptcha']['use_recaptcha'] == 1) and (!isset($GLOBALS['recaptcha_style'])))
{
	$theme = $settings['recaptcha']['style'];
	$body->add_head(<<<HTML
<script type="text/javascript">  
var RecaptchaOptions = {  
   theme : '$theme'
};  
</script>
HTML
);
	$GLOBALS['recaptcha_style'] = 1;
}

global $params;
if (($current_page > 0) and ($params[1] == 'go-back'))
{
	$answers = $db->result('SELECT `answers` FROM `'.$temp_history.'` WHERE `component_id`=? AND `session_id`=?', $component_id, session_id());
	$answers = unserialize($answers);
	if (is_array($answers))
	{
		$key = $current_page - 1;
		if (isset($answers[$key]))
		{
			unset($answers[$key]);
			$db->run('UPDATE `'.$temp_history.'` SET `answers`=?, `current_page`=? WHERE `component_id`=? AND `session_id`=?', serialize($answers), $key, $component_id, session_id());
		}
	}

	$url = $body->url(CURRENT_ALIAS);
	header('Location: ' . $url);
	return;
}

if (($_POST['submit_contact_form'] == $component_id) or ($current_page != 0)) 
{
	// scroll the contact form into view
	echo <<<HTML
<script type="text/javascript">
document.observe('dom:loaded', function()
{
	document.getElementById('contact_page_$component_id').scrollIntoView();
});
</script>
HTML;
}

// ================================================================

// display errors (if any)
if (isset($error)) {
	$output .= '<p class="error">'.$error.'</pre>';
}

if ($current_page == $num_pages) // meaning the last page
{
	// show complete
	$result = CF_CompleteForm($component_id, $fields_by_page);
	$output .= ($result) ? $settings['complete_message'] : '<p class="error">There was an error sending your request. Please try again.</p>';
	echo $output;
	return; // stop here fo sho
}
elseif ($current_page == 0)
{
	// show initial page
	$output .= $settings['preview_message'];
}
else
{
	// show interim
	$output .= $settings['interim_message'];
	$back = $body->url(CURRENT_ALIAS .'/go-back');
	$output .= <<<HTML
<p class="go_back"><a href="$back">Go back</a></p>
<p class="small">If you return to a previous page, you will lose all information entered on this page. Please review your answers before submitting.</p>
HTML;
}

// get form output
$cf_form  = new $layout($component_id, $num_pages, $current_page);


if ($settings['form_type'] == 0)
{
	// regular
	$form_url = $_SERVER['REQUEST_URI'];
	$output .= <<<HTML
<form method="post" action="$form_url" enctype="multipart/form-data">
<input type="hidden" name="submit_contact_form" value="$component_id" />
HTML;
}
else
{
	// ajax
	$ajax_url = $body->url('includes/content/contact/submit_contact.php');
	$output .= <<<HTML
<form method="post" action="$ajax_url" onsubmit="ContactForm_Submit(this); return false">
<input type="hidden" name="component_id" value="$component_id" />
HTML;
}

$output .= $cf_form->get_header();

for ($x = 0; $x < sizeof($page_fields); $x++)
{
	$f = $page_fields[$x];
	$r = ($f['required'] == 'required') ? TRUE : FALSE;
	$val = $form_data[$x];
	if ($f['type'] != 'break')
	{
		if ($f['type'] == 'dir_dropdown') {
			// requires special options
			$options = array();
			$options['directory_source'] = $f['directory_source'];
			$options['directory_field']  = $f['directory_field'];
		}
		else
		{
			$options = $f['options'];
		}

		$output .= $cf_form->get_field($x, $f['type'], $f['name'], $r, $current_page, $options, $f['caption'], $val);
	}
}

if (isset($settings['submit_button'])) { CF_MigrateButtons($component_id); } // move lecacy buttons if any

// get end of form output
if (($current_page+1) == $num_pages)
{
	if ($settings['recaptcha']['use_recaptcha'] == 1) {
		$output .= $cf_form->get_recaptcha($settings['recaptcha']['pub_key']);
	}
	else {
		$output .= $cf_form->get_captcha($body->url($captcha->Image()));
	}
	
	$output .= $cf_form->get_footer();
	$isEnd  = true;
}
else
{
	$output .= $cf_form->get_footer();
	$isEnd  = false;
}

// get button
$output .= CF_GetFormButton($component_id, $isEnd);
$output .= '</form>';

if (USER_ACCESS > 2)
{
	$output .= '<p><a href="'.$body->url('includes/content/contact/export.php?form_id='.$component_id).'">Contact History</a></p>';
}

echo '<div class="contact_form" id="contact_page_'.$component_id.'">';
echo $output;
echo '</div>';
?>