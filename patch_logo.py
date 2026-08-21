import os

staff_idx_path = r'c:\Users\pc\Downloads\client mgmt2\staff\index.php'
with open(staff_idx_path, 'r', encoding='utf-8') as f:
    idx = f.read()

# 1. Add CSS for glow logo
target_css = """        .app-header::after {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 150px;
            height: 150px;
            background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0) 70%);
            border-radius: 50%;
        }"""
repl_css = """        .app-header::after {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 150px;
            height: 150px;
            background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0) 70%);
            border-radius: 50%;
        }
        
        .company-logo-glow {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            z-index: 2;
            padding: 4px;
        }
        .company-logo-glow img {
            position: relative;
            z-index: 2;
            height: 38px;
            object-fit: contain;
            filter: drop-shadow(0 4px 6px rgba(0,0,0,0.1));
        }
        .company-logo-glow::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(255,255,255,0.6) 0%, rgba(255,255,255,0) 70%);
            z-index: 1;
            animation: softGlow 2.5s ease-in-out infinite alternate;
            border-radius: 50%;
        }
        @keyframes softGlow {
            0% { transform: scale(0.9); opacity: 0.4; }
            100% { transform: scale(1.6); opacity: 1; }
        }"""
idx = idx.replace(target_css, repl_css)

# 2. Update the duty-container HTML
target_html = """        <!-- Professional Sleek Duty Toggle -->
        <div class="duty-container">
            <div class="duty-label">
                <?php if ($is_punched_in): ?>
                    <div class="pulse-live"></div> <span id="duty-text">On Duty (Live)</span>
                <?php else: ?>
                    <div class="pulse-offline"></div> <span id="duty-text">Off Duty</span>
                <?php endif; ?>
            </div>
            <label class="switch">
                <input type="checkbox" id="dutyToggle" onchange="toggleDuty(this)" <?= $is_punched_in ? 'checked' : '' ?>>
                <span class="slider"></span>
            </label>
        </div>"""

repl_html = """        <!-- Professional Sleek Duty Toggle & Logo Box -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px; position:relative; z-index:2;">
            <div class="duty-container" style="margin-top:0;">
                <div class="duty-label">
                    <?php if ($is_punched_in): ?>
                        <div class="pulse-live"></div> <span id="duty-text">On Duty (Live)</span>
                    <?php else: ?>
                        <div class="pulse-offline"></div> <span id="duty-text">Off Duty</span>
                    <?php endif; ?>
                </div>
                <label class="switch">
                    <input type="checkbox" id="dutyToggle" onchange="toggleDuty(this)" <?= $is_punched_in ? 'checked' : '' ?>>
                    <span class="slider"></span>
                </label>
            </div>
            
            <div class="company-logo-glow">
                <img src="../logo.png" alt="BFS Logo" onerror="this.style.display='none'">
            </div>
        </div>"""

idx = idx.replace(target_html, repl_html)

with open(staff_idx_path, 'w', encoding='utf-8') as f:
    f.write(idx)
print("Added logo with glow animation")
