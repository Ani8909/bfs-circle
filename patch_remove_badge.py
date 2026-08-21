import os

staff_idx_path = r'c:\Users\pc\Downloads\client mgmt2\staff\index.php'
with open(staff_idx_path, 'r', encoding='utf-8') as f:
    idx = f.read()

# Target HTML to remove
html_target = """                <div class="metric-label" style="display:flex; align-items:center; gap:8px;">
                    Estimated Commission
                    <span id="commission_rate_badge" style="background:rgba(74, 222, 128, 0.2); color:#4ade80; padding:2px 6px; border-radius:4px; font-size:10px; font-weight:700; border:1px solid rgba(74, 222, 128, 0.4);">Rate: --%</span>
                </div>"""

html_repl = """                <div class="metric-label">
                    Estimated Commission
                </div>"""

idx = idx.replace(html_target, html_repl)

# Target JS to remove
js_target = """                // Set commission rate badge
                const rate = data.commission_rate || 0;
                document.getElementById('commission_rate_badge').innerText = `Rate: ${rate}%`;"""

js_repl = """                // Commission rate badge removed per user request"""

idx = idx.replace(js_target, js_repl)

with open(staff_idx_path, 'w', encoding='utf-8') as f:
    f.write(idx)
print("Removed commission rate badge")
