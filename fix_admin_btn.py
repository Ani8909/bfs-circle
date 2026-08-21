import os
import re

file_path = r'c:\Users\pc\Downloads\client mgmt2\admin_tracking.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# Replace the "View Route Map" button
content = re.sub(
    r'<button class="btn-route" onclick="viewRouteMap\([^>]+>[\s\n]*<i data-lucide="map"></i> View Route Map[\s\n]*</button>',
    r'<button class="btn-route" style="background:#4f46e5;" onclick="openStaff360(\'<?= $uname ?>\')"><i class="fas fa-id-card"></i> 360° Profile</button>',
    content
)

# Replace the "No Data Today" button if it exists
content = re.sub(
    r'<button class="btn-route" style="background:#94a3b8; cursor:not-allowed;" disabled>[\s\n]*<i data-lucide="map-pin-off"></i> No Data Today[\s\n]*</button>',
    r'<button class="btn-route" style="background:#4f46e5;" onclick="openStaff360(\'<?= $uname ?>\')"><i class="fas fa-id-card"></i> 360° Profile</button>',
    content
)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Fixed buttons in admin_tracking.php")
