<?php
$db = new PDO('sqlite:crm.db');
$cols = $db->query('PRAGMA table_info(bankers)');
while($r = $cols->fetch(PDO::FETCH_ASSOC)) echo $r['name'] . "\n";
echo "---field_visits---\n";
$cols2 = $db->query('PRAGMA table_info(field_visits)');
while($r = $cols2->fetch(PDO::FETCH_ASSOC)) echo $r['name'] . "\n";
echo "---referrals---\n";
$cols3 = $db->query('PRAGMA table_info(referrals)');
while($r = $cols3->fetch(PDO::FETCH_ASSOC)) echo $r['name'] . "\n";
echo "---payout_distributions---\n";
$cols4 = $db->query('PRAGMA table_info(payout_distributions)');
while($r = $cols4->fetch(PDO::FETCH_ASSOC)) echo $r['name'] . "\n";
echo "---reminders---\n";
$cols5 = $db->query('PRAGMA table_info(reminders)');
while($r = $cols5->fetch(PDO::FETCH_ASSOC)) echo $r['name'] . "\n";
