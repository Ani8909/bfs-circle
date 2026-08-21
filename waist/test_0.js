
    function toggleFilterDrawer() {
        document.getElementById('crm-filters-drawer').classList.toggle('active');
    }

    function formatAmt(num) {
        return new Intl.NumberFormat('en-IN').format(num);
    }


    async function addNote(id) {
        const input = document.getElementById('noteInput');
        const note = input.value.trim();
        if(!note) return;
        
        const fd = new FormData();
        fd.append('id', id);
        fd.append('note', note);
        
        try {
            const res = await fetch('api.php?api=add_applicant_note', { method: 'POST', body: fd });
            const data = await res.json();
            if(data.success) {
                input.value = '';
                showNotification('Note added', 'success');
                selectApplicantCard(id); // reload the pane
            } else {
                showNotification(data.error || 'Failed', 'error');
            }
        } catch(e) {
            showNotification('Error adding note', 'error');
        }
    }

    let currentOffset = 0;

    async function triggerSearch(reset = true) {
        if (reset) {
            currentOffset = 0;
        }
        
        const query = document.getElementById('search-query').value;
        const status = document.getElementById('filter-status').value;
        const type = document.getElementById('filter-type').value;
        
        const params = new URLSearchParams({
            api: 'search_applicants',
            query: query,
            status: status,
            loan_type: type,
            offset: currentOffset
        });
        
        try {
            const container = document.getElementById('crm-client-list');
            if (reset) {
                // Show Skeleton Loader for Left Pane
                container.innerHTML = Array(5).fill(`
                    <div class="client-card" style="pointer-events: none; border: 1px solid #e2e8f0;">
                        <div class="client-card-header">
                            <div class="skeleton" style="width: 140px; height: 20px;"></div>
                            <div class="skeleton" style="width: 60px; height: 24px; border-radius: 12px;"></div>
                        </div>
                        <div class="client-card-meta"><div class="skeleton" style="width: 120px; height: 14px;"></div></div>
                        <div class="client-card-meta"><div class="skeleton" style="width: 180px; height: 14px;"></div></div>
                        <div class="client-card-meta" style="margin-bottom:0;"><div class="skeleton" style="width: 100px; height: 14px;"></div></div>
                        <div style="margin-top: 12px; border-top: 1px dashed var(--border); padding-top: 10px; display: flex; justify-content: space-between;">
                            <div class="skeleton" style="width: 70px; height: 12px;"></div>
                            <div class="skeleton" style="width: 90px; height: 12px;"></div>
                        </div>
                    </div>
                `).join('');
            } else {
                // Change Load More button text to loading
                const realBtn = document.getElementById('real-load-more-btn');
                if (realBtn) realBtn.innerText = 'Loading...';
            }
            
            const response = await fetch('?' + params.toString());
            const applicants = await response.json();
            
            if (reset) {
                container.innerHTML = '';
            }
            
            if (reset && applicants.length === 0) {
                container.innerHTML = '<div style="background: white; border: 1px solid #e2e8f0; text-align: center; color: var(--text-light); padding: 40px; border-radius: var(--radius-md);">No applicants match criteria.</div>';
                return;
            }
            
            applicants.forEach(app => {
                const card = document.createElement('div');
                card.className = 'client-card';
                card.id = 'crm-client-card-' + app.id;
                card.dataset.id = app.id;
                card.onclick = () => selectApplicantCard(app.id);
                
                let badgeClass = 'badge-info';
                if(app.overall_status === 'Phase 2') badgeClass = 'badge-warning';
                if(app.overall_status === 'Phase 3') badgeClass = 'badge-primary';
                if(app.overall_status === 'Phase 4') badgeClass = 'badge-warning';
                if(app.overall_status === 'Completed') {
                    badgeClass = 'badge-success';
                    card.classList.add('won');
                }
                if(app.overall_status === 'Rejected') {
                    badgeClass = 'badge-danger';
                }
                
                card.innerHTML = `
                    <div class="client-card-header">
                        <span class="client-card-title">
                            ${app.customer_name}
                            ${app.calculated_completion < 100 
                                ? `<span style="display:inline-block; font-size: 10px; color: #ef4444; font-weight: 600; margin-left: 4px;" title="${app.calculated_completion}% Profile Complete"><i data-lucide="alert-circle" style="width:10px;height:10px;"></i> ${app.calculated_completion}%</span>`
                                : `<span style="display:inline-block; font-size: 10px; color: #10b981; font-weight: 600; margin-left: 4px;" title="100% Complete Profile"><i data-lucide="check-circle" style="width:10px;height:10px;"></i> 100%</span>`
                            }
                        </span>
                        <span class="badge ${badgeClass}">${app.overall_status}</span>
                    </div>
                    <div class="client-card-meta">
                        <i data-lucide="tag" style="width: 12px; height: 12px;"></i>
                        ${app.loan_id}
                    </div>
                    <div class="client-card-meta">
                        <i data-lucide="briefcase" style="width: 12px; height: 12px;"></i>
                        ${app.loan_type} - <span style="font-weight: 600;">₹${new Intl.NumberFormat('en-IN').format(app.amount)}</span>
                    </div>
                    <div class="client-card-meta" style="margin-bottom: 0;">
                        <i data-lucide="phone" style="width: 12px; height: 12px;"></i>
                        ${app.mobile}
                    </div>
                    <div style="margin-top: 12px; border-top: 1px dashed var(--border); padding-top: 10px; font-size: 11px; color: var(--text-light); display: flex; justify-content: space-between;">
                        <span><i data-lucide="clock" style="width:10px;height:10px;vertical-align:-1px;"></i> Unknown</span>
                        <span>Added by: System</span>
                    </div>
                `;
                container.appendChild(card);
            });
            
            const fixedLoadBtn = document.getElementById('load-more-btn-container');
            if (fixedLoadBtn) {
                if (applicants.length === 10) {
                    fixedLoadBtn.style.display = 'block';
                } else {
                    fixedLoadBtn.style.display = 'none';
                }
            }
            
            if(window.lucide) lucide.createIcons();
            const realBtn = document.getElementById('real-load-more-btn');
            if (realBtn) realBtn.innerText = '↓ Load More Records ↓';
            
        } catch (error) {
            console.error('Error searching:', error);
            showNotification('Applicant search failed.', 'error');
        }
    }

    async function selectApplicantCard(id) {
        document.querySelectorAll('.client-card').forEach(el => el.classList.remove('selected'));
        
        const selectedCard = document.getElementById('crm-client-card-' + id);
        if (selectedCard) selectedCard.classList.add('selected');
        
        try {
            const response = await fetch(`?api=applicant_full_details&id=${id}`);
            const app = await response.json();
            
            if (app.error) {
                showNotification(app.error, 'error');
                return;
            }
            
            const pane = document.getElementById('crm-detail-pane');
            pane.innerHTML = '';
            
            const steps = {
                phase1: ['Phase 1', 'Phase 2', 'Phase 3', 'Phase 4', 'Completed', 'Rejected'].includes(app.overall_status),
                phase2: ['Phase 2', 'Phase 3', 'Phase 4', 'Completed', 'Rejected'].includes(app.overall_status),
                phase3: ['Phase 3', 'Phase 4', 'Completed', 'Rejected'].includes(app.overall_status),
                phase4: ['Phase 4', 'Completed', 'Rejected'].includes(app.overall_status)
            };
            

            // Documents HTML Advanced Checklist
            let docsHtml = '';
            if (app.phase2_checklist) {
                docsHtml = `
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                        <span style="font-weight:600; color:var(--text-primary);"><i data-lucide="check-square" style="width:16px; vertical-align:-2px;"></i> Mandatory Checklist (${app.loan_type})</span>
                        <span class="badge ${app.phase2_completion === 100 ? 'badge-success' : 'badge-warning'}">${app.phase2_completion}% Complete</span>
                    </div>
                    <table class="data-table">
                        <thead><tr><th>Category</th><th>Status</th></tr></thead>
                        <tbody>
                            ${app.phase2_checklist.map(d => `<tr><td><strong>${d.category}</strong></td><td>${d.uploaded ? '<span class="badge badge-success"><i data-lucide="check" style="width:12px;"></i> Uploaded</span>' : '<span class="badge badge-danger"><i data-lucide="x" style="width:12px;"></i> Pending</span>'}</td></tr>`).join('')}
                        </tbody>
                    </table>
                `;
            } else {
                docsHtml = '<div style="text-align:center; padding:20px; color:var(--text-light);">No checklist found.</div>';
            }
            
            // Disbursements HTML
            let disbHtml = '';
            if (app.disbursements && app.disbursements.length > 0) {
                disbHtml = `
                    <table class="data-table">
                        <thead><tr><th>Phase</th><th>Amount</th><th>Status</th></tr></thead>
                        <tbody>
                            ${app.disbursements.map(d => `<tr><td>Phase ${d.phase_number}: ${d.phase_name}</td><td style="font-weight:bold;">₹${formatAmt(d.amount)}</td><td><span class="badge ${d.status === 'Disbursed' ? 'badge-success' : 'badge-warning'}">${d.status}</span></td></tr>`).join('')}
                        </tbody>
                    </table>
                `;
            } else {
                disbHtml = '<div style="text-align:center; padding:20px; color:var(--text-light);">No disbursements recorded yet.</div>';
            }
            
            // Banks HTML
            let banksHtml = '';
            if (app.banks && app.banks.length > 0) {
                banksHtml = `
                    <table class="data-table">
                        <thead><tr><th>Bank Name</th><th>Status</th><th>Resolution</th></tr></thead>
                        <tbody>
                            ${app.banks.map(b => {
                                let badge = 'badge-warning';
                                if (b.status === 'Approved') badge = 'badge-success';
                                if (b.status === 'Rejected') badge = 'badge-danger';
                                return `<tr><td><strong>${b.bank_name}</strong></td><td><span class="badge ${badge}">${b.status}</span></td><td style="font-size:12px; color:var(--text-muted);">${b.rejection_reason || '-'}</td></tr>`;
                            }).join('')}
                        </tbody>
                    </table>
                `;
            } else {
                banksHtml = '<div style="text-align:center; padding:20px; color:var(--text-light);">No banks assigned yet.</div>';
            }
            
            // Timeline HTML
            let timelineHtml = '<div class="timeline" style="margin-top:16px;">';
            if(app.timeline && app.timeline.length > 0) {
                app.timeline.forEach(t => {
                    timelineHtml += `
                        <div style="border-left: 2px solid var(--border); padding-left: 12px; margin-bottom: 12px; position:relative;">
                            <div style="position:absolute; left:-6px; top:4px; width:10px; height:10px; background:var(--primary); border-radius:50%;"></div>
                            <div style="font-size:11px; color:var(--text-muted);">${t.created_at}</div>
                            <div style="font-size:13px; color:var(--text-primary);">${t.description}</div>
                            <div style="font-size:11px; color:var(--text-light);">By: ${t.username}</div>
                        </div>
                    `;
                });
            } else {
                timelineHtml += '<div style="color:var(--text-muted); font-size:12px;">No activities recorded.</div>';
            }
            timelineHtml += '</div>';

            // CIBIL & Source & TAT Bar
            let cibilColor = app.cibil_score >= 750 ? 'badge-success' : (app.cibil_score >= 650 ? 'badge-warning' : 'badge-danger');
            let metaBar = `
                <div style="display:flex; gap:16px; background:#f8fafc; padding:12px; border-radius:var(--radius-md); border:1px solid #e2e8f0; margin-bottom:16px; flex-wrap:wrap;">
                    <div><span style="font-size:11px; color:var(--text-muted); display:block; text-transform:uppercase; font-weight:700;">TAT Ageing</span>
                        <span class="badge ${app.tat_days > 7 ? 'badge-danger' : 'badge-info'}">⏱️ ${app.tat_days} days in system</span>
                    </div>
                    ${app.cibil_score ? `<div><span style="font-size:11px; color:var(--text-muted); display:block; text-transform:uppercase; font-weight:700;">CIBIL</span>
                        <span class="badge ${cibilColor}">${app.cibil_score}</span></div>` : ''}
                    <div><span style="font-size:11px; color:var(--text-muted); display:block; text-transform:uppercase; font-weight:700;">Source</span>
                        <span style="font-weight:600; font-size:13px;">${app.lead_source || 'Direct'} ${app.referral_id ? '(' + app.referral_id + ')' : ''}</span>
                    </div>
                    <div><span style="font-size:11px; color:var(--text-muted); display:block; text-transform:uppercase; font-weight:700;">Added By</span>
                        <span style="font-weight:600; font-size:13px;">${app.added_by || 'System'}</span>
                    </div>
                </div>
            `;

            let actionButtons = '';
            if (app.phase1_completion === 100 && app.phase2_completion === 100) {
                let msg = encodeURIComponent(`Hello Bank Team,
Sharing complete file for ${app.customer_name}.
Loan Type: ${app.loan_type}
Amount: ${app.loan_amount_requested}
CIBIL: ${app.cibil_score || 'N/A'}
Please process.`);
                actionButtons = `
                    <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid #10b981; padding: 12px; border-radius: var(--radius-md); display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <div style="color: #047857; font-weight: 600;"><i data-lucide="check-circle" style="vertical-align:-2px; width:16px;"></i> File 100% Ready for Bank Dispatch</div>
                        <div style="display: flex; gap: 8px;">
                            <button type="button" onclick="openBankDispatchModal(${app.id}, '${app.customer_name}', '${app.loan_id}', '${app.loan_type}', '${app.loan_amount_requested}', ${app.documents ? app.documents.length : 0})" class="btn btn-primary" style="background:#0284c7; border:none; padding:6px 12px; font-size:13px;"><i data-lucide="mail" style="width:14px;"></i> Email</button>
                            <a href="https://wa.me/?text=${msg}" target="_blank" class="btn btn-primary" style="background:#16a34a; border:none; padding:6px 12px; font-size:13px;"><i data-lucide="message-circle" style="width:14px;"></i> WhatsApp</a>
                        </div>
                    </div>
                `;
            }

            pane.innerHTML = `
                ${app.overall_status === 'Completed' ? '<div style="background: var(--status-won); color: white; padding: 12px; border-radius: 8px 8px 0 0; margin: -24px -24px 20px -24px; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px; gap: 8px;"><i data-lucide="award" style="width:18px;"></i> Loan Application Successfully Completed</div>' : ''}
                ${app.overall_status === 'Rejected' ? '<div style="background: var(--danger); color: white; padding: 12px; border-radius: 8px 8px 0 0; margin: -24px -24px 20px -24px; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px; gap: 8px;"><i data-lucide="x-octagon" style="width:18px;"></i> Loan Application Rejected</div>' : ''}
                
                ${actionButtons}
                
                <div class="detail-header" style="margin-bottom: 16px;">
                    <div>
                        <div class="detail-company-title" style="font-size: 24px; color:var(--text-primary); margin-bottom:4px;">${app.customer_name}</div>
                        <div style="font-size: 14px; color: var(--text-muted); font-weight:600;">Loan ID: <span style="color:var(--primary);">${app.loan_id}</span></div>
                    </div>
                    <div style="text-align: right;">
                        <span class="badge-status ${app.overall_status.toLowerCase().replace(' ', '')}" style="font-size: 14px; padding: 8px 16px;">
                            ${app.overall_status === 'Phase 1' && app.phase1_completion === 100 && app.documents && app.documents.length > 0 ? 'Phase 2' : app.overall_status}
                        </span>
                    </div>
                </div>
                
                ${metaBar}
                
                ${app.phase1_completion < 100 ? `
                    <div style="background: #fffbeb; border: 1px solid #fcd34d; padding: 12px 16px; border-radius: var(--radius-md); margin-bottom: 20px; display:flex; justify-content:space-between; align-items:center;">
                        <div>
                            <div style="color: #b45309; font-weight: 700; margin-bottom:4px;"><i data-lucide="alert-triangle" style="width:16px; vertical-align:-3px;"></i> Incomplete Phase 1 (${app.phase1_completion}%)</div>
                            <div style="color: #92400e; font-size: 12px;">Missing: <strong>${app.phase1_missing.join(', ')}</strong></div>
                        </div>
                        <a href="add_applicant.php?id=${app.id}" class="btn" style="background:#f59e0b; color:white; border:none; padding:6px 12px; font-size:12px;">Complete Profile</a>
                    </div>
                ` : `
                    <div style="margin-bottom: 20px; display:flex; align-items:center; gap:8px;">
                        <span class="badge badge-success"><i data-lucide="check" style="width:14px; vertical-align:-2px;"></i> Phase 1 Profile 100% Complete</span>
                        <a href="add_applicant.php?id=${app.id}" class="btn btn-secondary" style="padding:2px 8px; font-size:11px;">Edit Details</a>
                    </div>
                `}
                
                <!-- LOS Pipeline Tracker -->
                <div class="detail-block-title" style="margin-top:24px;">LOS Pipeline Progress</div>
                <div class="pipeline-tracker">
                    <div class="pipeline-step ${app.phase1_completion === 100 ? 'completed' : ''}">
                        <div class="pipeline-icon-circle"><i data-lucide="user-check" style="width:14px;"></i></div>
                        <span class="pipeline-step-label">1. KYC</span>
                    </div>
                    <div class="pipeline-step ${app.phase2_completion === 100 ? 'completed' : ''}">
                        <div class="pipeline-icon-circle"><i data-lucide="file-text" style="width:14px;"></i></div>
                        <span class="pipeline-step-label">2. Docs</span>
                    </div>
                    <div class="pipeline-step ${steps.phase3 ? 'completed' : ''}">
                        <div class="pipeline-icon-circle"><i data-lucide="landmark" style="width:14px;"></i></div>
                        <span class="pipeline-step-label">3. Bank</span>
                    </div>
                    <div class="pipeline-step ${steps.phase4 ? 'completed' : ''}">
                        <div class="pipeline-icon-circle"><i data-lucide="coins" style="width:14px;"></i></div>
                        <span class="pipeline-step-label">4. Disb.</span>
                    </div>
                </div>
                
                <div class="detail-block-title" style="margin-top: 32px; display:flex; justify-content:space-between; align-items:center;">
                    <span>Action Dashboard</span>
                </div>
                
                <!-- Notes Input -->
                <div style="margin-bottom: 20px; display:flex; gap:8px;">
                    <input type="text" id="noteInput" placeholder="Add an internal note or remark..." style="flex:1; padding:8px 12px; border:1px solid var(--border); border-radius:var(--radius-md);">
                    <button onclick="addNote(${app.id})" class="btn btn-secondary" style="padding:8px 16px;"><i data-lucide="send" style="width:16px;"></i></button>
                </div>

                <div class="dashboard-layout-row" style="grid-template-columns: 2fr 1fr; gap:24px;">
                    <div>
                        <div class="detail-block-title">Phase 2: Documents</div>
                        <div style="margin-bottom:12px;"><a href="applicant_documents.php?id=${app.id}" class="btn btn-secondary" style="padding:4px 12px; font-size:12px;"><i data-lucide="upload" style="width:14px;"></i> Manage Documents</a></div>
                        ${docsHtml}
                        
                        <div class="detail-block-title" style="margin-top: 32px;">Phase 3: Bank Processing</div>
                        <div style="margin-bottom:12px;"><a href="applicant_disbursements.php?id=${app.id}" class="btn btn-secondary" style="padding:4px 12px; font-size:12px;"><i data-lucide="landmark" style="width:14px;"></i> Bank Processing</a></div>
                        ${banksHtml}
                        
                        <div class="detail-block-title" style="margin-top: 32px;">Phase 4: Final Disbursements</div>
                        <div style="margin-bottom:12px;"><a href="applicant_bank_assign.php?id=${app.id}" class="btn btn-secondary" style="padding:4px 12px; font-size:12px;"><i data-lucide="coins" style="width:14px;"></i> Customer Kundli & Disb.</a></div>
                        ${disbHtml}
                    </div>
                    
                    <div style="background:#f8fafc; border:1px solid var(--border); border-radius:var(--radius-md); padding:16px;">
                        <h4 style="margin:0 0 16px 0; font-size:14px; color:var(--text-primary);"><i data-lucide="clock" style="width:16px; vertical-align:-2px;"></i> Activity Timeline</h4>
                        ${timelineHtml}
                    </div>
                </div>
            `;
            
            lucide.createIcons();


        } catch (err) {
            console.error(err);
            showNotification('Could not load applicant LOS details.', 'error');
        }
    }

    function switchDetailTab(tabName) {
        document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        
        document.getElementById('tab-btn-' + tabName).classList.add('active');
        document.getElementById('tab-content-' + tabName).classList.add('active');
    }

    async function deleteApplicant(id) {
        if (!confirm('Are you sure you want to completely delete this application? This action cannot be undone.')) return;
        
        try {
            const formData = new FormData();
            formData.append('id', id);
            
            const res = await fetch('config.php?api=delete_applicant', {
                method: 'POST',
                body: formData
            });
            
            const text = await res.text();
            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                console.error("Failed to parse JSON. Server returned:", text);
                alert("Server Error. Please check console for details.\n\n" + text.substring(0, 100));
                return;
            }

            if (data.success) {
                alert('Application deleted successfully.');
                location.reload();
            } else {
                alert(data.error || 'Failed to delete application.');
            }
        } catch (err) {
            console.error(err);
            alert('A network error occurred: ' + err.message);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        triggerSearch();
        
        const urlParams = new URLSearchParams(window.location.search);
        const viewId = urlParams.get('id');
        if (viewId) {
            setTimeout(() => {
                selectApplicantCard(viewId);
            }, 600);
        }
    });
