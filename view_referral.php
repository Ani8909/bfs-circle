<?php
require_once 'config.php';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $db->prepare("SELECT * FROM referrals WHERE id = ?");
$stmt->execute([$id]);
$referral = $stmt->fetch();

$is_user_fallback = false;
if (!$referral) {
    $stmt_u = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt_u->execute([$id]);
    $user_prof = $stmt_u->fetch();
    
    if ($user_prof) {
        $is_user_fallback = true;
        $referral = [
            'id' => $user_prof['id'],
            'referral_id' => $user_prof['username'],
            'referrer_type' => $user_prof['role'],
            'full_name' => $user_prof['name'],
            'mobile' => $user_prof['mobile'] ?? 'N/A',
            'email' => $user_prof['email'] ?? 'N/A',
            'city_state' => 'N/A',
            'status' => 'Active',
            'created_at' => $user_prof['created_at'] ?? date('Y-m-d H:i:s'),
        ];
    } else {
        echo "Referral partner or User not found.";
        exit;
    }
}

$page_title = 'Referral Profile: ' . htmlspecialchars($referral['full_name']);
$page_subtitle = ' Detailed view of referral partner profile, commission structure, and KYC documents';
$ref_user_identifier = $referral['referral_id'] ?? '';
$total_leads = $db->query("SELECT COUNT(*) FROM leads WHERE added_by = " . $db->quote($ref_user_identifier))->fetchColumn();
$loans_passed = $db->query("SELECT COUNT(*) FROM applicants WHERE added_by = " . $db->quote($ref_user_identifier) . " AND overall_status = 'Completed'")->fetchColumn();

require_once 'header.php';
?>

<style>
    .premium-profile-wrapper {
        width: 100%;
        font-family: 'Inter', sans-serif;
    }
    
    .profile-header-card {
        background: #ffffff;
        border-radius: 16px;
        padding: 32px;
        margin-bottom: 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 4px 24px rgba(0, 0, 0, 0.04);
        border: 1px solid rgba(0,0,0,0.02);
    }
    
    .profile-avatar {
        width: 64px;
        height: 64px;
        background: var(--primary-light);
        color: var(--primary);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        font-weight: 700;
        margin-right: 20px;
    }
    
    .profile-title-group h1 {
        margin: 0 0 6px 0;
        font-family: 'Outfit', sans-serif;
        font-size: 26px;
        font-weight: 700;
        color: #0f172a;
        letter-spacing: -0.5px;
    }
    
    .profile-meta {
        display: flex;
        gap: 16px;
        align-items: center;
        color: #64748b;
        font-size: 14px;
    }
    
    .meta-item { display: flex; align-items: center; gap: 6px; }
    
    .status-badge {
        padding: 8px 16px;
        border-radius: 30px;
        font-size: 13px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .status-active { background: #ecfdf5; color: #10b981; }
    .status-pending { background: #fffbeb; color: #f59e0b; }
    .status-blocked { background: #fef2f2; color: #ef4444; }

    .premium-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 24px;
        align-items: start;
    }

    .premium-card {
        background: #ffffff;
        border-radius: 16px;
        padding: 28px;
        box-shadow: 0 4px 24px rgba(0, 0, 0, 0.03);
        border: 1px solid rgba(0,0,0,0.02);
    }
    
    .card-title {
        display: flex;
        align-items: center;
        gap: 10px;
        font-family: 'Outfit', sans-serif;
        font-size: 17px;
        font-weight: 600;
        color: #0f172a;
        margin: 0 0 24px 0;
    }
    
    .card-icon {
        background: #f8fafc;
        color: #64748b;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .card-icon svg { width: 16px; height: 16px; }

    .data-list {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
    
    .data-item {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    
    .data-label {
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #94a3b8;
    }
    
    .data-value {
        font-size: 15px;
        font-weight: 500;
        color: #1e293b;
    }

    .premium-doc-box {
        background: #f8fafc;
        border-radius: 12px;
        padding: 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
        transition: all 0.2s ease;
        border: 1px solid transparent;
    }
    .premium-doc-box:hover {
        background: #ffffff;
        border-color: #e2e8f0;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    .doc-info { display: flex; flex-direction: column; gap: 4px; }
    .doc-title { font-size: 13px; font-weight: 600; color: #475569; }
    .doc-value { font-family: monospace; font-size: 14px; font-weight: 700; color: var(--primary); letter-spacing: 0.5px; }
    
    /* Top Action Bar */
    .top-action-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
    }
</style>

<div class="view-container premium-profile-wrapper">
    
    <div class="top-action-bar">
        <div>
            <a href="referrals_list.php" class="btn btn-secondary" style="background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border:none; margin-right: 12px;"><i data-lucide="arrow-left"></i> Back to Directory</a>
            <?php if (($_SESSION['role'] ?? '') === 'Admin'): ?>
            <?php if (empty($is_user_fallback)): ?>
            <a href="edit_referral.php?id=<?php echo $referral['id']; ?>" class="btn btn-primary" style="box-shadow: 0 4px 12px rgba(249, 115, 22, 0.2);"><i data-lucide="edit"></i> Complete / Edit Profile</a>
            <?php endif; ?>
            <?php endif; ?>
        </div>
        <div style="color: #64748b; font-size: 13px; font-weight: 500;">
            ID: <?php echo htmlspecialchars($referral['referral_id'] ?? ''); ?>
        </div>
    </div>

    <!-- Header Card -->
    <div class="profile-header-card">
        <div style="display: flex; align-items: center;">
            <div class="profile-avatar">
                <?php echo strtoupper(substr($referral['full_name'] ?? 'A', 0, 1)); ?>
            </div>
            <div class="profile-title-group">
                <h1><?php echo htmlspecialchars($referral['full_name'] ?? ''); ?></h1>
                <div class="profile-meta">
                    <span class="meta-item"><i data-lucide="briefcase" style="width:14px;height:14px;"></i> <?php echo htmlspecialchars($referral['referrer_type'] ?? ''); ?></span>
                    <span class="meta-item"><i data-lucide="map-pin" style="width:14px;height:14px;"></i> <?php echo htmlspecialchars($referral['city_state'] ?? ''); ?></span>
                </div>
            </div>
        </div>
        <div>
            <?php 
                $s = $referral['status'] ?? '';
                $status_class = 'status-pending';
                if ($s === 'Active') $status_class = 'status-active';
                if ($s === 'Blocked') $status_class = 'status-blocked';
                
                // Calculate Completeness
                $fields = [
                    $referral['mobile'] ?? null, $referral['dob'] ?? null, $referral['email'] ?? null,
                    $referral['account_number'] ?? null, $referral['ifsc_code'] ?? null, 
                    $referral['pan_number'] ?? null, $referral['aadhar_number'] ?? null, 
                    $referral['commission_rate'] ?? null,
                    $referral['bank_document_path'] ?? null, $referral['pan_document_path'] ?? null
                ];
                $filled = 0;
                foreach($fields as $f) { if(!empty($f)) $filled++; }
                $completion = round(($filled / count($fields)) * 100);
                
                $progress_color = '#ef4444'; // red
                if($completion > 50) $progress_color = '#f59e0b'; // yellow
                if($completion == 100) $progress_color = '#10b981'; // green
            ?>
            <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 12px;">
                <span class="status-badge <?php echo $status_class; ?>">
                    <?php echo htmlspecialchars($s); ?>
                </span>
                
                <div style="text-align: right; width: 180px;">
                    <div style="display: flex; justify-content: space-between; font-size: 11px; font-weight: 600; color: #64748b; margin-bottom: 6px; text-transform: uppercase;">
                        <span>Profile Complete</span>
                        <span style="color: <?php echo $progress_color; ?>"><?php echo $completion; ?>%</span>
                    </div>
                    <div style="height: 6px; background: #f1f5f9; border-radius: 4px; overflow: hidden;">
                        <div style="height: 100%; width: <?php echo $completion; ?>%; background: <?php echo $progress_color; ?>; transition: width 1s ease-in-out; border-radius: 4px;"></div>
                    </div>
                    <?php if (($_SESSION['role'] ?? '') === 'Admin'): ?>
                    <div style="margin-top: 12px; text-align: right;">
                        <?php if (!empty($is_user_fallback)): ?>
                        <a href="api.php?api=create_referral_profile&user_id=<?php echo $referral['id']; ?>" class="btn btn-sm" style="background:#fff; border:1px solid #e2e8f0; color:#0f172a; font-size:11px; padding:4px 10px; border-radius:20px; box-shadow:0 1px 2px rgba(0,0,0,0.05); text-decoration:none; font-weight:600;">
                            <i data-lucide="plus-circle" style="width:12px; height:12px; margin-right:4px;"></i> Complete Profile
                        </a>
                        <?php else: ?>
                        <a href="edit_referral.php?id=<?php echo $referral['id']; ?>" class="btn btn-sm" style="background:#fff; border:1px solid #e2e8f0; color:#0f172a; font-size:11px; padding:4px 10px; border-radius:20px; box-shadow:0 1px 2px rgba(0,0,0,0.05); text-decoration:none; font-weight:600;">
                            <i data-lucide="edit" style="width:12px; height:12px; margin-right:4px;"></i> Edit / Complete Profile
                        </a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="premium-grid">
        <!-- Left Column -->
        <div style="display: flex; flex-direction: column; gap: 24px;">
            
            <div class="premium-card">
                <h3 class="card-title"><div class="card-icon"><i data-lucide="user"></i></div> Basic Information</h3>
                <div class="data-list">
                    <div class="data-item">
                        <span class="data-label">Date of Birth</span>
                        <span class="data-value"><?php echo htmlspecialchars($referral['dob'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="data-item">
                        <span class="data-label">Mobile Number</span>
                        <span class="data-value"><?php echo htmlspecialchars($referral['mobile'] ?? ''); ?></span>
                    </div>
                    <div class="data-item">
                        <span class="data-label">Email Address</span>
                        <span class="data-value"><?php echo htmlspecialchars($referral['email'] ?? 'N/A'); ?></span>
                    </div>
                </div>
            </div>

            <div class="premium-card">
                <h3 class="card-title"><div class="card-icon"><i data-lucide="trending-up"></i></div> Performance Metrics</h3>
                <div class="data-list" style="display:flex; justify-content:space-around; align-items:center; padding: 15px 0;">
                    <div style="text-align:center;">
                        <span class="data-label" style="display:block; margin-bottom:8px;">Total Leads Added</span>
                        <span style="font-size: 24px; font-weight: 700; color: var(--primary);"><?php echo $total_leads; ?></span>
                    </div>
                    <div style="width:1px; height:40px; background:#e2e8f0;"></div>
                    <div style="text-align:center;">
                        <span class="data-label" style="display:block; margin-bottom:8px;">Successful Loans (Passed)</span>
                        <span style="font-size: 24px; font-weight: 700; color: #16a34a;"><?php echo $loans_passed; ?></span>
                    </div>
                </div>
            </div>

            <?php 
            $extra = json_decode($referral['extra_details'] ?? '{}', true); 
            if (!empty($extra)): 
            ?>
            <div class="premium-card">
                <h3 class="card-title"><div class="card-icon"><i data-lucide="briefcase"></i></div> Professional Details</h3>
                <div class="data-list">
                    <?php foreach ($extra as $key => $val): 
                        $label = ucwords(str_replace('_', ' ', $key));
                        if ($key === 'rera_no') $label = 'RERA Reg No.';
                        if ($key === 'gst_no') $label = 'GST Number';
                    ?>
                    <div class="data-item">
                        <span class="data-label"><?php echo htmlspecialchars($label); ?></span>
                        <span class="data-value"><?php echo htmlspecialchars($val ?? 'N/A'); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="premium-card">
                <h3 class="card-title"><div class="card-icon"><i data-lucide="percent"></i></div> Commission Setup</h3>
                <div class="data-list">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="data-item">
                            <span class="data-label">Rate / Slab</span>
                            <span class="data-value" style="font-size: 18px; color: var(--primary); font-weight: 700;"><?php echo htmlspecialchars($referral['commission_rate'] ?? 'Not Set'); ?></span>
                        </div>
                        <div class="data-item" style="text-align: right;">
                            <span class="data-label">Frequency</span>
                            <span class="data-value"><?php echo htmlspecialchars($referral['payout_frequency'] ?? 'N/A'); ?></span>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Right Column -->
        <div style="display: flex; flex-direction: column; gap: 24px;">
            
            <div class="premium-card">
                <h3 class="card-title"><div class="card-icon"><i data-lucide="landmark"></i></div> Banking Information</h3>
                <div class="data-list">
                    <div class="data-item">
                        <span class="data-label">Account Name & Bank</span>
                        <span class="data-value"><?php echo htmlspecialchars($referral['account_name'] ?? 'N/A'); ?> &bull; <?php echo htmlspecialchars($referral['bank_name'] ?? 'N/A'); ?></span>
                    </div>
                    <div style="display: flex; gap: 24px;">
                        <div class="data-item" style="flex:1;">
                            <span class="data-label">Account Number</span>
                            <span class="data-value" style="font-family: monospace; letter-spacing:1px;"><?php echo htmlspecialchars($referral['account_number'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="data-item" style="flex:1;">
                            <span class="data-label">IFSC Code</span>
                            <span class="data-value" style="font-family: monospace; letter-spacing:1px;"><?php echo htmlspecialchars($referral['ifsc_code'] ?? 'N/A'); ?></span>
                        </div>
                    </div>
                    <div class="data-item">
                        <span class="data-label">UPI ID</span>
                        <span class="data-value"><?php echo htmlspecialchars($referral['upi_id'] ?? 'N/A'); ?></span>
                    </div>
                </div>
            </div>

            <div class="premium-card">
                <h3 class="card-title"><div class="card-icon"><i data-lucide="file-check-2"></i></div> KYC & Documents</h3>
                
                <div class="premium-doc-box">
                    <div class="doc-info">
                        <span class="doc-title">PAN Card</span>
                        <span class="doc-value"><?php echo htmlspecialchars($referral['pan_number'] ?? 'N/A'); ?></span>
                    </div>
                    <?php if (!empty($referral['pan_document_path'])): ?>
                        <a href="<?php echo htmlspecialchars($referral['pan_document_path']); ?>" target="_blank" class="btn btn-sm" style="background:#f1f5f9; color:#475569; border:none;"><i data-lucide="eye" style="width:14px;height:14px;"></i> View</a>
                    <?php endif; ?>
                </div>

                <div class="premium-doc-box">
                    <div class="doc-info">
                        <span class="doc-title">Aadhar Card</span>
                        <span class="doc-value"><?php echo htmlspecialchars($referral['aadhar_number'] ?? 'N/A'); ?></span>
                    </div>
                    <?php if (!empty($referral['aadhar_document_path'])): ?>
                        <a href="<?php echo htmlspecialchars($referral['aadhar_document_path']); ?>" target="_blank" class="btn btn-sm" style="background:#f1f5f9; color:#475569; border:none;"><i data-lucide="eye" style="width:14px;height:14px;"></i> View</a>
                    <?php endif; ?>
                </div>

                <div class="premium-doc-box">
                    <div class="doc-info">
                        <span class="doc-title">Bank Proof</span>
                        <span class="doc-value" style="color: #64748b; font-weight:500;">Cheque / Passbook</span>
                    </div>
                    <?php if (!empty($referral['bank_document_path'])): ?>
                        <a href="<?php echo htmlspecialchars($referral['bank_document_path']); ?>" target="_blank" class="btn btn-sm" style="background:#f1f5f9; color:#475569; border:none;"><i data-lucide="eye" style="width:14px;height:14px;"></i> View</a>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="premium-card">
                <h3 class="card-title"><div class="card-icon"><i data-lucide="sliders"></i></div> Management & Branch</h3>
                <div class="data-list">
                    <div style="display: flex; gap: 24px;">
                        <div class="data-item" style="flex:1;">
                            <span class="data-label">Mapped Branch</span>
                            <span class="data-value"><?php echo htmlspecialchars($referral['mapped_branch'] ?? ''); ?></span>
                        </div>
                        <div class="data-item" style="flex:1;">
                            <span class="data-label">Assigned RM</span>
                            <span class="data-value"><?php echo htmlspecialchars($referral['assigned_rm'] ?? 'Unassigned'); ?></span>
                        </div>
                    </div>
                    <div class="data-item">
                        <span class="data-label">Registered On</span>
                        <span class="data-value" style="color: #64748b;"><?php echo htmlspecialchars(date('d M Y, h:i A', strtotime($referral['created_at']))); ?></span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
async function deleteReferral(id) {
    if(!confirm("Are you sure you want to delete this referral partner? This action cannot be undone.")) return;
    try {
        const fd = new FormData();
        fd.append('id', id);
        const res = await fetch('?api=delete_referral', { method: 'POST', body: fd });
        const data = await res.json();
        if(data.success) {
            showNotification(data.message, 'success');
            setTimeout(() => window.location.href = 'referrals_list.php', 1500);
        } else {
            showNotification(data.error, 'error');
        }
    } catch(err) {
        showNotification("Network error", "error");
    }
}
</script>
<?php require_once 'footer.php'; ?>
