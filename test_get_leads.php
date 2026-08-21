<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require 'config.php';
$_SESSION['role'] = 'Admin';
$_SESSION['user_id'] = 1;

$s          = $_GET['search'] ?? '';
$f_stage    = $_GET['stage'] ?? '';
$f_priority = $_GET['priority'] ?? '';
$f_assigned = $_GET['assigned_to'] ?? '';
$f_source   = $_GET['source'] ?? '';
$f_type     = $_GET['loan_type'] ?? '';
$page       = max(1, intval($_GET['page'] ?? 1));
$limit      = isset($_GET['limit']) ? intval($_GET['limit']) : 50;
$offset     = ($page - 1) * $limit;

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
if ($f_source)   { $sql_l .= " AND lead_source = ?"; $params_l[] = $f_source; }
if ($f_type)     { $sql_l .= " AND requirement LIKE ?"; $params_l[] = "%$f_type%"; }

$sql_l .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
$params_l[] = $limit;
$params_l[] = $offset;

try {
    $stmt_l = $db->prepare($sql_l);
    $stmt_l->execute($params_l);
    $leads = $stmt_l->fetchAll(PDO::FETCH_ASSOC);
    echo "Success! Leads found: " . count($leads) . "\n";
} catch (Exception $e) {
    echo "DB Error: " . $e->getMessage() . "\n";
}
?>
