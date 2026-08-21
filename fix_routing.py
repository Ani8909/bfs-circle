import os

idx_path = r'c:\Users\pc\Downloads\client mgmt2\staff\index.php'
with open(idx_path, 'r', encoding='utf-8') as f:
    idx = f.read()

idx = idx.replace("fetch(`../api.php?api=get_staff_performance", "fetch(`?api=get_staff_performance")
idx = idx.replace("fetch(`../api.php?api=get_staff_recent_leads", "fetch(`?api=get_staff_recent_leads")
idx = idx.replace("fetch('../api.php', { method:'POST'", "fetch('?api=x', { method:'POST'")

# Also fix the tracker fetch!
footer_path = r'c:\Users\pc\Downloads\client mgmt2\footer.php'
with open(footer_path, 'r', encoding='utf-8') as f:
    footer = f.read()
footer = footer.replace("fetch('/api.php'", "fetch('?api=x'")
footer = footer.replace("fetch('<?php echo rtrim(str_replace(\"\\\\\\\\\", \"/\", dirname($_SERVER[\"PHP_SELF\"])), \"/\"); ?>/../api.php'", "fetch('?api=x'")

with open(idx_path, 'w', encoding='utf-8') as f:
    f.write(idx)
with open(footer_path, 'w', encoding='utf-8') as f:
    f.write(footer)
print("Fixed API routing bugs")
