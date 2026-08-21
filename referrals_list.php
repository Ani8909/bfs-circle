<?php
require_once 'config.php';
$page_title = 'Referrals & DSA Directory';
$page_subtitle = ' Manage external referral partners and DSAs';
require_once 'header.php';

// Pagination and Filter Setup
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$per_page = 10;

$search = isset($_GET['search']) ? strtolower(trim($_GET['search'])) : '';
$type_filter = isset($_GET['type']) ? trim($_GET['type']) : '';
$completion_filter = isset($_GET['completion']) ? trim($_GET['completion']) : '';

// Fetch ALL to allow PHP-based filtering (since completion is dynamic)
$stmt = $db->query("SELECT * FROM referrals ORDER BY created_at DESC");
$all_referrals = $stmt->fetchAll();

// Filter in PHP
$filtered_referrals = [];
foreach ($all_referrals as $ref) {
    // Search Filter
    $matches_search = true;
    if ($search !== '') {
        $search_content = strtolower($ref['full_name'] . ' ' . $ref['referral_id'] . ' ' . $ref['mobile']);
        if (strpos($search_content, $search) === false) {
            $matches_search = false;
        }
    }
    
    // Type Filter
    $matches_type = true;
    if ($type_filter !== '' && $ref['referrer_type'] !== $type_filter) {
        $matches_type = false;
    }
    
    // Calculate Completion
    $fields = [
        $ref['mobile'], $ref['dob'], $ref['email'],
        $ref['account_number'], $ref['ifsc_code'], 
        $ref['pan_number'], $ref['aadhar_number'], 
        $ref['commission_rate'],
        $ref['bank_document_path'], $ref['pan_document_path']
    ];
    $filled = 0;
    foreach($fields as $f) { if(!empty($f)) $filled++; }
    $completion = round(($filled / count($fields)) * 100);
    $ref['calculated_completion'] = $completion; // Store for later
    
    // Completion Filter
    $matches_completion = true;
    if ($completion_filter === 'incomplete' && $completion === 100) {
        $matches_completion = false;
    } else if ($completion_filter === 'complete' && $completion < 100) {
        $matches_completion = false;
    }
    
    if ($matches_search && $matches_type && $matches_completion) {
        $filtered_referrals[] = $ref;
    }
}

$total_items = count($filtered_referrals);
$total_pages = ceil($total_items / $per_page);
if ($total_pages == 0) $total_pages = 1;
if ($page > $total_pages) $page = $total_pages;

$offset = ($page - 1) * $per_page;
$paginated_referrals = array_slice($filtered_referrals, $offset, $per_page);
?>

<style>
.filter-form {
    display: flex;
    gap: 16px;
    align-items: center;
    background: #f8fafc;
    padding: 12px 16px;
    border-radius: 8px;
    margin-bottom: 24px;
    border: 1px solid var(--border);
    flex-wrap: wrap;
}
.search-wrapper {
    flex: 1;
    min-width: 250px;
    position: relative;
    display: flex;
    align-items: center;
}
.search-wrapper svg {
    position: absolute;
    left: 14px;
    color: #94a3b8;
    z-index: 1;
}
.search-wrapper input {
    width: 100%;
    padding: 10px 16px 10px 40px;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    font-size: 14px;
    transition: all 0.2s;
    background: white;
}
.search-wrapper input:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px var(--primary-glow);
    outline: none;
}
.filter-select {
    width: auto;
    min-width: 180px;
    flex-shrink: 0;
    padding: 10px 36px 10px 16px;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    font-size: 13.5px;
    background-color: white;
    color: #475569;
    font-weight: 500;
    cursor: pointer;
    appearance: none;
    background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 12px center;
    background-size: 16px;
}
.pagination-controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 0 0 0;
    border-top: 1px solid var(--border);
    margin-top: 20px;
}
.pagination-btn {
    padding: 8px 16px;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    background: white;
    color: #475569;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.2s;
}
.pagination-btn:hover:not(.disabled) {
    background: #f1f5f9;
    color: var(--primary);
    border-color: #94a3b8;
}
.pagination-btn.disabled {
    opacity: 0.5;
    cursor: not-allowed;
    pointer-events: none;
}
</style>

<div id="view-referrals" class="view-container">
    <div class="card">
        <div class="card-title-bar" style="margin-bottom: 20px;">
            <h2>Registered Partners (<?php echo $total_items; ?>)</h2>
            <div class="actions">
                <?php if (($_SESSION['role'] ?? '') === 'Admin'): ?>
                <a href="add_referral.php" class="btn btn-primary" style="box-shadow: 0 4px 12px rgba(249,115,22,0.25);"><i data-lucide="plus"></i> Add New Referral</a>
                <?php endif; ?>
            </div>
        </div>

        <form method="GET" action="referrals_list.php" class="filter-form">
            <div class="search-wrapper">
                <i data-lucide="search" style="width:18px;height:18px;"></i>
                <input type="text" name="search" placeholder="Search partners by name, ID, or mobile number..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <select name="type" class="filter-select" onchange="this.form.submit()">
                <option value="">All Partner Types</option>
                <option value="Builder" <?php if($type_filter=='Builder') echo 'selected'; ?>>Builder / Real Estate</option>
                <option value="CA" <?php if($type_filter=='CA') echo 'selected'; ?>>Chartered Accountant</option>
                <option value="DSA" <?php if($type_filter=='DSA') echo 'selected'; ?>>Financial Advisor / DSA</option>
                <option value="Agent" <?php if($type_filter=='Agent') echo 'selected'; ?>>Individual Agent</option>
            </select>
            <select name="completion" class="filter-select" onchange="this.form.submit()">
                <option value="">All Profiles</option>
                <option value="incomplete" <?php if($completion_filter=='incomplete') echo 'selected'; ?>>Incomplete Only</option>
                <option value="complete" <?php if($completion_filter=='complete') echo 'selected'; ?>>100% Complete</option>
            </select>
            <button type="submit" style="display:none;">Search</button>
        </form>

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
                    <?php if (empty($paginated_referrals)): ?>
                        <tr><td colspan="7" class="text-center">No referral partners found matching criteria.</td></tr>
                    <?php else: ?>
                        <?php foreach ($paginated_referrals as $ref): ?>
                            <tr>
                                <td><span class="badge badge-info"><?php echo htmlspecialchars($ref['referral_id'] ?? ''); ?></span></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($ref['full_name'] ?? ''); ?></strong>
                                    <?php if ($ref['calculated_completion'] < 100): ?>
                                        <br><span style="font-size: 11px; color: #ef4444; font-weight: 600;"><i data-lucide="alert-circle" style="width:10px;height:10px;"></i> Incomplete (<?php echo $ref['calculated_completion']; ?>%)</span>
                                    <?php else: ?>
                                        <br><span style="font-size: 11px; color: #10b981; font-weight: 600;"><i data-lucide="check-circle" style="width:10px;height:10px;"></i> 100% Complete</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($ref['referrer_type'] ?? ''); ?></td>
                                <td>
                                    <div><i data-lucide="phone" style="width:14px;height:14px;"></i> <?php echo htmlspecialchars($ref['mobile'] ?? ''); ?></div>
                                </td>
                                <td><?php echo htmlspecialchars($ref['city_state'] ?? ''); ?></td>
                                <td>
                                    <?php 
                                        $bg_class = '';
                                        if($ref['status'] == 'Active') $bg_class = 'success';
                                        elseif($ref['status'] == 'Pending Approval') $bg_class = 'warning';
                                        else $bg_class = 'danger';
                                    ?>
                                    <span class="badge badge-<?php echo $bg_class; ?>"><?php echo htmlspecialchars($ref['status'] ?? ''); ?></span>
                                </td>
                                <td>
                                    <div style="display:flex; gap: 6px;">
                                        <button class="btn btn-sm btn-secondary" title="Set Reminder" style="padding: 6px 8px; color:#f59e0b;" onclick="event.stopPropagation(); openReminderModal('Referral', <?php echo $ref['id']; ?>, '<?php echo addslashes(htmlspecialchars($ref['full_name'])); ?>')">
                                            <i data-lucide="bell"></i>
                                        </button>
                                        <a href="view_referral.php?id=<?php echo $ref['id']; ?>" class="btn btn-sm btn-secondary" title="View Profile" style="padding: 6px 8px;">
                                            <i data-lucide="eye"></i>
                                        </a>
                                        <?php if (($_SESSION['role'] ?? '') === 'Admin'): ?>
                                        <a href="edit_referral.php?id=<?php echo $ref['id']; ?>" class="btn btn-sm" title="Edit Profile" style="background:#f1f5f9; color:#475569; border:none; padding: 6px 8px;">
                                            <i data-lucide="edit"></i>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if($total_pages > 1): ?>
        <div class="pagination-controls">
            <?php 
                $q = $_GET; 
            ?>
            
            <?php $q['page'] = $page - 1; ?>
            <a href="?<?php echo http_build_query($q); ?>" class="pagination-btn <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                &laquo; Previous
            </a>
            
            <div style="font-size: 14px; color: var(--text-muted);">
                Page <strong><?php echo $page; ?></strong> of <strong><?php echo $total_pages; ?></strong>
            </div>
            
            <?php $q['page'] = $page + 1; ?>
            <a href="?<?php echo http_build_query($q); ?>" class="pagination-btn <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                Next &raquo;
            </a>
        </div>
        <?php endif; ?>
        
    </div>
</div>

<?php require_once 'footer.php'; ?>
