<?php
session_start();
$_SESSION['user_id'] = 1;
require 'config.php';
echo "Schema updated.";
