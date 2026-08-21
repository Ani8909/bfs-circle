import os

footer_path = r'c:\Users\pc\Downloads\client mgmt2\footer.php'
with open(footer_path, 'r', encoding='utf-8') as f:
    footer = f.read()

target = """    <!-- 10x Field Force Tracker Engine -->
    <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'Staff'): ?>
    <script>
        (function() {"""

repl = """    <!-- 10x Field Force Tracker Engine (Strict Enforcement + WakeLock) -->
    <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'Staff'): ?>
    <div id="gps-blocker" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.95); z-index:999999; flex-direction:column; align-items:center; justify-content:center; color:white; padding:20px; text-align:center;">
        <i class="fas fa-map-marker-slash" style="font-size:60px; color:#ef4444; margin-bottom:20px;"></i>
        <h2 style="margin-bottom:10px;">Location is Disabled!</h2>
        <p style="margin-bottom:20px; font-size:14px; opacity:0.8; max-width:300px;">You are currently <b>On Duty</b>. Company policy requires live GPS tracking to be active. Please enable your Location (GPS) and grant permission to continue using the app.</p>
        <button onclick="window.location.reload()" style="background:#22c55e; color:white; padding:12px 24px; border:none; border-radius:8px; font-weight:bold; font-size:16px;">I Have Enabled GPS - Refresh</button>
    </div>

    <script>
        (function() {"""

# Replace tracker logic
target2 = """            function fallbackFetch(lat, lon, bat) {
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
                // Respect Punch-In status
                if (typeof window.TRACKING_ACTIVE !== 'undefined' && window.TRACKING_ACTIVE === false) {
                    return; // Do not track if not punched in
                }
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
        })();"""

repl2 = """            let wakeLock = null;
            async function requestWakeLock() {
                try {
                    if ('wakeLock' in navigator) {
                        wakeLock = await navigator.wakeLock.request('screen');
                        wakeLock.addEventListener('release', () => { console.log('Wake Lock released'); });
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
                
                // Determine root path automatically. Use ?api=staff_ping if in root, else ../?api=staff_ping
                let basePath = window.location.pathname.includes('/staff/') ? '../' : './';
                fetch(basePath + '?api=staff_ping', { method: 'POST', body: fd }).catch(e=>console.log(e));
            }

            function showGpsBlocker() {
                document.getElementById('gps-blocker').style.display = 'flex';
            }
            function hideGpsBlocker() {
                document.getElementById('gps-blocker').style.display = 'none';
            }

            function startTracker() {
                // Respect Punch-In status
                if (typeof window.TRACKING_ACTIVE !== 'undefined' && window.TRACKING_ACTIVE === false) {
                    return; // Do not track if not punched in
                }
                
                requestWakeLock();
                document.addEventListener('visibilitychange', async () => {
                    if (wakeLock !== null && document.visibilityState === 'visible') {
                        requestWakeLock();
                    }
                });

                if (navigator.geolocation) {
                    // Watch position for continuous updates
                    navigator.geolocation.watchPosition(
                        async (pos) => {
                            hideGpsBlocker();
                            const lat = pos.coords.latitude;
                            const lon = pos.coords.longitude;
                            
                            const now = Date.now();
                            if (now - lastPingTime > 45000) { // Ping every 45s max
                                const bat = await getBattery();
                                fallbackFetch(lat, lon, bat);
                                lastPingTime = now;
                            }
                        },
                        (err) => {
                            console.log('Tracker GPS Error:', err);
                            showGpsBlocker(); // ENFORCE GPS!
                        },
                        { enableHighAccuracy: true, maximumAge: 10000, timeout: 5000 }
                    );
                    
                    // Force a ping every 2 mins even if stationary (in case watchPosition sleeps)
                    setInterval(() => {
                        navigator.geolocation.getCurrentPosition(
                            async (pos) => {
                                hideGpsBlocker();
                                const bat = await getBattery();
                                fallbackFetch(pos.coords.latitude, pos.coords.longitude, bat);
                                lastPingTime = Date.now();
                            },
                            (err) => {
                                showGpsBlocker();
                            }
                        );
                    }, 60000); // 1 minute stationary ping
                } else {
                    alert("Your browser does not support GPS tracking.");
                }
            }
            
            // Wait a few seconds before starting to not block page load
            setTimeout(startTracker, 2000);
        })();"""

footer = footer.replace(target, repl)
footer = footer.replace(target2, repl2)

with open(footer_path, 'w', encoding='utf-8') as f:
    f.write(footer)
print("Strict tracking engine applied to footer")
