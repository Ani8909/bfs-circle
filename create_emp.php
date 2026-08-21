<?php
require 'config.php';
$username = 'employee';
$password = 'emp123';
$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
$stmt->execute([$username]);
if ($stmt->fetchColumn() == 0) {
    $stmt = $db->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, 'Staff')");
    $stmt->execute([$username, $hash]);
    echo "Employee created successfully.";
} else {
    // update password just in case
    $stmt = $db->prepare("UPDATE users SET password_hash = ?, role = 'Staff' WHERE username = ?");
    $stmt->execute([$hash, $username]);
    echo "Employee updated successfully.";
}
