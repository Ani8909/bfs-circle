import sys

with open('client_vault/index.php', 'r', encoding='utf-8') as f:
    content = f.read()

old_btn = '.btn-pitch { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white; border: none; padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; transition: 0.2s; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; box-shadow: 0 4px 10px rgba(59,130,246,0.2); }'
old_btn_hover = '.btn-pitch:hover { transform: translateY(-1px); box-shadow: 0 6px 15px rgba(59,130,246,0.3); }'

new_btn = '.btn-pitch { background: var(--primary); color: white; border: none; padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; transition: 0.2s; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; box-shadow: 0 4px 10px rgba(234,88,12,0.2); }'
new_btn_hover = '.btn-pitch:hover { transform: translateY(-1px); box-shadow: 0 6px 15px rgba(234,88,12,0.3); }'

content = content.replace(old_btn, new_btn).replace(old_btn_hover, new_btn_hover)

with open('client_vault/index.php', 'w', encoding='utf-8') as f:
    f.write(content)

print('Success')
