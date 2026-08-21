<?php
require_once __DIR__ . '/../config.php';
$page_title = 'Referrals & DSA Directory';
$page_subtitle = 'ðŸ‘¥ Manage external referral partners and DSAs';
require_once __DIR__ . '/header.php';

$stmt = $db->query("SELECT * FROM referrals ORDER BY created_at DESC");
$referrals = $stmt->fetchAll();
?>

<div id="view-referrals" class="view-container">
    <div class="card">
        <div class="card-title-bar">
            <h2>Registered Partners</h2>
            <div class="actions">
                <?php if (($_SESSION['role'] ?? '') === 'Admin'): ?>
                <a href="add_referral.php" class="btn btn-primary"><i data-lucide="plus"></i> Add New Referral</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Referral ID</th>
                        <th>Partner Name</th>
                        <th>Type</th>
                        <th>Contact</th>
                        <th>City/State</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($referrals)): ?>
                        <tr><td colspan="7" class="text-center">No referral partners registered yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($referrals as $ref): ?>
                            <tr>
                                <td><span class="badge badge-info"><?php echo htmlspecialchars($ref['referral_id']); ?></span></td>
                                <td><strong><?php echo htmlspecialchars($ref['full_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($ref['referrer_type']); ?></td>
                                <td>
                                    <div><i data-lucide="phone" style="width:14px;height:14px;"></i> <?php echo htmlspecialchars($ref['mobile']); ?></div>
                                </td>
                                <td><?php echo htmlspecialchars($ref['city_state']); ?></td>
                                <td>
                                    <?php 
                                        $bg_class = '';
                                        if($ref['status'] == 'Active') $bg_class = 'success';
                                        elseif($ref['status'] == 'Pending Approval') $bg_class = 'warning';
                                        else $bg_class = 'danger';
                                    ?>
                                    <span class="badge badge-<?php echo $bg_class; ?>"><?php echo htmlspecialchars($ref['status']); ?></span>
                                </td>
                                <td>
                                    <a href="view_referral.php?id=<?php echo $ref['id']; ?>" class="btn btn-sm btn-secondary">
                                        <i data-lucide="eye"></i> View Profile
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>

