<?php
require_once 'includes/header.php';

// Get Referral ID from referrals table for this builder
// For builders, we assume the users table links to referrals via some mapping or they share the same username.
// Let's assume builders are also in referrals where referrer_type = 'Builder' and email or mobile matches,
// OR for simplicity in this demo, let's just match by full_name or assume a fixed referral_id.
$stmt_ref = $db->prepare("SELECT referral_id, full_name, commission_rate FROM referrals WHERE referrer_type = 'Builder' AND full_name = ? LIMIT 1");
$stmt_ref->execute([$builder_name]);
$partner = $stmt_ref->fetch();

$referral_id = $partner['referral_id'] ?? 'BLD-' . $builder_id;
$commission_pct = floatval($partner['commission_rate'] ?? 0.5); // Default 0.5% for Home Loans

// Date Filtering Logic
$filter = $_GET['filter'] ?? 'this_month';
$start_date = '';
$end_date = '';

if ($filter === 'this_week') {
    $start_date = date('Y-m-d', strtotime('monday this week'));
    $end_date = date('Y-m-d', strtotime('sunday this week'));
} elseif ($filter === 'this_month') {
    $start_date = date('Y-m-01');
    $end_date = date('Y-m-t');
} elseif ($filter === 'this_year') {
    $start_date = date('Y-01-01');
    $end_date = date('Y-12-31');
} elseif ($filter === 'custom') {
    $start_date = $_GET['start'] ?? date('Y-m-01');
    $end_date = $_GET['end'] ?? date('Y-m-t');
}

$date_condition = "";
$params = [$referral_id];

if ($start_date && $end_date) {
    $date_condition = " AND date(created_at) BETWEEN ? AND ?";
    $params[] = $start_date;
    $params[] = $end_date;
}

// Get Home Loan Stats
$stmt_stats = $db->prepare("
    SELECT 
        COUNT(id) as total_referrals,
        SUM(CASE WHEN overall_status IN ('Phase 2', 'Phase 3', 'Phase 4', 'Completed') THEN 1 ELSE 0 END) as approved_cases,
        SUM(CASE WHEN overall_status = 'Completed' THEN 1 ELSE 0 END) as disbursed_cases,
        SUM(CASE WHEN overall_status = 'Completed' THEN loan_amount_requested ELSE 0 END) as total_disbursed_amt
    FROM applicants 
    WHERE referral_id = ? $date_condition
");
$stmt_stats->execute($params);
$stats = $stmt_stats->fetch();

$total_submissions = (int)$stats['total_referrals'];
$approved = (int)$stats['approved_cases'];
$disbursed = (int)$stats['disbursed_cases'];
$disbursed_amount = (float)$stats['total_disbursed_amt'];

// Calculate estimated commission based on % of disbursed amount
$estimated_payout = $disbursed_amount * ($commission_pct / 100); 

$apply_link = "http://" . $_SERVER['HTTP_HOST'] . "/apply.php?ref=" . $referral_id;
$wa_text = urlencode("Hi! You can apply for your Home Loan through my official link here: " . $apply_link);
?>

<style>
.custom-select-wrapper { position: relative; user-select: none; }
.custom-select { background: rgba(255,255,255,0.15); padding: 6px 14px; border-radius: 14px; font-size: 12px; font-weight: 600; color: white; border: 1px solid rgba(255,255,255,0.2); cursor: pointer; display: flex; align-items: center; gap: 6px; transition: all 0.2s; }
.custom-select:hover { background: rgba(255,255,255,0.25); }
.custom-options { position: absolute; top: 100%; right: 0; margin-top: 8px; background: white; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); overflow: hidden; display: none; flex-direction: column; min-width: 140px; z-index: 50; border: 1px solid var(--border); }
.custom-options.open { display: flex; }
.custom-option { padding: 12px 16px; font-size: 13px; font-weight: 500; color: #1e293b; cursor: pointer; transition: background 0.2s; text-align: left; }
.custom-option:hover { background: #f1f5f9; color: var(--primary); }
.custom-option.selected { background: #f8fafc; color: var(--primary); font-weight: 700; }
</style>

<!-- Welcome Section -->
<div style="margin-bottom: 24px;">
    <h2 style="font-family: 'Outfit'; font-size: 24px; color: var(--text-primary); display: flex; align-items: center; gap: 8px;">
        Welcome, <?php echo htmlspecialchars(explode(' ', $builder_name)[0]); ?> 👋
    </h2>
    <p style="color: var(--text-muted); font-size: 14px; margin-top: 4px;">Welcome to the BFS Financial Services Builder Family!</p>
</div>

<!-- Commission Card -->
<div class="card" style="background: linear-gradient(135deg, var(--primary), var(--primary-hover)); color: white; margin-bottom: 24px; border: none; box-shadow: 0 10px 25px rgba(15, 23, 42, 0.2); position: relative; z-index: 10;">
    <div style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 16px;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div style="font-size: 13px; font-weight: 500; color: #cbd5e1; display:flex; align-items:center; gap:6px;">
                <i data-lucide="wallet" style="width:16px;"></i> Expected Payout
            </div>
            <form method="GET" id="filterForm" style="margin:0; display:flex; flex-direction:column; align-items:flex-end; gap:8px;">
                <input type="hidden" name="filter" id="filterInput" value="<?php echo htmlspecialchars($filter); ?>">
                
                <div class="custom-select-wrapper">
                    <div class="custom-select" onclick="document.getElementById('customOpts').classList.toggle('open')">
                        <?php 
                        $labels = ['this_week'=>'This Week', 'this_month'=>'This Month', 'this_year'=>'This Year', 'custom'=>'Custom...'];
                        echo $labels[$filter] ?? 'This Month';
                        ?>
                        <i data-lucide="chevron-down" style="width:14px;"></i>
                    </div>
                    <div class="custom-options" id="customOpts">
                        <div class="custom-option <?php echo $filter=='this_week'?'selected':''; ?>" onclick="setFilter('this_week')">This Week</div>
                        <div class="custom-option <?php echo $filter=='this_month'?'selected':''; ?>" onclick="setFilter('this_month')">This Month</div>
                        <div class="custom-option <?php echo $filter=='this_year'?'selected':''; ?>" onclick="setFilter('this_year')">This Year</div>
                        <div class="custom-option <?php echo $filter=='custom'?'selected':''; ?>" onclick="setFilter('custom')">Custom...</div>
                    </div>
                </div>
        </div>
        
        <div id="customDates" style="display: <?php echo $filter=='custom' ? 'flex' : 'none'; ?>; gap:6px; align-items:center; flex-wrap:nowrap; justify-content:flex-end; width:100%;">
            <input type="date" name="start" value="<?php echo htmlspecialchars($start_date); ?>" style="padding:6px; font-size:11px; border-radius:6px; border:none; outline:none; color:#0f172a; flex:1; max-width:130px;">
            <span style="color:white; font-size:11px; opacity:0.8;">to</span>
            <input type="date" name="end" value="<?php echo htmlspecialchars($end_date); ?>" style="padding:6px; font-size:11px; border-radius:6px; border:none; outline:none; color:#0f172a; flex:1; max-width:130px;">
            <button type="submit" style="background:white; color:var(--primary); border:none; border-radius:6px; padding:6px 12px; font-size:11px; font-weight:800; cursor:pointer;">Go</button>
        </div>
        </form>
    </div>
    <div style="font-family: 'Outfit'; font-size: 32px; font-weight: 800; color: var(--accent); margin-bottom: 4px;">
        ₹<?php echo number_format($estimated_payout); ?>
    </div>
    <div style="display: flex; justify-content: space-between; align-items: flex-end;">
        <div style="font-size: 12px; color: #94a3b8; font-weight: 500;">
            Based on <?php echo $commission_pct; ?>% of ₹<?php echo number_format($disbursed_amount); ?>
        </div>
        <a href="payouts.php" style="background: var(--accent); color: white; border: none; padding: 6px 12px; border-radius: 8px; font-weight: 600; font-size: 11px; text-decoration: none;">View Payouts</a>
    </div>
</div>

<!-- Stats Grid -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px;">
    <!-- Card 1 -->
    <div class="card" style="margin-bottom: 0;">
        <div style="width: 38px; height: 38px; border-radius: 10px; background: rgba(15, 23, 42, 0.05); color: var(--primary); display: flex; align-items: center; justify-content: center; margin-bottom: 12px;">
            <i data-lucide="home" style="width: 20px; height: 20px;"></i>
        </div>
        <div style="font-size: 28px; font-weight: 800; color: var(--text-primary); font-family: 'Outfit';"><?php echo $total_submissions; ?></div>
        <div style="font-size: 13px; color: var(--text-muted); font-weight: 600; margin-top: 4px;">Total Bookings</div>
    </div>
    
    <!-- Card 2 -->
    <div class="card" style="margin-bottom: 0;">
        <div style="width: 38px; height: 38px; border-radius: 10px; background: rgba(16, 185, 129, 0.15); color: var(--accent); display: flex; align-items: center; justify-content: center; margin-bottom: 12px;">
            <i data-lucide="check-circle" style="width: 20px; height: 20px;"></i>
        </div>
        <div style="font-size: 28px; font-weight: 800; color: var(--text-primary); font-family: 'Outfit';"><?php echo $approved; ?></div>
        <div style="font-size: 13px; color: var(--text-muted); font-weight: 600; margin-top: 4px;">Sanctioned</div>
    </div>
</div>

<!-- Disbursed Amount Target/Tracker -->
<?php
$target_amount = 50000000; // 5 Crore target for Builders
$progress_pct = min(100, ($disbursed_amount / $target_amount) * 100);
?>
<div class="card" style="margin-bottom: 24px; padding: 20px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
        <div style="font-family:'Outfit'; font-weight:700; color:var(--text-primary); font-size:16px;">Sales Volume Target</div>
        <div style="font-size:12px; color:var(--text-muted); font-weight:600;">₹5 Cr</div>
    </div>
    <div style="background: #f1f5f9; height: 10px; border-radius: 5px; overflow: hidden; margin-bottom: 8px;">
        <div style="background: var(--accent); height: 100%; width: <?php echo $progress_pct; ?>%; border-radius: 5px;"></div>
    </div>
    <div style="font-size:12px; color:var(--text-muted);">
        <strong>₹<?php echo number_format($disbursed_amount); ?></strong> disbursed so far. 
        <?php if ($progress_pct >= 100): ?>
            <span style="color:var(--accent); font-weight:bold;">Target Achieved! 🎉</span>
        <?php else: ?>
            Keep pushing to unlock bulk APF benefits!
        <?php endif; ?>
    </div>
</div>

<script>
function setFilter(val) {
    document.getElementById('customOpts').classList.remove('open');
    if(val === 'custom') {
        document.getElementById('customDates').style.display='flex';
        document.getElementById('filterInput').value = 'custom';
        document.querySelector('.custom-select').innerHTML = 'Custom... <i data-lucide="chevron-down" style="width:14px;"></i>';
        lucide.createIcons();
    } else {
        document.getElementById('filterInput').value = val;
        document.getElementById('filterForm').submit();
    }
}
document.addEventListener('click', function(e) {
    if (!e.target.closest('.custom-select-wrapper')) {
        const opts = document.getElementById('customOpts');
        if(opts) opts.classList.remove('open');
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
