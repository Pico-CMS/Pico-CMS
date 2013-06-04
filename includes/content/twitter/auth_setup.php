<?php
chdir('../../../');
require_once('core.php');

require_once('includes/content/twitter/libs/OAuth.php');
require_once('includes/content/twitter/libs/twitteroauth.php');

if ((!defined('USER_ACCESS')) or (USER_ACCESS < 3)) { exit(); }

$component_id    = $_GET['component_id'];
$additional_info = $db->result('SELECT `additional_info` FROM `'.DB_COMPONENT_TABLE.'` WHERE `component_id`=?', $component_id);
$settings        = unserialize($additional_info);

if (!is_array($settings)) { $settings = array(); }
if (!is_numeric($component_id)) { exit(); }

if (isset($_REQUEST['oauth_token']) && $_SESSION['oauth_token'] !== $_REQUEST['oauth_token']) 
{
	unset($_REQUEST['oauth_token']);
	echo 'There was an error completing your request. Please start the twitter authorization process over';
	exit();
}
else
{
	// save settings
	define('CONSUMER_KEY', 'AxjpVpFx58kc715ThIESTQ');
	define('CONSUMER_SECRET', 'JI2gUT2muPiKe9emiLKahz24YVIAShIMTNj7iRS8MA');
	$connection   = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);
	$access_token = $connection->getAccessToken($_REQUEST['oauth_verifier']);

	$settings['twitter_access'] = $access_token;
	$db->run('UPDATE `'.DB_COMPONENT_TABLE.'` SET `additional_info`=? WHERE `component_id`=?', serialize($settings), $component_id);
	header('Location: ' . 'http://' . $_SERVER['SERVER_NAME']);
}

?>