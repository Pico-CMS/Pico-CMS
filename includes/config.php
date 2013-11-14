<?php

$request = basename($_SERVER['REQUEST_URI']);
if ($request == 'config.php') { echo 'You cannot access this file directly'; exit(); }

$config = array();
// database settings

$config['dbhost']= "localhost";
$config['dbname'] = "sisaresell_kbake";
$config['dbuser'] = "sisaresell_kbake";
$config['dbpass'] = "icTl9kSq";
$config['dbprefix'] = "kb_";
$config['admin_email'] = 'info@kosherbaker.com';
$config['admin_from']  = 'Kosher Baker';

// domain settings

$config['domain'] = 'kosherbaker.sisarina.net';
$config['domain_path'] = '/';
$config['install_path'] = '/home/sisaresell/domains/sisarina.net/public_html/kosherbaker/';
define('SITE_TITLE', 'Kosher Baker');
?>