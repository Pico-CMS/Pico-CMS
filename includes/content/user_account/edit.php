<?php
$cs = $component_settings;
?>

<div class="ap_overflow">
	<form method="post" action="<?=$body->url('includes/content/user_login/submit.php')?>" onsubmit="UL_UpdateSettings(this); return false">
		<input type="hidden" name="page_action" value="update" />
		<input type="hidden" name="component_id" value="<?=$component_id?>" />
		<table border="0" cellpadding="2" cellspacing="1" class="admin_list">
		<tr class="a">
			<td>Welcome Text</td>
			<td><textarea class="ap_textarea" name="settings[welcome-back]"><?=$cs['welcome-back']?></textarea>
			<p class="small">Variables: FIRST_NAME, LAST_NAME, EMAIL</p></td>
		</tr>
		<tr class="b">
			<td>Settings Only</td>
			<td>
				<input type="checkbox" name="settings[settings-only]" value="1" <?=(($cs['settings-only'] == 1) ? 'checked="checked"' : '')?>/>
				<p class="small">If you only want to display the settings form and not transaction history or other options</p>
			</td>
		</tr>


		</table>
		<input type="submit" class="co_button co_button1" value="Save" />
	</form>
</div>