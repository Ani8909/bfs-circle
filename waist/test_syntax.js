async function loadLeads(page = 1) {
    const leads = [
        {id: 1, lead_name: "test", company_name: "c", mobile: "123", priority: "Hot", stage: "New Lead", lead_source: "Web", assigned_to: "a", added_by: "b", assigned_at: "2026-08-14 10:10:10", reminder_date: null}
    ];
    let STAGE_COLORS = {};
    let PRIORITY_BADGE = {};
    let currentUser = {};

    let html = leads.map(l => {
        const col = STAGE_COLORS[l.stage] || '#64748b';
        
        let reminderHtml = '';
        if (l.reminder_date) {
            const rDate = new Date(l.reminder_date);
            const now = new Date();
            const isPast = rDate < now;
            const rColor = isPast ? '#ef4444' : '#f59e0b';
            reminderHtml = `<div style="display:inline-flex; align-items:center; gap:4px; font-size:11px; padding:2px 6px; border-radius:12px; background:${rColor}15; color:${rColor}; font-weight:600; margin-top:6px;" title="Reminder: ${rDate.toLocaleString()}"><i class="lucide-bell" style="width:12px;height:12px;"></i> ${rDate.toLocaleString([], {month:'short', day:'numeric', hour:'2-digit', minute:'2-digit'})}</div>`;
        }

        return `<tr style="border-bottom:1px solid var(--border);transition:background .15s; cursor:pointer;" onmouseover="this.style.background='rgba(0,0,0,0.02)'" onmouseout="this.style.background=''" onclick="openLeadDetail(${l.id})">
            <td style="padding:12px;" onclick="event.stopPropagation()"><input type="checkbox" class="lead-checkbox" value="${l.id}"></td>
            <td style="padding:12px;">
                <div style="font-weight:600;color:var(--text-primary);display:flex;align-items:center;gap:6px;">
                    ${l.lead_name}
                    <a href="https://wa.me/91${l.mobile}?text=${encodeURIComponent('Hi ' + l.lead_name + ', I am calling from BFS Financial Services regarding your loan inquiry.')}" target="_blank" onclick="event.stopPropagation()" style="color:#25D366; display:inline-flex;" title="Chat on WhatsApp">
                        <svg style="width:16px;height:16px;" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
                    </a>
                </div>
                <div style="font-size:11px;color:var(--text-light);">${l.company_name || '—'}</div>
                ${reminderHtml}
            </td>
            <td style="padding:12px;font-size:13px;">${l.mobile}</td>
            <td style="padding:12px;font-size:12px;color:var(--text-light);">${l.lead_source}</td>
            <td style="padding:12px;">${PRIORITY_BADGE[l.priority] || l.priority}</td>
            <td style="padding:12px;">
                <span style="padding:3px 8px;border-radius:20px;font-size:12px;font-weight:600;background:${col}20;color:${col};display:inline-block;text-align:center;">
                    ${l.stage}
                </span>
            </td>
            <td style="padding:12px;">
                <div style="font-size:12px;color:var(--text-primary);font-weight:600;">${l.assigned_to || '<span style="color:#94a3b8;font-weight:normal;">Unassigned</span>'}</div>
                ${l.added_by ? `<div style="font-size:10px; color:#6366f1; background:#e0e7ff; padding:2px 6px; border-radius:12px; display:inline-block; margin-top:4px;">Added by: ${l.added_by}</div>` : ''}
                ${(() => {
                    if (!l.assigned_at) return '';
                    const d = new Date(l.assigned_at.replace(' ', 'T') + 'Z');
                    const localDate = isNaN(d) ? new Date(l.assigned_at) : d;
                    const diffMs = new Date() - localDate;
                    if (diffMs < 0) return `<div style="font-size:11px;color:var(--text-light);margin-top:2px;">${localDate.toLocaleString()}</div>`;
                    const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));
                    const diffHrs = Math.floor((diffMs % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const diffMins = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60));
                    let timeAgo = '';
                    if (diffDays > 0) timeAgo = diffDays + 'd ' + diffHrs + 'h ago';
                    else if (diffHrs > 0) timeAgo = diffHrs + 'h ' + diffMins + 'm ago';
                    else timeAgo = diffMins + 'm ago';
                    return `<div style="font-size:11px;color:var(--text-light);margin-top:2px;" title="${localDate.toLocaleString()}">⏱️ ${timeAgo}</div>`;
                })()}
            </td>
            <td style="padding:12px;" onclick="event.stopPropagation()">
                <div style="display:flex;gap:6px;">
                    <button class="btn btn-secondary" style="padding:4px 10px;font-size:11px;" onclick="openLeadDetail(${l.id})" title="View / Update">🔍 View</button>
                    <button class="btn btn-secondary" style="padding:4px 10px;font-size:11px;background:#dcfce7;color:#166534;border:none;" onclick="convertToClient(${l.id})" title="Convert to Client">Convert</button>
                    <button class="btn btn-secondary" style="padding:4px 10px;font-size:11px;" onclick="openReminderModal('Lead', ${l.id})" title="Set Reminder">⏰</button>
                    ${currentUser && currentUser.role === 'Admin' ? `<button class="btn btn-danger" style="padding:4px 10px;font-size:11px;" onclick="deleteLead(${l.id})" title="Delete">Delete</button>` : ''}
                </div>
            </td>
        </tr>`;
    }).join('');
    console.log("Success");
}
loadLeads();
