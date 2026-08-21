import os

header_path = r'c:\Users\pc\Downloads\client mgmt2\header.php'
with open(header_path, 'r', encoding='utf-8') as f:
    header = f.read()

# Remove from old location
old_tracking_block = """            <li class="menu-item <?php echo ($active_page === 'admin_tracking.php') ? 'active' : ''; ?>">
                <a href="admin_tracking.php" style="background: rgba(255,122,0,0.1); color:#ff7a00; border-left:3px solid #ff7a00; border-radius:4px;">
                    <i data-lucide="crosshair"></i>
                    <span class="menu-text" style="font-weight:700;">Live Tracking</span>
                </a>
            </li>"""

if old_tracking_block in header:
    header = header.replace(old_tracking_block, "")

# Add to new location (after Field Visits)
target = """            <li class="menu-item <?php echo ($active_page === 'field_visits.php') ? 'active' : ''; ?>">
                <a href="field_visits.php">
                    <i data-lucide="map-pin"></i>
                    <span class="menu-text">Field Visits</span>
                </a>
            </li>"""

repl = """            <li class="menu-item <?php echo ($active_page === 'field_visits.php') ? 'active' : ''; ?>">
                <a href="field_visits.php">
                    <i data-lucide="map-pin"></i>
                    <span class="menu-text">Field Visits</span>
                </a>
            </li>
            <li class="menu-item <?php echo ($active_page === 'admin_tracking.php') ? 'active' : ''; ?>">
                <a href="admin_tracking.php" style="background: rgba(255,122,0,0.1); color:#ff7a00; border-left:3px solid #ff7a00; border-radius:4px;">
                    <i data-lucide="crosshair"></i>
                    <span class="menu-text" style="font-weight:700;">Live Tracking</span>
                </a>
            </li>"""

header = header.replace(target, repl)

with open(header_path, 'w', encoding='utf-8') as f:
    f.write(header)
print("Moved Live Tracking to under Field Visits")
