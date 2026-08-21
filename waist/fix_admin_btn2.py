import os
import re

file_path = r'c:\Users\pc\Downloads\client mgmt2\admin_tracking.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# Pattern 1
p1 = re.compile(r'<button class="btn-route" onclick="viewRouteMap[^>]+>.*?<\/button>', re.DOTALL)
content = p1.sub(r'<button class="btn-route" style="background:#4f46e5;" onclick="openStaff360(\'<?= $uname ?>\')"><i class="fas fa-id-card"></i> 360° Profile</button>', content)

# Pattern 2
p2 = re.compile(r'<button class="btn-route" style="opacity:0.5[^>]+>.*?<\/button>', re.DOTALL)
content = p2.sub(r'<button class="btn-route" style="background:#4f46e5;" onclick="openStaff360(\'<?= $uname ?>\')"><i class="fas fa-id-card"></i> 360° Profile</button>', content)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Fixed buttons properly")
