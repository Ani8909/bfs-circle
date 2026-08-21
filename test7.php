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

$db_file = __DIR__ . '/crm.db';
$db = new PDO("sqlite:" . $db_file);
$_SESSION['session_token'] = $db->query('SELECT session_token FROM users WHERE id=1')->fetchColumn();

require 'config.php';
?>
