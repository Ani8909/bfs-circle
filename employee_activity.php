<?php
require_once 'config.php';
// Restrict to Admin
if (($_SESSION['role'] ?? '') !== 'Admin') {
    header("Location: dashboard.php");
    exit;
}

$page_title = 'Staff Productivity Dashboard';
$page_subtitle = 'Track employee daily activities and performance metrics';
require_once 'header.php';
?>

<div class="view-container">
    <div style="background: white; padding: 20px; border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
        <div>
            <h2 style="font-size: 18px; color: var(--text-primary); margin-bottom: 5px;">Daily Productivity Tracker</h2>
            <p style="font-size: 13.5px; color: var(--text-muted);">Select a date to see working hours, timeline, and what each employee accomplished.</p>
        </div>
        <div style="display: flex; gap: 12px; align-items: center;">
            <label style="font-weight: 600; color: var(--text-primary); font-size: 14px;">Select Date:</label>
            <input type="date" id="activity-date" class="form-control" value="<?php echo date('Y-m-d'); ?>" onchange="loadProductivity()" style="padding: 10px; border-radius: 8px; border: 1px solid #cbd5e1; outline: none; width: 180px;">
        </div>
    </div>

    <!-- Productivity Grid -->
    <div id="productivity-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px;">
        <!-- Loaded via JS -->
    </div>
</div>

<!-- Detailed Timeline Modal -->
<div id="timeline-modal" class="modal-overlay" style="display:none;">
    <div class="modal-content" style="max-width: 600px; height: 80vh; display: flex; flex-direction: column;">
        <div class="modal-header">
            <h3 id="timeline-title">Activity Timeline</h3>
            <button onclick="closeTimeline()" style="background:none;border:none;cursor:pointer;"><i data-lucide="x"></i></button>
        </div>
        <div class="modal-body" style="overflow-y: auto; padding: 20px; background: #f8fafc;">
            <div id="timeline-container" class="timeline">
                <!-- Loaded via JS -->
            </div>
        </div>
    </div>
</div>

<style>
    .productivity-card {
        background: white;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        padding: 24px;
        transition: transform 0.2s, box-shadow 0.2s;
        border: 1px solid #e2e8f0;
        cursor: pointer;
    }
    .productivity-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-md);
        border-color: var(--primary-light);
    }
    .prod-user {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px dashed #e2e8f0;
    }
    .prod-avatar {
        width: 48px;
        height: 48px;
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        color: white;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 18px;
        box-shadow: 0 4px 10px rgba(79, 70, 229, 0.2);
    }
    .prod-name {
        font-size: 16px;
        font-weight: 700;
        color: var(--text-primary);
    }
    .prod-meta {
        font-size: 12px;
        color: var(--text-muted);
        margin-top: 2px;
    }
    .prod-stats-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }
    .prod-stat-item {
        background: #f8fafc;
        border: 1px solid #f1f5f9;
        border-radius: 8px;
        padding: 12px;
        display: flex;
        flex-direction: column;
    }
    .prod-stat-label {
        font-size: 11px;
        color: var(--text-muted);
        font-weight: 600;
        text-transform: uppercase;
        margin-bottom: 4px;
    }
    .prod-stat-val {
        font-size: 20px;
        font-weight: 700;
        color: var(--text-primary);
    }
    
    /* Timeline styles */
    .timeline {
        position: relative;
        padding-left: 30px;
    }
    .timeline::before {
        content: '';
        position: absolute;
        left: 9px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e2e8f0;
    }
    .timeline-item {
        position: relative;
        margin-bottom: 24px;
    }
    .timeline-item::before {
        content: '';
        position: absolute;
        left: -26px;
        top: 4px;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: var(--primary);
        border: 2px solid white;
        box-shadow: 0 0 0 2px var(--primary-light);
    }
    .timeline-time {
        font-size: 11px;
        font-weight: 600;
        color: var(--primary);
        margin-bottom: 4px;
    }
    .timeline-content {
        background: white;
        padding: 12px 16px;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        font-size: 14px;
        color: var(--text-primary);
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }
</style>

<script>
    async function loadProductivity() {
        const date = document.getElementById('activity-date').value;
        const grid = document.getElementById('productivity-grid');
        grid.innerHTML = '<div style="grid-column: 1 / -1; text-align:center; padding: 40px; color: var(--text-light);">Loading productivity data...</div>';
        
        try {
            const res = await fetch(`?api=get_employee_productivity&date=${date}`);
            const data = await res.json();
            
            grid.innerHTML = '';
            
            if (data.length === 0) {
                grid.innerHTML = `<div style="grid-column: 1 / -1; background: white; text-align:center; padding: 60px; border-radius: 12px; border: 1px solid #e2e8f0; color: var(--text-muted);">
                    <i data-lucide="coffee" style="width: 48px; height: 48px; margin-bottom: 16px; opacity: 0.5;"></i>
                    <h3 style="color:var(--text-primary); margin-bottom: 8px;">No Activity Recorded</h3>
                    <p>There are no recorded activities for ${date}.</p>
                </div>`;
                lucide.createIcons();
                return;
            }
            
            data.forEach(user => {
                const initials = user.username.substring(0, 2).toUpperCase();
                
                const card = document.createElement('div');
                card.className = 'productivity-card';
                card.onclick = () => openTimeline(user.username, date);
                
                card.innerHTML = `
                    <div class="prod-user">
                        <div class="prod-avatar">${initials}</div>
                        <div>
                            <div class="prod-name">${user.username}</div>
                            <div class="prod-meta">Total Actions: <strong>${user.total_actions}</strong></div>
                        </div>
                    </div>
                    <div class="prod-stats-grid">
                        <div class="prod-stat-item">
                            <span class="prod-stat-label">Applicants Added</span>
                            <span class="prod-stat-val" style="color: #10b981;">${user.applicants_added}</span>
                        </div>
                        <div class="prod-stat-item">
                            <span class="prod-stat-label">Docs Uploaded</span>
                            <span class="prod-stat-val" style="color: #3b82f6;">${user.documents_uploaded}</span>
                        </div>
                        <div class="prod-stat-item">
                            <span class="prod-stat-label">Disbursements</span>
                            <span class="prod-stat-val" style="color: #8b5cf6;">${user.disbursements_processed}</span>
                        </div>
                        <div class="prod-stat-item">
                            <span class="prod-stat-label">Banks Assigned</span>
                            <span class="prod-stat-val" style="color: #f59e0b;">${user.banks_assigned}</span>
                        </div>
                    </div>
                    <div style="margin-top:16px; text-align:center; font-size:12px; color:var(--primary); font-weight:600;">
                        Click to view detailed timeline <i data-lucide="arrow-right" style="width:12px; height:12px; vertical-align:middle;"></i>
                    </div>
                `;
                grid.appendChild(card);
            });
            
            lucide.createIcons();
        } catch (err) {
            console.error(err);
            showNotification('Failed to load productivity stats.', 'error');
        }
    }

    async function openTimeline(username, date) {
        document.getElementById('timeline-title').innerText = `Activity Timeline: ${username}`;
        document.getElementById('timeline-modal').style.display = 'flex';
        
        const container = document.getElementById('timeline-container');
        container.innerHTML = '<div style="text-align:center; color: var(--text-light); padding: 20px;">Loading timeline...</div>';
        
        try {
            const res = await fetch(`?api=get_employee_activity_timeline&username=${username}&date=${date}`);
            const activities = await res.json();
            
            container.innerHTML = '';
            
            if (activities.length === 0) {
                container.innerHTML = '<div style="text-align:center; color: var(--text-light); padding: 20px;">No timeline data found.</div>';
                return;
            }
            
            activities.forEach(act => {
                const timeStr = new Date(act.created_at).toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit' });
                
                let iconStr = '<i data-lucide="check-circle" style="width:14px; height:14px; margin-right:6px; color:var(--success); vertical-align:middle;"></i>';
                
                const html = `
                    <div class="timeline-item">
                        <div class="timeline-time">${timeStr}</div>
                        <div class="timeline-content">
                            ${iconStr} <span>${act.description}</span>
                        </div>
                    </div>
                `;
                container.innerHTML += html;
            });
            
            lucide.createIcons();
        } catch (err) {
            console.error(err);
            container.innerHTML = '<div style="text-align:center; color: var(--danger); padding: 20px;">Failed to load timeline.</div>';
        }
    }

    function closeTimeline() {
        document.getElementById('timeline-modal').style.display = 'none';
    }

    document.addEventListener('DOMContentLoaded', () => {
        loadProductivity();
    });
</script>

<?php require_once 'footer.php'; ?>
