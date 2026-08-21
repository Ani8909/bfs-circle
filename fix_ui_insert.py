import os

idx_path = r'c:\Users\pc\Downloads\client mgmt2\staff\index.php'
with open(idx_path, 'r', encoding='utf-8') as f:
    idx = f.read()

# Insert the UI block right before <div class="metrics-grid">
target = '    <div class="metrics-grid">'

ui_code = """
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
    window.TRACKING_ACTIVE = <?= $is_punched_in ? 'true' : 'false' ?>;
    </script>
    
    <div class="metrics-grid">"""

if '<!-- SMART TRACKING PUNCH-IN UI -->' not in idx:
    idx = idx.replace(target, ui_code)
    with open(idx_path, 'w', encoding='utf-8') as f:
        f.write(idx)
    print("UI Block inserted successfully!")
else:
    print("UI Block already exists!")
