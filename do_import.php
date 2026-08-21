<?php
$db = new PDO('sqlite:crm.db');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
echo "Reading SQL file...\n";
$sql = file_get_contents('pincodes.sql');
echo "Executing SQL...\n";
$db->exec($sql);
echo "Done.\n";
?>
