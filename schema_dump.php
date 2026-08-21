<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
try {
    $db_file = __DIR__ . '/crm.db';
    $db = new PDO("sqlite:" . $db_file);
    $query = $db->query("SELECT sql FROM sqlite_master WHERE type='table';");
    $tables = $query->fetchAll(PDO::FETCH_ASSOC);
    foreach($tables as $t) {
        echo $t['sql'] . "\n\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
