<?php
require_once '../config.php';
$page_title = 'BT & Top-Up Savings';
$page_subtitle = 'Calculate exact savings when transferring a loan';

require_once 'includes/header.php';
$back_url = 'calculators.php';
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
    .res-label { font-size: 13px; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px; color: var(--text-muted); margin-bottom: 8px; text-align: center; }
    .res-value { font-size: 42px; font-weight: 800; color: var(--primary); text-align: center; margin-bottom: 30px; font-family: 'Outfit', sans-serif; }
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
            <h2 style="font-size: 22px; color: var(--text-primary); margin-bottom: 25px;">Parameters</h2>
            
            <div class="input-group"><label>Outstanding Loan</label><div class="input-wrapper"><span class="prefix">₹</span><input type="number" id="amt" value="2500000" oninput="calc()"></div></div>
            <div class="input-group"><label>Old ROI (%)</label><div class="input-wrapper"><input type="number" id="old" value="10.5" oninput="calc()"></div></div>
            <div class="input-group"><label>New ROI (%)</label><div class="input-wrapper"><input type="number" id="new" value="8.5" oninput="calc()"></div></div>
            <div class="input-group"><label>Remaining Tenure (Months)</label><div class="input-wrapper"><input type="number" id="ten" value="120" oninput="calc()"></div></div>
        </div>
        <div class="calc-results">
            <div class="res-label">Total Interest Saved</div>
            <div class="res-value" id="res-main">₹0</div>
            <div class="chart-container" style="height: 220px; width: 100%; position: relative;"><canvas id="chart"></canvas></div>
            <div class="res-sub-grid">
                <div class="res-sub-item"><div class="s-lbl">Old EMI</div><div class="s-val" style="text-decoration:line-through;color:var(--text-muted);" id="r-o">₹0</div></div><div class="res-sub-item"><div class="s-lbl">New EMI</div><div class="s-val" style="color:#059669;" id="r-n">₹0</div></div>
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
            data: { labels: ['Value 1', 'Value 2', 'Value 3'], datasets: [{ data: [1,1], backgroundColor: ['#3b82f6', '#cbd5e1'], borderWidth: 0 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { color: '#64748b', padding: 10, font: {size: 11} } } }, cutout: '70%' }
        });
    }
    function calc() { 
            const p = parseFloat(document.getElementById('amt').value)||0;
            const r1 = parseFloat(document.getElementById('old').value)||0;
            const r2 = parseFloat(document.getElementById('new').value)||0;
            const n = parseFloat(document.getElementById('ten').value)||0;
            const oEmi = (r1===0||n===0)?0: ((r1/1200)*p/(1-Math.pow(1+r1/1200, -n)));
            const nEmi = (r2===0||n===0)?0: ((r2/1200)*p/(1-Math.pow(1+r2/1200, -n)));
            const oInt = (oEmi*n)-p; const nInt = (nEmi*n)-p;
            document.getElementById('res-main').innerText = '₹'+fmt(oInt-nInt);
            document.getElementById('r-o').innerText = '₹'+fmt(oEmi);
            document.getElementById('r-n').innerText = '₹'+fmt(nEmi);
            if(chart) { chart.data.datasets[0].data = [oInt-nInt, nInt]; chart.update(); }
         }
    document.addEventListener('DOMContentLoaded', () => { initChart(); calc(); });
</script>
<?php require_once 'includes/footer.php'; ?>
} else {
    <?php require_once 'includes/footer.php'; ?>
}
?>


