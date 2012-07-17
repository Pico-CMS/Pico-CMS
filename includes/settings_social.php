<?php
if ( (!defined('USER_ACCESS')) or (USER_ACCESS < 3) ) { exit(); }
?>
<div class="ap_overflow">
	<h3>Social Media Integration</h3>
	<form method="post" action="<?=$body->url('includes/ap_actions.php')?>" onsubmit="Pico_SaveSettings(this); return false">
	<input type="hidden" name="ap_action" value="settings" />
	
	<table border="0" cellpadding="0" cellspacing="2" class="admin_list">
	<tr class="a">
		<td>Twitter Username</td>
		<td><input type="text" name="settings[twitter_name]" value="<?=Pico_Setting('twitter_name')?>" /></td>
	</tr>
	<tr class="b">
		<td>Disqus Username</td>
		<td><input type="text" name="settings[twitter_name]" value="<?=Pico_Setting('disqus_name')?>" /></td>
	</tr>
	<tr class="a">
		<td>Add This/Share This code</td>
		<td><textarea class="ap_textarea_lg" name="settings[share_this]"><?=htmlspecialchars(Pico_Setting('share_this'))?></textarea></td>
	</tr>
	</table>
	
	<input type="submit" name="submit_btn" value="Save" />
	</form>
	
	<?=$back?>
</div>