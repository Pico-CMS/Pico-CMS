<?php

$request = basename($_SERVER['REQUEST_URI']);
if ($request == 'config.php') { echo 'You cannot access this file directly'; exit(); }

$config = array();
// database settings

$config['dbhost']= "[[HOST]]";
$config['dbname'] = "[[DB_NAME]]";
$config['dbuser'] = "[[DB_USER]]";
$config['dbpass'] = "[[DB_PASS]]";
$config['dbprefix'] = "[[DB_PREFIX]]";
$config['admin_email'] = '[[ADMIN_EMAIL]]';
$config['admin_from']  = '[[ADMIN_FROM]]';

// domain settings

$config['domain'] = '[[DOMAIN]]';
$config['domain_path'] = '[[DOMAIN_PATH]]';
$config['install_path'] = '[[INSTALL_PATH]]';
define('SITE_TITLE', '[[SITE_TITLE]]');
?>