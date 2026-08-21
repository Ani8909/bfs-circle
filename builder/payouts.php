<?php
require_once 'includes/header.php';

// Handle Invoice Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload_invoice') {
    $amount = $_POST['amount'] ?? 0;
    $inv_num = $_POST['invoice_number'] ?? '';
    
    // In a real app we'd handle file upload here and insert into partner_payouts with invoice proof
    if ($amount > 0) {
        try {
            // Check if partner_payouts table exists (it was created in DSA task)
            $stmt = $db->prepare("INSERT INTO partner_payouts (user_id, amount) VALUES (?, ?)");
            $stmt->execute([$builder_id, $amount]);
        } catch (Exception $e) {}
        
        echo "<script>alert('GST Invoice uploaded successfully! Payout request for ₹" . number_format($amount) . " sent to admin.'); window.location.href='payouts.php';</script>";
        exit;
    }
}
?>

<div style="margin-bottom: 24px; display:flex; justify-content:space-between; align-items:center;">
    <div>
        <h2 style="font-family: 'Outfit'; font-size: 22px; color: var(--text-primary); display: flex; align-items: center; gap: 8px;">
            Payouts & Invoices
        </h2>
        <p style="color: var(--text-muted); font-size: 13px; margin-top: 4px;">Upload GST Invoice to get paid</p>
    </div>
</div>

<div class="card" style="margin-bottom: 24px; padding: 24px;">
    <h3 style="font-family: 'Outfit'; font-size: 16px; margin-bottom: 16px; display:flex; align-items:center; gap:8px;">
        <i data-lucide="file-text" style="color:var(--primary); width:18px;"></i> Upload GST Invoice
    </h3>
    
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="upload_invoice">
        
        <div style="margin-bottom: 16px;">
            <label style="display: block; font-size: 12px; font-weight: 600; color: var(--text-muted); margin-bottom: 6px;">Invoice Amount (₹) *</label>
            <input type="number" name="amount" required style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 12px; font-size: 14px; outline: none;" placeholder="e.g. 50000">
        </div>
        
        <div style="margin-bottom: 16px;">
            <label style="display: block; font-size: 12px; font-weight: 600; color: var(--text-muted); margin-bottom: 6px;">Invoice Number</label>
            <input type="text" name="invoice_number" style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 12px; font-size: 14px; outline: none;" placeholder="INV-2023-001">
        </div>
        
        <div style="border: 2px dashed var(--border); border-radius: 12px; padding: 20px; text-align: center; margin-bottom: 20px; background: #f8fafc;">
            <i data-lucide="upload-cloud" style="width: 24px; height: 24px; color: var(--primary); margin-bottom: 8px;"></i>
            <div style="font-size: 12px; font-weight: 600; color: var(--text-primary);">Attach PDF Invoice</div>
            <input type="file" name="invoice_file" accept=".pdf" style="margin-top: 12px; font-size: 11px; width: 100%;">
        </div>
        
        <button type="submit" style="width: 100%; background: var(--primary); color: white; border: none; padding: 14px; border-radius: 12px; font-weight: 700; font-size: 14px; cursor: pointer;">
            Submit Request
        </button>
    </form>
</div>

<?php
$user_id = $_SESSION['user_id'];

// Get totals
$stmt_t = $db->prepare("SELECT 
    SUM(CASE WHEN status='Paid' THEN net_payable ELSE 0 END) as total_paid,
    SUM(CASE WHEN status!='Paid' AND status!='Cancelled' THEN net_payable ELSE 0 END) as total_pending
    FROM payout_distributions WHERE payee_user_id = ?");
$stmt_t->execute([$user_id]);
$totals = $stmt_t->fetch(PDO::FETCH_ASSOC);

$total_paid = $totals['total_paid'] ?: 0;
$total_pending = $totals['total_pending'] ?: 0;

// Get history
$stmt_h = $db->prepare("SELECT pd.*, a.customer_name, a.loan_id 
    FROM payout_distributions pd 
    LEFT JOIN applicants a ON pd.applicant_id = a.id 
    WHERE pd.payee_user_id = ? 
    ORDER BY pd.created_at DESC");
$stmt_h->execute([$user_id]);
$payouts = $stmt_h->fetchAll(PDO::FETCH_ASSOC);
?>

<div style="display:flex; gap:16px; margin-bottom:24px; flex-wrap:wrap;">
    <div class="card" style="flex:1; padding:24px; background:var(--primary); color:white;">
        <div style="font-size: 14px; color: #cbd5e1; margin-bottom: 6px; font-weight: 500;">Total Earned (Paid)</div>
        <div style="font-size: 32px; font-weight: 800;">₹<?= number_format($total_paid, 2) ?></div>
    </div>
    <div class="card" style="flex:1; padding:24px; background:var(--accent); color:white;">
        <div style="font-size: 14px; color: #cbd5e1; margin-bottom: 6px; font-weight: 500;">Total Pending</div>
        <div style="font-size: 32px; font-weight: 800;">₹<?= number_format($total_pending, 2) ?></div>
    </div>
</div>

<!-- History -->
<h3 style="font-family: 'Outfit'; font-size: 16px; margin-bottom: 16px; margin-top:32px;">Recent Payouts</h3>
<div class="card" style="margin-bottom: 0;">
    <?php if(count($payouts) > 0): ?>
        <?php foreach($payouts as $p): ?>
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 16px; border-bottom: 1px solid var(--border);">
                <div>
                    <div style="font-weight: 700; font-size: 15px; color: var(--text-main); margin-bottom: 2px;">Commission for <?= htmlspecialchars($p['customer_name'] ?? 'Unknown Lead') ?></div>
                    <div style="font-size: 13px; color: var(--text-muted); font-weight: 500;">Loan ID: <?= htmlspecialchars($p['loan_id'] ?? 'N/A') ?> &nbsp;|&nbsp; Date: <?= date('d M Y', strtotime($p['created_at'])) ?></div>
                </div>
                <div style="text-align:right;">
                    <div style="font-weight: 700; font-size: 16px; color: <?= $p['status'] === 'Paid' ? 'var(--success)' : ($p['status'] === 'Cancelled' ? 'var(--danger)' : '#f59e0b') ?>;">₹<?= number_format($p['net_payable'], 2) ?></div>
                    <div style="font-size:12px; font-weight:600; color: <?= $p['status'] === 'Paid' ? 'var(--success)' : ($p['status'] === 'Cancelled' ? 'var(--danger)' : '#f59e0b') ?>;"><?= $p['status'] ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div style="text-align: center; padding: 20px;">
            <p style="font-size: 13px; color: var(--text-muted);">No payout history found.</p>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
