<?php
require_once 'includes/header.php';
?>

<div style="margin-bottom: 24px;">
    <h2 style="font-family: 'Outfit'; font-size: 24px; color: var(--text-primary);">Advisory Tools</h2>
    <p style="color: var(--text-muted); font-size: 14px;">Financial calculators for your clients</p>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
    
    <!-- Eligibility -->
    <a href="../calc_eligibility.php" class="card" style="text-decoration: none; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 24px 16px; text-align: center; border: 2px solid transparent; transition: border 0.2s;">
        <div style="width: 48px; height: 48px; background: rgba(15, 23, 42, 0.05); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 12px; color: var(--primary);">
            <i data-lucide="check-square" style="width: 24px; height: 24px;"></i>
        </div>
        <div style="font-family: 'Outfit'; font-size: 16px; font-weight: 700; color: var(--text-primary);">Eligibility</div>
        <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">Check loan limit</div>
    </a>
    
    <!-- Pre-EMI -->
    <a href="../calc_preemi.php" class="card" style="text-decoration: none; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 24px 16px; text-align: center;">
        <div style="width: 48px; height: 48px; background: rgba(234, 179, 8, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 12px; color: #a16207;">
            <i data-lucide="calculator" style="width: 24px; height: 24px;"></i>
        </div>
        <div style="font-family: 'Outfit'; font-size: 16px; font-weight: 700; color: var(--text-primary);">Pre-EMI</div>
        <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">Construction EMI</div>
    </a>
    
    <!-- Balance Transfer -->
    <a href="../calc_bt.php" class="card" style="text-decoration: none; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 24px 16px; text-align: center;">
        <div style="width: 48px; height: 48px; background: rgba(15, 23, 42, 0.05); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 12px; color: var(--primary);">
            <i data-lucide="arrow-right-left" style="width: 24px; height: 24px;"></i>
        </div>
        <div style="font-family: 'Outfit'; font-size: 16px; font-weight: 700; color: var(--text-primary);">BT Saving</div>
        <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">Balance Transfer</div>
    </a>
    
    <!-- LTV -->
    <a href="../calc_ltv.php" class="card" style="text-decoration: none; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 24px 16px; text-align: center;">
        <div style="width: 48px; height: 48px; background: rgba(234, 179, 8, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 12px; color: #a16207;">
            <i data-lucide="percent" style="width: 24px; height: 24px;"></i>
        </div>
        <div style="font-family: 'Outfit'; font-size: 16px; font-weight: 700; color: var(--text-primary);">LTV</div>
        <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">Loan to Value</div>
    </a>
    
</div>

<?php require_once 'includes/footer.php'; ?>
