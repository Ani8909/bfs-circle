<?php
$db = new PDO('sqlite:crm.db');
$db->exec("ALTER TABLE bankers ADD COLUMN status TEXT DEFAULT 'Active'");
echo "Success\n";
