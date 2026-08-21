<?php
require_once 'config.php';
$staff_members = $db->query("SELECT username FROM users WHERE role = 'Staff' AND is_active = 1")->fetchAll(PDO::FETCH_COLUMN);
$page_title = 'Search & CRM Track (LOS)';
$page_subtitle = 'Track applicant progress, manage pipeline phases and direct processing';
require_once 'header.php';
// Get distinct bank names for email modal
$stmt_banks = $db->query("SELECT DISTINCT bank_name FROM bankers WHERE bank_name IS NOT NULL AND bank_name != '' ORDER BY bank_name");
$banks = $stmt_banks->fetchAll(PDO::FETCH_COLUMN);
?>



<style>
.client-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 16px;
    cursor: pointer;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 2px 8px rgba(0,0,0,0.03);
    margin-bottom: 4px;
}
.client-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
    border-color: #cbd5e1;
}
.client-card.active {
    border: 2px solid #0f172a;
    background: #f8fafc;
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
    transform: translateY(-2px);
}
.client-card-title {
    font-size: 15px;
    font-weight: 700;
    color: #0f172a;
    font-family: 'Outfit', sans-serif;
    letter-spacing: -0.3px;
}
.client-card-meta {
    font-size: 12.5px;
    color: #64748b;
    margin-bottom: 6px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.badge-info { background: #f1f5f9; color: #334155; border: 1px solid #e2e8f0; }
.badge-warning { background: #f1f5f9; color: #334155; border: 1px solid #e2e8f0; }
.badge-primary { background: #f1f5f9; color: #334155; border: 1px solid #e2e8f0; }
.badge-success { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
.badge { font-weight: 600; font-size: 11px; padding: 4px 10px; border-radius: 20px; letter-spacing: 0.2px; text-transform: uppercase; }

/* Scrollbar beautification */
::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}
::-webkit-scrollbar-track {
    background: transparent;
}
::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}
::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

@keyframes shimmer {
    0% { background-position: -468px 0; }
    100% { background-position: 468px 0; }
}
.skeleton {
    background: #f6f7f8;
    background-image: linear-gradient(to right, #f6f7f8 0%, #edeef1 20%, #f6f7f8 40%, #f6f7f8 100%);
    background-repeat: no-repeat;
    background-size: 800px 100%;
    animation-duration: 1.5s;
    animation-fill-mode: forwards;
    animation-iteration-count: infinite;
    animation-name: shimmer;
    animation-timing-function: linear;
    border-radius: 4px;
}

/* Smart smooth entrance animation for cards */
@keyframes slideUpFade {
    from {
        opacity: 0;
        transform: translateY(15px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
.card-animate-enter {
    animation: slideUpFade 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}


.filter-item { display: flex; flex-direction: column; gap: 5px; }
.filter-item label { font-size: 11px; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; }
.filter-select, .filter-input { padding: 10px 12px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 13px; font-weight: 600; color: #0f172a; background: #f8fafc; outline: none; transition: 0.2s; width: 100%; box-sizing: border-box; }
.filter-select:focus, .filter-input:focus { border-color: #f97316; background: #fff; }

.filter-range-row { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }

.filter-actions { display: flex; align-items: center; justify-content: space-between; margin-top: 16px; padding-top: 16px; border-top: 1px solid #f1f5f9; }
.filter-active-chips { display: flex; flex-wrap: wrap; gap: 6px; }
.filter-chip { display: flex; align-items: center; gap: 5px; background: rgba(249,115,22,0.1); border: 1px solid rgba(249,115,22,0.3); color: #c2410c; border-radius: 20px; padding: 4px 10px; font-size: 12px; font-weight: 700; cursor: pointer; }
.filter-chip:hover { background: rgba(249,115,22,0.2); }

.btn-apply-filter { background: #0f172a; color: #fff; border: none; padding: 10px 22px; border-radius: 8px; font-weight: 800; font-size: 13px; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: 0.2s; }
.btn-apply-filter:hover { background: #1e293b; }
.btn-reset-filter { background: transparent; color: #64748b; border: 2px solid #e2e8f0; padding: 9px 16px; border-radius: 8px; font-weight: 700; font-size: 13px; cursor: pointer; transition: 0.2s; }
.btn-reset-filter:hover { border-color: #f97316; color: #f97316; }

/* Quick filter chips row */
.quick-filters { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 12px; }
.qf-chip { padding: 6px 14px; border-radius: 20px; border: 2px solid #e2e8f0; background: #fff; font-size: 12px; font-weight: 700; color: #475569; cursor: pointer; transition: 0.2s; white-space: nowrap; }
.qf-chip:hover, .qf-chip.active { border-color: #0f172a; background: #0f172a; color: #fff; }
.qf-chip.orange.active { border-color: #f97316; background: #f97316; color: #fff; }

/* Result count badge */
.result-meta { font-size: 12px; color: #64748b; font-weight: 600; padding: 8px 0 4px; display: flex; justify-content: space-between; align-items: center; }
.result-count-badge { background: #0f172a; color: #fff; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 800; }

</style>

<div id="view-search-crm" class="view-container">
    <!-- Search bar -->
    <div class="crm-search-bar" style="margin-bottom: 12px; display:flex; gap:12px;">
        <div class="search-input-wrapper" style="flex:1;">
            <i data-lucide="search" class="search-icon"></i>
            <input type="text" id="search-query" placeholder="Search by Applicant Name / Loan ID / Mobile / PAN..." oninput="triggerSearch()">
        </div>
        <button class="filters-toggle-btn" onclick="toggleAdvFilters()" id="advFilterToggleBtn" style="background:#fff; border:1px solid #cbd5e1; color:#0f172a; font-weight:600; padding:0 16px; border-radius:8px; display:flex; align-items:center; gap:8px; cursor:pointer; transition:0.2s;">
            <i data-lucide="sliders-horizontal" style="width:16px;"></i> <span id="advFilterToggleText">Filters</span>
            <span id="filterCountBadge" style="display:none; background:#f97316; color:#fff; border-radius:12px; padding:2px 6px; font-size:10px; margin-left:4px;">0</span>
        </button>
    </div>

    <!-- ===== QUICK FILTER CHIPS ===== -->
    <div class="quick-filters" id="quickFilters" style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:16px;">
        <button class="qf-chip active" onclick="setQuickFilter('all', this)">All Files</button>
        <button class="qf-chip" onclick="setQuickFilter('Phase 1', this)">Phase 1 - KYC</button>
        <button class="qf-chip" onclick="setQuickFilter('Phase 2', this)">Phase 2 - Docs</button>
        <button class="qf-chip" onclick="setQuickFilter('Phase 3', this)">Phase 3 - Bank</button>
        <button class="qf-chip" onclick="setQuickFilter('Phase 4', this)">Phase 4 - Disburse</button>
        <button class="qf-chip" onclick="setQuickFilter('Completed', this)" style="border-color:#bbf7d0; color:#15803d; background:#f0fdf4;">Completed</button>
        <button class="qf-chip" onclick="setQuickFilter('Rejected', this)" style="border-color:#fecaca; color:#b91c1c; background:#fef2f2;">Rejected</button>
    </div>

    <style>
        .qf-chip { padding:6px 14px; border-radius:20px; border:1px solid #e2e8f0; background:#fff; font-size:12px; font-weight:600; cursor:pointer; color:#475569; transition:all 0.2s; }
        .qf-chip:hover { border-color:#0f172a; color:#0f172a; }
        .qf-chip.active { background:#0f172a !important; color:#fff !important; border-color:#0f172a !important; }
        
        .adv-filter-panel { background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px 20px; margin-bottom: 20px; display: none; box-shadow: 0 4px 12px rgba(0,0,0,0.03); }
        .adv-filter-panel.open { display: block; }
        
        .compact-filter-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 12px 16px; }
        
        .f-group { display: flex; flex-direction: column; gap: 4px; }
        .f-group label { font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }
        .f-input { padding: 8px 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px; font-weight: 500; color: #0f172a; outline: none; background: #f8fafc; transition: 0.2s; width: 100%; box-sizing: border-box; }
        .f-input:focus { border-color: #f97316; background: #fff; box-shadow: 0 0 0 3px rgba(249,115,22,0.1); }
        
        .filter-actions-row { display: flex; justify-content: space-between; align-items: center; margin-top: 16px; padding-top: 16px; border-top: 1px solid #f1f5f9; }
        .btn-reset { background: transparent; border: 1px solid #cbd5e1; color: #475569; padding: 8px 16px; border-radius: 6px; font-weight: 600; font-size: 12px; cursor: pointer; transition: 0.2s; }
        .btn-reset:hover { border-color: #0f172a; color: #0f172a; }
        .btn-apply { background: #0f172a; border: none; color: #fff; padding: 8px 24px; border-radius: 6px; font-weight: 600; font-size: 12px; cursor: pointer; transition: 0.2s; box-shadow: 0 2px 8px rgba(15,23,42,0.2); }
        .btn-apply:hover { background: #1e293b; box-shadow: 0 4px 12px rgba(15,23,42,0.3); }
    </style>

    <!-- ===== ADVANCED FILTER PANEL ===== -->
    <div class="adv-filter-panel" id="advFilterBody">
        <div class="compact-filter-grid">
            <div class="f-group">
                <label>Phase Status</label>
                <select id="filter-status" class="f-input" onchange="triggerSearch()">
                    <option value="">All Phases</option>
                    <option value="Phase 1">Phase 1 - KYC</option>
                    <option value="Phase 2">Phase 2 - Documents</option>
                    <option value="Phase 3">Phase 3 - Bank Processing</option>
                    <option value="Phase 4">Phase 4 - Disbursements</option>
                    <option value="Completed">Completed / Sanctioned</option>
                    <option value="Rejected">Rejected</option>
                </select>
            </div>
            <div class="f-group">
                <label>Loan Type</label>
                <select id="filter-type" class="f-input" onchange="triggerSearch()">
                    <option value="">All Loan Types</option>
                    <option value="Home Loan">Home Loan</option>
                    <option value="Personal Loan">Personal Loan</option>
                    <option value="Vehicle Loan">Vehicle Loan</option>
                    <option value="Business Loan">Business Loan</option>
                    <option value="Gold Loan">Gold Loan</option>
                    <option value="Education Loan">Education Loan</option>
                    <option value="LAP">LAP</option>
                </select>
            </div>
            <div class="f-group">
                <label>Assigned Staff</label>
                <select id="filter-staff" class="f-input" onchange="triggerSearch()">
                    <option value="">All Staff</option>
                    <?php foreach($staff_members as $sm): ?>
                    <option value="<?= htmlspecialchars($sm) ?>"><?= htmlspecialchars($sm) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="f-group">
                <label>Bank Assigned</label>
                <select id="filter-bank" class="f-input" onchange="triggerSearch()">
                    <option value="">All Banks</option>
                    <?php foreach($banks as $bk): ?>
                    <option value="<?= htmlspecialchars($bk) ?>"><?= htmlspecialchars($bk) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="f-group">
                <label>TAT Aging</label>
                <select id="filter-aging" class="f-input" onchange="triggerSearch()">
                    <option value="">Any Duration</option>
                    <option value="0-7">0-7 Days</option>
                    <option value="7-30">7-30 Days</option>
                    <option value="30-90">30-90 Days</option>
                    <option value="90+">90+ Days</option>
                </select>
            </div>
            <div class="f-group">
                <label>Sort By</label>
                <select id="filter-sort" class="f-input" onchange="triggerSearch()">
                    <option value="newest">Newest First</option>
                    <option value="oldest">Oldest First</option>
                    <option value="amount_high">Highest Loan</option>
                    <option value="amount_low">Lowest Loan</option>
                    <option value="name_az">Name A-Z</option>
                </select>
            </div>
            <div class="f-group">
                <label>Date From</label>
                <input type="date" id="filter-date-from" class="f-input" onchange="triggerSearch()">
            </div>
            <div class="f-group">
                <label>Date To</label>
                <input type="date" id="filter-date-to" class="f-input" onchange="triggerSearch()">
            </div>
            <div class="f-group">
                <label>Min Amt (₹)</label>
                <input type="number" id="filter-amt-min" class="f-input" placeholder="0" onchange="triggerSearch()">
            </div>
            <div class="f-group">
                <label>Max Amt (₹)</label>
                <input type="number" id="filter-amt-max" class="f-input" placeholder="Any" onchange="triggerSearch()">
            </div>
        </div>

        <div class="filter-actions-row">
            <div id="activeFilterChips" style="display:flex; gap:6px; flex-wrap:wrap;"></div>
            <div style="display:flex; gap:10px; margin-left:auto;">
                <button onclick="resetAllFilters()" class="btn-reset">Reset</button>
                <button onclick="triggerSearch(); toggleAdvFilters();" class="btn-apply">Apply Filters</button>
            </div>
        </div>
    </div>

    <!-- CRM Layout Grid -->
    <div class="crm-layout">
        <div style="display:flex; flex-direction:column; background: #f8fafc; border-right: 1px solid var(--border); overflow: hidden; height: calc(100vh - 180px);">
            <div class="client-list-pane" id="crm-client-list" style="flex:1; border-right:none; background:transparent; padding-bottom: 20px;">
                <!-- Loaded dynamically -->
            </div>
        </div>

        <!-- Right panel: Live Tracker Details -->
        <div class="client-detail-pane" id="crm-detail-pane" style="position: sticky; top: 24px; max-height: calc(100vh - 48px); overflow-y: auto;">
            <div class="detail-placeholder" style="display:flex; flex-direction:column; justify-content:center; align-items:center; height:100%; color:var(--text-light); text-align:center;">
                <i data-lucide="file-search" style="width: 64px; height: 64px; stroke-width: 1.5; margin-bottom:16px;"></i>
                <div>
                    <h3 style="color:var(--text-primary);">No Applicant Selected</h3>
                    <p style="font-size: 13.5px; margin-top: 6px; max-width:300px;">Select an applicant from the list to view their pipeline progress, documents, and banking assignments.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Advanced Filter JS
    function toggleFilterDrawer() {
        toggleAdvFilters();
    }
    
    function toggleAdvFilters() {
        const body = document.getElementById('advFilterBody');
        const btn = document.getElementById('advFilterToggleBtn');
        const txt = document.getElementById('advFilterToggleText');
        body.classList.toggle('open');
        
        if (body.classList.contains('open')) {
            btn.style.background = '#0f172a';
            btn.style.color = '#fff';
            btn.style.borderColor = '#0f172a';
            txt.innerText = 'Close';
        } else {
            btn.style.background = '#fff';
            btn.style.color = '#0f172a';
            btn.style.borderColor = '#cbd5e1';
            txt.innerText = 'Filters';
        }
    }

    function setQuickFilter(status, btn) {
        document.querySelectorAll('.qf-chip').forEach(c => c.classList.remove('active'));
        btn.classList.add('active');
        
        if (status === 'all') {
            document.getElementById('filter-status').value = '';
        } else {
            document.getElementById('filter-status').value = status;
        }
        triggerSearch();
    }

    function resetAllFilters() {
        document.getElementById('search-query').value = '';
        document.getElementById('filter-status').value = '';
        document.getElementById('filter-type').value = '';
        document.getElementById('filter-staff').value = '';
        document.getElementById('filter-bank').value = '';
        document.getElementById('filter-aging').value = '';
        document.getElementById('filter-sort').value = 'newest';
        document.getElementById('filter-date-from').value = '';
        document.getElementById('filter-date-to').value = '';
        document.getElementById('filter-amt-min').value = '';
        document.getElementById('filter-amt-max').value = '';
        
        document.querySelectorAll('.qf-chip').forEach(c => c.classList.remove('active'));
        document.querySelector('.qf-chip').classList.add('active'); // Set 'All' active
        
        triggerSearch();
    }

    function updateFilterChips() {
        const chips = [];
        const status = document.getElementById('filter-status').value;
        const type = document.getElementById('filter-type').value;
        const staff = document.getElementById('filter-staff').value;
        const bank = document.getElementById('filter-bank').value;
        const aging = document.getElementById('filter-aging').value;
        const dFrom = document.getElementById('filter-date-from').value;
        const dTo = document.getElementById('filter-date-to').value;
        const aMin = document.getElementById('filter-amt-min').value;
        const aMax = document.getElementById('filter-amt-max').value;
        
        if(status) chips.push(`Status: ${status}`);
        if(type) chips.push(`Type: ${type}`);
        if(staff) chips.push(`Staff: ${staff}`);
        if(bank) chips.push(`Bank: ${bank}`);
        if(aging) chips.push(`Aging: ${aging} days`);
        if(dFrom || dTo) chips.push(`Date: ${dFrom} to ${dTo}`);
        if(aMin || aMax) chips.push(`Amt: ${aMin} to ${aMax}`);
        
        const container = document.getElementById('activeFilterChips');
        const badge = document.getElementById('filterCountBadge');
        
        container.innerHTML = chips.map(c => `<span style="background:#f8fafc; border:1px solid #cbd5e1; color:#475569; padding:2px 8px; border-radius:4px; font-size:10px; font-weight:600;">${c}</span>`).join('');
        
        if(chips.length > 0) {
            badge.style.display = 'inline-block';
            badge.innerText = `${chips.length} active`;
        } else {
            badge.style.display = 'none';
        }
    }

    function formatAmt(num) {
        return new Intl.NumberFormat('en-IN').format(num);
    }


    async function addNote(id) {
        const input = document.getElementById('noteInput');
        const note = input.value.trim();
        if(!note) return;
        
        const fd = new FormData();
        fd.append('id', id);
        fd.append('note', note);
        
        try {
            const res = await fetch('api.php?api=add_applicant_note', { method: 'POST', body: fd });
            const data = await res.json();
            if(data.success) {
                input.value = '';
                showNotification('Note added', 'success');
                selectApplicantCard(id); // reload the pane
            } else {
                showNotification(data.error || 'Failed', 'error');
            }
        } catch(e) {
            showNotification('Error adding note', 'error');
        }
    }

    let currentOffset = 0;

    async function triggerSearch(reset = true) {
        if (reset) {
            currentOffset = 0;
        }
        
        updateFilterChips();
        
        const params = new URLSearchParams({
            api: 'search_applicants',
            query: document.getElementById('search-query').value,
            status: document.getElementById('filter-status').value,
            loan_type: document.getElementById('filter-type').value,
            staff: document.getElementById('filter-staff').value,
            bank: document.getElementById('filter-bank').value,
            aging: document.getElementById('filter-aging').value,
            sort: document.getElementById('filter-sort').value,
            date_from: document.getElementById('filter-date-from').value,
            date_to: document.getElementById('filter-date-to').value,
            amt_min: document.getElementById('filter-amt-min').value,
            amt_max: document.getElementById('filter-amt-max').value,
            offset: currentOffset
        });
        
        try {
            const container = document.getElementById('crm-client-list');
            if (reset) {
                // Show Skeleton Loader for Left Pane
                container.innerHTML = Array(5).fill(`
                    <div class="client-card" style="pointer-events: none; border: 1px solid #e2e8f0;">
                        <div class="client-card-header">
                            <div class="skeleton" style="width: 140px; height: 20px;"></div>
                            <div class="skeleton" style="width: 60px; height: 24px; border-radius: 12px;"></div>
                        </div>
                        <div class="client-card-meta"><div class="skeleton" style="width: 120px; height: 14px;"></div></div>
                        <div class="client-card-meta"><div class="skeleton" style="width: 180px; height: 14px;"></div></div>
                        <div class="client-card-meta" style="margin-bottom:0;"><div class="skeleton" style="width: 100px; height: 14px;"></div></div>
                        <div style="margin-top: 12px; border-top: 1px dashed var(--border); padding-top: 10px; display: flex; justify-content: space-between;">
                            <div class="skeleton" style="width: 70px; height: 12px;"></div>
                            <div class="skeleton" style="width: 90px; height: 12px;"></div>
                        </div>
                    </div>
                `).join('');
            } else {
                // Change Load More button text to loading
                const realBtn = document.getElementById('real-load-more-btn');
                if (realBtn) realBtn.innerHTML = `<img src="logo.png" style="width:20px; height:20px; filter:brightness(0) invert(1); animation: spin-slow 1s linear infinite; vertical-align:-5px; margin-right:8px;" alt="Loading..."> Loading...
                
.filter-item { display: flex; flex-direction: column; gap: 5px; }
.filter-item label { font-size: 11px; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; }
.filter-select, .filter-input { padding: 10px 12px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 13px; font-weight: 600; color: #0f172a; background: #f8fafc; outline: none; transition: 0.2s; width: 100%; box-sizing: border-box; }
.filter-select:focus, .filter-input:focus { border-color: #f97316; background: #fff; }

.filter-range-row { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }

.filter-actions { display: flex; align-items: center; justify-content: space-between; margin-top: 16px; padding-top: 16px; border-top: 1px solid #f1f5f9; }
.filter-active-chips { display: flex; flex-wrap: wrap; gap: 6px; }
.filter-chip { display: flex; align-items: center; gap: 5px; background: rgba(249,115,22,0.1); border: 1px solid rgba(249,115,22,0.3); color: #c2410c; border-radius: 20px; padding: 4px 10px; font-size: 12px; font-weight: 700; cursor: pointer; }
.filter-chip:hover { background: rgba(249,115,22,0.2); }

.btn-apply-filter { background: #0f172a; color: #fff; border: none; padding: 10px 22px; border-radius: 8px; font-weight: 800; font-size: 13px; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: 0.2s; }
.btn-apply-filter:hover { background: #1e293b; }
.btn-reset-filter { background: transparent; color: #64748b; border: 2px solid #e2e8f0; padding: 9px 16px; border-radius: 8px; font-weight: 700; font-size: 13px; cursor: pointer; transition: 0.2s; }
.btn-reset-filter:hover { border-color: #f97316; color: #f97316; }

/* Quick filter chips row */
.quick-filters { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 12px; }
.qf-chip { padding: 6px 14px; border-radius: 20px; border: 2px solid #e2e8f0; background: #fff; font-size: 12px; font-weight: 700; color: #475569; cursor: pointer; transition: 0.2s; white-space: nowrap; }
.qf-chip:hover, .qf-chip.active { border-color: #0f172a; background: #0f172a; color: #fff; }
.qf-chip.orange.active { border-color: #f97316; background: #f97316; color: #fff; }

/* Result count badge */
.result-meta { font-size: 12px; color: #64748b; font-weight: 600; padding: 8px 0 4px; display: flex; justify-content: space-between; align-items: center; }
.result-count-badge { background: #0f172a; color: #fff; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 800; }

</style>`;
            }
            
            const response = await fetch('?' + params.toString());
            const applicants = await response.json();
            
            if (reset) {
                container.innerHTML = '';
            }
            
            if (reset && applicants.length === 0) {
                container.innerHTML = '<div style="background: white; border: 1px solid #e2e8f0; text-align: center; color: var(--text-light); padding: 40px; border-radius: var(--radius-md);">No applicants match criteria.</div>';
                return;
            }
            
            const existingBtn = document.getElementById('load-more-btn-container');
            if (existingBtn) existingBtn.remove();
            
            applicants.forEach((app, index) => {
                const card = document.createElement('div');
                // Add the smooth animation class with a staggered delay based on index
                card.className = 'client-card card-animate-enter';
                card.style.animationDelay = `${index * 0.05}s`;
                card.id = 'crm-client-card-' + app.id;
                card.dataset.id = app.id;
                card.onclick = () => selectApplicantCard(app.id);
                
                let badgeClass = 'badge-info';
                if(app.overall_status === 'Phase 2') badgeClass = 'badge-warning';
                if(app.overall_status === 'Phase 3') badgeClass = 'badge-primary';
                if(app.overall_status === 'Phase 4') badgeClass = 'badge-warning';
                if(app.overall_status === 'Completed') {
                    badgeClass = 'badge-success';
                    card.classList.add('won');
                }
                if(app.overall_status === 'Rejected') {
                    badgeClass = 'badge-danger';
                }
                
                card.innerHTML = `
                    <div class="client-card-header">
                        <span class="client-card-title">
                            ${app.customer_name}
                            ${app.calculated_completion < 100 
                                ? `<span style="display:inline-block; font-size: 10px; color: #ef4444; font-weight: 600; margin-left: 4px;" title="${app.calculated_completion}% Profile Complete"><i data-lucide="alert-circle" style="width:10px;height:10px;"></i> ${app.calculated_completion}%</span>`
                                : `<span style="display:inline-block; font-size: 10px; color: #10b981; font-weight: 600; margin-left: 4px;" title="100% Complete Profile"><i data-lucide="check-circle" style="width:10px;height:10px;"></i> 100%</span>`
                            }
                        </span>
                        <span class="badge ${badgeClass}">${app.overall_status}</span>
                    </div>
                    <div class="client-card-meta">
                        <i data-lucide="tag" style="width: 12px; height: 12px;"></i>
                        ${app.loan_id}
                    </div>
                    <div class="client-card-meta">
                        <i data-lucide="briefcase" style="width: 12px; height: 12px;"></i>
                        ${app.loan_type} - <span style="font-weight: 600;">₹${new Intl.NumberFormat('en-IN').format(app.amount)}</span>
                    </div>
                    <div class="client-card-meta" style="margin-bottom: 0;">
                        <i data-lucide="phone" style="width: 12px; height: 12px;"></i>
                        ${app.mobile}
                    </div>
                    <div style="margin-top: 12px; border-top: 1px dashed var(--border); padding-top: 10px; font-size: 11px; color: var(--text-light); display: flex; justify-content: space-between;">
                        <span><i data-lucide="clock" style="width:10px;height:10px;vertical-align:-1px;"></i> Unknown</span>
                        <span>Added by: System</span>
                    </div>
                `;
                container.appendChild(card);
            });
            
            if (applicants.length === 10) {
                const btnContainer = document.createElement('div');
                btnContainer.id = 'load-more-btn-container';
                btnContainer.style.padding = '16px';
                btnContainer.innerHTML = `<button id="real-load-more-btn" class="btn btn-primary" style="width: 100%; padding: 12px; font-weight: bold; background: #0f172a; color: white; border: none; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2);" onclick="currentOffset += 10; triggerSearch(false);">
                    ↓ Load More Records ↓
                </button>`;
                container.appendChild(btnContainer);
            }
            
            if(window.lucide) lucide.createIcons();
            
            // Automatically select the first applicant if this is a fresh search and results exist
            if (reset && applicants.length > 0) {
                // Ensure a small delay so the DOM has time to render the cards before adding 'active' class
                setTimeout(() => {
                    selectApplicantCard(applicants[0].id);
                }, 50);
            }
            
        } catch (error) {
            console.error('Error searching:', error);
            showNotification('Applicant search failed.', 'error');
        }
    }

    async function selectApplicantCard(id) {
        document.querySelectorAll('.client-card').forEach(el => el.classList.remove('active'));
        
        const selectedCard = document.getElementById('crm-client-card-' + id);
        if (selectedCard) selectedCard.classList.add('active');
        
        const pane = document.getElementById('crm-detail-pane');
        
        // Show loading state with logo.png
        pane.innerHTML = `
            <div style="display:flex; flex-direction:column; justify-content:center; align-items:center; height:100%; color:var(--text-light); text-align:center;">
                <div style="background:var(--primary); width:80px; height:80px; border-radius:50%; display:flex; justify-content:center; align-items:center; margin-bottom:24px; animation: pulse-ring 1.5s cubic-bezier(0.215, 0.61, 0.355, 1) infinite;">
                    <img src="logo.png" style="width:40px; height:40px; filter:brightness(0) invert(1); animation: pulse-logo 1.5s ease-in-out infinite alternate;" alt="Loading...">
                </div>
                <h3 style="color:var(--text-primary); margin:0;">Loading Records...</h3>
                <p style="font-size: 13.5px; margin-top: 6px; max-width:300px;">Please wait while we fetch the pipeline progress.</p>
                <style>
                    @keyframes pulse-ring {
                        0% { box-shadow: 0 0 0 0 rgba(15,23,42,0.3); }
                        70% { box-shadow: 0 0 0 20px rgba(15,23,42,0); }
                        100% { box-shadow: 0 0 0 0 rgba(15,23,42,0); }
                    }
                    @keyframes pulse-logo {
                        0% { transform: scale(0.9); opacity:0.8; }
                        100% { transform: scale(1.1); opacity:1; }
                    }
                

.filter-item { display: flex; flex-direction: column; gap: 5px; }
.filter-item label { font-size: 11px; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; }
.filter-select, .filter-input { padding: 10px 12px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 13px; font-weight: 600; color: #0f172a; background: #f8fafc; outline: none; transition: 0.2s; width: 100%; box-sizing: border-box; }
.filter-select:focus, .filter-input:focus { border-color: #f97316; background: #fff; }

.filter-range-row { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }

.filter-actions { display: flex; align-items: center; justify-content: space-between; margin-top: 16px; padding-top: 16px; border-top: 1px solid #f1f5f9; }
.filter-active-chips { display: flex; flex-wrap: wrap; gap: 6px; }
.filter-chip { display: flex; align-items: center; gap: 5px; background: rgba(249,115,22,0.1); border: 1px solid rgba(249,115,22,0.3); color: #c2410c; border-radius: 20px; padding: 4px 10px; font-size: 12px; font-weight: 700; cursor: pointer; }
.filter-chip:hover { background: rgba(249,115,22,0.2); }

.btn-apply-filter { background: #0f172a; color: #fff; border: none; padding: 10px 22px; border-radius: 8px; font-weight: 800; font-size: 13px; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: 0.2s; }
.btn-apply-filter:hover { background: #1e293b; }
.btn-reset-filter { background: transparent; color: #64748b; border: 2px solid #e2e8f0; padding: 9px 16px; border-radius: 8px; font-weight: 700; font-size: 13px; cursor: pointer; transition: 0.2s; }
.btn-reset-filter:hover { border-color: #f97316; color: #f97316; }

/* Quick filter chips row */
.quick-filters { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 12px; }
.qf-chip { padding: 6px 14px; border-radius: 20px; border: 2px solid #e2e8f0; background: #fff; font-size: 12px; font-weight: 700; color: #475569; cursor: pointer; transition: 0.2s; white-space: nowrap; }
.qf-chip:hover, .qf-chip.active { border-color: #0f172a; background: #0f172a; color: #fff; }
.qf-chip.orange.active { border-color: #f97316; background: #f97316; color: #fff; }

/* Result count badge */
.result-meta { font-size: 12px; color: #64748b; font-weight: 600; padding: 8px 0 4px; display: flex; justify-content: space-between; align-items: center; }
.result-count-badge { background: #0f172a; color: #fff; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 800; }

</style>
            </div>
        `;
        
        try {
            const response = await fetch(`?api=applicant_full_details&id=${id}`);
            const app = await response.json();
            
            if (app.error) {
                showNotification(app.error, 'error');
                pane.innerHTML = '<div style="text-align:center; padding:40px; color:var(--danger);">Error loading details</div>';
                return;
            }
            
            pane.innerHTML = '';
            
            const steps = {
                phase1: ['Phase 1', 'Phase 2', 'Phase 3', 'Phase 4', 'Completed', 'Rejected'].includes(app.overall_status),
                phase2: ['Phase 2', 'Phase 3', 'Phase 4', 'Completed', 'Rejected'].includes(app.overall_status),
                phase3: ['Phase 3', 'Phase 4', 'Completed', 'Rejected'].includes(app.overall_status),
                phase4: ['Phase 4', 'Completed', 'Rejected'].includes(app.overall_status)
            };
            

            // Documents HTML Advanced Checklist
            let docsHtml = '';
            if (app.phase2_checklist) {
                docsHtml = `
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                        <span style="font-weight:600; color:var(--text-primary);"><i data-lucide="check-square" style="width:16px; vertical-align:-2px;"></i> Mandatory Checklist (${app.loan_type})</span>
                        <span class="badge ${app.phase2_completion === 100 ? 'badge-success' : 'badge-warning'}">${app.phase2_completion}% Complete</span>
                    </div>
                    <table class="data-table">
                        <thead><tr><th>Category</th><th>Status</th></tr></thead>
                        <tbody>
                            ${app.phase2_checklist.map(d => `<tr><td><strong>${d.category}</strong></td><td>${d.uploaded ? '<span class="badge badge-success"><i data-lucide="check" style="width:12px;"></i> Uploaded</span>' : '<span class="badge badge-danger"><i data-lucide="x" style="width:12px;"></i> Pending</span>'}</td></tr>`).join('')}
                        </tbody>
                    </table>
                `;
            } else {
                docsHtml = '<div style="text-align:center; padding:20px; color:var(--text-light);">No checklist found.</div>';
            }
            
            // Disbursements HTML
            let disbHtml = '';
            if (app.disbursements && app.disbursements.length > 0) {
                disbHtml = `
                    <table class="data-table">
                        <thead><tr><th>Phase</th><th>Amount</th><th>Status</th></tr></thead>
                        <tbody>
                            ${app.disbursements.map(d => `<tr><td>Phase ${d.phase_number}: ${d.phase_name}</td><td style="font-weight:bold;">₹${formatAmt(d.amount)}</td><td><span class="badge ${d.status === 'Disbursed' ? 'badge-success' : 'badge-warning'}">${d.status}</span></td></tr>`).join('')}
                        </tbody>
                    </table>
                `;
            } else {
                disbHtml = '<div style="text-align:center; padding:20px; color:var(--text-light);">No disbursements recorded yet.</div>';
            }
            
            // Payout Distributions HTML
            let payoutsHtml = '';
            if (app.payouts && app.payouts.length > 0) {
                payoutsHtml = `
                    <table class="data-table">
                        <thead><tr><th>Payee</th><th>Type</th><th>Net Payable</th><th>Status</th></tr></thead>
                        <tbody>
                            ${app.payouts.map(p => {
                                let badge = 'badge-warning';
                                if (p.status === 'Paid') badge = 'badge-success';
                                if (p.status === 'Cancelled') badge = 'badge-danger';
                                return `<tr>
                                    <td><strong>${p.payee_name || 'N/A'}</strong></td>
                                    <td>${p.payee_type}</td>
                                    <td style="font-weight:bold; color:var(--success);">₹${formatAmt(p.net_payable)}</td>
                                    <td><span class="badge ${badge}">${p.status}</span></td>
                                </tr>`;
                            }).join('')}
                        </tbody>
                    </table>
                `;
            } else {
                payoutsHtml = '<div style="text-align:center; padding:20px; color:var(--text-light);">No payouts distributed yet.</div>';
            }
            
            // Banks HTML
            let banksHtml = '';
            if (app.banks && app.banks.length > 0) {
                banksHtml = `
                    <table class="data-table">
                        <thead><tr><th>Bank Name</th><th>Status</th><th>Resolution</th></tr></thead>
                        <tbody>
                            ${app.banks.map(b => {
                                let badge = 'badge-warning';
                                if (b.status === 'Approved') badge = 'badge-success';
                                if (b.status === 'Rejected') badge = 'badge-danger';
                                return `<tr><td><strong>${b.bank_name}</strong></td><td><span class="badge ${badge}">${b.status}</span></td><td style="font-size:12px; color:var(--text-muted);">${b.rejection_reason || '-'}</td></tr>`;
                            }).join('')}
                        </tbody>
                    </table>
                `;
            } else {
                banksHtml = '<div style="text-align:center; padding:20px; color:var(--text-light);">No banks assigned yet.</div>';
            }
            
            // Timeline HTML
            let timelineHtml = '<div class="timeline" style="margin-top:16px;">';
            if(app.timeline && app.timeline.length > 0) {
                app.timeline.forEach(t => {
                    timelineHtml += `
                        <div style="border-left: 2px solid var(--border); padding-left: 12px; margin-bottom: 12px; position:relative;">
                            <div style="position:absolute; left:-6px; top:4px; width:10px; height:10px; background:var(--primary); border-radius:50%;"></div>
                            <div style="font-size:11px; color:var(--text-muted);">${t.created_at}</div>
                            <div style="font-size:13px; color:var(--text-primary);">${t.description}</div>
                            <div style="font-size:11px; color:var(--text-light);">By: ${t.username}</div>
                        </div>
                    `;
                });
            } else {
                timelineHtml += '<div style="color:var(--text-muted); font-size:12px;">No activities recorded.</div>';
            }
            timelineHtml += '</div>';

            // CIBIL & Source & TAT Bar
            let cibilColor = app.cibil_score >= 750 ? 'badge-success' : (app.cibil_score >= 650 ? 'badge-warning' : 'badge-danger');
            let metaBar = `
                <div style="display:flex; gap:16px; background:#f8fafc; padding:12px; border-radius:var(--radius-md); border:1px solid #e2e8f0; margin-bottom:16px; flex-wrap:wrap;">
                    <div><span style="font-size:11px; color:var(--text-muted); display:block; text-transform:uppercase; font-weight:700;">TAT Ageing</span>
                        <span class="badge ${app.tat_days > 7 ? 'badge-danger' : 'badge-info'}">️ ${app.tat_days} days in system</span>
                    </div>
                    ${app.cibil_score ? `<div><span style="font-size:11px; color:var(--text-muted); display:block; text-transform:uppercase; font-weight:700;">CIBIL</span>
                        <span class="badge ${cibilColor}">${app.cibil_score}</span></div>` : ''}
                    <div><span style="font-size:11px; color:var(--text-muted); display:block; text-transform:uppercase; font-weight:700;">Source</span>
                        <span style="font-weight:600; font-size:13px;">${app.lead_source || 'Direct'} ${app.referral_id ? '(' + app.referral_id + ')' : ''}</span>
                    </div>
                    <div><span style="font-size:11px; color:var(--text-muted); display:block; text-transform:uppercase; font-weight:700;">Added By</span>
                        <span style="font-weight:600; font-size:13px;">${app.added_by || 'System'}</span>
                    </div>
                </div>
            `;

            let actionButtons = '';
            if (app.phase1_completion === 100 && app.phase2_completion === 100) {
                let msg = encodeURIComponent(`Hello Bank Team,
Sharing complete file for ${app.customer_name}.
Loan Type: ${app.loan_type}
Amount: ${app.loan_amount_requested}
CIBIL: ${app.cibil_score || 'N/A'}
Please process.`);
                actionButtons = `
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 12px; border-radius: var(--radius-md); display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <div style="color: #0f172a; font-weight: 600;"><i data-lucide="check-circle" style="vertical-align:-2px; width:16px;"></i> File 100% Ready for Bank Dispatch</div>
                        <div style="display: flex; gap: 8px;">
                            <button type="button" onclick="openBankDispatchModal(${app.id}, '${app.customer_name}', '${app.loan_id}', '${app.loan_type}', '${app.loan_amount_requested}', ${app.documents ? app.documents.length : 0})" class="btn btn-primary" style="background:#0f172a; border:none; padding:6px 12px; font-size:13px;"><i data-lucide="mail" style="width:14px;"></i> Email</button>
                            <a href="https://wa.me/?text=${msg}" target="_blank" class="btn btn-primary" style="background:#16a34a; border:none; padding:6px 12px; font-size:13px;"><i data-lucide="message-circle" style="width:14px;"></i> WhatsApp</a>
                        </div>
                    </div>
                `;
            }

            pane.innerHTML = `
                ${app.overall_status === 'Completed' ? '<div style="background: var(--status-won); color: white; padding: 12px; border-radius: 8px 8px 0 0; margin: -24px -24px 20px -24px; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px; gap: 8px;"><i data-lucide="award" style="width:18px;"></i> Loan Application Successfully Completed</div>' : ''}
                ${app.overall_status === 'Rejected' ? '<div style="background: var(--danger); color: white; padding: 12px; border-radius: 8px 8px 0 0; margin: -24px -24px 20px -24px; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px; gap: 8px;"><i data-lucide="x-octagon" style="width:18px;"></i> Loan Application Rejected</div>' : ''}
                
                ${actionButtons}
                
                <div class="detail-header" style="margin-bottom: 16px;">
                    <div>
                        <div style="display:flex; align-items:center; gap:12px; margin-bottom:4px;">
                            <div class="detail-company-title" style="font-size: 24px; color:var(--text-primary); margin:0;">${app.customer_name}</div>
                            <button onclick="openReminderModal('Lead', ${app.id}, '${app.customer_name}')" class="btn btn-secondary" style="padding:6px; height:32px; width:32px; border-radius:8px; display:inline-flex; align-items:center; justify-content:center; color:#f59e0b;" title="Set Lead Reminder"><i data-lucide="bell" style="width:16px; height:16px;"></i></button>
                        </div>
                        <div style="font-size: 14px; color: var(--text-muted); font-weight:600;">Loan ID: <span style="color:var(--primary);">${app.loan_id}</span></div>
                    </div>
                    <div style="text-align: right;">
                        <span class="badge-status ${app.overall_status.toLowerCase().replace(' ', '')}" style="font-size: 14px; padding: 8px 16px;">
                            ${app.overall_status === 'Phase 1' && app.phase1_completion === 100 && app.documents && app.documents.length > 0 ? 'Phase 2' : app.overall_status}
                        </span>
                    </div>
                </div>
                
                ${metaBar}
                
                ${app.phase1_completion < 100 ? `
                    <div style="background: #fffbeb; border: 1px solid #fcd34d; padding: 12px 16px; border-radius: var(--radius-md); margin-bottom: 20px; display:flex; justify-content:space-between; align-items:center;">
                        <div>
                            <div style="color: #b45309; font-weight: 700; margin-bottom:4px;"><i data-lucide="alert-triangle" style="width:16px; vertical-align:-3px;"></i> Incomplete Phase 1 (${app.phase1_completion}%)</div>
                            <div style="color: #92400e; font-size: 12px;">Missing: <strong>${app.phase1_missing.join(', ')}</strong></div>
                        </div>
                        <a href="add_applicant.php?id=${app.id}" class="btn" style="background:#334155; color:white; border:none; padding:6px 12px; font-size:12px;">Complete Profile</a>
                    </div>
                ` : `
                    <div style="margin-bottom: 20px; display:flex; align-items:center; gap:8px;">
                        <span class="badge badge-success"><i data-lucide="check" style="width:14px; vertical-align:-2px;"></i> Phase 1 Profile 100% Complete</span>
                        <a href="add_applicant.php?id=${app.id}" class="btn btn-secondary" style="padding:2px 8px; font-size:11px;">Edit Details</a>
                    </div>
                `}
                
                <!-- LOS Pipeline Tracker -->
                <div class="detail-block-title" style="margin-top:24px;">LOS Pipeline Progress</div>
                <div class="pipeline-tracker">
                    <div class="pipeline-step ${app.phase1_completion === 100 ? 'completed' : 'active'}" 
                         onclick="window.location.href='add_applicant.php?id=${app.id}'" 
                         style="cursor:pointer;" title="Go to KYC">
                        <div class="pipeline-icon-circle"><i data-lucide="user-check" style="width:14px;"></i></div>
                        <span class="pipeline-step-label">1. KYC</span>
                    </div>
                    
                    <div class="pipeline-step ${app.phase2_completion === 100 ? 'completed' : (app.phase1_completion === 100 ? 'active' : '')}" 
                         onclick="${app.phase1_completion === 100 ? `window.location.href='applicant_documents.php?id=${app.id}'` : `showCustomAlert('Action Blocked', 'Please complete Step 1 (KYC) first.')`}" 
                         style="cursor:${app.phase1_completion === 100 ? 'pointer' : 'not-allowed'}; opacity:${app.phase1_completion === 100 ? '1' : '0.6'};" title="Go to Docs">
                        <div class="pipeline-icon-circle"><i data-lucide="file-text" style="width:14px;"></i></div>
                        <span class="pipeline-step-label">2. Docs</span>
                    </div>
                    
                    <div class="pipeline-step ${steps.phase3 ? 'completed' : (app.phase2_completion === 100 ? 'active' : '')}" 
                         onclick="${app.phase2_completion === 100 ? `window.location.href='applicant_disbursements.php?id=${app.id}'` : `showCustomAlert('Action Blocked', 'Please complete Step 2 (Docs) first.')`}" 
                         style="cursor:${app.phase2_completion === 100 ? 'pointer' : 'not-allowed'}; opacity:${app.phase2_completion === 100 ? '1' : '0.6'};" title="Go to Bank">
                        <div class="pipeline-icon-circle"><i data-lucide="landmark" style="width:14px;"></i></div>
                        <span class="pipeline-step-label">3. Bank</span>
                    </div>
                    
                    <div class="pipeline-step ${steps.phase4 ? 'completed' : (steps.phase3 ? 'active' : '')}" 
                         onclick="${steps.phase3 ? `window.location.href='applicant_bank_assign.php?id=${app.id}'` : `showCustomAlert('Action Blocked', 'Please complete Step 3 (Bank Processing) first.')`}" 
                         style="cursor:${steps.phase3 ? 'pointer' : 'not-allowed'}; opacity:${steps.phase3 ? '1' : '0.6'};" title="Go to Disb.">
                        <div class="pipeline-icon-circle"><i data-lucide="coins" style="width:14px;"></i></div>
                        <span class="pipeline-step-label">4. Disb.</span>
                    </div>
                    
                    <div class="pipeline-step ${(app.payouts && app.payouts.length > 0) ? 'completed' : (steps.phase4 ? 'active' : '')}" 
                         onclick="${steps.phase4 ? `window.location.href='payout_distribution.php?search=${app.loan_id}'` : `showCustomAlert('Action Blocked', 'Please complete Step 4 (Disbursement) first.')`}" 
                         style="cursor:${steps.phase4 ? 'pointer' : 'not-allowed'}; opacity:${steps.phase4 ? '1' : '0.6'};" title="Go to Payout">
                        <div class="pipeline-icon-circle"><i data-lucide="wallet" style="width:14px;"></i></div>
                        <span class="pipeline-step-label">5. Payout</span>
                    </div>
                </div>
                
                <div class="detail-block-title" style="margin-top: 32px; display:flex; justify-content:space-between; align-items:center;">
                    <span>Action Dashboard</span>
                </div>
                
                <!-- Notes Input -->
                <div style="margin-bottom: 20px; display:flex; gap:8px;">
                    <input type="text" id="noteInput" placeholder="Add an internal note or remark..." style="flex:1; padding:8px 12px; border:1px solid var(--border); border-radius:var(--radius-md);">
                    <button onclick="addNote(${app.id})" class="btn btn-secondary" style="padding:8px 16px;"><i data-lucide="send" style="width:16px;"></i></button>
                </div>

                <div class="dashboard-layout-row" style="grid-template-columns: 2fr 1fr; gap:24px;">
                    <div>
                        <div class="detail-block-title">Phase 2: Documents</div>
                        <div style="margin-bottom:12px;"><a href="applicant_documents.php?id=${app.id}" class="btn btn-secondary" style="padding:4px 12px; font-size:12px;"><i data-lucide="upload" style="width:14px;"></i> Manage Documents</a></div>
                        ${docsHtml}
                        
                        <div class="detail-block-title" style="margin-top: 32px;">Phase 3: Bank Processing</div>
                        <div style="margin-bottom:12px;"><a href="applicant_disbursements.php?id=${app.id}" class="btn btn-secondary" style="padding:4px 12px; font-size:12px;"><i data-lucide="landmark" style="width:14px;"></i> Bank Processing</a></div>
                        ${banksHtml}
                        
                        <div class="detail-block-title" style="margin-top: 32px;">Phase 4: Final Disbursements</div>
                        <div style="margin-bottom:12px;"><a href="applicant_bank_assign.php?id=${app.id}" class="btn btn-secondary" style="padding:4px 12px; font-size:12px;"><i data-lucide="coins" style="width:14px;"></i> Customer Kundli & Disb.</a></div>
                        ${disbHtml}

                        <div class="detail-block-title" style="margin-top: 32px;">Payout Distributions</div>
                        <div style="margin-bottom:12px;"><a href="payout_distribution.php?search=${app.loan_id}" class="btn btn-secondary" style="padding:4px 12px; font-size:12px;"><i data-lucide="coins" style="width:14px;"></i> Manage Payouts</a></div>
                        ${payoutsHtml}
                    </div>
                    
                    <div style="background:#f8fafc; border:1px solid var(--border); border-radius:var(--radius-md); padding:16px;">
                        <h4 style="margin:0 0 16px 0; font-size:14px; color:var(--text-primary);"><i data-lucide="clock" style="width:16px; vertical-align:-2px;"></i> Activity Timeline</h4>
                        ${timelineHtml}
                    </div>
                </div>
            `;
            
            lucide.createIcons();


        } catch (err) {
            console.error(err);
            showNotification('Could not load applicant LOS details.', 'error');
        }
    }

    function switchDetailTab(tabName) {
        document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        
        document.getElementById('tab-btn-' + tabName).classList.add('active');
        document.getElementById('tab-content-' + tabName).classList.add('active');
    }

    async function deleteApplicant(id) {
        if (!confirm('Are you sure you want to completely delete this application? This action cannot be undone.')) return;
        
        try {
            const formData = new FormData();
            formData.append('id', id);
            
            const res = await fetch('config.php?api=delete_applicant', {
                method: 'POST',
                body: formData
            });
            
            const text = await res.text();
            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                console.error("Failed to parse JSON. Server returned:", text);
                alert("Server Error. Please check console for details.\n\n" + text.substring(0, 100));
                return;
            }

            if (data.success) {
                alert('Application deleted successfully.');
                location.reload();
            } else {
                alert(data.error || 'Failed to delete application.');
            }
        } catch (err) {
            console.error(err);
            alert('A network error occurred: ' + err.message);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        triggerSearch();
        
        const urlParams = new URLSearchParams(window.location.search);
        const viewId = urlParams.get('id');
        if (viewId) {
            setTimeout(() => {
                selectApplicantCard(viewId);
            }, 600);
        }
    });
</script>


<!-- Detailed Email Modal for Bank Dispatch -->
<div id="email-modal" onclick="closeBankDispatchModalOutside(event)" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.6); z-index:9999; align-items:center; justify-content:center; backdrop-filter: blur(4px);">
    <div id="email-modal-content" style="background:#fff; width:700px; min-width:500px; min-height:400px; border-radius:12px; box-shadow:0 10px 25px rgba(0,0,0,0.2); overflow:hidden; display:flex; flex-direction:column; max-height: 90vh; max-width: 95vw; resize:both;">
        
        <!-- Header -->
        <div style="background:var(--primary); padding:16px 24px; color:#fff; display:flex; justify-content:space-between; align-items:center;">
            <h3 style="margin:0; font-size:16px; display:flex; align-items:center; gap:8px;">
                <i data-lucide="send" style="color:#fff; width:20px; height:20px;"></i> Dispatch File to Banker
            </h3>
            <button type="button" onclick="closeBankDispatchModal()" style="background:none; border:none; color:#fff; cursor:pointer; padding:4px;"><i data-lucide="x" style="width:20px; height:20px;"></i></button>
        </div>

        <div style="padding:24px; overflow-y:auto; flex:1;">
            <!-- Attachment Preview Box -->
            <div style="background:#f8fafc; border:1px dashed #cbd5e1; border-radius:8px; padding:16px; margin-bottom:24px; display:flex; align-items:center; gap:16px;">
                <div style="background:#e2e8f0; width:48px; height:48px; border-radius:8px; display:flex; align-items:center; justify-content:center; color:#475569;">
                    <i data-lucide="file-archive" style="width:24px; height:24px;"></i>
                </div>
                <div>
                    <div id="modal_attachment_name" style="font-weight:600; color:#1e293b; font-size:14px;">Bundle.zip</div>
                    <div id="modal_attachment_desc" style="font-size:12px; color:#64748b; margin-top:4px;">Auto-generated ZIP containing Applicant Profile Summary + 0 Uploaded Documents</div>
                </div>
                <div style="margin-left:auto;">
                    <span class="badge badge-success" style="font-size:11px;">Ready to Attach</span>
                </div>
            </div>

            <form id="email-banker-form" onsubmit="sendBankDispatchEmail(event)">
                <input type="hidden" name="applicant_id" id="modal_applicant_id">
                
                <div class="form-grid" style="grid-template-columns: 1fr 1fr; gap:16px;">
                    <div class="form-group">
                        <label>Bank Name</label>
                        <select name="bank_name" id="modal_bank_name" onchange="updateBankDispatchSubjectAndBody()" style="width:100%; padding:10px; border:1px solid var(--border); border-radius:6px;">
                            <option value="">-- Select Bank --</option>
                            <?php foreach($banks as $b): ?>
                                <option value="<?php echo htmlspecialchars($b); ?>"><?php echo htmlspecialchars($b); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Banker's Name <span style="font-size:11px; color:#64748b;">(Optional)</span></label>
                        <input type="text" name="banker_name" id="modal_banker_name" onkeyup="updateBankDispatchSubjectAndBody()" placeholder="e.g. Mr. Sharma" style="width:100%; padding:10px; border:1px solid var(--border); border-radius:6px;">
                    </div>

                    <div class="form-group">
                        <label class="required">To (Banker's Email)</label>
                        <input type="email" name="banker_email" required placeholder="banker@bank.com" style="width:100%; padding:10px; border:1px solid var(--border); border-radius:6px;">
                    </div>
                    <div class="form-group">
                        <label>CC <span style="font-size:11px; color:#64748b;">(Optional)</span></label>
                        <input type="email" name="cc_email" placeholder="manager@bank.com" style="width:100%; padding:10px; border:1px solid var(--border); border-radius:6px;">
                    </div>
                    
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label class="required">Subject</label>
                        <input type="text" name="subject" id="modal_subject" required style="width:100%; padding:10px; border:1px solid var(--border); border-radius:6px; font-weight:600;">
                    </div>
                    
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label class="required">Email Body</label>
                        <textarea name="body" id="modal_body" rows="7" required style="width:100%; padding:12px; border:1px solid var(--border); border-radius:6px; font-family:inherit; line-height:1.5;"></textarea>
                    </div>
                </div>
            </form>
        </div>

        <!-- Footer -->
        <div style="background:#f8fafc; padding:16px 24px; border-top:1px solid var(--border); display:flex; justify-content:flex-end; gap:12px;">
            <button type="button" class="btn btn-secondary" onclick="closeBankDispatchModal()" style="padding:10px 20px;">Cancel</button>
            <button type="submit" form="email-banker-form" class="btn btn-primary" id="send-email-btn" style="padding:10px 24px; font-weight:600;"><i data-lucide="send" style="width:16px;height:16px;"></i> Dispatch Email</button>
        </div>
    </div>
</div>

<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
<script>
    let currentApp = {};

    function openBankDispatchModal(id, cName, lId, lType, amt, docsCount) {
        currentApp = { id, cName, lId, lType, amt, docsCount };
        
        document.getElementById('modal_applicant_id').value = id;
        
        const safeName = cName.replace(/[^A-Za-z0-9_-]/g, '_');
        document.getElementById('modal_attachment_name').innerText = `Bundle_${lId}_${safeName}.zip`;
        document.getElementById('modal_attachment_desc').innerText = `Auto-generated ZIP containing Applicant Profile Summary + ${docsCount} Uploaded Documents`;
        
        if(!CKEDITOR.instances.modal_body) {
            CKEDITOR.replace('modal_body', {
                height: 200,
                versionCheck: false,
                toolbar: [
                    ['Bold', 'Italic', 'Underline', 'Strike'],
                    ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent'],
                    ['Link', 'Unlink'],
                    ['Format', 'Font', 'FontSize']
                ]
            });
        }
        
        updateBankDispatchSubjectAndBody();
        document.getElementById('email-modal').style.display = 'flex';
        if(window.lucide) lucide.createIcons();
            const realBtn = document.getElementById('real-load-more-btn');
            if (realBtn) realBtn.innerText = '↓ Load More Records ↓';
    }

    function updateBankDispatchSubjectAndBody() {
        const bank = document.getElementById('modal_bank_name').value;
        const bName = document.getElementById('modal_banker_name').value;
        const subjEl = document.getElementById('modal_subject');
        
        let subj = `New ${currentApp.lType} Application - ${currentApp.cName} [ID: ${currentApp.lId}]`;
        if (bank) subj += ` for ${bank}`;
        subjEl.value = subj;

        let salutation = bName ? `Dear ${bName},` : `Dear Sir/Madam,`;
        
        const amtFormat = new Intl.NumberFormat('en-IN').format(currentApp.amt);
        
        const newBody = `${salutation}<br><br>

Please find attached the bundled ZIP file containing the complete KYC and property documents for the loan application of <strong>${currentApp.cName}</strong>.<br><br>

<strong>Applicant Name:</strong> ${currentApp.cName}<br>
<strong>Loan Type:</strong> ${currentApp.lType}<br>
<strong>Requested Amount:</strong> INR ${amtFormat}<br>
<strong>Total Documents Attached:</strong> ${currentApp.docsCount}<br><br>

Kindly review the file and let us know the sanction details.<br><br>

Regards,<br>
BFS Financial Services Sourcing Team`;

        if (CKEDITOR.instances.modal_body) {
            CKEDITOR.instances.modal_body.setData(newBody);
        } else {
            document.getElementById('modal_body').value = newBody.replace(/<br>/g, "\n").replace(/<[^>]+>/g, '');
        }
    }

    function closeBankDispatchModal() {
        document.getElementById('email-modal').style.display = 'none';
    }

    function closeBankDispatchModalOutside(event) {
        if (event.target.id === 'email-modal') {
            closeBankDispatchModal();
        }
    }
    
    async function sendBankDispatchEmail(e) {
        e.preventDefault();
        if (CKEDITOR.instances.modal_body) {
            CKEDITOR.instances.modal_body.updateElement();
        }
        const form = document.getElementById('email-banker-form');
        const formData = new FormData(form);
        const btn = document.getElementById('send-email-btn');
        
        btn.disabled = true;
        btn.innerHTML = 'Generating ZIP & Sending...';
        
        try {
            const res = await fetch('?api=email_banker_zip', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();
            
            if (res.ok && data.success) {
                showNotification(data.message, 'success');
                closeBankDispatchModal();
                form.reset();
            } else {
                showNotification(data.error || 'Failed to send email', 'error');
            }
        } catch(err) {
            showNotification('Network error', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = 'Dispatch Email';
        }
    }
</script>

<?php require_once 'footer.php'; ?>
