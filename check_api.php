<?php
$_GET['api'] = 'get_branches';
$_GET['bank'] = 'Bank of Baroda';
$_GET['city'] = 'Agra';
// Mock session so config.php doesn't kick us out
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'Admin';
require 'api.php';
