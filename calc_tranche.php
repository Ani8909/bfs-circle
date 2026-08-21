<?php
require_once 'config.php';
$page_title = 'Stage-wise Tranche Calculator';
$page_subtitle = 'Calculate loan release amounts based on site progress';
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
            
            <div class="input-group"><label>Sanctioned Loan</label><div class="input-wrapper"><span class="prefix">₹</span><input type="number" id="amt" value="3000000" oninput="calc()"></div></div>
            <div class="input-group"><label>Stage 1 (%)</label><div class="input-wrapper"><input type="number" id="p1" value="30" oninput="calc()"></div></div>
            <div class="input-group"><label>Stage 2 (%)</label><div class="input-wrapper"><input type="number" id="p2" value="40" oninput="calc()"></div></div>
            <div class="input-group"><label>Stage 3 (%)</label><div class="input-wrapper"><input type="number" id="p3" value="30" oninput="calc()"></div></div>
        </div>
        <div class="calc-results">
            <div class="res-label">Tranche 1 Release</div>
            <div class="res-value" id="res-main">₹0</div>
            <div style="height: 220px; width: 100%; position: relative;"><canvas id="chart"></canvas></div>
            <div class="res-sub-grid">
                <div class="res-sub-item"><div class="s-lbl">Tranche 2</div><div class="s-val" id="r-2">₹0</div></div><div class="res-sub-item"><div class="s-lbl">Tranche 3</div><div class="s-val" id="r-3">₹0</div></div>
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
            data: { labels: ['Value 1', 'Value 2', 'Value 3'], datasets: [{ data: [1,1], backgroundColor: ['#3b82f6', '#cbd5e1', '#0f172a'], borderWidth: 0 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { color: 'white', padding: 15, font: {size: 11} } } }, cutout: '70%' }
        });
    }
    function calc() { 
            const a = parseFloat(document.getElementById('amt').value)||0;
            const p1 = parseFloat(document.getElementById('p1').value)||0;
            const p2 = parseFloat(document.getElementById('p2').value)||0;
            const p3 = parseFloat(document.getElementById('p3').value)||0;
            const r1 = a*(p1/100); const r2 = a*(p2/100); const r3 = a*(p3/100);
            document.getElementById('res-main').innerText = '₹'+fmt(r1);
            document.getElementById('r-2').innerText = '₹'+fmt(r2);
            document.getElementById('r-3').innerText = '₹'+fmt(r3);
            if(chart) { chart.data.datasets[0].data = [r1, r2, r3]; chart.update(); }
         }
    document.addEventListener('DOMContentLoaded', () => { initChart(); calc(); });
</script>
<?php require_once 'footer.php'; ?>
