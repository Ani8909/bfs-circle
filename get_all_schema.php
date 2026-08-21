<?php
$db = new PDO('sqlite:crm.db');
$query = $db->query("SELECT sql FROM sqlite_master WHERE type='table';");
while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
    echo $row['sql'] . "\n\n";
}
?>
