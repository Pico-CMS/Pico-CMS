<?php
if ( (!defined('USER_ACCESS')) or (USER_ACCESS < 3) ) { exit(); }
?>
<div class="ap_overflow">
	<h3>General Settings</h3>
	<form method="post" action="<?=$body->url('includes/ap_actions.php')?>" onsubmit="Pico_SaveSettings(this); return false">
	<input type="hidden" name="ap_action" value="settings" />
	
	<table border="0" cellpadding="0" cellspacing="2" class="admin_list">
	<tr class="a">
		<td>HTML &lt;HEAD&gt; Additions</td>
		<td>
			Use this section to add additional links to javascript files or stylesheets that are used externally<br />
			<textarea class="ap_textarea_lg" name="settings[html_head]"><?=htmlspecialchars(Pico_Setting('html_head'))?></textarea>
		</td>
	</tr>
	<tr class="b">
		<td>&lt;/BODY&gt; Additions</td>
		<td>
			Use this section to add additional HTML code that is needed before the HTML BODY tag is closed (conversion codes, etc)<br />
			<textarea class="ap_textarea_lg" name="settings[html_body]"><?=htmlspecialchars(Pico_Setting('html_body'))?></textarea>
		</td>
	</tr>
	<tr class="a">
		<td>Timezone</td>
		<td><input type="text" name="settings[default_timezome]" value="<?=Pico_Setting('default_timezome')?>" /><br />
			Default is: America/New_York. <a href="http://us2.php.net/manual/en/timezones" target="_blank">More</a>
		</td>
	</tr>
	</table>
	
	<input type="submit" name="submit_btn" value="Save" />
	</form>
	
	<?=$back?>
</div>