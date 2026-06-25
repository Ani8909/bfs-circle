<?php
require_once __DIR__ . '/config.php';

// Strict Admin-only access check
if (($_SESSION['role'] ?? '') !== 'Admin') {
    header("Location: dashboard.php");
    exit;
}

$page_title = "Activity Logs";
$page_subtitle = "System audit trails and user action logs";
require_once __DIR__ . '/header.php';
?>

<div class="view-container">
    <div class="card" style="background: var(--bg-card); border-radius: var(--radius-lg); box-shadow: var(--shadow-md); border: 1px solid #f1f5f9; overflow: hidden; margin-bottom: 30px;">
        <div class="card-title-bar" style="padding: 24px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; background: white;">
            <h2 style="font-family: 'Outfit', sans-serif; font-weight: 700; font-size: 18px; color: var(--text-primary); margin: 0;">System Audit & Activity Logs</h2>
            <div class="badge-locked" style="background-color: var(--primary-light); color: var(--primary); padding: 6px 12px; border-radius: var(--radius-sm); font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
                <i data-lucide="shield" style="width: 14px; height: 14px;"></i> Admin Only
            </div>
        </div>
        <div style="padding: 24px;">
            <div class="table-responsive" style="overflow-x: auto; width: 100%;">
                <table class="data-table" style="width: 100%; border-collapse: collapse; text-align: left; font-size: 14px;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--border); color: var(--text-muted); font-weight: 600;">
                            <th style="padding: 12px 16px; width: 250px;">Date & Time</th>
                            <th style="padding: 12px 16px;">Action Log Details</th>
                        </tr>
                    </thead>
                    <tbody id="full-activity-logs-tbody">
                        <tr>
                            <td colspan="2" style="text-align: center; padding: 30px; color: var(--text-light);">
                                <div style="display: inline-flex; align-items: center; gap: 8px;">
                                    <i data-lucide="loader" class="animate-spin" style="width: 16px; height: 16px;"></i> Loading activities...
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
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
        padding: 14px 16px;
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
    async function loadFullActivityLogs() {
        try {
            const response = await fetch('?api=get_activity_logs');
            const data = await response.json();
            const container = document.getElementById('full-activity-logs-tbody');
            if (!container) return;

            if (data.error) {
                container.innerHTML = `<tr><td colspan="2" style="text-align:center;color:red;padding: 20px;">Error: ${data.error}</td></tr>`;
                return;
            }
            if (!Array.isArray(data)) {
                container.innerHTML = `<tr><td colspan="2" style="text-align:center;color:red;padding: 20px;">API Error: Expected array, got ${typeof data}</td></tr>`;
                return;
            }
            if (data.length === 0) {
                container.innerHTML = '<tr><td colspan="2" style="text-align:center;padding: 20px;color: var(--text-muted);">No activities found</td></tr>';
                return;
            }
            let html = '';
            data.forEach(act => {
                let descHtml = act.description || '';
                // highlight username
                descHtml = descHtml.replace(/^\[(.*?)\]/, '<span class="badge" style="background:var(--primary);color:white;padding:2px 6px;border-radius:4px;font-size:11px;font-weight:600;margin-right:8px;">$1</span>');
                
                let linkHtml = '';
                if (act.action_link) {
                    linkHtml = `<a href="${act.action_link}" style="margin-left:12px; padding:4px 10px; background:var(--primary-light); color:var(--primary); font-size:11px; font-weight:600; border-radius:6px; text-decoration:none; display:inline-flex; align-items:center; gap:4px; transition:0.2s;" onmouseover="this.style.background='var(--primary)'; this.style.color='white';" onmouseout="this.style.background='var(--primary-light)'; this.style.color='var(--primary)';"><svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg> View Details</a>`;
                }
                
                html += `<tr>
                    <td style="white-space:nowrap; color:var(--text-light); font-size:13px; font-family: monospace;">${act.created_at_formatted || ''}</td>
                    <td style="color: var(--text-primary); font-weight: 500; display:flex; align-items:center; justify-content:space-between;">
                        <span>${descHtml}</span>
                        ${linkHtml}
                    </td>
                </tr>`;
            });
            container.innerHTML = html;
        } catch (e) {
            console.error(e);
            const container = document.getElementById('full-activity-logs-tbody');
            if(container) {
                container.innerHTML = `<tr><td colspan="2" style="text-align:center;color:red;padding: 20px;">JS Exception: ${e.message}</td></tr>`;
            }
        }
    }

    document.addEventListener("DOMContentLoaded", () => {
        loadFullActivityLogs();
    });
</script>

<?php
require_once __DIR__ . '/footer.php';
?>
