<?php
require_once __DIR__ . '/../config.php';
$page_title = 'My Workspace';
$page_subtitle = 'Staff Activity & Performance Portal';
require_once __DIR__ . '/header.php';
?>

<div id="view-dashboard" class="view-container">
    <!-- Stats row -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-card-header">
                <span class="stat-label">Assigned Clients</span>
                <div class="stat-icon-wrapper"><i data-lucide="users"></i></div>
            </div>
            <div class="stat-value" id="stat-total-clients">-</div>
            <span style="font-size: 11px; color: var(--text-muted);">Your registered client accounts</span>
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
                { label: 'Pitches Made', count: data.funnel.pitches, color: 'var(--status-new)', icon: 'send' },
                { label: 'PPTs Presented', count: data.funnel.ppts, color: 'var(--status-contacted)', icon: 'presentation' },
                { label: 'Custom Mails Sent', count: data.funnel.mails, color: 'var(--status-negotiation)', icon: 'mail' },
                { label: 'Quotations Created', count: data.funnel.quotes, color: 'var(--status-won)', icon: 'file-text' },
                { label: 'Deals Closed Won', count: data.funnel.closed, color: '#10b981', icon: 'check-circle' }
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

            // 3. Top Clients by Quote Value Bar Chart
            const topLabels = data.top_clients.map(i => i.company_name);
            const topValues = data.top_clients.map(i => i.total_quote);

            if (topClientsChart) topClientsChart.destroy();
            const ctxTop = document.getElementById('chart-top-clients').getContext('2d');
            topClientsChart = new Chart(ctxTop, {
                type: 'bar',
                data: {
                    labels: topLabels,
                    datasets: [{
                        label: 'Quotation Amount (₹)',
                        data: topValues,
                        backgroundColor: 'rgba(249, 115, 22, 0.85)',
                        hoverBackgroundColor: '#ea580c',
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                color: '#94a3b8',
                                callback: function(value) {
                                    if (value >= 100000) return '₹' + (value / 100000).toFixed(1) + 'L';
                                    if (value >= 1000) return '₹' + (value / 1000).toFixed(0) + 'k';
                                    return '₹' + value;
                                }
                            },
                            grid: { color: '#f1f5f9' }
                        },
                        x: {
                            ticks: {
                                color: '#94a3b8',
                                callback: function(val, index) {
                                    const label = topLabels[index] || '';
                                    return label.length > 12 ? label.substring(0, 10) + '..' : label;
                                }
                            },
                            grid: { display: false }
                        }
                    }
                }
            });

            // 4. Weekly CRM Interactions
            const weeklyLabels = data.activity_weekly.map(i => i.label);
            const weeklyValues = data.activity_weekly.map(i => i.value);

            if (weeklyActivityChart) weeklyActivityChart.destroy();
            const ctxWeekly = document.getElementById('chart-weekly-activity').getContext('2d');
            weeklyActivityChart = new Chart(ctxWeekly, {
                type: 'line',
                data: {
                    labels: weeklyLabels,
                    datasets: [{
                        label: 'Interactions log count',
                        data: weeklyValues,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.08)',
                        fill: true,
                        tension: 0.3,
                        borderWidth: 2,
                        pointRadius: 4,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#10b981',
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

<?php require_once __DIR__ . '/footer.php'; ?>
