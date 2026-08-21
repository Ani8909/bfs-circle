<?php
require 'config.php';
$stmt = $db->query("PRAGMA table_info(bankers)");
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($cols);
