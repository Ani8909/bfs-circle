<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
$db_file = __DIR__ . '/crm.db';
$db = new PDO("sqlite:" . $db_file);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
    $db->exec("ALTER TABLE referrals ADD COLUMN extra_details TEXT");
    echo "extra_details added to referrals\n";
} catch(Exception $e) { echo $e->getMessage() . "\n"; }

echo "Done\n";
?>
