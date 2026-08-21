import re

file_path = r'c:\Users\pc\Downloads\client mgmt2\pre_leads.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Fix max-width
content = content.replace('max-width: 1400px;', 'width: 100%;')

# 2. Add @media print CSS
style_end = content.find('</style>')
print_css = """
@media print {
    /* Hide everything else */
    .sidebar, .header, header, .smart-tabs, .filter-bar, .top-actions, .kpi-grid, .pagination, .modal-overlay, .page-header { display: none !important; }
    /* Hide Quick Actions column */
    .pl-table th:last-child, .pl-table td:last-child { display: none !important; }
    /* Full width table for printing */
    body, .main-content, .pl-layout { margin: 0 !important; padding: 0 !important; width: 100% !important; background: #fff !important; }
    .pl-table { border: 1px solid #000 !important; width: 100% !important; }
    .pl-table th { background: #eee !important; color: #000 !important; border-bottom: 2px solid #000 !important; }
    .pl-table td { border-bottom: 1px solid #ddd !important; }
    /* Expand score badges for print */
    .score-badge { border: none !important; color: #000 !important; font-weight: bold !important; }
}
"""
content = content[:style_end] + print_css + content[style_end:]

# 3. Add Print Button
actions_insert = content.find('<?php if($is_admin): ?>')
print_btn = """<button class="btn-secondary" onclick="window.print()" title="Print Current Data"><i data-lucide="printer"></i> Print</button>\n            """
content = content[:actions_insert] + print_btn + content[actions_insert:]

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Width fixed and print feature added.")
