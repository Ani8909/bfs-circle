<?php
session_start();
$_SESSION['user_id'] = 1; // Assuming 1 is Admin
$_SESSION['role'] = 'Admin';

require 'config.php'; // get $db
$stmt = $db->prepare("SELECT session_token FROM users WHERE id=1");
$stmt->execute();
$_SESSION['session_token'] = $stmt->fetchColumn();

// Mocking the request
$_SERVER['REQUEST_METHOD'] = 'POST';
$_GET['api'] = 'add_referral';
$_POST = [
    'referrer_type' => 'Individual Agent',
    'full_name' => 'Ramesh Kumar',
    'dob' => '1990-05-15',
    'mobile' => '9876543210',
    'email' => 'ramesh@example.com',
    'city_state' => 'Delhi',
    'account_name' => 'Ramesh Kumar',
    'bank_name' => 'HDFC Bank',
    'account_number' => '50100123456789',
    'ifsc_code' => 'HDFC0001234',
    'commission_rate' => '1.5%',
    'payout_frequency' => 'Monthly',
    'pan_number' => 'ABCDE1234F',
    'aadhar_number' => '123456789012',
    'status' => 'Active'
];

ob_start();
require 'api.php';
$output = ob_get_clean();

echo "Pipeline Output:\n";
echo $output;
?>
