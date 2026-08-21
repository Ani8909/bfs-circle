<?php
$db = new PDO('sqlite:' . __DIR__ . '/crm.db');
$statuses = $db->query('SELECT DISTINCT overall_status FROM applicants')->fetchAll(PDO::FETCH_ASSOC);
print_r($statuses);
