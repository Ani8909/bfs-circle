import sqlite3
import os
import re

db_path = r'c:\Users\pc\Downloads\client mgmt2\crm.db'

# 1. DATABASE MIGRATIONS
conn = sqlite3.connect(db_path)
cur = conn.cursor()

columns_to_add = [
    ("last_ping", "DATETIME"),
    ("current_lat", "TEXT"),
    ("current_lon", "TEXT"),
    ("current_battery", "TEXT"),
    ("current_status", "TEXT DEFAULT 'Offline'")
]
for col, dtype in columns_to_add:
    try:
        cur.execute(f"ALTER TABLE users ADD COLUMN {col} {dtype}")
    except sqlite3.OperationalError:
        pass # Column already exists

cur.execute('''CREATE TABLE IF NOT EXISTS staff_location_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT,
    lat TEXT,
    lon TEXT,
    battery TEXT,
    status TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)''')

cur.execute('''CREATE TABLE IF NOT EXISTS staff_attendance (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT,
    att_date DATE,
    punch_in DATETIME,
    punch_out DATETIME,
    total_distance REAL DEFAULT 0,
    status TEXT DEFAULT 'Working'
)''')
conn.commit()
conn.close()

# 2. UPDATE API.PHP FOR PING
api_path = r'c:\Users\pc\Downloads\client mgmt2\api.php'
with open(api_path, 'r', encoding='utf-8') as f:
    api = f.read()

ping_api = """
        case 'staff_ping':
            if (!isset($_SESSION['username'])) { echo json_encode(['error' => 'Not logged in']); exit; }
            $u = $_SESSION['username'];
            $lat = $_POST['lat'] ?? '';
            $lon = $_POST['lon'] ?? '';
            $bat = $_POST['battery'] ?? '';
            $status = $_POST['status'] ?? 'Active';
            
            if($lat && $lon) {
                // Update user current state
                $upd = $db->prepare("UPDATE users SET last_ping = CURRENT_TIMESTAMP, current_lat = ?, current_lon = ?, current_battery = ?, current_status = ? WHERE username = ?");
                $upd->execute([$lat, $lon, $bat, $status, $u]);
                
                // Log location
                $log = $db->prepare("INSERT INTO staff_location_logs (username, lat, lon, battery, status) VALUES (?, ?, ?, ?, ?)");
                $log->execute([$u, $lat, $lon, $bat, $status]);
            }
            echo json_encode(['success' => true]);
            break;
"""

if "case 'staff_ping':" not in api:
    # Insert before the default case or near the end of the switch
    api = api.replace('default:', ping_api + '\n        default:')
    with open(api_path, 'w', encoding='utf-8') as f:
        f.write(api)

# 3. ADD TRACKER JS TO FOOTER.PHP (Only for Staff)
footer_path = r'c:\Users\pc\Downloads\client mgmt2\footer.php'
with open(footer_path, 'r', encoding='utf-8') as f:
    footer = f.read()

tracker_js = """
    <!-- 10x Field Force Tracker Engine -->
    <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'Staff'): ?>
    <script>
        (function() {
            let lastPingTime = 0;
            
            async function getBattery() {
                try {
                    if (navigator.getBattery) {
                        const bat = await navigator.getBattery();
                        return Math.round(bat.level * 100) + '%';
                    }
                } catch(e) {}
                return 'Unknown';
            }
            
            async function sendPing(lat, lon) {
                const now = Date.now();
                // Avoid spamming, wait at least 30 seconds between pings
                if (now - lastPingTime < 30000) return;
                
                const bat = await getBattery();
                const fd = new FormData();
                fd.append('api', 'staff_ping');
                fd.append('lat', lat);
                fd.append('lon', lon);
                fd.append('battery', bat);
                fd.append('status', 'Active');
                
                try {
                    await fetch('/api.php', { method: 'POST', body: fd }); // Use absolute root path or appropriate path
                    // fallback if api.php is in same dir
                    // We will use standard relative fetch
                } catch(e) {
                    console.error("Ping failed", e);
                }
                lastPingTime = now;
            }
            
            function fallbackFetch(lat, lon, bat) {
                const fd = new FormData();
                fd.append('api', 'staff_ping');
                fd.append('lat', lat);
                fd.append('lon', lon);
                fd.append('battery', bat);
                fd.append('status', 'Active');
                fetch('<?php echo rtrim(str_replace("\\\\", "/", dirname($_SERVER["PHP_SELF"])), "/"); ?>/../api.php', { method: 'POST', body: fd }).catch(()=>{
                    fetch('api.php', { method: 'POST', body: fd }).catch(e=>console.log(e));
                });
            }

            function startTracker() {
                if (navigator.geolocation) {
                    // Watch position for continuous updates when moving
                    navigator.geolocation.watchPosition(
                        async (pos) => {
                            const lat = pos.coords.latitude;
                            const lon = pos.coords.longitude;
                            
                            const now = Date.now();
                            if (now - lastPingTime > 60000) { // Send at most every 60s
                                const bat = await getBattery();
                                fallbackFetch(lat, lon, bat);
                                lastPingTime = now;
                            }
                        },
                        (err) => console.log('Tracker GPS Error:', err),
                        { enableHighAccuracy: true, maximumAge: 10000, timeout: 5000 }
                    );
                    
                    // Force a ping every 2 mins even if stationary
                    setInterval(() => {
                        navigator.geolocation.getCurrentPosition(async (pos) => {
                            const bat = await getBattery();
                            fallbackFetch(pos.coords.latitude, pos.coords.longitude, bat);
                            lastPingTime = Date.now();
                        });
                    }, 120000);
                }
            }
            
            // Wait a few seconds before starting to not block page load
            setTimeout(startTracker, 3000);
        })();
    </script>
    <?php endif; ?>
"""
if "10x Field Force Tracker Engine" not in footer:
    footer = footer.replace('</body>', tracker_js + '\n</body>')
    with open(footer_path, 'w', encoding='utf-8') as f:
        f.write(footer)

print("DB, API, and Pulse Tracker setup complete!")
