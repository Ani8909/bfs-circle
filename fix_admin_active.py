import os

# 1. Update admin_tracking.php
file_path_admin = r'c:\Users\pc\Downloads\client mgmt2\admin_tracking.php'
with open(file_path_admin, 'r', encoding='utf-8') as f:
    content_admin = f.read()

target_admin = """                    $diff_mins = $last_ping > 0 ? round((time() - $last_ping) / 60) : 999;
                    $is_active = $diff_mins <= 10;"""

repl_admin = """                    $diff_mins = $last_ping > 0 ? round((time() - $last_ping) / 60) : 999;
                    // Strict rule: if punched out (Offline), force inactive immediately. Otherwise, check 10 min ping window.
                    $is_active = ($st['current_status'] !== 'Offline' && $diff_mins <= 10);"""

content_admin = content_admin.replace(target_admin, repl_admin)

with open(file_path_admin, 'w', encoding='utf-8') as f:
    f.write(content_admin)


# 2. Add Shift Timer to staff/index.php
file_path_staff = r'c:\Users\pc\Downloads\client mgmt2\staff\index.php'
with open(file_path_staff, 'r', encoding='utf-8') as f:
    content_staff = f.read()

# Add punch_in_time to PHP variables
target_staff_php = """$is_punched_in = $attendance && !$attendance['punch_out'];
$is_punched_out = $attendance && $attendance['punch_out'];"""
repl_staff_php = """$is_punched_in = $attendance && !$attendance['punch_out'];
$is_punched_out = $attendance && $attendance['punch_out'];
$punch_in_time = $attendance['punch_in'] ?? '';
"""
content_staff = content_staff.replace(target_staff_php, repl_staff_php)

# Update UI to show Shift Timer right inside the duty-container
target_staff_ui = """                    <?php if ($is_punched_in): ?>
                        <div class="pulse-live"></div> <span id="duty-text">On Duty (Live)</span>
                    <?php else: ?>"""

repl_staff_ui = """                    <?php if ($is_punched_in): ?>
                        <div style="display:flex; flex-direction:column;">
                            <div style="display:flex; align-items:center;"><div class="pulse-live"></div> <span id="duty-text">On Duty (Live)</span></div>
                            <div id="shift_timer" style="font-size:11px; font-weight:700; color:#10b981; margin-top:2px; padding-left:14px; font-variant-numeric:tabular-nums;">Shift: 00:00:00</div>
                        </div>
                    <?php else: ?>"""
content_staff = content_staff.replace(target_staff_ui, repl_staff_ui)

# Add Javascript to run the timer
target_staff_js = """        window.TRACKING_ACTIVE = <?= $is_punched_in ? 'true' : 'false' ?>;
        </script>"""

repl_staff_js = """        window.TRACKING_ACTIVE = <?= $is_punched_in ? 'true' : 'false' ?>;
        
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
                timerEl.innerText = `Shift: ${hrs}:${mins}:${secs}`;
            }
            setInterval(updateShiftTimer, 1000);
            updateShiftTimer();
        })();
        <?php endif; ?>
        
        </script>"""

content_staff = content_staff.replace(target_staff_js, repl_staff_js)

with open(file_path_staff, 'w', encoding='utf-8') as f:
    f.write(content_staff)

print("Updated Admin logic and Staff Timer")
