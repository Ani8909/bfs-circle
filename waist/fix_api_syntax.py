import sys

with open('api.php', 'r', encoding='utf-8') as f:
    content = f.read()

content = content.replace('.assigned_to =', ' =')
content = content.replace('.priority =', ' =')
content = content.replace('.status =', ' =')
content = content.replace('.reference_type =', ' =')
content = content.replace('.reminder_category =', ' =')
content = content.replace('.remind_at =', ' =')

with open('api.php', 'w', encoding='utf-8') as f:
    f.write(content)
