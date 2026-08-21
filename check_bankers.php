<?php
require 'config.php';
$stmt = $db->query("SELECT * FROM bankers");
$bankers = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Count: " . count($bankers) . "\n";
foreach($bankers as $b) {
    echo $b['id'] . " - " . $b['full_name'] . " - " . $b['bank_name'] . "\n";
}
