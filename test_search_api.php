<?php
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'Admin';
$_SESSION['name'] = 'Admin User';
$_GET['api'] = 'search_applicants';
require 'config.php';
// config.php will route to api.php directly
?>
