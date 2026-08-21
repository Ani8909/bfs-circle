import os

staff_idx_path = r'c:\Users\pc\Downloads\client mgmt2\staff\index.php'
with open(staff_idx_path, 'r', encoding='utf-8') as f:
    idx = f.read()

tracker_html = """
    <!-- GPS Blocker Overlay -->
    <div id="gps-blocker" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.95); z-index:999999; flex-direction:column; align-items:center; justify-content:center; color:white; padding:20px; text-align:center;">
        <i class="fas fa-map-marker-slash" style="font-size:60px; color:#ef4444; margin-bottom:20px;"></i>
        <h2 style="margin-bottom:10px;">Location is Disabled!</h2>
        <p style="margin-bottom:20px; font-size:14px; opacity:0.8; max-width:300px;">You are currently <b>On Duty</b>. Company policy requires live GPS tracking to be active. Please enable your Location (GPS) and grant permission to continue using the app.</p>
        <button onclick="window.location.reload()" style="background:#22c55e; color:white; padding:12px 24px; border:none; border-radius:8px; font-weight:bold; font-size:16px;">I Have Enabled GPS - Refresh</button>
    </div>

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
</body>"""

target = "</body>"
idx = idx.replace(target, tracker_html)

with open(staff_idx_path, 'w', encoding='utf-8') as f:
    f.write(idx)
print("Injected tracker engine directly into staff/index.php")
