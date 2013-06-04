<h1>Welcome to Pico</h1>
Thank you for choosing pico!

<h2>Step 1 - Database Information</h2>

<form method="post" action="<?=$_SERVER['REQUEST_URI']?>">
<input type="hidden" name="page_action" value="verify_db" />
<table border="0" cellpadding="3" cellspacing="0">
<tr>
	<td>DB Host</td>
	<td><input type="text" name="db[host]" value="localhost" /></td>
</tr>
<tr>
	<td>DB Name</td>
	<td><input type="text" name="db[name]" /></td>
</tr>
<tr>
	<td>DB Username</td>
	<td><input type="text" name="db[username]" /></td>
</tr>
<tr>
	<td>DB Password</td>
	<td><input type="text" name="db[password]" /></td>
</tr>
<tr>
	<td>DB Prefix (prefix all Pico tables will start with, ex: "mysite_")</td>
	<td><input type="text" name="db[prefix]" value="" /></td>
</tr>
</table>
<input type="submit" value="Next" />
</form>