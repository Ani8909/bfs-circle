<?php
require_once 'config.php';
$stmt = $db->query("SELECT description, created_at, action_link, target_user FROM activities");
print_r($stmt->fetchAll());
