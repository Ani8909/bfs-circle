<?php
$db = new PDO("sqlite:" . __DIR__ . "/crm.db");
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Check if Partner exists
$stmt = $db->query("SELECT username FROM users WHERE role = 'Partner' LIMIT 1");
$user = $stmt->fetch();

if ($user) {
    $password_hash = password_hash('123456', PASSWORD_DEFAULT);
    $db->prepare("UPDATE users SET password_hash = ? WHERE username = ?")->execute([$password_hash, $user['username']]);
    echo "ID: " . $user['username'] . "\nPassword: 123456\n";
} else {
    $username = 'advisor01';
    $password = '123456';
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $db->prepare("INSERT INTO users (username, password_hash, role, name) VALUES (?, ?, 'Partner', 'Demo Adviser')")->execute([$username, $password_hash]);
    $user_id = $db->lastInsertId();
    $referral_id = 'ADV-'.time();
    $db->prepare("INSERT INTO referrals (user_id, full_name, referrer_type, referral_id) VALUES (?, ?, ?, ?)")->execute([$user_id, 'Demo Adviser', 'Financial Adviser', $referral_id]);
    echo "ID: " . $username . "\nPassword: 123456\n";
}
?>
