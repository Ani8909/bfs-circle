import os

api_path = r'c:\Users\pc\Downloads\client mgmt2\api.php'
with open(api_path, 'r', encoding='utf-8') as f:
    content = f.read()

# Add new case get_staff_360
new_endpoint = """        case 'get_staff_360':
            $username = trim($_GET['username'] ?? '');
            if (!$username) return_json(['error' => 'Missing username'], 400);

            // Basic Profile
            $stmt = $db->prepare("SELECT full_name, username, role, current_status, current_battery, current_lat, current_lon, last_ping FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $profile = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$profile) return_json(['error' => 'Staff not found'], 404);

            $today = date('Y-m-d');

            // Today's Attendance
            $att_stmt = $db->prepare("SELECT * FROM staff_attendance WHERE username = ? AND att_date = ? ORDER BY id DESC LIMIT 1");
            $att_stmt->execute([$username, $today]);
            $today_att = $att_stmt->fetch(PDO::FETCH_ASSOC);

            // Today's Field Visits
            $visit_stmt = $db->prepare("SELECT firm_name, person_name, mobile, check_in_time, check_out_time, verified_address, audio_path, remarks FROM field_visits WHERE executive_name = ? AND visit_date = ? ORDER BY id ASC");
            $visit_stmt->execute([$username, $today]);
            $today_visits = $visit_stmt->fetchAll(PDO::FETCH_ASSOC);

            // Attendance History (Last 30 Days)
            $hist_stmt = $db->prepare("SELECT att_date, punch_in, punch_out, total_distance FROM staff_attendance WHERE username = ? AND att_date <= ? ORDER BY att_date DESC LIMIT 30");
            $hist_stmt->execute([$username, $today]);
            $history = $hist_stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Format history
            $formatted_history = [];
            foreach ($history as $h) {
                $dur = "-";
                if ($h['punch_in']) {
                    $p_in = strtotime($h['punch_in']);
                    $p_out = $h['punch_out'] ? strtotime($h['punch_out']) : ($h['att_date'] == $today ? time() : strtotime($h['att_date'] . ' 18:00:00'));
                    $sec = max(0, $p_out - $p_in);
                    $hrs = floor($sec / 3600);
                    $mins = floor(($sec % 3600) / 60);
                    $dur = "{$hrs}h {$mins}m";
                }
                $h['duration'] = $dur;
                $formatted_history[] = $h;
            }

            return_json([
                'profile' => $profile,
                'today_attendance' => $today_att,
                'today_visits' => $today_visits,
                'history' => $formatted_history
            ]);
            break;

        case 'get_employee_productivity':"""

content = content.replace("        case 'get_employee_productivity':", new_endpoint)

with open(api_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Added get_staff_360 to api.php")
