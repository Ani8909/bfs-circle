<?php
require_once 'config.php';

try {
    // 1. Delete all existing leads and their notes
    $db->exec("DELETE FROM leads");
    $db->exec("DELETE FROM lead_notes");
    
    // 2. Add some dummy leads
    $stmt = $db->prepare("INSERT INTO leads (lead_name, company_name, mobile, email, lead_source, stage, priority, assigned_to, added_by, created_at, reminder_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $today = date('Y-m-d H:i:s');
    $yesterday = date('Y-m-d H:i:s', strtotime('-1 day'));
    $tomorrow = date('Y-m-d H:i:s', strtotime('+1 day'));
    
    // Lead 1: Direct Lead, Hot, In Progress, Added Today
    $stmt->execute([
        'Amit Sharma', 'Sharma Enterprises', '9876543210', 'amit@sharma.com', 'Website', 'New Lead', 'Hot', 'admin', 'direct', $today, $tomorrow
    ]);
    $lead1_id = $db->lastInsertId();
    $db->exec("INSERT INTO lead_notes (lead_id, note_text, added_by_user) VALUES ($lead1_id, 'Called Amit. Very interested in a 50L business loan.', 'admin')");

    // Lead 2: Builder Lead, Warm, In Progress, Added Yesterday
    $stmt->execute([
        'Rahul Verma', 'Verma Associates', '9123456789', 'rahul@verma.com', 'Builder', 'Interested', 'Warm', 'admin', 'BLD-1', $yesterday, null
    ]);
    $lead2_id = $db->lastInsertId();
    $db->exec("INSERT INTO lead_notes (lead_id, note_text, added_by_user) VALUES ($lead2_id, 'Asked for more details regarding the interest rates.', 'admin')");

    // Lead 3: Partner Lead, Cold, In Progress, Added Today
    $stmt->execute([
        'Priya Singh', 'Singh Boutique', '9988776655', 'priya@singh.com', 'Referral', 'Contacted', 'Cold', 'admin', 'PART-1', $today, null
    ]);
    $lead3_id = $db->lastInsertId();
    
    // Lead 4: Past due reminder
    $past_reminder = date('Y-m-d H:i:s', strtotime('-2 hours'));
    $stmt->execute([
        'Vikram Joshi', 'Joshi Logistics', '9871122334', 'vikram@joshi.com', 'Facebook', 'Scheduled', 'Hot', 'admin', 'direct', $yesterday, $past_reminder
    ]);
    $lead4_id = $db->lastInsertId();
    $db->exec("INSERT INTO lead_notes (lead_id, note_text, added_by_user) VALUES ($lead4_id, 'Meeting scheduled at his office. Do not forget to carry the catalog.', 'admin')");

    echo "Successfully deleted all leads and added 4 new dummy leads for testing.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
