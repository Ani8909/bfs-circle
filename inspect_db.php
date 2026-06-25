<?php
$db = new PDO('sqlite:crm.db');
$query = $db->query("SELECT name, sql FROM sqlite_master WHERE type='table' AND name IN ('users', 'staff');");
while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
    echo "Table: " . $row['name'] . "\n";
    echo $row['sql'] . "\n\n";
}
$query = $db->query("SELECT * FROM users LIMIT 1;");
if ($query) {
    print_r($query->fetch(PDO::FETCH_ASSOC));
}
?>
