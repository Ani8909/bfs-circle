<?php
$db = new PDO('sqlite:crm.db');
$t = $db->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name");
$rows = $t->fetchAll(PDO::FETCH_COLUMN);
echo implode("\n", $rows);
