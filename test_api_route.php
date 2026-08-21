<?php
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'Staff';
$_SESSION['username'] = 'fieldstaff1@aura.com';
$_GET['api'] = 'get_field_visits';
$_GET['page'] = '1';
include 'index.php';
