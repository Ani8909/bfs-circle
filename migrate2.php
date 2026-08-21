<?php
$db = new PDO('sqlite:crm.db');
$db->exec("ALTER TABLE bankers ADD COLUMN bank_type TEXT DEFAULT 'Public Sector Bank'");
$db->exec("CREATE TABLE IF NOT EXISTS ifsc_master (ifsc TEXT PRIMARY KEY, bank TEXT, branch TEXT, address TEXT, city TEXT, state TEXT)");
echo "DB Updated\n";
