<?php
chdir('../');
require_once('core.php');
if (USER_ACCESS < 3) { exit(); }

$settings_table = PICO_SETTINGS;

$db->run(<<<SQL
CREATE TABLE IF NOT EXISTS `$settings_table` (
	`keyfield` varchar(32) NOT NULL,
	`keyvalue` varchar(255)
);
SQL
);

$section = $_GET['section'];
$back = '<div class="click back" onclick="Pico_Settings()">[Back]</div>';

if ($section == 'general')
{
	include('includes/settings_general.php');
	return;
}
elseif ($section == 'ftp')
{
	include('includes/settings_ftp.php');
	return;
}
elseif ($section == 'seo')
{
	include('includes/settings_seo.php');
	return;
}
elseif ($section == 'social')
{
	include('includes/settings_social.php');
	return;
}
elseif ($section == 'login')
{
	include('includes/settings_login.php');
	return;
}

?>
<div class="setting_icon" onclick="Pico_SettingsSection('general')">General Settings</div>
<div class="setting_icon" onclick="Pico_SettingsSection('ftp')">FTP</div>
<div class="setting_icon" onclick="Pico_SettingsSection('seo')">SEO/Analytics</div>
<div class="clear"></div>
<div class="setting_icon" onclick="Pico_SettingsSection('login')">Login Page</div>
<div class="setting_icon" onclick="Pico_SettingsSection('social')">Social Media</div>
<div class="setting_icon" onclick="">Users</div>
