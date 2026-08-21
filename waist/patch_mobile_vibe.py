import os
idx_path = r'c:\Users\pc\Downloads\client mgmt2\staff\index.php'
with open(idx_path, 'r', encoding='utf-8') as f:
    idx = f.read()

target = """            <div>
                <div class="user-greeting">Welcome to BFS Financial Services Staff Family,</div>
                <div class="user-name" id="staff_name"><?= htmlspecialchars($username) ?></div>
            </div>"""
repl = """            <div style="display:flex; align-items:center; gap:12px;">
                <div style="width:40px; height:40px; background:rgba(255,255,255,0.2); border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:18px;">
                    👨‍💼
                </div>
                <div>
                    <div class="user-greeting" style="font-size:12px;">Hello,</div>
                    <div class="user-name" id="staff_name" style="font-size:16px; margin-top:2px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:200px;"><?= htmlspecialchars(explode('@', $username)[0]) ?></div>
                </div>
            </div>"""

idx = idx.replace(target, repl)
with open(idx_path, 'w', encoding='utf-8') as f:
    f.write(idx)
print("Mobile vibe applied")
