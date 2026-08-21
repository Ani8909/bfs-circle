import os
import re

file_path = r'c:\Users\pc\Downloads\client mgmt2\admin_tracking.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Add clickable-row CSS
target_css = ".tracking-table tr:last-child td { border-bottom: none; }"
repl_css = ".tracking-table tr:last-child td { border-bottom: none; }\n    .tracking-table tbody tr { transition: all 0.2s; cursor: pointer; }\n    .tracking-table tbody tr:hover { background: #f8fafc; transform: scale(1.01); box-shadow: 0 4px 12px rgba(0,0,0,0.05); }"
content = content.replace(target_css, repl_css)

# 2. Remove Action Header
content = re.sub(r'<th>Action</th>', '', content)

# 3. Add onclick to tr
# The row starts with `<tr>` right after `?>`
target_tr = """                  ?>
                  <tr>
                      <td>"""
repl_tr = """                  ?>
                  <tr onclick="openStaff360('<?= $uname ?>')">
                      <td>"""
content = content.replace(target_tr, repl_tr)

# 4. Remove Action column data
target_td = re.compile(r'<td>[\s\n]*<\?php if\(count\(\$logs\) > 0\): \?>[\s\n]*<button class="btn-route".*?</button>[\s\n]*<\?php else: \?>[\s\n]*<button class="btn-route".*?</button>[\s\n]*<\?php endif; \?>[\s\n]*</td>', re.DOTALL)
content = target_td.sub('', content)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Removed Action column, made row clickable")
