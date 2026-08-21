<?php
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'Staff';

$stmt = $db->prepare("SELECT department, photo_path FROM employees WHERE user_id = ?");
$stmt->execute([$user_id]);
$emp_data = $stmt->fetch(PDO::FETCH_ASSOC);
$department = $emp_data['department'] ?? '';
$photo_path = $emp_data['photo_path'] ?? '';

// Dynamic Greeting based on time
$hour = date('H');
if ($hour < 12) {
    $greeting = "Good Morning,";
} elseif ($hour < 16) {
    $greeting = "Good Afternoon,";
} elseif ($hour < 20) {
    $greeting = "Good Evening,";
} else {
    $greeting = "Good Night,";
}

if ($department !== 'Lead Generation Team') {
    die("Access Denied: This mobile application is restricted to Field Employees (Lead Generation Team) only.");
}

$username = $_SESSION['username'];
$role = $_SESSION['role'];

// Check Attendance Status
$today = date('Y-m-d');
$att_stmt = $db->prepare("SELECT * FROM staff_attendance WHERE username = ? AND att_date = ?");
$att_stmt->execute([$username, $today]);
$attendance = $att_stmt->fetch(PDO::FETCH_ASSOC);
$is_punched_in = $attendance && !$attendance['punch_out'];
$is_punched_out = $attendance && $attendance['punch_out'];
$punch_in_time = $attendance['punch_in'] ?? '';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>My Field App - BFS Financial Services</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #FF7A00;
            --primary-dark: #E66A00;
            --secondary: #1E293B;
            --bg-color: #F1F5F9;
            --card-bg: #FFFFFF;
            --text-main: #0F172A;
            --text-muted: #64748B;
            --border-color: #E2E8F0;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        
        body {
            background-color: var(--bg-color);
            color: var(--text-main);
            padding-bottom: 70px; /* Space for bottom nav */
        }

        /* App Header */
        .app-header {
            background: linear-gradient(135deg, #FF6B00 0%, #FF9A44 100%);
            color: white;
            padding: 24px 16px 20px 16px;
            border-bottom-left-radius: 32px;
            border-bottom-right-radius: 32px;
            box-shadow: 0 10px 30px rgba(255, 107, 0, 0.3);
            position: relative;
            overflow: hidden;
        }
        .app-header::after {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 150px;
            height: 150px;
            background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0) 70%);
            border-radius: 50%;
        }
        
        .company-logo-box {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            z-index: 2;
            padding-right: 8px;
            animation: floatLogo 4s ease-in-out infinite;
        }
        .company-logo-box img {
            height: 56px;
            object-fit: contain;
            position: relative;
            z-index: 2;
            /* Turn any colored logo into pure white */
            filter: brightness(0) invert(1) drop-shadow(0 2px 4px rgba(0,0,0,0.15));
        }
        @keyframes floatLogo {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-3px); }
            100% { transform: translateY(0px); }
        }

        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .user-greeting { font-size: 14px; opacity: 0.9; }
        .user-name { font-size: 20px; font-weight: 700; margin-top: 4px; }
        .logout-btn { color: white; text-decoration: none; font-size: 20px; }

        /* Dashboard Cards */
        .metrics-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            padding: 0 16px;
            margin-top: -35px;
        }

        .metric-card {
            background: white;
            border-radius: 24px;
            padding: 20px;
            box-shadow: 0 12px 24px rgba(0,0,0,0.04);
            text-align: center;
            border: 1px solid rgba(0,0,0,0.02);
        }
        
        .metric-card.full-width {
            grid-column: 1 / -1;
            display: flex;
            align-items: center;
            justify-content: space-between;
            text-align: left;
            background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%);
            color: white;
            border-radius: 24px;
            box-shadow: 0 16px 32px rgba(15, 23, 42, 0.2);
            border: 1px solid rgba(255,255,255,0.1);
        }

        .metric-icon {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: rgba(255, 122, 0, 0.1);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            margin: 0 auto 12px;
        }

        .metric-value { font-size: 24px; font-weight: 700; color: var(--text-main); }
        .metric-label { font-size: 12px; color: var(--text-muted); margin-top: 4px; }
        
        .full-width .metric-value { color: white; font-size: 28px; }
        .full-width .metric-label { color: #94A3B8; }
        .full-width .metric-icon { margin: 0; background: rgba(255,255,255,0.1); color: white; }

        /* Recent Activity */
        .section-container {
            padding: 24px 16px;
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .see-all { font-size: 12px; color: var(--primary); text-decoration: none; font-weight: 500; }

        .lead-card {
            background: white;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 16px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
            border: 1px solid var(--border-color);
        }

        .lead-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #F1F5F9;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
            flex-shrink: 0;
        }

        .lead-info { flex: 1; overflow: hidden; }
        .lead-name { font-size: 14px; font-weight: 600; margin-bottom: 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .lead-sub { font-size: 12px; color: var(--text-muted); }
        
        .lead-status {
            font-size: 11px;
            font-weight: 600;
            padding: 4px 8px;
            border-radius: 20px;
            background: #e0f2fe;
            color: #0369a1;
        }
        .status-completed { background: #dcfce7; color: #15803d; }

        /* Bottom Nav */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            display: flex;
            justify-content: space-around;
            padding: 12px 0;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.05);
            z-index: 100;
        }

        .nav-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 10px;
            font-weight: 500;
        }

        .nav-btn.active { color: var(--primary); }
        .nav-btn i { font-size: 20px; }
        
        /* Floating Action Button */
        .fab {
            position: absolute;
            top: -20px;
            left: 50%;
            transform: translateX(-50%);
            height: 44px;
            padding: 0 16px;
            border-radius: 22px;
            background: linear-gradient(135deg, #FF6B00 0%, #FF9A44 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 600;
            box-shadow: 0 6px 15px rgba(255, 107, 0, 0.35);
            text-decoration: none;
            white-space: nowrap;
            gap: 6px;
        }

        /* Tools Horizontal Scroll */
        .tools-scroll {
            display: flex;
            overflow-x: auto;
            gap: 16px;
            padding: 8px 16px 24px 16px;
            scrollbar-width: none; /* Firefox */
        }
        .tools-scroll::-webkit-scrollbar { display: none; }
        
        .tool-card {
            min-width: 130px;
            background: white;
            border-radius: 20px;
            padding: 18px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.03);
            text-decoration: none;
            display: flex;
            flex-direction: column;
            gap: 12px;
            border: 1px solid #f1f5f9;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .tool-card:active {
            transform: scale(0.96);
            box-shadow: 0 4px 8px rgba(0,0,0,0.02);
        }
        
        .tool-card .icon {
            width: 40px; height: 40px;
            border-radius: 12px;
            background: rgba(255,122,0,0.1);
            color: var(--primary);
            display: flex; align-items: center; justify-content: center;
            font-size: 18px;
        }
        
        .tool-card .title {
            color: var(--text-main);
            font-weight: 600;
            font-size: 14px;
        }


    
        /* Sleek Duty Toggle */
        .duty-container {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-radius: 100px;
            padding: 6px 12px;
            display: inline-flex;
            align-items: center;
            gap: 12px;
            margin-top: 10px;
            border: 1px solid rgba(255,255,255,0.3);
            box-shadow: inset 0 2px 4px rgba(255,255,255,0.1);
            position: relative;
            z-index: 2;
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
    </style>
</head>
<body>

    <div class="app-header">
        <div class="header-top">
            <div style="display:flex; align-items:center; gap:12px;">
                <div style="width:44px; height:44px; background:rgba(255,255,255,0.2); border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:18px; overflow:hidden; border:2px solid rgba(255,255,255,0.3);">
                    <?php if(!empty($photo_path)): ?>
                        <img src="../uploads/employees/<?= htmlspecialchars($photo_path) ?>" style="width:100%; height:100%; object-fit:cover;">
                    <?php else: ?>
                        👨‍💼
                    <?php endif; ?>
                </div>
                <div>
                    <div class="user-greeting" style="font-size:13px; font-weight:500; opacity:0.9;"><?= isset($greeting) ? $greeting : 'Hello,' ?></div>
                    <div class="user-name" id="staff_name" style="font-size:16px; margin-top:2px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:200px;"><?= htmlspecialchars(explode('@', $username)[0]) ?></div>
                </div>
            </div>
            <a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i></a>
        </div>
        
        <!-- Professional Sleek Duty Toggle & Logo Box -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px; position:relative; z-index:2;">
            <div class="duty-container" style="margin-top:0;">
                <div class="duty-label">
                    <?php if ($is_punched_in): ?>
                        <div style="display:flex; align-items:center;">
                            <div class="pulse-live"></div> 
                            <span id="duty-text" style="font-weight:600; color:#fff;">On Duty</span>
                            <span style="color:rgba(255,255,255,0.5); margin:0 6px;">|</span>
                            <i class="far fa-clock" style="font-size:11px; color:rgba(255,255,255,0.8); margin-right:4px;"></i>
                            <span id="shift_timer" style="font-size:13px; font-weight:700; color:#fff; font-variant-numeric:tabular-nums; letter-spacing:0.5px;">00:00:00</span>
                        </div>
                    <?php else: ?>
                        <div class="pulse-offline"></div> <span id="duty-text">Off Duty</span>
                    <?php endif; ?>
                </div>
                <label class="switch">
                    <input type="checkbox" id="dutyToggle" onchange="toggleDuty(this)" <?= $is_punched_in ? 'checked' : '' ?>>
                    <span class="slider"></span>
                </label>
            </div>
            
            <div class="company-logo-box">
                <img src="../logo.png" alt="BFS Logo" onerror="this.style.display='none'">
            </div>
        </div>
        
        <script>
        async function toggleDuty(cb) {
            const action = cb.checked ? 'in' : 'out';
            if (action === 'in') {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(async (pos) => {
                        await fetch('?api=punch_in', { method:'POST' });
                        window.location.reload();
                    }, (err) => {
                        document.getElementById('gps-blocker').style.display = 'flex';
                        cb.checked = false;
                    }, { timeout: 5000 });
                } else {
                    alert("GPS not supported.");
                    cb.checked = false;
                }
            } else {
                if(confirm("End shift and stop location tracking?")) {
                    await fetch('?api=punch_out', { method:'POST' });
                    window.location.reload();
                } else {
                    cb.checked = true;
                }
            }
        }
        window.TRACKING_ACTIVE = <?= $is_punched_in ? 'true' : 'false' ?>;
        
        <?php if ($is_punched_in && $punch_in_time): ?>
        (function() {
            // Calculate elapsed time securely using server-provided start time
            // PHP format is 'Y-m-d H:i:s'
            const punchInStr = "<?= $punch_in_time ?>".replace(/-/g, '/'); // Compatibility for Safari/iOS
            const startTime = new Date(punchInStr).getTime();
            const timerEl = document.getElementById('shift_timer');
            
            function updateShiftTimer() {
                const now = new Date().getTime();
                let diff = Math.floor((now - startTime) / 1000);
                if (diff < 0) diff = 0;
                
                const hrs = String(Math.floor(diff / 3600)).padStart(2, '0');
                const mins = String(Math.floor((diff % 3600) / 60)).padStart(2, '0');
                const secs = String(diff % 60).padStart(2, '0');
                timerEl.innerText = `${hrs}:${mins}:${secs}`;
            }
            setInterval(updateShiftTimer, 1000);
            updateShiftTimer();
        })();
        <?php endif; ?>
        
        </script>

        <div style="height: 40px;"></div> <!-- Spacer for overlapping cards -->
    </div>


    <div class="metrics-grid">
        <div class="metric-card full-width" style="position:relative; overflow:hidden;">
            <div>
                <div class="metric-label">
                    Estimated Commission
                </div>
                <div class="metric-value" id="total_commission">₹0</div>
            </div>
            <div class="metric-icon" style="z-index:2;"><i class="fas fa-wallet"></i></div>
            
            <!-- Background design element -->
            <i class="fas fa-coins" style="position:absolute; right:-10px; bottom:-10px; font-size:80px; opacity:0.05; z-index:1; transform:rotate(-15deg);"></i>
        </div>


        
        <div class="metric-card">
            <div class="metric-icon"><i class="fas fa-map-marker-alt"></i></div>
            <div class="metric-value" id="total_visits">0</div>
            <div class="metric-label">Field Visits</div>
        </div>
        
        <div class="metric-card">
            <div class="metric-icon"><i class="fas fa-users"></i></div>
            <div class="metric-value" id="total_leads">0</div>
            <div class="metric-label">Generated Leads</div>
        </div>
    </div>
    
    <div style="padding: 0 16px; margin-top: 16px;">
        <a href="my_route.php" style="display:block; text-decoration:none; background: linear-gradient(135deg, #1e293b, #0f172a); color: white; padding: 18px; border-radius: 20px; box-shadow: 0 8px 20px rgba(0,0,0,0.1); position:relative; overflow:hidden;">
            <div style="display:flex; align-items:center; justify-content:space-between; z-index:2; position:relative;">
                <div style="display:flex; align-items:center; gap:16px;">
                    <div style="width:48px; height:48px; background:rgba(255,255,255,0.1); border-radius:12px; display:flex; align-items:center; justify-content:center;">
                        <i class="fas fa-route" style="font-size:24px; color:#4ade80;"></i>
                    </div>
                    <div>
                        <div style="font-weight:700; font-size:16px; letter-spacing:0.3px;">My Timeline & Route</div>
                        <div style="font-size:13px; color:#94a3b8; margin-top:4px;">Track your today's field journey</div>
                    </div>
                </div>
                <div style="width:32px; height:32px; background:rgba(255,255,255,0.05); border-radius:50%; display:flex; align-items:center; justify-content:center;">
                    <i class="fas fa-chevron-right" style="color:#94a3b8; font-size:14px;"></i>
                </div>
            </div>
            <i class="fas fa-map-marked-alt" style="position:absolute; right:-15px; bottom:-15px; font-size:90px; opacity:0.04; z-index:1; transform:rotate(-15deg);"></i>
        </a>
    </div>

    <!-- Financial Tools Section -->
    <div class="section-container" style="margin-bottom: 0;">
        <div class="section-title" style="display:flex; justify-content:space-between; align-items:center;">
            <span>Financial Tools</span>
            <a href="calculators.php" style="color:var(--primary); font-size:13px; font-weight:600; text-decoration:none;">View All</a>
        </div>
    </div>
    <div class="tools-scroll">
        <a href="calc_emi.php" class="tool-card">
            <div class="icon"><i class="fas fa-calculator"></i></div>
            <div class="title">EMI<br>Calculator</div>
        </a>
        <a href="calc_eligibility.php" class="tool-card">
            <div class="icon" style="background:rgba(16, 185, 129, 0.1); color:#10b981;"><i class="fas fa-check-circle"></i></div>
            <div class="title">Loan<br>Eligibility</div>
        </a>
        <a href="calc_foir.php" class="tool-card">
            <div class="icon" style="background:rgba(99, 102, 241, 0.1); color:#6366f1;"><i class="fas fa-percentage"></i></div>
            <div class="title">FOIR<br>Calculator</div>
        </a>
        <a href="calc_ltv.php" class="tool-card">
            <div class="icon" style="background:rgba(236, 72, 153, 0.1); color:#ec4899;"><i class="fas fa-home"></i></div>
            <div class="title">LTV<br>Calculator</div>
        </a>
    </div>

    <!-- Leaderboard Banner -->
    <div style="padding: 0 16px 20px;">
        <a href="ranking.php" style="display:flex; align-items:center; justify-content:space-between; background:linear-gradient(135deg, #FFB75E 0%, #ED8F03 100%); border-radius:24px; padding:20px; color:white; text-decoration:none; box-shadow:0 12px 24px rgba(237, 143, 3, 0.3); border:1px solid rgba(255,255,255,0.2);">
            <div style="display:flex; align-items:center; gap:16px;">
                <div style="width:40px; height:40px; background:rgba(255,255,255,0.2); border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:20px;">
                    <i class="fas fa-trophy"></i>
                </div>
                <div>
                    <div style="font-weight:700; font-size:16px;">Leaderboard</div>
                    <div style="font-size:12px; opacity:0.9;">Check your ranking!</div>
                </div>
            </div>
            <i class="fas fa-chevron-right" style="opacity:0.8;"></i>
        </a>
    </div>

    <div class="section-container">
        <div class="section-title">
            <span>Recent Leads (Via Your Network)</span>
        </div>
        
        <div id="leads_list">
            <div style="text-align:center;padding:20px;color:gray;font-size:14px;">Loading...</div>
        </div>
    </div>

    <!-- Bottom Nav -->
    <div class="bottom-nav">
        <a href="index.php" class="nav-btn <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>
        <a href="visits.php" class="nav-btn <?= basename($_SERVER['PHP_SELF']) == 'visits.php' ? 'active' : '' ?>">
            <i class="fas fa-list-ul"></i>
            <span>My Visits</span>
        </a>
        <div style="width: 110px; position: relative;">
            <a href="add_visit.php" class="fab">
                <i class="fas fa-plus"></i> <span>Start Visit</span>
            </a>
        </div>
        <a href="files.php" class="nav-btn <?= basename($_SERVER['PHP_SELF']) == 'files.php' ? 'active' : '' ?>">
            <i class="fas fa-file-invoice"></i>
            <span>Files</span>
        </a>
        <a href="profile.php" class="nav-btn <?= basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : '' ?>">
            <i class="fas fa-user"></i>
            <span>Profile</span>
        </a>
    </div>

    <script>
        const username = "<?= htmlspecialchars($username) ?>";
        
        // Skeleton HTML function
        function getSkeletonHTML(count = 3) {
            let html = '';
            for(let i=0; i<count; i++) {
                html += `
                <div style="background:white; border-radius:16px; padding:18px; margin-bottom:12px; box-shadow:0 4px 15px rgba(0,0,0,0.04); position:relative; overflow:hidden;">
                    <div style="animation: shimmer 2s infinite linear; background: linear-gradient(to right, #f6f7f8 4%, #edeef1 25%, #f6f7f8 36%); background-size: 1000px 100%; height: 20px; width: 60%; margin-bottom: 12px; border-radius: 4px;"></div>
                    <div style="animation: shimmer 2s infinite linear; background: linear-gradient(to right, #f6f7f8 4%, #edeef1 25%, #f6f7f8 36%); background-size: 1000px 100%; height: 16px; width: 40%; border-radius: 4px;"></div>
                </div>`;
            }
            return html;
        }

        async function loadDashboardData() {
            try {
                // Fetch stats
                const res = await fetch(`?api=get_staff_performance&username=${encodeURIComponent(username)}`);
                const data = await res.json();
                
                document.getElementById('total_visits').innerText = data.total_visits || 0;
                document.getElementById('total_leads').innerText = data.total_leads || 0;
                
                // Format currency
                const commission = data.total_commission || 0;
                document.getElementById('total_commission').innerText = '₹' + parseFloat(commission).toLocaleString('en-IN', {maximumFractionDigits: 2});
                
                // Commission rate badge removed per user request
                
                // If they haven't earned anything yet but have a rate, we could show a tooltip, but it's self-explanatory now.
                
            } catch (err) {
                console.error("Failed to load metrics", err);
            }
            
            // Load recent leads
            const list = document.getElementById('leads_list');
            list.innerHTML = getSkeletonHTML(3);
            
            try {
                const res2 = await fetch(`?api=get_staff_recent_leads&username=${encodeURIComponent(username)}`);
                const data2 = await res2.json();
                
                if (!data2 || data2.error) {
                    list.innerHTML = `<div style="text-align:center;padding:20px;color:#ef4444;font-size:14px;">${data2?.error || 'Error loading leads'}</div>`;
                    return;
                }
                
                if (data2.length === 0) {
                    list.innerHTML = '<div style="text-align:center;padding:20px;color:gray;font-size:14px;">No recent leads generated yet.</div>';
                    return;
                }
                
                let html = '';
                data2.forEach(lead => {
                    const status = lead.overall_status || 'Pending';
                    const isCompleted = status.toLowerCase() === 'completed';
                    const statusClass = isCompleted ? 'status-completed' : '';
                    
                    html += `
                        <div class="lead-card">
                            <div class="lead-icon"><i class="fas fa-user"></i></div>
                            <div class="lead-info">
                                <div class="lead-name">${lead.applicant_name || 'N/A'}</div>
                                <div class="lead-sub">₹${parseFloat(lead.loan_amount_requested || 0).toLocaleString('en-IN')} - ${lead.loan_type || 'N/A'}</div>
                            </div>
                            <div class="lead-status ${statusClass}">${status}</div>
                        </div>
                    `;
                });
                
                list.innerHTML = html;
            } catch (err) {
                list.innerHTML = '<div style="text-align:center;color:red;font-size:14px;">Network Error loading leads.</div>';
            }
        }
        
        // Auto load
        loadDashboardData();
    </script>
    <style>
        @keyframes shimmer {
            0% { background-position: -1000px 0; }
            100% { background-position: 1000px 0; }
        }

    
        /* Sleek Duty Toggle */
        .duty-container {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-radius: 100px;
            padding: 6px 12px;
            display: inline-flex;
            align-items: center;
            gap: 12px;
            margin-top: 10px;
            border: 1px solid rgba(255,255,255,0.3);
            box-shadow: inset 0 2px 4px rgba(255,255,255,0.1);
            position: relative;
            z-index: 2;
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
    </style>

    <!-- GPS Blocker Overlay (Premium English + SVG) -->
    <div id="gps-blocker" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15, 23, 42, 0.75); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); z-index:999999; flex-direction:column; align-items:center; justify-content:center; padding:20px;">
        
        <div style="width:100%; max-width:380px; background:#ffffff; border-radius:28px; padding:36px 24px; box-shadow:0 24px 50px rgba(0,0,0,0.25); text-align:center;">
            
            <!-- Animated Pulse SVG Icon -->
            <div style="position:relative; width:72px; height:72px; margin:0 auto 20px auto;">
                <div style="position:absolute; inset:0; background:#FFEDD5; border-radius:50%; animation: pulse 2s infinite;"></div>
                <div style="position:absolute; inset:4px; background:#FFEDD5; border-radius:50%; box-shadow:0 8px 16px rgba(255,122,0,0.15); display:flex; align-items:center; justify-content:center;">
                    <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#FF7A00" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                        <circle cx="12" cy="10" r="3"></circle>
                    </svg>
                </div>
            </div>
            
            <h2 style="margin:0 0 8px 0; font-size:22px; color:#0F172A; font-weight:800; letter-spacing:-0.5px;">Location Access Required</h2>
            <p style="margin:0 0 28px 0; font-size:14px; color:#64748B; line-height:1.5;">To go On Duty, you need to enable location access for accurate field tracking.</p>
            
            <!-- Step 1 Card -->
            <div style="background:#F8FAFC; border-radius:20px; padding:18px; margin-bottom:12px; display:flex; align-items:flex-start; gap:16px; text-align:left; border:1px solid #E2E8F0;">
                <div style="width:42px; height:42px; background:white; border-radius:12px; display:flex; align-items:center; justify-content:center; box-shadow:0 4px 10px rgba(0,0,0,0.04); flex-shrink:0;">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#3B82F6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect>
                        <line x1="12" y1="18" x2="12.01" y2="18"></line>
                    </svg>
                </div>
                <div style="flex:1;">
                    <div style="font-size:11px; font-weight:800; color:#3B82F6; text-transform:uppercase; letter-spacing:0.8px; margin-bottom:4px;">Step 1</div>
                    <div style="font-size:15px; color:#1E293B; font-weight:700; margin-bottom:6px;">Enable Device GPS</div>
                    <div style="font-size:13px; color:#475569; font-weight:500; line-height:1.5;">
                        Swipe down from the top of your screen and ensure the <b>Location / GPS</b> toggle is turned ON.
                    </div>
                </div>
            </div>

            <!-- Step 2 Card -->
            <div style="background:#F8FAFC; border-radius:20px; padding:18px; margin-bottom:32px; display:flex; align-items:flex-start; gap:16px; text-align:left; border:1px solid #E2E8F0;">
                <div style="width:42px; height:42px; background:white; border-radius:12px; display:flex; align-items:center; justify-content:center; box-shadow:0 4px 10px rgba(0,0,0,0.04); flex-shrink:0;">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                </div>
                <div style="flex:1;">
                    <div style="font-size:11px; font-weight:800; color:#10B981; text-transform:uppercase; letter-spacing:0.8px; margin-bottom:4px;">Step 2</div>
                    <div style="font-size:15px; color:#1E293B; font-weight:700; margin-bottom:6px;">Allow Browser Access</div>
                    <div style="font-size:13px; color:#475569; font-weight:500; line-height:1.5;">
                        Tap the <b>🔒 Lock Icon</b> in your browser's address bar, go to <b>Permissions</b>, and set Location to <b>Allow</b>.
                    </div>
                </div>
            </div>

            <button onclick="window.location.reload()" style="background:linear-gradient(135deg, #FF7A00, #E66A00); color:white; padding:18px; border:none; border-radius:18px; font-weight:700; font-size:16px; width:100%; box-shadow:0 8px 25px rgba(255,122,0,0.3); cursor:pointer; margin-bottom:16px; transition: transform 0.2s, box-shadow 0.2s;">
                I've Enabled It - Refresh
            </button>
            <button onclick="document.getElementById('dutyToggle').checked = false; document.getElementById('gps-blocker').style.display='none';" style="background:transparent; border:none; color:#94A3B8; font-size:14px; font-weight:600; cursor:pointer; padding:8px;">
                Cancel
            </button>
        </div>
    </div>
    <style>
        @keyframes pulse {
            0% { transform: scale(1); opacity: 0.8; }
            50% { transform: scale(1.1); opacity: 0.3; }
            100% { transform: scale(1); opacity: 0.8; }
        }
    </style>
    <!-- 10x Field Force Tracker Engine -->
    <script>
        (function() {
            let lastPingTime = 0;
            let wakeLock = null;
            
            async function getBattery() {
                try {
                    if (navigator.getBattery) {
                        const bat = await navigator.getBattery();
                        return Math.round(bat.level * 100) + '%';
                    }
                } catch(e) {}
                return 'Unknown';
            }

            async function requestWakeLock() {
                try {
                    if ('wakeLock' in navigator) {
                        wakeLock = await navigator.wakeLock.request('screen');
                    }
                } catch (err) {}
            }

            function fallbackFetch(lat, lon, bat) {
                const fd = new FormData();
                fd.append('api', 'staff_ping');
                fd.append('lat', lat);
                fd.append('lon', lon);
                fd.append('battery', bat);
                fd.append('status', 'Active');
                
                fetch('?api=staff_ping', { method: 'POST', body: fd }).catch(e=>console.log(e));
            }

            function showGpsBlocker() {
                document.getElementById('gps-blocker').style.display = 'flex';
            }
            function hideGpsBlocker() {
                document.getElementById('gps-blocker').style.display = 'none';
            }

            function startTracker() {
                // Check if user is punched in (this variable is injected by PHP)
                const isPunchedIn = <?php echo $is_punched_in ? 'true' : 'false'; ?>;
                if (!isPunchedIn) {
                    return; // Do not track if Off Duty
                }
                
                requestWakeLock();
                document.addEventListener('visibilitychange', async () => {
                    if (wakeLock !== null && document.visibilityState === 'visible') {
                        requestWakeLock();
                    }
                });

                if (navigator.geolocation) {
                    navigator.geolocation.watchPosition(
                        async (pos) => {
                            hideGpsBlocker();
                            const lat = pos.coords.latitude;
                            const lon = pos.coords.longitude;
                            
                            const now = Date.now();
                            if (now - lastPingTime > 45000) { 
                                const bat = await getBattery();
                                fallbackFetch(lat, lon, bat);
                                lastPingTime = now;
                            }
                        },
                        (err) => {
                            console.log('Tracker GPS Error:', err);
                            showGpsBlocker(); // ENFORCE GPS
                        },
                        { enableHighAccuracy: true, maximumAge: 10000, timeout: 5000 }
                    );
                    
                    setInterval(() => {
                        navigator.geolocation.getCurrentPosition(
                            async (pos) => {
                                hideGpsBlocker();
                                const bat = await getBattery();
                                fallbackFetch(pos.coords.latitude, pos.coords.longitude, bat);
                                lastPingTime = Date.now();
                            },
                            (err) => { showGpsBlocker(); }
                        );
                    }, 60000); 
                } else {
                    alert("Your browser does not support GPS tracking.");
                }
            }
            
            setTimeout(startTracker, 2000);
        })();
    </script>
</body>
</html>

