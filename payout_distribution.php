<?php
require_once 'config.php';

// Ensure table exists
$db->exec("
CREATE TABLE IF NOT EXISTS payout_distributions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    applicant_id INTEGER NOT NULL,
    payee_type TEXT NOT NULL, 
    payee_user_id INTEGER,
    total_loan_amount REAL DEFAULT 0,
    commission_percentage REAL DEFAULT 0,
    gross_payout REAL DEFAULT 0,
    tds_deducted REAL DEFAULT 0,
    net_payable REAL DEFAULT 0,
    status TEXT DEFAULT 'Pending', 
    cancellation_reason TEXT,
    transaction_ref TEXT,
    paid_on DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
");

// Only Admin can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit;
}

$page_title = 'Payout Distribution';
$page_subtitle = 'Manage and disburse commissions to Partners and Staff';
require_once 'header.php';

// Handle Actions (AJAX-like via POST in same file for simplicity, or just simple post redirects)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $payout_id = $_POST['payout_id'] ?? 0;
    
    if ($_POST['action'] === 'update_status') {
        $status = $_POST['status'];
        $cancellation_reason = $_POST['cancellation_reason'] ?? '';
        $transaction_ref = $_POST['transaction_ref'] ?? '';
        $paid_on = ($status === 'Paid') ? date('Y-m-d H:i:s') : null;

        $stmt = $db->prepare("UPDATE payout_distributions SET status = ?, cancellation_reason = ?, transaction_ref = ?, paid_on = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$status, $cancellation_reason, $transaction_ref, $paid_on, $payout_id]);
        
        // Refresh page
        echo "<script>window.location.href='payout_distribution.php';</script>";
        exit;
    } elseif ($_POST['action'] === 'add_payout') {
        $applicant_id = (int)$_POST['applicant_id'];
        $payee_user_id = (int)$_POST['payee_user_id'];
        $gross_payout = (float)$_POST['gross_payout'];
        $tds_deducted = (float)$_POST['tds_deducted'];
        $net_payable = $gross_payout - $tds_deducted;
        
        // get payee type
        $stmtU = $db->prepare("SELECT role FROM users WHERE id = ?");
        $stmtU->execute([$payee_user_id]);
        $uRole = $stmtU->fetchColumn();
        $payee_type = in_array($uRole, ['Builder', 'CA', 'Partner']) ? 'Partner' : 'Employee';

        $stmt = $db->prepare("INSERT INTO payout_distributions (applicant_id, payee_type, payee_user_id, gross_payout, tds_deducted, net_payable) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$applicant_id, $payee_type, $payee_user_id, $gross_payout, $tds_deducted, $net_payable]);
        
        echo "<script>window.location.href='payout_distribution.php';</script>";
        exit;
    }
}

// Metrics
$metrics = $db->query("SELECT 
    SUM(CASE WHEN status='Pending' THEN net_payable ELSE 0 END) as total_pending,
    SUM(CASE WHEN status='Paid' THEN net_payable ELSE 0 END) as total_paid,
    SUM(CASE WHEN status='Cancelled' THEN net_payable ELSE 0 END) as total_cancelled
    FROM payout_distributions")->fetch(PDO::FETCH_ASSOC);

// Fetch dropdown data for Add Payout Modal
$eligible_applicants = $db->query("SELECT id, customer_name, loan_id, loan_amount_requested FROM applicants WHERE overall_status IN ('Phase 4', 'Completed') ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
$eligible_payees = $db->query("SELECT id, name, role FROM users WHERE role IN ('Partner', 'Builder', 'CA', 'Agent', 'Staff', 'Admin') ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="view-container">
    
    <!-- Metrics -->
    <div class="stats-grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 24px;">
        <div class="stat-card" style="border:1px solid #e2e8f0; border-radius:12px; background:#fff;">
            <div class="stat-card-header"><span class="stat-label">Total Pending</span></div>
            <div class="stat-value" style="color:#f59e0b;">₹<?= number_format($metrics['total_pending'] ?: 0) ?></div>
        </div>
        <div class="stat-card" style="border:1px solid #e2e8f0; border-radius:12px; background:#fff;">
            <div class="stat-card-header"><span class="stat-label">Total Paid</span></div>
            <div class="stat-value" style="color:#10b981;">₹<?= number_format($metrics['total_paid'] ?: 0) ?></div>
        </div>
        <div class="stat-card" style="border:1px solid #e2e8f0; border-radius:12px; background:#fff;">
            <div class="stat-card-header"><span class="stat-label">Total Cancelled</span></div>
            <div class="stat-value" style="color:#ef4444;">₹<?= number_format($metrics['total_cancelled'] ?: 0) ?></div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card" style="margin-bottom: 24px; padding: 16px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
            <h2 style="margin:0; font-size:18px;">Manage Payouts</h2>
            <button class="btn btn-primary" onclick="document.getElementById('addPayoutModal').style.display='flex'">+ Add Payout</button>
        </div>
        
        <form onsubmit="event.preventDefault(); loadPayouts(1);" style="display: flex; gap: 12px; align-items: center; flex-wrap: nowrap;">
            <div style="flex: 1;">
                <input type="text" id="filter_search" placeholder="Search by Lead Name, Loan ID or Payee Name..." style="width:100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; font-size:13px;">
            </div>
            <select id="filter_status" style="padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; font-size:13px; width:150px;">
                <option value="">All Statuses</option>
                <option value="Pending">Pending</option>
                <option value="Hold">Hold</option>
                <option value="Approved">Approved</option>
                <option value="Paid">Paid</option>
                <option value="Cancelled">Cancelled</option>
            </select>
            <select id="filter_payee" style="padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; font-size:13px; width:150px;">
                <option value="">All Payee Types</option>
                <option value="Partner">Partner (CA/Builder)</option>
                <option value="Employee">Employee (Staff)</option>
            </select>
            <button type="submit" class="btn btn-primary" style="padding: 10px 20px;">Search</button>
            <button type="button" class="btn btn-secondary" onclick="resetFilters()" style="padding: 10px 20px;">Reset</button>
        </form>
    </div>

    <!-- Data Table -->
    <div class="card" style="overflow-x: auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Applicant / Loan</th>
                    <th>Payee</th>
                    <th>Type</th>
                    <th>Gross</th>
                    <th>TDS</th>
                    <th>Net Payable</th>
                    <th>Timeline</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="payout-tbody">
                <!-- Data loaded via JS -->
            </tbody>
        </table>
        
        <div id="pagination-controls" style="display:flex; justify-content:space-between; align-items:center; margin-top:20px; padding:10px 0; border-top:1px solid #e2e8f0;">
            <div id="pagination-info" style="font-size:13px; color:#64748b; font-weight:500;"></div>
            <div id="pagination-buttons" style="display:flex; gap:8px;"></div>
        </div>
    </div>

</div>

<!-- Action Modal -->
<div id="actionModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; justify-content:center; align-items:center;">
    <div style="background:#fff; padding:24px; border-radius:12px; width:400px; max-width:90%;">
        <h3 style="margin-top:0; margin-bottom: 20px;">Update Payout Status</h3>
        <form method="POST" action="payout_distribution.php">
            <input type="hidden" name="action" value="update_status">
            <input type="hidden" name="payout_id" id="modal_payout_id">
            
            <div style="margin-bottom: 16px;">
                <label style="display:block; margin-bottom:8px; font-weight:600;">Status</label>
                <select name="status" id="modal_status" onchange="toggleModalFields()" style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:8px;" required>
                    <option value="Pending">Pending</option>
                    <option value="Hold">Hold</option>
                    <option value="Approved">Approved</option>
                    <option value="Paid">Paid</option>
                    <option value="Cancelled">Cancelled</option>
                </select>
            </div>
            
            <div id="div_transaction" style="display:none; margin-bottom: 16px;">
                <label style="display:block; margin-bottom:8px; font-weight:600;">Transaction Ref / UTR No.</label>
                <input type="text" name="transaction_ref" id="modal_transaction" style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:8px;">
            </div>

            <div id="div_cancel_reason" style="display:none; margin-bottom: 16px;">
                <label style="display:block; margin-bottom:8px; font-weight:600;">Cancellation Reason <span style="color:red">*</span></label>
                <textarea name="cancellation_reason" id="modal_cancel_reason" style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:8px; resize:none;" rows="3"></textarea>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" class="btn btn-secondary" onclick="closeActionModal()">Close</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Payout Modal -->
<div id="addPayoutModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; justify-content:center; align-items:center;">
    <div style="background:#fff; padding:24px; border-radius:12px; width:450px; max-width:90%;">
        <h3 style="margin-top:0; margin-bottom: 20px;">Generate New Payout</h3>
        <form method="POST" action="payout_distribution.php">
            <input type="hidden" name="action" value="add_payout">
            
            <div style="margin-bottom: 16px;">
                <label style="display:block; margin-bottom:8px; font-weight:600;">Select Lead / Applicant</label>
                <select name="applicant_id" style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:8px;" required>
                    <option value="">-- Choose Applicant --</option>
                    <?php foreach ($eligible_applicants as $ea): ?>
                        <option value="<?= $ea['id'] ?>"><?= htmlspecialchars($ea['customer_name'] . ' (Loan: ' . $ea['loan_amount_requested'] . ')') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div style="margin-bottom: 16px;">
                <label style="display:block; margin-bottom:8px; font-weight:600;">Select Payee (Partner/Staff)</label>
                <select name="payee_user_id" style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:8px;" required>
                    <option value="">-- Choose Payee --</option>
                    <?php foreach ($eligible_payees as $ep): ?>
                        <option value="<?= $ep['id'] ?>"><?= htmlspecialchars($ep['name'] . ' [' . $ep['role'] . ']') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px; margin-bottom: 16px;">
                <div>
                    <label style="display:block; margin-bottom:8px; font-weight:600;">Gross Payout (₹)</label>
                    <input type="number" step="0.01" name="gross_payout" id="new_gross" style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:8px;" oninput="calcNet()" required>
                </div>
                <div>
                    <label style="display:block; margin-bottom:8px; font-weight:600;">TDS Deduction (₹)</label>
                    <input type="number" step="0.01" name="tds_deducted" id="new_tds" value="0" style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:8px;" oninput="calcNet()" required>
                </div>
            </div>
            
            <div style="margin-bottom: 24px; padding:12px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px;">
                <span style="font-weight:600; color:#64748b; font-size:13px;">Net Payable</span>
                <div id="new_net" style="font-size:20px; font-weight:800; color:#10b981;">₹0.00</div>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('addPayoutModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-primary">Generate Payout</button>
            </div>
        </form>
    </div>
</div>

<script>
function calcNet() {
    let g = parseFloat(document.getElementById('new_gross').value) || 0;
    let t = parseFloat(document.getElementById('new_tds').value) || 0;
    document.getElementById('new_net').innerText = '₹' + (g - t).toFixed(2);
}

function openActionModal(payout) {
    document.getElementById('modal_payout_id').value = payout.id;
    document.getElementById('modal_status').value = payout.status;
    document.getElementById('modal_transaction').value = payout.transaction_ref || '';
    document.getElementById('modal_cancel_reason').value = payout.cancellation_reason || '';
    
    toggleModalFields();
    
    document.getElementById('actionModal').style.display = 'flex';
}

function closeActionModal() {
    document.getElementById('actionModal').style.display = 'none';
}

function toggleModalFields() {
    const status = document.getElementById('modal_status').value;
    const divTrans = document.getElementById('div_transaction');
    const divCancel = document.getElementById('div_cancel_reason');
    const cancelInput = document.getElementById('modal_cancel_reason');
    
    divTrans.style.display = 'none';
    divCancel.style.display = 'none';
    cancelInput.removeAttribute('required');

    if (status === 'Paid') {
        divTrans.style.display = 'block';
    } else if (status === 'Cancelled') {
        divCancel.style.display = 'block';
        cancelInput.setAttribute('required', 'true');
    }
}

function resetFilters() {
    document.getElementById('filter_search').value = '';
    document.getElementById('filter_status').value = '';
    document.getElementById('filter_payee').value = '';
    loadPayouts(1);
}

function formatCurrency(amt) {
    return '₹' + Number(amt).toLocaleString('en-IN', {minimumFractionDigits: 2});
}

async function loadPayouts(page = 1) {
    const tbody = document.getElementById('payout-tbody');
    
    // Skeleton loader
    let skelHtml = '';
    for(let i=0; i<5; i++) {
        skelHtml += `<tr><td colspan="10"><div class="skeleton" style="height:30px; width:100%;"></div></td></tr>`;
    }
    tbody.innerHTML = skelHtml;

    const search = document.getElementById('filter_search').value;
    const status = document.getElementById('filter_status').value;
    const payee = document.getElementById('filter_payee').value;
    
    try {
        const res = await fetch(`config.php?api=get_payouts&page=${page}&limit=10&search=${encodeURIComponent(search)}&status=${encodeURIComponent(status)}&payee_type=${encodeURIComponent(payee)}`);
        const data = await res.json();
        
        if (data.payouts && data.payouts.length > 0) {
            let html = '';
            data.payouts.forEach(p => {
                let badge = 'badge-warning';
                if(p.status.toLowerCase() === 'paid') badge = 'badge-success';
                else if(p.status.toLowerCase() === 'cancelled') badge = 'badge-danger';
                
                // Timeline Calculation
                let issueDate = new Date(p.created_at).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
                let timelineHtml = `<div style="font-size:12px;"><b>Issued:</b> ${issueDate}</div>`;
                
                if (p.paid_on) {
                    let d1 = new Date(p.created_at);
                    let d2 = new Date(p.paid_on);
                    let diffTime = Math.abs(d2 - d1);
                    let diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                    let dayText = diffDays === 1 ? '1 day' : (diffDays === 0 ? 'Same day' : `${diffDays} days`);
                    timelineHtml += `<div style="font-size:11px; color:#10b981; margin-top:2px;"><i data-lucide="clock" style="width:12px; vertical-align:middle;"></i> Paid in ${dayText}</div>`;
                } else if (p.status.toLowerCase() === 'cancelled') {
                    timelineHtml += `<div style="font-size:11px; color:#ef4444; margin-top:2px;">Cancelled</div>`;
                } else {
                    timelineHtml += `<div style="font-size:11px; color:#f59e0b; margin-top:2px;">Pending Disbursement</div>`;
                }
                
                html += `<tr>
                    <td>#${p.id}</td>
                    <td>
                        <div style="font-weight: 600;">${p.customer_name || 'Unknown'}</div>
                        <div style="font-size: 11px; color: #64748b;">${p.loan_id || ''}</div>
                    </td>
                    <td>${p.payee_name || 'N/A'}</td>
                    <td>${p.payee_type}</td>
                    <td>${formatCurrency(p.gross_payout)}</td>
                    <td style="color: #ef4444;">-${formatCurrency(p.tds_deducted)}</td>
                    <td style="font-weight: bold; color: #10b981;">${formatCurrency(p.net_payable)}</td>
                    <td>${timelineHtml}</td>
                    <td><span class="badge ${badge}">${p.status}</span></td>
                    <td>
                        <button class="btn btn-secondary" style="padding: 5px 10px; font-size: 12px;" onclick='openActionModal(${JSON.stringify(p).replace(/'/g, "&apos;")})'>Action</button>
                    </td>
                </tr>`;
            });
            tbody.innerHTML = html;
            if(window.lucide) lucide.createIcons();
        } else {
            tbody.innerHTML = `<tr><td colspan="10" style="text-align: center; padding: 20px; color: #64748b;">No payouts found.</td></tr>`;
        }
        
        // Render pagination controls
        document.getElementById('pagination-info').innerText = `Showing page ${data.page} of ${data.total_pages} (${data.total} total records)`;
        
        let btnHtml = '';
        if (data.page > 1) {
            btnHtml += `<button class="btn btn-secondary" style="padding:4px 10px; font-size:12px;" onclick="loadPayouts(${data.page - 1})">Prev</button>`;
        }
        if (data.page < data.total_pages) {
            btnHtml += `<button class="btn btn-secondary" style="padding:4px 10px; font-size:12px;" onclick="loadPayouts(${data.page + 1})">Next</button>`;
        }
        document.getElementById('pagination-buttons').innerHTML = btnHtml;
        
    } catch(err) {
        console.error(err);
        tbody.innerHTML = `<tr><td colspan="9" style="text-align: center; padding: 20px; color: red;">Failed to load data.</td></tr>`;
    }
}

// Initial load
document.addEventListener('DOMContentLoaded', () => loadPayouts(1));

</script>

<?php require_once 'footer.php'; ?>
