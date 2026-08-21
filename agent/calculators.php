<?php
require_once 'includes/header.php';
?>

<div style="margin-bottom: 20px; text-align: center;">
    <div style="display: inline-block; padding: 12px; background: linear-gradient(135deg, rgba(249, 115, 22, 0.1), rgba(234, 88, 12, 0.2)); border-radius: 50%; margin-bottom: 12px; color: var(--primary);">
        <i data-lucide="calculator" style="width: 32px; height: 32px;"></i>
    </div>
    <h2 style="font-family: 'Outfit'; font-size: 22px; color: var(--text-primary);">Calculators</h2>
    <p style="color: var(--text-muted); font-size: 14px;">Quick financial tools for your clients</p>
</div>

<div style="display: grid; grid-template-columns: 1fr; gap: 16px; margin-bottom: 30px;">
    
    <!-- Calc 1 -->
    <a href="../calc_eligibility.php" class="card" style="text-decoration: none; display: flex; align-items: center; gap: 16px; padding: 20px; transition: transform 0.2s;">
        <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(59, 130, 246, 0.1); color: #3b82f6; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="badge-check" style="width: 24px; height: 24px;"></i>
        </div>
        <div style="flex: 1;">
            <div style="font-weight: 700; font-size: 16px; color: var(--text-primary); margin-bottom: 4px;">Loan Eligibility</div>
            <div style="font-size: 12px; color: var(--text-muted);">Check how much loan a client can get based on their income.</div>
        </div>
        <i data-lucide="chevron-right" style="color: var(--text-light);"></i>
    </a>
    
    <!-- Calc 2 -->
    <a href="../calc_preemi.php" class="card" style="text-decoration: none; display: flex; align-items: center; gap: 16px; padding: 20px; transition: transform 0.2s;">
        <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(16, 185, 129, 0.1); color: #10b981; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="calendar-clock" style="width: 24px; height: 24px;"></i>
        </div>
        <div style="flex: 1;">
            <div style="font-weight: 700; font-size: 16px; color: var(--text-primary); margin-bottom: 4px;">Pre-EMI Calculator</div>
            <div style="font-size: 12px; color: var(--text-muted);">Calculate interest for under-construction property loans.</div>
        </div>
        <i data-lucide="chevron-right" style="color: var(--text-light);"></i>
    </a>
    
    <!-- Calc 3 -->
    <a href="../calc_bt.php" class="card" style="text-decoration: none; display: flex; align-items: center; gap: 16px; padding: 20px; transition: transform 0.2s;">
        <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(249, 115, 22, 0.1); color: #f97316; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="arrow-right-left" style="width: 24px; height: 24px;"></i>
        </div>
        <div style="flex: 1;">
            <div style="font-weight: 700; font-size: 16px; color: var(--text-primary); margin-bottom: 4px;">BT & Top-Up Savings</div>
            <div style="font-size: 12px; color: var(--text-muted);">Calculate exact savings when transferring a loan.</div>
        </div>
        <i data-lucide="chevron-right" style="color: var(--text-light);"></i>
    </a>
    
    <!-- Calc 4 -->
    <a href="../calc_ltv.php" class="card" style="text-decoration: none; display: flex; align-items: center; gap: 16px; padding: 20px; transition: transform 0.2s;">
        <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(139, 92, 246, 0.1); color: #8b5cf6; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="percent" style="width: 24px; height: 24px;"></i>
        </div>
        <div style="flex: 1;">
            <div style="font-weight: 700; font-size: 16px; color: var(--text-primary); margin-bottom: 4px;">LTV Calculator</div>
            <div style="font-size: 12px; color: var(--text-muted);">Calculate Loan to Value ratio for property.</div>
        </div>
        <i data-lucide="chevron-right" style="color: var(--text-light);"></i>
    </a>
    
</div>

<style>
    .card:active {
        transform: scale(0.98);
        background: #f8fafc;
    }
</style>

<?php require_once 'includes/footer.php'; ?>
