import os

file_path = r'c:\Users\pc\Downloads\client mgmt2\admin_tracking.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# Add the column header
target_header = """                      <th>Status</th>
                      <th>Battery</th>"""
repl_header = """                      <th>Status</th>
                      <th>Shift Duration</th>
                      <th>Battery</th>"""
content = content.replace(target_header, repl_header)


# Add the calculation logic before the row output
target_logic = """                    for($i=1; $i<count($logs); $i++) {
                        $p1 = $logs[$i-1];"""

repl_logic = """                    // Calculate Shift Duration
                    $att_stmt = $db->prepare("SELECT punch_in, punch_out FROM staff_attendance WHERE username = ? AND att_date = ? ORDER BY id DESC LIMIT 1");
                    $att_stmt->execute([$uname, $today]);
                    $att = $att_stmt->fetch(PDO::FETCH_ASSOC);
                    
                    $shift_str = "-";
                    if ($att && $att['punch_in']) {
                        $p_in = strtotime($att['punch_in']);
                        $p_out = $att['punch_out'] ? strtotime($att['punch_out']) : time();
                        if ($p_in > 0) {
                            $duration_secs = max(0, $p_out - $p_in);
                            $h = floor($duration_secs / 3600);
                            $m = floor(($duration_secs % 3600) / 60);
                            $shift_str = "{$h}h {$m}m";
                            if (!$att['punch_out'] && $is_active) {
                                $shift_str = "<span style='color:#10b981; font-weight:700;'><i class='fas fa-circle-notch fa-spin' style='font-size:10px; margin-right:4px;'></i>{$shift_str}</span>";
                            }
                        }
                    }

                    for($i=1; $i<count($logs); $i++) {
                        $p1 = $logs[$i-1];"""
content = content.replace(target_logic, repl_logic)


# Add the table data cell
target_td = """                      <td>
                          <span class="status-badge <?= $status_class ?>">
                              <div class="pulse-dot <?= $dot_class ?>"></div> <?= $status_text ?>
                          </span>
                      </td>
                      <td>"""

repl_td = """                      <td>
                          <span class="status-badge <?= $status_class ?>">
                              <div class="pulse-dot <?= $dot_class ?>"></div> <?= $status_text ?>
                          </span>
                      </td>
                      <td>
                          <div style="font-size:13px; font-weight:600; color:#334155;"><?= $shift_str ?></div>
                      </td>
                      <td>"""

content = content.replace(target_td, repl_td)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Added Shift Duration column to admin_tracking.php")
