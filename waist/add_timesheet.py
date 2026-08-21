import os

# Update API
api_path = r'c:\Users\pc\Downloads\client mgmt2\api.php'
with open(api_path, 'r', encoding='utf-8') as f:
    api_content = f.read()

target_api = """                if ($total_actions > 0 || true) {
                    $result[] = [
                        'username' => $username,"""

repl_api = """                
                // Working hours calculation
                $att_stmt = $db->prepare("SELECT punch_in, punch_out FROM staff_attendance WHERE username = ? AND att_date = ? ORDER BY id DESC LIMIT 1");
                $att_stmt->execute([$username, $date]);
                $att = $att_stmt->fetch(PDO::FETCH_ASSOC);
                $working_hours = "0h 0m";
                $is_live = false;
                if ($att && $att['punch_in']) {
                    $p_in = strtotime($att['punch_in']);
                    $p_out = $att['punch_out'] ? strtotime($att['punch_out']) : time();
                    
                    if ($p_in > 0) {
                        // If it's a past date and no punch out, cap it at 8 hours or end of day. For simplicity, if not today and no punch_out, we consider it incomplete.
                        if ($date != date('Y-m-d') && !$att['punch_out']) {
                            $p_out = strtotime($date . " 18:00:00");
                        }
                        
                        $duration_secs = max(0, $p_out - $p_in);
                        $h = floor($duration_secs / 3600);
                        $m = floor(($duration_secs % 3600) / 60);
                        $working_hours = "{$h}h {$m}m";
                        if (!$att['punch_out'] && $date == date('Y-m-d')) {
                            $is_live = true;
                        }
                    }
                }

                if ($total_actions > 0 || true) {
                    $result[] = [
                        'username' => $username,
                        'working_hours' => $working_hours,
                        'is_live' => $is_live,"""

api_content = api_content.replace(target_api, repl_api)
with open(api_path, 'w', encoding='utf-8') as f:
    f.write(api_content)


# Update UI
emp_path = r'c:\Users\pc\Downloads\client mgmt2\employee_activity.php'
with open(emp_path, 'r', encoding='utf-8') as f:
    emp_content = f.read()

target_emp = """            <div class="prod-stat-item">
                <div class="prod-stat-val">${user.applicants_added}</div>
                <div class="prod-stat-label">Applicants</div>
            </div>"""

repl_emp = """            <div class="prod-stat-item" style="background:#f0fdf4; border-color:#bbf7d0;">
                <div class="prod-stat-val" style="color:#166534;">
                    ${user.working_hours}
                    ${user.is_live ? '<i class="fas fa-circle-notch fa-spin" style="font-size:10px; margin-left:3px;"></i>' : ''}
                </div>
                <div class="prod-stat-label" style="color:#15803d;">Working Hours</div>
            </div>
            <div class="prod-stat-item">
                <div class="prod-stat-val">${user.applicants_added}</div>
                <div class="prod-stat-label">Applicants</div>
            </div>"""

emp_content = emp_content.replace(target_emp, repl_emp)

target_emp_title = """<p style="font-size: 13.5px; color: var(--text-muted);">Select a date to see what each employee accomplished.</p>"""
repl_emp_title = """<p style="font-size: 13.5px; color: var(--text-muted);">Select a date to see working hours, timeline, and what each employee accomplished.</p>"""
emp_content = emp_content.replace(target_emp_title, repl_emp_title)


with open(emp_path, 'w', encoding='utf-8') as f:
    f.write(emp_content)

print("Updated employee_activity.php to show working hours")
