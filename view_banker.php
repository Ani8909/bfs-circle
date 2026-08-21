<?php
require_once 'config.php';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $db->prepare("SELECT * FROM bankers WHERE id = ?");
$stmt->execute([$id]);
$banker = $stmt->fetch();

if (!$banker) {
    echo "Bank Contact not found.";
    exit;
}

$page_title = 'Bank Contact: ' . htmlspecialchars($banker['full_name']);
$page_subtitle = ' Detailed view of relationship manager contact details';
require_once 'header.php';
?>

<div id="view-banker-profile" class="view-container">
    <div class="card">
        <div class="card-title-bar">
            <h2>Contact Details</h2>
            <div class="actions">
                <a href="bankers_list.php" class="btn btn-secondary"><i data-lucide="arrow-left"></i> Back to List</a>
                <?php if (($_SESSION['role'] ?? '') === 'Admin'): ?>
                <a href="edit_banker.php?id=<?php echo $banker['id']; ?>" class="btn btn-primary" style="margin-left:10px;"><i data-lucide="edit"></i> Edit Contact</a>
                <button type="button" class="btn" style="background:#fef2f2; color:#ef4444; border:1px solid #fca5a5; margin-left:10px;" onclick="deleteBanker(<?php echo $banker['id']; ?>)">
                    <i data-lucide="trash-2"></i> Delete
                </button>
                <?php endif; ?>
            </div>
        </div>

        <div style="display: flex; gap: 20px; align-items: flex-start; flex-wrap: wrap;">
            <!-- Profile Info -->
            <div style="flex: 1; min-width: 300px;">
                <h3 style="margin-bottom: 10px;">Bank & Position</h3>
                <table class="data-table">
                    <tr><th style="width: 200px;">Full Name</th><td><strong><?php echo htmlspecialchars($banker['full_name']); ?></strong></td></tr>
                    <tr><th>Bank Name</th><td><span class="badge badge-info" style="font-size:13px; padding:6px 10px;"><?php echo htmlspecialchars($banker['bank_name']); ?></span></td></tr>
                    <tr><th>IFSC Code</th><td style="text-transform:uppercase; font-weight:600; letter-spacing:1px;"><?php echo htmlspecialchars($banker['ifsc_code'] ?? 'N/A'); ?></td></tr>
                    <tr><th>Designation</th><td><?php echo htmlspecialchars($banker['designation']); ?></td></tr>
                    <tr><th>Location</th><td><?php echo htmlspecialchars(($banker['city'] ?? 'N/A') . ', ' . ($banker['state'] ?? 'N/A')); ?></td></tr>
                    <tr><th>Branch Address</th><td><?php echo nl2br(htmlspecialchars($banker['address'])); ?></td></tr>
                </table>

                <h3 style="margin-top: 20px; margin-bottom: 10px;">Contact Information</h3>
                <table class="data-table">
                    <tr><th style="width: 200px;">Phone Number</th><td><i data-lucide="phone" style="width:14px;height:14px;color:#64748b;margin-right:4px;"></i> <?php echo htmlspecialchars($banker['contact_number']); ?></td></tr>
                    <tr><th>Email Address</th><td><i data-lucide="mail" style="width:14px;height:14px;color:#64748b;margin-right:4px;"></i> <a href="mailto:<?php echo htmlspecialchars($banker['official_email']); ?>"><?php echo htmlspecialchars($banker['official_email']); ?></a></td></tr>
                </table>
            </div>

            <!-- Loan Portfolio Criteria -->
            <div style="width: 350px; background: #f8fafc; padding: 20px; border-radius: 10px; border: 1px solid #e2e8f0;">
                <h3 style="display:flex; align-items:center; gap:8px;"><i data-lucide="briefcase" style="color:#d97706;"></i> Loan Portfolio Criteria</h3>
                
                <div style="margin-top: 20px;">
                    <strong>Supported Categories:</strong>
                    <div style="margin-top: 10px; display:flex; flex-wrap:wrap; gap:8px;">
                        <?php 
                        $cats = explode(',', $banker['loan_category'] ?? '');
                        if(empty(array_filter($cats))) {
                            echo "<span style='color:#64748b;'>Not Specified</span>";
                        } else {
                            foreach($cats as $c) {
                                $c = trim($c);
                                if($c) echo "<span class='badge' style='background:#dbeafe; color:#1d4ed8; border:1px solid #bfdbfe;'>".htmlspecialchars($c)."</span>";
                            }
                        }
                        ?>
                    </div>
                </div>

                <div style="margin-top: 20px; display:flex; flex-direction:column; gap:15px;">
                    <div style="background:white; padding:12px; border-radius:6px; border:1px solid #e2e8f0;">
                        <div style="font-size:12px; color:#64748b; font-weight:600; text-transform:uppercase;">Minimum Loan Limit</div>
                        <div style="font-size:18px; font-weight:700; color:#0f172a; margin-top:4px;">₹ <?php echo number_format((float)$banker['min_loan_limit']); ?></div>
                    </div>
                    <div style="background:white; padding:12px; border-radius:6px; border:1px solid #e2e8f0;">
                        <div style="font-size:12px; color:#64748b; font-weight:600; text-transform:uppercase;">Maximum Loan Limit</div>
                        <div style="font-size:18px; font-weight:700; color:#0f172a; margin-top:4px;">₹ <?php echo number_format((float)$banker['max_loan_limit']); ?></div>
                    </div>
                </div>
                
                <h3 style="display:flex; align-items:center; gap:8px; margin-top:30px;"><i data-lucide="map" style="color:#d97706;"></i> Serviceability Area</h3>
                <div style="margin-top: 15px; display:flex; flex-direction:column; gap:10px;">
                    <div style="background:white; padding:12px; border-radius:6px; border:1px solid #e2e8f0;">
                        <div style="font-size:12px; color:#64748b; font-weight:600; text-transform:uppercase;">Coverage Type</div>
                        <div style="font-size:15px; font-weight:600; color:#0f172a; margin-top:4px;">
                            <?php echo htmlspecialchars($banker['coverage_type'] ?: 'Not Specified'); ?>
                        </div>
                    </div>
                    <?php if(!empty($banker['coverage_type'])): ?>
                    <div style="background:white; padding:12px; border-radius:6px; border:1px solid #e2e8f0;">
                        <div style="font-size:12px; color:#64748b; font-weight:600; text-transform:uppercase;">Serviceable Areas / Radius</div>
                        <div style="font-size:14px; font-weight:500; color:#334155; margin-top:4px; line-height:1.4;">
                            <?php echo htmlspecialchars($banker['coverage_details'] ?: 'N/A'); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>

<script>
function deleteBanker(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You are about to delete this Bank Contact permanently. This action cannot be undone.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('id', id);

            fetch('api.php?api=delete_banker', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'Bank contact has been deleted.',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = 'bankers_list.php';
                    });
                } else {
                    Swal.fire('Error!', data.error || 'Failed to delete contact.', 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error!', 'A system error occurred.', 'error');
            });
        }
    });
}
</script>
