<?php
$db = new PDO('sqlite:crm.db');
$stmt = $db->query("SELECT sql FROM sqlite_master WHERE type='table' AND name='applicants'");
echo $stmt->fetchColumn();
?>
