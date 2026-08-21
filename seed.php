<?php
require_once 'config.php';
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$clients = [
    [
        'name' => 'Rahul Sharma', 'mobile' => '9876543210', 'email' => 'rahul@example.com',
        'city' => 'Delhi', 'emp' => 'Salaried', 'income' => 45000, 'cibil' => 750,
        'loan_type' => 'Personal Loan', 'sub_type' => 'Medical Emergency', 'amt' => 200000,
        'status' => 'Phase 1', 'added_by' => 'admin'
    ],
    [
        'name' => 'Priya Patel', 'mobile' => '9876543211', 'email' => 'priya@example.com',
        'city' => 'Ahmedabad', 'emp' => 'Self-Employed', 'income' => 80000, 'cibil' => 780,
        'loan_type' => 'Home Loan', 'sub_type' => 'New Purchase', 'amt' => 5000000,
        'status' => 'Phase 2', 'added_by' => 'admin'
    ],
    [
        'name' => 'Amit Singh', 'mobile' => '9876543212', 'email' => 'amit@example.com',
        'city' => 'Mumbai', 'emp' => 'Business', 'income' => 150000, 'cibil' => 720,
        'loan_type' => 'Business Loan', 'sub_type' => 'Working Capital', 'amt' => 1000000,
        'status' => 'Phase 3', 'added_by' => 'staff1'
    ],
    [
        'name' => 'Neha Gupta', 'mobile' => '9876543213', 'email' => 'neha@example.com',
        'city' => 'Pune', 'emp' => 'Salaried', 'income' => 60000, 'cibil' => 800,
        'loan_type' => 'Vehicle Loan', 'sub_type' => 'Used Car', 'amt' => 400000,
        'status' => 'Phase 4', 'added_by' => 'staff1'
    ],
    [
        'name' => 'Vikram Verma', 'mobile' => '9876543214', 'email' => 'vikram@example.com',
        'city' => 'Bangalore', 'emp' => 'Self-Employed', 'income' => 120000, 'cibil' => 740,
        'loan_type' => 'Home Loan', 'sub_type' => 'Construction', 'amt' => 3000000,
        'status' => 'Completed', 'added_by' => 'admin'
    ],
    [
        'name' => 'Sneha Desai', 'mobile' => '9876543215', 'email' => 'sneha@example.com',
        'city' => 'Surat', 'emp' => 'Salaried', 'income' => 35000, 'cibil' => 680,
        'loan_type' => 'Personal Loan', 'sub_type' => 'Wedding', 'amt' => 300000,
        'status' => 'Rejected', 'added_by' => 'staff1'
    ],
    [
        'name' => 'Arjun Reddy', 'mobile' => '9876543216', 'email' => 'arjun@example.com',
        'city' => 'Hyderabad', 'emp' => 'Business', 'income' => 200000, 'cibil' => 760,
        'loan_type' => 'Gold Loan', 'sub_type' => 'Business Expansion', 'amt' => 1500000,
        'status' => 'Phase 2', 'added_by' => 'admin'
    ],
    [
        'name' => 'Kavita Joshi', 'mobile' => '9876543217', 'email' => 'kavita@example.com',
        'city' => 'Jaipur', 'emp' => 'Salaried', 'income' => 50000, 'cibil' => 790,
        'loan_type' => 'Vehicle Loan', 'sub_type' => 'New Car', 'amt' => 800000,
        'status' => 'Phase 3', 'added_by' => 'admin'
    ],
    [
        'name' => 'Ravi Kumar', 'mobile' => '9876543218', 'email' => 'ravi@example.com',
        'city' => 'Chennai', 'emp' => 'Salaried', 'income' => 55000, 'cibil' => 710,
        'loan_type' => 'Home Loan', 'sub_type' => 'Balance Transfer', 'amt' => 2500000,
        'status' => 'Phase 1', 'added_by' => 'staff1'
    ],
    [
        'name' => 'Meera Nair', 'mobile' => '9876543219', 'email' => 'meera@example.com',
        'city' => 'Kochi', 'emp' => 'Business', 'income' => 90000, 'cibil' => 730,
        'loan_type' => 'Business Loan', 'sub_type' => 'Machinery Purchase', 'amt' => 1200000,
        'status' => 'Phase 4', 'added_by' => 'admin'
    ]
];

$docs = [
    ['cat' => 'Basic KYC', 'name' => 'Aadhaar Card Front', 'type' => 'image/jpeg', 'status' => 'Verified', 'notes' => 'Clear image'],
    ['cat' => 'Basic KYC', 'name' => 'PAN Card', 'type' => 'image/jpeg', 'status' => 'Verified', 'notes' => ''],
    ['cat' => 'Income Proof', 'name' => 'Last 6 Months Bank Statement', 'type' => 'application/pdf', 'status' => 'Pending', 'notes' => '']
];

try {
    foreach ($clients as $i => $c) {
        $loan_id = 'L' . date('Ym') . rand(1000, 9999);
        $stmt = $db->prepare("INSERT INTO applicants 
            (loan_id, customer_name, mobile, email, city, employment_type, monthly_income, cibil_score, loan_type, loan_sub_type, loan_amount_requested, overall_status, added_by, address, state, pincode, pan_number, aadhar_number) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Sample Address', 'State', '123456', 'ABCDE1234F', '123456789012')");
        $stmt->execute([
            $loan_id, $c['name'], $c['mobile'], $c['email'], $c['city'], $c['emp'], $c['income'], $c['cibil'], $c['loan_type'], $c['sub_type'], $c['amt'], $c['status'], $c['added_by']
        ]);
        
        $app_id = $db->lastInsertId();
        
        if ($c['status'] !== 'Phase 1') {
            foreach ($docs as $d) {
                $path = 'uploads/applicants/dummy_' . rand(1000,9999) . '.jpg';
                $stmt_doc = $db->prepare("INSERT INTO applicant_documents (applicant_id, document_category, document_name, file_path, file_type, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt_doc->execute([$app_id, $d['cat'], $d['name'], $path, $d['type'], $d['status'], $d['notes']]);
            }
            
            if ($c['status'] == 'Rejected' || $c['status'] == 'Phase 2') {
                $stmt_doc = $db->prepare("INSERT INTO applicant_documents (applicant_id, document_category, document_name, file_path, file_type, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt_doc->execute([$app_id, 'Other', 'Address Proof', 'uploads/applicants/dummy_err.jpg', 'image/jpeg', 'Rejected', 'Image is too blurry']);
            }
        }
    }
    echo "Success\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
