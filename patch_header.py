import os
import re

staff_idx_path = r'c:\Users\pc\Downloads\client mgmt2\staff\index.php'
with open(staff_idx_path, 'r', encoding='utf-8') as f:
    idx = f.read()

# 1. Update the PHP block at the top to fetch photo_path and compute greeting
php_target = """$stmt = $db->prepare("SELECT department FROM employees WHERE user_id = ?");
$stmt->execute([$user_id]);
$department = $stmt->fetchColumn();"""

php_repl = """$stmt = $db->prepare("SELECT department, photo_path FROM employees WHERE user_id = ?");
$stmt->execute([$user_id]);
$emp_data = $stmt->fetch(PDO::FETCH_ASSOC);
$department = $emp_data['department'] ?? '';
$photo_path = $emp_data['photo_path'] ?? '';

// Dynamic Greeting based on time
$hour = date('H');
if ($hour < 12) {
    $greeting = "Good Morning,";
} elseif ($hour < 16) {
    $greeting = "Good Afternoon,";
} elseif ($hour < 20) {
    $greeting = "Good Evening,";
} else {
    $greeting = "Good Night,";
}"""

idx = idx.replace(php_target, php_repl)

# 2. Update the HTML in the header to use $greeting and $photo_path
html_target = """        <div class="app-header">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px;">
                <div style="display:flex; align-items:center; gap:12px;">
                    <div style="width:40px; height:40px; background:rgba(255,255,255,0.2); border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:18px;">
                        <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=<?= urlencode($username) ?>" width="30" height="30" style="border-radius:50%;">
                    </div>
                    <div>
                        <div class="user-greeting">Hello,</div>
                        <div class="user-name" id="staff_name" style="font-size:16px; margin-top:2px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:200px;"><?= htmlspecialchars(explode('@', $username)[0]) ?></div>
                    </div>
                </div>"""

html_repl = """        <div class="app-header">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px;">
                <div style="display:flex; align-items:center; gap:12px;">
                    <div style="width:40px; height:40px; background:rgba(255,255,255,0.2); border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:18px; overflow:hidden;">
                        <?php if(!empty($photo_path)): ?>
                            <img src="../uploads/employees/<?= htmlspecialchars($photo_path) ?>" style="width:100%; height:100%; object-fit:cover;">
                        <?php else: ?>
                            <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=<?= urlencode($username) ?>" width="30" height="30" style="border-radius:50%;">
                        <?php endif; ?>
                    </div>
                    <div>
                        <div class="user-greeting" style="font-size:13px; opacity:0.9; font-weight:500;"><?= $greeting ?></div>
                        <div class="user-name" id="staff_name" style="font-size:16px; margin-top:2px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:200px;"><?= htmlspecialchars(explode('@', $username)[0]) ?></div>
                    </div>
                </div>"""

idx = idx.replace(html_target, html_repl)

with open(staff_idx_path, 'w', encoding='utf-8') as f:
    f.write(idx)
print("Updated greeting and profile photo in staff/index.php")
