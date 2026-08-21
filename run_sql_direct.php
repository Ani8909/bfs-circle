<?php
try {
    echo "Connecting to DB...\n";
    $db = new PDO("sqlite:crm.db");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Reading SQL...\n";
    $sql = file_get_contents('pincodes.sql');
    if (!$sql) throw new Exception("No sql content");
    
    echo "Executing SQL...\n";
    $db->exec($sql);
    
    echo "Done!\n";
    
    $c = $db->query("SELECT count(*) FROM pincodes")->fetchColumn();
    echo "Total: $c\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
