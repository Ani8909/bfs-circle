<?php
require_once 'config.php';
$page_title = 'Loan to Value (LTV)';
$page_subtitle = 'Calculate LTV ratio against property valuation';

if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'Agent') {
        require_once 'agent/includes/header.php';
        $back_url = 'agent/tools.php';
    } elseif ($_SESSION['role'] === 'Partner') {
        require_once 'partner/includes/header.php';
        $back_url = 'partner/tools.php';
    } elseif ($_SESSION['role'] === 'CA') {
        require_once 'ca/includes/header.php';
        $back_url = 'ca/calculators.php';
    } else {
        require_once 'header.php';
        $back_url = 'calculators.php';
    }
} else {
    require_once 'header.php';
    $back_url = 'calculators.php';
}
?>

<style>
    .calc-layout {
        display: flex;
        gap: 30px;
        align-items: flex-start;
    }
    @media (max-width: 768px) {
        .calc-layout {
            flex-direction: column-reverse;
            gap: 16px;
        }
        .calc-inputs, .calc-results {
            width: 100%;
        }
        .calc-results {
            padding: 16px;
        }
        .res-value {
            font-size: 28px;
            margin-bottom: 15px;
        }
        .chart-container {
            height: 180px !important;
        }
        .res-sub-grid {
            margin-top: 15px !important;
            padding-top: 15px !important;
            gap: 10px !important;
        }
    }
    .calc-inputs { flex: 1; background: white; border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); padding: 30px; border: 1px solid #e2e8f0; }
    .calc-results { width: 400px; background: white; border-radius: var(--radius-lg); padding: 30px; color: var(--text-primary); position: sticky; top: 24px; box-shadow: 0 10px 40px rgba(0,0,0,0.06); border: 1px solid #e2e8f0; }
    
    .input-group { margin-bottom: 25px; }
    .input-group label { display: block; font-weight: 700; margin-bottom: 10px; color: var(--text-primary); font-size: 15px; }
    .input-wrapper { display: flex; align-items: center; background: white; border: 1px solid #cbd5e1; border-radius: 8px; overflow: hidden; transition: border-color 0.2s; }
    .input-wrapper:focus-within { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(15, 23, 42, 0.15); }
    .prefix, .suffix { background: #f8fafc; padding: 12px 16px; color: var(--text-muted); font-weight: 700; font-size: 15px; border-right: 1px solid #cbd5e1; }
    .suffix { border-right: none; border-left: 1px solid #cbd5e1; }
    .input-wrapper input { flex: 1; border: none; padding: 12px 16px; outline: none; font-size: 16px; font-weight: 600; color: var(--text-primary); }
    
    input[type=range] { -webkit-appearance: none; width: 100%; margin-top: 15px; background: transparent; }
    input[type=range]::-webkit-slider-thumb { -webkit-appearance: none; height: 20px; width: 20px; border-radius: 50%; background: var(--primary); cursor: pointer; margin-top: -8px; box-shadow: 0 0 0 4px rgba(15, 23, 42, 0.15); }
    input[type=range]::-webkit-slider-runnable-track { width: 100%; height: 6px; cursor: pointer; background: #e2e8f0; border-radius: 3px; }
    
    .res-label { font-size: 13px; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px; color: var(--text-muted); margin-bottom: 8px; text-align: center; }
    .res-value { font-size: 42px; font-weight: 800; color: #059669; text-align: center; margin-bottom: 30px; font-family: 'Outfit', sans-serif; }
    .res-sub-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; border-top: 1px dashed #e2e8f0; padding-top: 20px; margin-top: 20px; }
    .res-sub-item { text-align: center; }
    .res-sub-item .s-lbl { font-size: 12px; color: var(--text-muted); font-weight: 600; margin-bottom: 5px; }
    .res-sub-item .s-val { font-size: 20px; font-weight: 800; color: var(--text-primary); font-family: 'Outfit', sans-serif; }
</style>

<div class="view-container">
    <a href="<?php echo $back_url; ?>" class="btn btn-secondary" style="margin-bottom: 20px; display: inline-flex; align-items: center; gap: 8px;">
        <i data-lucide="arrow-left" style="width:16px;"></i> Back to Calculators
    </a>
    
    <div class="calc-layout">
        <div class="calc-inputs">
            <h2 style="font-size: 22px; color: var(--text-primary); margin-bottom: 25px;">Valuation Parameters</h2>
            <div class="input-group">
                <label>Property Market Value (₹)</label>
                <div class="input-wrapper">
                    <span class="prefix">₹</span>
                    <input type="number" id="prop" value="5000000" oninput="calculate()">
                </div>
                <input type="range" min="500000" max="50000000" step="100000" value="5000000" oninput="document.getElementById('prop').value = this.value; calculate()">
            </div>
            <div class="input-group">
                <label>Bank Allowed LTV Ratio (%)</label>
                <div class="input-wrapper">
                    <input type="number" id="ltv" value="80" step="1" oninput="calculate()">
                    <span class="suffix">%</span>
                </div>
                <input type="range" min="10" max="100" step="5" value="80" oninput="document.getElementById('ltv').value = this.value; calculate()">
            </div>
        </div>
        
        <div class="calc-results">
            <div class="res-label">Maximum Loan Amount</div>
            <div class="res-value" id="res-loan">₹0</div>
            
            <div class="chart-container" style="height: 220px; width: 100%; position: relative;"><canvas id="chart"></canvas></div>
            
            <div class="res-sub-grid">
                <div class="res-sub-item"><div class="s-lbl">Downpayment Margin</div><div class="s-val" id="res-margin">₹0</div></div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    function fmt(num) { return new Intl.NumberFormat('en-IN').format(Math.round(num)); }
    
    let chart;
    function initChart() {
        const ctx = document.getElementById('chart').getContext('2d');
        chart = new Chart(ctx, {
            type: 'doughnut',
            data: { labels: ['Max Loan', 'Downpayment Margin'], datasets: [{ data: [1,1], backgroundColor: ['#3b82f6', '#cbd5e1'], borderWidth: 0 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { color: '#64748b', padding: 10, font: {size: 11} } } }, cutout: '70%' }
        });
    }

    function calculate() {
        const val = parseFloat(document.getElementById('prop').value) || 0;
        const pct = parseFloat(document.getElementById('ltv').value) || 0;
        
        const loan = val * (pct/100);
        const margin = val - loan;
        
        document.getElementById('res-loan').innerText = '₹' + fmt(loan);
        document.getElementById('res-margin').innerText = '₹' + fmt(margin);
        
        if(chart && val > 0) { chart.data.datasets[0].data = [loan, margin]; chart.update(); }
    }

    document.addEventListener('DOMContentLoaded', () => { initChart(); calculate(); });
</script>
<?php 
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'Agent') {
        require_once 'agent/includes/footer.php';
    } elseif ($_SESSION['role'] === 'Partner') {
        require_once 'partner/includes/footer.php';
    } elseif ($_SESSION['role'] === 'CA') {
        require_once 'ca/includes/footer.php';
    } else {
        require_once 'footer.php';
    }
} else {
    require_once 'footer.php';
}
?>

