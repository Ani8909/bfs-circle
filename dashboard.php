<?php
require_once 'config.php';
$page_title = 'Dashboard';
$page_subtitle = 'AuraCRM Operations Control Panel';
require_once 'header.php';
?>

<div id="view-dashboard" class="view-container">
    <!-- Stats row -->
    <div class="stats-grid">
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin'): ?>
        <div class="stat-card" style="border-left: 4px solid var(--primary); cursor: pointer;" onclick="showOnlineStaffModal();">
            <div class="stat-card-header">
                <span class="stat-label">Online Staff</span>
                <div class="stat-icon-wrapper" style="background: var(--primary-light); color: var(--primary);"><i data-lucide="shield-check"></i></div>
            </div>
            <div class="stat-value" id="stat-active-staff">-</div>
            <span style="font-size: 11px; color: var(--text-muted);">Click to view active staff</span>
        </div>
        <?php endif; ?>
        <div class="stat-card">
            <div class="stat-card-header">
                <span class="stat-label">Total Clients</span>
                <div class="stat-icon-wrapper"><i data-lucide="users"></i></div>
            </div>
            <div class="stat-value" id="stat-total-clients">-</div>
            <span style="font-size: 11px; color: var(--text-muted);">Database registered accounts</span>
        </div>
        <div class="stat-card">
            <div class="stat-card-header">
                <span class="stat-label">Emails Sent Today</span>
                <div class="stat-icon-wrapper"><i data-lucide="send"></i></div>
            </div>
            <div class="stat-value" id="stat-emails-today">-</div>
            <span style="font-size: 11px; color: var(--text-muted);">Dispatched communications</span>
        </div>
        <div class="stat-card">
            <div class="stat-card-header">
                <span class="stat-label">Quotations This Month</span>
                <div class="stat-icon-wrapper"><i data-lucide="file-check"></i></div>
            </div>
            <div class="stat-value" id="stat-quotes-month">-</div>
            <span style="font-size: 11px; color: var(--text-muted);">Drafted proposal invoices</span>
        </div>
        <div class="stat-card">
            <div class="stat-card-header">
                <span class="stat-label">Pending Follow-ups</span>
                <div class="stat-icon-wrapper" style="background-color: var(--status-lost-light); color: var(--status-lost);"><i data-lucide="clock"></i></div>
            </div>
            <div class="stat-value" id="stat-pending-followups">-</div>
            <span style="font-size: 11px; color: var(--status-lost); font-weight: 500;">Hot Leads requiring follow-up</span>
        </div>
        <div class="stat-card">
            <div class="stat-card-header">
                <span class="stat-label">Total Quotation Value</span>
                <div class="stat-icon-wrapper"><i data-lucide="indian-rupee"></i></div>
            </div>
            <div class="stat-value" id="stat-total-val">-</div>
            <span style="font-size: 11px; color: var(--text-muted);">Sum of generated quotes</span>
        </div>
        <div class="stat-card">
            <div class="stat-card-header">
                <span class="stat-label">No Quotations Yet</span>
                <div class="stat-icon-wrapper" style="background-color: var(--status-negotiation-light); color: var(--status-negotiation);"><i data-lucide="alert-circle"></i></div>
            </div>
            <div class="stat-value" id="stat-no-quotation">-</div>
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
                <!-- Top Clients by quotation value -->
                <div class="card" style="margin-bottom: 0;">
                    <div class="card-title-bar">
                        <h2>Top Clients by Quotation Value</h2>
                        <i data-lucide="award" style="color: var(--primary);"></i>
                    </div>
                    <div style="height: 220px; position: relative;">
                        <canvas id="chart-top-clients"></canvas>
                    </div>
                </div>

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
                <h2>Recent Activity Feed</h2>
                <i data-lucide="bell" style="color: var(--primary);"></i>
            </div>
            <div style="margin-bottom: 12px; display: flex; gap: 8px;">
                <select id="feed-user-filter" onchange="loadRecentActivities()" style="padding: 6px; font-size:12px; border-radius:4px; border:1px solid var(--border);">
                    <option value="">All Users</option>
                    <option value="System">System</option>
                    <?php
                    // Populate user list
                    $users_res = $db->query("SELECT username FROM users ORDER BY username ASC")->fetchAll(PDO::FETCH_COLUMN);
                    foreach($users_res as $uname) {
                        echo "<option value=\"" . htmlspecialchars($uname) . "\">" . htmlspecialchars($uname) . "</option>";
                    }
                    ?>
                </select>
                <select id="feed-date-filter" onchange="loadRecentActivities()" style="padding: 6px; font-size:12px; border-radius:4px; border:1px solid var(--border);">
                    <option value="0">All Time</option>
                    <option value="1">Last 24 Hours</option>
                    <option value="7">Last 7 Days</option>
                    <option value="30">Last 30 Days</option>
                </select>
            </div>
            <div class="activity-feed" id="dashboard-activity-feed">
                <!-- Loaded via API -->
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
            
            document.getElementById('stat-total-clients').innerText = data.total_clients;
            document.getElementById('stat-emails-today').innerText = data.emails_today;
            document.getElementById('stat-quotes-month').innerText = data.quotes_this_month;
            document.getElementById('stat-pending-followups').innerText = data.pending_followups;
            document.getElementById('stat-total-val').innerText = formatIndianCurrency(data.total_quote_value);
            document.getElementById('stat-no-quotation').innerText = data.no_quotation_clients;
            if (document.getElementById('stat-active-staff')) {
                document.getElementById('stat-active-staff').innerText = data.online_staff + ' / ' + data.total_staff;
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

    document.addEventListener('DOMContentLoaded', () => {
        loadDashboardStats();
        loadDashboardCharts();
        loadRecentActivities();
        setInterval(loadRecentActivities, 5000);
    });
    async function showOnlineStaffModal() {
        document.getElementById('online-staff-modal').style.display = 'flex';
        document.getElementById('online-staff-table-body').innerHTML = '<tr><td colspan="4" style="text-align:center;">Loading active staff...</td></tr>';
        
        try {
            const res = await fetch('?api=get_online_staff');
            const data = await res.json();
            
            if (data.error) {
                showNotification(data.error, 'error');
                return;
            }
            
            let html = '';
            if (data.length === 0) {
                html = '<tr><td colspan="4" style="text-align:center;color:var(--text-light);">No staff currently online</td></tr>';
            } else {
                data.forEach(s => {
                    html += `<tr>
                        <td>${s.name || s.username}</td>
                        <td>${s.username}</td>
                        <td>
                            <span style="font-family:monospace; background:var(--bg-main); padding:2px 6px; border-radius:4px; font-size:12px; margin-right: 8px;">${s.last_ip || 'Unknown'}</span>
                            ${s.last_ip && s.last_ip !== '::1' && s.last_ip !== '127.0.0.1' ? `<a href="https://ipinfo.io/${s.last_ip}" target="_blank" style="font-size: 11px; text-decoration: none; color: var(--primary);"><i data-lucide="map-pin" style="width: 12px; height: 12px; margin-right: 2px;"></i>View Map</a>` : '<span style="font-size: 11px; color: var(--text-light);">(Local IP)</span>'}
                        </td>
                        <td><span style="color:var(--success); font-weight:600;">● Online</span></td>
                    </tr>`;
                });
            }
            document.getElementById('online-staff-table-body').innerHTML = html;
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        } catch (e) {
            showNotification('Failed to load online staff', 'error');
        }
    }
    
    function closeOnlineStaffModal() {
        document.getElementById('online-staff-modal').style.display = 'none';
    }
</script>

<!-- Online Staff Modal -->
<div id="online-staff-modal" class="modal" style="display:none; z-index:10000;">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h3>Currently Online Staff</h3>
            <button class="modal-close" onclick="closeOnlineStaffModal()"><i data-lucide="x"></i></button>
        </div>
        <div class="modal-body" style="max-height:400px; overflow-y:auto; padding-top:0;">
            <table class="data-table" style="width:100%; text-align:left;">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Username</th>
                        <th>IP Address</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="online-staff-table-body">
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
