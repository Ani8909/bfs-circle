import re

file_path = r'c:\Users\pc\Downloads\client mgmt2\search_track.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# Fix the bug where JS uses 'selected' but CSS uses 'active' for the card highlighting
content = content.replace("el.classList.remove('selected')", "el.classList.remove('active')")
content = content.replace("selectedCard.classList.add('selected')", "selectedCard.classList.add('active')")

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Fixed card active state highlighting")
