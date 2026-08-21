<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
$db_file = __DIR__ . '/crm.db';
$db = new PDO("sqlite:" . $db_file);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
    $db->exec("ALTER TABLE applicant_documents ADD COLUMN file_type TEXT");
    echo "1\n";
} catch(Exception $e) { echo $e->getMessage() . "\n"; }
try {
    $db->exec("ALTER TABLE applicant_documents ADD COLUMN status TEXT DEFAULT 'Pending'");
    echo "2\n";
} catch(Exception $e) { echo $e->getMessage() . "\n"; }
try {
    $db->exec("ALTER TABLE applicant_documents ADD COLUMN notes TEXT");
    echo "3\n";
} catch(Exception $e) { echo $e->getMessage() . "\n"; }
echo "Done\n";
?>
