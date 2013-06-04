<?php

$additional_info = $db->result('SELECT `additional_info` FROM `'.DB_COMPONENT_TABLE.'` WHERE `component_id`=?', $component_id);
$settings        = unserialize($additional_info);
if (!is_array($settings)) { $settings = array(); }

$quote_table = DB_PREFIX . 'quote_table';
$quote       = $db->assoc('SELECT * FROM `'.$quote_table.'` WHERE `instance_id`=? ORDER BY RAND() LIMIT 1', $instance_id);

if (is_array($quote))
{
	$quote_text = ($settings['remove_quotes'] == 1) ? $quote['quote'] : '"' . $quote['quote'] . '"';

	echo '<span class="quote">' . $quote_text  . '</span>';
	echo '<span class="who">- ' . $quote['who'] .'</span>';

	if (strlen($quote['website']) > 0) {
		echo '<span class="website">';
		if (strlen($quote['website_url']) > 0)
		{
			echo '<a href="'.$quote['website_url'].'" target="_blank">';
		}
		echo $quote['website'];
		if (strlen($quote['website_url']) > 0)
		{
			echo '</a>';
		}
		echo '</span>';
	}
}
?>