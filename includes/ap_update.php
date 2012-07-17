<?php
chdir('../');
require_once('core.php');
if (USER_ACCESS < 3) { exit(); }
?>
<h3>Update Pico</h3>
<p>To check for updates for Pico, please click the button below</p>
<button onclick="Pico_CheckForUpdates()">Check For Updates</button>
<p>Status:</p>
<div id="update_status"></div>
