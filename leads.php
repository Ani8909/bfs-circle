<?php
require_once 'config.php';
$page_title = 'Lead Management';
$page_subtitle = '🎯 Capture, track and convert leads into clients';
require_once 'header.php';
?>

<div id="view-leads" class="view-container">
    <!-- Stats Bar -->
    <div class="stats-grid" style="grid-template-columns: repeat(4,1fr); margin-bottom:1.5rem;">
        <div class="stat-card" style="border-left:4px solid #6366f1;">
            <div class="stat-card-header"><span class="stat-label">Total Leads</span><i data-lucide="target" style="color:#6366f1;width:20px;height:20px;"></i></div>
            <div class="stat-value" id="lead-stat-total">—</div>
        </div>
        <div class="stat-card" style="border-left:4px solid var(--danger);">
            <div class="stat-card-header"><span class="stat-label">Hot Leads</span><i data-lucide="flame" style="color:var(--danger);width:20px;height:20px;"></i></div>
            <div class="stat-value" id="lead-stat-hot">—</div>
        </div>
        <div class="stat-card" style="border-left:4px solid var(--success);">
            <div class="stat-card-header"><span class="stat-label">Won Leads</span><i data-lucide="trophy" style="color:var(--success);width:20px;height:20px;"></i></div>
            <div class="stat-value" id="lead-stat-won">—</div>
        </div>
        <div class="stat-card" style="border-left:4px solid var(--text-muted);">
            <div class="stat-card-header"><span class="stat-label">In Progress</span><i data-lucide="clock" style="color:var(--text-muted);width:20px;height:20px;"></i></div>
            <div class="stat-value" id="lead-stat-progress">—</div>
        </div>
    </div>

    <div style="display:grid; grid-template-columns:1fr 2.2fr; gap:1.5rem; align-items:start;">
        <!-- ADD / EDIT LEAD FORM -->
        <div class="card" style="position:sticky;top:20px;">
            <div class="card-title-bar">
                <h2 id="lead-form-title">➕ New Lead</h2>
                <button type="button" class="btn btn-secondary" style="padding:4px 10px;font-size:12px;margin-left:auto;" onclick="openLeadsBulkUploadModal()">📤 Bulk Upload</button>
            </div>
            <form id="lead-form" onsubmit="saveLead(event)">
                <input type="hidden" name="lead_id" id="lead-id-hidden" value="">
                <div class="form-group" style="margin-bottom: 12px;">
                    <label class="required">Contact Name</label>
                    <input type="text" name="lead_name" id="lf-name" placeholder="e.g. Rajesh Gupta" required>
                </div>
                <div class="form-group" style="margin-bottom: 12px;">
                    <label>Company Name</label>
                    <input type="text" name="company_name" id="lf-company" placeholder="e.g. Infosys Ltd.">
                </div>
                <div class="form-group" style="margin-bottom: 12px;">
                    <label class="required">Mobile</label>
                    <input type="tel" name="mobile" id="lf-mobile" placeholder="10-digit number" required>
                </div>
                <div class="form-group" style="margin-bottom: 12px;">
                    <label>Email</label>
                    <input type="email" name="email" id="lf-email" placeholder="contact@company.com">
                </div>
                <div class="form-grid" style="grid-template-columns:1fr 1fr; gap:10px; margin-bottom: 12px;">
                    <div class="form-group">
                        <label>Lead Source</label>
                        <select name="lead_source" id="lf-source">
                            <option>Cold Call</option>
                            <option>Website</option>
                            <option>Reference</option>
                            <option>LinkedIn</option>
                            <option>Exhibition</option>
                            <option>Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Priority</label>
                        <select name="priority" id="lf-priority">
                            <option value="Hot">🔴 Hot</option>
                            <option value="Warm" selected>🟡 Warm</option>
                            <option value="Cold">🔵 Cold</option>
                        </select>
                    </div>
                </div>
                <div class="form-group admin-only-field" style="display:none; margin-bottom: 12px;">
                    <label>Assigned To</label>
                    <select name="assigned_to" id="lf-assigned" class="user-select">
                        <option value="">-- Unassigned --</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 12px;">
                    <label>Stage</label>
                    <select name="stage" id="lf-stage">
                        <option value="New Lead">New Lead</option>
                        <option value="Contacted">Contacted</option>
                        <option value="Interested">Interested</option>
                        <option value="Proposal Sent">Proposal Sent</option>
                        <option value="Negotiation">Negotiation</option>
                        <option value="Won">Won ✅</option>
                        <option value="Lost">Lost ❌</option>
                    </select>
                </div>
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label>Notes / Remarks</label>
                    <textarea name="notes" id="lf-notes" rows="3" placeholder="Any remarks about this lead..."></textarea>
                </div>
                <div class="form-actions" style="gap:8px;">
                    <button type="submit" class="btn btn-primary" id="lead-submit-btn">Save Lead</button>
                    <button type="button" class="btn btn-secondary" onclick="resetLeadForm()">Cancel</button>
                </div>
            </form>
        </div>

        <!-- LEADS TABLE -->
        <div class="card">
            <div class="card-title-bar" style="flex-wrap:wrap; gap:10px;">
                <h2>📋 All Leads</h2>
                <div style="display:flex;gap:8px;flex-wrap:wrap; align-items: center;">
                    <div class="admin-only-field" style="display:none; gap:6px; align-items:center; background:#f8fafc; padding:6px 10px; border-radius:6px; border:1px solid #e2e8f0; font-size:12px;">
                        <span style="font-weight:600; color:#475569;">Bulk:</span>
                        <select id="bulk-assign-leads-staff" class="user-select" style="padding:4px; border:1px solid #cbd5e1; border-radius:4px; font-size:12px;">
                            <option value="">-- Select Staff --</option>
                        </select>
                        <button class="btn btn-secondary" style="padding:4px 8px; font-size:11px;" onclick="bulkAssign('leads')">Assign</button>
                        <button class="btn btn-secondary" style="padding:4px 8px; font-size:11px; background:#fee2e2; color:#b91c1c; border:none; margin-left:8px;" onclick="bulkDelete('leads')">Delete</button>
                    </div>
                    
                    <input type="text" id="lead-search" placeholder="Search name/company..." style="padding:6px 12px;border:1px solid var(--border);border-radius:6px;font-size:13px;min-width:150px;" oninput="loadLeads()">
                    <select id="lead-filter-stage" onchange="loadLeads()" style="padding:6px 10px;border:1px solid var(--border);border-radius:6px;font-size:13px;">
                        <option value="">All Stages</option>
                        <option value="New Lead">New Lead</option>
                        <option value="Contacted">Contacted</option>
                        <option value="Interested">Interested</option>
                        <option value="Proposal Sent">Proposal Sent</option>
                        <option value="Negotiation">Negotiation</option>
                        <option value="Won">Won ✅</option>
                        <option value="Lost">Lost ❌</option>
                    </select>
                    <select id="lead-filter-priority" onchange="loadLeads()" style="padding:6px 10px;border:1px solid var(--border);border-radius:6px;font-size:13px;">
                        <option value="">All Priorities</option>
                        <option value="Hot">🔴 Hot</option>
                        <option value="Warm">🟡 Warm</option>
                        <option value="Cold">🔵 Cold</option>
                    </select>
                    <select id="lead-filter-staff" class="user-select" onchange="loadLeads()" style="padding:6px 10px;border:1px solid var(--border);border-radius:6px;font-size:13px;">
                        <option value="">All Staff</option>
                    </select>
                </div>
            </div>

            <!-- Pipeline Summary Bar -->
            <div id="lead-pipeline-bar" style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:1rem;padding:12px;background:var(--bg-main);border-radius:8px;"></div>

            <div style="overflow-x:auto;">
                <table style="width:100%;border-collapse:collapse;font-size:13px;">
                    <thead>
                        <tr style="background:var(--bg-main);color:var(--text-light);font-size:11px;text-transform:uppercase;letter-spacing:0.5px;border-bottom:1px solid var(--border);">
                            <th style="padding:10px 12px;text-align:left;width:40px;"><input type="checkbox" id="selectAllLeads" onclick="toggleSelectAll('leads')"></th>
                            <th style="padding:10px 12px;text-align:left;">Contact / Company</th>
                            <th style="padding:10px 12px;text-align:left;">Mobile</th>
                            <th style="padding:10px 12px;text-align:left;">Source</th>
                            <th style="padding:10px 12px;text-align:left;">Priority</th>
                            <th style="padding:10px 12px;text-align:left;">Stage</th>
                            <th style="padding:10px 12px;text-align:left;">Assigned</th>
                            <th style="padding:10px 12px;text-align:left;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="leads-table-body">
                        <tr><td colspan="8" style="padding:30px;text-align:center;color:var(--text-light);">Loading leads...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    const STAGE_COLORS = {
        'New Lead':      '#6366f1',
        'Contacted':     '#f59e0b',
        'Interested':    '#3b82f6',
        'Proposal Sent': '#8b5cf6',
        'Negotiation':   '#f97316',
        'Won':           '#10b981',
        'Lost':          '#ef4444'
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

    async function loadLeads() {
        const search   = document.getElementById('lead-search')?.value || '';
        const stage    = document.getElementById('lead-filter-stage')?.value || '';
        const priority = document.getElementById('lead-filter-priority')?.value || '';
        const assigned = document.getElementById('lead-filter-staff')?.value || '';
        const params   = new URLSearchParams({ api: 'get_leads', search, stage, priority, assigned_to: assigned });

        try {
            const res = await fetch('?' + params.toString());
            if (!res.ok) return;
            const leads = await res.json();

            // Update stat cards
            const total    = leads.length;
            const hot      = leads.filter(l => l.priority === 'Hot' && l.stage !== 'Won' && l.stage !== 'Lost').length;
            const won      = leads.filter(l => l.stage === 'Won').length;
            const progress = leads.filter(l => l.stage !== 'Won' && l.stage !== 'Lost').length;

            const setEl = (id, v) => { const el = document.getElementById(id); if(el) el.innerText = v; };
            setEl('lead-stat-total',    total);
            setEl('lead-stat-hot',      hot);
            setEl('lead-stat-won',      won);
            setEl('lead-stat-progress', progress);

            // Pipeline Bar counts
            const stageCounts = {};
            leads.forEach(l => { stageCounts[l.stage] = (stageCounts[l.stage] || 0) + 1; });
            const pipelineBar = document.getElementById('lead-pipeline-bar');
            if (pipelineBar) {
                const stages = ['New Lead','Contacted','Interested','Proposal Sent','Negotiation','Won','Lost'];
                pipelineBar.innerHTML = stages.map(s => {
                    const cnt = stageCounts[s] || 0;
                    const col = STAGE_COLORS[s];
                    return `<div onclick="document.getElementById('lead-filter-stage').value='${s}'; loadLeads();"
                        style="flex:1;min-width:80px;text-align:center;padding:8px 6px;border-radius:8px;background:${col}18;border:1px solid ${col}44;cursor:pointer;transition:all .2s;"
                        onmouseover="this.style.background='${col}33'" onmouseout="this.style.background='${col}18'">
                        <div style="font-size:18px;font-weight:700;color:${col};">${cnt}</div>
                        <div style="font-size:10px;color:${col};font-weight:600;">${s}</div>
                    </div>`;
                }).join('');
            }

            // Table rows
            const tbody = document.getElementById('leads-table-body');
            if (!tbody) return;
            if (leads.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" style="padding:40px;text-align:center;color:var(--text-light);">No leads found. Add your first lead using the form!</td></tr>';
                return;
            }
            tbody.innerHTML = leads.map(l => {
                const col = STAGE_COLORS[l.stage] || '#64748b';
                return `<tr style="border-bottom:1px solid var(--border);transition:background .15s;" onmouseover="this.style.background='rgba(0,0,0,0.02)'" onmouseout="this.style.background=''">
                    <td style="padding:12px;"><input type="checkbox" class="lead-checkbox" value="${l.id}"></td>
                    <td style="padding:12px;">
                        <div style="font-weight:600;color:var(--text-primary);">${l.lead_name}</div>
                        <div style="font-size:11px;color:var(--text-light);">${l.company_name || '—'}</div>
                    </td>
                    <td style="padding:12px;font-size:13px;">${l.mobile}</td>
                    <td style="padding:12px;font-size:12px;color:var(--text-light);">${l.lead_source}</td>
                    <td style="padding:12px;">${PRIORITY_BADGE[l.priority] || l.priority}</td>
                    <td style="padding:12px;">
                        <span style="padding:3px 8px;border-radius:20px;font-size:12px;font-weight:600;background:${col}20;color:${col};display:inline-block;text-align:center;">
                            ${l.stage}
                        </span>
                    </td>
                    <td style="padding:12px;font-size:12px;color:var(--text-light);">${l.assigned_to || '<span style="color:#94a3b8;font-weight:normal;">Unassigned</span>'}</td>
                    <td style="padding:12px;">
                        <div style="display:flex;gap:6px;">
                            ${currentUser && currentUser.role === 'Admin' ? `<button class="btn btn-secondary" style="padding:4px 10px;font-size:11px;" onclick="editLead(${l.id})" title="Edit">Edit</button>` : ''}
                            <button class="btn btn-secondary" style="padding:4px 10px;font-size:11px;background:#dcfce7;color:#166534;border:none;" onclick="convertToClient(${l.id})" title="Convert to Client">Convert</button>
                            <button class="btn btn-secondary" style="padding:4px 10px;font-size:11px;" onclick="openReminderModal('Lead', ${l.id})" title="Set Reminder">⏰</button>
                            ${currentUser && currentUser.role === 'Admin' ? `<button class="btn btn-danger" style="padding:4px 10px;font-size:11px;" onclick="deleteLead(${l.id})" title="Delete">Delete</button>` : ''}
                        </div>
                    </td>
                </tr>`;
            }).join('');
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
            
            document.getElementById('lead-form-title').innerText = '📝 Edit Lead';
            document.getElementById('lead-submit-btn').innerText = 'Update Lead';
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

    async function deleteLead(id) {
        if (!confirm('Are you sure you want to delete this lead?')) return;
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

    function convertToClient(id) {
        fetch(`?api=get_lead_detail&id=${id}`)
        .then(r => r.json())
        .then(l => {
            // Save lead details in sessionStorage for Add Client page to pick up
            sessionStorage.setItem('convert_lead_data', JSON.stringify(l));
            location.href = 'add_client.php';
        });
    }

    function resetLeadForm() {
        document.getElementById('lead-form').reset();
        document.getElementById('lead-id-hidden').value = '';
        document.getElementById('lead-form-title').innerText = '➕ New Lead';
        document.getElementById('lead-submit-btn').innerText = 'Save Lead';
    }

    function openLeadsBulkUploadModal() {
        openBulkUploadModal('leads');
    }

    document.addEventListener('DOMContentLoaded', () => {
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
</script>

<?php require_once 'footer.php'; ?>
