<?php
$db = new PDO('sqlite:crm.db');
$db->exec("ALTER TABLE bankers ADD COLUMN pincode TEXT");
echo "Added pincode\n";
