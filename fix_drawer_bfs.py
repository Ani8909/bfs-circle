import os
import re

file_path = r'c:\Users\pc\Downloads\client mgmt2\admin_tracking.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

target = """        <!-- BFS Branding -->
        <div style="display:inline-flex; align-items:center; justify-content:center; width:44px; height:44px; border-radius:50%; background:linear-gradient(135deg, #3b82f6, #2563eb); box-shadow:0 4px 10px rgba(59, 130, 246, 0.4); color:#fff; font-size:14px; font-weight:800; letter-spacing:1px; margin-bottom:20px; border:2px solid rgba(255,255,255,0.2);">
            BFS
        </div>"""

repl = ""

content = content.replace(target, repl)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Removed BFS from drawer")
