<?php
require_once 'config.php';
$_SESSION['role'] = 'Admin';
$_SESSION['username'] = 'admin';

$where = "1=1";
$params = [];
$limit = 10;
$offset = 0;

$stmt_c = $db->prepare("SELECT COUNT(*) FROM pre_leads WHERE $where");
$stmt_c->execute($params);
$total = $stmt_c->fetchColumn();

$stmt = $db->prepare("SELECT * FROM pre_leads WHERE $where ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
$stmt->execute($params);

echo json_encode([
    'success' => true,
    'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
    'total' => $total
]);
