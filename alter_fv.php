<?php
$db = new PDO('sqlite:crm.db');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
try {
    $db->exec("ALTER TABLE field_visits ADD COLUMN check_in_time DATETIME");
    $db->exec("ALTER TABLE field_visits ADD COLUMN check_out_time DATETIME");
    echo "Columns added!";
} catch(Exception $e) {
    echo "Error or already exists: " . $e->getMessage();
}
?>
