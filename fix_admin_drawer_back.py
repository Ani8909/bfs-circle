import os
import re

file_path = r'c:\Users\pc\Downloads\client mgmt2\admin_tracking.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

target = """<i class="fas fa-times" onclick="closeStaff360()" style="position:absolute; top:20px; right:20px; cursor:pointer; font-size:18px; color:rgba(255,255,255,0.7);"></i>"""
repl = """<!-- Top right close icon -->
        <i class="fas fa-times" onclick="closeStaff360()" style="position:absolute; top:20px; right:20px; cursor:pointer; font-size:20px; color:#fff; background: rgba(255,255,255,0.1); padding: 8px 12px; border-radius: 8px;"></i>
        <!-- Back button at the top left -->
        <div onclick="closeStaff360()" style="display:inline-flex; align-items:center; gap:6px; margin-bottom:16px; color:#94a3b8; font-size:13px; font-weight:600; cursor:pointer; transition:0.2s;">
            <i class="fas fa-arrow-left"></i> Back to Radar
        </div>"""

content = content.replace(target, repl)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Added back button to 360 drawer")
