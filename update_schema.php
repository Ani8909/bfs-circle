<?php
$db = new PDO('sqlite:crm.db');
$db->exec("ALTER TABLE users ADD COLUMN staff_type TEXT DEFAULT 'In-Office'");
$db->exec("ALTER TABLE users ADD COLUMN has_dashboard INTEGER DEFAULT 1");
$db->exec("ALTER TABLE users ADD COLUMN plain_password TEXT");
echo "Done for crm.db\n";

$db2 = new PDO('sqlite:aura_crm.sqlite');
$db2->exec("ALTER TABLE users ADD COLUMN staff_type TEXT DEFAULT 'In-Office'");
$db2->exec("ALTER TABLE users ADD COLUMN has_dashboard INTEGER DEFAULT 1");
$db2->exec("ALTER TABLE users ADD COLUMN plain_password TEXT");
echo "Done for aura_crm.sqlite\n";
?>
