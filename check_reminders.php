<?php
$db = new PDO('sqlite:crm.db');
$stmt = $db->query("SELECT sql FROM sqlite_master WHERE type='table' AND name='reminders'");
$res = $stmt->fetch();
if($res) {
    echo "EXISTS\n";
    echo $res['sql'];
} else {
    echo "NOT EXISTS\n";
}
?>
