<?php
$db = new PDO('sqlite:crm.db');
$tables = ['leads', 'users', 'referrals'];
foreach ($tables as $t) {
    $stmt = $db->query("SELECT sql FROM sqlite_master WHERE type='table' AND name='$t'");
    if ($stmt) {
        echo $stmt->fetchColumn() . "\n\n";
    }
}
?>
