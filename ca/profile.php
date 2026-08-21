<?php
require_once '../config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'CA') {
    header("Location: ../login.php");
    exit;
}

$page_title = 'My Profile - CA Portal';
$active_page = 'profile'; // Can be 'profile'
require_once 'includes/header.php';

// Fetch details
$user_id = $_SESSION['user_id'];
// Get from users table
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Get from referrals table (bank details etc)
$stmt = $db->prepare("SELECT * FROM referrals WHERE user_id = ?");
$stmt->execute([$user_id]);
$partner = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
?>

<style>
    .page-header { display: flex; align-items: center; gap: 12px; margin-bottom: 24px; font-size: 20px; font-weight: 800; color: var(--primary); }
    
    .profile-card {
        background: var(--surface); border-radius: 12px; padding: 32px;
        border: 1px solid var(--border); max-width: 800px; margin: 0 auto;
        text-align: center;
    }
    
    .profile-avatar-large {
        width: 100px; height: 100px; background: var(--primary); color: white;
        border-radius: 50%; display: flex; align-items: center; justify-content: center;
        font-size: 36px; font-weight: 800; margin: 0 auto 20px auto;
        box-shadow: 0 10px 20px rgba(15, 44, 89, 0.2);
    }
    
    .profile-name { font-size: 24px; font-weight: 800; color: var(--text-main); margin-bottom: 4px; }
    .profile-role { font-size: 14px; color: var(--text-muted); font-weight: 600; margin-bottom: 24px; display: inline-block; padding: 4px 12px; background: var(--bg); border-radius: 20px; border: 1px solid var(--border); }
    
    .info-grid {
        display: grid; grid-template-columns: 1fr 1fr; gap: 20px; text-align: left;
        margin-top: 30px; padding-top: 30px; border-top: 1px solid var(--border);
    }
    
    .info-item { margin-bottom: 16px; }
    .info-label { font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px; }
    .info-value { font-size: 15px; font-weight: 600; color: var(--text-main); }
    
    .btn-logout-large {
        display: inline-flex; align-items: center; justify-content: center; gap: 10px;
        background: #FFF1F2; color: var(--danger); padding: 16px 32px; border-radius: 12px;
        font-weight: 700; text-decoration: none; margin-top: 40px; border: 1px solid #FFE4E6;
        transition: all 0.2s; width: 100%;
    }
    .btn-logout-large:hover { background: var(--danger); color: white; }

    @media (max-width: 768px) {
        .profile-card { padding: 24px; border-radius: 12px; border-left: none; border-right: none; }
        .info-grid { grid-template-columns: 1fr; gap: 16px; margin-top: 24px; padding-top: 24px; }
    }
</style>

<div class="page-header">
    <i data-lucide="user" style="color:var(--primary); width:28px; height:28px;"></i>
    My Profile
</div>

<div class="profile-card">
    <div class="profile-avatar-large">
        <?php echo strtoupper(substr($user['username'] ?? 'C', 0, 1)); ?>
    </div>
    <div class="profile-name"><?php echo htmlspecialchars($user['username'] ?? 'User'); ?></div>
    <div class="profile-role">Chartered Accountant (CA) Partner</div>
    
    <div class="info-grid">
        <div class="info-item">
            <div class="info-label">Contact Number</div>
            <div class="info-value"><i data-lucide="phone" style="width:14px; height:14px; margin-right:6px; color:var(--text-muted);"></i> <?php echo htmlspecialchars($partner['mobile'] ?? 'Not provided'); ?></div>
        </div>
        <div class="info-item">
            <div class="info-label">Email Address</div>
            <div class="info-value"><i data-lucide="mail" style="width:14px; height:14px; margin-right:6px; color:var(--text-muted);"></i> <?php echo htmlspecialchars($partner['email'] ?? 'Not provided'); ?></div>
        </div>
        <div class="info-item">
            <div class="info-label">Company / Firm Name</div>
            <div class="info-value"><i data-lucide="building" style="width:14px; height:14px; margin-right:6px; color:var(--text-muted);"></i> <?php echo htmlspecialchars($partner['account_name'] ?? 'Not provided'); ?></div>
        </div>
        <div class="info-item">
            <div class="info-label">Joining Date</div>
            <div class="info-value"><i data-lucide="calendar" style="width:14px; height:14px; margin-right:6px; color:var(--text-muted);"></i> <?php echo date('d M Y', strtotime($user['created_at'])); ?></div>
        </div>
    </div>
    
    <a href="../config.php?logout=1" class="btn-logout-large ripple">
        <i data-lucide="log-out"></i> Secure Logout
    </a>
</div>

<?php require_once 'includes/footer.php'; ?>
