<?php

$additional_info = $db->result('SELECT `additional_info` FROM `'.DB_COMPONENT_TABLE.'` WHERE `component_id`=?', $component_id);
$settings        = unserialize($additional_info);
if (!is_array($settings)) { $settings = array(); }

$twitter_table = DB_PREFIX . 'twitter_data';
$db->run(<<<SQL
CREATE TABLE IF NOT EXISTS `$twitter_table` (
	`component_id` BIGINT(10) NOT NULL,
	`tweets` BLOB NULL, 
	`last_updated` BIGINT(11) NOT NULL,
	PRIMARY KEY (`component_id`)
);
SQL
);

if (!isset($settings['twitter_access']))
{
	require_once('includes/content/twitter/libs/OAuth.php');
	require_once('includes/content/twitter/libs/twitteroauth.php');

	define('CONSUMER_KEY', 'AxjpVpFx58kc715ThIESTQ');
	define('CONSUMER_SECRET', 'JI2gUT2muPiKe9emiLKahz24YVIAShIMTNj7iRS8MA');

	$callback   = 'http://' . $_SERVER['SERVER_NAME'] . $body->url('includes/content/twitter/auth_setup.php?component_id='.$component_id);
	$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET);

	$request_token = $connection->getRequestToken($callback);

	switch ($connection->http_code) {
	  case 200:
	    /* Build authorize URL and redirect user to Twitter. */
	    $_SESSION['oauth_token'] = $token = $request_token['oauth_token'];
	    $_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];
	    $url = $connection->getAuthorizeURL($token);
	    echo '<div class="ap_overflow">Please set up your twitter access by <a href="'.$url.'">clicking here</a></div>';
	    break;
	  default:
	    /* Show notification if something went wrong. */
	    echo '<div class="ap_overflow">Could not connect to Twitter ('.$connection->http_code.'). Refresh the page or try again later.'.
	    '<pre>' . print_r($connection, true) .'</pre>' .
	    '</div>';
	}

	return;
}

?>
<div class="ap_overflow">
	<form method="post" action="<?=$body->url('includes/content/twitter/submit.php')?>" onsubmit="TWTR_UpdateOptions(this); return false" />
		<input type="hidden" name="component_id" id="component_id" value="<?=$component_id?>" />
		<input type="hidden" name="page_action" value="update_options" />
		<table border="0" cellpadding="2" cellspacing="1" class="admin_list">
		<tr class="a">
			<td>Twitter Name/Search Phrase</td>
			<td><input type="text" name="settings[t_name]" value="<?=$settings['t_name']?>" /></td>
		</tr>
		<tr class="b">
			<td>Number of Entries</td>
			<td><input type="text" name="settings[t_num]" value="<?=$settings['t_num']?>" /></td>
		</tr>
		<tr class="a">
			<td>Title</td>
			<td><input type="text" name="settings[t_title]" value="<?=htmlspecialchars($settings['t_title'])?>" /></td>
		</tr>
		<tr class="b">
			<td>Bottom Link Text</td>
			<td><input type="text" name="settings[t_text]" value="<?=htmlspecialchars($settings['t_text'])?>" /></td>
		</tr>
		<tr class="a">
			<td>Search?</td>
			<td><input type="checkbox" name="settings[t_search]" value="yes" <?=($settings['t_search'] == 'yes') ? 'checked="checked"' : ''?> /></td>
		</tr>
		<tr class="b">
			<td>Remove Twitter Authentication</td>
			<td>
				<p>Select this box if you wish to remove the twitter account that is tied to this component. You will need to refresh your 
					browser and re-enable authentication</p>
				<input type="checkbox" name="twitter_reauth" value="1" />
			</td>
		</tr>
		</table>
		<input type="submit" value="Update" name="submitbtn" />
	</form>
</div>