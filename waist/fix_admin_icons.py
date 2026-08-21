import os
import re

file_path = r'c:\Users\pc\Downloads\client mgmt2\admin_tracking.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# Fix the Close Button (X)
target_close = '<i class="fas fa-times s360-close-btn" onclick="closeStaff360()"></i>'
repl_close = '''<div class="s360-close-btn" onclick="closeStaff360()">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </div>'''
content = content.replace(target_close, repl_close)

# Fix the Back Button (Arrow)
target_back = '<i class="fas fa-arrow-left"></i>'
repl_back = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>'
content = content.replace(target_back, repl_back)

# For dynamic JS icons that failed to render (FontAwesome wasn't loaded), let's just remove the <i> tags to keep it clean, 
# or replace them with SVGs where important. I'll just remove them to keep the text clean as it looks good in the screenshot.

content = re.sub(r'<i class="fas fa-[^"]+.*?></i> ', '', content)
content = re.sub(r'<i class="far fa-[^"]+.*?></i> ', '', content)
content = re.sub(r'<i class="fas fa-[^"]+.*?></i>', '', content)
content = re.sub(r'<i class="far fa-[^"]+.*?></i>', '', content)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Fixed icons in admin_tracking.php")
