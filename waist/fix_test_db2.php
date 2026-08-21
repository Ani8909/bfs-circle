<?php
require_once 'config.php';
$now = date('Y-m-d H:i:s');
$db->exec("UPDATE staff_attendance SET punch_in = '$now' WHERE att_date='2026-08-20'");
echo "Updated to $now";
