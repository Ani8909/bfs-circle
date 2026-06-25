<?php
$db = new PDO('sqlite:crm.db');

$username = 'staff1';
$password = 'staff123';
$name = 'Test Staff';
$role = 'Staff';

// Check if user already exists
$check = $db->prepare("SELECT id FROM users WHERE username = ?");
$check->execute([$username]);
if ($check->fetch()) {
    echo "Staff member '$username' already exists.\n";
} else {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $db->prepare("INSERT INTO users (username, password_hash, role, name) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$username, $hash, $role, $name])) {
        echo "Staff member created successfully!\n";
        echo "Username: $username\n";
        echo "Password: $password\n";
    } else {
        echo "Failed to create staff member.\n";
    }
}
?>
