import os

file_path = r'c:\Users\pc\Downloads\client mgmt2\staff\index.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

target = """                    <?php if ($is_punched_in): ?>
                        <div style="display:flex; flex-direction:column;">
                            <div style="display:flex; align-items:center;"><div class="pulse-live"></div> <span id="duty-text">On Duty (Live)</span></div>
                            <div id="shift_timer" style="font-size:11px; font-weight:700; color:#10b981; margin-top:2px; padding-left:14px; font-variant-numeric:tabular-nums;">Shift: 00:00:00</div>
                        </div>
                    <?php else: ?>"""

repl = """                    <?php if ($is_punched_in): ?>
                        <div style="display:flex; align-items:center;">
                            <div class="pulse-live"></div> 
                            <span id="duty-text" style="font-weight:600; color:#fff;">On Duty</span>
                            <span style="color:rgba(255,255,255,0.5); margin:0 6px;">|</span>
                            <i class="far fa-clock" style="font-size:11px; color:rgba(255,255,255,0.8); margin-right:4px;"></i>
                            <span id="shift_timer" style="font-size:13px; font-weight:700; color:#fff; font-variant-numeric:tabular-nums; letter-spacing:0.5px;">00:00:00</span>
                        </div>
                    <?php else: ?>"""

content = content.replace(target, repl)

# Also update the JS to output just the time
target_js = """                timerEl.innerText = `Shift: ${hrs}:${mins}:${secs}`;"""
repl_js = """                timerEl.innerText = `${hrs}:${mins}:${secs}`;"""

content = content.replace(target_js, repl_js)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Beautified the timer display")
