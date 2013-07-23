<?php

$additional_info = $db->result('SELECT `additional_info` FROM `'.DB_COMPONENT_TABLE.'` WHERE `component_id`=?', $component_id);
$settings        = unserialize($additional_info);
if (!is_array($settings)) { $settings = array(); }

$quote_table = DB_PREFIX . 'quote_table';
$num_quotes  = $db->result('SELECT count(1) FROM `'.$quote_table.'` WHERE `instance_id`=?', $instance_id);

if ($num_quotes > 1)
{
	$lastQuote = (isset($_SESSION['last_quote_'.$component_id])) ? $_SESSION['last_quote_'.$component_id] : 0;
	$quote = $db->assoc('SELECT * FROM `'.$quote_table.'` WHERE `instance_id`=? AND `id` !=? ORDER BY RAND() LIMIT 1', $instance_id, $lastQuote);
}
else
{
	$quote = $db->assoc('SELECT * FROM `'.$quote_table.'` WHERE `instance_id`=? ORDER BY RAND() LIMIT 1', $instance_id);
}

if (is_array($quote))
{
	$_SESSION['last_quote_'.$component_id] = $quote['id'];
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