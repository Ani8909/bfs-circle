import sys

with open('client_vault/index.php', 'r', encoding='utf-8') as f:
    content = f.read()

content = content.replace('<i data-lucide="database" style="color:#3b82f6;"></i>', '<i data-lucide="database" style="color:var(--primary);"></i>')

with open('client_vault/index.php', 'w', encoding='utf-8') as f:
    f.write(content)

print('Success')
