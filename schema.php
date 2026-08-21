<?php
$db = new PDO('sqlite:' . __DIR__ . '/crm.db');
$stmt = $db->query("PRAGMA table_info(employees)");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
