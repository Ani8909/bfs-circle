<?php
$db = new PDO('sqlite:crm.db');
$query = $db->query("SELECT sql FROM sqlite_master WHERE type='table' AND name='field_visits';");
print_r($query->fetch(PDO::FETCH_ASSOC));
?>
