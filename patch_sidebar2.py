import os

header_path = r'c:\Users\pc\Downloads\client mgmt2\header.php'
with open(header_path, 'r', encoding='utf-8') as f:
    header = f.read()

target = """            <li class="menu-item <?php echo ($active_page === 'employee_activity.php') ? 'active' : ''; ?>">
                <a href="employee_activity.php">
                    <i data-lucide="bar-chart-2"></i>
                    <span class="menu-text">Staff Productivity</span>
                </a>
            </li>"""

repl = """            <li class="menu-item <?php echo ($active_page === 'employee_activity.php') ? 'active' : ''; ?>">
                <a href="employee_activity.php">
                    <i data-lucide="bar-chart-2"></i>
                    <span class="menu-text">Staff Productivity</span>
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
