import os

file_path = r'c:\Users\pc\Downloads\client mgmt2\api.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

target1 = """                $upd = $db->prepare("UPDATE users SET last_ping = CURRENT_TIMESTAMP, current_lat = ?, current_lon = ?, current_battery = ?, current_status = ? WHERE username = ?");
                $upd->execute([$lat, $lon, $bat, $status, $u]);"""
repl1 = """                $now = date('Y-m-d H:i:s');
                $upd = $db->prepare("UPDATE users SET last_ping = ?, current_lat = ?, current_lon = ?, current_battery = ?, current_status = ? WHERE username = ?");
                $upd->execute([$now, $lat, $lon, $bat, $status, $u]);"""

target2 = """                $ins = $db->prepare("INSERT INTO staff_attendance (username, att_date, punch_in, status) VALUES (?, ?, CURRENT_TIMESTAMP, 'Working')");
                $ins->execute([$u, $today]);"""
repl2 = """                $now = date('Y-m-d H:i:s');
                $ins = $db->prepare("INSERT INTO staff_attendance (username, att_date, punch_in, status) VALUES (?, ?, ?, 'Working')");
                $ins->execute([$u, $today, $now]);"""

target3 = """            $upd = $db->prepare("UPDATE staff_attendance SET punch_out = CURRENT_TIMESTAMP, status = 'Completed' WHERE username = ? AND att_date = ?");
            $upd->execute([$u, $today]);"""
repl3 = """            $now = date('Y-m-d H:i:s');
            $upd = $db->prepare("UPDATE staff_attendance SET punch_out = ?, status = 'Completed' WHERE username = ? AND att_date = ?");
            $upd->execute([$now, $u, $today]);"""

content = content.replace(target1, repl1)
content = content.replace(target2, repl2)
content = content.replace(target3, repl3)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Fixed timezone mismatch in api.php")
