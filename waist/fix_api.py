import sys

with open('api.php', 'r', encoding='utf-8') as f:
    content = f.read()

content = content.replace(
    'SELECT assigned_to FROM applicants WHERE id=',
    'SELECT added_by FROM applicants WHERE id='
)

with open('api.php', 'w', encoding='utf-8') as f:
    f.write(content)

print('Success')
