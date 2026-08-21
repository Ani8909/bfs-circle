import os
import re

file_path = r'c:\Users\pc\Downloads\client mgmt2\admin_tracking.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# Fix auto-refresh closing the drawer
target_refresh = """    // Auto refresh the page every 60 seconds to see live pings
    setInterval(() => {
        if(document.getElementById('route-modal').style.display !== 'flex') {
            window.location.reload();
        }
    }, 60000);"""
repl_refresh = """    // Auto refresh the page every 60 seconds to see live pings
    setInterval(() => {
        if(document.getElementById('route-modal').style.display !== 'flex' && 
           document.getElementById('s360-overlay').style.display !== 'block') {
            window.location.reload();
        }
    }, 60000);"""
content = content.replace(target_refresh, repl_refresh)


# Fix avatar rendering
target_avatar = """        const p = data.profile;
        document.getElementById('s360-name').innerText = p.full_name || username;
        document.getElementById('s360-avatar').innerText = (p.full_name || username).charAt(0).toUpperCase();
        document.getElementById('s360-role').innerText = p.role + " | " + username;"""
repl_avatar = """        const p = data.profile;
        document.getElementById('s360-name').innerText = p.full_name || username;
        if (p.photo_path) {
            document.getElementById('s360-avatar').innerHTML = `<img src="uploads/employees/${p.photo_path}" style="width:100%; height:100%; object-fit:cover; border-radius:50%;">`;
        } else {
            document.getElementById('s360-avatar').innerHTML = (p.full_name || username).charAt(0).toUpperCase();
        }
        document.getElementById('s360-role').innerText = p.role + " | " + username;"""
content = content.replace(target_avatar, repl_avatar)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Fixed auto-refresh and photo logic")
