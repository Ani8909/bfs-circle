<?php
require 'config.php';
try {
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $count = $db->query("SELECT COUNT(*) FROM users WHERE role = 'Staff' AND last_active >= datetime('now', 'localtime', '-2 minutes')")->fetchColumn();
    echo "Count: $count\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
