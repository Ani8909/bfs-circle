import os
import re

file_path = r'c:\Users\pc\Downloads\client mgmt2\admin_tracking.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

target = """        let badge = '';
        if (p.current_status === 'Online') badge = `<span style="color:#4ade80;"><i class="fas fa-circle"></i> On Duty</span>`;
        else badge = `<span style="color:#f87171;"><i class="fas fa-circle"></i> Off Duty</span>`;"""

repl = """        let badge = '';
        // Base badge on actual attendance data rather than just users table status
        let isOnDuty = (data.today_attendance && data.today_attendance.punch_in && !data.today_attendance.punch_out);
        if (isOnDuty) {
            badge = `<span style="color:#4ade80;"><i class="fas fa-circle"></i> On Duty</span>`;
        } else {
            badge = `<span style="color:#f87171;"><i class="fas fa-circle"></i> Off Duty</span>`;
        }"""

content = content.replace(target, repl)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Fixed badge logic in admin_tracking.php")
