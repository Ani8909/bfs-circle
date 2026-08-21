<?php
require_once 'config.php';
$query = $db->query("SELECT name FROM sqlite_master WHERE type='table';");
print_r($query->fetchAll(PDO::FETCH_ASSOC));
?>
