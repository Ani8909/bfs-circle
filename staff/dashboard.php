<?php
require_once __DIR__ . '/../config.php';
$page_title = 'My Workspace';
$page_subtitle = 'Staff Activity & Performance Portal';
require_once __DIR__ . '/header.php';
?>

<div id="view-dashboard" class="view-container">
    <!-- Stats row -->
    <div class="stats-grid">
        <div class="stat-card" style="cursor: pointer; border-left: 4px solid var(--primary);" onclick="showAllClientsModal()">
            <div class="stat-card-header">
                <span class="stat-label">Assigned Clients</span>
                <div class="stat-icon-wrapper"><i data-lucide="users"></i></div>
            </div>
            <div class="stat-value" id="stat-total-clients"><div class="skeleton skeleton-text" style="width: 60px; height: 28px; margin: 0;"></div></div>
            <span style="font-size: 11px; color: var(--text-muted);">Click to view client list</span>
        </div>
        <div class="stat-card" style="cursor: pointer; border-left: 4px solid var(--success);" onclick="showAllClientsModal('Closed Won')">
            <div class="stat-card-header">
                <span class="stat-label">Won Deals</span>
                <div class="stat-icon-wrapper" style="background: #dcfce7; color: var(--success);"><i data-lucide="trophy"></i></div>
            </div>
            <div class="stat-value" id="stat-won-deals"><div class="skeleton skeleton-text" style="width: 50px; height: 28px; margin: 0;"></div></div>
            <span style="font-size: 11px; color: var(--text-muted);">Click to view won deals</span>
        </div>

        <div class="stat-card" style="cursor: pointer; border-left: 4px solid var(--primary);" onclick="showEmailsTodayModal()">
            <div class="stat-card-header">
                <span class="stat-label">Emails Sent Today</span>
                <div class="stat-icon-wrapper"><i data-lucide="send"></i></div>
            </div>
            <div class="stat-value" id="stat-emails-today"><div class="skeleton skeleton-text" style="width: 40px; height: 28px; margin: 0;"></div></div>
            <span style="font-size: 11px; color: var(--text-muted);">Click to view dispatched communications</span>
        </div>
        <div class="stat-card">
            <div class="stat-card-header">
                <span class="stat-label">Quotations This Month</span>
                <div class="stat-icon-wrapper"><i data-lucide="file-check"></i></div>
            </div>
            <div class="stat-value" id="stat-quotes-month"><div class="skeleton skeleton-text" style="width: 40px; height: 28px; margin: 0;"></div></div>
            <span style="font-size: 11px; color: var(--text-muted);">Drafted proposal invoices</span>
        </div>
        <div class="stat-card" style="cursor: pointer; border-left: 4px solid var(--primary);" onclick="showAllClientsModal('Pending Follow-ups')">
            <div class="stat-card-header">
                <span class="stat-label">Pending Follow-ups</span>
                <div class="stat-icon-wrapper" style="background-color: var(--status-lost-light); color: var(--status-lost);"><i data-lucide="clock"></i></div>
            </div>
            <div class="stat-value" id="stat-pending-followups"><div class="skeleton skeleton-text" style="width: 50px; height: 28px; margin: 0;"></div></div>
            <span style="font-size: 11px; color: var(--status-lost); font-weight: 500;">Click to view hot clients pending</span>
        </div>

        <div class="stat-card">
            <div class="stat-card-header">
                <span class="stat-label">No Quotations Yet</span>
                <div class="stat-icon-wrapper" style="background-color: var(--status-negotiation-light); color: var(--status-negotiation);"><i data-lucide="alert-circle"></i></div>
            </div>
            <div class="stat-value" id="stat-no-quotation"><div class="skeleton skeleton-text" style="width: 40px; height: 28px; margin: 0;"></div></div>
            <span style="font-size: 11px; color: var(--text-muted);">Clients at introductory stage</span>
        </div>
    </div>

    <!-- Dashboard Row 2: Charts and Activities -->
    <div class="dashboard-layout-row">
        <!-- Left panel: Grid of Charts -->
        <div>
            <div class="charts-row-inner">
                <!-- Growth Line Chart -->
                <div class="card" style="margin-bottom: 0;">
                    <div class="card-title-bar">
                        <h2>Client Growth (Month-wise)</h2>
                        <i data-lucide="trending-up" style="color: var(--primary);"></i>
                    </div>
                    <div style="height: 220px; position: relative;">
                        <canvas id="chart-client-growth"></canvas>
                    </div>
                </div>
                
                <!-- Communication Conversion Funnel -->
                <div class="card" style="margin-bottom: 0;">
                    <div class="card-title-bar">
                        <h2>Communication Funnel</h2>
                        <i data-lucide="filter" style="color: var(--primary);"></i>
                    </div>
                    <div class="funnel-container" id="funnel-visualization-container">
                        <!-- Generated Dynamically by JS -->
                    </div>
                </div>
            </div>
            
            <div class="charts-row-inner" style="margin-top: 24px; margin-bottom: 0;">


                <!-- Weekly activities counts -->
                <div class="card" style="margin-bottom: 0;">
                    <div class="card-title-bar">
                        <h2>Weekly CRM Interactions</h2>
                        <i data-lucide="calendar" style="color: var(--primary);"></i>
                    </div>
                    <div style="height: 220px; position: relative;">
                        <canvas id="chart-weekly-activity"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right panel: Activity Log -->
        <div class="card" style="margin-bottom: 0;">
            <div class="card-title-bar">
                <h2>My Recent Activities</h2>
                <i data-lucide="bell" style="color: var(--primary);"></i>
            </div>
            <div style="margin-bottom: 12px; display: flex; gap: 8px;">
                <select id="feed-date-filter" onchange="loadRecentActivities()" style="width: 100%; padding: 6px; font-size:12px; border-radius:4px; border:1px solid var(--border);">
                    <option value="0">All Time</option>
                    <option value="1">Last 24 Hours</option>
                    <option value="7">Last 7 Days</option>
                    <option value="30">Last 30 Days</option>
                </select>
            </div>
            <div class="activity-feed" id="dashboard-activity-feed">
                <!-- Skeletons -->
                <div class="skeleton-row"><div class="skeleton skeleton-avatar"></div><div style="flex:1"><div class="skeleton skeleton-text medium"></div><div class="skeleton skeleton-text short"></div></div></div>
                <div class="skeleton-row"><div class="skeleton skeleton-avatar"></div><div style="flex:1"><div class="skeleton skeleton-text long"></div><div class="skeleton skeleton-text short"></div></div></div>
                <div class="skeleton-row"><div class="skeleton skeleton-avatar"></div><div style="flex:1"><div class="skeleton skeleton-text medium"></div><div class="skeleton skeleton-text short"></div></div></div>
                <div class="skeleton-row"><div class="skeleton skeleton-avatar"></div><div style="flex:1"><div class="skeleton skeleton-text long"></div><div class="skeleton skeleton-text short"></div></div></div>
            </div>
        </div>
    </div>
</div>

<script>
    let clientGrowthChart = null;
    let topClientsChart = null;
    let weeklyActivityChart = null;

    async function loadDashboardStats() {
        try {
            const response = await fetch('?api=stats');
            const data = await response.json();
            
            if(document.getElementById('stat-total-clients')) document.getElementById('stat-total-clients').innerText = data.total_clients;
            if(document.getElementById('stat-won-deals')) document.getElementById('stat-won-deals').innerText = data.won_deals;
            if(document.getElementById('stat-won-revenue')) document.getElementById('stat-won-revenue').innerText = formatIndianCurrency(data.won_revenue);
            if(document.getElementById('stat-emails-today')) document.getElementById('stat-emails-today').innerText = data.emails_today;
            if(document.getElementById('stat-quotes-month')) document.getElementById('stat-quotes-month').innerText = data.quotes_this_month;
            if(document.getElementById('stat-pending-followups')) document.getElementById('stat-pending-followups').innerText = data.pending_followups;
            if(document.getElementById('stat-total-val')) document.getElementById('stat-total-val').innerText = formatIndianCurrency(data.total_quote_value);
            if(document.getElementById('stat-no-quotation')) document.getElementById('stat-no-quotation').innerText = data.no_quotation_clients;
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
                        tension: 0.3,
                        borderWidth: 2,
                        pointRadius: 4,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#f97316',
                        pointBorderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0, color: '#94a3b8' },
                            grid: { color: '#f1f5f9' }
                        },
                        x: {
                            ticks: { color: '#94a3b8' },
                            grid: { display: false }
                        }
                    }
                }
            });

            // 2. Communication funnel metrics visualization
            const funnelContainer = document.getElementById('funnel-visualization-container');
            funnelContainer.innerHTML = '';
            
            const funnelStages = [
                { label: 'Pitches Made', count: data.funnel[0].count, color: 'var(--status-new)', icon: 'send' },
                { label: 'PPTs Presented', count: data.funnel[1].count, color: 'var(--status-contacted)', icon: 'presentation' },
                { label: 'Custom Mails Sent', count: data.funnel[2].count, color: 'var(--status-negotiation)', icon: 'mail' },
                { label: 'Quotations Created', count: data.funnel[3].count, color: 'var(--status-won)', icon: 'file-text' },
                { label: 'Deals Closed Won', count: data.funnel[4].count, color: '#10b981', icon: 'check-circle' }
            ];

            const maxCount = Math.max(...funnelStages.map(s => s.count), 1);

            funnelStages.forEach((stage) => {
                const percentage = Math.round((stage.count / maxCount) * 100);
                const row = document.createElement('div');
                row.className = 'funnel-bar-row';
                row.innerHTML = `
                    <div class="funnel-bar-label">
                        <i data-lucide="${stage.icon}"></i>
                        <span>${stage.label}</span>
                    </div>
                    <div style="flex-grow:1; margin: 0 15px; position:relative;">
                        <div class="funnel-bar-fill" style="width: ${percentage}%; background-color: ${stage.color};"></div>
                    </div>
                    <div class="funnel-bar-value">${stage.count}</div>
                `;
                funnelContainer.appendChild(row);
            });
            lucide.createIcons();

            // 3. Weekly Activity Chart
            const weeklyLabels = data.activity_weekly.map(i => i.label);
            const weeklyValues = data.activity_weekly.map(i => i.value);
            
            if (weeklyActivityChart) weeklyActivityChart.destroy();
            const ctxWeekly = document.getElementById('chart-weekly-activity').getContext('2d');
            weeklyActivityChart = new Chart(ctxWeekly, {
                type: 'bar',
                data: {
                    labels: weeklyLabels,
                    datasets: [{
                        label: 'Interactions',
                        data: weeklyValues,
                        backgroundColor: '#f59e0b',
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0, color: '#94a3b8' },
                            grid: { color: '#f1f5f9' }
                        },
                        x: {
                            ticks: { color: '#94a3b8' },
                            grid: { display: false }
                        }
                    }
                }
            });

        } catch (err) {
            console.error("Charts loading error: ", err);
        }
    }

    async function loadRecentActivities() {
        try {
            const days = document.getElementById('feed-date-filter').value;
            const response = await fetch(`?api=recent_activities&days=${days}`);
            const data = await response.json();
            const feed = document.getElementById('dashboard-activity-feed');
            
            if (data.length === 0) {
                feed.innerHTML = `
                    <div style="text-align: center; padding: 40px 10px; color: var(--text-light);">
                        <i data-lucide="clock-alert" style="width:36px;height:36px;stroke-width:1.5;margin-bottom:8px;"></i>
                        <p style="font-size:13px;">No interactions logged for this time window.</p>
                    </div>
                `;
                lucide.createIcons();
                return;
            }
            
            let html = '';
            data.forEach(act => {
                let desc = act.description || '';
                desc = desc.replace(/^\[(.*?)\]/, '<span style="font-weight:600;color:var(--primary);">$1</span>');
                
                html += `
                    <div class="feed-item">
                        <div class="feed-marker"></div>
                        <div class="feed-content">
                            <p style="font-size: 13.5px; line-height: 1.4; color: var(--text-primary); font-weight:500;">${desc}</p>
                            <span class="feed-time">${act.time_formatted || ''}</span>
                        </div>
                    </div>
                `;
            });
            feed.innerHTML = html;
        } catch (err) {
            console.error(err);
        }
    }

    function formatIndianCurrency(amount) {
        let x = Math.round(amount).toString();
        let lastThree = x.slice(-3);
        let otherNumbers = x.slice(0, -3);
        if (otherNumbers !== '') lastThree = ',' + lastThree;
        let res = otherNumbers.replace(/\B(?=(\d{2})+(?!\d))/g, ",") + lastThree;
        return '₹' + res;
    }

    window.addEventListener('DOMContentLoaded', () => {
        loadDashboardStats();
        loadDashboardCharts();
        loadRecentActivities();
        
        setInterval(loadRecentActivities, 60000);
    });
</script>

<!-- All Clients Modal -->
<div id="all-clients-modal" class="modal" style="display:none; z-index:10000;">
    <div class="modal-content" style="max-width: 900px;">
        <div class="modal-header">
            <h3>Registered Clients List</h3>
            <button class="modal-close" onclick="closeAllClientsModal()"><i data-lucide="x"></i></button>
        </div>
        <div class="modal-body" style="max-height:500px; overflow-y:auto; padding-top:0;">
            <table class="data-table" style="width:100%; text-align:left;">
                <thead>
                    <tr>
                        <th>Company Name</th>
                        <th>Contact Person</th>
                        <th>Mobile</th>
                        <th>Status</th>
                        <th>Assigned To</th>
                        <th>Added On</th>
                    </tr>
                </thead>
                <tbody id="all-clients-table-body">
                    <tr><td colspan="6" style="text-align:center; padding: 20px;">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Emails Today Modal -->
<div id="emails-today-modal" class="modal" style="display:none; z-index:10000;">
    <div class="modal-content" style="max-width: 900px;">
        <div class="modal-header">
            <h3>📨 Emails Sent Today</h3>
            <button class="modal-close" onclick="closeEmailsTodayModal()"><i data-lucide="x"></i></button>
        </div>
        <div class="modal-body" style="max-height:500px; overflow-y:auto; padding-top:0;">
            <table class="data-table" style="width:100%; text-align:left;">
                <thead>
                    <tr>
                        <th>Client / Company</th>
                        <th>Type</th>
                        <th>Subject</th>
                        <th>Sent By</th>
                        <th>Time</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="emails-today-table-body">
                    <tr><td colspan="6" style="text-align:center; padding: 20px;">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- View Email Content Modal -->
<div id="view-email-modal" class="modal" style="display:none; z-index:10005;">
    <div class="modal-content" style="max-width: 800px;">
        <div class="modal-header">
            <h3>Email Content Preview</h3>
            <button class="modal-close" onclick="closeViewEmailModal()"><i data-lucide="x"></i></button>
        </div>
        <div class="modal-body" style="max-height:650px; overflow-y:auto; background: #f8fafc; border-radius: 8px; padding: 16px;">
            <div id="view-email-body-content" style="white-space: pre-wrap; font-family: sans-serif; font-size: 14px; color: #334155;"></div>
        </div>
    </div>
</div>

<script>
    let todayEmailsData = [];

    async function showEmailsTodayModal() {
        document.getElementById('emails-today-modal').style.display = 'flex';
        try {
            const response = await fetch('?api=emails_sent_today_list');
            const result = await response.json();
            
            if (!result.success) return;
            
            todayEmailsData = result.data;
            
            let html = '';
            if (todayEmailsData.length === 0) {
                html = '<tr><td colspan="6" style="text-align:center;color:var(--text-light);">No emails sent today</td></tr>';
            } else {
                todayEmailsData.forEach((email, index) => {
                    const time = new Date(email.sent_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                    html += `<tr>
                        <td>
                            <div style="font-weight:600;">${email.company_name}</div>
                            <div style="font-size:11px; color:var(--text-muted);">${email.client_email}</div>
                        </td>
                        <td><span style="font-size: 12px; font-weight: 500; color: var(--primary);">${email.type}</span></td>
                        <td style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="${email.subject}">${email.subject}</td>
                        <td>${email.sent_by}</td>
                        <td style="color:var(--text-muted); font-size:12px;">${time}</td>
                        <td>
                            <button onclick="viewEmailContent(${index})" class="btn" style="padding:4px 8px; font-size:11px;">View</button>
                        </td>
                    </tr>`;
                });
            }
            document.getElementById('emails-today-table-body').innerHTML = html;
        } catch (e) {
            showNotification('Failed to load today emails list', 'error');
        }
    }
    
    function closeEmailsTodayModal() {
        document.getElementById('emails-today-modal').style.display = 'none';
    }

    function viewEmailContent(index) {
        const email = todayEmailsData[index];
        if(!email) return;
        
        let htmlContent = email.body;
        if (email.attachment_name) {
            const attachmentUrl = '../uploads/' + email.attachment_name;
            htmlContent += `\n\n<hr style="border:0; border-top:1px solid #e2e8f0; margin: 15px 0;">`;
            htmlContent += `<div style="font-weight:600; margin-bottom:10px;">📎 Attached Document:</div>`;
            htmlContent += `<iframe src="${attachmentUrl}" style="width:100%; height:450px; border:1px solid #cbd5e1; border-radius:6px; background:#fff;"></iframe>`;
            htmlContent += `<div style="margin-top:8px; text-align:right;"><a href="${attachmentUrl}" target="_blank" style="color:var(--primary); text-decoration:none; font-size:12px; display:inline-flex; align-items:center; gap:4px;">Open in new tab <i data-lucide="external-link" style="width:12px;height:12px;"></i></a></div>`;
        }
        
        document.getElementById('view-email-body-content').innerHTML = htmlContent;
        if (typeof lucide !== 'undefined') lucide.createIcons();
        document.getElementById('view-email-modal').style.display = 'flex';
    }

    function closeViewEmailModal() {
        document.getElementById('view-email-modal').style.display = 'none';
    }

    async function showAllClientsModal(status = null) {
        const titleEl = document.querySelector('#all-clients-modal .modal-header h3');
        if (status === 'Closed Won') {
            titleEl.innerHTML = '🏆 Won Deals List';
        } else if (status === 'Pending Follow-ups') {
            titleEl.innerHTML = '⏰ Pending Follow-ups List';
        } else {
            titleEl.innerHTML = 'Registered Clients List';
        }
        
        document.getElementById('all-clients-modal').style.display = 'flex';
        
        try {
            const url = status ? `?api=all_clients_list&status=${encodeURIComponent(status)}` : '?api=all_clients_list';
            const response = await fetch(url);
            const result = await response.json();
            
            if (!result.success) return;
            
            let html = '';
            if (result.data.length === 0) {
                html = '<tr><td colspan="6" style="text-align:center;color:var(--text-light);">No clients found</td></tr>';
            } else {
                result.data.forEach(c => {
                    const statusClass = c.overall_status ? c.overall_status.toLowerCase().replace(' ', '') : 'new';
                    html += `<tr>
                        <td style="font-weight:600;">${c.company_name}</td>
                        <td>${c.contact_name}</td>
                        <td>${c.mobile}</td>
                        <td><span class="badge-status ${statusClass}">${c.overall_status}</span></td>
                        <td>${c.assigned_to || '<span style="color:var(--text-light); font-size:11px;">Unassigned</span>'}</td>
                        <td style="color:var(--text-muted); font-size:12px;">${new Date(c.created_at).toLocaleDateString()}</td>
                    </tr>`;
                });
            }
            document.getElementById('all-clients-table-body').innerHTML = html;
        } catch (e) {
            showNotification('Failed to load clients list', 'error');
        }
    }
    
    function closeAllClientsModal() {
        document.getElementById('all-clients-modal').style.display = 'none';
    }
</script>

<?php require_once 'footer.php'; ?>
