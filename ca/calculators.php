<?php
require_once '../config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'CA') {
    header("Location: ../login.php");
    exit;
}

$page_title = 'Financial Calculators - CA Portal';
$active_page = 'calculators'; // Let's add this state if needed
require_once 'includes/header.php';
?>

<style>
    .page-header { display: flex; align-items: center; gap: 12px; margin-bottom: 24px; font-size: 20px; font-weight: 800; color: var(--primary); }
    
    .calc-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; }
    
    .calc-card {
        background: var(--surface); border-radius: 16px; padding: 24px;
        border: 1px solid var(--border); display: flex; flex-direction: column; align-items: center;
        text-align: center; text-decoration: none; color: inherit; transition: all 0.2s;
        box-shadow: var(--shadow);
    }
    
    .calc-card:hover { transform: translateY(-4px); border-color: var(--primary); box-shadow: var(--shadow-lg); }
    
    .calc-icon {
        width: 56px; height: 56px; border-radius: 16px; background: var(--bg); color: var(--primary);
        display: flex; align-items: center; justify-content: center; margin-bottom: 16px;
    }
    
    .calc-card:hover .calc-icon { background: var(--primary); color: white; }
    
    .calc-title { font-size: 15px; font-weight: 800; color: var(--text-main); margin-bottom: 6px; }
    .calc-desc { font-size: 12px; font-weight: 500; color: var(--text-muted); line-height: 1.4; }

    @media (max-width: 480px) {
        .calc-grid { gap: 12px; }
        .calc-card { padding: 16px; }
        .calc-icon { width: 48px; height: 48px; margin-bottom: 12px; }
        .calc-title { font-size: 14px; }
    }
</style>

<div class="page-header">
    <i data-lucide="calculator" style="color:var(--primary); width:28px; height:28px;"></i>
    Loan Calculators
</div>

<div class="calc-grid">
    <a href="calc_eligibility.php" class="calc-card ripple">
        <div class="calc-icon"><i data-lucide="check-square" style="width:24px; height:24px;"></i></div>
        <div class="calc-title">Eligibility</div>
        <div class="calc-desc">Check loan eligibility amount instantly</div>
    </a>
    
    <a href="calc_preemi.php" class="calc-card ripple">
        <div class="calc-icon"><i data-lucide="pie-chart" style="width:24px; height:24px;"></i></div>
        <div class="calc-title">Pre-EMI</div>
        <div class="calc-desc">Calculate under-construction property EMI</div>
    </a>
    
    <a href="calc_ltv.php" class="calc-card ripple">
        <div class="calc-icon"><i data-lucide="percent" style="width:24px; height:24px;"></i></div>
        <div class="calc-title">LTV Ratio</div>
        <div class="calc-desc">Calculate Loan to Value ratio accurately</div>
    </a>
    
    <a href="calc_bt.php" class="calc-card ripple">
        <div class="calc-icon"><i data-lucide="arrow-left-right" style="width:24px; height:24px;"></i></div>
        <div class="calc-title">Balance Transfer</div>
        <div class="calc-desc">Check savings on BT & Top-Up loans</div>
    </a>
    
    <a href="calc_business.php" class="calc-card ripple">
        <div class="calc-icon"><i data-lucide="briefcase" style="width:24px; height:24px;"></i></div>
        <div class="calc-title">Business Loan</div>
        <div class="calc-desc">Eligibility based on turnover & profit</div>
    </a>
    
    <a href="calc_gold.php" class="calc-card ripple">
        <div class="calc-icon"><i data-lucide="coins" style="width:24px; height:24px;"></i></div>
        <div class="calc-title">Gold Loan</div>
        <div class="calc-desc">Calculate eligibility per gram rate</div>
    </a>
</div>

<?php require_once 'includes/footer.php'; ?>
