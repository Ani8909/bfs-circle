<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Partner') {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = $_POST['amount'] ?? 0;
    
    if ($amount > 0) {
        $stmt = $db->prepare("INSERT INTO partner_payouts (user_id, amount) VALUES (?, ?)");
        $stmt->execute([$_SESSION['user_id'], $amount]);
        
        // In a real app we'd set a session variable to show a success toast on index.php
        // For simplicity, we can just echo some JS and redirect.
        echo "<script>
            alert('Payout request for ₹" . number_format($amount) . " submitted successfully! Admin will process it shortly.');
            window.location.href = 'index.php';
        </script>";
        exit;
    }
}
header('Location: index.php');
exit;
?>
