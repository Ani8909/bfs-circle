<?php
$db = new PDO('sqlite:crm.db');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$username = 'fieldstaff1@aura.com';
$password = 'Field@2026';
$hash = password_hash($password, PASSWORD_DEFAULT);
$fullname = 'Rahul Field Exec';
$emp_id = 'AURA-F-001';
$mobile = '9876543210';
$department = 'Lead Generation Team';
$role = 'Staff';

try {
    $db->beginTransaction();
    
    // Check if user exists
    $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user_id = $stmt->fetchColumn();
    
    if (!$user_id) {
        $stmt = $db->prepare("INSERT INTO users (username, name, password_hash, role, is_active) VALUES (?, ?, ?, ?, 1)");
        $stmt->execute([$username, $fullname, $hash, $role]);
        $user_id = $db->lastInsertId();
    } else {
        $stmt = $db->prepare("UPDATE users SET password_hash = ?, role = ? WHERE id = ?");
        $stmt->execute([$hash, $role, $user_id]);
    }
    
    // Check if employee exists
    $stmt = $db->prepare("SELECT id FROM employees WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $emp_exist = $stmt->fetchColumn();
    
    if (!$emp_exist) {
        $stmt = $db->prepare("INSERT INTO employees (user_id, emp_id, full_name, official_email, mobile, department, access_role, commission_rate, designation) VALUES (?, ?, ?, ?, ?, ?, ?, 1.5, 'Field Executive')");
        $stmt->execute([$user_id, $emp_id, $fullname, $username, $mobile, $department, $role]);
    } else {
        $stmt = $db->prepare("UPDATE employees SET department = ?, commission_rate = 1.5 WHERE user_id = ?");
        $stmt->execute([$department, $user_id]);
    }
    
    $db->commit();
    echo "SUCCESS\n";
    echo "Username: " . $username . "\n";
    echo "Password: " . $password . "\n";
    
} catch (Exception $e) {
    $db->rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
}
