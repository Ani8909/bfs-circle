<?php
$_SERVER['PHP_SELF'] = 'api.php';
require 'config.php';
$stmt = $db->query("SELECT COUNT(*) FROM leads");
echo "Leads count: " . $stmt->fetchColumn() . "\n";
?>
