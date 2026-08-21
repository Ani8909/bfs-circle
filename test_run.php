<?php
try {
    require 'config.php';
    $stmt = $db->query('SELECT COUNT(*) FROM bankers');
    echo 'Count: ' . $stmt->fetchColumn() . "\n";
    $stmt = $db->query('SELECT * FROM bankers');
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($rows);
} catch(Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
