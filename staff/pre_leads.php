<?php
require_once __DIR__ . '/../config.php';
$page_title = 'My Pre-Leads';
$page_subtitle = 'Manage assigned raw prospects and unverified entries';
require_once __DIR__ . '/header.php';
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
                <h2><i data-lucide="plus"></i> Add Prospect</h2>
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
                <h2><i data-lucide="list"></i> My Pre-Leads List</h2>
                <input type="text" id="prelead-search" placeholder="Search mobile/name..." class="search-box" onkeyup="loadPreLeads()" style="max-width:200px; padding:6px 10px; font-size:12px; border:1px solid #cbd5e1; border-radius:4px;">
            </div>
            <div class="card-body" style="padding:0; overflow-x:auto; margin-top: 15px;">
                <table class="quotation-list-table" id="preleads-table">
                    <thead>
                        <tr>
                            <th>NAME / COMPANY</th>
                            <th>MOBILE</th>
                            <th>SOURCE</th>
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
    async function loadPreLeads() {
        // API get_preleads automatically restricts results based on role (shows only assigned for Staff)
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
                <td>
                    <div style="font-weight:600; color:var(--text-primary);">${p.name}</div>
                    <div style="font-size:12px;color:var(--text-light);">${p.company_name || p.location || '-'}</div>
                </td>
                <td>${p.mobile}<br><span style="font-size:11px;color:#888;">${p.email || ''}</span></td>
                <td><span class="badge" style="background:#f1f5f9;color:#475569;">${p.source}</span></td>
                <td>
                    <select onchange="updatePreLeadStatus(${p.id}, this.value)" style="padding:4px; border-radius:4px; font-size:12px; border:1px solid var(--border);">
                        <option value="Not Contacted" ${p.status==='Not Contacted'?'selected':''}>Not Contacted</option>
                        <option value="Interested" ${p.status==='Interested'?'selected':''}>Interested</option>
                        <option value="Junk" ${p.status==='Junk'?'selected':''}>Junk</option>
                    </select>
                </td>
                <td>
                    <div style="display:flex;gap:5px;">
                        <button class="btn btn-secondary" onclick="openReminderModal('Pre-Lead', ${p.id})" style="padding:4px 8px;" title="Set Reminder">⏰</button>
                        <button class="btn btn-primary" onclick="promotePreLead(${p.id})" style="padding:4px 8px; font-size:12px;" title="Promote to Lead CRM"><i data-lucide="rocket" style="width:14px;height:14px;"></i> Promote</button>
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

    function editPreLead(id) {
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
                document.getElementById('pl_notes').value = p.notes || '';
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        loadPreLeads();
    });
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
