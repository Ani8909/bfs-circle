<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require 'config.php';
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'Admin';
$_SESSION['username'] = 'admin';
$_SESSION['session_token'] = $db->query('SELECT session_token FROM users WHERE id=1')->fetchColumn();
$_GET['api'] = 'get_leads';
require 'api.php';
?>
