import re

file_path = r'c:\Users\pc\Downloads\client mgmt2\dashboard.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# ===========================================================
# 1. REPLACE KPI CSS — no icons, pure metric tile design
# ===========================================================
old_kpi_css = """.kpi-strip { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px; }
@media (max-width: 1200px) { .kpi-strip { grid-template-columns: repeat(2, 1fr); } }

/* Navy Dark KPI Card */
.kpi { background: var(--navy); border-radius: 16px; padding: 20px 22px; display: flex; align-items: center; gap: 16px; cursor: pointer; transition: all 0.25s ease; box-shadow: 0 4px 20px rgba(15,23,42,0.2); position: relative; overflow: hidden; }
.kpi::after { content: ''; position: absolute; top: -30px; right: -30px; width: 120px; height: 120px; background: radial-gradient(circle, rgba(249,115,22,0.1) 0%, transparent 70%); border-radius: 50%; pointer-events: none; }
.kpi:hover { transform: translateY(-4px); box-shadow: 0 14px 40px rgba(15,23,42,0.35); }
.kpi-icon { width: 50px; height: 50px; border-radius: 14px; background: rgba(249,115,22,0.15); border: 1px solid rgba(249,115,22,0.25); display: flex; align-items: center; justify-content: center; flex-shrink: 0; color: var(--orange); }
.kpi-icon svg { width: 22px; height: 22px; }
.kpi-val { font-size: 26px; font-weight: 900; color: #fff; line-height: 1; margin-bottom: 4px; }
.kpi-lbl { font-size: 10px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.6px; }
.kpi-sub { font-size: 11px; color: rgba(249,115,22,0.85); margin-top: 5px; font-weight: 600; }

/* Light White KPI (alternating) */
.kpi.light { background: #fff; box-shadow: 0 2px 12px rgba(0,0,0,0.04); border: 1px solid var(--border); }
.kpi.light::after { background: radial-gradient(circle, rgba(249,115,22,0.05) 0%, transparent 70%); }
.kpi.light .kpi-val { color: var(--navy); }
.kpi.light .kpi-lbl { color: var(--muted); }
.kpi.light .kpi-sub { color: var(--orange); }
.kpi.light:hover { box-shadow: 0 10px 30px rgba(249,115,22,0.12); border-color: rgba(249,115,22,0.3); }

/* Orange Accent KPI */
.kpi.accent { background: linear-gradient(135deg, var(--orange), var(--orange-deep)); box-shadow: 0 4px 20px rgba(249,115,22,0.35); }
.kpi.accent::after { background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%); }
.kpi.accent .kpi-icon { background: rgba(255,255,255,0.2); border-color: rgba(255,255,255,0.3); color: #fff; }
.kpi.accent .kpi-val { color: #fff; }
.kpi.accent .kpi-lbl { color: rgba(255,255,255,0.8); }
.kpi.accent .kpi-sub { color: rgba(255,255,255,0.9); }
.kpi.accent:hover { box-shadow: 0 14px 40px rgba(249,115,22,0.5); }"""

new_kpi_css = """.kpi-strip { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px; }
@media (max-width: 1200px) { .kpi-strip { grid-template-columns: repeat(2, 1fr); } }

/* ===== PURE METRIC TILE — No Icon, Pure Data ===== */
.kpi { background: var(--navy); border-radius: 16px; padding: 22px 24px 18px; cursor: pointer; transition: all 0.3s cubic-bezier(.4,0,.2,1); box-shadow: 0 4px 20px rgba(15,23,42,0.25); position: relative; overflow: hidden; display: flex; flex-direction: column; justify-content: space-between; min-height: 110px; }
.kpi::before { content: ''; position: absolute; bottom: 0; right: 0; width: 80px; height: 80px; background: radial-gradient(circle, rgba(249,115,22,0.14) 0%, transparent 70%); border-radius: 50%; }
.kpi::after  { content: ''; position: absolute; top: 0; left: 0; width: 3px; height: 100%; background: var(--orange); border-radius: 3px 0 0 3px; }
.kpi:hover { transform: translateY(-5px); box-shadow: 0 18px 45px rgba(15,23,42,0.4); }
.kpi-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px; }
.kpi-val { font-size: 32px; font-weight: 900; color: #fff; line-height: 1; letter-spacing: -1px; }
.kpi-arrow { font-size: 18px; color: var(--orange); opacity: 0.7; }
.kpi-lbl { font-size: 10px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 5px; }
.kpi-sub { font-size: 11px; color: rgba(249,115,22,0.9); font-weight: 700; }

/* Light White variant */
.kpi.light { background: #fff; box-shadow: 0 2px 12px rgba(0,0,0,0.04); border: 1px solid var(--border); }
.kpi.light::after { background: var(--orange); }
.kpi.light .kpi-val { color: var(--navy); }
.kpi.light .kpi-lbl { color: var(--muted); }
.kpi.light .kpi-sub { color: var(--orange); }
.kpi.light:hover { box-shadow: 0 12px 32px rgba(249,115,22,0.15); border-color: rgba(249,115,22,0.4); }

/* Orange Accent variant */
.kpi.accent { background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); box-shadow: 0 6px 24px rgba(249,115,22,0.4); }
.kpi.accent::before { background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%); }
.kpi.accent::after { background: rgba(255,255,255,0.3); }
.kpi.accent .kpi-val { color: #fff; }
.kpi.accent .kpi-lbl { color: rgba(255,255,255,0.85); }
.kpi.accent .kpi-sub { color: rgba(255,255,255,0.95); }
.kpi.accent .kpi-arrow { color: rgba(255,255,255,0.7); }
.kpi.accent:hover { box-shadow: 0 18px 45px rgba(249,115,22,0.55); }"""

content = content.replace(old_kpi_css, new_kpi_css)

# ===========================================================
# 2. REMOVE ICONS from KPI card JS template
# ===========================================================
old_kpi_js = """    document.getElementById('kpiStrip').innerHTML = kpis.map((k, i) => `
        <div class="kpi ${kpiStyles[i]}" onclick="window.location.href='${k.link}'" style="cursor:pointer;">
            <div class="kpi-icon"><i data-lucide="${k.icon}"></i></div>
            <div style="min-width:0;">
                <div class="kpi-val">${k.val}</div>
                <div class="kpi-lbl">${k.lbl}</div>
                <div class="kpi-sub">${k.sub}</div>
            </div>
        </div>
    `).join('');
    lucide.createIcons();"""

new_kpi_js = """    document.getElementById('kpiStrip').innerHTML = kpis.map((k, i) => `
        <div class="kpi ${kpiStyles[i]}" onclick="window.location.href='${k.link}'" style="cursor:pointer;">
            <div>
                <div class="kpi-lbl">${k.lbl}</div>
                <div class="kpi-top">
                    <div class="kpi-val">${k.val}</div>
                    <div class="kpi-arrow">↗</div>
                </div>
                <div class="kpi-sub">${k.sub}</div>
            </div>
        </div>
    `).join('');"""

content = content.replace(old_kpi_js, new_kpi_js)

# ===========================================================
# 3. CLEAN UP SKELETON — remove icon skeleton
# ===========================================================
old_skel = """    <div class="kpi">
        <div class="kpi-icon sk" style="width:46px;height:46px;border-radius:12px;"></div>
        <div><div class="sk" style="width:60px;height:24px;margin-bottom:6px;border-radius:4px;"></div><div class="sk" style="width:90px;height:12px;border-radius:4px;"></div></div>
    </div>"""

new_skel = """    <div class="kpi">
        <div><div class="sk" style="width:70px;height:10px;margin-bottom:10px;border-radius:4px;background:rgba(255,255,255,0.1);"></div><div class="sk" style="width:50px;height:30px;margin-bottom:8px;border-radius:4px;background:rgba(255,255,255,0.1);"></div><div class="sk" style="width:100px;height:10px;border-radius:4px;background:rgba(255,255,255,0.1);"></div></div>
    </div>"""

content = content.replace(old_skel, new_skel)

# ===========================================================
# 4. UPGRADE dc-hdr — replace lucide i tags with clean pill icons
# ===========================================================
# Remove the icon colour overrides from dc-title — clean text-only titles
content = content.replace('<i data-lucide="inbox" style="color:var(--orange);"></i>', '<span style="background:var(--orange);color:#fff;border-radius:6px;padding:2px 8px;font-size:11px;font-weight:800;">LEADS</span>')
content = content.replace('<i data-lucide="git-merge" style="color:var(--orange);"></i>', '<span style="background:var(--navy);color:#fff;border-radius:6px;padding:2px 8px;font-size:11px;font-weight:800;">PIPELINE</span>')
content = content.replace('<i data-lucide="trending-up" style="color:var(--orange);"></i>', '<span style="background:var(--green);color:#fff;border-radius:6px;padding:2px 8px;font-size:11px;font-weight:800;">GROWTH</span>')
content = content.replace('<i data-lucide="pie-chart" style="color:var(--orange);"></i>', '<span style="background:var(--orange);color:#fff;border-radius:6px;padding:2px 8px;font-size:11px;font-weight:800;">MIX</span>')
content = content.replace('<i data-lucide="building" style="color:var(--orange);"></i>', '<span style="background:var(--navy);color:#fff;border-radius:6px;padding:2px 8px;font-size:11px;font-weight:800;">BANKS</span>')
content = content.replace('<i data-lucide="map-pin" style="color:var(--orange);"></i>', '<span style="background:#14b8a6;color:#fff;border-radius:6px;padding:2px 8px;font-size:11px;font-weight:800;">VISITS</span>')
content = content.replace('<i data-lucide="shield" style="color:var(--orange);"></i>', '<span style="background:var(--green);color:#fff;border-radius:6px;padding:2px 8px;font-size:11px;font-weight:800;">VAULT</span>')
content = content.replace('<i data-lucide="users" style="color:var(--orange);"></i>', '<span style="background:#8b5cf6;color:#fff;border-radius:6px;padding:2px 8px;font-size:11px;font-weight:800;">REFS</span>')
content = content.replace('<i data-lucide="indian-rupee" style="color:var(--orange);"></i>', '<span style="background:var(--orange);color:#fff;border-radius:6px;padding:2px 8px;font-size:11px;font-weight:800;">PAY</span>')
content = content.replace('<i data-lucide="bar-chart-2" style="color:var(--orange);"></i>', '<span style="background:var(--navy);color:#fff;border-radius:6px;padding:2px 8px;font-size:11px;font-weight:800;">STAFF</span>')
content = content.replace('<i data-lucide="mail" style="color:var(--orange);"></i>', '<span style="background:#3b82f6;color:#fff;border-radius:6px;padding:2px 8px;font-size:11px;font-weight:800;">EMAIL</span>')
content = content.replace('<i data-lucide="bell" style="color:var(--orange);"></i>', '<span style="background:#ef4444;color:#fff;border-radius:6px;padding:2px 8px;font-size:11px;font-weight:800;">ALERTS</span>')
content = content.replace('<i data-lucide="activity" style="color:var(--orange);"></i>', '<span style="background:var(--orange);color:#fff;border-radius:6px;padding:2px 8px;font-size:11px;font-weight:800;">LIVE</span>')

# Hero icon - also clean
content = content.replace('<i data-lucide="layout-dashboard"', '<i data-lucide="grid"')

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Icons removed, KPIs upgraded to pure metric tiles")
