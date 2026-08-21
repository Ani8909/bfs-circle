import os
import re

file_path = r'c:\Users\pc\Downloads\client mgmt2\admin_tracking.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# Replace the <tr> inside <tbody>
# We can find <tbody> and the <tr> inside it.
content = re.sub(r'<tbody>\s*(<\?php.*?\?>)\s*<tr>', r'<tbody>\n                  \1\n                  <tr class="clickable-row" onclick="openStaff360(\'<?= $uname ?>\')">', content, flags=re.DOTALL)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Fixed TR")
