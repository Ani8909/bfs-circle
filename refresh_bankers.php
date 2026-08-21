<?php
session_start();
$_SESSION['user_id'] = 1; // Bypass auth redirect in config.php
require 'config.php';

// Drop table to fully recreate it clean
$db->exec("DROP TABLE IF EXISTS bankers");

// Require config again to trigger the CREATE TABLE IF NOT EXISTS block
// Actually config is already required, so the CREATE TABLE won't re-run.
// Let's manually run the create table here to be absolutely sure.
$db->exec("CREATE TABLE IF NOT EXISTS bankers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    full_name TEXT NOT NULL,
    bank_name TEXT NOT NULL,
    designation TEXT,
    state TEXT,
    city TEXT,
    address TEXT,
    ifsc_code TEXT,
    contact_number TEXT NOT NULL,
    official_email TEXT,
    loan_category TEXT,
    min_loan_limit REAL DEFAULT 0,
    max_loan_limit REAL DEFAULT 0,
    employee_id TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

$db->exec("DELETE FROM sqlite_sequence WHERE name='bankers'");

$banks = [
    [
        'full_name' => 'Vikram Singh',
        'bank_name' => 'HDFC Bank',
        'designation' => 'Senior Branch Manager',
        'state' => 'Maharashtra',
        'city' => 'Mumbai',
        'address' => 'Bandra West Branch, SV Road, Mumbai',
        'ifsc_code' => 'HDFC0001234',
        'contact_number' => '9876543210',
        'official_email' => 'vikram.singh@hdfcbank.com',
        'loan_category' => 'Home Loan, Personal Loan, Business Loan',
        'min_loan_limit' => 500000,
        'max_loan_limit' => 50000000
    ],
    [
        'full_name' => 'Neha Sharma',
        'bank_name' => 'State Bank of India',
        'designation' => 'Loan Officer',
        'state' => 'Delhi',
        'city' => 'New Delhi',
        'address' => 'Connaught Place Main Branch, New Delhi',
        'ifsc_code' => 'SBIN0000691',
        'contact_number' => '9123456780',
        'official_email' => 'neha.sharma@sbi.co.in',
        'loan_category' => 'Home Loan, Education Loan',
        'min_loan_limit' => 100000,
        'max_loan_limit' => 20000000
    ],
    [
        'full_name' => 'Arjun Patel',
        'bank_name' => 'ICICI Bank',
        'designation' => 'Regional Head - Mortgages',
        'state' => 'Gujarat',
        'city' => 'Ahmedabad',
        'address' => 'SG Highway Branch, Bodakdev, Ahmedabad',
        'ifsc_code' => 'ICIC0000024',
        'contact_number' => '9898989898',
        'official_email' => 'arjun.patel@icicibank.com',
        'loan_category' => 'Mortgage Loan, Business Loan',
        'min_loan_limit' => 1500000,
        'max_loan_limit' => 100000000
    ],
    [
        'full_name' => 'Anita Desai',
        'bank_name' => 'Axis Bank',
        'designation' => 'Relationship Manager',
        'state' => 'Karnataka',
        'city' => 'Bengaluru',
        'address' => 'Indiranagar 100ft Road Branch, Bengaluru',
        'ifsc_code' => 'UTIB0000114',
        'contact_number' => '9787675747',
        'official_email' => 'anita.desai@axisbank.com',
        'loan_category' => 'Personal Loan, Vehicle Loan',
        'min_loan_limit' => 50000,
        'max_loan_limit' => 5000000
    ],
    [
        'full_name' => 'Ravi Kumar',
        'bank_name' => 'Kotak Mahindra Bank',
        'designation' => 'Credit Analyst',
        'state' => 'Telangana',
        'city' => 'Hyderabad',
        'address' => 'Banjara Hills Branch, Road No 12, Hyderabad',
        'ifsc_code' => 'KKBK0007455',
        'contact_number' => '9654321098',
        'official_email' => 'ravi.k@kotak.com',
        'loan_category' => 'Business Loan',
        'min_loan_limit' => 2000000,
        'max_loan_limit' => 150000000
    ]
];

$stmt = $db->prepare("INSERT INTO bankers (full_name, bank_name, designation, state, city, address, ifsc_code, contact_number, official_email, loan_category, min_loan_limit, max_loan_limit, employee_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$time = time();
foreach ($banks as $index => $b) {
    $stmt->execute([
        $b['full_name'],
        $b['bank_name'],
        $b['designation'],
        $b['state'],
        $b['city'],
        $b['address'],
        $b['ifsc_code'],
        $b['contact_number'],
        $b['official_email'],
        $b['loan_category'],
        $b['min_loan_limit'],
        $b['max_loan_limit'],
        'EXT' . ($time + $index)
    ]);
}

echo "Database emptied and 5 fresh banks added successfully.";
