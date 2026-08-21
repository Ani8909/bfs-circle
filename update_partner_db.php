<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
$db_file = __DIR__ . '/crm.db';
$db = new PDO("sqlite:" . $db_file);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
    $db->exec("ALTER TABLE referrals ADD COLUMN user_id INTEGER");
    echo "user_id added to referrals\n";
} catch(Exception $e) { echo $e->getMessage() . "\n"; }

try {
    $db->exec("CREATE TABLE IF NOT EXISTS partner_projects (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        partner_user_id INTEGER NOT NULL,
        project_name TEXT NOT NULL,
        location TEXT,
        status TEXT DEFAULT 'Active',
        notes TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(partner_user_id) REFERENCES users(id)
    )");
    echo "partner_projects table created\n";
} catch(Exception $e) { echo $e->getMessage() . "\n"; }

echo "Done\n";
?>
