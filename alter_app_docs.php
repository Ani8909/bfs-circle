<?php
require_once 'config.php';

try {
    // Make sure the table exists first by calling config.php which has CREATE TABLE IF NOT EXISTS
    
    // Add columns if they don't exist
    $db->exec("ALTER TABLE applicant_documents ADD COLUMN file_type TEXT");
    $db->exec("ALTER TABLE applicant_documents ADD COLUMN status TEXT DEFAULT 'Pending'");
    $db->exec("ALTER TABLE applicant_documents ADD COLUMN notes TEXT");
    
    echo "Columns added successfully.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
