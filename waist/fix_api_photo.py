import os
import re

api_path = r'c:\Users\pc\Downloads\client mgmt2\api.php'
with open(api_path, 'r', encoding='utf-8') as f:
    content = f.read()

target = """            // Basic Profile
            $stmt = $db->prepare("SELECT name as full_name, username, role, current_status FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $profile = $stmt->fetch(PDO::FETCH_ASSOC);"""

repl = """            // Basic Profile
            $stmt = $db->prepare("SELECT u.name as full_name, u.username, u.role, u.current_status, e.photo_path FROM users u LEFT JOIN employees e ON u.id = e.user_id WHERE u.username = ?");
            $stmt->execute([$username]);
            $profile = $stmt->fetch(PDO::FETCH_ASSOC);"""

content = content.replace(target, repl)

with open(api_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Fixed api.php join for photo")
