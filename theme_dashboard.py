import re

file_path = r'c:\Users\pc\Downloads\client mgmt2\dashboard.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Add Hero Banner after <div class="db-wrap">
hero_html = """
<!-- ============ HERO BANNER ============ -->
<div class="db-hero">
    <div class="db-hero-left" style="position:relative;z-index:1;">
        <h2>BFS Circle — Control Tower</h2>
        <p>Real-time analytics across all business sections. Last refreshed: <span id="lastRefresh">just now</span></p>
    </div>
    <div class="db-hero-right">
        <div class="hero-stat" id="hero-apps"><div class="hero-stat-val">—</div><div class="hero-stat-lbl">Active Loans</div></div>
        <div class="hero-divider"></div>
        <div class="hero-stat" id="hero-vault"><div class="hero-stat-val">—</div><div class="hero-stat-lbl">Vault Clients</div></div>
        <div class="hero-divider"></div>
        <div class="hero-stat" id="hero-staff"><div class="hero-stat-val">—</div><div class="hero-stat-lbl">Staff Online</div></div>
    </div>
</div>

"""
content = content.replace('<div class="db-wrap">\n', '<div class="db-wrap">\n' + hero_html)

# 2. Fix ALL inline color overrides to be navy/orange theme only
# Replace rainbow inline color styles with neutral/theme colors
color_map = {
    'style="color:var(--navy);"': '',   # remove (mini-stat-val already handles)
    'style="color:var(--blue);"': 'style="color:var(--orange);"',
    'style="color:var(--amber);"': 'style="color:var(--navy);"',
    'style="color:var(--red);"': 'style="color:#ef4444;"',
    'style="color:var(--green);"': 'style="color:var(--green);"',
    'style="color:var(--purple);"': 'style="color:var(--orange);"',
    'style="color:var(--teal);"': 'style="color:var(--orange);"',
}
for old, new in color_map.items():
    content = content.replace(old, new)

# 3. Fix icon colors in dc-title to use orange
content = content.replace('style="color:var(--blue);"', 'style="color:var(--orange);"')
content = content.replace('style="color:var(--green);"', 'style="color:var(--orange);"')
content = content.replace('style="color:var(--teal);"', 'style="color:var(--orange);"')
content = content.replace('style="color:var(--purple);"', 'style="color:var(--orange);"')
content = content.replace('style="color:var(--amber);"', 'style="color:var(--orange);"')

# 4. Fix KPI card classes - replace 'orange', 'green', etc. class names with light/accent/default
content = content.replace("class=\"kpi orange\"", 'class="kpi"')
content = content.replace("class=\"kpi blue\"", 'class="kpi light"')
content = content.replace("class=\"kpi green\"", 'class="kpi accent"')
content = content.replace("class=\"kpi red\"", 'class="kpi"')
content = content.replace("class=\"kpi amber\"", 'class="kpi light"')
content = content.replace("class=\"kpi teal\"", 'class="kpi"')
content = content.replace("class=\"kpi navy\"", 'class="kpi light"')
content = content.replace("class=\"kpi purple\"", 'class="kpi accent"')

# 5. Update hero stats in JS - insert update calls
hero_js = """
        // Update Hero Banner
        document.getElementById('hero-apps').innerHTML = '<div class="hero-stat-val">' + d.applications.active + '</div><div class="hero-stat-lbl">Active Loans</div>';
        document.getElementById('hero-vault').innerHTML = '<div class="hero-stat-val">' + d.client_vault.total + '</div><div class="hero-stat-lbl">Vault Clients</div>';
        document.getElementById('hero-staff').innerHTML = '<div class="hero-stat-val">' + d.staff.online + '</div><div class="hero-stat-lbl">Staff Online</div>';
        
        // Update last refresh time
        const now = new Date();
        document.getElementById('lastRefresh').innerText = now.toLocaleTimeString('en-IN', {hour:'2-digit', minute:'2-digit'});
"""
# Insert after 'const res  = await fetch'  and after building kpis
content = content.replace("    // ===== KPI STRIP =====", hero_js + "\n    // ===== KPI STRIP =====")

# 6. Make CHART colors navy/orange themed (replace COLORS array)
content = content.replace(
    "const COLORS = ['#f97316','#3b82f6','#10b981','#8b5cf6','#f59e0b','#14b8a6','#ef4444','#0f172a'];",
    "const COLORS = ['#f97316','#0f172a','#ea580c','#334155','#fed7aa','#1e293b','#fb923c','#475569'];"
)

# 7. Upgrade the Navy/dark info banners in Client Vault + Email sections
content = content.replace(
    "style=\"background:linear-gradient(135deg,#0f172a,#1e293b); border-radius:12px; padding:16px; color:#fff; text-align:center; margin-top:8px;\"",
    "class=\"info-banner\" style=\"margin-top:12px; text-align:center;\""
)
content = content.replace(
    "style=\"background:linear-gradient(135deg,#1e3a5f,#1e293b); border-radius:12px; padding:20px; color:#fff; text-align:center; margin-top:8px;\"",
    "class=\"info-banner\" style=\"margin-top:12px; text-align:center;\""
)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Theme + Hero banner applied to dashboard")
