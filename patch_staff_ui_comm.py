import os

idx_path = r'c:\Users\pc\Downloads\client mgmt2\staff\index.php'
with open(idx_path, 'r', encoding='utf-8') as f:
    idx = f.read()

# 1. Update HTML structure for Commission
target_html = """        <div class="metric-card full-width">
            <div>
                <div class="metric-label">Estimated Commission</div>
                <div class="metric-value" id="total_commission">₹0</div>
            </div>
            <div class="metric-icon"><i class="fas fa-wallet"></i></div>
        </div>"""

repl_html = """        <div class="metric-card full-width" style="position:relative; overflow:hidden;">
            <div>
                <div class="metric-label" style="display:flex; align-items:center; gap:8px;">
                    Estimated Commission
                    <span id="commission_rate_badge" style="background:rgba(74, 222, 128, 0.2); color:#4ade80; padding:2px 6px; border-radius:4px; font-size:10px; font-weight:700; border:1px solid rgba(74, 222, 128, 0.4);">Rate: --%</span>
                </div>
                <div class="metric-value" id="total_commission">₹0</div>
            </div>
            <div class="metric-icon" style="z-index:2;"><i class="fas fa-wallet"></i></div>
            
            <!-- Background design element -->
            <i class="fas fa-coins" style="position:absolute; right:-10px; bottom:-10px; font-size:80px; opacity:0.05; z-index:1; transform:rotate(-15deg);"></i>
        </div>"""

idx = idx.replace(target_html, repl_html)

# 2. Update JS to set the rate
target_js = """                // Format currency
                const commission = data.total_commission || 0;
                document.getElementById('total_commission').innerText = '₹' + commission.toLocaleString('en-IN');"""

repl_js = """                // Format currency
                const commission = data.total_commission || 0;
                document.getElementById('total_commission').innerText = '₹' + parseFloat(commission).toLocaleString('en-IN', {maximumFractionDigits: 2});
                
                // Set commission rate badge
                const rate = data.commission_rate || 0;
                document.getElementById('commission_rate_badge').innerText = `Rate: ${rate}%`;
                
                // If they haven't earned anything yet but have a rate, we could show a tooltip, but it's self-explanatory now."""

idx = idx.replace(target_js, repl_js)

with open(idx_path, 'w', encoding='utf-8') as f:
    f.write(idx)
print("Staff Dashboard updated with Commission Rate & real payout display")
