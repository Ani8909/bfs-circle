<?php
$_GET['api'] = 'login';
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['username'] = 'staff1';
$_POST['password'] = 'staff123';
require 'api.php';
?>
