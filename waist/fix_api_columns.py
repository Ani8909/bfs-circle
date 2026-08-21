import os

api_path = r'c:\Users\pc\Downloads\client mgmt2\api.php'
with open(api_path, 'r', encoding='utf-8') as f:
    content = f.read()

target = """            // Basic Profile
            $stmt = $db->prepare("SELECT full_name, username, role, current_status, current_battery, current_lat, current_lon, last_ping FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $profile = $stmt->fetch(PDO::FETCH_ASSOC);"""

repl = """            // Basic Profile
            $stmt = $db->prepare("SELECT name as full_name, username, role, current_status FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $profile = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Get live tracking data if available
            $track_stmt = $db->prepare("SELECT battery as current_battery, latitude as current_lat, longitude as current_lon, updated_at as last_ping FROM staff_location_logs WHERE username = ? ORDER BY id DESC LIMIT 1");
            $track_stmt->execute([$username]);
            if ($track = $track_stmt->fetch(PDO::FETCH_ASSOC)) {
                $profile = array_merge($profile, $track);
            }"""

content = content.replace(target, repl)

with open(api_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Fixed api.php query")
