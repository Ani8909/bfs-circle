<?php
require_once __DIR__ . '/../config.php';
$page_title = 'My Pre-Leads';
$page_subtitle = 'Manage assigned raw prospects and unverified entries';
require_once __DIR__ . '/header.php';
?>

<style>
/* ── Staff Pre-Leads Styles (same as admin) ── */
.pl-stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}
.pl-stat-card {
    background: #fff;
    border-radius: 12px;
    padding: 20px 24px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 1px 4px rgba(0,0,0,.04);
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.pl-stat-card .label { font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 6px; }
.pl-stat-card .value { font-size: 32px; font-weight: 800; color: #0f172a; line-height: 1; }
.pl-stat-card .icon-wrap { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; }

/* Filter Bar */
.pl-filter-bar {
    display: grid;
    grid-template-columns: 2fr 1.2fr 1.2fr 1fr 1fr auto;
    gap: 10px;
    align-items: end;
    padding: 14px 16px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    margin-bottom: 12px;
}
@media (max-width: 900px) { .pl-filter-bar { grid-template-columns: 1fr 1fr; } }
@media (max-width: 560px) { .pl-filter-bar { grid-template-columns: 1fr; } }

.pl-filter-bar input,
.pl-filter-bar select {
    padding: 8px 12px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: 13px;
    background: #fff;
    color: #334155;
    width: 100%;
    height: 38px;
    transition: border-color .2s, box-shadow .2s;
}
.pl-filter-bar input:focus,
.pl-filter-bar select:focus {
    outline: none;
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99,102,241,.1);
}
.pl-filter-bar label {
    font-size: 11px;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: .4px;
    margin-bottom: 4px;
    display: block;
}
.pl-filter-group { display: flex; flex-direction: column; }
.btn-reset-filter {
    padding: 0 16px;
    height: 38px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    background: #fff;
    color: #64748b;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    display: flex; align-items: center; gap: 6px;
    white-space: nowrap;
    transition: all .2s;
}
.btn-reset-filter:hover { background: #f1f5f9; border-color: #cbd5e1; }

/* Status Pill Bar */
.pl-status-bar { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 14px; }
.pl-status-pill {
    padding: 5px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    border: 1.5px solid;
    transition: all .18s;
    user-select: none;
}
.pl-status-pill:hover { transform: translateY(-1px); box-shadow: 0 3px 8px rgba(0,0,0,.1); }

/* Table */
.pl-table-wrap { overflow-x: auto; }
.pl-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.pl-table thead tr { background: #f8fafc; border-bottom: 2px solid #e2e8f0; }
.pl-table th { padding: 11px 14px; text-align: left; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .5px; white-space: nowrap; }
.pl-table tbody tr { border-bottom: 1px solid #f1f5f9; transition: background .15s; }
.pl-table tbody tr:hover { background: #f8fafc; }
.pl-table td { padding: 12px 14px; vertical-align: middle; }

/* Status select */
.pl-status-select {
    padding: 4px 24px 4px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    border: 1.5px solid;
    cursor: pointer;
    appearance: none;
    -webkit-appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6'%3E%3Cpath d='M0 0l5 6 5-6z' fill='%2364748b'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 8px center;
    transition: all .2s;
}

/* Pagination */
.pl-pagination {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 0 0;
    margin-top: 8px;
    border-top: 1px solid #f1f5f9;
    flex-wrap: wrap;
    gap: 10px;
}
.pl-pagination .info { font-size: 13px; color: #64748b; }
.pl-pagination .pages { display: flex; gap: 4px; align-items: center; }
.pl-page-btn {
    min-width: 34px;
    height: 34px;
    padding: 0 10px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    background: #fff;
    color: #475569;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: all .18s;
    display: flex; align-items: center; justify-content: center;
}
.pl-page-btn:hover:not([disabled]) { background: #6366f1; color: #fff; border-color: #6366f1; }
.pl-page-btn.active { background: #6366f1; color: #fff; border-color: #6366f1; font-weight: 700; }
.pl-page-btn[disabled] { opacity: .4; cursor: not-allowed; }
</style>

<div id="view-preleads" class="view-container">

    <!-- Stats -->
    <div class="pl-stats-grid">
        <div class="pl-stat-card" style="border-left: 4px solid #f59e0b;">
            <div>
                <div class="label">My Prospects</div>
                <div class="value" id="prelead-stat-total">—</div>
            </div>
            <div class="icon-wrap" style="background:#fef3c7;"><i data-lucide="inbox" style="color:#f59e0b;width:22px;height:22px;"></i></div>
        </div>
        <div class="pl-stat-card" style="border-left: 4px solid #10b981;">
            <div>
                <div class="label">Interested</div>
                <div class="value" id="prelead-stat-interested">—</div>
            </div>
            <div class="icon-wrap" style="background:#d1fae5;"><i data-lucide="thumbs-up" style="color:#10b981;width:22px;height:22px;"></i></div>
        </div>
        <div class="pl-stat-card" style="border-left: 4px solid #ef4444;">
            <div>
                <div class="label">Junk</div>
                <div class="value" id="prelead-stat-junk">—</div>
            </div>
            <div class="icon-wrap" style="background:#fee2e2;"><i data-lucide="trash-2" style="color:#ef4444;width:22px;height:22px;"></i></div>
        </div>
    </div>

    <!-- Main Card -->
    <div class="card">

        <!-- Toolbar -->
        <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px; margin-bottom:16px;">
            <h2 style="font-size:16px; font-weight:700; color:#0f172a; display:flex; align-items:center; gap:8px; margin:0;">
                <i data-lucide="database" style="width:18px;height:18px;"></i> My Pre-Leads List
            </h2>
            <div style="display:flex; align-items:center; gap:8px;">
                <button class="btn btn-primary" onclick="bulkPromote()" style="padding:6px 14px; font-size:13px; background:#10b981; border:none; display:flex; align-items:center; gap:6px;">
                    <i data-lucide="rocket" style="width:14px;height:14px;"></i> Promote Selected
                </button>
                <button class="btn btn-secondary" onclick="printPreLeads()" title="Print" style="padding:6px 10px; height:36px; display:flex; align-items:center; gap:5px; font-size:13px;">
                    <i data-lucide="printer" style="width:14px;height:14px;"></i> Print
                </button>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="pl-filter-bar">
            <div class="pl-filter-group">
                <label>Search</label>
                <input type="text" id="prelead-search" placeholder="Name, mobile…" oninput="loadPreLeads(1)">
            </div>
            <div class="pl-filter-group">
                <label>Status</label>
                <select id="prelead-filter-status" onchange="loadPreLeads(1)">
                    <option value="">All Statuses</option>
                    <option value="Not Contacted">Not Contacted</option>
                    <option value="Interested">Interested</option>
                    <option value="Junk">Junk</option>
                </select>
            </div>
            <div class="pl-filter-group">
                <label>Source</label>
                <select id="prelead-filter-source" onchange="loadPreLeads(1)">
                    <option value="">All Sources</option>
                    <option value="Cold Call">Cold Call</option>
                    <option value="Website">Website</option>
                    <option value="Email Campaign">Email Campaign</option>
                    <option value="Referral">Referral</option>
                    <option value="LinkedIn">LinkedIn</option>
                    <option value="Trade Show">Trade Show</option>
                    <option value="Walk-in">Walk-in</option>
                    <option value="Justdial">Justdial</option>
                    <option value="Unknown">Unknown</option>
                </select>
            </div>
            <div class="pl-filter-group">
                <label>From Date</label>
                <input type="date" id="prelead-filter-date-start" onchange="loadPreLeads(1)">
            </div>
            <div class="pl-filter-group">
                <label>To Date</label>
                <input type="date" id="prelead-filter-date-end" onchange="loadPreLeads(1)">
            </div>
            <button class="btn-reset-filter" onclick="resetPreLeadFilters()">
                <i data-lucide="rotate-ccw" style="width:13px;height:13px;"></i> Reset
            </button>
        </div>

        <!-- Status Pill Bar -->
        <div class="pl-status-bar" id="prelead-status-bar"></div>

        <!-- Table -->
        <div class="pl-table-wrap">
            <table class="pl-table" id="preleads-table">
                <thead>
                    <tr>
                        <th style="width:36px;"><input type="checkbox" id="selectAllPreLeads" onclick="toggleSelectAll()"></th>
                        <th>Name / Company</th>
                        <th>Mobile</th>
                        <th>Source</th>
                        <th>Status</th>
                        <th>Added On</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pl-pagination" id="preleads-pagination"></div>
    </div>
</div>

<script>
    let plCurrentPage = 1;
    let plTotalPages  = 1;

    const PL_STATUS_CFG = {
        'Not Contacted': { bg:'#f1f5f9', color:'#475569', border:'#cbd5e1' },
        'Interested':    { bg:'#dcfce7', color:'#16a34a', border:'#86efac' },
        'Junk':          { bg:'#fee2e2', color:'#dc2626', border:'#fca5a5' }
    };

    function toggleSelectAll() {
        const cb = document.getElementById('selectAllPreLeads');
        document.querySelectorAll('.prelead-checkbox').forEach(c => c.checked = cb.checked);
    }

    async function bulkPromote() {
        const ids = Array.from(document.querySelectorAll('.prelead-checkbox:checked')).map(c => c.value);
        if (!ids.length) { showNotification('Select at least one record.','error'); return; }
        if (!confirm(`Promote ${ids.length} pre-lead(s) to Lead Management?`)) return;
        const fd = new FormData();
        fd.append('api','bulk_promote_preleads'); fd.append('ids', JSON.stringify(ids));
        try {
            const r = await fetch('?api=bulk_promote_preleads',{method:'POST',body:fd});
            const d = await r.json();
            if (d.success) { showNotification(d.message,'success'); document.getElementById('selectAllPreLeads').checked=false; loadPreLeads(plCurrentPage); }
            else showNotification(d.error||'Promote failed','error');
        } catch(e) { showNotification('Error during bulk promote','error'); }
    }

    async function loadPreLeads(page = 1) {
        plCurrentPage = page;
        const tbody = document.querySelector('#preleads-table tbody');

        // Skeleton
        if (tbody) tbody.innerHTML = Array(5).fill(0).map(() => `
            <tr>
                <td style="padding:14px;"><div class="skeleton skeleton-text" style="width:18px;height:18px;margin:0;"></div></td>
                <td style="padding:14px;"><div class="skeleton skeleton-text medium"></div><div class="skeleton skeleton-text short" style="margin-top:6px;"></div></td>
                <td style="padding:14px;"><div class="skeleton skeleton-text medium"></div></td>
                <td style="padding:14px;"><div class="skeleton skeleton-text" style="width:70px;height:22px;border-radius:20px;"></div></td>
                <td style="padding:14px;"><div class="skeleton skeleton-text" style="width:90px;height:26px;border-radius:20px;"></div></td>
                <td style="padding:14px;"><div class="skeleton skeleton-text short"></div></td>
                <td style="padding:14px;"><div class="skeleton skeleton-text" style="width:80px;height:28px;border-radius:6px;"></div></td>
            </tr>
        `).join('');

        const search    = document.getElementById('prelead-search')?.value || '';
        const status    = document.getElementById('prelead-filter-status')?.value || '';
        const source    = document.getElementById('prelead-filter-source')?.value || '';
        const dateStart = document.getElementById('prelead-filter-date-start')?.value || '';
        const dateEnd   = document.getElementById('prelead-filter-date-end')?.value || '';

        const params = new URLSearchParams({
            api: 'get_preleads', search, status, source,
            date_start: dateStart, date_end: dateEnd,
            page, limit: 20
        });

        try {
            const res  = await fetch('?' + params.toString());
            const data = await res.json();
            const preleads   = data.preleads   || [];
            const stats      = data.stats      || {};
            const pagination = data.pagination || {};
            plTotalPages = pagination.total_pages || 1;
            window.allPreLeadsData = preleads;

            // Stats
            document.getElementById('prelead-stat-total').innerText      = stats.total      ?? '—';
            document.getElementById('prelead-stat-interested').innerText  = stats.interested ?? '—';
            document.getElementById('prelead-stat-junk').innerText        = stats.junk       ?? '—';

            // Status Quick-Filter Pills
            const statusBar = document.getElementById('prelead-status-bar');
            if (statusBar) {
                const counts = {'Not Contacted':0,'Interested':0,'Junk':0};
                preleads.forEach(p => { if (counts[p.status] !== undefined) counts[p.status]++; });
                const activeStatus = status;
                statusBar.innerHTML = [
                    `<div class="pl-status-pill" onclick="document.getElementById('prelead-filter-status').value=''; loadPreLeads(1);"
                        style="border-color:${activeStatus===''?'#6366f1':'#e2e8f0'}; background:${activeStatus===''?'#ede9fe':'#fff'}; color:${activeStatus===''?'#6366f1':'#64748b'}">
                        All (${stats.total||0})</div>`,
                    ...Object.entries(PL_STATUS_CFG).map(([s,c]) =>
                        `<div class="pl-status-pill" onclick="document.getElementById('prelead-filter-status').value='${s}'; loadPreLeads(1);"
                            style="border-color:${activeStatus===s?c.color:c.border}; background:${activeStatus===s?c.bg:'#fff'}; color:${c.color}">
                            ${s} (${counts[s]})</div>`
                    )
                ].join('');
            }

            // Table
            if (!tbody) return;
            if (preleads.length === 0) {
                tbody.innerHTML = `<tr><td colspan="7" style="padding:40px; text-align:center; color:#94a3b8; font-size:14px;">
                    <i data-lucide="search-x" style="width:32px;height:32px;margin-bottom:10px;display:block;margin-left:auto;margin-right:auto;"></i>
                    No pre-leads match your filters.</td></tr>`;
                lucide.createIcons();
                renderPagination(pagination);
                return;
            }

            tbody.innerHTML = '';
            preleads.forEach(p => {
                const sc = PL_STATUS_CFG[p.status] || PL_STATUS_CFG['Not Contacted'];
                const addedDate = p.created_at ? new Date(p.created_at).toLocaleDateString('en-IN',{day:'2-digit',month:'short',year:'numeric'}) : '—';

                // Time ago
                let timeAgo = '';
                if (p.created_at) {
                    const diff = Math.floor((Date.now() - new Date(p.created_at)) / 1000);
                    if (diff < 60) timeAgo = diff + 's ago';
                    else if (diff < 3600) timeAgo = Math.floor(diff/60) + 'm ago';
                    else if (diff < 86400) timeAgo = Math.floor(diff/3600) + 'h ago';
                    else timeAgo = Math.floor(diff/86400) + 'd ago';
                }

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td><input type="checkbox" class="prelead-checkbox" value="${p.id}"></td>
                    <td>
                        <div style="font-weight:600; color:#0f172a; font-size:13px;">${p.name}</div>
                        <div style="font-size:11px; color:#64748b; margin-top:2px;">${p.company_name||p.location||'—'}</div>
                        ${p.notes ? `<div style="font-size:11px; color:#94a3b8; margin-top:2px; font-style:italic;">${p.notes.substring(0,50)}${p.notes.length>50?'…':''}</div>` : ''}
                    </td>
                    <td>
                        <div style="font-weight:500; color:#334155;">${p.mobile}</div>
                        <div style="font-size:11px; color:#94a3b8;">${p.email||''}</div>
                    </td>
                    <td><span style="display:inline-block; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; background:#f1f5f9; color:#475569;">${p.source}</span></td>
                    <td>
                        <select onchange="updatePreLeadStatus(${p.id}, this.value)" class="pl-status-select"
                            style="border-color:${sc.border}; background:${sc.bg}; color:${sc.color};">
                            <option value="Not Contacted" ${p.status==='Not Contacted'?'selected':''}>Not Contacted</option>
                            <option value="Interested" ${p.status==='Interested'?'selected':''}>Interested</option>
                            <option value="Junk" ${p.status==='Junk'?'selected':''}>Junk</option>
                        </select>
                    </td>
                    <td>
                        <div style="font-size:12px; color:#475569;">${addedDate}</div>
                        <div style="font-size:11px; color:#94a3b8;">${timeAgo}</div>
                    </td>
                    <td>
                        <div style="display:flex; gap:5px; align-items:center;">
                            <button class="btn btn-secondary" onclick="openReminderModal('Pre-Lead', ${p.id})" style="padding:4px 8px; height:30px;" title="Set Reminder">⏰</button>
                            <button class="btn btn-primary" onclick="promotePreLead(${p.id})" style="padding:4px 10px; height:30px; font-size:12px; display:flex; align-items:center; gap:4px;">
                                <i data-lucide="rocket" style="width:12px;height:12px;"></i> Promote
                            </button>
                        </div>
                    </td>
                `;
                tbody.appendChild(tr);
            });

            renderPagination(pagination);
            lucide.createIcons();
        } catch(e) {
            console.error(e);
            if(tbody) tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;padding:30px;color:#ef4444;">Error loading data.</td></tr>`;
        }
    }

    function renderPagination(p) {
        const container = document.getElementById('preleads-pagination');
        if (!container) return;
        if (!p || p.total_pages <= 1) {
            container.innerHTML = `<div class="info">Showing ${p?.total_records||0} records</div>`;
            return;
        }
        const total = p.total_pages, cur = p.current_page, tot = p.total_records;
        let pagesHtml = '';
        pagesHtml += `<button class="pl-page-btn" onclick="loadPreLeads(${cur-1})" ${cur<=1?'disabled':''}>‹ Prev</button>`;
        const start = Math.max(1, cur-2), end = Math.min(total, cur+2);
        if (start > 1) { pagesHtml += `<button class="pl-page-btn" onclick="loadPreLeads(1)">1</button>`; if (start > 2) pagesHtml += `<span style="padding:0 4px;color:#94a3b8;">…</span>`; }
        for (let i = start; i <= end; i++) {
            pagesHtml += `<button class="pl-page-btn ${i===cur?'active':''}" onclick="loadPreLeads(${i})">${i}</button>`;
        }
        if (end < total) { if (end < total-1) pagesHtml += `<span style="padding:0 4px;color:#94a3b8;">…</span>`; pagesHtml += `<button class="pl-page-btn" onclick="loadPreLeads(${total})">${total}</button>`; }
        pagesHtml += `<button class="pl-page-btn" onclick="loadPreLeads(${cur+1})" ${cur>=total?'disabled':''}>Next ›</button>`;

        container.innerHTML = `
            <div class="info">Page <strong>${cur}</strong> of <strong>${total}</strong> &nbsp;·&nbsp; <strong>${tot}</strong> total records</div>
            <div class="pages">${pagesHtml}</div>
        `;
    }

    function resetPreLeadFilters() {
        ['prelead-search','prelead-filter-date-start','prelead-filter-date-end'].forEach(id => { const el=document.getElementById(id); if(el) el.value=''; });
        ['prelead-filter-status','prelead-filter-source'].forEach(id => { const el=document.getElementById(id); if(el) el.value=''; });
        loadPreLeads(1);
    }

    async function promotePreLead(id) {
        if(!confirm('Promote this prospect to main Leads CRM?')) return;
        const fd = new FormData(); fd.append('id',id);
        const r = await fetch('?api=promote_prelead',{method:'POST',body:fd});
        const d = await r.json();
        if(d.success) { showNotification('🚀 '+d.message,'success'); loadPreLeads(plCurrentPage); }
        else showNotification(d.error,'error');
    }

    async function updatePreLeadStatus(id, status) {
        showConfirm(async () => {
            const fd = new FormData(); fd.append('id',id); fd.append('status',status);
            await fetch('?api=update_prelead_status',{method:'POST',body:fd});
            loadPreLeads(plCurrentPage);
        }, 'Confirm Status Change', `Change status to "${status}"?`, 'Yes, Update');
    }

    function printPreLeads() {
        const data = window.allPreLeadsData || [];
        if (!data.length) { alert('No data to print.'); return; }
        const pw = window.open('','_blank');
        let rows = data.map(p => `<tr><td>${p.name||''}</td><td>${p.company_name||p.location||''}</td><td>${p.mobile||''}</td><td>${p.source||''}</td><td>${p.status||''}</td><td>${p.created_at?new Date(p.created_at).toLocaleDateString():''}</td><td>${p.notes||''}</td></tr>`).join('');
        pw.document.write(`<html><head><title>Pre-Leads</title><style>body{font-family:Arial;padding:20px}table{width:100%;border-collapse:collapse;font-size:12px}th,td{border:1px solid #ddd;padding:7px;text-align:left}th{background:#f4f4f4}@media print{@page{size:landscape}}</style></head><body><h2>Pre-Leads Report</h2><table><thead><tr><th>Name</th><th>Company</th><th>Mobile</th><th>Source</th><th>Status</th><th>Added</th><th>Notes</th></tr></thead><tbody>${rows}</tbody></table><script>window.onload=()=>{window.print();window.close()}<\/script></body></html>`);
        pw.document.close();
    }

    document.addEventListener('DOMContentLoaded', () => { loadPreLeads(1); });
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
