<?php
require_once __DIR__ . '/../config.php';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $db->prepare("SELECT b.*, u.username, u.is_active, u.last_active FROM bankers b JOIN users u ON b.user_id = u.id WHERE b.id = ?");
$stmt->execute([$id]);
$banker = $stmt->fetch();

if (!$banker) {
    echo "Banker not found.";
    exit;
}

$page_title = 'Banker Profile: ' . htmlspecialchars($banker['full_name']);
$page_subtitle = 'ðŸ‘¥ Detailed view of banker profile and KYC documents';
require_once __DIR__ . '/header.php';
?>

<div id="view-banker-profile" class="view-container">
    <div class="card">
        <div class="card-title-bar">
            <h2>Profile Details</h2>
            <div class="actions">
                <a href="bankers_list.php" class="btn btn-secondary"><i data-lucide="arrow-left"></i> Back to List</a>
            </div>
        </div>

        <div style="display: flex; gap: 20px; align-items: flex-start; flex-wrap: wrap;">
            <!-- Profile Info -->
            <div style="flex: 1; min-width: 300px;">
                <table class="data-table">
                    <tr><th style="width: 200px;">Employee ID</th><td><strong><?php echo htmlspecialchars($banker['employee_id']); ?></strong></td></tr>
                    <tr><th>Full Name</th><td><?php echo htmlspecialchars($banker['full_name']); ?></td></tr>
                    <tr><th>Date of Birth</th><td><?php echo htmlspecialchars($banker['dob']); ?></td></tr>
                    <tr><th>Gender</th><td><?php echo htmlspecialchars($banker['gender']); ?></td></tr>
                    <tr><th>Contact Number</th><td><?php echo htmlspecialchars($banker['contact_number']); ?></td></tr>
                    <tr><th>Personal Email</th><td><?php echo htmlspecialchars($banker['personal_email']); ?></td></tr>
                    <tr><th>Address</th><td><?php echo nl2br(htmlspecialchars($banker['address'])); ?></td></tr>
                </table>

                <h3 style="margin-top: 20px; margin-bottom: 10px;">Official Details</h3>
                <table class="data-table">
                    <tr><th style="width: 200px;">Designation</th><td><?php echo htmlspecialchars($banker['designation']); ?></td></tr>
                    <tr><th>Department</th><td><?php echo htmlspecialchars($banker['department']); ?></td></tr>
                    <tr><th>Branch Name</th><td><?php echo htmlspecialchars($banker['branch_name']); ?></td></tr>
                    <tr><th>Date of Joining</th><td><?php echo htmlspecialchars($banker['doj']); ?></td></tr>
                    <tr><th>Official Email</th><td><?php echo htmlspecialchars($banker['official_email']); ?></td></tr>
                    <tr><th>Work Phone</th><td><?php echo htmlspecialchars($banker['work_phone']); ?></td></tr>
                    <tr><th>System Role</th><td><?php echo htmlspecialchars($banker['access_level']); ?></td></tr>
                    <tr><th>Account Status</th>
                        <td>
                            <?php if ($banker['is_active']): ?>
                                <span class="badge badge-success">Active</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Deactivated</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr><th>Last Login</th><td><?php echo htmlspecialchars($banker['last_active'] ?: 'Never'); ?></td></tr>
                </table>

                <h3 style="margin-top: 20px; margin-bottom: 10px;">KYC & Documents</h3>
                <table class="data-table">
                    <tr><th style="width: 200px;">PAN Number</th><td><?php echo htmlspecialchars($banker['pan_number']); ?></td></tr>
                    <tr><th>Aadhar Number</th><td><?php echo htmlspecialchars($banker['aadhar_number']); ?></td></tr>
                    <tr><th>Background Check</th>
                        <td>
                            <span class="badge badge-<?php echo ($banker['bg_verification_status']=='Approved' ? 'success' : ($banker['bg_verification_status']=='Pending' ? 'warning' : 'danger')); ?>">
                                <?php echo htmlspecialchars($banker['bg_verification_status']); ?>
                            </span>
                        </td>
                    </tr>
                </table>

                <h3 style="margin-top: 20px; margin-bottom: 10px;">Emergency Contact</h3>
                <table class="data-table">
                    <tr><th style="width: 200px;">Name</th><td><?php echo htmlspecialchars($banker['emergency_contact_name']); ?></td></tr>
                    <tr><th>Relation</th><td><?php echo htmlspecialchars($banker['emergency_relation']); ?></td></tr>
                    <tr><th>Phone Number</th><td><?php echo htmlspecialchars($banker['emergency_phone']); ?></td></tr>
                </table>
            </div>

            <!-- Documents View -->
            <div style="width: 300px; background: #f8fafc; padding: 20px; border-radius: 10px; border: 1px solid #e2e8f0;">
                <h3>Documents</h3>
                
                <div style="margin-top: 20px;">
                    <strong>Employee Photo:</strong><br>
                    <?php if ($banker['photo_path']): ?>
                        <img src="<?php echo htmlspecialchars("../" . $banker['photo_path']); ?>" style="width: 100%; border-radius: 8px; margin-top: 10px; border: 1px solid #ccc;">
                    <?php else: ?>
                        <div style="padding: 20px; background: #e2e8f0; text-align: center; margin-top: 10px; border-radius: 8px;">No Photo Available</div>
                    <?php endif; ?>
                </div>

                <div style="margin-top: 30px;">
                    <strong>ID Proof Document:</strong><br>
                    <?php if ($banker['id_proof_path']): ?>
                        <a href="<?php echo htmlspecialchars("../" . $banker['id_proof_path']); ?>" target="_blank" class="btn btn-secondary" style="display: block; text-align: center; margin-top: 10px;">
                            <i data-lucide="external-link"></i> View ID Proof
                        </a>
                    <?php else: ?>
                        <div style="padding: 10px; background: #e2e8f0; text-align: center; margin-top: 10px; border-radius: 8px;">No ID Proof Uploaded</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>

