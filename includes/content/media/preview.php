<?php
$viewtype = $_GET['viewtype'];

chdir('../../../');
require_once('core.php');
if (USER_ACCESS < 3) { exit(); }

require_once('includes/content/media/functions.php');

$config_file = 'includes/content/media/galleries/'.$viewtype.'/config.php';
include($config_file);

echo '<p>'.$options['text_description'].'</p>';

?>
<table class="pico_editlist" align="left">
<tr class="a">
	<td class="bold">Has Categories</td>
	<td><?=($options['categories']==TRUE)?'Yes':'No'?></td>
</tr>
<tr class="b">
	<td class="bold">Has Title</td>
	<td><?=($options['title']==TRUE)?'Yes':'No'?></td>
</tr>
<tr class="a">
	<td class="bold">Has Description</td>
	<td><?=($options['description']==TRUE)?'Yes':'No'?></td>
</tr>
<tr class="b">
	<td class="bold">Clickable to URL</td>
	<td><?=($options['url']==TRUE)?'Yes':'No'?></td>
</tr>
<tr class="a">
	<td class="bold">Configurable Options</td>
	<td><?=sizeof($settings)?></td>
</tr>
</table>