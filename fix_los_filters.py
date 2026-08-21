import re

file_path = r'c:\Users\pc\Downloads\client mgmt2\search_track.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Replace the old filters drawer using regex
pattern = re.compile(r'<!-- Filters Drawer -->.*?<!-- CRM Layout Grid -->', re.DOTALL)

new_filter_panel = """<!-- ===== QUICK FILTER CHIPS ===== -->
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
    </div>

    <!-- CRM Layout Grid -->"""

content = pattern.sub(new_filter_panel, content)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Replacement successful")
