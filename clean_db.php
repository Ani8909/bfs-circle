<?php
require 'config.php';

// Quick security check
if (($_SESSION['role'] ?? '') !== 'Admin') {
    die("Unauthorized! Only Admin can clean the database.");
}

$tables_to_empty = [
    'email_templates', 'presentations', 'leads', 'pre_leads', 'clients', 
    'communications', 'quotations', 'activities', 'reminders', 'call_logs', 
    'bankers', 'employees', 'referrals', 'applicants', 'applicant_documents', 
    'applicant_disbursements', 'applicant_bank_assignments', 'staff_attendance', 
    'co_applicants', 'staff_location_logs', 'payout_distributions', 'field_visits', 
    'field_visit_followups'
];

try {
    foreach ($tables_to_empty as $table) {
        // We use try catch just in case a table doesn't exist
        try {
            $db->exec("DELETE FROM $table");
            $db->exec("DELETE FROM sqlite_sequence WHERE name='$table'");
        } catch (Exception $e) {}
    }

    // Delete all users except Admin (ID 1)
    $db->exec("DELETE FROM users WHERE id > 1");
    // Set auto increment back to 1
    try { $db->exec("UPDATE sqlite_sequence SET seq = 1 WHERE name='users'"); } catch(Exception $e) {}

    echo "<div style='font-family: Arial; text-align: center; margin-top: 50px;'>";
    echo "<h2 style='color: green;'>Database Cleaned Successfully! ??</h2>";
    echo "<p>All test data has been removed. The Admin account, Company Profile, and IFSC Master Data have been preserved.</p>";
    echo "<br><a href='dashboard.php' style='padding: 10px 20px; background: #0f172a; color: white; text-decoration: none; border-radius: 5px;'>Back to Dashboard</a>";
    echo "</div>";
    
    // Self-destruct for security
    @unlink(__FILE__);
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
