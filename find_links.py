import re
html = open('header.php', encoding='utf-8').read()
for m in re.finditer(r'href=[\'"](.*?)[\'"]', html):
    print(m.group(1))
