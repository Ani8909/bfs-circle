import os

idx_path = r'c:\Users\pc\Downloads\client mgmt2\staff\index.php'
with open(idx_path, 'r', encoding='utf-8') as f:
    idx = f.read()

# 1. Remove the old ugly tracking block I added
import re
# Find the exact block I inserted and remove it
idx = re.sub(r'<!-- SMART TRACKING PUNCH-IN UI -->.*?<div class="metrics-grid">', '<div class="metrics-grid">', idx, flags=re.DOTALL)
idx = re.sub(r'\.tracking-card \{.*?</style>', '</style>', idx, flags=re.DOTALL)

# 2. Add the Sleek Toggle Styles
styles = """
        /* Sleek Duty Toggle */
        .duty-container {
            background: rgba(0, 0, 0, 0.15);
            backdrop-filter: blur(10px);
            border-radius: 100px;
            padding: 8px 12px;
            display: inline-flex;
            align-items: center;
            gap: 12px;
            margin-top: 16px;
            border: 1px solid rgba(255,255,255,0.2);
        }
        
        .duty-label {
            color: white;
            font-size: 13px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .pulse-live {
            width: 8px;
            height: 8px;
            background: #4ade80;
            border-radius: 50%;
            box-shadow: 0 0 0 0 rgba(74, 222, 128, 0.7);
            animation: pulse-green 1.5s infinite;
        }
        .pulse-offline {
            width: 8px;
            height: 8px;
            background: #f87171;
            border-radius: 50%;
        }
        @keyframes pulse-green {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(74, 222, 128, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(74, 222, 128, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(74, 222, 128, 0); }
        }

        /* iOS style toggle switch */
        .switch {
            position: relative;
            display: inline-block;
            width: 44px;
            height: 24px;
        }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider {
            position: absolute; cursor: pointer;
            top: 0; left: 0; right: 0; bottom: 0;
            background-color: rgba(255,255,255,0.3);
            transition: .3s;
            border-radius: 24px;
        }
        .slider:before {
            position: absolute; content: "";
            height: 18px; width: 18px;
            left: 3px; bottom: 3px;
            background-color: white;
            transition: .3s;
            border-radius: 50%;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        input:checked + .slider { background-color: #22c55e; }
        input:checked + .slider:before { transform: translateX(20px); }
    </style>"""
idx = idx.replace('    </style>', styles)

# 3. Add the toggle inside the app-header
header_target = """            <a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i></a>
        </div>"""

header_new = """            <a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i></a>
        </div>
        
        <!-- Professional Sleek Duty Toggle -->
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
        </div>
        
        <script>
        async function toggleDuty(cb) {
            const action = cb.checked ? 'in' : 'out';
            if (action === 'in') {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(async (pos) => {
                        await fetch('../api.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'api=punch_in' });
                        window.location.reload();
                    }, (err) => {
                        alert("GPS Permission is required to go On Duty.");
                        cb.checked = false;
                    });
                } else {
                    alert("GPS not supported.");
                    cb.checked = false;
                }
            } else {
                if(confirm("End shift and stop location tracking?")) {
                    await fetch('../api.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'api=punch_out' });
                    window.location.reload();
                } else {
                    cb.checked = true;
                }
            }
        }
        window.TRACKING_ACTIVE = <?= $is_punched_in ? 'true' : 'false' ?>;
        </script>
"""
idx = idx.replace(header_target, header_new)

with open(idx_path, 'w', encoding='utf-8') as f:
    f.write(idx)

print("Redesigned to professional UI!")
