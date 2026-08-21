import re

file_path = r'c:\Users\pc\Downloads\client mgmt2\pre_leads.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Update the Quick Action Button CSS
content = content.replace('width: 36px; height: 36px; border-radius: 10px; border: none; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: 0.2s; margin-right: 6px;',
                          'width: 28px; height: 28px; border-radius: 6px; border: none; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: 0.2s; margin-right: 4px;')

# Replace 16px icons inside the buttons with 14px
content = content.replace('style="width:16px;"></i></a>', 'style="width:14px;"></i></a>')
content = content.replace('style="width:16px;"></i></button>', 'style="width:14px;"></i></button>')

# 2. Upgrade the Print CSS for maximum paper efficiency and fixing the left margin
old_print_start = content.find('@media print {')
old_print_end = content.find('}', content.find('}', content.find('}', old_print_start) + 1) + 1) + 1 # finding the end of the media query block is tricky with regex, better to just replace everything between @media print { and its closing brace.
# Let's use regex to replace the print block safely
pattern = re.compile(r'@media print \{.*?\n\}', re.DOTALL)

new_print_css = """@media print {
    @page { margin: 0.5cm; }
    /* Hide all non-essential UI */
    .sidebar, .header, header, .smart-tabs, .filter-bar, .top-actions, .kpi-grid, .pagination, .modal-overlay, .page-header { display: none !important; }
    
    /* Fix the massive left margin left by the sidebar */
    body, html, .main-content, #main-content, .content, .pl-layout, .main-wrapper, #wrapper { 
        margin: 0 !important; 
        padding: 0 !important; 
        width: 100% !important; 
        max-width: 100% !important; 
        background: #fff !important; 
        left: 0 !important;
    }
    
    /* Hide Quick Actions column completely */
    .pl-table th:last-child, .pl-table td:last-child { display: none !important; }
    
    /* Ultra-compact table to save paper */
    .pl-table { border: 1px solid #000 !important; width: 100% !important; border-collapse: collapse !important; margin: 0 !important; }
    .pl-table th { background: #eee !important; color: #000 !important; border-bottom: 2px solid #000 !important; padding: 4px 6px !important; font-size: 10px !important; }
    .pl-table td { border-bottom: 1px solid #ccc !important; padding: 4px 6px !important; font-size: 9px !important; line-height: 1.2 !important; }
    
    /* Make fonts darker and badges cleaner for B&W printers */
    .score-badge { border: none !important; color: #000 !important; font-weight: bold !important; padding: 0 !important; }
    * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; color: #000 !important; }
}"""

if pattern.search(content):
    content = pattern.sub(new_print_css, content)
else:
    # If not found, just append before </style>
    content = content.replace('</style>', new_print_css + '\n</style>')

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Icons resized and print layout optimized")
