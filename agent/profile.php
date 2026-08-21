<?php
require_once 'includes/header.php';

// Get Agent Details
$stmt = $db->prepare("SELECT * FROM referrals WHERE user_id = ?");
$stmt->execute([$agent_id]);
$agent = $stmt->fetch();
?>

<div style="margin-bottom: 24px;">
    <h2 style="font-family: 'Outfit'; font-size: 24px; color: var(--text-primary);">Profile</h2>
</div>

<div class="card" style="display: flex; flex-direction: column; align-items: center; padding: 24px;">
    <div style="width: 80px; height: 80px; background: rgba(249, 115, 22, 0.1); color: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 32px; font-family: 'Outfit'; font-weight: 700; margin-bottom: 16px;">
        <?php echo strtoupper(substr($agent_name, 0, 1)); ?>
    </div>
    
    <h3 style="font-size: 20px; font-weight: 600; color: var(--text-primary); margin-bottom: 4px;"><?php echo htmlspecialchars($agent_name); ?></h3>
    <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 16px;">Referral Partner / Agent</p>
    
    <span class="badge badge-approved" style="padding: 6px 12px; font-size: 12px; margin-bottom: 8px;">Status: <?php echo htmlspecialchars($agent['status'] ?? 'Active'); ?></span>
</div>

<div class="card" style="padding: 0;">
    <div style="padding: 16px; border-bottom: 1px solid var(--border);">
        <h4 style="font-size: 15px; font-weight: 600; color: var(--text-primary); margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
            <i data-lucide="user" style="width: 16px; color: var(--primary);"></i> Personal Details
        </h4>
        
        <div style="display: flex; flex-direction: column; gap: 12px;">
            <div style="display: flex; justify-content: space-between;">
                <span style="color: var(--text-muted); font-size: 13px;">Phone</span>
                <span style="color: var(--text-primary); font-size: 13px; font-weight: 500;"><?php echo htmlspecialchars($agent['mobile'] ?? 'N/A'); ?></span>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span style="color: var(--text-muted); font-size: 13px;">Email</span>
                <span style="color: var(--text-primary); font-size: 13px; font-weight: 500;"><?php echo htmlspecialchars($agent['email'] ?? 'N/A'); ?></span>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span style="color: var(--text-muted); font-size: 13px;">Location</span>
                <span style="color: var(--text-primary); font-size: 13px; font-weight: 500;"><?php echo htmlspecialchars($agent['city_state'] ?? 'N/A'); ?></span>
            </div>
        </div>
    </div>
    
    <div style="padding: 16px;">
        <h4 style="font-size: 15px; font-weight: 600; color: var(--text-primary); margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
            <i data-lucide="landmark" style="width: 16px; color: var(--primary);"></i> Bank & Payout Details
        </h4>
        
        <div style="display: flex; flex-direction: column; gap: 12px;">
            <div style="display: flex; justify-content: space-between;">
                <span style="color: var(--text-muted); font-size: 13px;">Bank Name</span>
                <span style="color: var(--text-primary); font-size: 13px; font-weight: 500;"><?php echo htmlspecialchars($agent['bank_name'] ?? 'N/A'); ?></span>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span style="color: var(--text-muted); font-size: 13px;">Account No</span>
                <span style="color: var(--text-primary); font-size: 13px; font-weight: 500;"><?php echo htmlspecialchars($agent['account_number'] ?? 'N/A'); ?></span>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span style="color: var(--text-muted); font-size: 13px;">IFSC Code</span>
                <span style="color: var(--text-primary); font-size: 13px; font-weight: 500;"><?php echo htmlspecialchars($agent['ifsc_code'] ?? 'N/A'); ?></span>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span style="color: var(--text-muted); font-size: 13px;">Commission Rate</span>
                <span style="color: var(--primary); font-size: 13px; font-weight: 600;"><?php echo htmlspecialchars($agent['commission_rate'] ?? 'N/A'); ?></span>
            </div>
        </div>
    </div>
</div>

<a href="../login.php?logout=1" class="btn" style="background: white; color: var(--danger); border: 1px solid var(--danger); margin-top: 10px;">
    <i data-lucide="log-out" style="display: inline-block; vertical-align: middle; width: 18px; margin-right: 6px;"></i> Logout
</a>

<?php require_once 'includes/footer.php'; ?>
