<?php
$db = new PDO('sqlite:crm.db');
$stmt = $db->query('SELECT username, last_ping, current_lat, current_lon FROM users WHERE last_ping IS NOT NULL');
print_r($stmt->fetchAll());
?>
