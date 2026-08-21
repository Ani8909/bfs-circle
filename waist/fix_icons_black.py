import sys

with open('client_vault/index.php', 'r', encoding='utf-8') as f:
    content = f.read()

content = content.replace('style="color: #3b82f6; background: rgba(59,130,246,0.1);"', 'style="color: var(--text-primary); background: #f1f5f9;"')
content = content.replace('style="color: #10b981; background: rgba(16,185,129,0.1);"', 'style="color: var(--text-primary); background: #f1f5f9;"')
content = content.replace('style="color: var(--primary); background: var(--primary-light);"', 'style="color: var(--text-primary); background: #f1f5f9;"')

with open('client_vault/index.php', 'w', encoding='utf-8') as f:
    f.write(content)

print('Success')
