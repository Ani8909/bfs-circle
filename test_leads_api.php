<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'config.php';
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'fieldstaff1@aura.com';
$_GET['api'] = 'get_staff_recent_leads';
require 'api.php';
