import sys

with open('client_vault/index.php', 'r', encoding='utf-8') as f:
    content = f.read()

# Replace CSS
old_css = '''.metric-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 24px; }
    .metric-box { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #fff; padding: 24px; border-radius: 16px; display: flex; align-items: center; gap: 20px; box-shadow: 0 10px 25px rgba(15,23,42,0.15); transition: transform 0.2s; }
    .metric-box:hover { transform: translateY(-3px); }
    .metric-icon { background: rgba(255,255,255,0.1); width: 56px; height: 56px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 24px; color: #38bdf8; }
    .metric-val { font-size: 28px; font-weight: 800; margin-bottom: 4px; letter-spacing: -0.5px; }
    .metric-lbl { font-size: 13px; color: #94a3b8; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }'''

new_css = '''.metric-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 24px; }
    .metric-box { background: var(--bg-card); border: 1px solid var(--border); padding: 24px; border-radius: var(--radius-lg); display: flex; align-items: center; gap: 20px; box-shadow: var(--shadow-sm); transition: transform 0.2s, box-shadow 0.2s; }
    .metric-box:hover { transform: translateY(-3px); box-shadow: var(--shadow-md); }
    .metric-icon { width: 56px; height: 56px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 24px; }
    .metric-val { font-size: 28px; font-weight: 800; margin-bottom: 4px; letter-spacing: -0.5px; color: var(--text-primary); }
    .metric-lbl { font-size: 13px; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }'''

# Replace HTML
old_html = '''<div class="metric-grid">
        <div class="metric-box">
            <div class="metric-icon"><i data-lucide="shield"></i></div>
            <div>
                <div class="metric-val"><?= number_format($metrics['total_clients'] ?? 0) ?></div>
                <div class="metric-lbl">Total Vault Clients</div>
            </div>
        </div>
        <div class="metric-box">
            <div class="metric-icon" style="color: #34d399; background: rgba(52,211,153,0.1);"><i data-lucide="landmark"></i></div>
            <div>
                <div class="metric-val">&#8377;<?= number_format($metrics['total_volume'] ?? 0, 0) ?></div>
                <div class="metric-lbl">Total Disbursed Volume</div>
            </div>
        </div>
        <div class="metric-box" style="background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);">
            <div class="metric-icon" style="color: #fff; background: rgba(255,255,255,0.2);"><i data-lucide="trending-up"></i></div>
            <div>
                <div class="metric-val">High</div>
                <div class="metric-lbl">Cross-Sell Potential</div>
            </div>
        </div>
    </div>'''

new_html = '''<div class="metric-grid">
        <div class="metric-box">
            <div class="metric-icon" style="color: #3b82f6; background: rgba(59,130,246,0.1);"><i data-lucide="shield"></i></div>
            <div>
                <div class="metric-val"><?= number_format($metrics['total_clients'] ?? 0) ?></div>
                <div class="metric-lbl">Total Vault Clients</div>
            </div>
        </div>
        <div class="metric-box">
            <div class="metric-icon" style="color: #10b981; background: rgba(16,185,129,0.1);"><i data-lucide="landmark"></i></div>
            <div>
                <div class="metric-val">&#8377;<?= number_format($metrics['total_volume'] ?? 0, 0) ?></div>
                <div class="metric-lbl">Total Disbursed Volume</div>
            </div>
        </div>
        <div class="metric-box">
            <div class="metric-icon" style="color: var(--primary); background: var(--primary-light);"><i data-lucide="trending-up"></i></div>
            <div>
                <div class="metric-val">High</div>
                <div class="metric-lbl">Cross-Sell Potential</div>
            </div>
        </div>
    </div>'''

content = content.replace(old_css, new_css).replace(old_html, new_html)

with open('client_vault/index.php', 'w', encoding='utf-8') as f:
    f.write(content)

print('Success')
