import os

header_path = r'c:\Users\pc\Downloads\client mgmt2\header.php'
with open(header_path, 'r', encoding='utf-8') as f:
    header = f.read()

target = """                <!-- Field Force Menu -->
                <?php if ($role === 'Admin'): ?>"""

repl = """                <!-- Field Force Menu -->
                <?php if ($role === 'Admin'): ?>
                <div class="menu-item">
                    <a href="admin_tracking.php" class="<?= $current_page == 'admin_tracking.php' ? 'active' : '' ?>">
                        <i data-lucide="crosshair"></i> Live Tracking
                    </a>
                </div>"""

if 'admin_tracking.php' not in header:
    header = header.replace(target, repl)
    with open(header_path, 'w', encoding='utf-8') as f:
        f.write(header)
    print("Added to header!")
else:
    print("Already in header")
