import sys

with open('reminders.php', 'r', encoding='utf-8') as f:
    content = f.read()

old_time = '''      if (col === 'overdue') {
        const ago = Math.abs(diffMins);
        timeStr = ago < 60 ? ago + ' min overdue' : Math.round(ago/60) + 'h overdue';
        timeClass = 'overdue';
    }'''

new_time = '''      if (col === 'overdue') {
        const ago = Math.abs(diffMins);
        if (ago < 60) {
            timeStr = ago + ' min overdue';
        } else if (ago < 1440) {
            timeStr = Math.round(ago/60) + 'h overdue';
        } else {
            timeStr = Math.round(ago/1440) + ' days overdue';
        }
        timeClass = 'overdue';
    }'''

content = content.replace(old_time, new_time)

old_card = '''    const refLabel = r.reference_label || (r.lead_id ? '#' + r.lead_id : '');
    const refId    = r.reference_id || r.lead_id || '';
    const title    = r.title || r.notes || 'Reminder';
    const notes    = r.notes && r.notes !== title ? r.notes : '';'''

new_card = '''    const name = r.fetched_name || r.reference_label || (r.lead_id ? 'ID #' + r.lead_id : 'Unknown');
    const mobile = r.fetched_mobile ? `<br><a href="tel:` + r.fetched_mobile + `" style="color:#64748b; font-size:11px; text-decoration:none; margin-top:2px; display:inline-block;"><i data-lucide="phone" style="width:10px;height:10px;"></i> ` + r.fetched_mobile + `</a>` : '';
    const refLabel = name;
    const refId    = r.reference_id || r.lead_id || '';
    const title    = r.title || 'Follow up with ' + name;
    const notes    = r.notes && r.notes !== title ? r.notes : '';'''

content = content.replace(old_card, new_card)

old_html = '''        <div class="rem-card-top">
            <div class="rem-card-title">${CATEGORY_ICONS[cat] || ''} ${escHtml(title)}</div>
            <span class="rem-card-pri pri-badge-${pri}">${pri}</span>
        </div>
        ${entityHtml}'''

new_html = '''        <div class="rem-card-top" style="margin-bottom:6px;">
            <div class="rem-card-title" style="font-size:14px; font-weight:700; color:#0f172a;">${CATEGORY_ICONS[cat] || ''} ${escHtml(title)}</div>
            <span class="rem-card-pri pri-badge-${pri}">${pri}</span>
        </div>
        ${notes ? `<div style="font-size:12px; color:#475569; margin-bottom:10px; line-height:1.4; background:#f8fafc; padding:8px; border-radius:6px; border:1px solid #e2e8f0;">${escHtml(notes)}</div>` : ''}
        ${entityHtml}'''

content = content.replace(old_html, new_html)

old_entity_html = '''        entityHtml = `<a class="rem-entity-badge entity-${refType}" href="${url}" target="_blank" title="Go to ${refType}">
            ${ENTITY_ICONS[refType] || ''} ${escHtml(refLabel)}
            <i data-lucide="external-link" style="width:11px;height:11px;"></i>
        </a>`;'''

new_entity_html = '''        entityHtml = `<a class="rem-entity-badge entity-${refType}" href="${url}" target="_blank" title="Go to ${refType}" style="display:flex; justify-content:space-between; align-items:center; padding:10px 12px;">
            <div>
                <div style="font-weight:700; color:#1e293b; font-size:13px;">${ENTITY_ICONS[refType] || ''} ${escHtml(refLabel)}</div>
                ${mobile}
            </div>
            <i data-lucide="external-link" style="width:16px;height:16px; color:#94a3b8;"></i>
        </a>`;'''

content = content.replace(old_entity_html, new_entity_html)

with open('reminders.php', 'w', encoding='utf-8') as f:
    f.write(content)

print('Success')
