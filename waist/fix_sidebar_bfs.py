import os
import re

file_path = r'c:\Users\pc\Downloads\client mgmt2\header.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

target = """        <div class="brand-container" style="display:flex; align-items:center; gap: 14px; padding: 24px 20px;">
            <div style="display: flex; align-items: center; justify-content: center; background: transparent; width: auto; height: auto;">
                <img src="logo.png" alt="BFS Financial Services Logo" style="height: 40px; width: auto; object-fit: contain; filter: brightness(0) invert(1) drop-shadow(0 2px 8px rgba(255,255,255,0.2)); transition: transform 0.3s ease;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
            </div>
            <div class="brand-name" style="font-family: 'Outfit', sans-serif; font-size: 24px; font-weight: 800; color: #ffffff; letter-spacing: -0.5px; margin-left: 8px;">BFS Financial Services</div>
        </div>"""

repl = """        <div class="brand-container" style="display:flex; align-items:center; justify-content:center; padding: 24px 20px; border-bottom: 1px solid rgba(255,255,255,0.05);">
            <div style="display:inline-flex; align-items:center; justify-content:center; width:64px; height:64px; border-radius:50%; background:linear-gradient(135deg, #3b82f6, #2563eb); box-shadow:0 4px 15px rgba(59, 130, 246, 0.4); color:#fff; font-size:20px; font-weight:900; letter-spacing:1px; border:3px solid rgba(255,255,255,0.15);">
                BFS
            </div>
        </div>"""

content = content.replace(target, repl)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Updated Sidebar to BFS Circle Logo.")
