<?php

$mode = (isset($_GET['mode'])) ? $_GET['mode'] : 'all';
$function_num = (isset($_GET['CKEditorFuncNum'])) ? $_GET['CKEditorFuncNum'] : null;

include('browse_manager.php');
return;
?>