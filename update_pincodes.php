<?php
require 'config.php';

if (($_SESSION['role'] ?? '') !== 'Admin') {
    die("Unauthorized!");
}

$sql_file = __DIR__ . '/PINCODE.SQL';
if (!file_exists($sql_file)) {
    die("PINCODE.SQL not found on server.");
}

$sql = file_get_contents($sql_file);

try {
    $db->exec($sql);
    echo "<h2 style='color:green;'>Pincodes updated successfully in database!</h2>";
    echo "<a href='dashboard.php'>Back to Dashboard</a>";
    
    // Auto delete after running
    @unlink($sql_file);
    @unlink(__FILE__);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
