<?php
require 'config.php';

$stmt = $db->prepare("INSERT INTO bankers (full_name, bank_name, designation, state, city, address, ifsc_code, contact_number, official_email, loan_category, min_loan_limit, max_loan_limit, employee_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->execute([
    'Vikram Singh',
    'HDFC Bank',
    'Senior Branch Manager',
    'Maharashtra',
    'Mumbai',
    'Bandra West Branch, SV Road',
    'HDFC0001234',
    '9876543210',
    'vikram.singh@hdfcbank.com',
    'Home Loan, Personal Loan, Business Loan',
    500000,
    50000000,
    'EXT' . time()
]);
echo "Test Bank Contact inserted.";
