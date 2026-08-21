<?php
session_start();
if (!isset($_SESSION['user_id'])) exit;

$db = new PDO("sqlite:" . __DIR__ . "/../../crm.db");
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$_SESSION['user_id']]);
}
?>
