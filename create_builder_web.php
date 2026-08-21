<?php
require 'config.php';
$hash = password_hash('password123', PASSWORD_DEFAULT);
try {
    // Check if exists
    $stmt = $db->query("SELECT id FROM users WHERE username = 'builder'");
    if ($stmt->fetch()) {
        $db->exec("UPDATE users SET password_hash = '$hash', role = 'Builder' WHERE username = 'builder'");
        echo "Builder user updated!";
    } else {
        $db->exec("INSERT INTO users (username, name, password_hash, role) VALUES ('builder', 'Lodha Developers', '$hash', 'Builder')");
        echo "Builder user created!";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
