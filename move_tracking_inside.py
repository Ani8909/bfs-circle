import os

fv_path = r'c:\Users\pc\Downloads\client mgmt2\field_visits.php'
with open(fv_path, 'r', encoding='utf-8') as f:
    fv = f.read()

target = """            <a href="staff_ranking.php" class="btn" style="background:#0f172a; color:white; padding:10px 16px; border-radius:8px; text-decoration:none; font-size:14px; font-weight:600; display:flex; align-items:center; gap:8px; border:none; box-shadow:0 2px 4px rgba(0,0,0,0.15); white-space:nowrap;">
                <i class="fas fa-trophy"></i> Leaderboard
            </a>"""

repl = """            <a href="admin_tracking.php" class="btn" style="background:linear-gradient(135deg, #ff7a00, #e66a00); color:white; padding:10px 16px; border-radius:8px; text-decoration:none; font-size:14px; font-weight:600; display:flex; align-items:center; gap:8px; border:none; box-shadow:0 4px 10px rgba(255, 122, 0, 0.3); white-space:nowrap;">
                <i class="fas fa-crosshairs fa-spin-hover"></i> Live Tracking Radar
            </a>
            <a href="staff_ranking.php" class="btn" style="background:#0f172a; color:white; padding:10px 16px; border-radius:8px; text-decoration:none; font-size:14px; font-weight:600; display:flex; align-items:center; gap:8px; border:none; box-shadow:0 2px 4px rgba(0,0,0,0.15); white-space:nowrap;">
                <i class="fas fa-trophy"></i> Leaderboard
            </a>"""

fv = fv.replace(target, repl)

with open(fv_path, 'w', encoding='utf-8') as f:
    f.write(fv)

# Now remove the Live Tracking menu item from header.php so it doesn't clutter
header_path = r'c:\Users\pc\Downloads\client mgmt2\header.php'
with open(header_path, 'r', encoding='utf-8') as f:
    header = f.read()

tracking_block = """            <li class="menu-item <?php echo ($active_page === 'admin_tracking.php') ? 'active' : ''; ?>">
                <a href="admin_tracking.php" style="background: rgba(255,122,0,0.1); color:#ff7a00; border-left:3px solid #ff7a00; border-radius:4px;">
                    <i data-lucide="crosshair"></i>
                    <span class="menu-text" style="font-weight:700;">Live Tracking</span>
                </a>
            </li>"""
            
if tracking_block in header:
    header = header.replace(tracking_block, "")
    with open(header_path, 'w', encoding='utf-8') as f:
        f.write(header)

print("Moved Live Tracking inside Field Visits page")
