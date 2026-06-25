<?php
require_once 'config.php';
$staff_members = $db->query("SELECT username FROM users WHERE role = 'Staff' AND is_active = 1")->fetchAll(PDO::FETCH_COLUMN);
$page_title = 'Search & Tracking Dashboard';
$page_subtitle = 'Interactive CRM conversion tracker and client card catalog';
require_once 'header.php';
?>

<div id="view-search-crm" class="view-container">
    <!-- Search bar -->
    <div class="crm-search-bar">
        <div class="search-input-wrapper">
            <i data-lucide="search" class="search-icon"></i>
            <input type="text" id="search-query" placeholder="Type Client Company Name / GSTIN / Contact Name / Email / City..." oninput="triggerSearch()">
        </div>
        <button class="filters-toggle-btn" onclick="toggleFilterDrawer()"><i data-lucide="sliders-horizontal" style="margin-right: 6px;"></i> Filters</button>
    </div>

    <!-- Filters Drawer -->
    <div class="filters-drawer" id="crm-filters-drawer">
        <div class="form-group">
            <label>By Status</label>
            <select id="filter-status" onchange="triggerSearch()">
                <option value="">All Statuses</option>
                <option value="New">🔵 New</option>
                <option value="Contacted">🟠 Contacted</option>
                <option value="In Negotiation">🟡 In Negotiation</option>
                <option value="Closed Won">🟢 Closed Won</option>
                <option value="Closed Lost">🔴 Closed Lost</option>
            </select>
        </div>
        <div class="form-group">
            <label>By Priority</label>
            <select id="filter-priority" onchange="triggerSearch()">
                <option value="">All Priorities</option>
                <option value="Hot">Hot</option>
                <option value="Warm">Warm</option>
                <option value="Cold">Cold</option>
            </select>
        </div>
        <div class="form-group">
            <label>By City</label>
            <select id="filter-city" onchange="triggerSearch()">
                <option value="">All Cities</option>
            </select>
        </div>
        <div class="form-group">
            <label>By Added By</label>
            <select id="filter-added-by" onchange="triggerSearch()">
                <option value="">All Staff</option>
            </select>
        </div>
        <div class="form-group">
            <label>Registered Start</label>
            <input type="date" id="filter-date-start" onchange="triggerSearch()">
        </div>
        <div class="form-group">
            <label>Registered End</label>
            <input type="date" id="filter-date-end" onchange="triggerSearch()">
        </div>
    </div>

    <!-- CRM Layout Grid -->
    <div class="crm-layout">
        <!-- Left panel: Card Lists -->
        <div class="client-list-pane" id="crm-client-list">
            <!-- Loaded dynamically -->
        </div>

        <!-- Right panel: Live Tracker Details -->
        <div class="client-detail-pane" id="crm-detail-pane">
            <div class="detail-placeholder">
                <i data-lucide="eye" style="width: 64px; height: 64px; stroke-width: 1.5;"></i>
                <div>
                    <h3>No Client Selected</h3>
                    <p style="font-size: 13.5px; margin-top: 6px;">Select a client from the search results to view their CRM history, communications, and quotation statuses.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const STAFF_MEMBERS = <?php echo json_encode($staff_members); ?>;
    let crmClientList = [];

    function toggleFilterDrawer() {
        document.getElementById('crm-filters-drawer').classList.toggle('active');
    }

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
            
            const CELEBRATED_KEY = 'crm_celebrated_clients';
            let celebratedClients = JSON.parse(localStorage.getItem(CELEBRATED_KEY) || '[]');
            let didCelebrate = false;
            
            const container = document.getElementById('crm-client-list');
            container.innerHTML = '';
            
            if (clients.length === 0) {
                container.innerHTML = '<div style="background: white; border: 1px solid #e2e8f0; text-align: center; color: var(--text-light); padding: 40px; border-radius: var(--radius-md);">No records match criteria.</div>';
                return;
            }
            
            clients.forEach(c => {
                let isAllCompleted = c.pitch_sent && c.ppt_shared && c.quotation_sent && c.custom_mail_sent;
                if (isAllCompleted && !celebratedClients.includes(c.id)) {
                    celebratedClients.push(c.id);
                    didCelebrate = true;
                }
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
                        <span>${c.contact_name} — ${c.designation || 'Client Contact'}</span>
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
            
            if (didCelebrate) {
                localStorage.setItem(CELEBRATED_KEY, JSON.stringify(celebratedClients));
                setTimeout(() => {
                    if (typeof confetti === 'function') {
                        confetti({
                            particleCount: 200,
                            spread: 90,
                            origin: { y: 0.6 },
                            zIndex: 9999,
                            colors: ['#26ccff', '#a25afd', '#ff5e7e', '#88ff5a', '#fcff42', '#ffa62d', '#ff36ff']
                        });
                    }
                    showNotification(`🎉 Incredible! Client Pipeline fully completed!`, 'success');
                }, 500);
            }

            lucide.createIcons();
            
            // Auto-select first client if available
            if (clients.length > 0 && !document.querySelector('.client-card.selected')) {
                selectClientCard(clients[0].id);
            }
        } catch (err) {
            showNotification('CRM Search request failed.', 'error');
        }
    }

    async function selectClientCard(id) {
        document.querySelectorAll('.client-card').forEach(el => el.classList.remove('selected'));
        
        const selectedCard = document.getElementById('crm-client-card-' + id);
        if (selectedCard) selectedCard.classList.add('selected');
        
        try {
            const response = await fetch(`?api=client_details&id=${id}`);
            const c = await response.json();
            
            const pane = document.getElementById('crm-detail-pane');
            pane.innerHTML = '';
            
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
            
            if (c.overall_status === 'Contacted') stepsClassList.pitch = 'active';
            if (c.overall_status === 'In Negotiation') stepsClassList.quote = 'active';
            
            let quotesHtml = '';
            if (c.quotations && c.quotations.length > 0) {
                quotesHtml = c.quotations.map(q => {
                    const amt = formatIndianCurrency(q.total_amount);
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
                    <div class="pipeline-step ${stepsClassList.quote}">
                        <div class="pipeline-icon-circle"><i data-lucide="${c.quotation_sent ? 'check':'file-text'}" style="width:14px;"></i></div>
                        <span class="pipeline-step-label">Quote Sent</span>
                        <span class="pipeline-step-date">${quoteDate}</span>
                    </div>
                    <div class="pipeline-step ${stepsClassList.mail}">
                        <div class="pipeline-icon-circle"><i data-lucide="${c.mail_sent ? 'check':'sparkles'}" style="width:14px;"></i></div>
                        <span class="pipeline-step-label">Custom Mail</span>
                        <span class="pipeline-step-date">${mailDate}</span>
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
                <div style="display:flex; gap:10px; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid #f1f5f9; flex-wrap: wrap;">
                    ${c.overall_status === 'Not Interested' ? `
                    <button class="btn btn-secondary" disabled title="Client is Not Interested. Communication restricted." style="opacity:0.6; cursor:not-allowed;"><i data-lucide="mail"></i> Send Mail</button>
                    <button class="btn btn-secondary" disabled title="Client is Not Interested. PPT restricted." style="opacity:0.6; cursor:not-allowed;"><i data-lucide="presentation"></i> Mark PPT</button>
                    <button class="btn btn-secondary" disabled title="Client is Not Interested. Quoting restricted." style="opacity:0.6; cursor:not-allowed;"><i data-lucide="file-signature"></i> Create Quote</button>
                    ` : `
                    <button class="btn btn-primary" onclick="initiateEmailForClient(${c.id})"><i data-lucide="mail"></i> Send Mail</button>
                    <button class="btn btn-secondary" onclick="markPPTShared(${c.id}, '${c.company_name.replace(/'/g, "\\'")}')"><i data-lucide="presentation"></i> Mark PPT</button>
                    <button class="btn btn-secondary" onclick="initiateQuotationForClient(${c.id})"><i data-lucide="file-signature"></i> Create Quote</button>
                    `}
                    <button class="btn btn-secondary" style="background: var(--bg-main); color: var(--text-primary); border-color: #cbd5e1;" onclick="initiateLogCall(${c.id}, '${c.company_name.replace(/'/g, "\\'")}')"><i data-lucide="phone-call"></i> Log Call</button>
                    ${currentUser && currentUser.role === 'Admin' ? `<button class="btn btn-secondary" style="border-color: #cbd5e1;" onclick="initiateReassign(${c.id}, '${c.company_name.replace(/'/g, "\\'")}', '${c.assigned_to || ''}')"><i data-lucide="users"></i> Reassign</button>` : ''}
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

    function switchDetailTab(tabName) {
        document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        
        document.getElementById('tab-btn-' + tabName).classList.add('active');
        document.getElementById('tab-content-' + tabName).classList.add('active');
    }

    function formatDate(sqlDate) {
        if (!sqlDate) return 'N/A';
        const date = new Date(sqlDate);
        return date.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
    }

    function initiateEmailForClient(clientId) {
        location.href = 'send_email.php?client_id=' + clientId;
    }

    function initiateQuotationForClient(clientId) {
        location.href = 'quotation_builder.php?client_id=' + clientId;
    }

    async function markPPTShared(clientId, companyName) {
        if (!confirm(`Are you sure you want to mark PPT as shared for ${companyName}?`)) return;
        
        const fd = new FormData();
        fd.append('client_id', clientId);
        
        try {
            const res = await fetch('?api=log_ppt_shared', { method: 'POST', body: fd });
            const data = await res.json();
            if (data.success) {
                showNotification(data.message, 'success');
                triggerSearch();
            } else {
                showNotification(data.error || 'Failed to mark PPT.', 'error');
            }
        } catch (e) {
            showNotification('Error communicating with server.', 'error');
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        loadFilterOptions();
        triggerSearch();
        
        // Auto-open client details if requested via URL
        const urlParams = new URLSearchParams(window.location.search);
        const viewId = urlParams.get('view_client');
        if (viewId) {
            setTimeout(() => {
                if (typeof selectClientCard === 'function') selectClientCard(viewId);
            }, 600);
        }
    });
</script>

<!-- Log Call Modal -->
<div id="log-call-modal" class="modal" style="display:none;">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h3>Log a Call</h3>
            <button onclick="closeLogCallModal()" style="background:none;border:none;cursor:pointer;"><i data-lucide="x"></i></button>
        </div>
        <form id="log-call-form" onsubmit="submitLogCall(event)">
            <input type="hidden" id="log-call-client-id" name="client_id">
            
            <div class="form-group" style="margin-bottom: 15px;">
                <label>Call Outcome / Response</label>
                <select id="log-call-outcome" name="outcome" class="form-control" required style="width:100%; padding: 8px; border-radius: 4px; border: 1px solid #cbd5e1;">
                    <option value="">Select Response...</option>
                    <option value="Connected - Interested">Connected - Interested</option>
                    <option value="Connected - Follow-up requested">Connected - Follow-up requested</option>
                    <option value="Connected - Not Interested">Connected - Not Interested</option>
                    <option value="No Answer">No Answer</option>
                    <option value="Number Busy / Invalid">Number Busy / Invalid</option>
                </select>
            </div>
            
            <div class="form-group" style="margin-bottom: 15px;">
                <label>Remarks / Call Summary</label>
                <textarea id="log-call-remarks" name="remarks" class="form-control" required rows="4" placeholder="Briefly describe what was discussed..." style="width:100%; padding: 8px; border-radius: 4px; border: 1px solid #cbd5e1;"></textarea>
            </div>
            
            <div style="text-align: right;">
                <button type="button" class="btn btn-secondary" onclick="closeLogCallModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Call Log</button>
            </div>
        </form>
    </div>
</div>

    <!-- Reassign Modal -->
    <div id="reassign-modal" class="modal-overlay" style="display:none;">
        <div class="modal-content" style="max-width:400px;">
            <div class="modal-header">
                <h3 id="reassign-title">Reassign Lead/Client</h3>
                <button onclick="closeReassignModal()" style="background:none;border:none;cursor:pointer;"><i data-lucide="x"></i></button>
            </div>
            <div class="form-group" style="margin-bottom:15px;">
                <label>Select New Staff Member</label>
                <input type="hidden" id="reassign-client-id">
                <select id="reassign-staff-select" class="user-select">
                    <option value="">-- Select Staff --</option>
                    <?php foreach($staff_members as $sm): ?>
                        <option value="<?= htmlspecialchars($sm) ?>"><?= htmlspecialchars($sm) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="text-align: right;">
                <button class="btn btn-secondary" onclick="closeReassignModal()">Cancel</button>
                <button class="btn btn-primary" onclick="submitReassign()">Reassign</button>
            </div>
        </div>
    </div>

<script>
    function initiateReassign(clientId, companyName, currentAssignee) {
        document.getElementById('reassign-client-id').value = clientId;
        document.getElementById('reassign-title').innerText = 'Reassign ' + companyName;
        document.getElementById('reassign-staff-select').value = currentAssignee;
        document.getElementById('reassign-modal').style.display = 'flex';
    }

    function closeReassignModal() {
        document.getElementById('reassign-modal').style.display = 'none';
    }

    async function submitReassign() {
        const clientId = document.getElementById('reassign-client-id').value;
        const newStaff = document.getElementById('reassign-staff-select').value;
        if (!newStaff) {
            showNotification('Please select a staff member.', 'warning');
            return;
        }
        
        const fd = new FormData();
        fd.append('client_id', clientId);
        fd.append('new_staff', newStaff);
        
        try {
            const res = await fetch('?api=reassign_client', { method: 'POST', body: fd });
            const data = await res.json();
            if (data.success) {
                showNotification('Client successfully reassigned.', 'success');
                closeReassignModal();
                triggerSearch();
            } else {
                showNotification(data.error || 'Failed to reassign.', 'error');
            }
        } catch (e) {
            showNotification('Error reassigning.', 'error');
        }
    }

    function initiateLogCall(clientId, companyName) {
        document.getElementById('log-call-client-id').value = clientId;
        document.getElementById('log-call-outcome').value = '';
        document.getElementById('log-call-remarks').value = '';
        document.getElementById('log-call-modal').style.display = 'flex';
        
        // Ensure icons load
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }

    function closeLogCallModal() {
        document.getElementById('log-call-modal').style.display = 'none';
    }

    async function submitLogCall(e) {
        e.preventDefault();
        const btn = e.target.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.innerText = 'Saving...';
        
        const clientId = document.getElementById('log-call-client-id').value;
        const outcome = document.getElementById('log-call-outcome').value;
        const remarks = document.getElementById('log-call-remarks').value;
        
        try {
            const fd = new FormData();
            fd.append('client_id', clientId);
            fd.append('outcome', outcome);
            fd.append('remarks', remarks);
            
            const res = await fetch('?api=log_call', {
                method: 'POST',
                body: fd
            });
            const data = await res.json();
            
            if (data.error) {
                showNotification(data.error, 'error');
            } else {
                showNotification('Call logged successfully!', 'success');
                closeLogCallModal();
                selectClientCard(clientId); // Refresh client detail view
            }
        } catch (err) {
            showNotification('Server Error', 'error');
        }
        
        btn.disabled = false;
        btn.innerText = 'Save Call Log';
    }
</script>

<?php require_once 'footer.php'; ?>
