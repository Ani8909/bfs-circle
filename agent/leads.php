<?php
require_once 'includes/header.php';

// Get Agent Details
$stmt = $db->prepare("SELECT * FROM referrals WHERE user_id = ?");
$stmt->execute([$agent_id]);
$agent = $stmt->fetch();
$referral_id = $agent['referral_id'] ?? '';

?>

<div style="margin-bottom: 20px;">
    <h2 style="font-family: 'Outfit'; font-size: 22px; color: var(--text-primary);">My Leads</h2>
    <p style="color: var(--text-muted); font-size: 14px;">Track the status of your submitted leads.</p>
</div>

<!-- Tabs -->
<div style="display: flex; gap: 10px; margin-bottom: 20px; overflow-x: auto; padding-bottom: 5px;">
    <a href="leads.php" style="padding: 6px 14px; background: var(--primary); color: white; border-radius: 20px; font-size: 13px; font-weight: 500; text-decoration: none; white-space: nowrap;">All Leads</a>
    <a href="leads.php?status=Pending" style="padding: 6px 14px; background: white; color: var(--text-muted); border: 1px solid var(--border); border-radius: 20px; font-size: 13px; font-weight: 500; text-decoration: none; white-space: nowrap;">Pending</a>
    <a href="leads.php?status=Approved" style="padding: 6px 14px; background: white; color: var(--text-muted); border: 1px solid var(--border); border-radius: 20px; font-size: 13px; font-weight: 500; text-decoration: none; white-space: nowrap;">Approved</a>
    <a href="leads.php?status=Rejected" style="padding: 6px 14px; background: white; color: var(--text-muted); border: 1px solid var(--border); border-radius: 20px; font-size: 13px; font-weight: 500; text-decoration: none; white-space: nowrap;">Rejected</a>
</div>

<div style="display: flex; flex-direction: column; gap: 12px;">
<?php
if ($referral_id) {
    $filter = $_GET['status'] ?? '';
    
    $query = "SELECT * FROM applicants WHERE referral_id = ?";
    $params = [$referral_id];
    
    if ($filter === 'Pending') {
        $query .= " AND overall_status = 'Phase 1'";
    } elseif ($filter === 'Approved') {
        $query .= " AND (overall_status IN ('Phase 2', 'Phase 3', 'Phase 4', 'Completed'))";
    } elseif ($filter === 'Rejected') {
        $query .= " AND overall_status = 'Rejected'";
    }
    
    $query .= " ORDER BY id DESC";
    
    $stmt_leads = $db->prepare($query);
    $stmt_leads->execute($params);
    $leads = $stmt_leads->fetchAll();
    
    if (count($leads) > 0) {
        foreach ($leads as $app) {
            $status_class = 'badge-pending';
            if ($app['overall_status'] === 'Rejected') $status_class = 'badge-rejected';
            elseif ($app['overall_status'] === 'Completed') $status_class = 'badge-approved';
            elseif ($app['overall_status'] !== 'Phase 1') $status_class = 'badge-approved';
            
            echo '<div class="card" style="padding: 16px;">';
            echo '<div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">';
            echo '<div>';
            echo '<div style="font-weight: 600; font-size: 16px; color: var(--text-primary);">' . htmlspecialchars($app['customer_name']) . '</div>';
            echo '<div style="font-size: 13px; color: var(--text-muted); margin-top: 4px; display: flex; align-items: center; gap: 4px;"><i data-lucide="phone" style="width:12px;height:12px;"></i> ' . htmlspecialchars($app['mobile']) . '</div>';
            echo '</div>';
            echo '<span class="badge ' . $status_class . '">' . htmlspecialchars($app['overall_status']) . '</span>';
            echo '</div>';
            
            echo '<div style="display: flex; justify-content: space-between; align-items: center; padding-top: 12px; border-top: 1px dashed var(--border);">';
            echo '<div style="font-size: 12px; color: var(--text-muted);">Amount: <strong style="color: var(--text-primary);">₹' . number_format($app['loan_amount_requested']) . '</strong></div>';
            echo '<div style="font-size: 12px; color: var(--text-muted);">' . date('M d, Y', strtotime($app['created_at'])) . '</div>';
            echo '</div>';
            echo '</div>';
        }
    } else {
        echo '<div style="text-align: center; padding: 40px; background: white; border-radius: 16px; color: var(--text-muted);">';
        echo '<i data-lucide="list-x" style="width: 48px; height: 48px; margin-bottom: 12px; opacity: 0.5;"></i>';
        echo '<p>No leads found in this category.</p>';
        echo '</div>';
    }
}
?>
</div>

<script>
    // Highlight the active tab filter based on URL
    document.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const status = urlParams.get('status');
        const tabs = document.querySelectorAll('div[style*="overflow-x"] a');
        
        tabs.forEach(tab => {
            if (status) {
                if (tab.textContent === status) {
                    tab.style.background = 'var(--primary)';
                    tab.style.color = 'white';
                    tab.style.border = 'none';
                } else {
                    tab.style.background = 'white';
                    tab.style.color = 'var(--text-muted)';
                    tab.style.border = '1px solid var(--border)';
                }
            }
        });
    });
</script>

<?php require_once 'includes/footer.php'; ?>
