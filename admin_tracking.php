<?php
require_once 'config.php';
// Ensure admin access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    die("Access Denied");
}
$current_page = 'admin_tracking.php';
$page_title = 'Live Field Force Radar';
$page_subtitle = 'Track staff location, battery, and route maps in real-time.';
require_once 'header.php';

// Distance calculation function (Haversine formula in PHP for quick total calc)
function haversineGreatCircleDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371) {
    $latFrom = deg2rad($latitudeFrom);
    $lonFrom = deg2rad($longitudeFrom);
    $latTo = deg2rad($latitudeTo);
    $lonTo = deg2rad($longitudeTo);
    $latDelta = $latTo - $latFrom;
    $lonDelta = $lonTo - $lonFrom;
    $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) + cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
    return $angle * $earthRadius;
}

// Fetch Staff Data
$staff_stmt = $db->query("SELECT * FROM users WHERE role = 'Staff' AND is_active = 1");
$staff_list = $staff_stmt->fetchAll(PDO::FETCH_ASSOC);

// For today's stats
$today = date('Y-m-d');
?>
<style>
    .tracking-table { width: 100%; border-collapse: separate; border-spacing: 0; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
    .tracking-table th { background: #f8fafc; padding: 14px 16px; text-align: left; font-size: 13px; font-weight: 600; color: #475569; border-bottom: 1px solid #e2e8f0; }
    .tracking-table td { padding: 16px; border-bottom: 1px solid #f1f5f9; font-size: 14px; color: #1e293b; vertical-align: middle; }
    .tracking-table tr:last-child td { border-bottom: none; }
    .tracking-table tbody tr { transition: all 0.2s; cursor: pointer; }
    .tracking-table tbody tr:hover { background: #f8fafc; transform: scale(1.01); box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
    
    .status-badge { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
    .status-active { background: #dcfce7; color: #166534; }
    .status-inactive { background: #fee2e2; color: #991b1b; }
    
    .pulse-dot { width: 8px; height: 8px; border-radius: 50%; }
    .pulse-dot.active { background: #22c55e; box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7); animation: pulse-green 2s infinite; }
    .pulse-dot.inactive { background: #ef4444; }
    
    @keyframes pulse-green {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(34, 197, 94, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
    }
    
    .btn-route { background: #3b82f6; color: white; border: none; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; }
    .btn-route:hover { background: #2563eb; }
</style>

<div class="dashboard-layout">
    <div style="background:#fff; padding:24px; border-radius:12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); margin-bottom:24px;">
        <table class="tracking-table">
            <thead>
                <tr>
                    <th>Staff Name</th>
                    <th>Status</th>
                    <th>Battery</th>
                    <th>Last Ping</th>
                    <th>Today's Distance</th>
                    <th>Clients Visited</th>
                    
                </tr>
            </thead>
            <tbody>
                  <?php foreach ($staff_list as $st): 
                    $uname = $st['username'];
                    
                    // Determine Status
                    $last_ping = $st['last_ping'] ? strtotime($st['last_ping']) : 0;
                    $diff_mins = $last_ping > 0 ? round((time() - $last_ping) / 60) : 999;
                    
                    // Fetch actual shift status from attendance today
                    $today = date('Y-m-d');
                    $att_check = $db->prepare("SELECT punch_out FROM staff_attendance WHERE username = ? AND att_date = ? ORDER BY id DESC LIMIT 1");
                    $att_check->execute([$uname, $today]);
                    $att = $att_check->fetch(PDO::FETCH_ASSOC);
                    
                    $is_on_duty = ($att !== false && $att['punch_out'] === null);
                    
                    if ($is_on_duty) {
                        if ($diff_mins <= 10) {
                            $status_class = 'status-active';
                            $status_text = 'On Duty (Active)';
                            $dot_class = 'active';
                        } else {
                            // On duty but GPS ping delayed
                            $status_class = 'status-active';
                            $status_text = 'On Duty (GPS Lost)';
                            $dot_class = 'inactive';
                        }
                    } else {
                        $status_class = 'status-inactive';
                        $status_text = 'Off Duty';
                        $dot_class = 'inactive';
                    }
                    
                    // We only need $is_active for the timer
                    $is_active = $is_on_duty;
                    
                    if ($diff_mins == 0) $ping_str = "Just now";
                    elseif ($diff_mins < 60) $ping_str = "{$diff_mins} mins ago";
                    elseif ($last_ping > 0) $ping_str = round($diff_mins/60) . " hours ago";
                    else $ping_str = "Never";
                    
                    // Calculate Today's Distance from Location Logs
                    $log_stmt = $db->prepare("SELECT lat, lon FROM staff_location_logs WHERE username = ? AND date(created_at) = ? ORDER BY created_at ASC");
                    $log_stmt->execute([$uname, $today]);
                    $logs = $log_stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    $total_dist = 0;
                    $log_points = [];
                    // Calculate Shift Duration
                    $att_stmt = $db->prepare("SELECT punch_in, punch_out FROM staff_attendance WHERE username = ? AND att_date = ? ORDER BY id DESC LIMIT 1");
                    $att_stmt->execute([$uname, $today]);
                    $att = $att_stmt->fetch(PDO::FETCH_ASSOC);
                    
                    $shift_str = "-";
                    if ($att && $att['punch_in']) {
                        $p_in = strtotime($att['punch_in']);
                        $p_out = $att['punch_out'] ? strtotime($att['punch_out']) : time();
                        if ($p_in > 0) {
                            $duration_secs = max(0, $p_out - $p_in);
                            $h = floor($duration_secs / 3600);
                            $m = floor(($duration_secs % 3600) / 60);
                            $shift_str = "{$h}h {$m}m";
                            if (!$att['punch_out'] && $is_active) {
                                $shift_str = "<span style='color:#10b981; font-weight:700;'><i class='fas fa-circle-notch fa-spin' style='font-size:10px; margin-right:4px;'></i>{$shift_str}</span>";
                            }
                        }
                    }

                    for($i=1; $i<count($logs); $i++) {
                        $p1 = $logs[$i-1];
                        $p2 = $logs[$i];
                        $d = haversineGreatCircleDistance((float)$p1['lat'], (float)$p1['lon'], (float)$p2['lat'], (float)$p2['lon']);
                        $total_dist += $d;
                    }
                    foreach($logs as $l) { $log_points[] = [(float)$l['lat'], (float)$l['lon']]; }
                    $route_json = htmlspecialchars(json_encode($log_points));
                    
                    // Count Visits Today
                    $vis_stmt = $db->prepare("SELECT count(*) FROM field_visits WHERE executive_name = ? AND visit_date = ?");
                    $vis_stmt->execute([$uname, $today]);
                    $vis_count = $vis_stmt->fetchColumn();
                ?>
                  <tr class="clickable-row" onclick="openStaff360('<?= $uname ?>')">
                    <td>
                        <div style="font-weight:600;"><?= htmlspecialchars($st['full_name'] ?? $uname) ?></div>
                        <div style="font-size:12px; color:#64748b;">EMP-<?= $st['id'] ?></div>
                    </td>
                    <td>
                        <span class="status-badge <?= $status_class ?>">
                            <div class="pulse-dot <?= $dot_class ?>"></div> <?= $status_text ?>
                        </span>
                    </td>
                    <td>
                        <?php if($st['current_battery']): ?>
                            <i data-lucide="battery-charging" style="width:16px; color:#10b981; vertical-align:-3px;"></i> <?= htmlspecialchars($st['current_battery']) ?>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td>
                        <div style="font-size:13px; font-weight:500;"><?= $ping_str ?></div>
                        <div style="font-size:11px; color:#94a3b8;"><?= $last_ping ? date('h:i A', $last_ping) : '' ?></div>
                    </td>
                    <td>
                        <div style="font-weight:600; color:#334155;"><?= number_format($total_dist, 1) ?> km</div>
                    </td>
                    <td>
                        <span style="background:#f1f5f9; padding:4px 8px; border-radius:6px; font-size:12px; font-weight:600; color:#475569;"><?= $vis_count ?> Done</span>
                    </td>
                    
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>


<!-- Staff 360 Drawer -->
<div class="modal-overlay" id="s360-overlay" onclick="closeStaff360()" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.6); backdrop-filter:blur(2px); z-index:9999; transition: opacity 0.3s ease;"></div>
<div id="s360-drawer" style="position:fixed; top:0; right:-550px; width:480px; max-width:100%; height:100%; background:#f8fafc; z-index:10000; box-shadow:-10px 0 40px rgba(0,0,0,0.15); transition: right 0.4s cubic-bezier(0.16, 1, 0.3, 1); display:flex; flex-direction:column;">
    
    <!-- Header Section -->
    <div style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color:white; padding: 24px 24px 32px 24px; position:relative; border-bottom-left-radius: 24px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
        <!-- Top right close icon -->
        <div class="s360-close-btn" onclick="closeStaff360()">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </div>
        

        
        <div style="display:flex; align-items:center; gap:20px;">
            <div id="s360-avatar" style="width:72px; height:72px; border-radius:50%; background:linear-gradient(135deg, #3b82f6, #2563eb); display:flex; align-items:center; justify-content:center; font-size:28px; font-weight:800; border:3px solid rgba(255,255,255,0.15); box-shadow: 0 4px 10px rgba(0,0,0,0.3); overflow:hidden;">A</div>
            <div>
                <h2 id="s360-name" style="font-size:22px; font-weight:700; margin:0 0 4px 0; letter-spacing: -0.02em;">Staff Name</h2>
                <div id="s360-role" style="font-size:13.5px; color:#94a3b8; margin-bottom:10px; font-weight:500;">EMP-ID | Role</div>
                <div id="s360-badge" style="display:inline-flex; align-items:center; padding:5px 12px; border-radius:30px; font-size:12px; font-weight:600; background:rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.05); backdrop-filter: blur(4px);">Status</div>
            </div>
        </div>
    </div>
    
    <!-- Floating Tabs -->
    <div style="padding: 0 24px; margin-top: -24px; position: relative; z-index: 2;">
        <div style="display:flex; background: #fff; border-radius: 12px; padding: 6px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border: 1px solid #e2e8f0;">
            <div class="s360-tab active" onclick="switchS360Tab('today')" id="tab-today">Live Today</div>
            <div class="s360-tab" onclick="switchS360Tab('history')" id="tab-history">All-Time History</div>
        </div>
    </div>
    
    <!-- Content Body -->
    <div style="flex:1; overflow-y:auto; padding:24px;" id="s360-content">
        <!-- Live Today Tab -->
        <div id="s360-pane-today" class="s360-pane active">
            <h4 style="font-size:15px; font-weight:700; margin-bottom:20px; color:#1e293b; display:flex; align-items:center; gap:8px;">
                Today's Activity Timeline
            </h4>
            <div id="s360-timeline" class="s360-timeline-container">
                Loading...
            </div>
        </div>
        
        <!-- History Tab -->
        <div id="s360-pane-history" class="s360-pane" style="display:none;">
            <h4 style="font-size:15px; font-weight:700; margin-bottom:20px; color:#1e293b; display:flex; align-items:center; gap:8px;">
                Past 30 Days Attendance
            </h4>
            <div id="s360-history-table">
                Loading...
            </div>
        </div>
    </div>
</div>

<style>
/* Drawer Custom CSS */
.s360-close-btn { position:absolute; top:24px; right:24px; cursor:pointer; font-size:18px; color:rgba(255,255,255,0.6); background: rgba(255,255,255,0.05); width: 36px; height: 36px; display:flex; align-items:center; justify-content:center; border-radius: 50%; transition: 0.2s; }
.s360-close-btn:hover { background: rgba(255,255,255,0.15); color: #fff; transform: scale(1.05); }

.s360-back-btn { display:inline-flex; align-items:center; gap:6px; margin-bottom:20px; color:#94a3b8; font-size:13px; font-weight:600; cursor:pointer; transition:0.2s; padding: 6px 12px 6px 0; border-radius: 6px; }
.s360-back-btn:hover { color:#fff; transform: translateX(-3px); }

.s360-tab { flex:1; text-align:center; padding:10px 0; font-size:13.5px; font-weight:600; color:#64748b; cursor:pointer; border-radius: 8px; transition:all 0.2s; }
.s360-tab:hover { color:#0f172a; }
.s360-tab.active { color:#0f172a; background:#f1f5f9; box-shadow: inset 0 2px 4px rgba(0,0,0,0.02); }

.s360-timeline-container { border-left:2px dashed #cbd5e1; margin-left:14px; padding-left:24px; position:relative; }
.s360-tl-item { position:relative; margin-bottom:24px; }
.s360-tl-item::before { content:''; position:absolute; left:-31px; top:4px; width:12px; height:12px; border-radius:50%; background:#fff; border:3px solid #3b82f6; box-shadow: 0 0 0 4px #f8fafc; transition: 0.3s; }
.s360-tl-item:hover::before { transform: scale(1.2); background: #3b82f6; }

.s360-tl-item.shift-start::before { border-color: #10b981; }
.s360-tl-item.shift-end::before { border-color: #ef4444; }

.s360-tl-time { font-size:11.5px; font-weight:700; color:#64748b; margin-bottom:6px; display:inline-block; background: #e2e8f0; padding: 2px 8px; border-radius: 12px; }
.s360-tl-card { background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:16px; box-shadow:0 2px 5px rgba(0,0,0,0.02); transition: 0.3s; position: relative; overflow: hidden; }
.s360-tl-card:hover { transform: translateY(-3px); box-shadow:0 8px 15px rgba(0,0,0,0.05); border-color: #cbd5e1; }

.hist-row { display:flex; justify-content:space-between; padding:16px; background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; margin-bottom: 12px; box-shadow: 0 2px 5px rgba(0,0,0,0.02); transition: 0.2s; }
.hist-row:hover { border-color: #cbd5e1; transform: translateY(-2px); box-shadow:0 6px 12px rgba(0,0,0,0.05); }
.hist-date { font-weight: 700; color: #1e293b; font-size: 14px; margin-bottom: 4px; }
.hist-stat { font-size: 12.5px; color: #64748b; font-weight: 500; }
</style>

<script>
function openStaff360(username) {
    document.getElementById('s360-overlay').style.display = 'block';
    document.getElementById('s360-drawer').style.right = '0';
    loadStaff360(username);
}

function closeStaff360() {
    document.getElementById('s360-overlay').style.display = 'none';
    document.getElementById('s360-drawer').style.right = '-600px';
}

function switchS360Tab(tab) {
    document.querySelectorAll('.s360-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.s360-pane').forEach(p => p.style.display = 'none');
    document.getElementById('tab-' + tab).classList.add('active');
    document.getElementById('s360-pane-' + tab).style.display = 'block';
}

async function loadStaff360(username) {
    document.getElementById('s360-timeline').innerHTML = '<p>Loading...</p>';
    document.getElementById('s360-history-table').innerHTML = '<p>Loading...</p>';
    
    try {
        const res = await fetch(`?api=get_staff_360&username=${username}`);
        const data = await res.json();
        
        // Profile mapping
        const p = data.profile;
        document.getElementById('s360-name').innerText = p.full_name || username;
        if (p.photo_path) {
            document.getElementById('s360-avatar').innerHTML = `<img src="uploads/employees/${p.photo_path}" style="width:100%; height:100%; object-fit:cover; border-radius:50%;">`;
        } else {
            document.getElementById('s360-avatar').innerHTML = (p.full_name || username).charAt(0).toUpperCase();
        }
        document.getElementById('s360-role').innerText = p.role + " | " + username;
        
        let badge = '';
        // Base badge on actual attendance data rather than just users table status
        let isOnDuty = (data.today_attendance && data.today_attendance.punch_in && !data.today_attendance.punch_out);
        if (isOnDuty) {
            badge = `<span style="color:#4ade80;">On Duty</span>`;
        } else {
            badge = `<span style="color:#f87171;">Off Duty</span>`;
        }
        
        if (p.current_battery) badge += ` &nbsp;|&nbsp; 🔋 ${p.current_battery}`;
        document.getElementById('s360-badge').innerHTML = badge;
        
        // Today's Timeline
        let tlHtml = '';
        if (data.today_attendance && data.today_attendance.punch_in) {
            tlHtml += `
                <div class="s360-tl-item shift-start">
                    <div class="s360-tl-time">${data.today_attendance.punch_in.split(' ')[1]}</div>
                    <div class="s360-tl-card" style="background: linear-gradient(to right, #ecfdf5, #fff); border-left:4px solid #10b981;">
                        <div style="font-weight:700; color:#047857; font-size: 14.5px;">Shift Started (Punched In)</div>
                    </div>
                </div>
            `;
        }
        
        if (data.today_visits && data.today_visits.length > 0) {
            data.today_visits.forEach(v => {
                tlHtml += `
                    <div class="s360-tl-item">
                        <div class="s360-tl-time">${v.check_in_time || 'Unknown Time'}</div>
                        <div class="s360-tl-card" style="border-left:4px solid #3b82f6;">
                            <div style="font-weight:800; color:#0f172a; margin-bottom:6px; font-size: 15px;">${v.firm_name}</div>
                            <div style="display:flex; align-items:center; gap:12px; font-size:12.5px; color:#475569; margin-bottom:8px; font-weight:500;">
                                <span>${v.person_name}</span>
                                <span>${v.mobile}</span>
                            </div>
                            ${v.verified_address ? `<div style="font-size:12px; margin-top:8px; color:#64748b; background:#f1f5f9; padding:8px; border-radius:6px; display:inline-block;">${v.verified_address}</div>` : ''}
                            ${v.audio_path ? `<div style="margin-top:12px; border-top:1px dashed #e2e8f0; padding-top:12px;"><div style="font-size:11px; font-weight:700; color:#94a3b8; margin-bottom:4px; text-transform:uppercase;">Voice Note</div><audio controls style="width:100%; height:30px; outline:none;"><source src="${v.audio_path}"></audio></div>` : ''}
                        </div>
                    </div>
                `;
            });
        }
        
        if (data.today_attendance && data.today_attendance.punch_out) {
            tlHtml += `
                <div class="s360-tl-item shift-end">
                    <div class="s360-tl-time">${data.today_attendance.punch_out.split(' ')[1]}</div>
                    <div class="s360-tl-card" style="background: linear-gradient(to right, #fef2f2, #fff); border-left:4px solid #ef4444;">
                        <div style="font-weight:700; color:#b91c1c; font-size: 14.5px;">Shift Ended (Punched Out)</div>
                    </div>
                </div>
            `;
        }
        
        if (tlHtml === '') tlHtml = '<div style="text-align:center; padding:40px 0; color:#94a3b8;"><div style="font-size:14px; font-weight:500;">No activity logged today.</div></div>';
        document.getElementById('s360-timeline').innerHTML = tlHtml;
        
        // History Table
        let hHtml = '';
        if (data.history && data.history.length > 0) {
            data.history.forEach(h => {
                hHtml += `
                    <div class="hist-row">
                        <div>
                            <div class="hist-date">${h.att_date}</div>
                            <div class="hist-stat" style="display:flex; gap:12px; margin-top:6px;">
                                <span>${h.punch_in ? h.punch_in.split(' ')[1] : '--:--'}</span>
                                <span>${h.punch_out ? h.punch_out.split(' ')[1] : 'Active'}</span>
                            </div>
                        </div>
                        <div style="text-align:right;">
                            <div style="font-weight:800; color:#3b82f6; font-size:15px; margin-bottom:4px;">${h.duration || '0h 0m'}</div>
                            <div style="font-size:11.5px; font-weight:600; color:#94a3b8; background:#f1f5f9; padding:2px 8px; border-radius:10px;">${h.total_distance || '0.0'} km</div>
                        </div>
                    </div>
                `;
            });
        } else {
            hHtml = '<div style="text-align:center; padding:40px 0; color:#94a3b8;"><div style="font-size:14px; font-weight:500;">No past attendance found.</div></div>';
        }
        document.getElementById('s360-history-table').innerHTML = hHtml;
        
    } catch (e) {
        document.getElementById('s360-timeline').innerHTML = '<p style="color:red;">Error loading data.</p>';
        console.error(e);
    }
}
</script>

<!-- Route Map Modal -->
<div id="route-modal" class="modal" style="z-index: 100000; display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.6); align-items:center; justify-content:center; backdrop-filter: blur(4px);">
    <div class="modal-content" style="max-width:800px; width:95%; background:#fff; border-radius:12px; overflow:hidden;">
        <div class="modal-header" style="background:var(--primary); padding:16px 24px; color:#fff; display:flex; justify-content:space-between; align-items:center;">
            <h2 id="route-title" style="margin:0; font-size:16px; display:flex; align-items:center; gap:8px;"><i data-lucide="route" style="color:#fff;"></i> Today's Route Trail</h2>
            <span class="close" onclick="closeRouteMap()" style="cursor:pointer; color:#fff; font-size:24px;">&times;</span>
        </div>
        <div class="modal-body" style="padding:0;">
            <div id="big-route-map" style="height: 500px; width: 100%;"></div>
        </div>
    </div>
</div>

<!-- Leaflet JS for Polyline Map -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    let routeMap = null;
    let routePolyline = null;
    let markers = [];

    function viewRouteMap(staffName, points) {
        document.getElementById('route-title').innerHTML = `<i data-lucide="route" style="color:#fff;"></i> ${staffName}'s Route Trail`;
        document.getElementById('route-modal').style.display = 'flex';
        
        if (!routeMap) {
            routeMap = L.map('big-route-map');
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(routeMap);
        }
        
        // Clear previous
        if(routePolyline) { routeMap.removeLayer(routePolyline); }
        markers.forEach(m => routeMap.removeLayer(m));
        markers = [];
        
        if (points && points.length > 0) {
            // Draw Polyline
            routePolyline = L.polyline(points, {color: '#ef4444', weight: 4, opacity: 0.8}).addTo(routeMap);
            
            // Start Marker (Green)
            const startIcon = L.divIcon({className: 'custom-div-icon', html: "<div style='background-color:#10b981; width:12px; height:12px; border-radius:50%; border:2px solid #fff; box-shadow:0 0 4px rgba(0,0,0,0.5);'></div>", iconSize: [16, 16], iconAnchor: [8, 8]});
            markers.push(L.marker(points[0], {icon: startIcon}).addTo(routeMap).bindPopup('<b>Start Point</b><br>First ping today.'));
            
            // End Marker (Blue)
            const endIcon = L.divIcon({className: 'custom-div-icon', html: "<div style='background-color:#3b82f6; width:12px; height:12px; border-radius:50%; border:2px solid #fff; box-shadow:0 0 4px rgba(0,0,0,0.5);'></div>", iconSize: [16, 16], iconAnchor: [8, 8]});
            markers.push(L.marker(points[points.length-1], {icon: endIcon}).addTo(routeMap).bindPopup('<b>Last Point</b><br>Latest ping today.'));
            
            routeMap.fitBounds(routePolyline.getBounds(), {padding: [50, 50]});
        }
        
        setTimeout(() => routeMap.invalidateSize(), 300);
    }
    
    function closeRouteMap() {
        document.getElementById('route-modal').style.display = 'none';
    }
    
    // Auto refresh the page every 60 seconds to see live pings
    setInterval(() => {
        if(document.getElementById('route-modal').style.display !== 'flex' && 
           document.getElementById('s360-overlay').style.display !== 'block') {
            window.location.reload();
        }
    }, 60000);
</script>

<?php require_once 'footer.php'; ?>
