<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['SCRIPT_NAME'] = '/leads.php';
$_GET['api'] = 'get_leads';

session_start();
require 'config.php';
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'Admin';
$_SESSION['username'] = 'admin';
$_SESSION['session_token'] = $db->query('SELECT session_token FROM users WHERE id=1')->fetchColumn();

// Include API logic
$action = 'get_leads';
$s          = $_GET['search'] ?? '';
$f_stage    = $_GET['stage'] ?? '';
$f_priority = $_GET['priority'] ?? '';
$f_assigned = $_GET['assigned_to'] ?? '';
$sql_l      = "SELECT * FROM leads WHERE 1=1";
$params_l   = [];
if (($_SESSION['role'] ?? '') !== 'Admin') {
    $sql_l .= " AND assigned_to = ?";
    $params_l[] = $_SESSION['username'] ?? '';
}
if ($s)          { $sql_l .= " AND (lead_name LIKE ? OR company_name LIKE ? OR mobile LIKE ?)"; $params_l = array_merge($params_l, ["%$s%","%$s%","%$s%"]); }
if ($f_stage)    { $sql_l .= " AND stage = ?";       $params_l[] = $f_stage; }
if ($f_priority) { $sql_l .= " AND priority = ?";    $params_l[] = $f_priority; }
if ($f_assigned) { $sql_l .= " AND assigned_to = ?"; $params_l[] = $f_assigned; }
$sql_l .= " ORDER BY created_at DESC";
$stmt = $db->prepare($sql_l);
$stmt->execute($params_l);
$leads = $stmt->fetchAll();
var_dump($leads);
?>
