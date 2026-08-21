<?php
require_once 'includes/header.php';

// Fetch Leaderboard Data
$stmt = $db->query("
    SELECT 
        u.name as partner_name,
        u.username,
        a.referral_id,
        COUNT(a.id) as total_leads,
        SUM(CASE WHEN a.overall_status IN ('Phase 2', 'Phase 3', 'Phase 4', 'Completed') THEN 1 ELSE 0 END) as approved_leads
    FROM applicants a
    JOIN users u ON a.referral_id = u.username
    WHERE u.role IN ('Agent', 'Partner')
    GROUP BY a.referral_id
    ORDER BY approved_leads DESC, total_leads DESC
    LIMIT 10
");
$rankings = $stmt->fetchAll(PDO::FETCH_ASSOC);

$my_rank = '-';
$my_approved = 0;
foreach ($rankings as $index => $rank) {
    if ($rank['username'] === $partner_username) {
        $my_rank = $index + 1;
        $my_approved = $rank['approved_leads'];
        break;
    }
}
?>

<div style="margin-bottom: 20px; text-align: center;">
    <div style="display: inline-block; padding: 12px; background: linear-gradient(135deg, rgba(234, 179, 8, 0.1), rgba(202, 138, 4, 0.2)); border-radius: 50%; margin-bottom: 12px; color: var(--accent);">
        <i data-lucide="trophy" style="width: 32px; height: 32px;"></i>
    </div>
    <h2 style="font-family: 'Outfit'; font-size: 22px; color: var(--text-primary);">Adviser Leaderboard</h2>
    <p style="color: var(--text-muted); font-size: 14px;">Top performers based on approved clients</p>
</div>

<!-- My Rank Card -->
<div class="card" style="background: linear-gradient(135deg, #0f172a, #1e293b); color: white; display: flex; justify-content: space-between; align-items: center; padding: 20px;">
    <div>
        <div style="font-size: 13px; color: #94a3b8; font-weight: 500; margin-bottom: 4px;">My Current Rank</div>
        <div style="font-family: 'Outfit'; font-size: 28px; font-weight: 800; color: var(--accent);">
            #<?php echo $my_rank; ?>
        </div>
    </div>
    <div style="text-align: right;">
        <div style="font-size: 20px; font-weight: 700;"><?php echo $my_approved; ?></div>
        <div style="font-size: 12px; color: #94a3b8;">Approved Clients</div>
    </div>
</div>

<!-- Rankings List -->
<div style="background: white; border-radius: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid rgba(0,0,0,0.02); overflow: hidden;">
    
    <?php if (count($rankings) > 0): ?>
        <?php foreach ($rankings as $index => $agent): 
            $rank_num = $index + 1;
            $rank_color = 'var(--text-muted)';
            $bg_color = 'transparent';
            if ($rank_num == 1) { $rank_color = '#eab308'; $bg_color = 'rgba(234, 179, 8, 0.1)'; }
            if ($rank_num == 2) { $rank_color = '#94a3b8'; $bg_color = 'rgba(148, 163, 184, 0.15)'; }
            if ($rank_num == 3) { $rank_color = '#d97706'; $bg_color = 'rgba(217, 119, 6, 0.1)'; }
            
            $is_me = ($agent['username'] === $partner_username);
        ?>
            <div style="display: flex; align-items: center; padding: 16px 20px; border-bottom: 1px solid var(--border); <?php echo $is_me ? 'background: rgba(234, 179, 8, 0.05);' : ''; ?>">
                <div style="width: 40px; font-family: 'Outfit'; font-size: 18px; font-weight: 700; color: <?php echo $rank_color; ?>;">
                    <?php if ($rank_num <= 3): ?>
                        <div style="width: 32px; height: 32px; border-radius: 8px; background: <?php echo $bg_color; ?>; display: flex; align-items: center; justify-content: center;">
                            #<?php echo $rank_num; ?>
                        </div>
                    <?php else: ?>
                        <div style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; opacity: 0.7;">
                            #<?php echo $rank_num; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div style="flex: 1; margin-left: 12px;">
                    <div style="font-weight: 600; font-size: 15px; color: var(--text-primary);">
                        <?php echo htmlspecialchars($agent['partner_name']); ?>
                        <?php if ($is_me) echo '<span style="font-size:10px; background:var(--primary); color:white; padding:2px 6px; border-radius:4px; margin-left:6px; font-weight:700;">YOU</span>'; ?>
                    </div>
                    <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">
                        Total Clients: <?php echo $agent['total_leads']; ?>
                    </div>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 18px; font-weight: 700; color: var(--text-primary); font-family: 'Outfit';">
                        <?php echo $agent['approved_leads']; ?>
                    </div>
                    <div style="font-size: 10px; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">
                        Approved
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div style="text-align: center; padding: 40px 20px; color: var(--text-muted);">
            <i data-lucide="bar-chart-2" style="width: 48px; height: 48px; opacity: 0.3; margin-bottom: 12px;"></i>
            <p>No clients submitted yet to generate leaderboard.</p>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
