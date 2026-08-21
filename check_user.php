<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'config.php';
$output = "Starting check...\n";

try {
    $stmt = $db->query("SELECT * FROM users WHERE username = 'builder'");
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        $output .= "User exists:\n";
        $output .= print_r($user, true);
        if (password_verify('password123', $user['password_hash'])) {
            $output .= "Password matches 'password123'.\n";
        } else {
            $output .= "Password DOES NOT match 'password123'.\n";
        }
    } else {
        $output .= "User 'builder' not found in database.\n";
    }
} catch (Exception $e) {
    $output .= "Error: " . $e->getMessage() . "\n";
}

file_put_contents('output.txt', $output);
?>
