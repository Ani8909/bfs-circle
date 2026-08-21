<?php
require_once __DIR__ . '/../config.php';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $db->prepare("SELECT * FROM referrals WHERE id = ?");
$stmt->execute([$id]);
$referral = $stmt->fetch();

if (!$referral) {
    echo "Referral partner not found.";
    exit;
}

$page_title = 'Referral Profile: ' . htmlspecialchars($referral['full_name']);
$page_subtitle = 'ðŸ‘¥ Detailed view of referral partner profile, commission structure, and KYC documents';
require_once __DIR__ . '/header.php';
?>

<div id="view-referral-profile" class="view-container">
    <div class="card">
        <div class="card-title-bar">
            <h2>Partner Profile Details</h2>
            <div class="actions">
                <a href="referrals_list.php" class="btn btn-secondary"><i data-lucide="arrow-left"></i> Back to List</a>
            </div>
        </div>

        <div style="display: flex; gap: 20px; align-items: flex-start; flex-wrap: wrap;">
            <!-- Profile Info -->
            <div style="flex: 1; min-width: 300px;">
                <h3 style="margin-top: 0; margin-bottom: 10px; color: var(--primary);">Basic Information</h3>
                <table class="data-table">
                    <tr><th style="width: 200px;">Referral ID</th><td><strong style="color: var(--primary);"><?php echo htmlspecialchars($referral['referral_id']); ?></strong></td></tr>
                    <tr><th>Referrer Type</th><td><?php echo htmlspecialchars($referral['referrer_type']); ?></td></tr>
                    <tr><th>Full Name</th><td><?php echo htmlspecialchars($referral['full_name']); ?></td></tr>
                    <tr><th>Date of Birth</th><td><?php echo htmlspecialchars($referral['dob']); ?></td></tr>
                    <tr><th>Mobile Number</th><td><?php echo htmlspecialchars($referral['mobile']); ?></td></tr>
                    <tr><th>Email Address</th><td><?php echo htmlspecialchars($referral['email']); ?></td></tr>
                    <tr><th>City & State</th><td><?php echo htmlspecialchars($referral['city_state']); ?></td></tr>
                </table>

                <h3 style="margin-top: 25px; margin-bottom: 10px; color: var(--primary);">Banking & Payout Details</h3>
                <table class="data-table">
                    <tr><th style="width: 200px;">Account Holder Name</th><td><?php echo htmlspecialchars($referral['account_name']); ?></td></tr>
                    <tr><th>Bank Name</th><td><?php echo htmlspecialchars($referral['bank_name']); ?></td></tr>
                    <tr><th>Account Number</th><td><?php echo htmlspecialchars($referral['account_number']); ?></td></tr>
                    <tr><th>IFSC Code</th><td><?php echo htmlspecialchars($referral['ifsc_code']); ?></td></tr>
                    <tr><th>UPI ID</th><td><?php echo htmlspecialchars($referral['upi_id'] ?: 'N/A'); ?></td></tr>
                </table>

                <h3 style="margin-top: 25px; margin-bottom: 10px; color: var(--primary);">Commission Structure</h3>
                <table class="data-table">
                    <tr><th style="width: 200px;">Commission Rate/Slab</th><td><strong><?php echo htmlspecialchars($referral['commission_rate']); ?></strong></td></tr>
                    <tr><th>Payout Frequency</th><td><?php echo htmlspecialchars($referral['payout_frequency']); ?></td></tr>
                </table>

                <h3 style="margin-top: 25px; margin-bottom: 10px; color: var(--primary);">Internal Mapping</h3>
                <table class="data-table">
                    <tr><th style="width: 200px;">Mapped Branch</th><td><?php echo htmlspecialchars($referral['mapped_branch']); ?></td></tr>
                    <tr><th>Assigned RM</th><td><?php echo htmlspecialchars($referral['assigned_rm']); ?></td></tr>
                    <tr><th>Status</th>
                        <td>
                            <span class="badge badge-<?php echo ($referral['status']=='Active' ? 'success' : ($referral['status']=='Pending Approval' ? 'warning' : 'danger')); ?>">
                                <?php echo htmlspecialchars($referral['status']); ?>
                            </span>
                        </td>
                    </tr>
                    <tr><th>Registered On</th><td><?php echo htmlspecialchars(date('d M Y h:i A', strtotime($referral['created_at']))); ?></td></tr>
                </table>
            </div>

            <!-- Documents View -->
            <div style="width: 320px; background: #f8fafc; padding: 20px; border-radius: 10px; border: 1px solid #e2e8f0;">
                <h3 style="margin-top:0;">KYC & Documents</h3>
                
                <div style="margin-top: 20px;">
                    <strong>PAN Card Number:</strong><br>
                    <div style="padding: 8px; background: #fff; border: 1px solid #cbd5e1; border-radius: 5px; margin-top: 5px; font-weight: bold;">
                        <?php echo htmlspecialchars($referral['pan_number']); ?>
                    </div>
                </div>

                <div style="margin-top: 15px;">
                    <strong>Aadhar Card Number:</strong><br>
                    <div style="padding: 8px; background: #fff; border: 1px solid #cbd5e1; border-radius: 5px; margin-top: 5px; font-weight: bold;">
                        <?php echo htmlspecialchars($referral['aadhar_number']); ?>
                    </div>
                </div>

                <div style="margin-top: 30px; border-top: 1px dashed #cbd5e1; padding-top: 20px;">
                    <strong>Bank Document (Cheque/Passbook):</strong><br>
                    <?php if ($referral['bank_document_path']): ?>
                        <a href="<?php echo htmlspecialchars("../" . $referral['bank_document_path']); ?>" target="_blank" class="btn btn-secondary" style="display: block; text-align: center; margin-top: 10px; width: 100%;">
                            <i data-lucide="external-link"></i> View Bank Document
                        </a>
                    <?php else: ?>
                        <div style="padding: 10px; background: #e2e8f0; text-align: center; margin-top: 10px; border-radius: 8px; font-size: 13px;">No Bank Document Uploaded</div>
                    <?php endif; ?>
                </div>

                <div style="margin-top: 20px;">
                    <strong>PAN Card Document:</strong><br>
                    <?php if ($referral['pan_document_path']): ?>
                        <a href="<?php echo htmlspecialchars("../" . $referral['pan_document_path']); ?>" target="_blank" class="btn btn-secondary" style="display: block; text-align: center; margin-top: 10px; width: 100%;">
                            <i data-lucide="external-link"></i> View PAN Document
                        </a>
                    <?php else: ?>
                        <div style="padding: 10px; background: #e2e8f0; text-align: center; margin-top: 10px; border-radius: 8px; font-size: 13px;">No PAN Document Uploaded</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>

