<?php
$db = new PDO('sqlite:crm.db');
$stmt = $db->query('SELECT * FROM staff_location_logs ORDER BY id DESC LIMIT 5');
print_r($stmt->fetchAll());
?>
