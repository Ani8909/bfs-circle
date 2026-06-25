<?php
require_once __DIR__ . '/../config.php';

$page_title = "Follow-up Reminders";
$page_subtitle = "Pending CRM Tasks, Call Backs, and Lead Schedules";
require_once __DIR__ . '/header.php';
?>

<div class="view-container">
    <div class="card" style="background: var(--bg-card); border-radius: var(--radius-lg); box-shadow: var(--shadow-md); border: 1px solid #f1f5f9; padding: 24px; margin-bottom: 30px;">
        <div class="card-title-bar" style="border-bottom: 1px solid #f1f5f9; padding-bottom: 15px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
            <h2 style="font-family: 'Outfit', sans-serif; font-weight: 700; font-size: 18px; color: var(--text-primary); margin: 0;">📅 My Follow-up Reminders</h2>
            <div style="background-color: var(--primary-light); color: var(--primary); padding: 6px 12px; border-radius: var(--radius-sm); font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
                <i data-lucide="bell" style="width: 14px; height: 14px;"></i> Active Schedules
            </div>
        </div>

        <div style="overflow-x: auto; width: 100%;">
            <table class="data-table" id="reminders-table" style="width: 100%; border-collapse: collapse; text-align: left; font-size: 14px;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--border); color: var(--text-muted); font-weight: 600;">
                        <th style="padding: 12px; width: 25%;">Date & Time</th>
                        <th style="padding: 12px; width: 15%;">Lead Type</th>
                        <th style="padding: 12px; width: 15%;">Lead Reference ID</th>
                        <th style="padding: 12px; width: 35%;">Follow-up Notes</th>
                        <th style="padding: 12px; width: 10%; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 30px; color: var(--text-light);">
                            <div style="display: inline-flex; align-items: center; gap: 8px;">
                                <i data-lucide="loader" class="animate-spin" style="width: 16px; height: 16px;"></i> Loading follow-ups...
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .data-table tbody tr {
        border-bottom: 1px solid var(--border);
        transition: background-color var(--transition-fast);
    }
    .data-table tbody tr:hover {
        background-color: var(--bg-main);
    }
    .data-table tbody td {
        padding: 14px 12px;
    }
    .animate-spin {
        animation: spin 1s linear infinite;
    }
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
</style>

<script>
    async function loadReminders() {
        try {
            // API automatically restricts results based on user session role
            let res = await fetch('?api=get_reminders');
            let data = await res.json();
            let tbody = document.querySelector("#reminders-table tbody");
            if (!tbody) return;

            if (data.error) { 
                tbody.innerHTML = `<tr><td colspan='5' style='text-align:center;color:red;padding:20px;'>Failed to load reminders: ${data.error}</td></tr>`; 
                return; 
            }
            if (!Array.isArray(data)) {
                tbody.innerHTML = `<tr><td colspan='5' style='text-align:center;color:red;padding:20px;'>API Error: Expected array, got ${typeof data}</td></tr>`;
                return;
            }
            if (data.length === 0) {
                tbody.innerHTML = `<tr><td colspan='5' style='text-align:center;padding:20px;color:var(--text-light);'>No pending reminders set.</td></tr>`;
                return;
            }
            
            let html = "";
            data.forEach(r => {
                let dateObj = new Date(r.remind_at);
                let isPast = dateObj < new Date() && r.status !== 'Completed';
                let color = isPast ? 'var(--danger)' : 'var(--primary)';
                let pastBadge = isPast ? '<span style="font-size:10px; background:var(--danger-light); color:var(--danger); padding:2px 4px; border-radius:4px; margin-left:6px; font-weight:600;">OVERDUE</span>' : '';
                
                html += `<tr>
                    <td style="color:${color}; font-weight:600; white-space:nowrap;">
                        ${dateObj.toLocaleString()}
                        ${pastBadge}
                    </td>
                    <td style="text-transform: capitalize; font-weight:500;">
                        ${r.lead_type === 'pre_lead' ? 'Pre-Lead' : 'Lead'}
                    </td>
                    <td>
                        <strong>#${r.lead_id}</strong>
                    </td>
                    <td style="color:var(--text-primary); font-weight:500;">
                        ${r.notes || '<span style="color:var(--text-light); font-style:italic;">No notes specified</span>'}
                    </td>
                    <td style="text-align:right;">
                        ${r.status === 'Pending' ? `<button class="btn btn-primary" style="padding:4px 8px; font-size:12px; cursor:pointer;" onclick="completeReminder(${r.id})">Mark Done</button>` : ''}
                    </td>
                </tr>`;
            });
            tbody.innerHTML = html;
        } catch(e) {
            console.error(e);
            let tbody = document.querySelector("#reminders-table tbody");
            if (tbody) tbody.innerHTML = `<tr><td colspan='5' style='text-align:center;color:red;padding:20px;'>JS Exception: ${e.message}</td></tr>`;
        }
    }
    
    async function completeReminder(id) {
        if (!confirm("Mark this reminder as Completed?")) return;
        let fd = new FormData();
        fd.append("api", "complete_reminder");
        fd.append("id", id);
        try {
            let res = await fetch("?api=complete_reminder", {method:"POST", body:fd});
            let json = await res.json();
            if (json.success) {
                showNotification("Reminder completed successfully", "success");
                loadReminders();
            } else {
                showNotification(json.error || "Failed to complete reminder", "error");
            }
        } catch(e) {
            console.error(e);
            showNotification("Connection error", "error");
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        loadReminders();
    });
</script>

<?php
require_once __DIR__ . '/footer.php';
?>
