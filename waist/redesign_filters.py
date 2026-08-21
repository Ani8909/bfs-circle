import re

file_path = r'c:\Users\pc\Downloads\client mgmt2\search_track.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Clean up the messy multiple CSS injections
content = re.sub(r'<style>@keyframes spin-slow.*?/\* ===== ADVANCED FILTER BAR ===== \*/', '/* ===== ADVANCED FILTER BAR ===== */', content, flags=re.DOTALL)
content = re.sub(r'/\* ===== ADVANCED FILTER BAR ===== \*/.*?\.filter-grid \{ display: grid; grid-template-columns: repeat\(auto-fill, minmax\(200px, 1fr\)\); gap: 14px; \}', '', content, flags=re.DOTALL)

# 2. Define the new CSS & HTML replacement block
new_ui_block = """<!-- Search bar -->
    <div class="crm-search-bar" style="margin-bottom: 16px;">
        <div class="search-input-wrapper">
            <i data-lucide="search" class="search-icon"></i>
            <input type="text" id="search-query" placeholder="Search by Applicant Name / Loan ID / Mobile / PAN..." oninput="triggerSearch()">
        </div>
        <button class="filters-toggle-btn" onclick="toggleAdvFilters()" id="advFilterToggleBtn" style="background:#fff; border:1px solid #cbd5e1; color:#334155;">
            <i data-lucide="sliders-horizontal" style="margin-right: 6px;"></i> <span id="advFilterToggleText">Advanced Filters</span>
            <span class="filter-count" id="filterCountBadge" style="display:none; background:#f97316; color:#fff; border-radius:20px; padding:2px 8px; font-size:11px; margin-left:8px;">0</span>
        </button>
    </div>

    <!-- ===== QUICK FILTER CHIPS ===== -->
    <div class="quick-filters" id="quickFilters" style="display:flex; gap:10px; flex-wrap:wrap; margin-bottom:16px;">
        <button class="qf-chip active" onclick="setQuickFilter('all', this)" style="padding:6px 16px; border-radius:24px; border:1px solid #cbd5e1; background:#fff; font-size:12px; font-weight:600; cursor:pointer; color:#475569; transition:0.2s;">All Files</button>
        <button class="qf-chip" onclick="setQuickFilter('Phase 1', this)" style="padding:6px 16px; border-radius:24px; border:1px solid #cbd5e1; background:#fff; font-size:12px; font-weight:600; cursor:pointer; color:#475569; transition:0.2s;">Phase 1 — KYC</button>
        <button class="qf-chip" onclick="setQuickFilter('Phase 2', this)" style="padding:6px 16px; border-radius:24px; border:1px solid #cbd5e1; background:#fff; font-size:12px; font-weight:600; cursor:pointer; color:#475569; transition:0.2s;">Phase 2 — Docs</button>
        <button class="qf-chip" onclick="setQuickFilter('Phase 3', this)" style="padding:6px 16px; border-radius:24px; border:1px solid #cbd5e1; background:#fff; font-size:12px; font-weight:600; cursor:pointer; color:#475569; transition:0.2s;">Phase 3 — Bank</button>
        <button class="qf-chip" onclick="setQuickFilter('Phase 4', this)" style="padding:6px 16px; border-radius:24px; border:1px solid #cbd5e1; background:#fff; font-size:12px; font-weight:600; cursor:pointer; color:#475569; transition:0.2s;">Phase 4 — Disburse</button>
        <button class="qf-chip" onclick="setQuickFilter('Completed', this)" style="padding:6px 16px; border-radius:24px; border:1px solid #bbf7d0; background:#f0fdf4; font-size:12px; font-weight:600; cursor:pointer; color:#166534; transition:0.2s;">✅ Completed</button>
        <button class="qf-chip" onclick="setQuickFilter('Rejected', this)" style="padding:6px 16px; border-radius:24px; border:1px solid #fecaca; background:#fef2f2; font-size:12px; font-weight:600; cursor:pointer; color:#991b1b; transition:0.2s;">❌ Rejected</button>
    </div>

    <style>
        .qf-chip:hover { border-color:var(--navy) !important; color:var(--navy) !important; }
        .qf-chip.active { background:var(--navy) !important; color:#fff !important; border-color:var(--navy) !important; }
        
        .adv-filter-panel { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; margin-bottom: 20px; display: none; }
        .adv-filter-panel.open { display: block; animation: slideDown 0.3s ease-out; }
        
        .filter-section-title { font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 12px; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px; }
        .filter-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 24px; }
        .filter-row.half { grid-template-columns: repeat(2, 1fr); }
        
        .f-group { display: flex; flex-direction: column; gap: 6px; }
        .f-group label { font-size: 12px; font-weight: 600; color: #334155; }
        .f-input { padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; color: #0f172a; outline: none; background: #fff; transition: 0.2s; }
        .f-input:focus { border-color: var(--orange); box-shadow: 0 0 0 3px rgba(249,115,22,0.1); }
        
        @keyframes slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
    </style>

    <!-- ===== ADVANCED FILTER PANEL ===== -->
    <div class="adv-filter-panel" id="advFilterBody">
        
        <div class="filter-section-title">Categorization</div>
        <div class="filter-row">
            <div class="f-group">
                <label>Phase Status</label>
                <select id="filter-status" class="f-input" onchange="triggerSearch()">
                    <option value="">All Phases</option>
                    <option value="Phase 1">Phase 1 — KYC</option>
                    <option value="Phase 2">Phase 2 — Documents</option>
                    <option value="Phase 3">Phase 3 — Bank Processing</option>
                    <option value="Phase 4">Phase 4 — Disbursements</option>
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
        </div>

        <div class="filter-row half">
            <div>
                <div class="filter-section-title">Dates & Aging</div>
                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px;">
                    <div class="f-group">
                        <label>From Date</label>
                        <input type="date" id="filter-date-from" class="f-input" onchange="triggerSearch()">
                    </div>
                    <div class="f-group">
                        <label>To Date</label>
                        <input type="date" id="filter-date-to" class="f-input" onchange="triggerSearch()">
                    </div>
                    <div class="f-group">
                        <label>TAT Aging</label>
                        <select id="filter-aging" class="f-input" onchange="triggerSearch()">
                            <option value="">Any Duration</option>
                            <option value="0-7">0–7 Days (Fresh)</option>
                            <option value="7-30">7–30 Days</option>
                            <option value="30-90">30–90 Days (Slow)</option>
                            <option value="90+">90+ Days (Critical)</option>
                        </select>
                    </div>
                </div>
            </div>

            <div>
                <div class="filter-section-title">Amount & Sorting</div>
                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px;">
                    <div class="f-group">
                        <label>Min Amt (₹)</label>
                        <input type="number" id="filter-amt-min" class="f-input" placeholder="0" onchange="triggerSearch()">
                    </div>
                    <div class="f-group">
                        <label>Max Amt (₹)</label>
                        <input type="number" id="filter-amt-max" class="f-input" placeholder="Any" onchange="triggerSearch()">
                    </div>
                    <div class="f-group">
                        <label>Sort By</label>
                        <select id="filter-sort" class="f-input" onchange="triggerSearch()">
                            <option value="newest">Newest First</option>
                            <option value="oldest">Oldest First</option>
                            <option value="amount_high">Highest Loan</option>
                            <option value="amount_low">Lowest Loan</option>
                            <option value="name_az">Name A–Z</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid #e2e8f0; padding-top:16px;">
            <div id="activeFilterChips" style="display:flex; gap:8px; flex-wrap:wrap;"></div>
            <div style="display:flex; gap:12px;">
                <button onclick="resetAllFilters()" style="background:transparent; border:1px solid #cbd5e1; color:#475569; padding:8px 16px; border-radius:6px; font-weight:600; font-size:12px; cursor:pointer;">Reset All</button>
                <button onclick="triggerSearch(); toggleAdvFilters();" style="background:var(--navy); border:none; color:#fff; padding:8px 20px; border-radius:6px; font-weight:600; font-size:12px; cursor:pointer;">Apply & Close</button>
            </div>
        </div>
    </div>

    <!-- CRM Layout Grid -->"""

# Regex substitution
content = re.sub(r'<!-- Search bar -->.*?<!-- CRM Layout Grid -->', new_ui_block, content, flags=re.DOTALL)

# Update Javascript toggle logic
content = content.replace("""    function toggleAdvFilters() {
        const body = document.getElementById('advFilterBody');
        const arrow = document.getElementById('filterToggleArrow');
        body.classList.toggle('open');
        arrow.innerText = body.classList.contains('open') ? '▲ Collapse' : '▼ Expand';
    }""", """    function toggleAdvFilters() {
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
    }""")

# Clean up chips styling in JS
content = content.replace("""        container.innerHTML = chips.map(c => `<span class="filter-chip">${c}</span>`).join('');""", """        container.innerHTML = chips.map(c => `<span style="background:rgba(249,115,22,0.1); color:#c2410c; padding:4px 10px; border-radius:12px; font-size:11px; font-weight:600;">${c}</span>`).join('');""")

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Redesigned Filter UI successfully injected.")
