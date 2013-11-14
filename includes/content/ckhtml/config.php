<?php
$options = array();
$options['title'] = 'HTML Rich Text'; // what you call your content
$options['description'] = 'MS-Word-like formatted text editor'; // a brief description of what your content does, any restrictions, etc
$options['content_file'] = 'view.php'; // file that will be called when executing/displaying REQUIRED
$options['edit_file'] = 'edit.php'; // file that will be called when editing the content
$options['static_js'] = ''; // file that will always be included into javascript when the component is called for
$options['static_css'] = ''; // same as javascript, only for CSS
//$options['admin_js'] = 'fckeditor/fckeditor.js'; // same as static, but if logged in as moderator or higher
$options['admin_js'] = 'admin.js'; // same as static, but if logged in as moderator or higher
$options['admin_css'] = 'admin.css'; // same as static, but if logged in as moderator or higher
$options['delete_file'] = ''; // file that will be called in addition to removing the component from the content-links table
$options['install_file'] = ''; // file that will be called after initally installing the component (good time to gather required information for your component, or create an SQL table if needed)
//$options['on_edit_load'] = 'CKT_LoadFCK(\'ck_text\')'; // javascript code that will happen after load is done
$options['on_ap_close'] = 'CKT_Close()'; 
$options['on_edit_load'] = 'CKT_Load()';// javascript code that will happen after load is done
$options['foo'] = 'bar'; // hack fix for update system
$options['author_editable'] = TRUE; 
$options['view_setting_can_change'] = TRUE; 

$options['edit_options'] = array(
	array(
		'container' => 'co_html_settings',
		'link_text' => 'HTML Settings',
		'inc_file'  => 'ckhtml_settings.php',
		'access'    => 4
	),
); // for custom options while editing

?>