<?php
require 'config.php';
try {
    $db->exec('ALTER TABLE company_profile ADD COLUMN global_tds REAL DEFAULT 5.0');
    $db->exec('ALTER TABLE company_profile ADD COLUMN lead_auto_assign TEXT DEFAULT "Round Robin"');
    $db->exec('ALTER TABLE company_profile ADD COLUMN loan_products TEXT');
    $db->exec('ALTER TABLE company_profile ADD COLUMN lead_sources TEXT');
    echo "Columns added.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
