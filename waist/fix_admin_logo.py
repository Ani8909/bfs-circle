import os

file_path = r'c:\Users\pc\Downloads\client mgmt2\admin_tracking.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

target_back = """        <!-- Back button at the top left -->
        <div onclick="closeStaff360()" class="s360-back-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg> Back to Radar
        </div>"""

repl_back = """        <!-- BFS Branding -->
        <div style="display:inline-flex; align-items:center; justify-content:center; width:44px; height:44px; border-radius:50%; background:linear-gradient(135deg, #3b82f6, #2563eb); box-shadow:0 4px 10px rgba(59, 130, 246, 0.4); color:#fff; font-size:14px; font-weight:800; letter-spacing:1px; margin-bottom:20px; border:2px solid rgba(255,255,255,0.2);">
            BFS
        </div>"""

content = content.replace(target_back, repl_back)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Replaced Back to Radar with BFS circle logo.")
