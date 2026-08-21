import os

staff_idx_path = r'c:\Users\pc\Downloads\client mgmt2\staff\index.php'
with open(staff_idx_path, 'r', encoding='utf-8') as f:
    idx = f.read()

target = "height: 34px;"
repl = "height: 56px;"

idx = idx.replace(target, repl)

with open(staff_idx_path, 'w', encoding='utf-8') as f:
    f.write(idx)
print("Increased logo size to 56px")
