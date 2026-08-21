<?php
$db = new PDO('sqlite:' . __DIR__ . '/crm.db');
$tables = $db->query('SELECT name FROM sqlite_master WHERE type="table"')->fetchAll(PDO::FETCH_ASSOC);
foreach($tables as $t) {
    echo "\nTable: " . $t['name'] . "\n";
    $cols = $db->query("PRAGMA table_info(" . $t['name'] . ")")->fetchAll(PDO::FETCH_ASSOC);
    foreach($cols as $c) {
        echo "  - " . $c['name'] . " (" . $c['type'] . ")\n";
    }
}
