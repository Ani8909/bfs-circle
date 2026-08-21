<?php
require_once 'config.php';
$page_title = 'Late Payment Penalty';
$page_subtitle = 'Calculate bounce charges and penal interest';
require_once 'header.php';
?>
<style>
    .calc-layout { display: flex; gap: 30px; align-items: flex-start; }
    .calc-inputs { flex: 1; background: white; border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); padding: 30px; border: 1px solid #e2e8f0; }
    .calc-results { width: 400px; background: linear-gradient(135deg, #1e293b, #0f172a); border-radius: var(--radius-lg); padding: 30px; color: white; position: sticky; top: 24px; box-shadow: 0 15px 35px rgba(0,0,0,0.1); }
    .input-group { margin-bottom: 25px; }
    .input-group label { display: block; font-weight: 700; margin-bottom: 10px; color: var(--text-primary); font-size: 15px; }
    .input-wrapper { display: flex; align-items: center; background: white; border: 1px solid #cbd5e1; border-radius: 8px; overflow: hidden; transition: border-color 0.2s; }
    .input-wrapper:focus-within { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(15, 23, 42, 0.15); }
    .prefix, .suffix { background: #f8fafc; padding: 12px 16px; color: var(--text-muted); font-weight: 700; font-size: 15px; border-right: 1px solid #cbd5e1; }
    .suffix { border-right: none; border-left: 1px solid #cbd5e1; }
    .input-wrapper input { flex: 1; border: none; padding: 12px 16px; outline: none; font-size: 16px; font-weight: 600; color: var(--text-primary); }
    .res-label { font-size: 14px; text-transform: uppercase; letter-spacing: 1px; color: rgba(255,255,255,0.7); margin-bottom: 8px; text-align: center; }
    .res-value { font-size: 42px; font-weight: 800; color: white; text-align: center; margin-bottom: 30px; }
    .res-sub-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 20px; margin-top: 20px; }
    .res-sub-item { text-align: center; }
    .res-sub-item .s-lbl { font-size: 13px; color: rgba(255,255,255,0.7); margin-bottom: 5px; }
    .res-sub-item .s-val { font-size: 20px; font-weight: 700; color: white; }
</style>
<div class="view-container">
    <a href="calculators.php" class="btn btn-secondary" style="margin-bottom: 20px; display: inline-flex; align-items: center; gap: 8px;">
        <i data-lucide="arrow-left" style="width:16px;"></i> Back to Calculators
    </a>
    <div class="calc-layout">
        <div class="calc-inputs">
            <h2 style="font-size: 22px; color: var(--text-primary); margin-bottom: 25px;">Parameters</h2>
            
            <div class="input-group"><label>EMI Amount</label><div class="input-wrapper"><span class="prefix">₹</span><input type="number" id="e" value="25000" oninput="calc()"></div></div>
            <div class="input-group"><label>Overdue Days</label><div class="input-wrapper"><input type="number" id="d" value="45" oninput="calc()"></div></div>
            <div class="input-group"><label>Penal ROI (% per month)</label><div class="input-wrapper"><input type="number" id="r" value="2.0" oninput="calc()"></div></div>
            <div class="input-group"><label>Bounce Charges</label><div class="input-wrapper"><span class="prefix">₹</span><input type="number" id="b" value="500" oninput="calc()"></div></div>
        </div>
        <div class="calc-results">
            <div class="res-label">Total Payable (EMI + Penalty)</div>
            <div class="res-value" id="res-main">₹0</div>
            <div style="height: 220px; width: 100%; position: relative;"><canvas id="chart"></canvas></div>
            <div class="res-sub-grid">
                <div class="res-sub-item"><div class="s-lbl">Penal Interest</div><div class="s-val" id="r-int">₹0</div></div>
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
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { color: 'white', padding: 15, font: {size: 11} } } }, cutout: '70%' }
        });
    }
    function calc() { 
            const e = parseFloat(document.getElementById('e').value)||0;
            const d = parseFloat(document.getElementById('d').value)||0;
            const r = parseFloat(document.getElementById('r').value)||0;
            const b = parseFloat(document.getElementById('b').value)||0;
            const i = e*(r/100)*(d/30); const t = e+i+b;
            document.getElementById('res-main').innerText = '₹'+fmt(t);
            document.getElementById('r-int').innerText = '₹'+fmt(i);
            if(chart) { chart.data.datasets[0].data = [e, i+b]; chart.update(); }
         }
    document.addEventListener('DOMContentLoaded', () => { initChart(); calc(); });
</script>
<?php require_once 'footer.php'; ?>
