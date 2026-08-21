<?php
session_start();
$_SESSION['user_id'] = 1;
require 'config.php';
try {
    $stmt = $db->query("SELECT id, customer_name FROM applicants");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
