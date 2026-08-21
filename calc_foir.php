<?php
require_once 'config.php';
$page_title = 'FOIR / DTI Checker';
$page_subtitle = 'Check Fixed Obligation to Income Ratio limits';
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
            
            <div class="input-group">
                <label>Gross Monthly Income (₹)</label>
                <div class="input-wrapper"><span class="prefix">₹</span><input type="number" id="inc" value="100000" oninput="calc()"></div>
            </div>
            <div class="input-group">
                <label>Total Existing EMIs (₹)</label>
                <div class="input-wrapper"><span class="prefix">₹</span><input type="number" id="exist" value="25000" oninput="calc()"></div>
            </div>
            <div class="input-group">
                <label>Proposed New EMI (₹)</label>
                <div class="input-wrapper"><span class="prefix">₹</span><input type="number" id="new" value="15000" oninput="calc()"></div>
            </div>
        </div>
        <div class="calc-results">
            <div class="res-label">Total FOIR Percentage</div>
            <div class="res-value" id="res-main">₹0</div>
            <div style="height: 220px; width: 100%; position: relative;"><canvas id="chart"></canvas></div>
            <div class="res-sub-grid">
                <div class="res-sub-item"><div class="s-lbl">Total Obligations</div><div class="s-val" id="r-obs">₹0</div></div><div class="res-sub-item"><div class="s-lbl">Status</div><div class="s-val" id="r-stat">Safe</div></div>
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
            const inc = parseFloat(document.getElementById('inc').value)||0;
            const ex = parseFloat(document.getElementById('exist').value)||0;
            const nw = parseFloat(document.getElementById('new').value)||0;
            const tot = ex + nw;
            const pct = inc > 0 ? (tot/inc)*100 : 0;
            document.getElementById('res-main').innerText = Math.round(pct) + '%';
            document.getElementById('r-obs').innerText = '₹' + fmt(tot);
            document.getElementById('r-stat').innerText = pct <= 50 ? 'Safe' : (pct <= 65 ? 'Moderate' : 'High Risk');
            if(chart) { chart.data.datasets[0].data = [tot, inc-tot>0?inc-tot:0]; chart.update(); }
         }
    document.addEventListener('DOMContentLoaded', () => { initChart(); calc(); });
</script>
<?php require_once 'footer.php'; ?>
