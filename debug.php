<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require 'config.php';
try {
    $stmt = $db->prepare('SELECT a.* FROM applicants a LEFT JOIN referrals r ON a.referral_id = r.referral_id WHERE a.added_by = ? OR r.assigned_rm = ?');
    $stmt->execute(['staff1', 'staff1']);
    var_dump($stmt->fetchAll());
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
