import os

api_path = r'c:\Users\pc\Downloads\client mgmt2\api.php'
with open(api_path, 'r', encoding='utf-8') as f:
    api = f.read()

new_api = """
        case 'punch_in':
            if (!isset($_SESSION['username'])) exit;
            $u = $_SESSION['username'];
            $today = date('Y-m-d');
            $stmt = $db->prepare("SELECT id FROM staff_attendance WHERE username = ? AND att_date = ?");
            $stmt->execute([$u, $today]);
            if (!$stmt->fetch()) {
                $ins = $db->prepare("INSERT INTO staff_attendance (username, att_date, punch_in, status) VALUES (?, ?, CURRENT_TIMESTAMP, 'Working')");
                $ins->execute([$u, $today]);
            }
            echo json_encode(['success' => true]);
            break;
            
        case 'punch_out':
            if (!isset($_SESSION['username'])) exit;
            $u = $_SESSION['username'];
            $today = date('Y-m-d');
            $upd = $db->prepare("UPDATE staff_attendance SET punch_out = CURRENT_TIMESTAMP, status = 'Completed' WHERE username = ? AND att_date = ?");
            $upd->execute([$u, $today]);
            
            // Also set user to Offline
            $u_upd = $db->prepare("UPDATE users SET current_status = 'Offline' WHERE username = ?");
            $u_upd->execute([$u]);
            
            echo json_encode(['success' => true]);
            break;
"""

if "case 'punch_in':" not in api:
    api = api.replace('default:', new_api + '\n        default:')
    with open(api_path, 'w', encoding='utf-8') as f:
        f.write(api)

# 2. Update staff/index.php
idx_path = r'c:\Users\pc\Downloads\client mgmt2\staff\index.php'
with open(idx_path, 'r', encoding='utf-8') as f:
    idx = f.read()

php_logic = """$username = $_SESSION['username'];
$role = $_SESSION['role'];

// Check Attendance Status
$today = date('Y-m-d');
$att_stmt = $db->prepare("SELECT * FROM staff_attendance WHERE username = ? AND att_date = ?");
$att_stmt->execute([$username, $today]);
$attendance = $att_stmt->fetch(PDO::FETCH_ASSOC);
$is_punched_in = $attendance && !$attendance['punch_out'];
$is_punched_out = $attendance && $attendance['punch_out'];
?>"""

idx = idx.replace('$username = $_SESSION[\'username\'];\n$role = $_SESSION[\'role\'];\n?>', php_logic)

styles = """
        .tracking-card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            margin: 20px;
            box-shadow: 0 10px 25px rgba(255, 122, 0, 0.15);
            text-align: center;
            border: 2px solid var(--primary);
            position: relative;
            overflow: hidden;
        }
        .btn-punch {
            width: 100%;
            padding: 16px;
            border-radius: 12px;
            border: none;
            font-size: 16px;
            font-weight: 800;
            color: white;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        .btn-punch-in { background: var(--primary); box-shadow: 0 4px 15px rgba(255, 122, 0, 0.4); }
        .btn-punch-in:hover { background: var(--primary-dark); }
        .btn-punch-out { background: #ef4444; box-shadow: 0 4px 15px rgba(239, 68, 68, 0.4); }
        .btn-punch-out:hover { background: #dc2626; }
        
        .pulse-live {
            display: inline-block;
            width: 12px;
            height: 12px;
            background: #22c55e;
            border-radius: 50%;
            box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7);
            animation: pulse-green 1.5s infinite;
        }
        @keyframes pulse-green {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 10px rgba(34, 197, 94, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
        }
    </style>"""
idx = idx.replace('    </style>', styles)

ui_code = """    </div> <!-- Close app-header -->

    <!-- SMART TRACKING PUNCH-IN UI -->
    <div class="tracking-card">
        <?php if (!$attendance): ?>
            <div style="font-size:40px; margin-bottom:10px;">🛵</div>
            <h3 style="margin-bottom:6px; color:#1e293b;">Ready for the Field?</h3>
            <p style="font-size:13px; color:#64748b; margin-bottom:20px;">Start your day to begin live tracking and log visits.</p>
            <button class="btn-punch btn-punch-in" onclick="punchAction('in')">
                <i class="fas fa-play"></i> Start Field Day
            </button>
        <?php elseif ($is_punched_in): ?>
            <div style="background:#dcfce7; color:#166534; padding:8px 16px; border-radius:20px; font-weight:700; font-size:13px; display:inline-flex; align-items:center; gap:8px; margin-bottom:20px;">
                <div class="pulse-live"></div> LIVE GPS TRACKING ACTIVE
            </div>
            <p style="font-size:13px; color:#64748b; margin-bottom:20px;">Started at: <?= date('h:i A', strtotime($attendance['punch_in'])) ?></p>
            <button class="btn-punch btn-punch-out" onclick="punchAction('out')">
                <i class="fas fa-stop"></i> End Field Day
            </button>
        <?php else: ?>
            <div style="font-size:40px; margin-bottom:10px;">✅</div>
            <h3 style="margin-bottom:6px; color:#166534;">Day Completed</h3>
            <p style="font-size:13px; color:#64748b;">You worked from <?= date('h:i A', strtotime($attendance['punch_in'])) ?> to <?= date('h:i A', strtotime($attendance['punch_out'])) ?></p>
        <?php endif; ?>
    </div>
    
    <script>
    async function punchAction(action) {
        if (action === 'in') {
            // Request GPS before punching in to ensure tracker works
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(async (pos) => {
                    await fetch('../api.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'api=punch_in' });
                    window.location.reload();
                }, (err) => {
                    alert("Please enable GPS Location permission to Start your Day!");
                });
            } else {
                alert("GPS is not supported by your phone.");
            }
        } else {
            if(confirm("Are you sure you want to end your field day? Tracking will stop.")) {
                await fetch('../api.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'api=punch_out' });
                window.location.reload();
            }
        }
    }
    // Set global tracking variable for footer
    window.TRACKING_ACTIVE = <?= $is_punched_in ? 'true' : 'false' ?>;
    </script>
"""

idx = idx.replace('    </div> <!-- Close app-header -->', ui_code)
with open(idx_path, 'w', encoding='utf-8') as f:
    f.write(idx)

# 3. Update footer tracker.js to respect window.TRACKING_ACTIVE
footer_path = r'c:\Users\pc\Downloads\client mgmt2\footer.php'
with open(footer_path, 'r', encoding='utf-8') as f:
    footer = f.read()

tracker_condition_old = 'function startTracker() {'
tracker_condition_new = """function startTracker() {
                // Respect Punch-In status
                if (typeof window.TRACKING_ACTIVE !== 'undefined' && window.TRACKING_ACTIVE === false) {
                    return; // Do not track if not punched in
                }"""
if 'window.TRACKING_ACTIVE === false' not in footer:
    footer = footer.replace(tracker_condition_old, tracker_condition_new)
    with open(footer_path, 'w', encoding='utf-8') as f:
        f.write(footer)

print("Staff UI dramatically updated with Punch-In!")
