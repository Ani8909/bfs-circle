<?php
$db = new PDO('sqlite:crm.db');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
try {
    $db->exec("ALTER TABLE field_visits ADD COLUMN audio_path TEXT DEFAULT NULL");
    echo "Column audio_path added successfully.";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'duplicate column name') !== false) {
        echo "Column already exists.";
    } else {
        echo "Error: " . $e->getMessage();
    }
}
?>
