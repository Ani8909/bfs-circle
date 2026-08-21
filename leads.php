<?php
require_once 'config.php';
$page_title = 'Lead Management';
$page_subtitle = ' Capture, track and convert leads into clients';
require_once 'header.php';
?>

<div id="view-leads" class="view-container">
    <!-- Stats Bar -->
    <div class="stats-grid" style="grid-template-columns: repeat(4,1fr); margin-bottom:1.5rem;">
        <div class="stat-card" style="border:1px solid #e2e8f0; box-shadow:0 1px 3px rgba(0,0,0,0.02); border-radius:12px; background:#fff;">
            <div class="stat-card-header"><span class="stat-label" style="color:#64748b; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; font-size:11px;">Total Leads</span><i data-lucide="target" style="color:#0f172a;width:18px;height:18px;"></i></div>
            <div class="stat-value" id="lead-stat-total" style="color:#0f172a;">—</div>
        </div>
        <div class="stat-card" style="border:1px solid #e2e8f0; box-shadow:0 1px 3px rgba(0,0,0,0.02); border-radius:12px; background:#fff;">
            <div class="stat-card-header"><span class="stat-label" style="color:#64748b; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; font-size:11px;">Today's Leads</span><i data-lucide="calendar-plus" style="color:#0f172a;width:18px;height:18px;"></i></div>
            <div class="stat-value" id="lead-stat-today" style="color:#0f172a;">—</div>
        </div>
        <div class="stat-card" style="border:1px solid #e2e8f0; box-shadow:0 1px 3px rgba(0,0,0,0.02); border-radius:12px; background:#fff;">
            <div class="stat-card-header"><span class="stat-label" style="color:#e11d48; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; font-size:11px;">Hot Leads</span><i data-lucide="flame" style="color:#e11d48;width:18px;height:18px;"></i></div>
            <div class="stat-value" id="lead-stat-hot" style="color:#0f172a;">—</div>
        </div>
        <div class="stat-card" style="border:1px solid #e2e8f0; box-shadow:0 1px 3px rgba(0,0,0,0.02); border-radius:12px; background:#fff;">
            <div class="stat-card-header"><span class="stat-label" style="color:#64748b; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; font-size:11px;">In Progress</span><i data-lucide="clock" style="color:#0f172a;width:18px;height:18px;"></i></div>
            <div class="stat-value" id="lead-stat-progress" style="color:#0f172a;">—</div>
        </div>
    </div>

    <div style="display:grid; grid-template-columns:1fr; gap:1.5rem; align-items:start;">
        <!-- LEADS TABLE -->
        <div class="card">
            <div class="card-title-bar" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px; margin-bottom: 15px; border-bottom: none; padding-bottom: 0;">
                <div style="display:flex; align-items:center; gap: 10px;">
                    <h2 style="margin:0;"> All Leads</h2>
                    <a href="add_lead.php" class="btn btn-primary" style="padding:6px 12px; font-size:13px; display:flex; align-items:center; gap:5px;"><i data-lucide="plus" style="width:16px; height:16px;"></i> Add New Lead</a>
                    <button class="btn btn-secondary" onclick="openLeadsBulkUploadModal()" title="Bulk Upload" style="padding:6px 12px; font-size:13px; display:flex; align-items:center; gap:5px;"><i data-lucide="upload" style="width:14px; height:14px;"></i> Bulk Upload</button>
                    <button class="btn btn-secondary" onclick="printLeads()" title="Print Leads Data" style="padding:6px 8px; font-size:12px; display:flex; align-items:center; justify-content:center; height:32px; background:white; border:1px solid #cbd5e1; color:#475569;"><i data-lucide="printer" style="width:14px; height:14px;"></i></button>
                </div>
                <div class="admin-only-field" style="display:none; gap:6px; align-items:center; background:#f8fafc; padding:8px 12px; border-radius:8px; border:1px solid #e2e8f0; font-size:13px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                    <span style="font-weight:600; color:#334155;">Bulk Actions:</span>
                    <select id="bulk-assign-leads-staff" class="user-select" style="width:auto; padding:4px 8px; border:1px solid #cbd5e1; border-radius:4px; font-size:12px; min-width:130px;">
                        <option value="">-- Select Staff --</option>
                    </select>
                    <button class="btn btn-secondary" style="padding:5px 12px; font-size:12px; font-weight:500; border-radius:6px;" onclick="bulkAssign('leads')">Assign</button>
                    <button class="btn btn-secondary" style="padding:5px 12px; font-size:12px; font-weight:500; background:#fee2e2; color:#b91c1c; border:1px solid #fecaca; border-radius:6px;" onclick="bulkDelete('leads')">Delete</button>
                </div>
            </div>

            <!-- Advanced Pill Filter Bar -->
            <div style="display:flex; flex-wrap:nowrap; overflow-x:auto; gap:12px; margin-bottom:24px; padding:12px; background:#ffffff; border-radius:12px; border:1px solid #e2e8f0; align-items:center;">
                
                <div style="flex:0 0 260px; position:relative;">
                    <i data-lucide="search" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); width:16px; height:16px; color:#94a3b8;"></i>
                    <input type="text" id="lead-search" placeholder="Search by name, mobile..." style="width:100%; padding:8px 12px 8px 36px; border:1px solid #cbd5e1; border-radius:20px; font-size:13px; background:#fff; outline:none; transition:border-color 0.2s;" oninput="loadLeads()" onfocus="this.style.borderColor='#94a3b8'" onblur="this.style.borderColor='#cbd5e1'">
                </div>
                
                <select id="lead-filter-date" onchange="loadLeads()" style="flex:0 0 auto; width:auto; padding:8px 16px; border:1px solid #e2e8f0; border-radius:20px; font-size:12px; font-weight:600; background:#f8fafc; color:#334155; outline:none; cursor:pointer;">
                    <option value="">All Time</option>
                    <option value="today">Today</option>
                    <option value="yesterday">Yesterday</option>
                    <option value="last7">Last 7 Days</option>
                    <option value="this_month">This Month</option>
                </select>
                
                <select id="lead-filter-amount" onchange="loadLeads()" style="flex:0 0 auto; width:auto; padding:8px 16px; border:1px solid #e2e8f0; border-radius:20px; font-size:12px; font-weight:600; background:#f8fafc; color:#334155; outline:none; cursor:pointer;">
                    <option value="">All Amounts</option>
                    <option value="1000000">> ₹10 Lakh</option>
                    <option value="2500000">> ₹25 Lakh</option>
                    <option value="5000000">> ₹50 Lakh</option>
                    <option value="10000000">> ₹1 Crore</option>
                </select>
                
                <select id="lead-filter-type" onchange="loadLeads()" style="flex:0 0 auto; width:auto; padding:8px 16px; border:1px solid #e2e8f0; border-radius:20px; font-size:12px; font-weight:600; background:#f8fafc; color:#334155; outline:none; cursor:pointer;">
                    <option value="">All Loan Types</option>
                    <option value="Personal Loan">Personal Loan</option>
                    <option value="Home Loan">Home Loan</option>
                    <option value="Business Loan">Business Loan</option>
                    <option value="Auto Loan">Auto Loan</option>
                    <option value="Mortgage Loan">Mortgage Loan / LAP</option>
                </select>
                
                <select id="lead-filter-stage" onchange="loadLeads()" style="flex:0 0 auto; width:auto; padding:8px 16px; border:1px solid #e2e8f0; border-radius:20px; font-size:12px; font-weight:600; background:#f8fafc; color:#334155; outline:none; cursor:pointer;">
                    <option value="">All Stages</option>
                    <option value="New Lead">New Lead</option>
                    <option value="Scheduled">Scheduled</option>
                    <option value="Interested">Interested</option>
                    <option value="Not Answered">Not Answered</option>
                    <option value="Rejected">Rejected</option>
                </select>
                
                <select id="lead-filter-priority" onchange="loadLeads()" style="flex:0 0 auto; width:auto; padding:8px 16px; border:1px solid #e2e8f0; border-radius:20px; font-size:12px; font-weight:600; background:#f8fafc; color:#334155; outline:none; cursor:pointer;">
                    <option value="">All Priorities</option>
                    <option value="Hot">Hot</option>
                    <option value="Warm">Warm</option>
                    <option value="Cold">Cold</option>
                </select>
                
                <select id="lead-filter-staff" class="user-filter-select" onchange="loadLeads()" style="flex:0 0 auto; width:auto; padding:8px 16px; border:1px solid #e2e8f0; border-radius:20px; font-size:12px; font-weight:600; background:#f8fafc; color:#334155; outline:none; cursor:pointer;">
                    <option value="">All Staff</option>
                </select>
            </div>

            <!-- Pipeline Summary Bar -->
            <div id="lead-pipeline-bar" style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:1rem;padding:12px;background:var(--bg-main);border-radius:8px;"></div>

            <div style="overflow-x:auto;">
                <table style="width:100%;border-collapse:collapse;font-size:13px;">
                    <thead>
                        <tr style="background:#f8fafc;color:#64748b;font-size:10px;text-transform:uppercase;font-weight:700;letter-spacing:0.05em;border-bottom:1px solid #e2e8f0;">
                            <th style="padding:16px 12px;text-align:left;width:40px;"><input type="checkbox" id="selectAllLeads" onclick="toggleSelectAll('leads')"></th>
                            <th style="padding:16px 12px;text-align:left;">Customer Details</th>
                            <th style="padding:16px 12px;text-align:left;">Requirement</th>
                            <th style="padding:16px 12px;text-align:left;">Source</th>
                            <th style="padding:16px 12px;text-align:left;">Stage</th>
                            <th style="padding:16px 12px;text-align:left;">Date Added</th>
                            <th style="padding:16px 12px;text-align:left;">Assigned</th>
                            <th style="padding:16px 12px;text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="leads-table-body">
                        <tr><td colspan="8" style="padding:30px;text-align:center;color:var(--text-light);">Loading leads...</td></tr>
                    </tbody>
                </table>
            </div>
            <div id="leads-pagination" style="display:flex; justify-content:flex-end; align-items:center; gap:10px; margin-top:20px; padding:10px 15px; border-top:1px solid var(--border);"></div>
        </div>
    </div>
</div>

<script>
    const STAGE_COLORS = {
        'New Lead':      '#6366f1',
        'Contacted':     '#f59e0b',
        'Scheduled':     '#8b5cf6',
        'Interested':    '#3b82f6',
        'Not Answered':  '#94a3b8',
        'Rejected':      '#ef4444','Converted':     '#10b981'
    };

    const PRIORITY_BADGE = {
        'Hot':  '<span style="background:#fee2e2;color:#ef4444;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600;">Hot</span>',
        'Warm': '<span style="background:#fef9c3;color:#ca8a04;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600;">Warm</span>',
        'Cold': '<span style="background:#dbeafe;color:#2563eb;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600;">Cold</span>'
    };

    function toggleSelectAll(type) {
        const isChecked = document.getElementById(type === 'leads' ? 'selectAllLeads' : 'selectAllPreLeads').checked;
        document.querySelectorAll(type === 'leads' ? '.lead-checkbox' : '.prelead-checkbox').forEach(cb => cb.checked = isChecked);
    }

    async function bulkDelete(type) {
        const checkboxes = document.querySelectorAll(type === 'leads' ? '.lead-checkbox:checked' : '.prelead-checkbox:checked');
        const ids = Array.from(checkboxes).map(cb => cb.value);
        
        if (ids.length === 0) {
            showNotification("Please select at least one record to delete", "error");
            return;
        }
        
        if(!confirm(`Are you sure you want to permanently delete ${ids.length} records?`)) return;

        let fd = new FormData();
        fd.append("api", "bulk_delete");
        fd.append("type", type);
        fd.append("ids", JSON.stringify(ids));

        try {
            let res = await fetch("?api=bulk_delete", {method: "POST", body: fd});
            let json = await res.json();
            if (json.success) {
                showNotification(`Successfully deleted ${ids.length} records`, "success");
                const sa = document.getElementById(type === 'leads' ? 'selectAllLeads' : 'selectAllPreLeads');
                if(sa) sa.checked = false;
                
                if (typeof loadLeads === 'function') loadLeads();
            } else {
                showNotification(json.error || "Failed to bulk delete", "error");
            }
        } catch(e) {
            showNotification("Error during bulk delete", "error");
        }
    }

    async function bulkAssign(type) {
        const checkboxes = document.querySelectorAll(type === 'leads' ? '.lead-checkbox:checked' : '.prelead-checkbox:checked');
        const ids = Array.from(checkboxes).map(cb => cb.value);
        
        if (ids.length === 0) {
            showNotification("Please select at least one record", "error");
            return;
        }
        
        const staffDropdown = document.getElementById(type === 'leads' ? 'bulk-assign-leads-staff' : 'bulk-assign-preleads-staff');
        const staff = staffDropdown.value;
        
        if (!staff) {
            showNotification("Please select a staff member to assign to", "error");
            return;
        }

        let fd = new FormData();
        fd.append("api", "bulk_assign");
        fd.append("type", type);
        fd.append("assigned_to", staff);
        fd.append("ids", JSON.stringify(ids));

        try {
            let res = await fetch("?api=bulk_assign", {method: "POST", body: fd});
            let json = await res.json();
            if (json.success) {
                showNotification(`Successfully assigned ${ids.length} records to ${staff}`, "success");
                const sa = document.getElementById(type === 'leads' ? 'selectAllLeads' : 'selectAllPreLeads');
                if(sa) sa.checked = false;
                
                loadLeads();
            } else {
                showNotification(json.error || "Failed to bulk assign", "error");
            }
        } catch(e) {
            showNotification("Error during bulk assignment", "error");
        }
    }

    let currentLeadsPage = 1;
    async function loadLeads(page = 1) {
        currentLeadsPage = page;
        const search   = document.getElementById('lead-search')?.value || '';
        const stage    = document.getElementById('lead-filter-stage')?.value || '';
        const priority = document.getElementById('lead-filter-priority')?.value || '';
        const assigned = document.getElementById('lead-filter-staff')?.value || '';
        const source   = document.getElementById('lead-filter-source')?.value || '';
        const type     = document.getElementById('lead-filter-type')?.value || '';
        const params   = new URLSearchParams({ api: 'get_leads', search, stage, priority, assigned_to: assigned, source, loan_type: type, page: currentLeadsPage, limit: 20 });

        const tbody = document.getElementById('leads-table-body');
        if (tbody) {
            tbody.innerHTML = Array(5).fill(0).map(() => `
                <tr>
                    <td style="padding:12px;"><div class="skeleton skeleton-text" style="width:20px;height:20px;margin:0;"></div></td>
                    <td style="padding:12px;"><div class="skeleton skeleton-text medium"></div><div class="skeleton skeleton-text short"></div></td>
                    <td style="padding:12px;"><div class="skeleton skeleton-text medium"></div></td>
                    <td style="padding:12px;"><div class="skeleton skeleton-text medium"></div><div class="skeleton skeleton-text short"></div></td>
                    <td style="padding:12px;"><div class="skeleton skeleton-text medium"></div></td>
                    <td style="padding:12px;"><div class="skeleton skeleton-text" style="width:50px;height:22px;border-radius:20px;"></div></td>
                    <td style="padding:12px;"><div class="skeleton skeleton-text" style="width:70px;height:22px;border-radius:20px;"></div></td>
                    <td style="padding:12px;"><div class="skeleton skeleton-text medium"></div><div class="skeleton skeleton-text short"></div></td>
                    <td style="padding:12px;"><div class="skeleton skeleton-text medium"></div><div class="skeleton skeleton-text short"></div></td>
                    <td style="padding:12px;"><div class="skeleton skeleton-text" style="width:60px;height:26px;border-radius:6px;"></div></td>
                </tr>
            `).join('');
        }

        try {
            const res = await fetch('?' + params.toString());
            if (!res.ok) return;
            const resData = await res.json();
            
            // Check if it's the new format or old array format (fallback)
            const leads = resData.leads ? resData.leads : (Array.isArray(resData) ? resData : []);
            window.allLeadsData = leads;
            const stageCounts = resData.stage_counts || {};
            const stats = resData.stats || {};

            // Update stat cards
            const total    = stats.total !== undefined ? stats.total : leads.length;
            const hot      = stats.hot !== undefined ? stats.hot : leads.filter(l => l.priority === 'Hot' && l.stage !== 'Converted' && l.stage !== 'Lost').length;
            const won      = stats.won !== undefined ? stats.won : leads.filter(l => l.stage === 'Converted').length;
            const progress = stats.progress !== undefined ? stats.progress : leads.filter(l => l.stage !== 'Converted' && l.stage !== 'Lost').length;

            const setEl = (id, v) => { const el = document.getElementById(id); if(el) el.innerText = v; };
            setEl('lead-stat-total',    total);
            setEl('lead-stat-today',    stats.today !== undefined ? stats.today : 0);
            setEl('lead-stat-hot',      hot);
            setEl('lead-stat-won',      won);
            setEl('lead-stat-progress', progress);

            // Pipeline Bar counts
            if (!resData.stage_counts && leads.length > 0) {
                // Fallback if API hasn't been updated yet
                leads.forEach(l => { stageCounts[l.stage] = (stageCounts[l.stage] || 0) + 1; });
            }
            const pipelineBar = document.getElementById('lead-pipeline-bar');
            if (pipelineBar) {
                const stages = ['New Lead','Scheduled','Interested','Not Answered','Rejected'];
                pipelineBar.innerHTML = stages.map(s => {
                    const cnt = stageCounts[s] || 0;
                    const col = STAGE_COLORS[s];
                    return `<div onclick="document.getElementById('lead-filter-stage').value='${s}'; loadLeads();"
                        style="flex:1;min-width:80px;text-align:center;padding:10px 8px;border-radius:8px;background:#ffffff;border:1px solid #e2e8f0;cursor:pointer;transition:all .2s;"
                        onmouseover="this.style.background='#f8fafc'; this.style.borderColor='#cbd5e1'" onmouseout="this.style.background='#ffffff'; this.style.borderColor='#e2e8f0'">
                        <div style="font-size:18px;font-weight:700;color:#0f172a;margin-bottom:4px;">${cnt}</div>
                        <div style="font-size:10px;color:#64748b;font-weight:600;display:flex;align-items:center;justify-content:center;gap:4px;"><span style="width:6px;height:6px;border-radius:50%;background:${col};"></span> ${s}</div>
                    </div>`;
                }).join('');
            }

            // Table rows
            if (!tbody) return;
            if (leads.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" style="padding:40px;text-align:center;color:var(--text-light);">No leads found. Add your first lead using the form!</td></tr>';
                return;
            }
            tbody.innerHTML = leads.map(l => {
                const col = STAGE_COLORS[l.stage] || '#64748b';
                
                let reminderHtml = '';
                if (l.reminder_date) {
                    const rDate = new Date(l.reminder_date);
                    const now = new Date();
                    const isPast = rDate < now;
                    const rColor = isPast ? '#ef4444' : '#f59e0b';
                    reminderHtml = `<div style="display:inline-flex; align-items:center; gap:4px; font-size:11px; padding:2px 6px; border-radius:12px; background:${rColor}15; color:${rColor}; font-weight:600; margin-top:6px;" title="Reminder: ${rDate.toLocaleString()}"><i class="lucide-bell" style="width:12px;height:12px;"></i> ${rDate.toLocaleString([], {month:'short', day:'numeric', hour:'2-digit', minute:'2-digit'})}</div>`;
                }

                return `<tr style="border-bottom:1px solid var(--border);transition:background .15s; cursor:pointer;" onmouseover="this.style.background='rgba(0,0,0,0.02)'" onmouseout="this.style.background=''" onclick="openLeadDetail(${l.id})">
                    <td style="padding:12px;" onclick="event.stopPropagation()"><input type="checkbox" class="lead-checkbox" value="${l.id}"></td>
                    <td style="padding:12px;">
                        <div style="font-weight:600;color:var(--text-primary);display:flex;align-items:center;gap:6px;">
                            ${l.lead_name}
                            ${l.notes ? `<span title="${l.notes.replace(/"/g, '&quot;')}" style="cursor:help;color:#94a3b8;"><svg style="width:14px;height:14px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg></span>` : ''}
                            <a href="https://wa.me/91${l.mobile}?text=${encodeURIComponent('Hi ' + l.lead_name + ', I am calling from BFS Financial Services regarding your loan inquiry.')}" target="_blank" onclick="event.stopPropagation()" style="color:#25D366; display:inline-flex;" title="Chat on WhatsApp">
                                <svg style="width:16px;height:16px;" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
                            </a>
                        </div>
                        <div style="font-size:11px;color:var(--text-light);">${l.company_name || '—'}</div>
                        ${l.location ? `<div style="font-size:11px;color:var(--text-light);margin-top:2px;"> ${l.location}</div>` : ''}
                        ${reminderHtml}
                    </td>
                    <td style="padding:12px;font-size:13px;">
                        <div>${l.mobile}</div>
                        ${l.email ? `<div style="font-size:11px;color:var(--text-light);margin-top:4px;"><a href="mailto:${l.email}" onclick="event.stopPropagation()" style="color:var(--primary);text-decoration:none;"><svg style="width:10px;height:10px;display:inline;margin-right:2px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>${l.email}</a></div>` : ''}
                    </td>
                    <td style="padding:12px;font-size:13px;">
                        <div style="font-weight:500;">${l.requirement || '-'}</div>
                        ${l.loan_amount ? `<div style="font-size:11px;color:var(--text-light);margin-top:4px;font-weight:600;">₹ ${Number(l.loan_amount).toLocaleString('en-IN')}</div>` : ''}
                    </td>
                    <td style="padding:12px;font-size:12px;color:var(--text-light);">
                        <div style="font-weight:600; color:var(--text-primary);">${l.lead_source}</div>
                        ${l.added_by && l.added_by !== 'direct' ? `<div style="font-size:11px; margin-top:4px;">By: <strong style="color:var(--primary);">${l.added_by_name || l.added_by}</strong> ${l.added_by_role ? `<span style="color:#64748b;">(${l.added_by_role})</span>` : ''}</div>` : ''}
                    </td>
                    <td style="padding:12px;">${PRIORITY_BADGE[l.priority] || l.priority}</td>
                    <td style="padding:12px;">
                        <span style="padding:3px 8px;border-radius:20px;font-size:12px;font-weight:600;background:${col}20;color:${col};display:inline-block;text-align:center;">
                            ${l.stage}
                        </span>
                    </td>
                    <td style="padding:12px;font-size:12px;color:var(--text-light);">
                        ${(() => {
                            const d = new Date(l.created_at.replace(' ', 'T') + 'Z');
                            const localDate = isNaN(d) ? new Date(l.created_at) : d;
                            return `<div style="font-weight:500;color:var(--text-primary);">${localDate.toLocaleDateString('en-GB', {day:'2-digit', month:'short', year:'numeric'})}</div>
                                    <div style="font-size:11px;margin-top:2px;">${localDate.toLocaleTimeString('en-US', {hour:'2-digit', minute:'2-digit'})}</div>`;
                        })()}
                    </td>
                    <td style="padding:12px;">
                        <div style="font-size:12px;color:var(--text-primary);font-weight:600;">${l.assigned_to || '<span style="color:#94a3b8;font-weight:normal;">Unassigned</span>'}</div>
                        ${(() => {
                            if (!l.assigned_at) return '';
                            const d = new Date(l.assigned_at.replace(' ', 'T') + 'Z');
                            const localDate = isNaN(d) ? new Date(l.assigned_at) : d;
                            const diffMs = new Date() - localDate;
                            if (diffMs < 0) return `<div style="font-size:11px;color:var(--text-light);margin-top:2px;">${localDate.toLocaleString()}</div>`;
                            const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));
                            const diffHrs = Math.floor((diffMs % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                            const diffMins = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60));
                            let timeAgo = '';
                            if (diffDays > 0) timeAgo = diffDays + 'd ' + diffHrs + 'h ago';
                            else if (diffHrs > 0) timeAgo = diffHrs + 'h ' + diffMins + 'm ago';
                            else timeAgo = diffMins + 'm ago';
                            return `<div style="font-size:11px;color:var(--text-light);margin-top:2px;" title="${localDate.toLocaleString()}">️ ${timeAgo}</div>`;
                        })()}
                    </td>
                    <td style="padding:12px;" onclick="event.stopPropagation()">
                                                  <div style="display:flex;gap:6px;">
                              <button class="btn btn-secondary" style="padding:6px; width:28px; height:28px; display:inline-flex; align-items:center; justify-content:center; border-radius:6px;" onclick="openLeadDetail(${l.id})" title="View / Update"><i data-lucide="eye" style="width:14px; height:14px;"></i></button>
                              <button class="btn btn-secondary" style="padding:6px; width:28px; height:28px; display:inline-flex; align-items:center; justify-content:center; border-radius:6px; background:#dcfce7; color:#166534; border:none;" onclick="convertToClient(${l.id})" title="Convert to Client"><i data-lucide="user-check" style="width:14px; height:14px;"></i></button>
                              <button class="btn btn-secondary" style="padding:6px; width:28px; height:28px; display:inline-flex; align-items:center; justify-content:center; border-radius:6px;" onclick="openReminderModal('Lead', ${l.id})" title="Set Reminder"><i data-lucide="bell" style="width:14px; height:14px;"></i></button>
                              ${'<?php echo $_SESSION['role'] ?? ''; ?>' === 'Admin' ? `<button class="btn btn-danger" style="padding:6px; width:28px; height:28px; display:inline-flex; align-items:center; justify-content:center; border-radius:6px;" onclick="deleteLead(${l.id})" title="Delete"><i data-lucide="trash-2" style="width:14px; height:14px;"></i></button>` : ''}
                          </div>
                    </td>
                </tr>`;
            }).join('');
            
            // Render Pagination
            const paginationContainer = document.getElementById('leads-pagination');
            if (paginationContainer && resData.pagination) {
                const p = resData.pagination;
                let html = `<div style="font-size:13px; color:var(--text-light); margin-right:auto;">Showing ${leads.length} of ${p.total_records} records</div>`;
                
                if (p.total_pages > 1) {
                    html += `<button class="btn btn-secondary" style="padding:4px 10px;font-size:12px;" onclick="loadLeads(${p.current_page - 1})" ${p.current_page <= 1 ? 'disabled' : ''}>Previous</button>`;
                    html += `<span style="font-size:13px; font-weight:600;">Page ${p.current_page} of ${p.total_pages}</span>`;
                    html += `<button class="btn btn-secondary" style="padding:4px 10px;font-size:12px;" onclick="loadLeads(${p.current_page + 1})" ${p.current_page >= p.total_pages ? 'disabled' : ''}>Next</button>`;
                }
                paginationContainer.innerHTML = html;
            }
            
            lucide.createIcons();
        } catch(err) {
            console.warn('loadLeads error:', err);
        }
    }

    async function saveLead(e) {
        e.preventDefault();
        const fd = new FormData(e.target);
        const res = await fetch('?api=save_lead', { method: 'POST', body: fd });
        const data = await res.json();
        if (data.success) {
            showNotification(data.message, 'success');
            resetLeadForm();
            loadLeads();
            
            // Automatic Workflow Triggers
            const stage = fd.get('stage');
            if (stage === 'Interested') {
                convertToClient(data.id);
            } else if (stage === 'Scheduled') {
                setTimeout(() => {
                    openReminderModal('Lead', data.id);
                    showNotification('Lead Scheduled! Please set a reminder for the call.', 'info');
                }, 500);
            } else if (stage === 'Rejected') {
                showNotification('Lead marked as Rejected and paused.', 'error');
            }
        } else {
            showNotification(data.error || 'Failed to save lead', 'error');
        }
    }

    async function editLead(id) {
        try {
            const res = await fetch(`?api=get_lead_detail&id=${id}`);
            const l = await res.json();
            
            if (l.error) {
                showNotification(l.error, 'error');
                return;
            }

            document.getElementById('lead-id-hidden').value = l.id;
            document.getElementById('lf-name').value     = l.lead_name || '';
            document.getElementById('lf-company').value  = l.company_name || '';
            document.getElementById('lf-mobile').value   = l.mobile || '';
            document.getElementById('lf-email').value    = l.email || '';
            document.getElementById('lf-source').value   = l.lead_source || '';
            document.getElementById('lf-priority').value = l.priority || '';
            document.getElementById('lf-stage').value    = l.stage || '';
            
            const staffSel = document.getElementById('lf-assigned');
            if (staffSel) staffSel.value = l.assigned_to || '';
            
            document.getElementById('lf-notes').value    = l.notes || '';
            
            document.getElementById('lead-form-title').innerText = ' Log Call / Update Lead';
            document.getElementById('lead-submit-btn').innerText = 'Save Updates';
            document.getElementById('lead-form').scrollIntoView({ behavior: 'smooth', block: 'start' });
        } catch(err) {
            console.error('editLead error:', err);
            showNotification('Could not load lead details.', 'error');
        }
    }

    async function quickUpdateStage(id, stage) {
        const fd = new FormData();
        fd.append('id', id); fd.append('stage', stage);
        const res = await fetch('?api=update_lead_stage', { method: 'POST', body: fd });
        const data = await res.json();
        if (data.success) {
            showNotification(`Stage updated to "${stage}"`, 'success');
            loadLeads();
        } else {
            showNotification(data.error || 'Stage update failed', 'error');
        }
    }

    function deleteLead(id) {
        Swal.fire({
            title: 'Delete Lead?',
            text: "This action cannot be undone.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, delete it!'
        }).then(async (result) => {
            if (result.isConfirmed) {
                const fd = new FormData(); fd.append('id', id);
                const res = await fetch('?api=delete_lead', { method: 'POST', body: fd });
                const data = await res.json();
                if (data.success) {
                    showNotification('Lead deleted', 'success');
                    loadLeads();
                } else {
                    showNotification(data.error || 'Failed to delete lead', 'error');
                }
            }
        });
    }

    function convertToClient(id) {
        Swal.fire({
            title: 'Convert Lead?',
            text: "Are you sure you want to convert this lead into a client application?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, Convert'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`?api=get_lead_detail&id=${id}`)
                .then(r => r.json())
                .then(l => {
                    // Save lead details in sessionStorage for Add Applicant page to pick up
                    sessionStorage.setItem('convert_lead_data', JSON.stringify(l));
                    location.href = 'add_applicant.php';
                });
            }
        });
    }

    function resetLeadForm() {
        document.getElementById('lead-form').reset();
        document.getElementById('lead-id-hidden').value = '';
        document.getElementById('lead-form-title').innerText = ' New Lead';
        document.getElementById('lead-submit-btn').innerText = 'Save Lead';
    }

    function openLeadsBulkUploadModal() {
        openBulkUploadModal('leads');
    }

    document.addEventListener('DOMContentLoaded', () => {
        // Populate source filter
        const sourceDrop = document.getElementById('lead-filter-source');
        if (sourceDrop) {
            fetch('?api=get_active_referrals').then(r=>r.json()).then(data=>{
                if(data && data.length) {
                    data.forEach(ref => {
                        sourceDrop.innerHTML += `<option value="${ref.referral_id}">${ref.full_name}</option>`;
                    });
                } else if(data.success && data.data) {
                    data.data.forEach(ref => {
                        sourceDrop.innerHTML += `<option value="${ref.referral_id}">${ref.full_name}</option>`;
                    });
                }
            }).catch(e=>console.error(e));
        }
        
        loadLeads();
        
        // Auto-open lead edit modal if requested via URL (e.g. from Activity Log)
        const urlParams = new URLSearchParams(window.location.search);
        const editId = urlParams.get('edit_lead');
        if (editId) {
            setTimeout(() => {
                if (typeof editLead === 'function') editLead(editId);
            }, 600);
        }
    });

    function printLeads() {
        const data = window.allLeadsData || [];
        if (data.length === 0) {
            alert('No data to print.');
            return;
        }

        let printWindow = window.open('', '_blank');
        let html = `
        <html>
        <head>
            <title>Leads Data</title>
            <style>
                body { font-family: Arial, sans-serif; padding: 20px; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 13px; }
                th { background-color: #f4f4f4; }
                h2 { text-align: center; }
                @media print {
                    @page { size: landscape; }
                }
            </style>
        </head>
        <body>
            <h2>Leads Report</h2>
            <p>Total Records: ${data.length}</p>
            <table>
                <thead>
                    <tr>
                        <th>Lead Name</th>
                        <th>Company</th>
                        <th>Mobile</th>
                        <th>Source</th>
                        <th>Priority</th>
                        <th>Stage</th>
                        <th>Assigned To</th>
                        <th>Created At</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
        `;

        data.forEach(l => {
            html += `
                <tr>
                    <td>${l.lead_name || ''}</td>
                    <td>${l.company_name || ''}</td>
                    <td>${l.mobile || ''}</td>
                    <td>${l.lead_source || ''}</td>
                    <td>${l.priority || ''}</td>
                    <td>${l.stage || ''}</td>
                    <td>${l.assigned_to || 'Unassigned'}</td>
                    <td>${new Date(l.created_at).toLocaleDateString()}</td>
                    <td>${l.notes || ''}</td>
                </tr>
            `;
        });

        html += `
                </tbody>
            </table>
            <script>
                window.onload = function() { window.print(); window.close(); }
            <\/script>
        </body>
        `;
        printWindow.document.write(html);
        printWindow.document.close();
    }

    // --- LEAD DETAIL MODAL LOGIC ---
    function closeLeadDetail() {
        document.getElementById('lead-detail-modal').style.display = 'none';
    }

    async function openLeadDetail(id) {
        try {
            const res = await fetch(`?api=get_lead_detail&id=${id}`);
            const data = await res.json();
            if (data.error) {
                showNotification(data.error, 'error');
                return;
            }
            
            document.getElementById('detail-lead-id').value = data.id;
            document.getElementById('detail-lead-name').innerText = data.lead_name || 'Unknown Lead';
            
            // Map comprehensive details
            document.getElementById('dl-company').innerText = data.company_name || 'N/A';
            document.getElementById('dl-email').innerText = data.email || 'N/A';
            if (data.email) {
                document.getElementById('dl-email').innerHTML = `<a href="mailto:${data.email}" style="color:var(--primary);text-decoration:none;">${data.email}</a>`;
            }
            document.getElementById('dl-location').innerText = data.location || 'N/A';
            document.getElementById('dl-requirement').innerText = data.requirement || 'N/A';
            document.getElementById('dl-amount').innerText = data.loan_amount ? `₹ ${Number(data.loan_amount).toLocaleString('en-IN')}` : 'N/A';
            document.getElementById('dl-stage').innerText = data.stage || 'N/A';
            document.getElementById('dl-priority').innerText = data.priority || 'N/A';
            document.getElementById('dl-assigned').innerText = data.assigned_to || 'Unassigned';
              
              if (data.photo) {
                  document.getElementById('dl-photo').src = data.photo;
                  document.getElementById('dl-photo-container').style.display = 'block';
              } else {
                  document.getElementById('dl-photo-container').style.display = 'none';
              }
            
            // Notes
            const notesEl = document.getElementById('dl-notes');
            if (data.notes) {
                notesEl.innerText = data.notes;
                notesEl.parentElement.style.display = 'block';
            } else {
                notesEl.parentElement.style.display = 'none';
            }

            document.getElementById('detail-lead-phone').innerText = data.mobile;
            document.getElementById('detail-lead-phone').href = `tel:${data.mobile}`;
            document.getElementById('detail-lead-wa').href = `https://wa.me/91${data.mobile}?text=${encodeURIComponent('Hi ' + data.lead_name + ', I am calling from BFS Financial Services regarding your loan inquiry.')}`;
            
            // Source & Referrer
            let sourceHtml = `<strong>${data.lead_source || 'Unknown'}</strong>`;
            if (data.added_by && data.added_by !== 'direct') {
                sourceHtml += `<div style="margin-top:4px; font-size:12px; display:flex; align-items:center; gap:6px;">
                                By: <span style="color:#1e293b; font-weight:600;">${data.added_by_name || data.added_by}</span>`;
                if (data.referrer_id) {
                     sourceHtml += `<a href="view_referral.php?id=${data.referrer_id}" style="color:var(--primary); text-decoration:none; font-weight:600; background:#eff6ff; padding:2px 8px; border-radius:12px; font-size:10px;">View Profile</a>`;
                }
                sourceHtml += `</div>`;
            }
            document.getElementById('dl-source-box').innerHTML = sourceHtml;
            
            // Build timeline
            const timelineDiv = document.getElementById('lead-detail-timeline');
            if (!data.timeline || data.timeline.length === 0) {
                timelineDiv.innerHTML = '<div style="color:var(--text-light); text-align:center; padding:20px;">No activity logged yet.</div>';
            } else {
                timelineDiv.innerHTML = data.timeline.map(n => `
                    <div style="border-left:2px solid #e2e8f0; padding-left:15px; margin-bottom:15px; position:relative;">
                        <div style="position:absolute; left:-6px; top:0; width:10px; height:10px; border-radius:50%; background:#6366f1;"></div>
                        <div style="font-size:11px; color:var(--text-light); margin-bottom:4px;">${new Date(n.created_at).toLocaleString()} • <strong>${n.added_by_user}</strong></div>
                        <div style="font-size:13px; color:var(--text-primary); background:#f8fafc; padding:10px; border-radius:6px; border:1px solid #e2e8f0;">${n.note_text.replace(/\n/g, '<br>')}</div>
                    </div>
                `).join('');
            }
            
            document.getElementById('lead-detail-modal').style.display = 'flex';
        } catch(e) {
            console.error(e);
            showNotification('Failed to load lead details', 'error');
        }
    }
    
    async function submitLeadNote(e) {
        e.preventDefault();
        const fd = new FormData(e.target);
        fd.append('api', 'add_lead_note');
        try {
            const res = await fetch('?api=add_lead_note', { method:'POST', body:fd });
            const data = await res.json();
            if (data.success) {
                showNotification('Activity logged!', 'success');
                e.target.reset();
                openLeadDetail(fd.get('lead_id'));
                loadLeads();
            } else {
                showNotification(data.error || 'Error adding note', 'error');
            }
        } catch(e) {
            console.error(e);
        }
    }
</script>

<!-- LEAD DETAIL MODAL -->
<div id="lead-detail-modal" onclick="if(event.target===this) closeLeadDetail()" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; justify-content:flex-end;">
    <div style="background:#fff; width:100%; max-width:500px; height:100%; display:flex; flex-direction:column; box-shadow:-5px 0 25px rgba(0,0,0,0.1); animation: slideInRight 0.3s ease;">
        <div style="padding:20px; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:flex-start; background:#f8fafc;">
            <div>
                <h2 id="detail-lead-name" style="margin:0; font-size:22px; color:#1e293b; font-weight:700;">Lead Name</h2>
                <div style="display:flex; gap:12px; margin-top:10px; font-size:13px;">
                    <a id="detail-lead-phone" href="#" style="color:#4f46e5; font-weight:600; text-decoration:none; display:flex; align-items:center; gap:4px; background:#e0e7ff; padding:4px 10px; border-radius:6px;"> <span>Phone</span></a>
                    <a id="detail-lead-wa" href="#" target="_blank" style="color:#16a34a; font-weight:600; text-decoration:none; display:flex; align-items:center; gap:4px; background:#dcfce7; padding:4px 10px; border-radius:6px;"><svg style="width:14px;height:14px;" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg> WhatsApp</a>
                </div>
            </div>
            <button onclick="closeLeadDetail()" style="background:#e2e8f0; border:none; width:30px; height:30px; border-radius:50%; font-size:18px; line-height:1; cursor:pointer; color:#475569; display:flex; align-items:center; justify-content:center; transition:all 0.2s;" onmouseover="this.style.background='#cbd5e1'" onmouseout="this.style.background='#e2e8f0'">&times;</button>
        </div>
        
        <div style="flex:1; overflow-y:auto; padding:20px; background:#fff;">
            <!-- Comprehensive Details Box -->
            <div style="margin-bottom:25px;">
                <h3 style="font-size:13px; text-transform:uppercase; letter-spacing:0.5px; color:#94a3b8; margin:0 0 15px 0; display:flex; align-items:center; gap:6px;"><i data-lucide="user" style="width:14px;height:14px;"></i> Lead Information</h3>
                
                <div id="dl-photo-container" style="margin-bottom:20px; display:none; text-align:center;">
                      <img id="dl-photo" src="" style="width:120px; height:120px; object-fit:cover; border-radius:50%; border:3px solid var(--primary); box-shadow:0 4px 10px rgba(0,0,0,0.1);">
                      <div style="font-size:11px; color:#64748b; margin-top:5px;">Applicant Photo</div>
                  </div>
                  <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px; font-size:13px;">
                    <div><div style="color:#64748b; font-size:11px; margin-bottom:2px;">Email</div><div id="dl-email" style="font-weight:500; color:#0f172a;"></div></div>
                    <div><div style="color:#64748b; font-size:11px; margin-bottom:2px;">Company</div><div id="dl-company" style="font-weight:500; color:#0f172a;"></div></div>
                    <div><div style="color:#64748b; font-size:11px; margin-bottom:2px;">Location</div><div id="dl-location" style="font-weight:500; color:#0f172a;"></div></div>
                    <div><div style="color:#64748b; font-size:11px; margin-bottom:2px;">Requirement</div><div id="dl-requirement" style="font-weight:500; color:#0f172a;"></div></div>
                    <div><div style="color:#64748b; font-size:11px; margin-bottom:2px;">Loan Amount</div><div id="dl-amount" style="font-weight:600; color:#0f172a;"></div></div>
                    <div><div style="color:#64748b; font-size:11px; margin-bottom:2px;">Stage</div><div id="dl-stage" style="font-weight:600; color:var(--primary);"></div></div>
                    <div><div style="color:#64748b; font-size:11px; margin-bottom:2px;">Priority</div><div id="dl-priority" style="font-weight:500; color:#0f172a;"></div></div>
                    <div><div style="color:#64748b; font-size:11px; margin-bottom:2px;">Assigned To</div><div id="dl-assigned" style="font-weight:500; color:#0f172a;"></div></div>
                </div>
                
                <div style="margin-top:15px; padding-top:15px; border-top:1px dashed #e2e8f0;">
                    <div style="color:#64748b; font-size:11px; margin-bottom:4px;">Lead Source & Referrer</div>
                    <div id="dl-source-box" style="font-size:13px; color:#0f172a; background:#f8fafc; padding:10px; border-radius:6px; border:1px solid #f1f5f9;"></div>
                </div>
                
                <div style="margin-top:15px;">
                    <div style="color:#64748b; font-size:11px; margin-bottom:4px;">Notes / Description</div>
                    <div id="dl-notes" style="font-size:13px; color:#334155; line-height:1.5; background:#fff7ed; padding:10px; border-radius:6px; border:1px solid #ffedd5;"></div>
                </div>
            </div>

            <h3 style="font-size:13px; text-transform:uppercase; letter-spacing:0.5px; color:#94a3b8; margin:0 0 15px 0; display:flex; align-items:center; gap:6px;"><i data-lucide="history" style="width:14px;height:14px;"></i> Activity Timeline</h3>
            <div id="lead-detail-timeline" style="margin-left:8px;"></div>
        </div>
        
        <div style="padding:20px; border-top:1px solid #e2e8f0; background:#fff;">
            <form onsubmit="submitLeadNote(event)">
                <input type="hidden" name="lead_id" id="detail-lead-id">
                <div style="margin-bottom:10px;">
                    <textarea name="note_text" required placeholder="Log a call, note, or meeting..." style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:6px; resize:none; height:80px; font-size:13px; font-family:inherit;"></textarea>
                </div>
                <div style="margin-bottom:15px;">
                    <label style="display:block; font-size:11px; font-weight:600; color:#64748b; margin-bottom:4px;"> Set Follow-up Reminder (Optional)</label>
                    <input type="datetime-local" name="reminder_date" style="width:100%; padding:8px; border:1px solid #cbd5e1; border-radius:6px; font-size:13px;">
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%; padding:10px;">Save Activity</button>
            </form>
        </div>
    </div>
</div>
<style>
@keyframes slideInRight {
    from { transform: translateX(100%); }
    to { transform: translateX(0); }
}
</style>

<?php require_once 'footer.php'; ?>
