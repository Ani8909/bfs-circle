import os

staff_idx_path = r'c:\Users\pc\Downloads\client mgmt2\staff\index.php'
with open(staff_idx_path, 'r', encoding='utf-8') as f:
    idx = f.read()

html_target = """    <div class="app-header">
        <div class="header-top">
            <div style="display:flex; align-items:center; gap:12px;">
                <div style="width:40px; height:40px; background:rgba(255,255,255,0.2); border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:18px;">
                    👨‍💼
                </div>
                <div>
                    <div class="user-greeting" style="font-size:12px;">Hello,</div>"""

html_repl = """    <div class="app-header">
        <div class="header-top">
            <div style="display:flex; align-items:center; gap:12px;">
                <div style="width:44px; height:44px; background:rgba(255,255,255,0.2); border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:18px; overflow:hidden; border:2px solid rgba(255,255,255,0.3);">
                    <?php if(!empty($photo_path)): ?>
                        <img src="../uploads/employees/<?= htmlspecialchars($photo_path) ?>" style="width:100%; height:100%; object-fit:cover;">
                    <?php else: ?>
                        👨‍💼
                    <?php endif; ?>
                </div>
                <div>
                    <div class="user-greeting" style="font-size:13px; font-weight:500; opacity:0.9;"><?= isset($greeting) ? $greeting : 'Hello,' ?></div>"""

idx = idx.replace(html_target, html_repl)

with open(staff_idx_path, 'w', encoding='utf-8') as f:
    f.write(idx)
print("Fixed header target and replaced correctly.")
