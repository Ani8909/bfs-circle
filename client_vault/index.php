<?php
define('IS_SUBFOLDER', true);
require_once '../config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    die("Access Denied");
}

$current_page = 'client_vault';
$page_title = 'Client Vault & Cross-Sell Hub';
$page_subtitle = 'Secure archive of all completed customers for future cross-selling and retention.';

// Handling AJAX Request for Pagination/Search
if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $loan_type = isset($_GET['loan_type']) ? trim($_GET['loan_type']) : '';
    $time_frame = isset($_GET['time_frame']) ? trim($_GET['time_frame']) : '';
    $sort_by = isset($_GET['sort_by']) ? trim($_GET['sort_by']) : 'newest';
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $limit = 10;
    $offset = ($page - 1) * $limit;
    
    $where = "overall_status = 'Completed'";
    $params = [];
    
    // Advanced Search
    if ($search !== '') {
        $where .= " AND (customer_name LIKE ? OR mobile LIKE ? OR pan_number LIKE ? OR aadhar_number LIKE ? OR email LIKE ?)";
        $search_param = "%$search%";
        $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param, $search_param]);
    }
    
    // Loan Type Filter
    if ($loan_type !== '') {
        $where .= " AND loan_type = ?";
        $params[] = $loan_type;
    }
    
    // Time Frame Filter
    if ($time_frame !== '') {
        $now = date('Y-m-d H:i:s');
        if ($time_frame === '3_months') {
            $where .= " AND created_at >= DATE('now', '-3 months')";
        } elseif ($time_frame === '6_months') {
            $where .= " AND created_at >= DATE('now', '-6 months') AND created_at < DATE('now', '-3 months')";
        } elseif ($time_frame === 'prime') { // 6+ months
            $where .= " AND created_at < DATE('now', '-6 months')";
        }
    }
    
    // Sorting
    $order_by = "a.created_at DESC";
    if ($sort_by === 'oldest') $order_by = "a.created_at ASC";
    if ($sort_by === 'highest_amount') $order_by = "a.loan_amount_requested DESC";
    if ($sort_by === 'name_asc') $order_by = "a.customer_name ASC";
    
    // Total count
    $stmt_count = $db->prepare("SELECT COUNT(*) FROM applicants WHERE $where");
    $stmt_count->execute($params);
    $total_records = $stmt_count->fetchColumn();
    $total_pages = ceil($total_records / $limit);
    
    // Fetch data
    $sql = "SELECT a.*, (SELECT COUNT(*) FROM applicant_documents WHERE applicant_id = a.id) as doc_count 
            FROM applicants a 
            WHERE $where 
            ORDER BY $order_by LIMIT $limit OFFSET $offset";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode([
        'data' => $clients,
        'total' => $total_records,
        'page' => $page,
        'total_pages' => $total_pages
    ]);
    exit;
}
// Fetch basic summary metrics (can be cached in a real prod app)
$stmt_metrics = $db->query("SELECT COUNT(*) as total_clients, SUM(loan_amount_requested) as total_volume FROM applicants WHERE overall_status = 'Completed'");
$metrics = $stmt_metrics->fetch(PDO::FETCH_ASSOC);

require_once '../header.php';
?>

<style>
    .vault-layout { width: 100%; margin: 0 auto; padding: 24px; }
    .vault-card { background: #fff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); padding: 24px; margin-bottom: 24px; border: 1px solid #f1f5f9; }
    
    .metric-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 24px; }
    .metric-box { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #fff; padding: 24px; border-radius: 16px; display: flex; align-items: center; gap: 20px; box-shadow: 0 10px 25px rgba(15,23,42,0.15); transition: transform 0.2s; }
    .metric-box:hover { transform: translateY(-3px); }
    .metric-icon { background: rgba(255,255,255,0.1); width: 56px; height: 56px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 24px; color: #38bdf8; }
    .metric-val { font-size: 28px; font-weight: 800; margin-bottom: 4px; letter-spacing: -0.5px; }
    .metric-lbl { font-size: 13px; color: #94a3b8; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
    
    .search-bar { position: relative; width: 300px; }
    .search-bar input { width: 100%; padding: 12px 16px 12px 42px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 14px; outline: none; transition: 0.2s; box-sizing: border-box; }
    .search-bar input:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.1); }
    .search-bar svg { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #94a3b8; width: 18px; }
    
    .client-table { width: 100%; border-collapse: separate; border-spacing: 0; }
    .client-table th { background: #f8fafc; padding: 16px; text-align: left; font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0; }
    .client-table td { padding: 16px; border-bottom: 1px solid #f1f5f9; font-size: 14px; color: #1e293b; vertical-align: middle; transition: background 0.2s; }
    .client-table tbody tr:hover td { background: #f8fafc; }
    
    .badge-doc { display: inline-flex; align-items: center; padding: 6px 12px; background: #e0f2fe; color: #0284c7; border-radius: 20px; font-size: 12px; font-weight: 700; gap: 4px; }
    .badge-time { display: inline-flex; align-items: center; padding: 6px 12px; background: #fef3c7; color: #d97706; border-radius: 20px; font-size: 12px; font-weight: 700; gap: 4px; }
    .badge-time.prime { background: #dcfce7; color: #166534; }
    
    .btn-pitch { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white; border: none; padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; transition: 0.2s; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; box-shadow: 0 4px 10px rgba(59,130,246,0.2); }
    .btn-pitch:hover { transform: translateY(-1px); box-shadow: 0 6px 15px rgba(59,130,246,0.3); }
    .btn-view { background: #f1f5f9; color: #475569; border: none; padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; transition: 0.2s; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; }
    .btn-view:hover { background: #e2e8f0; color: #0f172a; }
    
    /* Pagination */
    .pagination { display: flex; align-items: center; justify-content: space-between; margin-top: 24px; padding-top: 20px; border-top: 1px solid #f1f5f9; }
    .page-btn { padding: 8px 16px; border: 1px solid #e2e8f0; background: #fff; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 13px; color: #475569; transition: 0.2s; }
    .page-btn:hover:not(:disabled) { background: #f8fafc; border-color: #cbd5e1; }
    .page-btn:disabled { opacity: 0.5; cursor: not-allowed; }
    .page-info { font-size: 13px; font-weight: 600; color: #64748b; }
    
    /* Skeleton Loader */
    .skeleton { background: #e2e8f0; border-radius: 4px; overflow: hidden; position: relative; }
    .skeleton::after { content: ""; display: block; position: absolute; top: 0; left: 0; right: 0; bottom: 0; transform: translateX(-100%); background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent); animation: shimmer 1.5s infinite; }
    @keyframes shimmer { 100% { transform: translateX(100%); } }
    .sk-text { height: 16px; margin-bottom: 8px; width: 70%; }
    .sk-text-sm { height: 12px; width: 40%; }
    .sk-badge { height: 26px; width: 90px; border-radius: 20px; }
    .sk-btn { height: 34px; width: 100px; border-radius: 8px; }
</style>

<div class="vault-layout">
    
    <!-- Top Metrics -->
    <div class="metric-grid">
        <div class="metric-box">
            <div class="metric-icon"><i data-lucide="shield"></i></div>
            <div>
                <div class="metric-val"><?= number_format($metrics['total_clients'] ?? 0) ?></div>
                <div class="metric-lbl">Total Vault Clients</div>
            </div>
        </div>
        <div class="metric-box">
            <div class="metric-icon" style="color: #34d399; background: rgba(52,211,153,0.1);"><i data-lucide="landmark"></i></div>
            <div>
                <div class="metric-val">&#8377;<?= number_format($metrics['total_volume'] ?? 0, 0) ?></div>
                <div class="metric-lbl">Total Disbursed Volume</div>
            </div>
        </div>
        <div class="metric-box" style="background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);">
            <div class="metric-icon" style="color: #fff; background: rgba(255,255,255,0.2);"><i data-lucide="trending-up"></i></div>
            <div>
                <div class="metric-val">High</div>
                <div class="metric-lbl">Cross-Sell Potential</div>
            </div>
        </div>
    </div>
    
    <!-- Data Table Card -->
    <div class="vault-card">
        <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:24px; flex-wrap:wrap; gap:16px;">
            <div>
                <h3 style="margin:0; font-size:20px; font-weight:800; color:#0f172a; display:flex; align-items:center; gap:8px;">
                    <i data-lucide="database" style="color:#3b82f6;"></i> Scalable Vault Data
                </h3>
                <p style="margin:4px 0 0 0; font-size:13px; color:#64748b;">Advanced search & filtering. Zero hang on massive datasets.</p>
            </div>
            
            <div style="display:flex; gap:12px; flex-wrap:wrap; align-items:center;">
                
                <!-- Filters -->
                <div style="display:flex; gap:8px;">
                    <select id="filterLoanType" onchange="debounceSearch()" style="padding:10px 14px; border:2px solid #e2e8f0; border-radius:10px; font-size:13px; font-weight:600; color:#475569; outline:none; background:#fff; cursor:pointer;">
                        <option value="">All Loan Types</option>
                        <option value="Home Loan">Home Loan</option>
                        <option value="Personal Loan">Personal Loan</option>
                        <option value="Business Loan">Business Loan</option>
                        <option value="Mortgage Loan">Mortgage/LAP</option>
                        <option value="Auto Loan">Auto/Car Loan</option>
                        <option value="Credit Card">Credit Card</option>
                    </select>
                    
                    <select id="filterTimeFrame" onchange="debounceSearch()" style="padding:10px 14px; border:2px solid #e2e8f0; border-radius:10px; font-size:13px; font-weight:600; color:#475569; outline:none; background:#fff; cursor:pointer;">
                        <option value="">All Time</option>
                        <option value="3_months">Recent (< 3 Months)</option>
                        <option value="6_months">Mid (3-6 Months)</option>
                        <option value="prime">Prime for Cross-Sell (6+ Months)</option>
                    </select>
                    
                    <select id="filterSort" onchange="debounceSearch()" style="padding:10px 14px; border:2px solid #e2e8f0; border-radius:10px; font-size:13px; font-weight:600; color:#475569; outline:none; background:#fff; cursor:pointer;">
                        <option value="newest">Sort: Newest First</option>
                        <option value="oldest">Sort: Oldest First</option>
                        <option value="highest_amount">Sort: Highest Amount</option>
                        <option value="name_asc">Sort: Name (A-Z)</option>
                    </select>
                </div>
                
                <div class="search-bar" style="width: 250px;">
                    <i data-lucide="search"></i>
                    <input type="text" id="searchInput" placeholder="Search Name, Phone, PAN, Email..." oninput="debounceSearch()">
                </div>
                
                <button onclick="resetFilters()" style="padding:10px 14px; background:#f1f5f9; color:#475569; border:none; border-radius:10px; font-weight:600; font-size:13px; cursor:pointer; display:flex; align-items:center; gap:6px;">
                    <i data-lucide="refresh-cw" style="width:14px;"></i> Reset
                </button>
            </div>
        </div>
        <div style="overflow-x:auto;">
            <table class="client-table">
                <thead>
                    <tr>
                        <th>Customer Details</th>
                        <th>Loan Type & Amount</th>
                        <th>Verified Docs</th>
                        <th>Time Since Loan</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <!-- Data populated by AJAX -->
                </tbody>
            </table>
        </div>
        
        <div class="pagination">
            <button id="prevBtn" class="page-btn" onclick="changePage(-1)"><i data-lucide="chevron-left" style="width:16px; vertical-align:middle;"></i> Previous</button>
            <div id="pageInfo" class="page-info">Loading...</div>
            <button id="nextBtn" class="page-btn" onclick="changePage(1)">Next <i data-lucide="chevron-right" style="width:16px; vertical-align:middle;"></i></button>
        </div>
    </div>
</div>

<!-- Pitch Product Modal -->
<div id="pitchModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15, 23, 42, 0.7); z-index:9999; align-items:center; justify-content:center; backdrop-filter:blur(4px);">
    <div style="background:#fff; width:100%; max-width:500px; border-radius:16px; padding:32px; box-shadow:0 20px 40px rgba(0,0,0,0.3);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
            <h3 style="margin:0; font-size:20px; color:#0f172a; display:flex; align-items:center; gap:10px;"><i data-lucide="zap" style="color:#3b82f6;"></i> New Product Pitch</h3>
            <button onclick="document.getElementById('pitchModal').style.display='none'" style="background:none; border:none; cursor:pointer; color:#94a3b8;"><i data-lucide="x"></i></button>
        </div>
        
        <form method="POST" action="process_pitch.php">
            <input type="hidden" name="applicant_id" id="pitch_applicant_id">
            
            <div style="margin-bottom:20px;">
                <label style="display:block; font-size:13px; font-weight:700; color:#475569; margin-bottom:8px;">Selected Customer</label>
                <div style="background:#f8fafc; padding:12px 16px; border-radius:8px; border:1px solid #e2e8f0; font-weight:600; color:#1e293b; font-size:15px;" id="pitch_customer_display"></div>
            </div>
            
            <div style="margin-bottom:20px;">
                <label style="display:block; font-size:13px; font-weight:700; color:#475569; margin-bottom:8px;">New Product / Service</label>
                <select name="product_type" required style="width:100%; padding:12px; border:2px solid #e2e8f0; border-radius:8px; font-size:15px; outline:none;">
                    <option value="">Select a product...</option>
                    <option value="Top-up Loan">Top-up Loan</option>
                    <option value="Personal Loan">Personal Loan</option>
                    <option value="Health Insurance">Health Insurance</option>
                    <option value="Credit Card">Credit Card</option>
                </select>
            </div>
            
            <div style="margin-bottom:28px;">
                <label style="display:block; font-size:13px; font-weight:700; color:#475569; margin-bottom:8px;">Pitch Details</label>
                <textarea name="pitch_notes" rows="4" style="width:100%; padding:12px; border:2px solid #e2e8f0; border-radius:8px; font-size:14px; outline:none; resize:vertical;"></textarea>
            </div>
            
            <div style="display:flex; justify-content:flex-end; gap:16px;">
                <button type="button" onclick="document.getElementById('pitchModal').style.display='none'" style="padding:12px 20px; background:#f1f5f9; color:#475569; border:none; border-radius:8px; font-weight:700; cursor:pointer;">Cancel</button>
                <button type="submit" style="padding:12px 24px; background:#3b82f6; color:#fff; border:none; border-radius:8px; font-weight:700; cursor:pointer;">Launch Pitch</button>
            </div>
        </form>
    </div>
</div>

<script>
let currentPage = 1;
let currentSearch = '';
let searchTimeout = null;

function renderSkeleton() {
    const tbody = document.getElementById('tableBody');
    let html = '';
    for(let i=0; i<5; i++) {
        html += `
        <tr>
            <td><div class="skeleton sk-text"></div><div class="skeleton sk-text-sm"></div></td>
            <td><div class="skeleton sk-text"></div><div class="skeleton sk-text-sm"></div></td>
            <td><div class="skeleton sk-badge"></div></td>
            <td><div class="skeleton sk-badge"></div></td>
            <td style="text-align:right;"><div style="display:flex; justify-content:flex-end; gap:8px;"><div class="skeleton sk-btn"></div><div class="skeleton sk-btn"></div></div></td>
        </tr>`;
    }
    tbody.innerHTML = html;
}

function calculateMonths(dateStr) {
    const d = new Date(dateStr);
    const now = new Date();
    let months = (now.getFullYear() - d.getFullYear()) * 12;
    months -= d.getMonth();
    months += now.getMonth();
    return months <= 0 ? 0 : months;
}

function loadData() {
    renderSkeleton();
    
    const s = encodeURIComponent(document.getElementById('searchInput').value);
    const lt = encodeURIComponent(document.getElementById('filterLoanType').value);
    const tf = encodeURIComponent(document.getElementById('filterTimeFrame').value);
    const sb = encodeURIComponent(document.getElementById('filterSort').value);
    
    const url = `index.php?ajax=1&page=${currentPage}&search=${s}&loan_type=${lt}&time_frame=${tf}&sort_by=${sb}`;
    
    fetch(url)
        .then(res => res.json())
        .then(data => {
            const tbody = document.getElementById('tableBody');
            let html = '';
            
            if(data.data.length === 0) {
                html = `<tr><td colspan="5" style="text-align:center; padding:40px; color:#94a3b8; font-size:14px;"><i data-lucide="folder-search" style="width:40px; height:40px; margin-bottom:12px; color:#cbd5e1; display:block; margin:0 auto;"></i>No clients found matching your filters.</td></tr>`;
            } else {
                data.data.forEach(c => {
                    const months = calculateMonths(c.created_at);
                    let timeBadge = '';
                    if(months >= 6) {
                        timeBadge = `<span class="badge-time prime"><i data-lucide="check-circle" style="width:14px;"></i> 6+ Months (Prime)</span>`;
                    } else if(months > 0) {
                        timeBadge = `<span class="badge-time"><i data-lucide="clock" style="width:14px;"></i> ${months} Months Ago</span>`;
                    } else {
                        timeBadge = `<span class="badge-time" style="background:#e2e8f0; color:#475569;"><i data-lucide="history" style="width:14px;"></i> Recent</span>`;
                    }
                    
                    let amount = new Intl.NumberFormat('en-IN').format(c.loan_amount_requested);
                    let safeName = c.customer_name.replace(/'/g, "\\'");
                    
                    html += `
                    <tr>
                        <td>
                            <div style="font-weight:700; color:#1e293b; font-size:15px; margin-bottom:4px;">${c.customer_name}</div>
                            <div style="font-size:13px; color:#64748b; display:flex; align-items:center; gap:8px;">
                                <span><i data-lucide="phone" style="width:12px; vertical-align:middle;"></i> ${c.mobile}</span>
                                <span style="color:#cbd5e1;">|</span>
                                <span>PAN: <strong style="color:#475569;">${c.pan_number || 'N/A'}</strong></span>
                            </div>
                        </td>
                        <td>
                            <div style="font-weight:700; color:#3b82f6;">${c.loan_type}</div>
                            <div style="font-size:13px; color:#64748b;">&#8377;${amount}</div>
                        </td>
                        <td>
                            <span class="badge-doc"><i data-lucide="folder-check" style="width:14px;"></i> ${c.doc_count} Docs Saved</span>
                        </td>
                        <td>${timeBadge}</td>
                        <td style="text-align:right;">
                            <a href="view_client.php?id=${c.id}" class="btn-view"><i data-lucide="eye" style="width:14px;"></i> View Vault</a>
                            <button class="btn-pitch" onclick="openPitchModal(${c.id}, '${safeName}')"><i data-lucide="phone-call" style="width:14px;"></i> Pitch Product</button>
                        </td>
                    </tr>`;
                });
            }
            
            tbody.innerHTML = html;
            lucide.createIcons();
            
            document.getElementById('pageInfo').innerText = `Page ${data.page} of ${data.total_pages || 1} (${data.total} total clients)`;
            document.getElementById('prevBtn').disabled = data.page <= 1;
            document.getElementById('nextBtn').disabled = data.page >= data.total_pages;
        })
        .catch(err => {
            console.error(err);
            document.getElementById('tableBody').innerHTML = `<tr><td colspan="5" style="text-align:center; color:red;">Error loading data</td></tr>`;
        });
}

function debounceSearch() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        currentSearch = document.getElementById('searchInput').value;
        currentPage = 1; // Reset to page 1 on search
        loadData();
    }, 500);
}


function resetFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('filterLoanType').value = '';
    document.getElementById('filterTimeFrame').value = '';
    document.getElementById('filterSort').value = 'newest';
    currentPage = 1;
    loadData();
}
function changePage(delta) {
    currentPage += delta;
    loadData();
}

function openPitchModal(id, name) {
    document.getElementById('pitch_applicant_id').value = id;
    document.getElementById('pitch_customer_display').innerText = name;
    document.getElementById('pitchModal').style.display = 'flex';
}

// Initial Load
document.addEventListener("DOMContentLoaded", () => {
    lucide.createIcons();
    loadData();
});
</script>

<?php require_once '../footer.php'; ?>
