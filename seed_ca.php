<?php
try {
    $db = new PDO('sqlite:crm.db');
    
    // CA leads
    $db->exec("INSERT INTO leads (lead_name, mobile, loan_amount, requirement, stage, added_by, lead_source, created_at) 
               VALUES ('Rahul Industries', '9876543210', 5000000, 'Business Loan', 'Disbursed', 'CA-DEMO', 'Referral', datetime('now', '-5 days'))");
               
    $db->exec("INSERT INTO leads (lead_name, mobile, loan_amount, requirement, stage, added_by, lead_source, created_at) 
               VALUES ('Vikas Gupta', '8765432109', 15000000, 'Home Loan', 'Login', 'CA-DEMO', 'Referral', datetime('now', '-2 days'))");
               
    $db->exec("INSERT INTO leads (lead_name, mobile, loan_amount, requirement, stage, added_by, lead_source, created_at) 
               VALUES ('Simran Kaur', '7654321098', 2500000, 'Personal Loan', 'New Lead', 'CA-DEMO', 'Referral', datetime('now'))");
               
    echo "CA Leads inserted.";
} catch(Exception $e) { echo $e->getMessage(); }
