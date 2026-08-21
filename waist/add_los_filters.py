import re

# ===================================================
# STEP 1: Upgrade the search_track.php filter drawer
# ===================================================
file_path = r'c:\Users\pc\Downloads\client mgmt2\search_track.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# 1a. Read the staff list for the PHP staff filter
# Add CSS for the new advanced filter bar
old_style_end = '</style>'
new_filter_css = """
/* ===== ADVANCED FILTER BAR ===== */
.filter-panel { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; margin: 0 0 16px 0; padding: 0; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,0.04); }
.filter-panel-header { display: flex; align-items: center; justify-content: space-between; padding: 14px 20px; background: #0f172a; cursor: pointer; }
.filter-panel-header h4 { font-size: 13px; font-weight: 800; color: #fff; margin: 0; display: flex; align-items: center; gap: 8px; }
.filter-panel-header .filter-count { background: #f97316; color: #fff; border-radius: 20px; padding: 2px 8px; font-size: 11px; font-weight: 800; }
.filter-panel-body { padding: 16px 20px 20px; display: none; }
.filter-panel-body.open { display: block; }

.filter-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 14px; }
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
"""
content = content.replace('</style>', new_filter_css + '\n</style>')

# 1b. Replace the old filters drawer with the new advanced panel
old_filter_drawer = """    <!-- Filters Drawer -->
    <div class="filters-drawer" id="crm-filters-drawer">
        <div class="form-group">
            <label>By Phase Status</label>
            <select id="filter-status" onchange="triggerSearch()">
                <option value="">All Phases</option>
                <option value="Phase 1">Phase 1 (Basic &amp; KYC)</option>
                <option value="Phase 2">Phase 2 (Documents)</option>
                <option value="Phase 3">Phase 3 (Bank Processing)</option>
                <option value="Phase 4">Phase 4 (Disbursements)</option>
                <option value="Completed">Completed (Sanctioned)</option>
                <option value="Rejected">Rejected</option>
            </select>
        </div>
        <div class="form-group">
            <label>By Loan Type</label>
            <select id="filter-type" onchange="triggerSearch()">
                <option value="">All Types</option>
                <option value="Home Loan">Home Loan</option>
                <option value="Personal Loan">Personal Loan</option>
                <option value="Vehicle Loan">Vehicle Loan</option>
                <option value="Business Loan">Business Loan</option>
                <option value="Gold Loan">Gold Loan</option>
                <option value="Education Loan">Education Loan</option>
            </select>
        </div>
    </div>"""

new_filter_panel = """    <!-- ===== QUICK FILTER CHIPS ===== -->
    <div class="quick-filters" id="quickFilters">
        <button class="qf-chip active" onclick="setQuickFilter('all', this)">All Files</button>
        <button class="qf-chip orange" onclick="setQuickFilter('Phase 1', this)">Phase 1 — KYC</button>
        <button class="qf-chip orange" onclick="setQuickFilter('Phase 2', this)">Phase 2 — Docs</button>
        <button class="qf-chip orange" onclick="setQuickFilter('Phase 3', this)">Phase 3 — Bank</button>
        <button class="qf-chip orange" onclick="setQuickFilter('Phase 4', this)">Phase 4 — Disburse</button>
        <button class="qf-chip" onclick="setQuickFilter('Completed', this)">✅ Completed</button>
        <button class="qf-chip" onclick="setQuickFilter('Rejected', this)">❌ Rejected</button>
    </div>

    <!-- ===== ADVANCED FILTER PANEL ===== -->
    <div class="filter-panel" id="advFilterPanel">
        <div class="filter-panel-header" onclick="toggleAdvFilters()">
            <h4>⚙️ Advanced Filters &amp; Sort  <span class="filter-count" id="filterCountBadge" style="display:none;">0 active</span></h4>
            <span style="color:#f97316; font-size:13px; font-weight:700;" id="filterToggleArrow">▼ Expand</span>
        </div>
        <div class="filter-panel-body" id="advFilterBody">
            <div class="filter-grid">

                <div class="filter-item">
                    <label>Phase Status</label>
                    <select id="filter-status" class="filter-select" onchange="triggerSearch()">
                        <option value="">All Phases</option>
                        <option value="Phase 1">Phase 1 — KYC</option>
                        <option value="Phase 2">Phase 2 — Documents</option>
                        <option value="Phase 3">Phase 3 — Bank Processing</option>
                        <option value="Phase 4">Phase 4 — Disbursements</option>
                        <option value="Completed">Completed / Sanctioned</option>
                        <option value="Rejected">Rejected by Bank</option>
                    </select>
                </div>

                <div class="filter-item">
                    <label>Loan Type</label>
                    <select id="filter-type" class="filter-select" onchange="triggerSearch()">
                        <option value="">All Loan Types</option>
                        <option value="Home Loan">🏠 Home Loan</option>
                        <option value="Personal Loan">💼 Personal Loan</option>
                        <option value="Vehicle Loan">🚗 Vehicle Loan</option>
                        <option value="Business Loan">🏭 Business Loan</option>
                        <option value="Gold Loan">🥇 Gold Loan</option>
                        <option value="Education Loan">📚 Education Loan</option>
                        <option value="LAP">🏗️ LAP</option>
                    </select>
                </div>

                <div class="filter-item">
                    <label>Assigned Staff</label>
                    <select id="filter-staff" class="filter-select" onchange="triggerSearch()">
                        <option value="">All Staff</option>
                        <?php foreach($staff_members as $sm): ?>
                        <option value="<?= htmlspecialchars($sm) ?>"><?= htmlspecialchars($sm) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-item">
                    <label>Bank Assigned</label>
                    <select id="filter-bank" class="filter-select" onchange="triggerSearch()">
                        <option value="">All Banks</option>
                        <?php foreach($banks as $bk): ?>
                        <option value="<?= htmlspecialchars($bk) ?>"><?= htmlspecialchars($bk) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-item">
                    <label>TAT Aging (Days)</label>
                    <select id="filter-aging" class="filter-select" onchange="triggerSearch()">
                        <option value="">Any Duration</option>
                        <option value="0-7">0–7 Days (Fresh)</option>
                        <option value="7-30">7–30 Days</option>
                        <option value="30-90">30–90 Days (Slow)</option>
                        <option value="90+">90+ Days (Critical)</option>
                    </select>
                </div>

                <div class="filter-item">
                    <label>Sort By</label>
                    <select id="filter-sort" class="filter-select" onchange="triggerSearch()">
                        <option value="newest">Newest First</option>
                        <option value="oldest">Oldest First</option>
                        <option value="amount_high">Highest Loan Amount</option>
                        <option value="amount_low">Lowest Loan Amount</option>
                        <option value="name_az">Name A–Z</option>
                    </select>
                </div>

                <div class="filter-item">
                    <label>Application Date — From</label>
                    <input type="date" id="filter-date-from" class="filter-input" onchange="triggerSearch()">
                </div>

                <div class="filter-item">
                    <label>Application Date — To</label>
                    <input type="date" id="filter-date-to" class="filter-input" onchange="triggerSearch()">
                </div>

                <div class="filter-item">
                    <label>Loan Amount — Min (₹)</label>
                    <input type="number" id="filter-amt-min" class="filter-input" placeholder="e.g. 100000" onchange="triggerSearch()">
                </div>

                <div class="filter-item">
                    <label>Loan Amount — Max (₹)</label>
                    <input type="number" id="filter-amt-max" class="filter-input" placeholder="e.g. 5000000" onchange="triggerSearch()">
                </div>

            </div>

            <div class="filter-actions">
                <div id="activeFilterChips" class="filter-active-chips"></div>
                <div style="display:flex; gap:8px;">
                    <button class="btn-reset-filter" onclick="resetAllFilters()">↺ Reset All</button>
                    <button class="btn-apply-filter" onclick="triggerSearch()">Apply Filters</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Result count -->
    <div class="result-meta" id="resultMeta" style="display:none;">
        <span id="resultCountText">Showing results</span>
        <span class="result-count-badge" id="resultCountBadge">0</span>
    </div>"""

content = content.replace(old_filter_drawer, new_filter_panel)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Advanced filter panel injected into search_track.php")
