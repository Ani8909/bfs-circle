<?php
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    die("Access Denied. Only Admins can download this data.");
}

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=Staff_Ranking_' . date('Y-m-d') . '.csv');

$output = fopen('php://output', 'w');
fputs($output, "\xEF\xBB\xBF"); // BOM for excel

fputcsv($output, ['Rank', 'Executive Name', 'Total Field Visits', 'Last Active (Visit Date)']);

$stmt = $db->query("
    SELECT executive_name, COUNT(*) as total_visits, MAX(created_at) as last_activity 
    FROM field_visits 
    GROUP BY executive_name 
    ORDER BY total_visits DESC, last_activity DESC
");
$rankings = $stmt->fetchAll(PDO::FETCH_ASSOC);

$rank = 1;
foreach ($rankings as $staff) {
    fputcsv($output, [
        $rank,
        $staff['executive_name'],
        $staff['total_visits'],
        date('d M Y, h:i A', strtotime($staff['last_activity']))
    ]);
    $rank++;
}

fclose($output);
exit;
