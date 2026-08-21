<?php
require_once 'includes/header.php';

// Get Agent Details
$stmt = $db->prepare("SELECT * FROM referrals WHERE user_id = ?");
$stmt->execute([$agent_id]);
$agent = $stmt->fetch();

$referral_id = $agent['referral_id'] ?? '';

// Get Stats
$stats = [
    'total_leads' => 0,
    'approved' => 0,
    'rejected' => 0,
    'disbursed' => 0
];

if ($referral_id) {
    // We consider 'applicants' as the main leads that move through phases
    $stmt_stats = $db->prepare("SELECT overall_status, COUNT(*) as cnt FROM applicants WHERE referral_id = ? GROUP BY overall_status");
    $stmt_stats->execute([$referral_id]);
    $results = $stmt_stats->fetchAll();
    
    foreach ($results as $row) {
        $stats['total_leads'] += $row['cnt'];
        if ($row['overall_status'] === 'Completed') $stats['disbursed'] += $row['cnt'];
        elseif ($row['overall_status'] === 'Rejected') $stats['rejected'] += $row['cnt'];
        elseif (in_array($row['overall_status'], ['Phase 2', 'Phase 3', 'Phase 4'])) $stats['approved'] += $row['cnt']; 
    }
}
?>

<div style="margin-bottom: 24px;">
    <h2 style="font-family: 'Outfit'; font-size: 24px; color: var(--text-primary);">Hello, <?php echo htmlspecialchars($agent_name); ?> 👋</h2>
    <p style="color: var(--text-muted); font-size: 14px; margin-top: 4px;">Welcome to the BFS Financial Services Agent Family!</p>
</div>

<!-- Stats Grid -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px;">
    
    <!-- Card 1 -->
    <div class="card" style="margin-bottom: 0;">
        <div style="width: 38px; height: 38px; border-radius: 10px; background: rgba(5, 150, 105, 0.1); color: #059669; display: flex; align-items: center; justify-content: center; margin-bottom: 12px;">
            <i data-lucide="users" style="width: 20px; height: 20px;"></i>
        </div>
        <div style="font-size: 28px; font-weight: 800; color: var(--text-primary); font-family: 'Outfit';"><?php echo $stats['total_leads']; ?></div>
        <div style="font-size: 13px; color: var(--text-muted); font-weight: 600; margin-top: 4px;">Total Leads</div>
    </div>
    
    <!-- Card 2 -->
    <div class="card" style="margin-bottom: 0;">
        <div style="width: 38px; height: 38px; border-radius: 10px; background: linear-gradient(135deg, rgba(5, 150, 105, 0.15), rgba(4, 120, 87, 0.2)); color: #047857; display: flex; align-items: center; justify-content: center; margin-bottom: 12px;">
            <i data-lucide="check-circle" style="width: 20px; height: 20px;"></i>
        </div>
        <div style="font-size: 28px; font-weight: 800; color: var(--text-primary); font-family: 'Outfit';"><?php echo $stats['approved']; ?></div>
        <div style="font-size: 13px; color: var(--text-muted); font-weight: 600; margin-top: 4px;">Approved</div>
    </div>
    
    <!-- Card 3 -->
    <div class="card" style="margin-bottom: 0;">
        <div style="width: 38px; height: 38px; border-radius: 10px; background: rgba(249, 115, 22, 0.1); color: #ea580c; display: flex; align-items: center; justify-content: center; margin-bottom: 12px;">
            <i data-lucide="banknote" style="width: 20px; height: 20px;"></i>
        </div>
        <div style="font-size: 28px; font-weight: 800; color: var(--text-primary); font-family: 'Outfit';"><?php echo $stats['disbursed']; ?></div>
        <div style="font-size: 13px; color: var(--text-muted); font-weight: 600; margin-top: 4px;">Disbursed</div>
    </div>
    
    <!-- Card 4 -->
    <div class="card" style="margin-bottom: 0;">
        <div style="width: 38px; height: 38px; border-radius: 10px; background: linear-gradient(135deg, rgba(249, 115, 22, 0.15), rgba(234, 88, 12, 0.2)); color: #ea580c; display: flex; align-items: center; justify-content: center; margin-bottom: 12px;">
            <i data-lucide="x-circle" style="width: 20px; height: 20px;"></i>
        </div>
        <div style="font-size: 28px; font-weight: 800; color: var(--text-primary); font-family: 'Outfit';"><?php echo $stats['rejected']; ?></div>
        <div style="font-size: 13px; color: var(--text-muted); font-weight: 600; margin-top: 4px;">Rejected</div>
    </div>
</div>


<!-- Smart Actions -->
<div class="section-title" style="margin-top:20px; font-weight:700; font-family:'Outfit';">Smart Actions</div>
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom:24px;">
    <a href="marketing.php" class="card" style="text-decoration:none; display:flex; flex-direction:column; align-items:center; justify-content:center; padding:20px 10px; text-align:center;">
        <div style="width: 44px; height: 44px; background: #e0f2fe; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 8px; color: #0284c7;">
            <i data-lucide="qr-code" style="width:20px;height:20px;"></i>
        </div>
        <div style="font-family: 'Outfit'; font-size: 14px; font-weight: 700; color: var(--text-primary);">Share Link</div>
    </a>
    <a href="calculators.php" class="card" style="text-decoration:none; display:flex; flex-direction:column; align-items:center; justify-content:center; padding:20px 10px; text-align:center;">
        <div style="width: 44px; height: 44px; background: #ffedd5; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 8px; color: #ea580c;">
            <i data-lucide="calculator" style="width:20px;height:20px;"></i>
        </div>
        <div style="font-family: 'Outfit'; font-size: 14px; font-weight: 700; color: var(--text-primary);">Calculators</div>
    </a>
</div>
<!-- Performance Funnel Chart -->
<div class="card" style="margin-bottom: 24px; padding: 24px;">
    <h3 style="font-family: 'Outfit'; font-size: 18px; color: var(--text-primary); margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
        <i data-lucide="bar-chart-2" style="color: var(--primary); width: 20px;"></i> Conversion Funnel
    </h3>
    <div style="height: 220px; width: 100%;">
        <canvas id="funnelChart"></canvas>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const ctx = document.getElementById('funnelChart').getContext('2d');
    
    // Creating gradient for chart
    let gradient = ctx.createLinearGradient(0, 0, 0, 220);
    gradient.addColorStop(0, 'rgba(5, 150, 105, 0.8)');
    gradient.addColorStop(1, 'rgba(5, 150, 105, 0.1)');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Total', 'Approved', 'Disbursed'],
            datasets: [{
                label: 'Leads',
                data: [<?php echo max(0, $stats['total_leads']); ?>, <?php echo max(0, $stats['approved']); ?>, <?php echo max(0, $stats['disbursed']); ?>],
                backgroundColor: gradient,
                borderColor: '#047857',
                borderWidth: 1.5,
                borderRadius: 8,
                barThickness: 24
            }]
        },
        options: {
            indexAxis: 'y', // Horizontal bar for funnel effect
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0f172a',
                    titleFont: { family: 'Inter', size: 13 },
                    bodyFont: { family: 'Inter', size: 14, weight: 'bold' },
                    padding: 12,
                    cornerRadius: 8,
                    displayColors: false
                }
            },
            scales: {
                x: { 
                    beginAtZero: true, 
                    grid: { display: true, drawBorder: false, color: '#f1f5f9' }, 
                    ticks: { stepSize: 1, font: { family: 'Inter', size: 11 } },
                    suggestedMax: <?php echo $stats['total_leads'] == 0 ? 5 : $stats['total_leads'] + 2; ?>
                },
                y: { 
                    grid: { display: false, drawBorder: false }, 
                    ticks: { font: { family: 'Inter', weight: '600' }, color: '#64748b' } 
                }
            }
        }
    });
});
</script>

<!-- Recent Leads List -->
<h3 style="font-family: 'Outfit'; font-size: 18px; color: var(--text-primary); margin-bottom: 12px;">Recent Activity</h3>

<?php
if ($referral_id) {
    $stmt_recent = $db->prepare("SELECT * FROM applicants WHERE referral_id = ? ORDER BY id DESC LIMIT 3");
    $stmt_recent->execute([$referral_id]);
    $recent = $stmt_recent->fetchAll();
    
    if (count($recent) > 0) {
        foreach ($recent as $app) {
            $status_class = 'badge-pending';
            if ($app['overall_status'] === 'Rejected') $status_class = 'badge-rejected';
            elseif ($app['overall_status'] === 'Completed') $status_class = 'badge-approved';
            elseif ($app['overall_status'] !== 'Phase 1') $status_class = 'badge-approved'; // Mid-phases
            
            echo '<div class="card" style="display: flex; justify-content: space-between; align-items: center; padding: 16px;">';
            echo '<div>';
            echo '<div style="font-weight: 600; font-size: 15px; color: var(--text-primary);">' . htmlspecialchars($app['customer_name']) . '</div>';
            echo '<div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">Loan: ₹' . number_format($app['loan_amount_requested']) . '</div>';
            echo '</div>';
            echo '<span class="badge ' . $status_class . '">' . htmlspecialchars($app['overall_status']) . '</span>';
            echo '</div>';
        }
    } else {
        echo '<div style="text-align: center; padding: 30px; background: white; border-radius: 16px; color: var(--text-muted);">';
        echo '<i data-lucide="inbox" style="width: 40px; height: 40px; margin-bottom: 10px; opacity: 0.5;"></i>';
        echo '<p>No leads found.</p>';
        echo '</div>';
    }
}
?>

<?php require_once 'includes/footer.php'; ?>
