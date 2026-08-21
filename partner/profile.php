<?php
require_once 'includes/header.php';

$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$partner_id]);
$user_info = $stmt->fetch();

// Also get referral info
$stmt_ref = $db->prepare("SELECT * FROM referrals WHERE id = ? LIMIT 1");
$stmt_ref->execute([$partner_id]);
$partner_details = $stmt_ref->fetch();

$phone = $partner_details['mobile'] ?? 'Not provided';
$email = $partner_details['email'] ?? 'Not provided';
$joined = date('d M Y', strtotime($user_info['created_at']));
?>

<div style="margin-bottom: 24px;">
    <h2 style="font-family: 'Outfit'; font-size: 24px; color: var(--text-primary);">My Profile</h2>
    <p style="color: var(--text-muted); font-size: 14px; margin-top: 4px;">Manage your account details</p>
</div>

<div class="card" style="text-align: center; padding: 30px 20px;">
    <div style="width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, #10b981, #059669); color: white; display: flex; align-items: center; justify-content: center; font-size: 32px; font-weight: 800; font-family: 'Outfit'; margin: 0 auto 16px auto; box-shadow: 0 10px 25px rgba(16, 185, 129, 0.35); border: 3px solid white;">
        <?php echo strtoupper(substr($partner['full_name'], 0, 1)); ?>
    </div>
    
    <h3 style="font-family: 'Outfit'; font-size: 22px; color: var(--text-primary); margin-bottom: 4px; display:flex; align-items:center; justify-content:center; gap:6px;">
        <?php echo htmlspecialchars($partner['full_name']); ?>
        <i data-lucide="badge-check" style="width:20px; color:#10b981; fill:rgba(16,185,129,0.1);"></i>
    </h3>
    <p style="color: var(--text-muted); font-weight: 500; font-size: 14px; margin-bottom: 12px;">Financial Adviser (Partner)</p>
    
    <div style="margin-bottom: 24px;">
        <span style="display:inline-flex; align-items:center; gap:4px; background:#dcfce7; color:#166534; padding:6px 14px; border-radius:20px; font-size:12px; font-weight:700; border: 1px solid #bbf7d0;">
            <i data-lucide="check-circle-2" style="width:14px; height:14px;"></i> Active Account
        </span>
    </div>

    <div style="text-align: left; background: var(--bg-main); border-radius: 12px; padding: 16px; margin-bottom: 20px;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid var(--border);">
            <div style="background: rgba(15,23,42,0.05); padding: 8px; border-radius: 8px;">
                <i data-lucide="phone" style="width: 18px; color: var(--primary);"></i>
            </div>
            <div>
                <div style="font-size: 11px; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Mobile Number</div>
                <div style="font-size: 14px; color: var(--text-primary); font-weight: 500;"><?php echo htmlspecialchars($phone); ?></div>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid var(--border);">
            <div style="background: rgba(15,23,42,0.05); padding: 8px; border-radius: 8px;">
                <i data-lucide="mail" style="width: 18px; color: var(--primary);"></i>
            </div>
            <div>
                <div style="font-size: 11px; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Email Address</div>
                <div style="font-size: 14px; color: var(--text-primary); font-weight: 500;"><?php echo htmlspecialchars($email); ?></div>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="background: rgba(15,23,42,0.05); padding: 8px; border-radius: 8px;">
                <i data-lucide="calendar" style="width: 18px; color: var(--primary);"></i>
            </div>
            <div>
                <div style="font-size: 11px; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Joined On</div>
                <div style="font-size: 14px; color: var(--text-primary); font-weight: 500;"><?php echo $joined; ?></div>
            </div>
        </div>
    </div>
    
    <a href="../login.php?logout=1" style="display: flex; align-items: center; justify-content: center; gap: 8px; background: #fee2e2; color: #b91c1c; padding: 12px; border-radius: 12px; text-decoration: none; font-weight: 600; transition: background 0.2s;">
        <i data-lucide="log-out" style="width: 18px;"></i> Logout Securely
    </a>
</div>

<?php require_once 'includes/footer.php'; ?>
