import os

staff_idx_path = r'c:\Users\pc\Downloads\client mgmt2\staff\index.php'
with open(staff_idx_path, 'r', encoding='utf-8') as f:
    idx = f.read()

target = """<button onclick="document.getElementById('dutyToggle').checked = false; hideGpsBlocker();" style="background:transparent; border:none; color:#94A3B8; font-size:14px; font-weight:600; cursor:pointer; padding:8px;">"""

repl = """<button onclick="document.getElementById('dutyToggle').checked = false; document.getElementById('gps-blocker').style.display='none';" style="background:transparent; border:none; color:#94A3B8; font-size:14px; font-weight:600; cursor:pointer; padding:8px;">"""

idx = idx.replace(target, repl)

with open(staff_idx_path, 'w', encoding='utf-8') as f:
    f.write(idx)
print("Fixed Cancel Button")
