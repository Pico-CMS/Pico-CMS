<?php

echo <<<HTML
<tr>
	<td class="title">$name_box_text</td>
</tr>
<tr>
	<td class="field"><input type="text" class="text" name="first_name" /></td>
</tr>
<tr>
	<td class="title">$email_box_text</td>
</tr>
<tr>
	<td class="field"><input type="text" class="text" name="email" /></td>
</tr>
<tr>
	<td class="button">$signup_button</td>
</tr>
</table>
HTML;

?>