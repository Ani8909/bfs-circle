<?php
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'Staff';

$stmt = $db->prepare("SELECT department FROM employees WHERE user_id = ?");
$stmt->execute([$user_id]);
$department = $stmt->fetchColumn();

if ($department !== 'Lead Generation Team') {
    die("Access Denied: This mobile application is restricted to Field Employees (Lead Generation Team) only.");
}

$username = $_SESSION['username'];
$role = $_SESSION['role'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>My Field App - BFS Financial Services</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #FF7A00;
            --primary-dark: #E66A00;
            --secondary: #1E293B;
            --bg-color: #F1F5F9;
            --card-bg: #FFFFFF;
            --text-main: #0F172A;
            --text-muted: #64748B;
            --border-color: #E2E8F0;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        
        body {
            background-color: var(--bg-color);
            color: var(--text-main);
            padding-bottom: 70px; /* Space for bottom nav */
        }

        /* App Header */
        .app-header {
            background: var(--primary);
            color: white;
            padding: 20px 16px;
            border-bottom-left-radius: 20px;
            border-bottom-right-radius: 20px;
            box-shadow: 0 4px 10px rgba(255, 122, 0, 0.2);
        }

        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .user-greeting { font-size: 14px; opacity: 0.9; }
        .user-name { font-size: 20px; font-weight: 700; margin-top: 4px; }
        .logout-btn { color: white; text-decoration: none; font-size: 20px; }

        /* Dashboard Cards */
        .metrics-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            padding: 0 16px;
            margin-top: -30px;
        }

        .metric-card {
            background: white;
            border-radius: 16px;
            padding: 16px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            text-align: center;
        }
        
        .metric-card.full-width {
            grid-column: 1 / -1;
            display: flex;
            align-items: center;
            justify-content: space-between;
            text-align: left;
            background: linear-gradient(135deg, #1E293B 0%, #0F172A 100%);
            color: white;
        }

        .metric-icon {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: rgba(255, 122, 0, 0.1);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            margin: 0 auto 12px;
        }

        .metric-value { font-size: 24px; font-weight: 700; color: var(--text-main); }
        .metric-label { font-size: 12px; color: var(--text-muted); margin-top: 4px; }
        
        .full-width .metric-value { color: white; font-size: 28px; }
        .full-width .metric-label { color: #94A3B8; }
        .full-width .metric-icon { margin: 0; background: rgba(255,255,255,0.1); color: white; }

        /* Recent Activity */
        .section-container {
            padding: 24px 16px;
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .see-all { font-size: 12px; color: var(--primary); text-decoration: none; font-weight: 500; }

        .lead-card {
            background: white;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 16px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
            border: 1px solid var(--border-color);
        }

        .lead-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #F1F5F9;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
            flex-shrink: 0;
        }

        .lead-info { flex: 1; overflow: hidden; }
        .lead-name { font-size: 14px; font-weight: 600; margin-bottom: 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .lead-sub { font-size: 12px; color: var(--text-muted); }
        
        .lead-status {
            font-size: 11px;
            font-weight: 600;
            padding: 4px 8px;
            border-radius: 20px;
            background: #e0f2fe;
            color: #0369a1;
        }
        .status-completed { background: #dcfce7; color: #15803d; }

        /* Bottom Nav */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            display: flex;
            justify-content: space-around;
            padding: 12px 0;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.05);
            z-index: 100;
        }

        .nav-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 10px;
            font-weight: 500;
        }

        .nav-btn.active { color: var(--primary); }
        .nav-btn i { font-size: 20px; }
        
        /* Floating Action Button */
        .fab {
            position: absolute;
            top: -20px;
            left: 50%;
            transform: translateX(-50%);
            height: 44px;
            padding: 0 16px;
            border-radius: 22px;
            background: linear-gradient(135deg, #FF6B00 0%, #FF9A44 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 600;
            box-shadow: 0 6px 15px rgba(255, 107, 0, 0.35);
            text-decoration: none;
            white-space: nowrap;
            gap: 6px;
        }

    </style>
</head>
<body>

    <div class="app-header">
        <div class="header-top">
            <div>
                <div class="user-greeting">Welcome back,</div>
                <div class="user-name" id="staff_name"><?= htmlspecialchars($username) ?></div>
            </div>
            <a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i></a>
        </div>
        <div style="height: 40px;"></div> <!-- Spacer for overlapping cards -->
    </div>

    <?php
        $stmt_emp = $db->prepare("SELECT * FROM employees WHERE user_id = ?");
        $stmt_emp->execute([$user_id]);
        $emp_data = $stmt_emp->fetch();
        $fullname = $emp_data['full_name'] ?? $username;
        $dept = $emp_data['department'] ?? 'Staff';
        $comm = $emp_data['commission_rate'] ?? 1.0;
        $mobile = $emp_data['mobile'] ?? 'N/A';
        $email = $emp_data['official_email'] ?? 'N/A';
    ?>
    <div style="padding:20px; padding-bottom:80px; z-index:1; padding-top:20px;">
        <h2 style="font-size:18px; margin-bottom:15px; color:#1e293b;">My Profile</h2>
        
        <div style="background:white; border-radius:16px; padding:25px 20px; box-shadow:0 4px 15px rgba(0,0,0,0.05); text-align:center;">
            <?php if(!empty($emp_data['photo_path'])): ?>
                <div style="width:80px; height:80px; border-radius:50%; margin:0 auto 15px auto; overflow:hidden; border:3px solid var(--primary); box-shadow:0 4px 10px rgba(0,0,0,0.1);">
                    <img src="../uploads/employees/<?php echo htmlspecialchars($emp_data['photo_path']); ?>" style="width:100%; height:100%; object-fit:cover;">
                </div>
            <?php else: ?>
                <div style="width:80px; height:80px; border-radius:50%; background:linear-gradient(135deg, var(--primary), #3b82f6); color:white; display:flex; align-items:center; justify-content:center; font-size:32px; font-weight:bold; margin:0 auto 15px auto;">
                    <?php echo strtoupper(substr($fullname, 0, 1)); ?>
                </div>
            <?php endif; ?>
            
            <h3 style="margin:0 0 5px 0; font-size:22px; color:#0f172a;"><?php echo htmlspecialchars($fullname); ?></h3>
            <p style="margin:0 0 15px 0; color:#64748b; font-size:14px;"><?php echo htmlspecialchars($dept); ?></p>
            
            <div style="display:flex; justify-content:center; gap:10px; margin-bottom:20px;">
                <span style="background:#f1f5f9; padding:6px 12px; border-radius:20px; font-size:12px; color:#475569; font-weight:600;">
                    <i class="fas fa-percent"></i> Comm. Rate: <?php echo $comm; ?>%
                </span>
            </div>
            
            <div style="text-align:left; background:#f8fafc; padding:15px; border-radius:12px; margin-bottom:20px;">
                <div style="margin-bottom:10px;">
                    <span style="font-size:12px; color:gray; display:block;">Email Address</span>
                    <span style="font-size:14px; color:#1e293b; font-weight:500;"><?php echo htmlspecialchars($email); ?></span>
                </div>
                <div>
                    <span style="font-size:12px; color:gray; display:block;">Phone Number</span>
                    <span style="font-size:14px; color:#1e293b; font-weight:500;"><?php echo htmlspecialchars($mobile); ?></span>
                </div>
            </div>
            
            <button onclick="toggleEdit()" style="display:block; width:100%; padding:12px 20px; background:#3b82f6; color:white; border-radius:10px; border:none; text-decoration:none; font-weight:600; text-align:center; margin-bottom:15px; cursor:pointer; font-size:15px;">
                <i class="fas fa-edit"></i> Edit Profile
            </button>
            
            <a href="../logout.php" style="display:block; padding:12px 20px; background:#ef4444; color:white; border-radius:10px; text-decoration:none; font-weight:600; text-align:center;">
                <i class="fas fa-sign-out-alt"></i> Log Out
            </a>
        </div>
        
        <!-- Edit Profile Form -->
        <div id="edit_form" style="display:none; background:white; border-radius:16px; padding:25px 20px; box-shadow:0 4px 15px rgba(0,0,0,0.05); margin-top:20px;">
            <h3 style="margin-top:0; color:#1e293b; font-size:18px; margin-bottom:20px;">Update Details</h3>
            <form id="profileForm" onsubmit="saveProfile(event)">
                <input type="hidden" name="username" value="<?php echo htmlspecialchars($username); ?>">
                
                <div style="margin-bottom:15px; text-align:left;">
                    <label style="display:block; font-size:13px; color:#64748b; margin-bottom:5px; font-weight:500;">Profile Photo</label>
                    <input type="file" name="profile_photo" accept="image/*" style="width:100%; padding:10px; border:1px dashed #cbd5e1; border-radius:10px; font-size:13px; background:#f8fafc;">
                </div>

                <div style="margin-bottom:15px; text-align:left;">
                    <label style="display:block; font-size:13px; color:#64748b; margin-bottom:5px; font-weight:500;">Phone Number</label>
                    <input type="text" name="mobile" value="<?php echo htmlspecialchars($mobile); ?>" style="width:100%; padding:12px 15px; border:1px solid #e2e8f0; border-radius:10px; font-size:15px; outline:none;" required>
                </div>
                
                <div style="margin-bottom:15px;">
                    <label style="display:block; font-size:13px; color:#64748b; margin-bottom:5px; font-weight:500;">Personal Email</label>
                    <input type="email" name="personal_email" value="<?php echo htmlspecialchars($emp_data['personal_email'] ?? ''); ?>" style="width:100%; padding:12px 15px; border:1px solid #e2e8f0; border-radius:10px; font-size:15px; outline:none;">
                </div>
                
                <div style="margin-bottom:20px;">
                    <label style="display:block; font-size:13px; color:#64748b; margin-bottom:5px; font-weight:500;">New Password (Leave blank to keep current)</label>
                    <input type="password" name="password" placeholder="Enter new password" style="width:100%; padding:12px 15px; border:1px solid #e2e8f0; border-radius:10px; font-size:15px; outline:none;">
                </div>
                
                <div id="profile_msg" style="margin-bottom:15px; font-size:14px; text-align:center;"></div>
                
                <div style="display:flex; gap:10px;">
                    <button type="button" onclick="toggleEdit()" style="flex:1; padding:12px; background:#f1f5f9; color:#475569; border:none; border-radius:10px; font-weight:600; cursor:pointer;">Cancel</button>
                    <button type="submit" id="save_btn" style="flex:1; padding:12px; background:var(--primary); color:white; border:none; border-radius:10px; font-weight:600; cursor:pointer;">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bottom Nav -->
    <div class="bottom-nav">
        <a href="index.php" class="nav-btn <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>
        <a href="visits.php" class="nav-btn <?= basename($_SERVER['PHP_SELF']) == 'visits.php' ? 'active' : '' ?>">
            <i class="fas fa-list-ul"></i>
            <span>My Visits</span>
        </a>
        <div style="width: 110px; position: relative;">
            <a href="add_visit.php" class="fab">
                <i class="fas fa-plus"></i> <span>Start Visit</span>
            </a>
        </div>
        <a href="files.php" class="nav-btn <?= basename($_SERVER['PHP_SELF']) == 'files.php' ? 'active' : '' ?>">
            <i class="fas fa-file-invoice"></i>
            <span>Files</span>
        </a>
        <a href="profile.php" class="nav-btn <?= basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : '' ?>">
            <i class="fas fa-user"></i>
            <span>Profile</span>
        </a>
    </div>

    <script>
        function toggleEdit() {
            const form = document.getElementById('edit_form');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }
        
        async function saveProfile(e) {
            e.preventDefault();
            const btn = document.getElementById('save_btn');
            const msg = document.getElementById('profile_msg');
            const fd = new FormData(e.target);
            
            btn.innerText = 'Saving...';
            btn.disabled = true;
            msg.innerText = '';
            
            try {
                const res = await fetch('?api=update_staff_profile', { method: 'POST', body: fd });
                const data = await res.json();
                if (data.success) {
                    msg.style.color = '#10b981';
                    msg.innerText = 'Profile updated successfully!';
                    setTimeout(() => location.reload(), 1500);
                } else {
                    msg.style.color = '#ef4444';
                    msg.innerText = data.error || 'Failed to update profile';
                    btn.disabled = false;
                    btn.innerText = 'Save Changes';
                }
            } catch(err) {
                msg.style.color = '#ef4444';
                msg.innerText = 'Network error occurred.';
                btn.disabled = false;
                btn.innerText = 'Save Changes';
            }
        }
    </script>
</body>
</html>
