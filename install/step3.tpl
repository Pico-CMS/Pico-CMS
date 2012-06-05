<h2>Step 3 - Additional Settings</h2>

<form method="post" action="<?=$_SERVER['REQUEST_URI']?>" onsubmit="return confirm('Pico will now install. These settings are final. Are you sure you want to continue?');">
<input type="hidden" name="page_action" value="additional_settings" />
<table border="0" cellpadding="3" cellspacing="0">
<tr>
	<td>Admin User<br />(the master account for this site)</td>
	<td><input type="text" name="settings[username]" value="admin" /></td>
</tr>
<tr>
	<td>Admin Password</td>
	<td><input type="password" name="settings[password]" /></td>
</tr>
<tr>
	<td>Admin E-mail Address</td>
	<td><input type="text" name="settings[email]" /></td>
</tr>
<tr>
	<td>Site Name</td>
	<td><input type="text" name="settings[site_name]" /></td>
</tr>
<tr>
	<td>Site E-mail Address<br />(the address that external users will receive mail from)</td>
	<td><input type="text" name="settings[site_email]" /></td>
</tr>
<tr>
	<td>Site E-mail Name<br />(the FROM: that external users will receive mail from)</td>
	<td><input type="text" name="settings[site_from]" /></td>
</tr>
</table>
<input type="submit" value="Next" />
</form>