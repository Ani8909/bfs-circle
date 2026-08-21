import re
content = open('header.php', encoding='utf-8').read()
matches = re.findall(r'href="[^"]+"', content)
for m in matches:
    print(m)
