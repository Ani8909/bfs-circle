import sys

with open('client_vault/index.php', 'r', encoding='utf-8') as f:
    content = f.read()

content = content.replace('style="color:var(--primary);"', 'style="color:var(--text-primary);"')

with open('client_vault/index.php', 'w', encoding='utf-8') as f:
    f.write(content)

print('Success')
