<?php
$db = new PDO("sqlite:" . __DIR__ . "/crm.db");
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $db->query("SELECT * FROM users WHERE username = 'advisor01'");
$user = $stmt->fetch();

if ($user) {
    $user_id = $user['id'];
    $stmt2 = $db->query("SELECT * FROM referrals WHERE user_id = $user_id");
    $ref = $stmt2->fetch();
    
    if (!$ref) {
        $referral_id = 'ADV-'.time();
        $db->prepare("INSERT INTO referrals (user_id, full_name, referrer_type, referral_id, mobile, email) VALUES (?, ?, ?, ?, ?, ?)")->execute([$user_id, 'Demo Adviser', 'Financial Adviser', $referral_id, '9999999999', 'demo@adviser.com']);
        echo "Created missing referral entry for advisor01\n";
    } else {
        echo "Referral entry already exists\n";
    }
} else {
    echo "advisor01 not found\n";
}
?>
