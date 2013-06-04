<?php
chdir('../../../');
require_once('core.php');

if (USER_ACCESS < 3) { exit(); }

$entry_id = $_GET['entry_id'];
$filename = urldecode($_GET['filename']);

$history_table = DB_PREFIX . 'pico_contact_history';
		
$entry_info = $db->assoc('SELECT * FROM `'.$history_table .'` WHERE `entry_id`=?', $entry_id);
if (!is_array($entry_info)) { exit(); }

$attachments = unserialize($entry_info['attachments']);
if (!isset($attachments[$filename])) { exit(); }

$file_data      = base64_decode($attachments[$filename]);
$file_extension = strtolower(array_pop(explode('.', $filename)));

switch( $file_extension )
{
	case "pdf": $ctype="application/pdf"; break;
	case "exe": $ctype="application/octet-stream"; break;
	case "zip": $ctype="application/zip"; break;
	case "doc": $ctype="application/msword"; break;
	case "xls": $ctype="application/vnd.ms-excel"; break;
	case "ppt": $ctype="application/vnd.ms-powerpoint"; break;
	case "gif": $ctype="image/gif"; break;
	case "png": $ctype="image/png"; break;
	case "jpeg":
	case "jpg": $ctype="image/jpg"; break;
	default: $ctype="application/force-download";
}

$file_parts = explode('_', $filename);
array_shift($file_parts);
$actual_filename = implode('_', $file_parts);

header("Pragma: public"); // required
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false); // required for certain browsers 
header("Content-Type: $ctype");
header("Content-Disposition: attachment; filename=\"".basename($actual_filename)."\";" );
header("Content-Transfer-Encoding: binary");
header("Content-Length: ".strlen($file_data));
echo $file_data;
exit();
?>