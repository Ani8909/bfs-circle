import sys

with open('reminders.php', 'r', encoding='utf-8') as f:
    content = f.read()

# Replace KPI colors
content = content.replace('.kpi-overdue  .rem-kpi-icon { background: #fef2f2; color: #ef4444; }', '.kpi-overdue  .rem-kpi-icon { background: #f8fafc; color: var(--text-primary); border: 1px solid var(--border); }')
content = content.replace('.kpi-overdue  .rem-kpi-count { color: #ef4444; }', '.kpi-overdue  .rem-kpi-count { color: var(--text-primary); }')
content = content.replace('.kpi-today    .rem-kpi-icon { background: #fffbeb; color: #f59e0b; }', '.kpi-today    .rem-kpi-icon { background: #f8fafc; color: var(--text-primary); border: 1px solid var(--border); }')
content = content.replace('.kpi-today    .rem-kpi-count { color: #f59e0b; }', '.kpi-today    .rem-kpi-count { color: var(--text-primary); }')
content = content.replace('.kpi-upcoming .rem-kpi-icon { background: #eff6ff; color: #3b82f6; }', '.kpi-upcoming .rem-kpi-icon { background: #f8fafc; color: var(--text-primary); border: 1px solid var(--border); }')
content = content.replace('.kpi-upcoming .rem-kpi-count { color: #3b82f6; }', '.kpi-upcoming .rem-kpi-count { color: var(--text-primary); }')
content = content.replace('.kpi-done     .rem-kpi-icon { background: #f0fdf4; color: #10b981; }', '.kpi-done     .rem-kpi-icon { background: #f8fafc; color: var(--text-primary); border: 1px solid var(--border); }')
content = content.replace('.kpi-done     .rem-kpi-count { color: #10b981; }', '.kpi-done     .rem-kpi-count { color: var(--text-primary); }')

# Replace column header colors
content = content.replace('.col-overdue  .rem-col-header { background: #fef2f2; color: #ef4444; }', '.col-overdue  .rem-col-header { background: #f8fafc; color: var(--text-primary); border: 1px solid var(--border); }')
content = content.replace('.col-today    .rem-col-header { background: #fffbeb; color: #d97706; }', '.col-today    .rem-col-header { background: #f8fafc; color: var(--text-primary); border: 1px solid var(--border); }')
content = content.replace('.col-upcoming .rem-col-header { background: #eff6ff; color: #3b82f6; }', '.col-upcoming .rem-col-header { background: #f8fafc; color: var(--text-primary); border: 1px solid var(--border); }')

# Replace card left-border colors
content = content.replace('.rem-card.pri-High::before   { background: #ef4444; }', '.rem-card.pri-High::before   { background: var(--text-primary); }')
content = content.replace('.rem-card.pri-Medium::before { background: #f59e0b; }', '.rem-card.pri-Medium::before { background: #64748b; }')
content = content.replace('.rem-card.pri-Low::before    { background: #10b981; }', '.rem-card.pri-Low::before    { background: #cbd5e1; }')

# Replace priority badges
content = content.replace('.pri-badge-High   { background: #fef2f2; color: #ef4444; }', '.pri-badge-High   { background: var(--text-primary); color: white; border: 1px solid var(--text-primary); }')
content = content.replace('.pri-badge-Medium { background: #fffbeb; color: #d97706; }', '.pri-badge-Medium { background: #f8fafc; color: var(--text-primary); border: 1px solid #94a3b8; }')
content = content.replace('.pri-badge-Low    { background: #f0fdf4; color: #10b981; }', '.pri-badge-Low    { background: #f8fafc; color: #64748b; border: 1px solid var(--border); }')

# Replace entity badges
content = content.replace('.entity-Lead     { background: #eff6ff; color: #2563eb; }', '.entity-Lead     { background: #f8fafc; color: var(--text-primary); border: 1px solid var(--border); }')
content = content.replace('.entity-Banker   { background: #faf5ff; color: #7c3aed; }', '.entity-Banker   { background: #f8fafc; color: var(--text-primary); border: 1px solid var(--border); }')
content = content.replace('.entity-Referral { background: #f0fdf4; color: #16a34a; }', '.entity-Referral { background: #f8fafc; color: var(--text-primary); border: 1px solid var(--border); }')
content = content.replace('.entity-Pre-Lead { background: #fff7ed; color: #ea580c; }', '.entity-Pre-Lead { background: #f8fafc; color: var(--text-primary); border: 1px solid var(--border); }')
# Add Staff
content = content.replace('.entity-General  { background: #f8fafc; color: #64748b; }', '.entity-General  { background: #f8fafc; color: #64748b; border: 1px solid var(--border); }\n  .entity-Staff { background: #f8fafc; color: var(--text-primary); border: 1px solid var(--border); }')

# Replace time text
content = content.replace('.rem-time.overdue { color: #ef4444; }', '.rem-time.overdue { color: var(--text-primary); font-weight: 800; }')
content = content.replace('.rem-time.today   { color: #d97706; }', '.rem-time.today   { color: var(--text-primary); font-weight: 700; }')
content = content.replace('.rem-time.ok      { color: #3b82f6; }', '.rem-time.ok      { color: #64748b; }')

# Replace buttons
content = content.replace('.rem-btn-done    { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }', '.rem-btn-done    { background: #f8fafc; color: var(--text-primary); border: 1px solid var(--border); }')
content = content.replace('.rem-btn-done:hover    { background: #dcfce7; }', '.rem-btn-done:hover    { background: #e2e8f0; }')
content = content.replace('.rem-btn-snooze  { background: #fefce8; color: #b45309; border: 1px solid #fde68a; position: relative; }', '.rem-btn-snooze  { background: #f8fafc; color: var(--text-primary); border: 1px solid var(--border); position: relative; }')
content = content.replace('.rem-btn-snooze:hover  { background: #fef9c3; }', '.rem-btn-snooze:hover  { background: #e2e8f0; }')

with open('reminders.php', 'w', encoding='utf-8') as f:
    f.write(content)

print('Success')
