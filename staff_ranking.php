<?php
require_once 'config.php';
$current_page = 'staff_ranking.php';
$page_title = 'Field Staff Leaderboard';
$page_subtitle = 'Ranking of all field staff based on the total number of visits generated.';
require_once 'header.php';

// Fetch rankings
$stmt = $db->query("
    SELECT executive_name, COUNT(*) as total_visits, MAX(created_at) as last_activity 
    FROM field_visits 
    GROUP BY executive_name 
    ORDER BY total_visits DESC, last_activity DESC
");
$rankings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
    .header-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
    }
    
    .table-container {
        background: var(--card-bg, #fff);
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        overflow-x: auto;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        white-space: nowrap;
    }

    th {
        background: #F8FAFC;
        padding: 16px 24px;
        text-align: left;
        font-size: 12px;
        font-weight: 600;
        color: var(--text-muted, #64748B);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border-bottom: 1px solid var(--border-color, #E2E8F0);
    }

    td {
        padding: 16px 24px;
        font-size: 14px;
        color: var(--text-main, #0F172A);
        border-bottom: 1px solid var(--border-color, #E2E8F0);
        vertical-align: middle;
    }

    tr:last-child td {
        border-bottom: none;
    }
    
    tr:hover {
        background: #F8FAFC;
    }

    .rank-badge {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 14px;
    }
    
    .rank-1 { background: #fef08a; color: #854d0e; border: 2px solid #eab308; }
    .rank-2 { background: #e2e8f0; color: #475569; border: 2px solid #94a3b8; }
    .rank-3 { background: #ffedd5; color: #9a3412; border: 2px solid #f97316; }
    .rank-other { background: #f1f5f9; color: #64748b; }

</style>

<div class="header-actions">
    <div>
        <a href="field_visits.php" style="color: var(--primary); text-decoration: none; font-weight: 500; font-size: 14px;">
            <i class="fas fa-arrow-left"></i> Back to Field Visits
        </a>
    </div>
    <a href="export_ranking.php" class="btn" style="background:#10b981; color:white; padding:10px 16px; border-radius:8px; text-decoration:none; font-size:14px; font-weight:600; display:flex; align-items:center; gap:8px; box-shadow:0 2px 4px rgba(16,185,129,0.2);">
        <i class="fas fa-download"></i> Download Ranking
    </a>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th style="width:80px;">Rank</th>
                <th>Executive Name</th>
                <th>Total Field Visits</th>
                <th>Last Active (Visit Date)</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($rankings) === 0): ?>
                <tr>
                    <td colspan="4" style="text-align:center; padding:30px; color:var(--text-muted);">No field visits recorded yet.</td>
                </tr>
            <?php else: ?>
                <?php 
                $rank = 1;
                foreach ($rankings as $staff): 
                    $rankClass = 'rank-other';
                    if ($rank === 1) $rankClass = 'rank-1';
                    else if ($rank === 2) $rankClass = 'rank-2';
                    else if ($rank === 3) $rankClass = 'rank-3';
                ?>
                <tr>
                    <td>
                        <div class="rank-badge <?= $rankClass ?>">
                            <?php if ($rank === 1): ?><i class="fas fa-trophy" style="font-size:12px;"></i>
                            <?php else: ?><?= $rank ?><?php endif; ?>
                        </div>
                    </td>
                    <td style="font-weight:600;"><?= htmlspecialchars($staff['executive_name']) ?></td>
                    <td>
                        <span style="font-size:16px; font-weight:700; color:var(--primary);"><?= $staff['total_visits'] ?></span> visits
                    </td>
                    <td style="color:var(--text-muted); font-size:13px;">
                        <?= date('d M Y, h:i A', strtotime($staff['last_activity'])) ?>
                    </td>
                </tr>
                <?php $rank++; endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once 'footer.php'; ?>
