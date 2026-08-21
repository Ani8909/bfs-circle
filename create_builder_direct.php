<?php
$db_file = __DIR__ . '/crm.db';
$db = new PDO("sqlite:" . $db_file);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$hash = password_hash('password123', PASSWORD_DEFAULT);
try {
    $stmt = $db->query("SELECT id FROM users WHERE username = 'builder'");
    if ($stmt->fetch()) {
        $db->exec("UPDATE users SET password_hash = '$hash', role = 'Builder' WHERE username = 'builder'");
        echo "Builder user updated!\n";
    } else {
        $db->exec("INSERT INTO users (username, name, password_hash, role) VALUES ('builder', 'Lodha Developers', '$hash', 'Builder')");
        echo "Builder user created!\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
