<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once 'config.php';

try {
    echo "Executing SQL...\n";
    $sql = file_get_contents('pincodes.sql');
    if (!$sql) throw new Exception("Failed to read pincodes.sql");
    $db->exec($sql);
    echo "SQL executed!\n";
    $c = $db->query("SELECT count(*) FROM pincodes")->fetchColumn();
    echo "Total in DB: $c\n";
    
    $r = $db->query("SELECT * FROM pincodes WHERE pincode='282001'")->fetch();
    print_r($r);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
