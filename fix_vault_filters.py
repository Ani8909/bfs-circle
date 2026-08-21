import re

file_path = r'c:\Users\pc\Downloads\client mgmt2\client_vault\index.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# Replace the AJAX handling block
ajax_start = content.find("if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {")
ajax_end = content.find("    exit;\n}") + 12

new_ajax = """if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
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
}"""

content = content[:ajax_start] + new_ajax + content[ajax_end:]

# Replace the search bar UI and add filters
search_ui_start = content.find('<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">')
search_ui_end = content.find('<div style="overflow-x:auto;">')

new_search_ui = """<div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:24px; flex-wrap:wrap; gap:16px;">
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
        """

content = content[:search_ui_start] + new_search_ui + content[search_ui_end:]

# Update JS function loadData
js_load_start = content.find('function loadData() {')
js_load_end = content.find('function debounceSearch() {')

new_js_load = """function loadData() {
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
                    let safeName = c.customer_name.replace(/'/g, "\\\\'");
                    
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

"""

content = content[:js_load_start] + new_js_load + content[js_load_end:]

# Add reset filters JS function
if "function resetFilters()" not in content:
    reset_js = """
function resetFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('filterLoanType').value = '';
    document.getElementById('filterTimeFrame').value = '';
    document.getElementById('filterSort').value = 'newest';
    currentPage = 1;
    loadData();
}
"""
    content = content.replace("function changePage(delta) {", reset_js + "function changePage(delta) {")

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Advanced Filtering & Searching added")
