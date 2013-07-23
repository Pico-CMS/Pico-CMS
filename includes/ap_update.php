<?php
chdir('../');
require_once('core.php');
if (USER_ACCESS < 3) { exit(); }
?>
<div id="update_status"><p>Checking for updates...</p></div>