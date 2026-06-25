
                function handleLogin(e) {
                    e.preventDefault();
                    const fd = new FormData(e.target);
                    fetch('?api=login', {method: 'POST', body: fd})
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) location.reload();
                        else alert(data.error);
                    });
                }
            

                function logout() {
                    fetch('?api=logout').then(() => location.reload());
                }
            

                function loadUsersList() {
                    fetch('?api=get_users')
                    .then(res => res.json())
                    .then(data => {
                        const container = document.getElementById('users-list-container');
                        let html = '<table class="data-table"><thead><tr><th>Username</th><th>Role</th><th>Status</th><th>Actions</th></tr></thead><tbody>';
                        data.forEach(u => {
                            let statusBadge = u.is_active == 1 ? '<span class="status-badge status-new" style="background:#dcfce7; color:#166534;">Active</span>' : '<span class="status-badge status-closed-lost">Deactivated</span>';
                            let actionBtn = '';
                            let deleteBtn = '';
                            if (u.role !== 'Admin') {
                                actionBtn = u.is_active == 1 ? `<button type="button" class="btn btn-secondary" style="font-size: 11px; padding: 3px 6px;" onclick="toggleUserStatus(${u.id})">Deactivate</button>` : `<button type="button" class="btn btn-primary" style="font-size: 11px; padding: 3px 6px;" onclick="toggleUserStatus(${u.id})">Activate</button>`;
                                deleteBtn = `<button type="button" class="btn btn-danger" style="font-size: 11px; padding: 3px 6px; background: #fee2e2; color: #ef4444; border: 1px solid #fca5a5;" onclick="deleteUser(${u.id})">Delete</button>`;
                            }
                            
                            html += `<tr>
                                <td><strong>${u.username}</strong></td>
                                <td>${u.role}</td>
                                <td>${statusBadge}</td>
                                <td style="display:flex; gap: 5px;">${actionBtn} ${deleteBtn}</td>
                            </tr>`;
                        });
                        html += '</tbody></table>';
                        container.innerHTML = html;
                    });
                }

                function toggleUserStatus(id) {
                    if (!confirm("Are you sure you want to change this user's status?")) return;
                    let fd = new FormData(); fd.append('id', id);
                    fetch('?api=toggle_user_status', {method: 'POST', body: fd})
                    .then(r => r.json()).then(d => {
                        if(d.success) { showNotification(d.message, 'success'); loadUsersList(); }
                        else showNotification(d.error, 'error');
                    });
                }

                function deleteUser(id) {
                    if (!confirm("Are you sure you want to permanently DELETE this user?")) return;
                    let fd = new FormData(); fd.append('id', id);
                    fetch('?api=delete_user', {method: 'POST', body: fd})
                    .then(r => r.json()).then(d => {
                        if(d.success) { showNotification(d.message, 'success'); loadUsersList(); }
                        else showNotification(d.error, 'error');
                    });
                }

                function createUser(e) {
                    e.preventDefault();
                    const fd = new FormData(e.target);
                    fetch('?api=create_user', {method: 'POST', body: fd})
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            showNotification(data.message, 'success');
                            e.target.reset();
                            loadUsersList();
                        } else {
                            showNotification(data.error, 'error');
                        }
                    });
                }
                document.addEventListener('DOMContentLoaded', loadUsersList);
            

                function loadTemplatesList() {
                    fetch('?api=get_templates')
                    .then(res => res.json())
                    .then(data => {
                        window.globalEmailTemplates = data;
                        
                        // Populate templates list in Settings
                        const container = document.getElementById('templates-list-container');
                        if (data.length === 0) {
                            container.innerHTML = '<p style="color: var(--text-light); font-size: 0.9rem;">No templates saved yet.</p>';
                        } else {
                            let html = '<ul style="list-style:none; padding:0; margin:0;">';
                            data.forEach(t => {
                                let attachBadge = t.attachment_name ? `<span style="font-size: 11px; background: var(--secondary-light); color: var(--secondary); padding: 2px 6px; border-radius: 4px; margin-left: 8px;">📎 Attached File</span>` : '';
                                
                                let buttonsHtml = ``;
                                if (t.delete_requested == 1) {
                                    if (currentUser && currentUser.role === 'Admin') {
                                        buttonsHtml = `
                                            <div style="display:flex; gap:0.5rem;">
                                                <button type="button" onclick="approveDeleteTemplate(${t.id})" style="padding: 0.4rem 0.8rem; font-size: 12px; background: #fee2e2; color: #ef4444; border: 1px solid #fca5a5; border-radius: 6px; cursor: pointer;">✅ Approve</button>
                                                <button type="button" onclick="rejectDeleteTemplate(${t.id})" style="padding: 0.4rem 0.8rem; font-size: 12px; background: #f3f4f6; color: #4b5563; border: 1px solid #d1d5db; border-radius: 6px; cursor: pointer;">❌ Reject</button>
                                            </div>
                                        `;
                                    } else {
                                        buttonsHtml = `<span style="font-size:12px; color:#ef4444; border:1px solid #fca5a5; padding: 0.4rem 0.8rem; border-radius: 6px; background: #fee2e2;">🔴 Pending Admin Approval</span>`;
                                    }
                                } else {
                                    buttonsHtml = `<button type="button" class="btn btn-danger" style="padding: 0.4rem 0.8rem; font-size: 12px; background: #fee2e2; color: #ef4444; border: 1px solid #fca5a5; border-radius: 6px; cursor: pointer;" onclick="deleteTemplate(${t.id})">Delete</button>`;
                                }
                                
                                html += `<li style="padding: 1rem; border: 1px solid var(--border); border-radius: 6px; margin-bottom: 0.8rem; display: flex; justify-content: space-between; align-items: center;">
                                    <div>
                                        <strong style="display:block; cursor:pointer; color:var(--primary); text-decoration:underline;" onclick="showTemplatePreview(${t.id})">${t.template_name} (${t.type})</strong>
                                        <span style="font-size: 13px; color: var(--text-light);">Sub: ${t.subject}</span>
                                        ${attachBadge}
                                    </div>
                                    ${buttonsHtml}
                                </li>`;
                            });
                            html += '</ul>';
                            container.innerHTML = html;
                        }

                        // Populate dropdown in Send Email view
                        const dropdown = document.getElementById('email-template-select');
                        if (dropdown) {
                            dropdown.innerHTML = '<option value="" selected>-- Select a template to auto-fill --</option>';
                            data.forEach(t => {
                                dropdown.innerHTML += `<option value="${t.id}">${t.template_name} (${t.type})</option>`;
                            });
                        }
                    });
                }
                function saveTemplate(e) {
                    e.preventDefault();
                    const fd = new FormData(e.target);
                    fetch('?api=save_template', {method: 'POST', body: fd})
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            showNotification(data.message, 'success');
                            e.target.reset();
                            loadTemplatesList();
                        } else {
                            showNotification(data.error, 'error');
                        }
                    });
                }
                function deleteTemplate(id) {
                    if (!confirm('Are you sure you want to delete this template?')) return;
                    const fd = new FormData();
                    fd.append('id', id);
                    fetch('?api=delete_template', {method: 'POST', body: fd})
                    .then(() => {
                        showNotification('Template deleted', 'success');
                        loadTemplatesList();
                    });
                }
                
                function applyEmailTemplate() {
                    const select = document.getElementById('email-template-select');
                    const templateId = select.value;
                    const badge = document.getElementById('email-template-attachment-badge');
                    document.getElementById('email-template-id-hidden').value = templateId;
                    
                    if (!templateId) {
                        document.querySelector('input[name="subject"]').value = '';
                        if (window.quillEmailEditor) window.quillEmailEditor.root.innerHTML = '';
                        badge.style.display = 'none';
                        return;
                    }
                    
                    const t = window.globalEmailTemplates.find(x => x.id == templateId);
                    if (t) {
                        document.querySelector('input[name="subject"]').value = t.subject;
                        if (window.quillEmailEditor) window.quillEmailEditor.root.innerHTML = t.body;
                        document.querySelector('select[name="type"]').value = t.type;
                        
                        if (t.attachment_name) {
                            badge.textContent = `📎 Will automatically attach: ${t.attachment_name}`;
                            badge.style.display = 'block';
                        } else {
                            badge.style.display = 'none';
                        }
                    }
                }
                
                function loadPptsList() {
                    fetch('?api=get_ppts')
                    .then(res => res.json())
                    .then(data => {
                        const selectEl = document.getElementById('saved-ppt-select');
                        if (selectEl) {
                            let optionsHtml = '<option value="">-- Select Presentation --</option>';
                            data.forEach(p => {
                                optionsHtml += `<option value="${p.id}">${p.original_name}</option>`;
                            });
                            selectEl.innerHTML = optionsHtml;
                        }
                        
                        const container = document.getElementById('ppts-list-container');
                        if (container) {
                            if (data.length === 0) {
                                container.innerHTML = '<p style="color: var(--text-light); font-size: 0.9rem;">No presentations saved yet.</p>';
                            } else {
                                let html = '<ul style="list-style:none; padding:0; margin:0;">';
                                data.forEach(p => {
                                    let buttonsHtml = ``;
                                    if (p.delete_requested == 1) {
                                        if (currentUser && currentUser.role === 'Admin') {
                                            buttonsHtml = `
                                                <div style="display:flex; gap:0.5rem;">
                                                    <button type="button" onclick="approveDeletePpt(${p.id})" style="padding: 4px 8px; font-size: 11px; background: #fee2e2; color: #ef4444; border: 1px solid #fca5a5; border-radius: 6px; cursor: pointer;">✅ Approve</button>
                                                    <button type="button" onclick="rejectDeletePpt(${p.id})" style="padding: 4px 8px; font-size: 11px; background: #f3f4f6; color: #4b5563; border: 1px solid #d1d5db; border-radius: 6px; cursor: pointer;">❌ Reject</button>
                                                </div>
                                            `;
                                        } else {
                                            buttonsHtml = `<span style="font-size:11px; color:#ef4444; border:1px solid #fca5a5; padding: 4px 8px; border-radius: 6px; background: #fee2e2;">🔴 Pending</span>`;
                                        }
                                    } else {
                                        buttonsHtml = `<button type="button" class="btn btn-danger" style="padding: 4px 8px; font-size: 11px;" onclick="deletePpt(${p.id})">Delete</button>`;
                                    }

                                    html += `<li style="padding: 10px; border: 1px solid var(--border); margin-bottom: 5px; border-radius: 6px; display:flex; justify-content:space-between; align-items:center; background: var(--card-bg);">
                                        <div>
                                            <strong><a href="uploads/${p.filename}" target="_blank" style="color:var(--primary); text-decoration:underline;">${p.original_name}</a></strong> 
                                            <span style="font-size:11px; color:var(--text-muted); margin-left: 10px;">${p.filename}</span>
                                        </div>
                                        ${buttonsHtml}
                                    </li>`;
                                });
                                html += '</ul>';
                                container.innerHTML = html;
                            }
                        }
                    });
                }
                
                function savePpt(e) {
                    e.preventDefault();
                    const fd = new FormData(e.target);
                    fetch('?api=upload_ppt', {method:'POST', body:fd})
                    .then(res => res.json())
                    .then(data => {
                        if(data.success) {
                            showNotification(data.message, 'success');
                            e.target.reset();
                            loadPptsList();
                        } else showNotification(data.error, 'error');
                    });
                }
                
                function deletePpt(id) {
                    if(!confirm('Are you sure you want to delete this presentation?')) return;
                    let fd = new FormData(); fd.append('id', id);
                    fetch('?api=delete_ppt', {method:'POST', body:fd}).then(r=>r.json()).then(d=>{
                        showNotification('Presentation deleted', 'success');
                        loadPptsList();
                    });
                }
                
                document.addEventListener('DOMContentLoaded', () => {
                    loadTemplatesList();
                    loadPptsList();
                });
            

        // Init Global state references
        let companyProfile = null;
        let currentUser = null;
        let clientGrowthChart = null;
        let topClientsChart = null;
        let weeklyActivityChart = null;
        let quillEmailEditor = null;
        let crmClientList = []; // stores local copy of fetched search results
        window.globalEmailTemplates = [];

        // Template Preview & Delete Approval Logic
        function closeTemplatePreviewModal() {
            document.getElementById("template-preview-modal").style.display = "none";
        }
        function showTemplatePreview(id) {
            const t = window.globalEmailTemplates.find(x => x.id == id);
            if(!t) return;
            let bodyContent = `<strong>Subject:</strong> ${t.subject}\n\n${t.body}`;
            document.getElementById("template-preview-body").innerHTML = bodyContent;
            
            const footer = document.getElementById("template-preview-footer");
            if(t.attachment_name) {
                footer.style.display = "block";
                footer.innerHTML = `<a href="uploads/${t.attachment_name}" target="_blank" class="btn btn-secondary" style="text-decoration:none;">≡ƒôÄ Download Attachment</a>`;
            } else {
                footer.style.display = "none";
            }
            document.getElementById("template-preview-modal").style.display = "block";
        }

        async function approveDeleteTemplate(id) {
            if(!confirm("Permanently delete this template?")) return;
            let fd = new FormData(); fd.append("id", id);
            let res = await fetch("?api=approve_delete_template", {method:"POST", body:fd});
            let json = await res.json();
            showNotification(json.message || (json.error ? "Error" : "Success"), json.success ? "success" : "error");
            if(json.success) loadTemplatesList();
        }
        async function rejectDeleteTemplate(id) {
            let fd = new FormData(); fd.append("id", id);
            let res = await fetch("?api=reject_delete_template", {method:"POST", body:fd});
            let json = await res.json();
            showNotification(json.message || (json.error ? "Error" : "Success"), json.success ? "success" : "error");
            if(json.success) loadTemplatesList();
        }
        
        async function approveDeletePpt(id) {
            if(!confirm("Permanently delete this presentation?")) return;
            let fd = new FormData(); fd.append("id", id);
            let res = await fetch("?api=approve_delete_ppt", {method:"POST", body:fd});
            let json = await res.json();
            showNotification(json.message || (json.error ? "Error" : "Success"), json.success ? "success" : "error");
            if(json.success) loadPptsList();
        }
        async function rejectDeletePpt(id) {
            let fd = new FormData(); fd.append("id", id);
            let res = await fetch("?api=reject_delete_ppt", {method:"POST", body:fd});
            let json = await res.json();
            showNotification(json.message || (json.error ? "Error" : "Success"), json.success ? "success" : "error");
            if(json.success) loadPptsList();
        }

                async function initUserSelects() {
            if (currentUser && currentUser.role === 'Admin') {
                document.querySelectorAll('.admin-only-field').forEach(el => el.style.display = 'block');
                try {
                    const res = await fetch('?api=get_users');
                    const users = await res.json();
                    if(users && !users.error) {
                        let opts = '<option value="">-- Unassigned --</option>';
                        users.forEach(u => opts += `<option value="${u.username}">${u.username}</option>`);
                        document.querySelectorAll('.user-select').forEach(sel => sel.innerHTML = opts);
                    }
                } catch(e) {}
            }
        }
        document.addEventListener("DOMContentLoaded", initUserSelects);

        function openReminderModal(type, id) {
            document.getElementById('reminder-lead-type').value = type;
            document.getElementById('reminder-lead-id').value = id;
            document.getElementById('reminder-modal').style.display = 'flex';
        }
        function closeReminderModal() {
            document.getElementById('reminder-modal').style.display = 'none';
        }
        document.getElementById('reminder-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            let fd = new FormData(e.target);
            fd.append("api", "save_reminder");
            let res = await fetch("?", {method:"POST", body:fd});
            let json = await res.json();
            if(json.success) {
                showNotification("Reminder set successfully", "success");
                closeReminderModal();
            } else {
                showNotification(json.error || "Failed", "error");
            }
        });

        async function loadReminders() {
            try {
                let res = await fetch('?api=get_reminders');
                let data = await res.json();
                let tbody = document.querySelector("#reminders-table tbody");
                if(data.error) { tbody.innerHTML = "<tr><td colspan='6'>Failed to load</td></tr>"; return; }
                
                let html = "";
                data.forEach(r => {
                    let dateObj = new Date(r.remind_at);
                    let isPast = dateObj < new Date() && r.status !== 'Completed';
                    let color = isPast ? '#ef4444' : '#3b82f6';
                    
                    html += `<tr>
                        <td style="color:${color}; font-weight:600;">${dateObj.toLocaleString()}</td>
                        <td>${r.lead_type}</td>
                        <td>ID: ${r.lead_id} <br><span style="font-size:11px;color:#888;">${r.assigned_to}</span></td>
                        <td>${r.notes || '<span style="color:#ccc;">No notes</span>'}</td>
                        <td><span class="badge" style="background:${r.status==='Pending'?'#fee2e2':'#dcfce7'};color:${r.status==='Pending'?'#ef4444':'#166534'};">${r.status}</span></td>
                        <td>
                            ${r.status === 'Pending' ? `<button class="btn btn-secondary" onclick="completeReminder(${r.id})">✅ Done</button>` : ''}
                        </td>
                    </tr>`;
                });
                tbody.innerHTML = html;
            } catch(e) {}
        }
        
        async function completeReminder(id) {
            let fd = new FormData();
            fd.append("api", "complete_reminder");
            fd.append("id", id);
            let res = await fetch("?", {method:"POST", body:fd});
            let json = await res.json();
            if(json.success) loadReminders();
        }
        
        // Add hook to view switcher
        document.querySelectorAll(".menu-item").forEach(item => {
            item.addEventListener("click", () => {
                if(item.dataset.view === 'reminders') loadReminders();
            });
        });

        // Document Load entry triggers
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
            
            // Switch tabs dynamically
            initNavigation();
            
            // Render widgets on homepage
            loadDashboardStats();
            loadDashboardCharts();
            loadRecentActivities();
            
            // Warm up filter select lists
            loadFilterOptions();
            
            // Setup rich text editor
            quillEmailEditor = new Quill('#email-body-editor', {
                theme: 'snow',
                placeholder: 'Write your official pitch/proposals/quotation email here...',
                modules: {
                    toolbar: [
                        ['bold', 'italic', 'underline'],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        ['clean']
                    ]
                }
            });
            
            // Populate select lists after editor ready - only load if on relevant view
            if (document.getElementById('view-send-email')?.classList.contains('active') ||
                document.getElementById('view-create-quotation')?.classList.contains('active')) {
                refreshClientDropdowns();
            }
        });

        // Toggle mobile aside layout, view redirection and UI updates
        let activityPollInterval = null;
        function initNavigation() {
            document.querySelectorAll('.sidebar-menu .menu-item').forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    if (activityPollInterval) clearInterval(activityPollInterval);
                    
                    // Mark sidebar active
                    document.querySelectorAll('.sidebar-menu .menu-item').forEach(li => li.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Toggle visibility of panels
                    const targetView = this.getAttribute('data-view');
                    document.querySelectorAll('.view-container').forEach(view => view.classList.remove('active'));
                    
                    const activeViewEl = document.getElementById('view-' + targetView);
                    activeViewEl.classList.add('active');
                    
                    // Adjust Header Text Details
                    const titles = {
                        'dashboard': { title: 'Dashboard', sub: 'AuraCRM Operations Control Panel' },
                        'preleads': { title: 'Pre-Leads (Raw Data)', sub: 'Manage raw data and unverified prospects' },
                        'leads': { title: 'Lead Management', sub: '≡ƒÄ» Capture, track and convert leads into clients' },
                        'add-client': { title: 'Register Client Account', sub: '≡ƒöÆ Lock-in customer profile parameters permanently' },
                        'search-crm': { title: 'Search & Tracking Dashboard', sub: 'Interactive CRM conversion tracker and client card catalog' },
                        'send-email': { title: 'Communication Center', sub: 'Compose and dispatch simulated customer interaction emails' },
                        'create-quotation': { title: 'Quotation Builder Suite', sub: 'Create items proposals with instant Indian GST taxation logic' },
                        'quotation-list': { title: 'Invoice Ledgers', sub: 'Audit and track approved client quotations' },
                        'settings': { title: 'CRM Settings & Configurations', sub: 'Manage company profile, GST details, billing parameters, and default user' },
                        'activity-logs': { title: 'Activity Audit Logs', sub: 'System tracking of all staff actions and record updates' }
                    };
                    
                    document.getElementById('view-title').innerText = titles[targetView] ? titles[targetView].title : 'Configurations';
                    document.getElementById('view-subtitle').innerText = titles[targetView] ? titles[targetView].sub : '';
                    
                    // Refresh data based on view
                    if (targetView === 'dashboard') {
                        loadDashboardStats();
                        loadDashboardCharts();
                        loadRecentActivities();
                        activityPollInterval = setInterval(loadRecentActivities, 5000);
                    } else if (targetView === 'leads') {
                        loadLeads();
                    } else if (targetView === 'search-crm') {
                        loadFilterOptions();
                        triggerSearch();
                    } else if (targetView === 'send-email') {
                        refreshClientDropdowns();
                    } else if (targetView === 'create-quotation') {
                        refreshClientDropdowns();
                        resetQuotationForm();
                    } else if (targetView === 'quotation-list') {
                        loadQuotationList();
                    } else if (targetView === 'activity-logs') {
                        loadFullActivityLogs();
                        activityPollInterval = setInterval(loadFullActivityLogs, 5000);
                    }
                });
            });
        }

        // ==========================================
        //  TOAST SYSTEM
        // ==========================================
        function showNotification(message, icon = 'info') {
            if (message === 'SESSION_EXPIRED') {
                alert('Your session has expired because your account was logged in from another device.');
                window.location.reload();
                return;
            }
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = 'toast';
            
            let iconMarkup = `<i data-lucide="info" style="color: var(--primary);"></i>`;
            if (icon === 'success') iconMarkup = `<i data-lucide="check-circle" style="color: var(--success);"></i>`;
            else if (icon === 'error') iconMarkup = `<i data-lucide="alert-triangle" style="color: var(--danger);"></i>`;
            
            toast.innerHTML = `
                ${iconMarkup}
                <span>${message}</span>
            `;
            
            container.appendChild(toast);
            lucide.createIcons();
            
            setTimeout(() => {
                toast.style.animation = 'slideInRight 0.3s reverse forwards';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        // Indian Currency Formatter (Lakhs, Crores format helper)
        function formatIndianCurrency(amount) {
            const x = amount.toString().split('.');
            let lastThree = x[0].substring(x[0].length - 3);
            const otherSymbols = x[0].substring(0, x[0].length - 3);
            if (otherSymbols !== '') {
                lastThree = ',' + lastThree;
            }
            let res = otherSymbols.replace(/\B(?=(\d{2})+(?!\d))/g, ",") + lastThree;
            if (x.length > 1) {
                res += '.' + x[1].substring(0, 2);
            }
            return '&#8377;' + res;
        }

        // ==========================================
        //  DASHBOARD WIDGETS AND API PIPES
        // ==========================================
        async function loadDashboardStats() {
            try {
                const response = await fetch('?api=stats');
                const data = await response.json();
                
                document.getElementById('stat-total-clients').innerText = data.total_clients;
                document.getElementById('stat-emails-today').innerText = data.emails_today;
                document.getElementById('stat-quotes-month').innerText = data.quotes_this_month;
                document.getElementById('stat-pending-followups').innerText = data.pending_followups;
                document.getElementById('stat-total-val').innerText = formatIndianCurrency(data.total_quote_value);
                document.getElementById('stat-no-quotation').innerText = data.no_quotation_clients;
                if (document.getElementById('stat-active-staff')) {
                    document.getElementById('stat-active-staff').innerText = data.active_staff;
                }
            } catch (err) {
                showNotification('Failed to retrieve dashboard stats summary metrics.', 'error');
            }
        }

        async function loadDashboardCharts() {
            try {
                const response = await fetch('?api=charts_data');
                const data = await response.json();
                
                // 1. Client growth line Chart
                const growthLabels = data.growth.map(i => i.label);
                const growthValues = data.growth.map(i => i.value);
                
                if (clientGrowthChart) clientGrowthChart.destroy();
                const ctxGrowth = document.getElementById('chart-client-growth').getContext('2d');
                clientGrowthChart = new Chart(ctxGrowth, {
                    type: 'line',
                    data: {
                        labels: growthLabels,
                        datasets: [{
                            label: 'Registered Accounts',
                            data: growthValues,
                            borderColor: '#f97316',
                            backgroundColor: 'rgba(249, 115, 22, 0.08)',
                            fill: true,
                            tension: 0.4,
                            borderWidth: 3,
                            pointBackgroundColor: '#ea580c',
                            pointHoverRadius: 7
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { grid: { color: '#f1f5f9' }, ticks: { stepSize: 1 } },
                            x: { grid: { display: false } }
                        }
                    }
                });

                // 2. Custom Funnel builder UI
                const funnelContainer = document.getElementById('funnel-visualization-container');
                funnelContainer.innerHTML = '';
                
                // Calculate scale divisor
                const maxVal = Math.max(...data.funnel.map(f => f.count), 1);
                
                data.funnel.forEach(f => {
                    const pct = maxVal > 0 ? (f.count / maxVal) * 100 : 0;
                    const stageEl = document.createElement('div');
                    stageEl.className = 'funnel-stage';
                    stageEl.innerHTML = `
                        <div class="funnel-label">${f.stage}</div>
                        <div class="funnel-bar-wrapper">
                            <div class="funnel-bar" style="width: ${pct}%">
                                <span class="funnel-count">${f.count} Accounts</span>
                            </div>
                        </div>
                    `;
                    funnelContainer.appendChild(stageEl);
                });

                // 3. Top clients chart
                const topLabels = data.top_clients.map(i => i.name);
                const topValues = data.top_clients.map(i => i.value);
                
                if (topClientsChart) topClientsChart.destroy();
                const ctxTop = document.getElementById('chart-top-clients').getContext('2d');
                topClientsChart = new Chart(ctxTop, {
                    type: 'bar',
                    data: {
                        labels: topLabels,
                        datasets: [{
                            data: topValues,
                            backgroundColor: '#f97316',
                            hoverBackgroundColor: '#ea580c',
                            borderRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { grid: { color: '#f1f5f9' } },
                            x: { grid: { display: false } }
                        }
                    }
                });

                // 4. Weekly Activity summary
                const actLabels = data.activity_weekly.map(i => i.label);
                const actValues = data.activity_weekly.map(i => i.value);
                
                if (weeklyActivityChart) weeklyActivityChart.destroy();
                const ctxAct = document.getElementById('chart-weekly-activity').getContext('2d');
                weeklyActivityChart = new Chart(ctxAct, {
                    type: 'bar',
                    data: {
                        labels: actLabels,
                        datasets: [{
                            data: actValues,
                            backgroundColor: '#0f172a',
                            borderRadius: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { grid: { color: '#f1f5f9' }, ticks: { stepSize: 1 } },
                            x: { grid: { display: false } }
                        }
                    }
                });

            } catch (err) {
                console.error(err);
                showNotification('Failed to generate interactive dashboard charts.', 'error');
            }
        }

        async function loadFullActivityLogs() {
            try {
                const response = await fetch('?api=get_activity_logs');
                const data = await response.json();
                const container = document.getElementById('full-activity-logs-tbody');
                if (data.error) {
                    container.innerHTML = `<tr><td colspan="2" style="text-align:center;color:red;">Error: ${data.error}</td></tr>`;
                    return;
                }
                if (!Array.isArray(data)) {
                    container.innerHTML = `<tr><td colspan="2" style="text-align:center;color:red;">API Error: Expected array, got ${typeof data}</td></tr>`;
                    return;
                }
                if (data.length === 0) {
                    container.innerHTML = '<tr><td colspan="2" style="text-align:center;">No activities found</td></tr>';
                    return;
                }
                let html = '';
                data.forEach(act => {
                    let descHtml = act.description || '';
                    // highlight username
                    descHtml = descHtml.replace(/^\[(.*?)\]/, '<span class="badge" style="background:var(--primary);color:white;">$1</span>');
                    html += `<tr>
                        <td style="white-space:nowrap; color:var(--text-light); font-size:12px;">${act.created_at_formatted || ''}</td>
                        <td>${descHtml}</td>
                    </tr>`;
                });
                container.innerHTML = html;
            } catch (e) {
                console.error(e);
                const container = document.getElementById('full-activity-logs-tbody');
                if(container) container.innerHTML = `<tr><td colspan="2" style="text-align:center;color:red;">JS Exception: ${e.message}</td></tr>`;
            }
        }

        async function loadRecentActivities() {
            try {
                let userFilter = "";
                let dateFilter = "0";
                const uEl = document.getElementById('feed-user-filter');
                const dEl = document.getElementById('feed-date-filter');
                if(uEl) userFilter = uEl.value;
                if(dEl) dateFilter = dEl.value;
                
                const response = await fetch(`?api=recent_activities&user=${userFilter}&days=${dateFilter}`);
                const data = await response.json();
                const container = document.getElementById('dashboard-activity-feed');
                
                container.innerHTML = '';
                if (data.length === 0) {
                    container.innerHTML = '<div style="color: var(--text-light); text-align: center; padding: 20px;">No activities logged yet.</div>';
                    return;
                }
                
                data.forEach(act => {
                    const item = document.createElement('div');
                    item.className = 'activity-item';
                    item.innerHTML = `
                        <div class="activity-bullet"></div>
                        <div class="activity-content">
                            <div class="activity-text">${act.description}</div>
                            <div class="activity-time">${act.time_formatted}</div>
                        </div>
                    `;
                    container.appendChild(item);
                });
            } catch (err) {
                showNotification('Failed to sync recent activities.', 'error');
            }
        }

        // ==========================================
        //  CLIENT REGISTRATION MODULE (No Edit/Delete rule)
        // ==========================================
        async function saveClient(event) {
            event.preventDefault();
            const form = document.getElementById('client-registration-form');
            const formData = new FormData(form);
            
            try {
                const response = await fetch('?api=add_client', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (response.ok && data.success) {
                    showNotification(data.message, 'success');
                    form.reset();
                    refreshClientDropdowns();
                    // Auto redirect to tracker
                    setTimeout(() => {
                        document.querySelector('[data-view="search-crm"]').click();
                    }, 500);
                } else {
                    showNotification(data.error || 'Registration failed.', 'error');
                }
            } catch (err) {
                showNotification('Connection failure in client registration.', 'error');
            }
        }

        // Populate unique fields for searching & filtering
        async function loadFilterOptions() {
            try {
                const res = await fetch('?api=get_unique_filters');
                const data = await res.json();
                
                const citySelect = document.getElementById('filter-city');
                citySelect.innerHTML = '<option value="">All Cities</option>';
                data.cities.forEach(c => {
                    citySelect.innerHTML += `<option value="${c}">${c}</option>`;
                });
                
                const addedSelect = document.getElementById('filter-added-by');
                addedSelect.innerHTML = '<option value="">All Staff</option>';
                data.added_by.forEach(u => {
                    addedSelect.innerHTML += `<option value="${u}">${u}</option>`;
                });
            } catch (err) {
                console.error('Filter synchronization failure', err);
            }
        }

        // ==========================================
        //  MODULE 2: COMPLETE CRM TRACKING & SEARCH
        // ==========================================
        function toggleFilterDrawer() {
            document.getElementById('crm-filters-drawer').classList.toggle('active');
        }

        async function triggerSearch() {
            const query = document.getElementById('search-query').value;
            const status = document.getElementById('filter-status').value;
            const priority = document.getElementById('filter-priority').value;
            const city = document.getElementById('filter-city').value;
            const addedBy = document.getElementById('filter-added-by').value;
            const dateStart = document.getElementById('filter-date-start').value;
            const dateEnd = document.getElementById('filter-date-end').value;
            
            const params = new URLSearchParams({
                api: 'search_clients',
                query: query,
                status: status,
                priority: priority,
                city: city,
                added_by: addedBy,
                date_start: dateStart,
                date_end: dateEnd
            });
            
            try {
                const response = await fetch('?' + params.toString());
                const clients = await response.json();
                crmClientList = clients;
                
                const container = document.getElementById('crm-client-list');
                container.innerHTML = '';
                
                if (clients.length === 0) {
                    container.innerHTML = '<div style="background: white; border: 1px solid #e2e8f0; text-align: center; color: var(--text-light); padding: 40px; border-radius: var(--radius-md);">No records match criteria.</div>';
                    return;
                }
                
                clients.forEach(c => {
                    const card = document.createElement('div');
                    card.className = 'client-card';
                    card.id = 'crm-client-card-' + c.id;
                    card.onclick = () => selectClientCard(c.id);
                    
                    const priorityClass = 'badge-' + c.priority.toLowerCase();
                    const statusClass = c.overall_status.toLowerCase().replace(' ', '');
                    
                    card.innerHTML = `
                        <div class="client-card-header">
                            <span class="client-card-title">${c.company_name}</span>
                            <span class="badge ${priorityClass}">${c.priority}</span>
                        </div>
                        <div class="client-card-meta">
                            <i data-lucide="user" style="width: 12px; height: 12px;"></i>
                            <span>${c.contact_name} ΓÇö ${c.designation || 'Client Contact'}</span>
                        </div>
                        <div class="client-card-meta">
                            <i data-lucide="phone" style="width: 12px; height: 12px;"></i>
                            <span>${c.mobile}</span>
                        </div>
                        <div class="client-card-meta">
                            <i data-lucide="map-pin" style="width: 12px; height: 12px;"></i>
                            <span>${c.city}, ${c.state}</span>
                        </div>
                        <div style="display:flex; justify-content: space-between; align-items: center; margin-top: 12px; border-top: 1px dashed #e2e8f0; padding-top: 8px;">
                            <span class="badge-status ${statusClass}">${c.overall_status}</span>
                            <span style="font-size: 11px; color: var(--text-muted);">By: ${c.added_by}</span>
                        </div>
                    `;
                    container.appendChild(card);
                });
                lucide.createIcons();
            } catch (err) {
                showNotification('CRM Search request failed.', 'error');
            }
        }

        async function selectClientCard(id) {
            // Unselect all cards
            document.querySelectorAll('.client-card').forEach(el => el.classList.remove('selected'));
            
            const selectedCard = document.getElementById('crm-client-card-' + id);
            if (selectedCard) selectedCard.classList.add('selected');
            
            try {
                const response = await fetch(`?api=client_details&id=${id}`);
                const c = await response.json();
                
                const pane = document.getElementById('crm-detail-pane');
                pane.innerHTML = '';
                
                // Pipeline tracker checklist dates math
                const pitchDate = c.pitch_sent ? formatDate(c.pitch_sent) : 'Not Sent';
                const pptDate = c.ppt_sent ? formatDate(c.ppt_sent) : 'Not Shared';
                const mailDate = c.mail_sent ? formatDate(c.mail_sent) : 'Not Sent';
                const quoteDate = c.quotation_sent ? formatDate(c.quotation_sent) : 'No Quotation';
                
                const stepsClassList = {
                    pitch: c.pitch_sent ? 'completed' : '',
                    ppt: c.ppt_sent ? 'completed' : '',
                    mail: c.mail_sent ? 'completed' : '',
                    quote: c.quotation_sent ? 'completed' : ''
                };
                
                // Active highlighting
                if (c.overall_status === 'Contacted') stepsClassList.pitch = 'active';
                if (c.overall_status === 'In Negotiation') stepsClassList.quote = 'active';
                
                // Construct quotes summary markup
                let quotesHtml = '';
                if (c.quotations && c.quotations.length > 0) {
                    quotesHtml = c.quotations.map(q => {
                        const amt = formatIndianCurrency(q.total_amount);
                        const statusClass = q.status.toLowerCase();
                        return `
                            <div style="display:flex; justify-content: space-between; align-items:center; background:#f8fafc; padding: 10px; border-radius: var(--radius-sm); border:1px solid #e2e8f0; margin-bottom: 8px;">
                                <div>
                                    <strong style="color:var(--primary); font-size:13.5px;">${q.quotation_number}</strong>
                                    <span style="color: var(--text-muted); font-size:12px; margin-left: 10px;">Created on: ${formatDate(q.created_at)}</span>
                                </div>
                                <div style="display:flex; align-items:center; gap: 12px;">
                                    <strong style="font-size:13.5px;">${amt}</strong>
                                    <span class="badge-status ${q.status.toLowerCase()}">${q.status}</span>
                                </div>
                            </div>
                        `;
                    }).join('');
                } else {
                    quotesHtml = '<div style="color: var(--text-light); text-align:center; padding: 20px; font-size:13px;">No quotations generated yet for this company.</div>';
                }

                // Construct communications list
                let commsHtml = '';
                if (c.communications_logs && c.communications_logs.length > 0) {
                    commsHtml = c.communications_logs.map(log => `
                        <div class="history-item">
                            <div class="history-item-header">
                                <span style="color: var(--primary); font-weight:700;">${log.type}</span>
                                <span style="color: var(--text-muted);">${formatDate(log.sent_at)}</span>
                            </div>
                            <div class="history-item-body">
                                <strong>Subject:</strong> ${log.subject}<br>
                                <span style="font-size:11px; color: var(--text-muted);">Sent by: ${log.sent_by}</span>
                            </div>
                        </div>
                    `).join('');
                } else {
                    commsHtml = '<div style="color: var(--text-light); text-align:center; padding: 20px; font-size:13px;">No communication entries logged.</div>';
                }

                pane.innerHTML = `
                    <div class="detail-header">
                        <div>
                            <div class="detail-company-title">${c.company_name}</div>
                            <div style="font-size: 13.5px; color: var(--text-muted); margin-top: 4px;">Sector: ${c.industry_sector || 'N/A'} | Type: ${c.business_type}</div>
                        </div>
                        <div style="text-align: right;">
                            <span class="badge-status ${c.overall_status.toLowerCase().replace(' ', '')}" style="font-size: 13px; padding: 6px 14px;">${c.overall_status}</span>
                            <div style="font-size:11px; color: var(--text-light); margin-top: 6px;">Added by ${c.added_by}</div>
                        </div>
                    </div>
                    
                    <!-- CRM Funnel Checklist -->
                    <div class="detail-block-title">Pipeline Tracking Status</div>
                    <div class="pipeline-tracker">
                        <div class="pipeline-step ${stepsClassList.pitch}">
                            <div class="pipeline-icon-circle"><i data-lucide="${c.pitch_sent ? 'check':'mail'}" style="width:14px;"></i></div>
                            <span class="pipeline-step-label">Pitch Sent</span>
                            <span class="pipeline-step-date">${pitchDate}</span>
                        </div>
                        <div class="pipeline-step ${stepsClassList.ppt}">
                            <div class="pipeline-icon-circle"><i data-lucide="${c.ppt_sent ? 'check':'file-presentation'}" style="width:14px;"></i></div>
                            <span class="pipeline-step-label">PPT Shared</span>
                            <span class="pipeline-step-date">${pptDate}</span>
                        </div>
                        <div class="pipeline-step ${stepsClassList.mail}">
                            <div class="pipeline-icon-circle"><i data-lucide="${c.mail_sent ? 'check':'sparkles'}" style="width:14px;"></i></div>
                            <span class="pipeline-step-label">Custom Mail</span>
                            <span class="pipeline-step-date">${mailDate}</span>
                        </div>
                        <div class="pipeline-step ${stepsClassList.quote}">
                            <div class="pipeline-icon-circle"><i data-lucide="${c.quotation_sent ? 'check':'file-text'}" style="width:14px;"></i></div>
                            <span class="pipeline-step-label">Quote Sent</span>
                            <span class="pipeline-step-date">${quoteDate}</span>
                        </div>
                    </div>

                    <!-- Client Detail Info Blocks -->
                    <div class="detail-grid">
                        <div>
                            <div class="detail-block-title">Company Info</div>
                            <div class="detail-field"><label>GSTIN:</label> <span>${c.gstin || 'Not Available'}</span></div>
                            <div class="detail-field"><label>PAN No:</label> <span>${c.pan || 'Not Available'}</span></div>
                            <div class="detail-field"><label>Website:</label> <span>${c.website ? `<a href="${c.website}" target="_blank" style="color:var(--primary);">${c.website}</a>` : 'N/A'}</span></div>
                            <div class="detail-field"><label>Turnover:</label> <span>${c.turnover || 'N/A'}</span></div>
                            <div class="detail-field"><label>Employees:</label> <span>${c.employees || 'N/A'}</span></div>
                        </div>
                        <div>
                            <div class="detail-block-title">Key Contact Details</div>
                            <div class="detail-field"><label>Contact Name:</label> <span>${c.contact_name}</span></div>
                            <div class="detail-field"><label>Role:</label> <span>${c.designation || 'N/A'}</span></div>
                            <div class="detail-field"><label>Mobile:</label> <span>${c.mobile}</span></div>
                            <div class="detail-field"><label>WhatsApp:</label> <span>${c.whatsapp || 'N/A'}</span></div>
                            <div class="detail-field"><label>Email:</label> <span><a href="mailto:${c.email}" style="color:var(--primary);">${c.email}</a></span></div>
                        </div>
                    </div>

                    <div style="margin-bottom: 24px;">
                        <div class="detail-block-title">Address coordinates</div>
                        <div style="font-size: 13.5px; line-height:1.5;">
                            ${c.address_line1}, ${c.address_line2 ? c.address_line2 + ',' : ''}<br>
                            ${c.city}, ${c.state} - ${c.pincode}, ${c.country}
                        </div>
                    </div>
                    
                    <!-- Quick action buttons -->
                    <div style="display:flex; gap:10px; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid #f1f5f9;">
                        <button class="btn btn-primary" onclick="initiateEmailForClient(${c.id}, '${c.company_name}')"><i data-lucide="mail"></i> Send Mail</button>
                        <button class="btn btn-secondary" onclick="initiateQuotationForClient(${c.id})"><i data-lucide="file-signature"></i> Create Quote</button>
                    </div>

                    <!-- Tabs Details (History & Quotes) -->
                    <div class="tabs">
                        <div class="tab active" onclick="switchDetailTab('history')" id="tab-btn-history">Communication Logs</div>
                        <div class="tab" onclick="switchDetailTab('quotes')" id="tab-btn-quotes">Quotations Created</div>
                    </div>

                    <div class="tab-content active" id="tab-content-history">
                        <div class="history-list">
                            ${commsHtml}
                        </div>
                    </div>
                    
                    <div class="tab-content" id="tab-content-quotes">
                        <div style="max-height: 250px; overflow-y:auto;">
                            ${quotesHtml}
                        </div>
                    </div>
                `;
                lucide.createIcons();
            } catch (err) {
                console.error(err);
                showNotification('Could not load client CRM profile details.', 'error');
            }
        }

        // Helper Tab Switches inside profile pane
        function switchDetailTab(tabName) {
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            
            document.getElementById('tab-btn-' + tabName).classList.add('active');
            document.getElementById('tab-content-' + tabName).classList.add('active');
        }

        // Date String cleanups helper
        function formatDate(sqlDate) {
            if (!sqlDate) return 'N/A';
            const date = new Date(sqlDate);
            return date.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
        }

        // ==========================================
        //  RECIPIENT DROPDOWN POPULATOR
        // ==========================================
        async function refreshClientDropdowns() {
            try {
                const response = await fetch('?api=search_clients');
                if (!response.ok) return; // silently skip if session expired or unauthorized
                const clients = await response.json();
                if (!Array.isArray(clients)) return;
                
                const emailSelect = document.getElementById('email-to-select');
                const quoteSelect = document.getElementById('quote-client-select');
                
                const optHtml = clients.map(c => `<option value="${c.id}">${c.company_name} (${c.contact_name})</option>`).join('');
                
                if (emailSelect) emailSelect.innerHTML = '<option value="" disabled selected>Choose client account...</option>' + optHtml;
                if (quoteSelect) quoteSelect.innerHTML = '<option value="" disabled selected>Choose client account...</option>' + optHtml;
            } catch (err) {
                // Silent fail ΓÇö do not show error toast on initial load
                console.warn('refreshClientDropdowns:', err);
            }
        }

        // ==========================================
        //  EMAIL DISPATCH (Simulated)
        // ==========================================
        function initiateEmailForClient(clientId, companyName) {
            document.querySelector('[data-view="send-email"]').click();
            setTimeout(() => {
                document.getElementById('email-to-select').value = clientId;
            }, 100);
        }

        async function dispatchEmail(e) {
            e.preventDefault();
            
            // Sync editor contents to hidden payload field
            const hiddenBodyInput = document.getElementById('email-body-hidden');
            hiddenBodyInput.value = quillEmailEditor.getSemanticHTML();
            
            const form = document.getElementById('email-sender-form');
            const formData = new FormData(form);
            
            try {
                const response = await fetch('?api=send_email', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (response.ok && data.success) {
                    showNotification(data.message, 'success');
                    form.reset();
                    quillEmailEditor.setText(''); // Empty editor
                    
                    // Redirect back to CRM search tracker
                    setTimeout(() => {
                        document.querySelector('[data-view="search-crm"]').click();
                    }, 500);
                } else {
                    showNotification(data.error || 'Simulated mail routing failed.', 'error');
                }
            } catch (err) {
                showNotification('Email sending operation failed.', 'error');
            }
        }

        // ==========================================
        //  QUOTATION BUILDER & GST CALCULATOR
        // ==========================================
        function initiateQuotationForClient(clientId) {
            document.querySelector('[data-view="create-quotation"]').click();
            setTimeout(() => {
                document.getElementById('quote-client-select').value = clientId;
                autofillQuoteClient();
            }, 100);
        }

        async function autofillQuoteClient() {
            const clientId = document.getElementById('quote-client-select').value;
            if (!clientId) return;
            
            try {
                const response = await fetch(`?api=client_details&id=${clientId}`);
                const c = await response.json();
                
                document.getElementById('quote-client-details-card').style.display = 'block';
                document.getElementById('qc-company').innerText = c.company_name;
                document.getElementById('qc-gstin').innerText = c.gstin || 'No GSTIN Entered';
                document.getElementById('qc-email').innerText = c.email;
                document.getElementById('qc-address').innerText = `${c.address_line1}, ${c.city}, ${c.state} - ${c.pincode}`;
            } catch (err) {
                showNotification('Autofill metadata mapping failed.', 'error');
            }
        }

        function resetQuotationForm() {
            document.getElementById('quotation-builder-form').reset();
            document.getElementById('quote-items-tbody').innerHTML = '';
            document.getElementById('quote-client-details-card').style.display = 'none';
            document.getElementById('quote-number-display').value = 'Auto-generated on Save';
            
            // Add single initial item row
            addQuotationRow();
            calculateQuotationTotals();
        }

        function addQuotationRow() {
            const tbody = document.getElementById('quote-items-tbody');
            const rowId = 'row-' + Date.now();
            
            const tr = document.createElement('tr');
            tr.id = rowId;
            tr.innerHTML = `
                <td><input type="text" placeholder="Item/Service name description..." required class="item-name"></td>
                <td><input type="number" min="1" value="1" required class="item-qty" oninput="calculateRowMath('${rowId}')"></td>
                <td><input type="number" min="0.01" step="0.01" placeholder="0.00" required class="item-rate" oninput="calculateRowMath('${rowId}')"></td>
                <td><input type="text" readonly value="&#8377;0.00" class="item-taxval" style="background:#f1f5f9; font-weight: 500;"></td>
                <td>
                    <select class="item-gst" onchange="calculateRowMath('${rowId}')">
                        <option value="0">0% Exempt</option>
                        <option value="5">5% SGST+CGST</option>
                        <option value="12">12% Standard</option>
                        <option value="18" selected>18% Standard</option>
                        <option value="28">28% Premium</option>
                    </select>
                </td>
                <td><button type="button" class="delete-row-btn" onclick="removeQuotationRow('${rowId}')"><i data-lucide="trash-2" style="width:16px;"></i></button></td>
            `;
            
            tbody.appendChild(tr);
            lucide.createIcons();
            calculateQuotationTotals();
        }

        function removeQuotationRow(rowId) {
            const row = document.getElementById(rowId);
            const tbody = document.getElementById('quote-items-tbody');
            
            if (tbody.children.length > 1) {
                row.remove();
                calculateQuotationTotals();
            } else {
                showNotification('At least one item line must exist in quotes.', 'warning');
            }
        }

        function calculateRowMath(rowId) {
            const row = document.getElementById(rowId);
            const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
            const rate = parseFloat(row.querySelector('.item-rate').value) || 0;
            
            const taxVal = qty * rate;
            row.querySelector('.item-taxval').value = formatIndianCurrency(taxVal);
            
            calculateQuotationTotals();
        }

        function calculateQuotationTotals() {
            let subtotal = 0;
            let totalGst = 0;
            
            document.querySelectorAll('#quote-items-tbody tr').forEach(row => {
                const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
                const rate = parseFloat(row.querySelector('.item-rate').value) || 0;
                const gstRate = parseFloat(row.querySelector('.item-gst').value) || 0;
                
                const taxVal = qty * rate;
                const gstVal = taxVal * (gstRate / 100);
                
                subtotal += taxVal;
                totalGst += gstVal;
            });
            
            const grandTotal = subtotal + totalGst;
            
            document.getElementById('quote-subtotal').innerText = formatIndianCurrency(subtotal);
            document.getElementById('quote-gst').innerText = formatIndianCurrency(totalGst);
            document.getElementById('quote-grand-total').innerText = formatIndianCurrency(grandTotal);
        }

        async function saveQuotation(e) {
            e.preventDefault();
            
            const clientId = document.getElementById('quote-client-select').value;
            if (!clientId) {
                showNotification('Please select a client account.', 'warning');
                return;
            }
            
            // Gather items matrix
            const items = [];
            let errorFlag = false;
            
            let subtotal = 0;
            let totalGst = 0;
            
            document.querySelectorAll('#quote-items-tbody tr').forEach(row => {
                const name = row.querySelector('.item-name').value.trim();
                const qty = parseInt(row.querySelector('.item-qty').value) || 0;
                const rate = parseFloat(row.querySelector('.item-rate').value) || 0;
                const gstRate = parseFloat(row.querySelector('.item-gst').value) || 0;
                
                if (name === '' || qty <= 0 || rate <= 0) {
                    errorFlag = true;
                }
                
                const taxVal = qty * rate;
                const gstVal = taxVal * (gstRate / 100);
                
                subtotal += taxVal;
                totalGst += gstVal;
                
                items.push({
                    name: name,
                    qty: qty,
                    rate: rate,
                    gst_rate: gstRate,
                    total: taxVal + gstVal
                });
            });
            
            if (errorFlag) {
                showNotification('Please verify all row descriptions, rates, and values.', 'warning');
                return;
            }
            
            const grandTotal = subtotal + totalGst;
            
            const payload = new URLSearchParams({
                client_id: clientId,
                subtotal: subtotal.toFixed(2),
                gst_amount: totalGst.toFixed(2),
                total_amount: grandTotal.toFixed(2),
                items_json: JSON.stringify(items)
            });
            
            try {
                const response = await fetch('?api=save_quotation', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: payload.toString()
                });
                
                const data = await response.json();
                
                if (response.ok && data.success) {
                    showNotification(data.message, 'success');
                    
                    // Direct redirect to ledgers page
                    setTimeout(() => {
                        document.querySelector('[data-view="quotation-list"]').click();
                    }, 500);
                } else {
                    showNotification(data.error || 'Failed to save quotation.', 'error');
                }
            } catch (err) {
                showNotification('Quotation save error.', 'error');
            }
        }

        // ==========================================
        //  QUOTATION LEDGER AND AUDIT PIPES
        // ==========================================
        // ==========================================
        //  LEAD MANAGEMENT FUNCTIONS
        // ==========================================

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
            'Hot':  '<span style="background:#fee2e2;color:#ef4444;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600;">≡ƒö┤ Hot</span>',
            'Warm': '<span style="background:#fef9c3;color:#ca8a04;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600;">≡ƒƒí Warm</span>',
            'Cold': '<span style="background:#dbeafe;color:#2563eb;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600;">≡ƒö╡ Cold</span>'
        };

        // ==========================================
        // PRE-LEADS JAVASCRIPT LOGIC
        // ==========================================
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
                showNotification("≡ƒÜÇ " + data.message, 'success'); 
                loadPreLeads(); 
                loadLeads(); // refresh main leads
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
            // Re-using the bulk modal but setting an indicator it's for pre-leads
            document.getElementById('bulk-upload-modal').style.display = 'flex';
            // We set a global variable to indicate destination
            window.bulkUploadDestination = 'pre_leads';
        }

        // Run loadPreLeads periodically or when clicking the tab
        document.querySelector('[data-view="preleads"]')?.addEventListener('click', () => {
            loadPreLeads();
        });

        async function loadLeads() {
            const search   = document.getElementById('lead-search')?.value || '';
            const stage    = document.getElementById('lead-filter-stage')?.value || '';
            const priority = document.getElementById('lead-filter-priority')?.value || '';
            const params   = new URLSearchParams({ api: 'get_leads', search, stage, priority });

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
                    tbody.innerHTML = '<tr><td colspan="7" style="padding:40px;text-align:center;color:var(--text-light);">No leads found. Add your first lead using the form!</td></tr>';
                    return;
                }
                tbody.innerHTML = leads.map(l => {
                    const col = STAGE_COLORS[l.stage] || '#64748b';
                    return `<tr style="border-bottom:1px solid var(--border);transition:background .15s;" onmouseover="this.style.background='var(--bg-color)'" onmouseout="this.style.background=''">
                        <td style="padding:12px;">
                            <div style="font-weight:600;color:var(--text);">${l.lead_name}</div>
                            <div style="font-size:11px;color:var(--text-light);">${l.company_name || 'ΓÇö'}</div>
                        </td>
                        <td style="padding:12px;font-size:13px;">${l.mobile}</td>
                        <td style="padding:12px;font-size:12px;color:var(--text-light);">${l.lead_source}</td>
                        <td style="padding:12px;">${PRIORITY_BADGE[l.priority] || l.priority}</td>
                        <td style="padding:12px;">
                            <select onchange="quickUpdateStage(${l.id}, this.value)" style="padding:3px 8px;border:none;border-radius:20px;font-size:12px;font-weight:600;background:${col}20;color:${col};cursor:pointer;outline:none;">
                                ${['New Lead','Contacted','Interested','Proposal Sent','Negotiation','Won','Lost'].map(s =>
                                    `<option value="${s}" ${l.stage===s?'selected':''}>${s}</option>`
                                ).join('')}
                            </select>
                        </td>
                        <td style="padding:12px;font-size:12px;color:var(--text-light);">${l.assigned_to || 'ΓÇö'}</td>
                        <td style="padding:12px;">
                            <div style="display:flex;gap:6px;">
                                ${currentUser && currentUser.role === 'Admin' ? `<button class="btn btn-secondary" style="padding:4px 10px;font-size:11px;" onclick="editLead(${l.id})" title="Edit">Γ£Å∩╕Å</button>` : ''}
                                <button class="btn btn-secondary" style="padding:4px 10px;font-size:11px;background:#dcfce7;color:#166534;border:none;" onclick="convertToClient(${l.id})" title="Convert to Client">≡ƒöä</button>
                                ${currentUser && currentUser.role === 'Admin' ? `<button class="btn btn-danger" style="padding:4px 10px;font-size:11px;" onclick="deleteLead(${l.id})" title="Delete">≡ƒùæ∩╕Å</button>` : ''}
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
                console.log('editLead called for id:', id);
                const res = await fetch(`?api=get_lead_detail&id=${id}`);
                const text = await res.text();
                console.log('editLead API response:', text);
                const l = JSON.parse(text);
                
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
                document.getElementById('lf-assigned').value = l.assigned_to || '';
                document.getElementById('lf-notes').value    = l.notes || '';
                
                document.getElementById('lead-form-title').innerText = 'Γ£Å∩╕Å Edit Lead';
                document.getElementById('lead-submit-btn').innerText = 'Update Lead';
                document.getElementById('lead-form').scrollIntoView({ behavior: 'smooth', block: 'start' });
            } catch(err) {
                console.error('editLead error:', err);
                showNotification('Could not load lead details. Check console.', 'error');
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
                // Switch to Add Client view and pre-fill fields
                document.querySelector('.menu-item[data-view="add-client"]').click();
                setTimeout(() => {
                    const setVal = (sel, val) => { const el = document.querySelector(sel); if(el && val) el.value = val; };
                    setVal('#client-registration-form input[name="contact_name"]', l.lead_name);
                    setVal('#client-registration-form input[name="company_name"]', l.company_name);
                    setVal('#client-registration-form input[name="mobile"]',       l.mobile);
                    setVal('#client-registration-form input[name="email"]',        l.email);
                    setVal('#client-registration-form select[name="lead_source"]', l.lead_source);
                    setVal('#client-registration-form select[name="priority"]',    l.priority);
                    if (l.location) {
                        setVal('#client-registration-form input[name="city"]', l.location);
                    }
                    showNotification('Lead data pre-filled! Complete the client registration form.', 'info');
                }, 400);
            });
        }

        function resetLeadForm() {
            document.getElementById('lead-form').reset();
            document.getElementById('lead-id-hidden').value = '';
            document.getElementById('lead-form-title').innerText = 'Γ₧ò New Lead';
            document.getElementById('lead-submit-btn').innerText = 'Save Lead';
        }

        async function loadQuotationList() {
            const search = document.getElementById('quote-search').value;
            const status = document.getElementById('quote-filter-status').value;
            
            const params = new URLSearchParams({
                api: 'quotation_list',
                search: search,
                status: status
            });
            
            try {
                const res = await fetch('?' + params.toString());
                if (!res.ok) return; // silently skip if unauthorized
                const data = await res.json();
                if (!data || !data.summary) return;
                
                // Set summaries
                document.getElementById('qs-total').innerText = formatIndianCurrency(data.summary.total_value);
                document.getElementById('qs-pending').innerText = formatIndianCurrency(data.summary.pending_value);
                document.getElementById('qs-approved').innerText = formatIndianCurrency(data.summary.approved_value);
                document.getElementById('qs-rejected').innerText = formatIndianCurrency(data.summary.rejected_value);
                
                const tbody = document.getElementById('quotation-list-tbody');
                tbody.innerHTML = '';
                
                if (data.quotations.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; color: var(--text-light); padding: 30px;">No quotations matches requirements.</td></tr>';
                    return;
                }
                
                data.quotations.forEach(q => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td><strong>${q.quotation_number}</strong></td>
                        <td>
                            <strong style="color:var(--text-primary);">${q.company_name}</strong><br>
                            <span style="font-size:11px; color: var(--text-muted);">${q.client_email} | ${q.city}</span>
                        </td>
                        <td>${formatIndianCurrency(q.subtotal)}</td>
                        <td>${formatIndianCurrency(q.gst_amount)}</td>
                        <td><strong style="color:var(--primary);">${formatIndianCurrency(q.total_amount)}</strong></td>
                        <td>
                            <select class="status-pill-select ${q.status}" onchange="updateQuoteStatus(${q.id}, this.value)">
                                <option value="Pending" ${q.status === 'Pending' ? 'selected' : ''}>Pending</option>
                                <option value="Approved" ${q.status === 'Approved' ? 'selected' : ''}>Approved</option>
                                <option value="Rejected" ${q.status === 'Rejected' ? 'selected' : ''}>Rejected</option>
                            </select>
                        </td>
                        <td>
                            <div style="display:flex; gap:8px;">
                                <button class="btn btn-secondary" style="padding: 6px 10px;" onclick="printQuotationPDF(${q.id})" title="Print or Save PDF"><i data-lucide="printer" style="width:14px; height:14px;"></i></button>
                                <button class="btn btn-secondary" style="padding: 6px 10px;" onclick="emailQuotationQuick(${q.id}, '${q.client_email}', '${q.company_name}')" title="Email Quotation"><i data-lucide="mail" style="width:14px; height:14px;"></i></button>
                            </div>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
                lucide.createIcons();
            } catch (err) {
                showNotification('Failed to retrieve quotation ledger data.', 'error');
            }
        }

        async function updateQuoteStatus(quoteId, newStatus) {
            const payload = new URLSearchParams({
                quote_id: quoteId,
                status: newStatus
            });
            
            try {
                const response = await fetch('?api=update_quotation_status', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: payload.toString()
                });
                const data = await response.json();
                
                if (response.ok && data.success) {
                    showNotification(data.message, 'success');
                    loadQuotationList(); // refresh
                } else {
                    showNotification(data.error || 'Failed to adjust quotation status.', 'error');
                }
            } catch (err) {
                showNotification('Status transaction execution failed.', 'error');
            }
        }

        // ==========================================
        //  PDF PRINT ENGINE & VISUAL EMULATOR
        // ==========================================
        async function printQuotationPDF(quoteId) {
            try {
                // Fetch quotation metadata details from client card info api
                const response = await fetch(`?api=quotation_list`);
                const data = await response.json();
                const quote = data.quotations.find(q => q.id == quoteId);
                
                if (!quote) {
                    showNotification('Quotation details trace missing.', 'error');
                    return;
                }
                
                // Query client address specs
                const cliRes = await fetch(`?api=client_details&id=${quote.client_id}`);
                const cli = await cliRes.json();
                
                const items = JSON.parse(quote.items_json);
                const printContainer = document.getElementById('invoice-print-container');
                printContainer.innerHTML = '';
                
                // Build a beautiful printable A4 corporate invoice template
                const template = document.createElement('div');
                template.style.padding = '30px';
                template.style.background = '#ffffff';
                template.style.color = '#1e293b';
                template.style.fontFamily = 'Inter, sans-serif';
                template.style.fontSize = '12px';
                template.style.lineHeight = '1.6';
                
                let rowsHtml = items.map((item, idx) => `
                    <tr>
                        <td style="border: 1px solid #cbd5e1; padding: 10px; text-align: center;">${idx + 1}</td>
                        <td style="border: 1px solid #cbd5e1; padding: 10px;">${item.name}</td>
                        <td style="border: 1px solid #cbd5e1; padding: 10px; text-align: center;">${item.qty}</td>
                        <td style="border: 1px solid #cbd5e1; padding: 10px; text-align: right;">${formatIndianCurrency(item.rate)}</td>
                        <td style="border: 1px solid #cbd5e1; padding: 10px; text-align: center;">${item.gst_rate}%</td>
                        <td style="border: 1px solid #cbd5e1; padding: 10px; text-align: right; font-weight:600;">${formatIndianCurrency(item.total)}</td>
                    </tr>
                `).join('');
                
                let isSameState = false;
                if (cli.state && companyProfile.state) {
                    isSameState = cli.state.trim().toLowerCase() === companyProfile.state.trim().toLowerCase();
                }
                
                let cgstAmount = 0;
                let sgstAmount = 0;
                let igstAmount = 0;
                if (isSameState) {
                    cgstAmount = quote.gst_amount / 2;
                    sgstAmount = quote.gst_amount / 2;
                } else {
                    igstAmount = quote.gst_amount;
                }
                
                let taxBreakdownHtml = '';
                if (isSameState) {
                    taxBreakdownHtml = `
                        <tr>
                            <td style="padding: 6px 0; color:#64748b;">CGST:</td>
                            <td style="padding: 6px 0; text-align: right; font-weight:600;">${formatIndianCurrency(cgstAmount)}</td>
                        </tr>
                        <tr>
                            <td style="padding: 6px 0; color:#64748b; border-bottom: 1px solid #cbd5e1;">SGST:</td>
                            <td style="padding: 6px 0; text-align: right; font-weight:600; border-bottom: 1px solid #cbd5e1;">${formatIndianCurrency(sgstAmount)}</td>
                        </tr>
                    `;
                } else {
                    taxBreakdownHtml = `
                        <tr>
                            <td style="padding: 6px 0; color:#64748b; border-bottom: 1px solid #cbd5e1;">IGST:</td>
                            <td style="padding: 6px 0; text-align: right; font-weight:600; border-bottom: 1px solid #cbd5e1;">${formatIndianCurrency(igstAmount)}</td>
                        </tr>
                    `;
                }
                
                let bankDetailsHtml = '';
                if (companyProfile.bank_name && companyProfile.account_number) {
                    bankDetailsHtml = `
                        <div style="margin-top: 15px; border: 1px dashed #cbd5e1; padding: 10px; border-radius: 6px; background-color: #f8fafc;">
                            <strong style="color:#ea580c; font-size: 10px; text-transform: uppercase;">Bank Account Details:</strong>
                            <div style="font-size:10px; color:#475569; margin-top: 4px; line-height: 1.4;">
                                <strong>Bank Name:</strong> ${companyProfile.bank_name}<br>
                                <strong>Account Number:</strong> ${companyProfile.account_number}<br>
                                <strong>IFSC Code:</strong> ${companyProfile.ifsc_code || 'N/A'}
                            </div>
                        </div>
                    `;
                }

                template.innerHTML = `
                    <div style="display:flex; justify-content: space-between; align-items:flex-start; margin-bottom: 40px; border-bottom: 2px solid #ea580c; padding-bottom: 20px;">
                        <div>
                            <h1 style="color:#ea580c; font-family:'Outfit'; font-size: 26px; font-weight:800; text-transform: uppercase;">Quotation</h1>
                            <div style="font-size:11px; color:#64748b; margin-top:4px;">Draft ID: ${quote.quotation_number}</div>
                        </div>
                        <div style="text-align: right; font-size: 12px; color: #334155; line-height: 1.4;">
                            <strong style="font-size:18px; color: #0f172a;">${companyProfile.company_name}</strong><br>
                            ${companyProfile.address_line1}<br>
                            ${companyProfile.address_line2 ? companyProfile.address_line2 + '<br>' : ''}
                            ${companyProfile.city}, ${companyProfile.state} - ${companyProfile.pincode}<br>
                            <strong>GSTIN:</strong> ${companyProfile.gstin}<br>
                            <strong>Email:</strong> ${companyProfile.email} ${companyProfile.mobile ? '| <strong>Phone:</strong> ' + companyProfile.mobile : ''}
                        </div>
                    </div>
                    
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 40px;">
                        <div>
                            <strong style="text-transform: uppercase; color:#ea580c; font-size:11px;">Quotation Prepared For:</strong>
                            <div style="font-size:15px; font-weight:700; color: #0f172a; margin: 6px 0 4px 0;">${quote.company_name}</div>
                            <div style="color: #475569; line-height: 1.4;">
                                <strong>Contact:</strong> ${cli.contact_name} (${cli.designation})<br>
                                <strong>Email:</strong> ${cli.email} | <strong>Mobile:</strong> ${cli.mobile}<br>
                                <strong>GSTIN:</strong> ${cli.gstin || 'Not Provided'}<br>
                                <strong>Address:</strong> ${cli.address_line1}${cli.address_line2 ? ', ' + cli.address_line2 : ''}, ${cli.city}, ${cli.state} - ${cli.pincode}
                            </div>
                        </div>
                        <div style="text-align: right; color: #475569; line-height: 1.4;">
                            <strong style="text-transform: uppercase; color:#ea580c; font-size:11px;">Quotation Information:</strong>
                            <div style="margin-top: 6px;">
                                <strong>Date Issued:</strong> ${formatDate(quote.created_at)}<br>
                                <strong>Valid Until:</strong> ${formatDate(new Date(new Date(quote.created_at).getTime() + (30 * 24 * 60 * 60 * 1000)))} (30 Days)<br>
                                <strong>Status:</strong> <span style="font-weight: 600; color: ${quote.status === 'Approved' ? '#10b981' : quote.status === 'Rejected' ? '#ef4444' : '#f59e0b'};">${quote.status}</span><br>
                                <strong>Prepared By:</strong> ${companyProfile.contact_person}
                            </div>
                        </div>
                    </div>
                    
                    <table style="width:100%; border-collapse: collapse; margin-bottom: 30px;">
                        <thead>
                            <tr style="background-color: #f8fafc; color:#ea580c;">
                                <th style="border: 1px solid #cbd5e1; padding: 12px 10px; width: 6%; text-align: center;">S.No</th>
                                <th style="border: 1px solid #cbd5e1; padding: 12px 10px; width: 50%; text-align: left;">Item/Service Specification</th>
                                <th style="border: 1px solid #cbd5e1; padding: 12px 10px; width: 8%; text-align: center;">Qty</th>
                                <th style="border: 1px solid #cbd5e1; padding: 12px 10px; width: 14%; text-align: right;">Rate (&#8377;)</th>
                                <th style="border: 1px solid #cbd5e1; padding: 12px 10px; width: 10%; text-align: center;">GST</th>
                                <th style="border: 1px solid #cbd5e1; padding: 12px 10px; width: 14%; text-align: right;">Total (&#8377;)</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${rowsHtml}
                        </tbody>
                    </table>
                    
                    <div style="display:grid; grid-template-columns: 1.3fr 1fr; gap: 40px; margin-top: 40px;">
                        <div>
                            <strong style="color:#ea580c; font-size: 11px; text-transform: uppercase;">Terms & Conditions:</strong>
                            <ol style="font-size:10px; color:#64748b; padding-left:14px; margin-top:6px; line-height: 1.6;">
                                <li>Payment terms: 50% advance on approval, remaining 50% post implementation.</li>
                                <li>The rates listed are valid for 30 calendar days from issued date.</li>
                                <li>All disputes are subject to ${companyProfile.city || 'Mumbai'} jurisdiction.</li>
                            </ol>
                            ${bankDetailsHtml}
                        </div>
                        <div style="background-color: #f8fafc; padding: 15px; border-radius: 6px; border: 1px solid #cbd5e1; align-self: flex-start;">
                            <table style="width: 100%; border-collapse: collapse; font-size:13px; line-height: 1.6;">
                                <tr>
                                    <td style="padding: 6px 0; color:#64748b;">Subtotal (Taxable Value):</td>
                                    <td style="padding: 6px 0; text-align: right; font-weight:600;">${formatIndianCurrency(quote.subtotal)}</td>
                                </tr>
                                ${taxBreakdownHtml}
                                <tr>
                                    <td style="padding: 10px 0 0 0; font-size: 16px; font-weight:800; color:#ea580c; border-top: 1px solid #cbd5e1;">Grand Total:</td>
                                    <td style="padding: 10px 0 0 0; text-align: right; font-size: 16px; font-weight:800; color:#ea580c; border-top: 1px solid #cbd5e1;">${formatIndianCurrency(quote.total_amount)}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div style="margin-top: 80px; display:flex; justify-content: space-between; align-items:flex-end;">
                        <div>
                            <div style="border-bottom: 1px solid #cbd5e1; width: 160px; margin-bottom:6px;"></div>
                            <span style="font-size: 10px; color:#64748b; font-weight: 500;">Client Signature / Acceptor</span>
                        </div>
                        <div style="text-align: right;">
                            <div style="border-bottom: 1px solid #cbd5e1; width: 160px; margin-bottom:6px; margin-left: auto;"></div>
                            <span style="font-size: 10px; color:#ea580c; font-weight:700;">For ${companyProfile.company_name}</span>
                        </div>
                    </div>
                `;
                
                printContainer.appendChild(template);
                
                // Run HTML to PDF conversion engine download
                const opt = {
                    margin:       10,
                    filename:     `Quotation_${quote.quotation_number}_${quote.company_name.replace(/\s+/g, '_')}.pdf`,
                    image:        { type: 'jpeg', quality: 0.98 },
                    html2canvas:  { scale: 2 },
                    jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
                };
                
                showNotification('Generating PDF Quotation...', 'info');
                const pdfWindow = window.open('', '_blank');
                if (pdfWindow) {
                    pdfWindow.document.write('<div style="font-family:sans-serif; padding: 20px;">Generating PDF... Please wait.</div>');
                }
                
                html2pdf().set(opt).from(template).outputPdf('bloburl').then(function(pdfUrl) {
                    if (pdfWindow) {
                        pdfWindow.location.href = pdfUrl;
                    } else {
                        // Fallback if popup blocked
                        html2pdf().set(opt).from(template).save();
                    }
                });
                
            } catch (err) {
                console.error(err);
                showNotification('Could not export quotation to PDF format.', 'error');
            }
        }
        // Save Company Settings Form Handler
        function saveCompanySettings(e) {
            e.preventDefault();
            const btn = document.getElementById('save-settings-btn');
            btn.disabled = true;
            btn.innerText = 'Saving...';
            
            const formData = new FormData(document.getElementById('settings-form'));
            
            fetch('?api=save_settings', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    
                    // Update the local companyProfile variable
                    companyProfile.company_name = formData.get('company_name');
                    companyProfile.gstin = formData.get('gstin');
                    companyProfile.address_line1 = formData.get('address_line1');
                    companyProfile.address_line2 = formData.get('address_line2');
                    companyProfile.city = formData.get('city');
                    companyProfile.state = formData.get('state');
                    companyProfile.pincode = formData.get('pincode');
                    companyProfile.country = formData.get('country');
                    companyProfile.email = formData.get('email');
                    companyProfile.mobile = formData.get('mobile');
                    companyProfile.contact_person = formData.get('contact_person');
                    companyProfile.bank_name = formData.get('bank_name');
                    companyProfile.account_number = formData.get('account_number');
                    companyProfile.ifsc_code = formData.get('ifsc_code');
                    
                    // Dynamically update user avatar and user labels across UI
                    document.getElementById('sidebar-user-name').innerText = companyProfile.contact_person;
                    document.getElementById('header-user-name').innerText = companyProfile.contact_person;
                    
                    const names = companyProfile.contact_person.split(' ');
                    let initials = '';
                    names.forEach(n => {
                        if (n) initials += n[0];
                    });
                    initials = initials.toUpperCase().substring(0, 2);
                    document.getElementById('header-user-avatar').innerText = initials;
                    
                    const addedByEl = document.querySelector('input[name="added_by"]');
                    if (addedByEl) addedByEl.value = companyProfile.contact_person;
                    
                    const sentByEl = document.querySelector('input[name="sent_by"]');
                    if (sentByEl) sentByEl.value = companyProfile.contact_person;
                    
                } else {
                    showNotification(data.error || 'Failed to save settings.', 'error');
                }
            })
            .catch(err => {
                console.error(err);
                showNotification('Connection error while saving settings.', 'error');
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerText = 'Save Configurations';
            });
        }

        // Quick Email shortcut redirect from list view
        function emailQuotationQuick(quoteId, clientEmail, companyName) {
            document.querySelector('[data-view="send-email"]').click();
            
            // Wait for DOM values mapping
            setTimeout(() => {
                const toSelect = document.getElementById('email-to-select');
                // Find and select option matching company name
                for (let i = 0; i < toSelect.options.length; i++) {
                    if (toSelect.options[i].text.includes(companyName)) {
                        toSelect.selectedIndex = i;
                        break;
                    }
                }
                
                document.querySelector('select[name="type"]').value = 'Quotation';
                document.querySelector('input[name="subject"]').value = `Quotation Proposal Details for ${companyName}`;
                
                // Prefill template email details
                quillEmailEditor.root.innerHTML = `
                    <p>Dear Sir/Madam,</p>
                    <p>Please find enclosed our quotation proposal regarding the requirements discussed.</p>
                    <p>Kindly check the quotation details ledger in the portal attachment section and approve the transaction.</p>
                    <p>Thank you for your business!</p>
                `;
            }, 150);
        }
        // ==========================================
        //  BULK UPLOAD & SMART SPLIT
        // ==========================================
        let bulkParsedData = [];
        let systemUsersList = [];

        function openBulkUploadModal() {
            window.bulkUploadDestination = null;
            document.getElementById('bulk-upload-modal').style.display = 'flex';
            document.getElementById('bulk-upload-file').value = '';
            document.getElementById('bulk-preview-container').style.display = 'none';
            document.getElementById('bulk-split-checkbox').checked = false;
            toggleSplitOptions();
            
            // Pre-fetch users for dropdowns
            if(systemUsersList.length === 0) {
                fetch('?api=get_users')
                    .then(res => res.json())
                    .then(data => {
                        if(data.users) systemUsersList = data.users;
                    });
            }
        }

        function closeBulkUploadModal() {
            document.getElementById('bulk-upload-modal').style.display = 'none';
        }

        function handleBulkFileSelect(e) {
            const file = e.target.files[0];
            if(!file) return;
            const reader = new FileReader();
            reader.onload = function(evt) {
                try {
                    const data = evt.target.result;
                    const workbook = XLSX.read(data, {type: 'binary'});
                    const firstSheetName = workbook.SheetNames[0];
                    const worksheet = workbook.Sheets[firstSheetName];
                    let json = XLSX.utils.sheet_to_json(worksheet, {defval: ""});
                    
                    // Clean columns mapping
                    bulkParsedData = json.map(row => {
                        return {
                            lead_name: row['Contact Name'] || row['contact_name'] || row['lead_name'] || '',
                            company_name: row['Company Name'] || row['company_name'] || '',
                            mobile: row['Mobile'] || row['mobile'] || row['Phone'] || '',
                            email: row['Email'] || row['email'] || '',
                            lead_source: row['Lead Source'] || row['lead_source'] || 'Cold Call',
                            priority: row['Priority'] || row['priority'] || 'Warm',
                            stage: row['Stage'] || row['stage'] || 'New Lead',
                            assigned_to: row['Assigned To'] || row['assigned_to'] || '',
                            location: row['Location'] || row['location'] || row['City'] || '',
                            notes: row['Notes'] || row['notes'] || ''
                        };
                    }).filter(r => r.lead_name !== '' || r.mobile !== '');
                    
                    document.getElementById('bulk-record-count').innerText = bulkParsedData.length;
                    document.getElementById('bulk-preview-container').style.display = 'block';
                    renderSplitUI();
                } catch(err) {
                    console.error(err);
                    alert("Error parsing file. Ensure it is a valid CSV or Excel file.");
                }
            };
            reader.readAsBinaryString(file);
        }

        function toggleSplitOptions() {
            const isChecked = document.getElementById('bulk-split-checkbox').checked;
            document.getElementById('split-options-container').style.display = isChecked ? 'block' : 'none';
        }

        function renderSplitUI() {
            const uiContainer = document.getElementById('dynamic-split-ui');
            const splitType = document.querySelector('input[name="split_type"]:checked')?.value;
            if(!splitType) {
                uiContainer.style.display = 'none';
                return;
            }
            uiContainer.style.display = 'block';
            
            let userOptions = `<option value="">-- Select Staff --</option>` + systemUsersList.map(u => `<option value="${u.username}">${u.username}</option>`).join('');

            if(splitType === 'random') {
                uiContainer.innerHTML = `
                    <p style="font-size:13px;margin-bottom:10px;">Even rows assigned to Team A, Odd rows to Team B.</p>
                    <div style="display:flex; gap:10px;">
                        <div style="flex:1;"><label>Team A (Even)</label><select id="split-random-a" style="width:100%;padding:6px;border-radius:4px;border:1px solid var(--border);">${userOptions}</select></div>
                        <div style="flex:1;"><label>Team B (Odd)</label><select id="split-random-b" style="width:100%;padding:6px;border-radius:4px;border:1px solid var(--border);">${userOptions}</select></div>
                    </div>
                `;
            } 
            else if(splitType === 'serial') {
                uiContainer.innerHTML = `
                    <p style="font-size:13px;margin-bottom:10px;">Define ranges (e.g., 1-50, 51-100).</p>
                    <div id="serial-ranges-container">
                        <div class="serial-row" style="display:flex;gap:10px;margin-bottom:10px;">
                            <input type="number" placeholder="Start" class="s-start" style="width:70px;padding:4px;border:1px solid var(--border);border-radius:4px;">
                            <input type="number" placeholder="End" class="s-end" style="width:70px;padding:4px;border:1px solid var(--border);border-radius:4px;">
                            <select class="s-staff" style="flex:1;padding:4px;border:1px solid var(--border);border-radius:4px;">${userOptions}</select>
                        </div>
                    </div>
                    <button type="button" class="btn btn-secondary" style="padding:4px 10px;font-size:11px;" onclick="addSerialRow()">+ Add Range</button>
                `;
            }
            else if(splitType === 'location') {
                // Get unique locations
                const locations = [...new Set(bulkParsedData.map(r => String(r.location).trim()).filter(l => l !== ''))];
                if(locations.length === 0) {
                    uiContainer.innerHTML = `<p style="font-size:13px;color:red;">No locations found in the uploaded file.</p>`;
                    return;
                }
                
                let locHTML = `<p style="font-size:13px;margin-bottom:10px;">Assign staff for each location found in file:</p><div id="location-mapping-container">`;
                locations.forEach(loc => {
                    locHTML += `
                        <div class="loc-row" data-loc="${loc}" style="display:flex;gap:10px;margin-bottom:8px;align-items:center;">
                            <span style="flex:1;font-weight:600;">≡ƒôì ${loc}</span>
                            <select class="l-staff" style="flex:1;padding:4px;border:1px solid var(--border);border-radius:4px;">${userOptions}</select>
                        </div>
                    `;
                });
                locHTML += `</div>`;
                uiContainer.innerHTML = locHTML;
            }
        }
        
        function addSerialRow() {
            let userOptions = `<option value="">-- Select Staff --</option>` + systemUsersList.map(u => `<option value="${u.username}">${u.username}</option>`).join('');
            const row = document.createElement('div');
            row.className = 'serial-row';
            row.style.cssText = 'display:flex;gap:10px;margin-bottom:10px;';
            row.innerHTML = `
                <input type="number" placeholder="Start" class="s-start" style="width:70px;padding:4px;border:1px solid var(--border);border-radius:4px;">
                <input type="number" placeholder="End" class="s-end" style="width:70px;padding:4px;border:1px solid var(--border);border-radius:4px;">
                <select class="s-staff" style="flex:1;padding:4px;border:1px solid var(--border);border-radius:4px;">${userOptions}</select>
            `;
            document.getElementById('serial-ranges-container').appendChild(row);
        }

        async function processBulkUpload() {
            if(bulkParsedData.length === 0) return showNotification('No data to save.', 'error');
            
            const isChecked = document.getElementById('bulk-split-checkbox').checked;
            let finalData = [...bulkParsedData];
            
            if(isChecked) {
                const splitType = document.querySelector('input[name="split_type"]:checked')?.value;
                if(splitType === 'random') {
                    const teamA = document.getElementById('split-random-a').value;
                    const teamB = document.getElementById('split-random-b').value;
                    finalData.forEach((row, idx) => {
                        row.assigned_to = (idx % 2 === 0) ? teamA : teamB;
                    });
                }
                else if(splitType === 'serial') {
                    const rows = document.querySelectorAll('.serial-row');
                    let ranges = [];
                    rows.forEach(r => {
                        ranges.push({
                            start: parseInt(r.querySelector('.s-start').value),
                            end: parseInt(r.querySelector('.s-end').value),
                            staff: r.querySelector('.s-staff').value
                        });
                    });
                    
                    finalData.forEach((row, idx) => {
                        const serialNum = idx + 1;
                        let assigned = row.assigned_to;
                        for(let rng of ranges) {
                            if(rng.staff && !isNaN(rng.start) && !isNaN(rng.end) && serialNum >= rng.start && serialNum <= rng.end) {
                                assigned = rng.staff;
                                break;
                            }
                        }
                        row.assigned_to = assigned;
                    });
                }
                else if(splitType === 'location') {
                    const locRows = document.querySelectorAll('.loc-row');
                    let mapping = {};
                    locRows.forEach(r => {
                        const loc = r.getAttribute('data-loc');
                        const staff = r.querySelector('.l-staff').value;
                        if(staff) mapping[loc] = staff;
                    });
                    
                    finalData.forEach(row => {
                        if(row.location && mapping[row.location.trim()]) {
                            row.assigned_to = mapping[row.location.trim()];
                        }
                    });
                }
            }
            
            const btn = document.getElementById('btn-save-bulk');
            btn.innerText = 'Saving... Please wait';
            btn.disabled = true;
            
            try {
                const fd = new FormData();
                const action = window.bulkUploadDestination === 'pre_leads' ? 'bulk_upload_preleads' : 'bulk_upload_leads';
                fd.append('leads_json', JSON.stringify(finalData));
                
                const res = await fetch(`?api=${action}`, { method:'POST', body: fd });
                const json = await res.json();
                
                if(json.success) {
                    showNotification(json.success, 'success');
                    closeBulkUploadModal();
                    if(action === 'bulk_upload_preleads') {
                        loadPreLeads();
                    } else {
                        loadLeads();
                    }
                } else {
                    showNotification(json.error || 'Failed to bulk upload', 'error');
                }
            } catch(e) {
                console.error(e);
                showNotification('Connection Error during bulk upload.', 'error');
            } finally {
                btn.innerText = 'Save All Leads';
                btn.disabled = false;
            }
        }
    