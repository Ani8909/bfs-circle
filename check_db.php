<?php
$db = new PDO('sqlite:crm.db');
$stmt = $db->query("SELECT * FROM staff_attendance WHERE att_date='2026-08-20'");
$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($res);
