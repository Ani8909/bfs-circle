<?php
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'Admin';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_GET['api'] = 'get_staff_360';
$_GET['username'] = 'anujj';
require 'config.php';
