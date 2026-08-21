<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['SCRIPT_NAME'] = '/leads.php';
$_GET['api'] = 'get_leads';

session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'Admin';
$_SESSION['username'] = 'admin';

require 'config.php';
// config.php will require api.php and exit, so we will see the output of api.php!
?>
