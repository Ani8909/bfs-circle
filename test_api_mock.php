<?php
$db_file = __DIR__ . '/crm.db';
$db = new PDO("sqlite:" . $db_file);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $db->query("SELECT id, session_token FROM users WHERE id = 1");
$row = $stmt->fetch(PDO::FETCH_ASSOC);

session_start();
$_SESSION['user_id'] = $row['id'];
$_SESSION['session_token'] = $row['session_token'];
$_SESSION['username'] = 'admin';
$_SESSION['role'] = 'Admin';
$_GET['api'] = 'get_staff_360';
$_GET['username'] = 'anujj';
$_SERVER['REQUEST_METHOD'] = 'GET';

function return_json($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

require 'api.php';
