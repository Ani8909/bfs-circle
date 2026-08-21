<?php
$db = new PDO('sqlite:crm.db');
$query = $db->query("SELECT name, sql FROM sqlite_master WHERE type='table' AND name IN ('applicants', 'applicant_documents');");
while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
    echo "Table: " . $row['name'] . "\n";
    echo $row['sql'] . "\n\n";
}
?>
