<?php
require_once 'config.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$current_page = 'pre_leads.php';
$page_title = 'Pre-Leads (Raw Data)';
$page_subtitle = 'Manage raw data and unverified prospects (10x Smart Hub)';
require_once 'header.php';

// Check if Admin
$is_admin = ($_SESSION['role'] ?? '') === 'Admin';
?>
<style>
/* Theme-Matched Ultra Smart UI */
.pl-layout { width: 100%; margin: 0 auto; padding: 24px; font-family: 'Inter', sans-serif; }

/* Dashboard Cards for KPI */
.kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 24px; }
.kpi-card { background: #fff; border-radius: 12px; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.02); border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 16px; transition: 0.2s; }
.kpi-card:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
.kpi-icon { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px; }
.kpi-val { font-size: 24px; font-weight: 800; color: #0f172a; margin-bottom: 4px; }
.kpi-lbl { font-size: 12px; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }

/* Smart Pill Tabs */
.smart-tabs { display: flex; gap: 12px; background: #e2e8f0; padding: 6px; border-radius: 12px; display: inline-flex; }
.smart-tab { padding: 10px 20px; font-weight: 700; color: #475569; cursor: pointer; border-radius: 8px; transition: 0.3s; font-size: 14px; display: flex; align-items: center; gap: 8px; }
.smart-tab:hover { color: #0f172a; background: rgba(255,255,255,0.5); }
.smart-tab.active { color: #fff; background: #0f172a; box-shadow: 0 4px 10px rgba(15,23,42,0.2); }

.filter-bar { background: #fff; padding: 16px 20px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.02); display: flex; gap: 16px; margin-bottom: 24px; border: 1px solid #e2e8f0; align-items: center; justify-content: space-between; flex-wrap: wrap; }
.filter-group { display: flex; gap: 12px; align-items: center; flex-wrap: wrap; }
.filter-input { padding: 10px 16px; display: inline-block; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 13px; font-weight: 600; color: #1e293b; outline: none; transition: 0.2s; min-width: 160px; background: #f8fafc; }
.filter-input:focus { border-color: #3b82f6; background: #fff; }

.pl-table { width: 100%; border-collapse: separate; border-spacing: 0; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.02); border: 1px solid #e2e8f0; }
.pl-table th { background: #0f172a; padding: 16px; text-align: left; font-size: 12px; font-weight: 700; color: #f8fafc; text-transform: uppercase; letter-spacing: 0.5px; }
.pl-table td { padding: 16px; font-size: 13px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; transition: background 0.2s; }
.pl-table tr:hover td { background: #f8fafc; }

.quick-action-btn { width: 28px; height: 28px; border-radius: 6px; border: none; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: 0.2s; margin-right: 4px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
.btn-whatsapp { background: #22c55e; color: #fff; } .btn-whatsapp:hover { background: #16a34a; transform: translateY(-2px); box-shadow: 0 4px 10px rgba(34,197,94,0.3); }
.btn-call { background: #3b82f6; color: #fff; } .btn-call:hover { background: #2563eb; transform: translateY(-2px); box-shadow: 0 4px 10px rgba(59,130,246,0.3); }
.btn-script { background: #8b5cf6; color: #fff; } .btn-script:hover { background: #7c3aed; transform: translateY(-2px); box-shadow: 0 4px 10px rgba(139,92,246,0.3); }

.score-badge { display: inline-flex; align-items: center; gap: 4px; padding: 6px 10px; border-radius: 20px; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; }
.score-hot { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }
.score-warm { background: #fef3c7; color: #d97706; border: 1px solid #fde68a; }
.score-cold { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }

.top-actions { display: flex; gap: 12px; }
.btn-primary { background: #0f172a; color: #fff; border: none; padding: 12px 20px; border-radius: 8px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 8px; font-size: 13px; transition: 0.2s; }
.btn-primary:hover { background: #1e293b; box-shadow: 0 4px 12px rgba(15,23,42,0.2); }
.btn-secondary { background: #fff; color: #0f172a; border: 2px solid #e2e8f0; padding: 10px 18px; border-radius: 8px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 8px; font-size: 13px; transition: 0.2s; }
.btn-secondary:hover { border-color: #cbd5e1; background: #f8fafc; }

/* Modals */
.modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15,23,42,0.8); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(4px); }
.modal-content { background: #fff; width: 100%; max-width: 600px; border-radius: 16px; box-shadow: 0 25px 50px rgba(0,0,0,0.25); overflow: hidden; animation: slideUp 0.3s ease-out; }
@keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
.modal-header { padding: 24px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #f8fafc; }
.modal-header h3 { margin: 0; font-size: 18px; color: #0f172a; display: flex; align-items: center; gap: 10px; font-weight: 800; }
.modal-body { padding: 24px; max-height: 65vh; overflow-y: auto; }
.modal-footer { padding: 20px 24px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 12px; }

/* Pagination */
.pagination { display: flex; justify-content: space-between; align-items: center; padding: 16px 20px; background: #fff; border-radius: 12px; margin-top: 20px; border: 1px solid #e2e8f0; box-shadow: 0 4px 15px rgba(0,0,0,0.02); }

/* Custom Format Sections */
.format-box { border: 2px dashed #cbd5e1; border-radius: 12px; padding: 24px; text-align: center; cursor: pointer; transition: 0.2s; background: #f8fafc; margin-bottom: 16px; }
.format-box:hover { border-color: #3b82f6; background: #eff6ff; }

@media print {
    @page { margin: 0.5cm; }
    /* Hide all non-essential UI */
    .sidebar, .header, header, .smart-tabs, .filter-bar, .top-actions, .kpi-grid, .pagination, .modal-overlay, .page-header { display: none !important; }
    
    /* Fix the massive left margin left by the sidebar */
    body, html, .main-content, #main-content, .content, .pl-layout, .main-wrapper, #wrapper { 
        margin: 0 !important; 
        padding: 0 !important; 
        width: 100% !important; 
        max-width: 100% !important; 
        background: #fff !important; 
        left: 0 !important;
    }
    
    /* Hide Quick Actions column completely */
    .pl-table th:last-child, .pl-table td:last-child { display: none !important; }
    
    /* Ultra-compact table to save paper */
    .pl-table { border: 1px solid #000 !important; width: 100% !important; border-collapse: collapse !important; margin: 0 !important; }
    .pl-table th { background: #eee !important; color: #000 !important; border-bottom: 2px solid #000 !important; padding: 4px 6px !important; font-size: 10px !important; }
    .pl-table td { border-bottom: 1px solid #ccc !important; padding: 4px 6px !important; font-size: 9px !important; line-height: 1.2 !important; }
    
    /* Make fonts darker and badges cleaner for B&W printers */
    .score-badge { border: none !important; color: #000 !important; font-weight: bold !important; padding: 0 !important; }
    * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; color: #000 !important; }
}
</style>

<div class="pl-layout">
    <!-- KPI Dashboard -->
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-icon" style="background:#e0f2fe; color:#0284c7;"><i data-lucide="database"></i></div>
            <div>
                <div class="kpi-val" id="kpi-total">--</div>
                <div class="kpi-lbl">Total Raw Leads</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon" style="background:#fef3c7; color:#d97706;"><i data-lucide="clock"></i></div>
            <div>
                <div class="kpi-val" id="kpi-followup">--</div>
                <div class="kpi-lbl">Pending Follow-ups</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon" style="background:#dcfce7; color:#16a34a;"><i data-lucide="check-circle"></i></div>
            <div>
                <div class="kpi-val" id="kpi-converted">--</div>
                <div class="kpi-lbl">Converted (Success)</div>
            </div>
        </div>
    </div>

    
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
        <div class="smart-tabs">
            <div class="smart-tab active" onclick="switchTab('new')" id="tab-new"><i data-lucide="inbox" style="width:16px; vertical-align:middle; margin-right:4px;"></i> New Raw Pool</div>
            <div class="smart-tab" onclick="switchTab('followup')" id="tab-followup"><i data-lucide="clock" style="width:16px; vertical-align:middle; margin-right:4px;"></i> Follow-up Queue</div>
            <div class="smart-tab" onclick="switchTab('archived')" id="tab-archived"><i data-lucide="archive" style="width:16px; vertical-align:middle; margin-right:4px;"></i> Archived / Junk</div>
        </div>
        
        <div class="top-actions">
            <button class="btn-secondary" onclick="window.print()" title="Print Current Data"><i data-lucide="printer"></i> Print</button>
            <?php if($is_admin): ?>
            <button class="btn-secondary" onclick="document.getElementById('bulkModal').style.display='flex'">
                <i data-lucide="users"></i> Auto-Distribute
            </button>
            <button class="btn-secondary" onclick="document.getElementById('advancedBulkModal').style.display='flex'">
                <i data-lucide="upload"></i> Bulk Import
            </button>
            <?php endif; ?>
            <button class="btn-primary" onclick="document.getElementById('addModal').style.display='flex'">
                <i data-lucide="plus"></i> Add Pre-Lead
            </button>
        </div>
    </div>

    <!-- Filter Bar -->
    <!-- Filter Bar -->
    <div class="filter-bar" style="display: flex; flex-direction: row; align-items: center; justify-content: flex-start; flex-wrap: nowrap; gap: 16px; overflow-x: auto; padding: 12px 20px; width: 100%; box-sizing: border-box;">
        
        <div style="position:relative; flex: 1; min-width: 250px;">
            <i data-lucide="search" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); width:16px; color:#94a3b8;"></i>
            <input type="text" id="searchInput" class="filter-input" placeholder="Search Phone, Name, Email..." style="padding-left:36px; width:100%; box-sizing:border-box; margin:0;" oninput="debounceLoad()">
        </div>
        
        <select id="statusFilter" class="filter-input" style="width: 200px; flex-shrink: 0; margin:0;" onchange="loadData()">
            <option value="">All Statuses</option>
            <option value="Not Contacted">Not Contacted</option>
            <option value="Follow Up">Follow Up</option>
            <option value="Interested">Interested</option>
            <option value="Not Interested">Not Interested</option>
            <option value="Junk">Junk</option>
        </select>
        
        <select id="intentFilter" class="filter-input" style="width: 200px; flex-shrink: 0; margin:0;" onchange="loadData()">
            <option value="">All Intents</option>
            <option value="Loan">Loan</option>
            <option value="Insurance">Insurance</option>
            <option value="Credit Card">Credit Card</option>
        </select>
        
        <button class="btn-secondary" style="padding:10px 18px; flex-shrink: 0; white-space: nowrap; margin:0;" onclick="resetFilters()">
            <i data-lucide="refresh-cw" style="width:14px;"></i> Reset
        </button>
        
    </div>
    
    <!-- Data Table -->
    <table class="pl-table">
        <thead>
            <tr>
                <th>Customer Name</th>
                <th>Contact Info</th>
                <th>Intent & Heat</th>
                <th>Status</th>
                <th>Calling Activity</th>
                <th style="text-align:right;">Quick Actions</th>
            </tr>
        </thead>
        <tbody id="plBody">
            <tr><td colspan="6" style="text-align:center; padding:40px;"><div class="loader">Loading...</div></td></tr>
        </tbody>
    </table>
    
    <div class="pagination">
        <button id="prevBtn" class="btn-secondary" onclick="changePage(-1)">Previous</button>
        <span id="pageInfo" style="font-size:13px; font-weight:600; color:#64748b;">Page 1</span>
        <button id="nextBtn" class="btn-secondary" onclick="changePage(1)">Next</button>
    </div>

</div>

<!-- Call Script & Log Modal -->
<div id="callModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i data-lucide="phone-call" style="color:#3b82f6;"></i> Smart Telecaller Console</h3>
            <button onclick="document.getElementById('callModal').style.display='none'" style="background:none;border:none;cursor:pointer;"><i data-lucide="x"></i></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="call_lead_id">
            
            <div style="background:#f8fafc; padding:16px; border-radius:8px; border:1px solid #e2e8f0; margin-bottom:20px;">
                <div style="font-size:12px; color:#64748b; font-weight:700; margin-bottom:8px; text-transform:uppercase;">Suggested Pitch</div>
                <div id="scriptContent" style="font-size:14px; color:#1e293b; line-height:1.5;">
                    "Hi [Name], this is [MyName] from BFS Circle. I noticed you might be looking for financial services. Do you have 2 minutes to discuss how we can help?"
                </div>
            </div>
            
            <div style="margin-bottom:16px;">
                <label style="display:block; font-size:12px; font-weight:700; color:#475569; margin-bottom:8px;">Call Outcome (Status)</label>
                <select id="call_status" class="filter-input" style="width:100%;">
                    <option value="Follow Up">Follow Up Later</option>
                    <option value="Interested">Interested (Convert to Lead)</option>
                    <option value="Not Interested">Not Interested</option>
                    <option value="Junk">Wrong Number / Junk</option>
                </select>
            </div>
            
            <div style="margin-bottom:16px;">
                <label style="display:block; font-size:12px; font-weight:700; color:#475569; margin-bottom:8px;">Follow-up Date (if any)</label>
                <input type="datetime-local" id="call_followup" class="filter-input" style="width:100%;">
            </div>
            
            <div style="margin-bottom:16px;">
                <label style="display:block; font-size:12px; font-weight:700; color:#475569; margin-bottom:8px;">Call Notes</label>
                <textarea id="call_notes" class="filter-input" style="width:100%; resize:vertical;" rows="3" placeholder="Customer said..."></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-secondary" onclick="document.getElementById('callModal').style.display='none'">Cancel</button>
            <button class="btn-primary" onclick="submitCallLog()">Save & Close</button>
        </div>
    </div>
</div>


<!-- Advanced Bulk Import Modal -->
<div id="advancedBulkModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i data-lucide="hard-drive" style="color:#3b82f6;"></i> Advanced Data Import Hub</h3>
            <button onclick="document.getElementById('advancedBulkModal').style.display='none'" style="background:none;border:none;cursor:pointer;color:#64748b;"><i data-lucide="x"></i></button>
        </div>
        <div class="modal-body">
            <p style="font-size:14px; color:#475569; margin-top:0; margin-bottom:20px;">Intelligent import engine. Select your data format, and the system will auto-tag and guard against duplicate numbers.</p>
            
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px;">
                <div class="format-box" onclick="document.getElementById('csv_file').click()">
                    <i data-lucide="file-spreadsheet" style="width:32px; height:32px; color:#10b981; margin-bottom:12px;"></i>
                    <div style="font-weight:700; color:#0f172a; margin-bottom:4px;">Excel / CSV Format</div>
                    <div style="font-size:12px; color:#64748b;">Standard rows and columns</div>
                </div>
                
                <div class="format-box" onclick="alert('Raw Text parsing coming soon!')">
                    <i data-lucide="file-text" style="width:32px; height:32px; color:#f59e0b; margin-bottom:12px;"></i>
                    <div style="font-weight:700; color:#0f172a; margin-bottom:4px;">Raw Text Format</div>
                    <div style="font-size:12px; color:#64748b;">Paste messy text directly</div>
                </div>
            </div>
            
            <form id="advancedBulkForm" onsubmit="submitAdvancedBulk(event)" enctype="multipart/form-data" style="margin-top:20px; border-top:1px solid #e2e8f0; padding-top:20px;">
                <input type="file" id="csv_file" name="csv_file" accept=".csv" required style="display:block; margin-bottom:16px; width:100%; font-size:14px;">
                
                <div style="margin-bottom:16px;">
                    <label style="display:block; font-size:12px; font-weight:700; color:#475569; margin-bottom:8px;">Source Tag (Where did this come from?)</label>
                    <input type="text" name="import_source" class="filter-input" style="width:100%; box-sizing:border-box;" placeholder="e.g., Facebook Ads, JustDial, Old Excel" required>
                </div>
                
                <div style="margin-bottom:16px;">
                    <label style="display:block; font-size:12px; font-weight:700; color:#475569; margin-bottom:8px;">Service Intent (If known)</label>
                    <select name="import_intent" class="filter-input" style="width:100%; box-sizing:border-box;">
                        <option value="Unspecified / Raw">Unspecified / Mixed</option>
                        <option value="Loan">Loan</option>
                        <option value="Insurance">Insurance</option>
                        <option value="Credit Card">Credit Card</option>
                    </select>
                </div>
                
                <div style="background:#f0fdf4; padding:12px; border-radius:8px; border:1px solid #bbf7d0; font-size:12px; color:#166534; display:flex; align-items:center; gap:8px;">
                    <i data-lucide="shield-check" style="width:16px;"></i> Deduplication Guard is Active
                </div>
                
                <button type="submit" id="bulkSubmitBtn" class="btn-primary" style="width:100%; justify-content:center; margin-top:20px; padding:14px;">Upload & Process Data</button>
            </form>
        </div>
    </div>
</div>

<script>
let currentTab = 'new';
let currentPage = 1;
let searchTimeout;

function switchTab(tab) {
    document.querySelectorAll('.smart-tab').forEach(e => e.classList.remove('active'));
    document.getElementById('tab-' + tab).classList.add('active');
    currentTab = tab;
    currentPage = 1;
    loadData();
}

function debounceLoad() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => { currentPage = 1; loadData(); }, 500);
}

function loadData() {
    const s = encodeURIComponent(document.getElementById('searchInput').value);
    const stat = encodeURIComponent(document.getElementById('statusFilter').value);
    const int = encodeURIComponent(document.getElementById('intentFilter').value);
    
    fetch(`?api=get_preleads&page=${currentPage}&tab=${currentTab}&search=${s}&status=${stat}&intent=${int}`)
    .then(res => res.json())
    .then(res => {
        let html = '';
        if(!res.data || res.data.length === 0) {
            html = `<tr><td colspan="6" style="text-align:center; padding:40px; color:#94a3b8;">No data found in this view.</td></tr>`;
        } else {
            res.data.forEach(row => {
                let heat = parseInt(row.heat_score || 0);
                let heatBadge = heat > 70 ? `<span class="score-badge score-hot">🔥 Hot</span>` : (heat > 30 ? `<span class="score-badge score-warm">⚡ Warm</span>` : `<span class="score-badge score-cold">❄️ Cold</span>`);
                
                let intent = row.service_intent || 'Unspecified';
                let lastCall = row.last_called_at ? row.last_called_at : 'Never Called';
                let cCount = row.call_count || 0;
                
                let safeName = (row.name||'').replace(/'/g,"");
                let msg = encodeURIComponent(`Hi ${safeName}, this is from BFS Circle regarding your inquiry.`);
                
                html += `
                <tr>
                    <td>
                        <div style="font-weight:700; color:#0f172a;">${row.name || 'Unknown'}</div>
                        <div style="font-size:12px; color:#64748b;">${row.company_name || 'No Company'}</div>
                    </td>
                    <td>
                        <div style="font-weight:600; color:#1e293b;"><i data-lucide="phone" style="width:12px;"></i> ${row.mobile}</div>
                        <div style="font-size:12px; color:#64748b;"><i data-lucide="mail" style="width:12px;"></i> ${row.email || 'N/A'}</div>
                    </td>
                    <td>
                        <div style="font-weight:600; color:#3b82f6; font-size:12px; margin-bottom:4px;">${intent}</div>
                        ${heatBadge}
                    </td>
                    <td>
                        <div style="font-weight:600; color:#0f172a; font-size:12px;">${row.status || 'Not Contacted'}</div>
                        ${row.followup_date ? `<div style="font-size:11px; color:#d97706; margin-top:4px;"><i data-lucide="clock" style="width:10px;"></i> ${row.followup_date}</div>` : ''}
                    </td>
                    <td>
                        <div style="font-size:12px; color:#475569;">Calls: <b>${cCount}</b></div>
                        <div style="font-size:11px; color:#94a3b8; margin-top:2px;">Last: ${lastCall}</div>
                    </td>
                    <td style="text-align:right; white-space:nowrap;">
                        <a href="tel:${row.mobile}" class="quick-action-btn btn-call" title="Click to Call"><i data-lucide="phone" style="width:14px;"></i></a>
                        <a href="https://wa.me/91${row.mobile}?text=${msg}" target="_blank" class="quick-action-btn btn-whatsapp" title="WhatsApp"><i data-lucide="message-circle" style="width:14px;"></i></a>
                        <button class="quick-action-btn btn-script" onclick="openCallConsole(${row.id}, '${safeName}', '${intent}')" title="Log Call / Script"><i data-lucide="file-text" style="width:14px;"></i></button>
                    </td>
                </tr>`;
            });
        }
        
        // Update KPIs
        if (currentTab === 'new') document.getElementById('kpi-total').innerText = res.total || 0;
        if (currentTab === 'followup') document.getElementById('kpi-followup').innerText = res.total || 0;
        if (currentTab === 'archived') document.getElementById('kpi-converted').innerText = res.total || 0;
document.getElementById('plBody').innerHTML = html;
        lucide.createIcons();
        
        document.getElementById('pageInfo').innerText = `Page ${res.page} of ${res.total_pages || 1} (${res.total} total)`;
        document.getElementById('prevBtn').disabled = res.page <= 1;
        document.getElementById('nextBtn').disabled = res.page >= (res.total_pages||1);
    });
}

function openCallConsole(id, name, intent) {
    document.getElementById('call_lead_id').value = id;
    
    // Smart Script based on intent
    let script = `"Hi ${name}, this is from BFS Circle. `;
    if (intent.includes('Loan')) script += `We noticed you were looking for Loan options. We have some great pre-approved offers for you."`;
    else if (intent.includes('Insurance')) script += `We can help you get the best Insurance quotes today."`;
    else script += `Do you have 2 minutes to discuss how our financial services can help you?"`;
    
    document.getElementById('scriptContent').innerText = script;
    document.getElementById('callModal').style.display = 'flex';
}


function submitAdvancedBulk(e) {
    e.preventDefault();
    const btn = document.getElementById('bulkSubmitBtn');
    btn.innerText = 'Processing...'; btn.disabled = true;
    
    let fd = new FormData(e.target);
    fd.append('api', 'bulk_import_advanced'); // Assumes we will map this in bulk_import.php or api.php
    
    // We will just post it to the standard bulk endpoint for now and handle the UI
    fetch('ajax_bulk_import.php?type=pre_leads', {method: 'POST', body: fd})
    .then(r => r.json())
    .then(d => {
        btn.innerText = 'Upload & Process Data'; btn.disabled = false;
        if(d.success) {
            alert('Data Imported Successfully!');
            document.getElementById('advancedBulkModal').style.display = 'none';
            e.target.reset();
            loadData();
        } else {
            alert(d.error || 'Import Failed');
        }
    }).catch(err => {
        btn.innerText = 'Upload & Process Data'; btn.disabled = false;
        alert('Server Error during import.');
    });
}

function submitCallLog() {
    let id = document.getElementById('call_lead_id').value;
    let stat = document.getElementById('call_status').value;
    let fdate = document.getElementById('call_followup').value;
    let notes = document.getElementById('call_notes').value;
    
    let fd = new FormData();
    fd.append('id', id);
    fd.append('status', stat);
    fd.append('followup_date', fdate);
    fd.append('notes', notes);
    
    fetch('?api=log_call', {method: 'POST', body: fd})
    .then(r=>r.json())
    .then(d=>{
        if(d.success) {
            document.getElementById('callModal').style.display = 'none';
            document.getElementById('call_notes').value = '';
            document.getElementById('call_followup').value = '';
            loadData();
        } else { alert(d.error || 'Failed'); }
    });
}

function changePage(delta) { currentPage += delta; loadData(); }
function resetFilters() { document.getElementById('searchInput').value=''; document.getElementById('statusFilter').value=''; document.getElementById('intentFilter').value=''; loadData(); }

document.addEventListener("DOMContentLoaded", () => {
    lucide.createIcons();
    loadData();
});
</script>

<?php require_once 'footer.php'; ?>
