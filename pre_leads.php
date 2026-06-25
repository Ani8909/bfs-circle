<?php
require_once 'config.php';
$page_title = 'Pre-Leads (Raw Data)';
$page_subtitle = 'Manage raw data and unverified prospects';
require_once 'header.php';
?>

<div id="view-preleads" class="view-container">
    <!-- Stats Bar -->
    <div class="stats-grid" style="grid-template-columns: repeat(3,1fr); margin-bottom:1.5rem;">
        <div class="stat-card" style="border-left:4px solid #f59e0b;">
            <div class="stat-card-header"><span class="stat-label">Total Prospects</span><i data-lucide="inbox" style="color:#f59e0b;width:20px;height:20px;"></i></div>
            <div class="stat-value" id="prelead-stat-total">-</div>
        </div>
        <div class="stat-card" style="border-left:4px solid #10b981;">
            <div class="stat-card-header"><span class="stat-label">Interested</span><i data-lucide="thumbs-up" style="color:#10b981;width:20px;height:20px;"></i></div>
            <div class="stat-value" id="prelead-stat-interested">-</div>
        </div>
        <div class="stat-card" style="border-left:4px solid #ef4444;">
            <div class="stat-card-header"><span class="stat-label">Junk</span><i data-lucide="trash-2" style="color:#ef4444;width:20px;height:20px;"></i></div>
            <div class="stat-value" id="prelead-stat-junk">-</div>
        </div>
    </div>

    <div class="flex-row" style="gap:20px; align-items:flex-start;">
        <!-- Add Form -->
        <div class="card" style="flex:0 0 300px; position:sticky; top:20px;">
            <div class="card-title-bar" style="display:flex; justify-content:space-between; align-items:center;">
                <h2><i data-lucide="plus"></i> Add Pre-Lead</h2>
                <button type="button" class="btn btn-secondary" onclick="openPreLeadBulkUploadModal()" style="font-size:11px; padding:4px 8px;"><i data-lucide="upload" style="width:14px;height:14px;margin-right:4px;"></i> Bulk</button>
            </div>
            <div class="card-body" style="margin-top: 15px;">
                <form id="prelead-form" onsubmit="savePreLead(event)">
                    <input type="hidden" name="action" value="save_prelead">
                    <input type="hidden" name="id" id="prelead_id" value="">
                    
                    <div class="form-group" style="margin-bottom: 12px;">
                        <label>Name *</label>
                        <input type="text" name="name" id="pl_name" required>
                    </div>
                    <div class="form-group" style="margin-bottom: 12px;">
                        <label>Mobile *</label>
                        <input type="text" name="mobile" id="pl_mobile" required>
                    </div>
                    <div class="form-group" style="margin-bottom: 12px;">
                        <label>Company / Location</label>
                        <input type="text" name="company_name" id="pl_company">
                    </div>
                    <div class="form-group" style="margin-bottom: 12px;">
                        <label>Source</label>
                        <select name="source" id="pl_source">
                            <option value="Unknown">Unknown</option>
                            <option value="Website">Website</option>
                            <option value="Justdial">Justdial</option>
                            <option value="Reference">Reference</option>
                        </select>
                    </div>
                    <div class="form-group admin-only-field" style="display:none; margin-bottom: 12px;">
                        <label>Assigned To</label>
                        <select name="assigned_to" id="pl-assigned_to" class="user-select">
                            <option value="">-- Unassigned --</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label>Notes</label>
                        <textarea name="notes" id="pl_notes" rows="2"></textarea>
                    </div>
                    
                    <div style="display:flex; gap:10px; margin-top:15px;">
                        <button type="submit" class="btn btn-primary" style="flex:1;">Save</button>
                        <button type="button" class="btn btn-secondary" onclick="resetPreLeadForm()">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Table -->
        <div class="card" style="flex:1;">
            <div class="card-title-bar" style="display:flex; justify-content:space-between; align-items:center; flex-wrap: wrap; gap: 10px;">
                <h2><i data-lucide="list"></i> All Pre-Leads</h2>
                <div class="admin-only-field" style="display:none; gap:10px; align-items:center; background:#f8fafc; padding:6px 12px; border-radius:6px; border:1px solid #e2e8f0;">
                    <span style="font-size:12px; font-weight:600; color:#475569;">Bulk Assign:</span>
                    <select id="bulk-assign-preleads-staff" class="user-select" style="padding:4px; border:1px solid #cbd5e1; border-radius:4px; font-size:12px; outline:none; width: auto;">
                        <option value="">-- Select Staff --</option>
                    </select>
                    <button class="btn btn-secondary" style="padding:4px 10px; font-size:11px;" onclick="bulkAssign('preleads')">Assign Selected</button>
                    <button class="btn btn-secondary" style="padding:4px 10px; font-size:11px; background:#fee2e2; color:#b91c1c; border:none; margin-left:8px;" onclick="bulkDelete('preleads')">Delete Selected</button>
                </div>
                <input type="text" id="prelead-search" placeholder="Search mobile/name..." class="search-box" onkeyup="loadPreLeads()" style="max-width:200px; padding:6px 10px; font-size:12px; border:1px solid #cbd5e1; border-radius:4px;">
            </div>
            <div class="card-body" style="padding:0; overflow-x:auto; margin-top: 15px;">
                <table class="quotation-list-table" id="preleads-table">
                    <thead>
                        <tr>
                            <th style="width:40px;"><input type="checkbox" id="selectAllPreLeads" onclick="toggleSelectAll('preleads')"></th>
                            <th>NAME / COMPANY</th>
                            <th>MOBILE</th>
                            <th>SOURCE</th>
                            <th>ASSIGNED TO</th>
                            <th>STATUS</th>
                            <th>ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
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
                
                if (typeof loadPreLeads === 'function') loadPreLeads();
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
                
                if (type === 'leads') {
                    if (typeof loadLeads === 'function') loadLeads();
                } else {
                    loadPreLeads();
                }
            } else {
                showNotification(json.error || "Failed to bulk assign", "error");
            }
        } catch(e) {
            showNotification("Error during bulk assignment", "error");
        }
    }

    async function loadPreLeads() {
        const res = await fetch('?api=get_preleads');
        const data = await res.json();
        
        const tbody = document.querySelector('#preleads-table tbody');
        tbody.innerHTML = '';
        
        let total = 0, interested = 0, junk = 0;
        
        const search = document.getElementById('prelead-search')?.value.toLowerCase() || '';
        
        data.forEach(p => {
            if(search && !p.name.toLowerCase().includes(search) && !p.mobile.includes(search)) return;
            
            total++;
            if(p.status === 'Interested') interested++;
            if(p.status === 'Junk') junk++;
            
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><input type="checkbox" class="prelead-checkbox" value="${p.id}"></td>
                <td>
                    <div style="font-weight:600;">${p.name}</div>
                    <div style="font-size:12px;color:var(--text-light);">${p.company_name || p.location || '-'}</div>
                </td>
                <td>${p.mobile}<br><span style="font-size:11px;color:#888;">${p.email || ''}</span></td>
                <td><span class="badge" style="background:#f1f5f9;color:#475569;">${p.source}</span></td>
                <td><div style="font-size:12px; font-weight:600; color:#475569;">${p.assigned_to || '<span style="color:#94a3b8;font-weight:normal;">Unassigned</span>'}</div></td>
                <td>
                    <select onchange="updatePreLeadStatus(${p.id}, this.value)" style="padding:4px; border-radius:4px; font-size:12px;">
                        <option value="Not Contacted" ${p.status==='Not Contacted'?'selected':''}>Not Contacted</option>
                        <option value="Interested" ${p.status==='Interested'?'selected':''}>Interested</option>
                        <option value="Junk" ${p.status==='Junk'?'selected':''}>Junk</option>
                    </select>
                </td>
                <td>
                    <div style="display:flex;gap:5px;">
                        ${currentUser && currentUser.role === 'Admin' ? `<button class="btn btn-secondary" onclick="editPreLead(${p.id})" style="padding:4px 8px;" title="Edit"><i data-lucide="edit" style="width:14px;height:14px;"></i></button>` : ''}
                        <button class="btn btn-primary" onclick="promotePreLead(${p.id})" style="padding:4px 8px; font-size:12px;" title="Promote to Lead"><i data-lucide="rocket" style="width:14px;height:14px;"></i> Promote</button>
                        ${currentUser && currentUser.role === 'Admin' ? `<button class="btn btn-danger" onclick="deletePreLead(${p.id})" style="padding:4px 8px;" title="Delete"><i data-lucide="trash-2" style="width:14px;height:14px;"></i></button>` : ''}
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });
        
        document.getElementById('prelead-stat-total').innerText = total;
        document.getElementById('prelead-stat-interested').innerText = interested;
        document.getElementById('prelead-stat-junk').innerText = junk;
        lucide.createIcons();
    }

    async function savePreLead(e) {
        e.preventDefault();
        const fd = new FormData(e.target);
        const res = await fetch('?api=save_prelead', { method:'POST', body:fd });
        const data = await res.json();
        if(data.success) {
            showNotification(data.message, 'success');
            resetPreLeadForm();
            loadPreLeads();
        } else {
            showNotification(data.error, 'error');
        }
    }

    function resetPreLeadForm() {
        document.getElementById('prelead-form').reset();
        document.getElementById('prelead_id').value = '';
    }

    async function deletePreLead(id) {
        if(!confirm("Are you sure you want to delete this raw data?")) return;
        const fd = new FormData(); fd.append('id', id);
        const res = await fetch('?api=delete_prelead', { method:'POST', body:fd });
        const data = await res.json();
        if(data.success) { showNotification(data.message, 'success'); loadPreLeads(); }
        else showNotification(data.error, 'error');
    }

    async function promotePreLead(id) {
        if(!confirm("Promote this prospect to your main Leads CRM?")) return;
        const fd = new FormData(); fd.append('id', id);
        const res = await fetch('?api=promote_prelead', { method:'POST', body:fd });
        const data = await res.json();
        if(data.success) { 
            showNotification("🚀 " + data.message, 'success'); 
            loadPreLeads(); 
        } else {
            showNotification(data.error, 'error');
        }
    }

    async function updatePreLeadStatus(id, status) {
        const fd = new FormData(); fd.append('id', id); fd.append('status', status);
        await fetch('?api=update_prelead_status', { method:'POST', body:fd });
        loadPreLeads();
    }
    
    function openPreLeadBulkUploadModal() {
        openBulkUploadModal('pre_leads');
    }

    function editPreLead(id) {
        // Find prelead in local display list (via direct lookup if needed)
        fetch(`?api=get_preleads`)
        .then(r => r.json())
        .then(data => {
            const p = data.find(x => x.id == id);
            if(p) {
                document.getElementById('prelead_id').value = p.id;
                document.getElementById('pl_name').value = p.name;
                document.getElementById('pl_mobile').value = p.mobile;
                document.getElementById('pl_company').value = p.company_name || '';
                document.getElementById('pl_source').value = p.source || 'Unknown';
                const staffSel = document.getElementById('pl-assigned_to');
                if(staffSel) staffSel.value = p.assigned_to || '';
                document.getElementById('pl_notes').value = p.notes || '';
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        loadPreLeads();
        
        // Auto-open prelead edit modal if requested via URL
        const urlParams = new URLSearchParams(window.location.search);
        const editId = urlParams.get('edit_prelead');
        if (editId) {
            setTimeout(() => {
                if (typeof editPreLead === 'function') editPreLead(editId);
            }, 600);
        }
    });
</script>

<?php require_once 'footer.php'; ?>
