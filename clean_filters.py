import re

file_path = r'c:\Users\pc\Downloads\client mgmt2\search_track.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

new_filter_panel = """<!-- Search bar -->
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

    <!-- CRM Layout Grid -->"""

# Replace the entire block
content = re.sub(r'<!-- Search bar -->.*?<!-- CRM Layout Grid -->', new_filter_panel, content, flags=re.DOTALL)

# Fix Javascript toggleAdvFilters to use correct colors
js_old = """    function toggleAdvFilters() {
        const body = document.getElementById('advFilterBody');
        const btn = document.getElementById('advFilterToggleBtn');
        const txt = document.getElementById('advFilterToggleText');
        body.classList.toggle('open');
        
        if (body.classList.contains('open')) {
            btn.style.background = 'var(--navy)';
            btn.style.color = '#fff';
            btn.style.borderColor = 'var(--navy)';
            txt.innerText = 'Close Filters';
        } else {
            btn.style.background = '#fff';
            btn.style.color = '#334155';
            btn.style.borderColor = '#cbd5e1';
            txt.innerText = 'Advanced Filters';
        }
    }"""

js_new = """    function toggleAdvFilters() {
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
    }"""
content = content.replace(js_old, js_new)

# Fix chip color in updateFilterChips
chip_old = """        container.innerHTML = chips.map(c => `<span style="background:rgba(249,115,22,0.1); color:#c2410c; padding:4px 10px; border-radius:12px; font-size:11px; font-weight:600;">${c}</span>`).join('');"""
chip_new = """        container.innerHTML = chips.map(c => `<span style="background:#f8fafc; border:1px solid #cbd5e1; color:#475569; padding:2px 8px; border-radius:4px; font-size:10px; font-weight:600;">${c}</span>`).join('');"""
content = content.replace(chip_old, chip_new)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Professional minimal filters injected.")
