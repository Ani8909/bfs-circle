import os

file_path = r'c:\Users\pc\Downloads\client mgmt2\api.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

target = """        case 'punch_in':
            if (!isset($_SESSION['username'])) exit;
            $u = $_SESSION['username'];
            $today = date('Y-m-d');
            $stmt = $db->prepare("SELECT id FROM staff_attendance WHERE username = ? AND att_date = ?");
            $stmt->execute([$u, $today]);
            if (!$stmt->fetch()) {
                $now = date('Y-m-d H:i:s');
                $ins = $db->prepare("INSERT INTO staff_attendance (username, att_date, punch_in, status) VALUES (?, ?, ?, 'Working')");
                $ins->execute([$u, $today, $now]);
            }
            echo json_encode(['success' => true]);
            break;"""

repl = """        case 'punch_in':
            if (!isset($_SESSION['username'])) exit;
            $u = $_SESSION['username'];
            $today = date('Y-m-d');
            $now = date('Y-m-d H:i:s');
            
            $stmt = $db->prepare("SELECT id FROM staff_attendance WHERE username = ? AND att_date = ?");
            $stmt->execute([$u, $today]);
            if (!$stmt->fetch()) {
                $ins = $db->prepare("INSERT INTO staff_attendance (username, att_date, punch_in, status) VALUES (?, ?, ?, 'Working')");
                $ins->execute([$u, $today, $now]);
            } else {
                $upd = $db->prepare("UPDATE staff_attendance SET punch_out = NULL, status = 'Working' WHERE username = ? AND att_date = ?");
                $upd->execute([$u, $today]);
            }
            
            $u_upd = $db->prepare("UPDATE users SET current_status = 'Online' WHERE username = ?");
            $u_upd->execute([$u]);
            
            echo json_encode(['success' => true]);
            break;"""

content = content.replace(target, repl)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Fixed punch_in resume logic")
