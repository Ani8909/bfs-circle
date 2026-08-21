<?php
$db = new PDO("sqlite:" . __DIR__ . "/crm.db");
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$stmt = $db->query("SELECT name, sql FROM sqlite_master WHERE type='table';");
foreach ($stmt->fetchAll() as $row) {
    echo $row['name'] . "\n" . $row['sql'] . "\n\n";
}
?>
