<?php
try {
    $db=new PDO('sqlite:crm.db');
    echo "USERS:\n";
    foreach($db->query("SELECT * FROM users") as $row) {
        echo $row['username'] . " - " . ($row['full_name'] ?? $row['name'] ?? 'No Name') . "\n";
    }
    echo "REFERRALS:\n";
    foreach($db->query("SELECT * FROM referrals") as $row) {
        echo $row['referral_id'] . " - " . $row['full_name'] . "\n";
    }
} catch(Exception $e) { echo $e->getMessage(); }
