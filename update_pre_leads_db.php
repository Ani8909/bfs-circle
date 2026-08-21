<?php
require_once 'config.php';

$queries = [
    "ALTER TABLE pre_leads ADD COLUMN service_intent TEXT DEFAULT 'Unspecified / Raw'",
    "ALTER TABLE pre_leads ADD COLUMN followup_date DATETIME",
    "ALTER TABLE pre_leads ADD COLUMN last_called_at DATETIME",
    "ALTER TABLE pre_leads ADD COLUMN call_count INTEGER DEFAULT 0",
    "ALTER TABLE pre_leads ADD COLUMN heat_score INTEGER DEFAULT 0"
];

foreach ($queries as $q) {
    try {
        $db->exec($q);
        echo "Executed: $q\n";
    } catch (PDOException $e) {
        // Ignore if column already exists
        if (strpos($e->getMessage(), 'duplicate column name') !== false) {
            echo "Column already exists.\n";
        } else {
            echo "Error: " . $e->getMessage() . "\n";
        }
    }
}

echo "Database update complete.\n";
