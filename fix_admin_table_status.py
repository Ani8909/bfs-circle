import os
import re

file_path = r'c:\Users\pc\Downloads\client mgmt2\admin_tracking.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

target = """                    // Determine Status
                    $last_ping = $st['last_ping'] ? strtotime($st['last_ping']) : 0;
                    $diff_mins = $last_ping > 0 ? round((time() - $last_ping) / 60) : 999;
                    // Strict rule: if punched out (Offline), force inactive immediately. Otherwise, check 10 min ping window.
                    $is_active = ($st['current_status'] !== 'Offline' && $diff_mins <= 10);
                    $status_class = $is_active ? 'status-active' : 'status-inactive';
                    $status_text = $is_active ? 'Active' : 'Inactive';
                    $dot_class = $is_active ? 'active' : 'inactive';"""

repl = """                    // Determine Status
                    $last_ping = $st['last_ping'] ? strtotime($st['last_ping']) : 0;
                    $diff_mins = $last_ping > 0 ? round((time() - $last_ping) / 60) : 999;
                    
                    // Fetch actual shift status from attendance today
                    $today = date('Y-m-d');
                    $att_check = $db->prepare("SELECT punch_out FROM staff_attendance WHERE username = ? AND att_date = ? ORDER BY id DESC LIMIT 1");
                    $att_check->execute([$uname, $today]);
                    $att = $att_check->fetch(PDO::FETCH_ASSOC);
                    
                    $is_on_duty = ($att !== false && $att['punch_out'] === null);
                    
                    if ($is_on_duty) {
                        if ($diff_mins <= 10) {
                            $status_class = 'status-active';
                            $status_text = 'On Duty (Active)';
                            $dot_class = 'active';
                        } else {
                            // On duty but GPS ping delayed
                            $status_class = 'status-active';
                            $status_text = 'On Duty (GPS Lost)';
                            $dot_class = 'inactive';
                        }
                    } else {
                        $status_class = 'status-inactive';
                        $status_text = 'Off Duty';
                        $dot_class = 'inactive';
                    }
                    
                    // We only need $is_active for the timer
                    $is_active = $is_on_duty;"""

content = content.replace(target, repl)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Fixed status ambiguity in admin_tracking.php")
