<?php
require 'config.php';
$stmt = $db->query("PRAGMA table_info(bankers)");
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach($cols as $col) {
    echo $col['name'] . " - " . $col['type'] . "\n";
}
