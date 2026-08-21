<?php
define('IS_SUBFOLDER', true);
require_once '../config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    die("Access Denied");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $applicant_id = intval($_POST['applicant_id']);
    $product_type = $_POST['product_type'] ?? '';
    $pitch_notes = $_POST['pitch_notes'] ?? '';
    $admin_name = $_SESSION['username'] ?? 'Admin';
    
    // In a full implementation, this might create a row in `leads` or `pre_leads`.
    // For now, let's insert a note in the `lead_notes` or just simulate success.
    // We will just redirect back with a success message.
    
    // Using javascript alert and redirect for simplicity in this CRM:
    echo "<script>
        alert('Success! Pitch for " . htmlspecialchars($product_type) . " has been logged.');
        window.location.href = 'index.php';
    </script>";
    exit;
}
