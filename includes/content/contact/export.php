<?php
chdir('../../../');
require_once('core.php');

if (USER_ACCESS < 3) { exit(); }
//require_once('includes/content/contact/export-xls.class.php');

if (!is_file('includes/phpexcel/pico_excel.php')) {
	echo 'Unable to locate required export tools. Please update Pico';
	exit();
}

require_once('includes/phpexcel/pico_excel.php');

$component_id = $_GET['form_id'];

if (!is_numeric($component_id)) { exit(); }

$contact_table = DB_PREFIX . 'pico_contact_form';
$history_table  = DB_PREFIX . 'pico_contact_history';

$fields   = $db->result('SELECT `fields` FROM `'.$contact_table.'` WHERE `component_id`=? ', $component_id);
$fields   = unserialize($fields);

// reorganize the fields so we are getting data we want in the order we want
$all_fields = array();
foreach ($fields as $field) {
	if ($field['type'] == 'group') { // get all the group fields, put them into a linear format
		$children = $field['children'];
		if (sizeof($children) > 0) {
			foreach ($children as $child) {
				if (($child['type'] != 'info') and ($child['type'] != 'file')) // ignore these fields
				{
					$all_fields[] = $child;
				}
			}
		}
	}
	elseif (($field['type'] != 'info') and ($field['type'] != 'file')) // ignore these fields
	{
		$all_fields[] = $field;
	}
}

$fields = $all_fields;

$headers = array();
$headers[] = 'Time';

foreach ($fields as $f)
{
	$headers[] = $f['name'];
}

#create an instance of the class
$xls = new PicoExport('Exported Users', 'contact-history-'.date('M-d-Y').'.xlsx');
$xls->addRow($headers);

array_shift($headers);

// get data
$contact_data = $db->force_multi_assoc('SELECT * FROM `'.$history_table.'` WHERE `component_id`=? ORDER BY `timestamp` DESC', $component_id);

if ( (is_array($contact_data)) and (sizeof($contact_data)> 0) )
{
	foreach ($contact_data as $c)
	{
		$ts = $c['timestamp'];
		
		$values = unserialize($c['log_data']);
		$data = array();
		
		foreach ($headers as $field)
		{
			// this makes sure each field is present
			$data[] = $values[$field];
		}
		
		array_unshift($data, date('h:i m/d/y', $ts));
		$xls->addRow($data);
	}
}

$xls->output();
?>