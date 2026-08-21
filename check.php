<?php
$db = new PDO('sqlite:crm.db');
$city = 'AGRA';
$s = $db->prepare("SELECT DISTINCT city FROM ifsc_master WHERE city = ? OR city LIKE ? OR city LIKE ? OR city LIKE ?");
$s->execute([$city, $city . ' %', '% ' . $city, '% ' . $city . ' %']);
print_r($s->fetchAll(PDO::FETCH_COLUMN));
