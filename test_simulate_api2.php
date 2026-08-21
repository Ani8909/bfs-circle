<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'config.php';
$uid = 19;
$user = $db->query("SELECT * FROM users WHERE id = $uid")->fetch(PDO::FETCH_ASSOC);
if (!$user) { echo "No user found"; exit; }
echo "User: "; print_r($user);

$ref = $db->query("SELECT id FROM referrals WHERE user_id = $uid")->fetch(PDO::FETCH_ASSOC);
if ($ref) { echo "Already exists"; exit; }

$ref_id = 'REF-' . strtoupper(uniqid());
try {
    $stmt = $db->prepare("INSERT INTO referrals (user_id, referral_id, full_name, referrer_type, mobile, email) VALUES (?, ?, ?, ?, '', '')");
    $stmt->execute([$user['id'], $ref_id, $user['name'], $user['role']]);
    echo "Inserted!";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
?>
