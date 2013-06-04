<?php

unset($action); // in case

if ($_GET['reload'] == 1)
{
	chdir('../../../');
	$instance_id  = $_GET['instance_id'];
	$component_id = $_GET['component_id'];
	require_once('core.php');
	if (USER_ACCESS < 3) { exit(); }
	
	if (isset($_GET['edit']))
	{
		$quote_table = DB_PREFIX . 'quote_table';
		$action     = 'edit_quote';
		$quote_id   = $_GET['edit'];
		$quote_text = $db->result('SELECT `quote` FROM `'.$quote_table.'` WHERE `id`=?', $quote_id);
		$quote_who  = $db->result('SELECT `who` FROM `'.$quote_table.'` WHERE `id`=?', $quote_id);
		$quote_info = $db->assoc('SELECT * FROM `'.$quote_table.'` WHERE `id`=?', $quote_id);
		$form_title = 'Edit Quote';
	}
}

if (!isset($action))
{
	$action     = 'add_quote';
	$quote_id   = 0;
	$quote_info = array();
	$form_title = 'Add Quote';
}

$quote_table = DB_PREFIX . 'quote_table';

$db->run(<<<SQL
CREATE TABLE IF NOT EXISTS `$quote_table` (
	`id` BIGINT(11) AUTO_INCREMENT,
	`quote` TEXT,
	`who` VARCHAR(100),
	`website` VARCHAR(255),
	`website_url` VARCHAR(255),
	`instance_id` VARCHAR(32) NOT NULL, 
	PRIMARY KEY(`id`)
)
SQL
);

?>
<div class="ap_overflow">
	<h3><?=$form_title?></h3>
	<form method="post" action="<?=$body->url('includes/content/quotes/submit.php')?>" onsubmit="Q_Submit(this); return false;" style="height: auto">
		<input type="hidden" name="page_action" value="<?=$action?>" />
		<input type="hidden" id="component_id" name="component_id" value="<?=$component_id?>" />
		<input type="hidden" id="instance_id" name="instance_id" value="<?=$instance_id?>" />
		<input type="hidden" name="quote_id" value="<?=$quote_id?>" />
		<table border="0" cellpadding="0" cellspacing="1" class="admin_list">
		<tr class="a">
			<td>Quote</td>
			<td><textarea name="quote" style="width: 300px; height: 75px"><?=$quote_info['quote']?></textarea></td>
		</tr>
		<tr class="b">
			<td>Name</td>
			<td><input type="text" name="who" value="<?=$quote_info['who']?>" maxlength="100" /></td>
		</tr>
		<tr class="a">
			<td>Website</td>
			<td><input type="text" name="website" value="<?=$quote_info['website']?>" maxlength="255" /></td>
		</tr>
		<tr class="b">
			<td>Website URL</td>
			<td><input type="text" name="website_url" value="<?=$quote_info['website_url']?>" maxlength="255"  /></td>
		</tr>
		</table>
		<input type="submit" value="<?=$form_title?>" />
	</form>

	<?php
	if ($action == 'edit_quote') {
		echo '<p class="click" onclick="Q_Reload();">[Back]</p></div>';
		return;
	}
	?>
	<h3>Manage Quotes</h3>
	<table cellpadding="2" cellspacing="1" border="0" class="admin_list">
	<tr>
		<th>Who</th>
		<th>Quote</th>
		<th>Actions</th>
	</tr>
	<?php
	$quotes  = $db->force_multi_assoc('SELECT * FROM `'.$quote_table.'` WHERE `instance_id`=?', $instance_id);
	$counter = 0;
	if ( (is_array($quotes)) and (sizeof($quotes) > 0) )
	{
		foreach ($quotes as $q)
		{
			$class = ($counter % 2 == 0) ? 'a' : 'b';
			$counter++;	
			$edit = '<img src="'.$body->url('includes/icons/edit.png').'" class="click" onclick="Q_Edit('.$q['id'].')" />';
			$delete = '<img src="'.$body->url('includes/icons/delete.png').'" class="click" onclick="Q_Delete('.$q['id'].')" />';
		?>
	<tr class="<?=$class?>">
		<td><?=$q['who']?></td>
		<td><?=$q['quote']?></td>
		<td><?=$edit.$delete?></td>
	</tr>
	<?php
	
		}
	}
	?>
	</table>
</div>