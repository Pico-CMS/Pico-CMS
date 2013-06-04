<h2>Step 2 - FTP Information</h2>

<form method="post" action="<?=$_SERVER['REQUEST_URI']?>">
<input type="hidden" name="page_action" value="verify_ftp" />
<table border="0" cellpadding="3" cellspacing="0">
<tr>
	<td>FTP Username</td>
	<td><input type="text" name="ftp[username]" /></td>
</tr>
<tr>
	<td>FTP Password</td>
	<td><input type="text" name="ftp[password]" /></td>
</tr>
<tr>
	<td>FTP Host</td>
	<td><input type="text" name="ftp[host]" value="<?=$_SERVER['SERVER_NAME']?>" /></td>
</tr>
<tr>
	<td>FTP Port</td>
	<td><input type="text" name="ftp[port]" value="21" /></td>
</tr>
<tr>
	<td>FTP Path (directory to where pico is installed)</td>
	<td><input type="text" name="ftp[path]" /></td>
</tr>
<tr>
	<td>Manual FTP</td>
	<td><input type="checkbox" name="ftp[ok]" value="skip" /></td>
</tr>
<tr>
	<td>Secure FTP</td>
	<td><input type="checkbox" name="ftp[secure]" value="1" /></td>
</tr>
</table>
<input type="submit" value="Next" />
</form>