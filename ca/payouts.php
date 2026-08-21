<?php
require_once '../config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'CA') {
    header("Location: ../login.php");
    exit;
}

$page_title = 'Earnings & Payouts - CA Portal';
$active_page = 'payouts';
require_once 'includes/header.php';
?>
<style>
    .page-header { display: flex; align-items: center; gap: 12px; margin-bottom: 24px; font-size: 20px; font-weight: 800; color: var(--primary); }
    
    .earnings-card {
        background: var(--primary); border-radius: 16px; padding: 32px; color: white;
        margin-bottom: 32px; box-shadow: var(--shadow-lg); position: relative; overflow: hidden;
    }
    .earnings-subtitle { font-size: 14px; color: #cbd5e1; margin-bottom: 6px; font-weight: 500; }
    .earnings-amount { font-size: 42px; font-weight: 800; letter-spacing: -1px; margin-bottom: 24px; color: white; }
    .earnings-actions { display: flex; gap: 16px; }
    
    .btn-action {
        flex: 1; padding: 14px; border-radius: 8px; font-weight: 600; font-size: 14px;
        display: flex; align-items: center; justify-content: center; gap: 8px;
        text-decoration: none; transition: background 0.2s; cursor: pointer; border: none;
    }
    .btn-primary-white { background: var(--accent); color: white; }
    .btn-primary-white:hover { background: #EA580C; }
    .btn-glass { background: rgba(255,255,255,0.1); color: white; border: 1px solid rgba(255,255,255,0.2); }
    .btn-glass:hover { background: rgba(255,255,255,0.2); }

    .history-card { background: var(--surface); border-radius: 12px; padding: 24px; border: 1px solid var(--border); }
    .history-header { font-size: 16px; font-weight: 700; margin-bottom: 16px; color: var(--primary); }
    .history-item { display: flex; justify-content: space-between; align-items: center; padding: 16px 0; border-bottom: 1px solid var(--border); }
    .history-item:last-child { border-bottom: none; }
    .h-title { font-weight: 700; font-size: 15px; color: var(--text-main); margin-bottom: 2px; }
    .h-subtitle { font-size: 13px; color: var(--text-muted); font-weight: 500; }
    .h-amount { font-weight: 700; font-size: 16px; color: var(--success); }

    @media (max-width: 768px) {
        .earnings-card { padding: 24px; border-radius: 12px; }
        .earnings-amount { font-size: 36px; }
        .history-card { padding: 20px; border-radius: 12px; }
        .earnings-actions { flex-direction: column; gap: 12px; }
    }
</style>

<?php
$user_id = $_SESSION['user_id'];

// Get totals
$stmt = $db->prepare("SELECT 
    SUM(CASE WHEN status='Paid' THEN net_payable ELSE 0 END) as total_paid,
    SUM(CASE WHEN status!='Paid' AND status!='Cancelled' THEN net_payable ELSE 0 END) as total_pending
    FROM payout_distributions WHERE payee_user_id = ?");
$stmt->execute([$user_id]);
$totals = $stmt->fetch(PDO::FETCH_ASSOC);

$total_paid = $totals['total_paid'] ?: 0;
$total_pending = $totals['total_pending'] ?: 0;

// Get history
$stmt2 = $db->prepare("SELECT pd.*, a.customer_name, a.loan_id 
    FROM payout_distributions pd 
    LEFT JOIN applicants a ON pd.applicant_id = a.id 
    WHERE pd.payee_user_id = ? 
    ORDER BY pd.created_at DESC");
$stmt2->execute([$user_id]);
$payouts = $stmt2->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="page-header">
    <i data-lucide="wallet" style="color:var(--primary); width:28px; height:28px;"></i>
    Earnings & Payouts
</div>

<div style="display:flex; gap:16px; margin-bottom:24px; flex-wrap:wrap;">
    <div class="earnings-card" style="flex:1; margin-bottom:0;">
        <div class="earnings-subtitle">Total Earned (Paid)</div>
        <div class="earnings-amount">₹<?= number_format($total_paid, 2) ?></div>
        
        <div class="earnings-actions">
            <button class="btn-action btn-glass ripple" onclick="vibrateAction(); document.getElementById('invoice-upload').click();"><i data-lucide="upload"></i> Upload Invoice</button>
            <input type="file" id="invoice-upload" style="display:none;" accept=".pdf,.jpg,.jpeg,.png">
        </div>
    </div>
    <div class="earnings-card" style="flex:1; background:var(--accent); margin-bottom:0;">
        <div class="earnings-subtitle">Total Pending</div>
        <div class="earnings-amount">₹<?= number_format($total_pending, 2) ?></div>
        <div style="font-size: 13px; margin-top: -15px; margin-bottom: 20px; opacity: 0.8;">Admin is processing these</div>
    </div>
</div>

<div class="history-card">
    <div class="history-header">Payout History</div>
    <div id="payout-history">
        <?php if(count($payouts) > 0): ?>
            <?php foreach($payouts as $p): ?>
                <div class="history-item">
                    <div>
                        <div class="h-title">Commission for <?= htmlspecialchars($p['customer_name'] ?? 'Unknown Lead') ?></div>
                        <div class="h-subtitle">Loan ID: <?= htmlspecialchars($p['loan_id'] ?? 'N/A') ?> &nbsp;|&nbsp; Date: <?= date('d M Y', strtotime($p['created_at'])) ?></div>
                    </div>
                    <div style="text-align:right;">
                        <div class="h-amount" style="color: <?= $p['status'] === 'Paid' ? 'var(--success)' : ($p['status'] === 'Cancelled' ? 'var(--danger)' : '#f59e0b') ?>;">₹<?= number_format($p['net_payable'], 2) ?></div>
                        <div style="font-size:12px; font-weight:600; color: <?= $p['status'] === 'Paid' ? 'var(--success)' : ($p['status'] === 'Cancelled' ? 'var(--danger)' : '#f59e0b') ?>;"><?= $p['status'] ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="text-align:center; padding: 20px; color: var(--text-muted); font-weight:500;">No payout history found.</div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
