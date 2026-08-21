<?php
require 'config.php';
$cols = $db->query("PRAGMA table_info(company_profile)")->fetchAll(PDO::FETCH_ASSOC);
print_r($cols);
$profile = $db->query("SELECT * FROM company_profile")->fetch(PDO::FETCH_ASSOC);
print_r($profile);
