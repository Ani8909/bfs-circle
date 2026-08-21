<?php
$db = new PDO('sqlite:crm.db');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

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
        echo "Error: " . $e->getMessage() . "\n";
    }
}
echo "Done.\n";
