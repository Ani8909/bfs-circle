import sys
import re
with open('applicant_bank_assign.php', 'r', encoding='utf-8') as f:
    content = f.read()
content = content.replace('htmlspecialchars([' + chr(39) + 'lead_source' + chr(39) + '])', 'htmlspecialchars([' + chr(39) + 'lead_source' + chr(39) + '] ?? ' + chr(39) + chr(39) + ')')
content = content.replace('background:var(--primary-light)', 'background:#f8fafc')
content = content.replace('background:var(--bg-main)', 'background:#f8fafc')
css = '''
    .data-table { border: 1px solid var(--border) !important; border-collapse: collapse !important; background: #fff !important; }
    .data-table td, .data-table th { border: 1px solid var(--border) !important; padding: 12px 18px !important; vertical-align: middle !important; }
    .data-table tr td:first-child { background: #f8fafc !important; color: #0f172a !important; font-weight: 600 !important; width: 35% !important; }
    .data-table tr td:last-child { color: #0f172a !important; font-weight: 500 !important; }
    #sourcing-header-row td { background: #0f172a !important; color: #ffffff !important; font-weight: 700 !important; border: 1px solid #0f172a !important; }
    #sourcing-header-row td div { color: #ffffff !important; letter-spacing: 1px !important; }
    .card-title-bar { background: #ffffff !important; border-bottom: 2px solid #0f172a !important; }
    .card-title-bar h3 { color: #0f172a !important; font-weight: 800 !important; }
    .badge { background: white !important; border: 1px solid #0f172a !important; color: #0f172a !important; font-weight: 600 !important; padding: 4px 10px !important; border-radius: 20px !important; }
</style>'''
content = content.replace('</style>', css)
with open('applicant_bank_assign.php', 'w', encoding='utf-8') as f:
    f.write(content)
print('Success')
