<?php
chdir('../');
require_once('core.php');
if (USER_ACCESS < 3) { exit(); }
?>
<h3>Update Pico</h3>
<p>To check for updates for Pico, please click the button below</p>
<button onclick="Pico_CheckForUpdates(this)" style="margin-bottom: 10px">Check For Updates</button>
<div id="update_status"></div>
