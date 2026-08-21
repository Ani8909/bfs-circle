<?php
require_once 'config.php';
$page_title = 'Bankers Directory';
$page_subtitle = ' View and manage external bank relationship managers';
require_once 'header.php';

// Pagination and Filter Setup
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$per_page = 10;

$search = isset($_GET['search']) ? strtolower(trim($_GET['search'])) : '';
$bank_filter = isset($_GET['bank']) ? trim($_GET['bank']) : '';
$bank_type_filter = isset($_GET['bank_type']) ? trim($_GET['bank_type']) : '';
$state_filter = isset($_GET['state']) ? trim($_GET['state']) : '';
$city_filter = isset($_GET['city']) ? trim($_GET['city']) : '';
$category_filter = isset($_GET['category']) ? trim($_GET['category']) : '';
$amount_req = isset($_GET['amount']) ? (float)$_GET['amount'] : 0;

// Fetch all first to allow array filtering
$stmt = $db->query("SELECT * FROM bankers ORDER BY created_at DESC");
$all_bankers = $stmt->fetchAll();

// Unique extracts for dropdowns
$unique_banks = array_unique(array_filter(array_column($all_bankers, 'bank_name')));
sort($unique_banks);

$unique_types = array_unique(array_filter(array_column($all_bankers, 'bank_type')));
sort($unique_types);

$unique_states = array_unique(array_filter(array_column($all_bankers, 'state')));
sort($unique_states);

$unique_cities = array_unique(array_filter(array_column($all_bankers, 'city')));
sort($unique_cities);

// Filter in PHP
$filtered_bankers = [];
foreach ($all_bankers as $banker) {
    // Search Filter
    $matches_search = true;
    if ($search !== '') {
        $search_content = strtolower($banker['full_name'] . ' ' . $banker['bank_name'] . ' ' . $banker['contact_number'] . ' ' . $banker['loan_category'] . ' ' . $banker['city'] . ' ' . $banker['state']);
        if (strpos($search_content, $search) === false) {
            $matches_search = false;
        }
    }
    
    // Dropdown Filters
    $matches_dropdowns = true;
    if ($bank_filter !== '' && strcasecmp(trim($banker['bank_name'] ?? ''), $bank_filter) !== 0) $matches_dropdowns = false;
    if ($bank_type_filter !== '' && strcasecmp(trim($banker['bank_type'] ?? ''), $bank_type_filter) !== 0) $matches_dropdowns = false;
    if ($state_filter !== '' && strcasecmp(trim($banker['state'] ?? ''), $state_filter) !== 0) $matches_dropdowns = false;
    if ($city_filter !== '' && strcasecmp(trim($banker['city'] ?? ''), $city_filter) !== 0) $matches_dropdowns = false;
    
    // Amount Recommendation Engine
    $matches_amount = true;
    if ($amount_req > 0) {
        $min = (float)$banker['min_loan_limit'];
        $max = (float)$banker['max_loan_limit'];
        // Recommend this bank if amount is between its min and max
        if ($amount_req < $min || $amount_req > $max) {
            $matches_amount = false;
        }
    }
    
    // Category Filter
    $matches_category = true;
    if ($category_filter !== '') {
        if (stripos($banker['loan_category'] ?? '', $category_filter) === false) {
            $matches_category = false;
        }
    }
    
    if ($matches_search && $matches_dropdowns && $matches_amount && $matches_category) {
        $filtered_bankers[] = $banker;
    }
}

$total_items = count($filtered_bankers);
$total_pages = ceil($total_items / $per_page);
if ($total_pages == 0) $total_pages = 1;
if ($page > $total_pages) $page = $total_pages;

$offset = ($page - 1) * $per_page;
$paginated_bankers = array_slice($filtered_bankers, $offset, $per_page);
?>

<style>
/* ... existing filter styles ... */
.filter-form {
    display: flex;
    gap: 10px;
    align-items: center;
    background: #f8fafc;
    padding: 12px 16px;
    border-radius: 8px;
    margin-bottom: 24px;
    border: 1px solid var(--border);
    flex-wrap: nowrap;
    overflow-x: auto;
}
.filter-form::-webkit-scrollbar {
    height: 6px;
}
.filter-form::-webkit-scrollbar-thumb {
    background-color: #cbd5e1;
    border-radius: 10px;
}
.search-wrapper {
    flex: 1;
    min-width: 150px;
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
    min-width: 130px;
    flex-shrink: 0;
    padding: 10px 32px 10px 12px;
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

/* Expandable Row Styles */
.expandable-row {
    cursor: pointer;
    transition: background-color 0.2s;
}
.expandable-row:hover {
    background-color: #f8fafc;
}
.expandable-row td {
    padding: 16px !important;
    vertical-align: middle;
}
.details-row {
    display: none;
    background: #f8fafc;
}
.details-row.open {
    display: table-row;
    animation: fadeInDown 0.3s ease-out;
}
.details-content {
    padding: 20px !important;
    border-top: 1px dashed #cbd5e1;
}
.details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
}
.detail-item label {
    display: block;
    font-size: 11px;
    text-transform: uppercase;
    color: #64748b;
    letter-spacing: 0.5px;
    margin-bottom: 4px;
    font-weight: 600;
}
.detail-item .value {
    font-size: 14px;
    color: #0f172a;
    font-weight: 500;
}
@keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
.expand-icon {
    transition: transform 0.3s;
}
.expandable-row.open .expand-icon {
    transform: rotate(180deg);
}
</style>

<div id="view-bankers" class="view-container">
    <div class="card">
        <div class="card-title-bar" style="margin-bottom: 20px;">
            <h2>Bank Contacts (<?php echo $total_items; ?>)</h2>
            <div class="actions">
                <?php if (($_SESSION['role'] ?? '') === 'Admin'): ?>
                <a href="add_banker.php" class="btn btn-primary" style="box-shadow: 0 4px 12px rgba(249,115,22,0.25);"><i data-lucide="plus"></i> Add Bank Contact</a>
                <?php endif; ?>
            </div>
        </div>
        
        <form method="GET" action="bankers_list.php" class="filter-form" id="filter-form">
            <div class="search-wrapper">
                <i data-lucide="search" style="width:18px;height:18px;"></i>
                <input type="text" name="search" placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            
            <div class="search-wrapper" style="min-width: 160px; flex: unset;">
                <i data-lucide="briefcase" style="width:18px;height:18px;color:#d97706;"></i>
                <input type="number" name="amount" placeholder="Loan Amt (₹)..." value="<?php echo $amount_req > 0 ? $amount_req : ''; ?>" style="border-color:#fcd34d;">
            </div>

            <select name="category" class="filter-select" onchange="this.form.submit()">
                <option value="">All Categories</option>
                <?php 
                $opts = ['Home Loan', 'Personal Loan', 'Business Loan', 'Mortgage Loan', 'Education Loan', 'Vehicle Loan'];
                foreach($opts as $opt): 
                ?>
                    <option value="<?php echo htmlspecialchars($opt); ?>" <?php if($category_filter==$opt) echo 'selected'; ?>><?php echo htmlspecialchars($opt); ?></option>
                <?php endforeach; ?>
            </select>
            
            <select name="bank_type" id="filter_bank_type" class="filter-select" onchange="this.form.submit()">
                <option value="">All Bank Types</option>
            </select>

            <select name="bank" id="filter_bank_name" class="filter-select" onchange="this.form.submit()">
                <option value="">All Banks</option>
            </select>
            
            <select name="state" id="filter_state" class="filter-select" onchange="this.form.submit()">
                <option value="">All States</option>
            </select>
            
            <select name="city" id="filter_city" class="filter-select" onchange="this.form.submit()">
                <option value="">All Cities</option>
            </select>
            <button type="submit" style="display:none;">Search</button>
        </form>

        <script src="assets/js/locations.js"></script>
        <script src="assets/js/banks_directory.js"></script>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // PHP selected values
            const selType = "<?php echo addslashes($bank_type_filter); ?>";
            const selBank = "<?php echo addslashes($bank_filter); ?>";
            const selState = "<?php echo addslashes($state_filter); ?>";
            const selCity = "<?php echo addslashes($city_filter); ?>";

            const typeSelect = document.getElementById('filter_bank_type');
            const bankSelect = document.getElementById('filter_bank_name');
            const stateSelect = document.getElementById('filter_state');
            const citySelect = document.getElementById('filter_city');

            // Populate Bank Types
            for (const type in BANK_DIRECTORY) {
                const opt = document.createElement('option');
                opt.value = type;
                opt.textContent = type;
                typeSelect.appendChild(opt);
            }
            if (selType) typeSelect.value = selType;

            // Populate Banks
            function updateBanks() {
                const t = typeSelect.value;
                bankSelect.innerHTML = '<option value="">All Banks</option>';
                if (t && BANK_DIRECTORY[t]) {
                    BANK_DIRECTORY[t].forEach(b => {
                        const opt = document.createElement('option');
                        opt.value = b;
                        opt.textContent = b;
                        bankSelect.appendChild(opt);
                    });
                } else {
                    // Populate ALL banks if no type selected
                    for (const ty in BANK_DIRECTORY) {
                        BANK_DIRECTORY[ty].forEach(b => {
                            const opt = document.createElement('option');
                            opt.value = b;
                            opt.textContent = b;
                            bankSelect.appendChild(opt);
                        });
                    }
                }
            }
            
            // Initial bank pop
            updateBanks();
            if (selBank) bankSelect.value = selBank;

            // Populate States
            Object.keys(locationData).forEach(s => {
                const opt = document.createElement('option');
                opt.value = s;
                opt.textContent = s;
                stateSelect.appendChild(opt);
            });
            if (selState) stateSelect.value = selState;

            // Populate Cities
            function updateCities() {
                const s = stateSelect.value;
                citySelect.innerHTML = '<option value="">All Cities</option>';
                if (s && locationData[s]) {
                    locationData[s].forEach(c => {
                        const opt = document.createElement('option');
                        opt.value = c;
                        opt.textContent = c;
                        citySelect.appendChild(opt);
                    });
                }
            }
            
            updateCities();
            if (selCity) citySelect.value = selCity;
            
            // We only bind the change AFTER populating so it doesn't infinite loop submit
            typeSelect.addEventListener('change', function() {
                updateBanks(); 
                document.getElementById('filter-form').submit();
            });
            stateSelect.addEventListener('change', function() {
                updateCities();
                document.getElementById('filter-form').submit();
            });
            
            // Remove inline onchange from state and bank_type since we handle it in JS now
            typeSelect.removeAttribute('onchange');
            stateSelect.removeAttribute('onchange');
        });
        </script>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:40px;"></th>
                        <th>Banker Name</th>
                        <th>Bank Name</th>
                        <th>Status</th>
                        <th>Contact Number</th>
                        <th>Limits</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($paginated_bankers)): ?>
                        <tr><td colspan="7" class="text-center">No bank contacts found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($paginated_bankers as $banker): 
                            $status = $banker['status'] ?? 'Active';
                            $statusClass = $status === 'Active' ? 'badge-success' : 'badge-danger';
                        ?>
                            <tr class="expandable-row" onclick="toggleRow('details-<?php echo $banker['id']; ?>', this)">
                                <td><i data-lucide="chevron-down" class="expand-icon" style="color:#94a3b8; width:20px; height:20px;"></i></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($banker['full_name']); ?></strong>
                                    <br><span style="font-size: 12px; color: #64748b;"><?php echo htmlspecialchars($banker['designation']); ?></span>
                                </td>
                                <td>
                                    <span class="badge badge-info"><?php echo htmlspecialchars($banker['bank_name'] ?? 'N/A'); ?></span>
                                </td>
                                <td><span class="badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($status); ?></span></td>
                                <td>
                                    <div style="font-size:13px; font-weight:500;"><i data-lucide="phone" style="width:12px;height:12px;color:#64748b;"></i> <?php echo htmlspecialchars($banker['contact_number']); ?></div>
                                </td>
                                <td>
                                    <div style="font-size:13px; font-weight:600; color:#475569;">
                                        ₹<?php echo number_format((float)($banker['min_loan_limit'] ?? 0)); ?> - ₹<?php echo number_format((float)($banker['max_loan_limit'] ?? 0)); ?>
                                    </div>
                                </td>
                                <td style="text-align:right;">
                                    <div style="display:flex; gap: 6px; justify-content:flex-end;">
                                        <button class="btn btn-sm btn-secondary" title="Set Reminder" style="padding: 6px 8px; color:#f59e0b;" onclick="event.stopPropagation(); openReminderModal('Banker', <?php echo $banker['id']; ?>, '<?php echo addslashes(htmlspecialchars($banker['full_name'])); ?>')">
                                            <i data-lucide="bell"></i>
                                        </button>
                                        <a href="view_banker.php?id=<?php echo $banker['id']; ?>" class="btn btn-sm btn-secondary" title="View Profile" style="padding: 6px 8px;" onclick="event.stopPropagation();">
                                            <i data-lucide="eye"></i>
                                        </a>
                                        <?php if (($_SESSION['role'] ?? '') === 'Admin'): ?>
                                        <a href="edit_banker.php?id=<?php echo $banker['id']; ?>" class="btn btn-sm" title="Edit Contact" style="background:#f1f5f9; color:#475569; border:none; padding: 6px 8px;" onclick="event.stopPropagation();">
                                            <i data-lucide="edit"></i>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <tr id="details-<?php echo $banker['id']; ?>" class="details-row">
                                <td colspan="6" class="details-content">
                                    <div class="details-grid">
                                        <div class="detail-item">
                                            <label>Email Address</label>
                                            <div class="value"><?php echo htmlspecialchars($banker['official_email'] ?: 'N/A'); ?></div>
                                        </div>
                                        <div class="detail-item">
                                            <label>Location</label>
                                            <div class="value"><?php echo htmlspecialchars(($banker['city'] ?? 'N/A') . ', ' . ($banker['state'] ?? 'N/A')); ?></div>
                                        </div>
                                        <div class="detail-item">
                                            <label>IFSC Code</label>
                                            <div class="value" style="text-transform:uppercase; font-family:monospace;"><?php echo htmlspecialchars($banker['ifsc_code'] ?? 'N/A'); ?></div>
                                        </div>
                                        <div class="detail-item">
                                            <label>Loan Categories</label>
                                            <div style="display:flex; flex-wrap:wrap; gap:4px;">
                                                <?php 
                                                $cats = explode(',', $banker['loan_category'] ?? '');
                                                $has_cat = false;
                                                foreach($cats as $c) {
                                                    $c = trim($c);
                                                    if($c) {
                                                        echo "<span class='badge' style='background:#e2e8f0; color:#334155; font-size:11px; padding:4px 8px;'>".htmlspecialchars($c)."</span>";
                                                        $has_cat = true;
                                                    }
                                                }
                                                if(!$has_cat) echo "<span style='color:#94a3b8; font-size:13px;'>Not Specified</span>";
                                                ?>
                                            </div>
                                        </div>
                                        <div class="detail-item">
                                            <label>Coverage Type</label>
                                            <div class="value"><?php echo htmlspecialchars($banker['coverage_type'] ?: 'Not Specified'); ?></div>
                                        </div>
                                        <div class="detail-item">
                                            <label>Serviceable Area</label>
                                            <div class="value"><?php echo htmlspecialchars($banker['coverage_details'] ?: 'N/A'); ?></div>
                                        </div>
                                        <div class="detail-item" style="grid-column: 1 / -1;">
                                            <label>Branch Street Address</label>
                                            <div class="value" style="font-size:13px; color:#475569;"><?php echo nl2br(htmlspecialchars($banker['address'] ?: 'N/A')); ?></div>
                                        </div>
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

<script>
function toggleRow(detailsId, rowElement) {
    const detailsRow = document.getElementById(detailsId);
    
    // Check if currently open
    const isOpen = rowElement.classList.contains('open');
    
    // Close all other rows first (optional, for accordion behavior)
    document.querySelectorAll('.expandable-row').forEach(el => el.classList.remove('open'));
    document.querySelectorAll('.details-row').forEach(el => el.classList.remove('open'));
    
    // Toggle the clicked row
    if (!isOpen) {
        rowElement.classList.add('open');
        detailsRow.classList.add('open');
    }
}
</script>
