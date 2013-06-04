<?php

$additional_info = $db->result('SELECT `additional_info` FROM `'.DB_COMPONENT_TABLE.'` WHERE `component_id`=?', $component_id);
$settings        = unserialize($additional_info);
if (!is_array($settings)) { $settings = array(); }

if (strlen($settings['t_title']) > 0)
{
	echo '<div class="title">'.$settings['t_title'].'</div>';
}

if (!isset($settings['twitter_access']))
{
	if (USER_ACCESS >= 3) { echo 'Please autheticate to Twitter by editing this component'; }
	return;
}

function object_to_array($data)
{
	if (is_array($data) || is_object($data))
	{
		$result = array();
		foreach ($data as $key => $value)
		{
			$result[$key] = object_to_array($value);
		}
		return $result;
	}
	return $data;
}

$twitter_table = DB_PREFIX . 'twitter_data';

if (sizeof($settings) == 0)
{
	echo "Please set up this component before using";
	return;
}

// pico consumer codes
define('CONSUMER_KEY', 'AxjpVpFx58kc715ThIESTQ');
define('CONSUMER_SECRET', 'JI2gUT2muPiKe9emiLKahz24YVIAShIMTNj7iRS8MA');

$t_config = array(
	'consumer_key'    => CONSUMER_KEY,
	'consumer_secret' => CONSUMER_SECRET,
	'user_token'      => $settings['twitter_access']['oauth_token'],
	'user_secret'     => $settings['twitter_access']['oauth_token_secret'],
);

$twitter_info = $db->assoc('SELECT * FROM `'.$twitter_table.'` WHERE `component_id`=?', $component_id);
$force_update = false;

if (is_array($twitter_info))
{
	// check to see if we have the new style tweets yet.
	$tweets = unserialize($twitter_info['tweets']);
	if (!is_array($tweets)) { $force_update = true; } else {
		if (is_array($tweets[0])) {
			$force_update = true;
		}
	}
}

if ( (!is_array($twitter_info)) or ((time() - $twitter_info['last_updated']) > 300) or ($force_update) )
{
	require_once('includes/content/twitter/libs/tmhOAuth.php');
	require_once('includes/content/twitter/libs/tmhUtilities.php');
	$twitter = new tmhOAuth($t_config);

	if ($settings['t_search'] == 'yes')
	{
		$get_options = array(
			'count' => 200,
			'q' => $settings['t_name'],
		);

		$twitter->request('GET', $twitter->url('1.1/search/tweets'), $get_options);
		$response = json_decode($twitter->response['response']);
		$response = $response->statuses;
	}
	else
	{
		$get_options = array(
			'count' => 200
		);

		$twitter->request('GET', $twitter->url('1.1/statuses/user_timeline'), $get_options);
		$response = json_decode($twitter->response['response']);
	}

	$tweets = array();
	$counter = 0;
	if (sizeof($response) > 0)
	{
		foreach ($response as $tweet)
		{
			//$t = (array) $t;
			$tweet = object_to_array($tweet);
			$tweet = tmhUtilities::entify_with_options($tweet);
			$tweets[] = $tweet;
			$counter++;
			if ($counter >= $settings['t_num'])
			{
				break;
			}
		}
	}

	if (!is_array($twitter_info))
	{
		$db->run('INSERT INTO `'.$twitter_table.'` (`component_id`, `tweets`, `last_updated`) VALUES (?,?,?)',
			$component_id, serialize($tweets), time()
		);
	}
	else
	{
		$db->run('UPDATE `'.$twitter_table.'` SET `tweets`=?, `last_updated`=? WHERE `component_id`=?', serialize($tweets), time(), $component_id);
	}
}
else
{
	$tweets = unserialize($twitter_info['tweets']);
}

if (sizeof($tweets) > 0)
{
	$shown = 0;
	foreach ($tweets as $tweet)
	{
		echo '<div class="message tweet">'.$tweet.'</div>';
		$shown++;
		if ($shown >= $settings['t_num'])
		{
			break;
		}
	}
}

if (strlen($settings['t_text']) > 0)
{
	echo '<div class="follow"><a href="http://twitter.com/'.$settings['t_name'].'" target="_blank">'.$settings['t_text'].'</a></div>';
}

?>