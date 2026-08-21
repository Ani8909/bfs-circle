import os

prof_path = r'c:\Users\pc\Downloads\client mgmt2\staff\profile.php'
with open(prof_path, 'r', encoding='utf-8') as f:
    prof = f.read()

target1 = """    <div style="padding:20px; padding-bottom:80px; z-index:1; padding-top:20px;">
        <h2 style="font-size:18px; margin-bottom:15px; color:#1e293b;">My Profile</h2>
        
        <div style="background:white; border-radius:16px; padding:25px 20px; box-shadow:0 4px 15px rgba(0,0,0,0.05); text-align:center;">
            <div style="width:80px; height:80px; border-radius:50%; background:linear-gradient(135deg, var(--primary), #3b82f6); color:white; display:flex; align-items:center; justify-content:center; font-size:32px; font-weight:bold; margin:0 auto 15px auto;">
                <?php echo strtoupper(substr($fullname, 0, 1)); ?>
            </div>"""

repl1 = """    <div style="padding:20px; padding-bottom:80px; z-index:1; padding-top:20px;">
        <h2 style="font-size:18px; margin-bottom:15px; color:#1e293b;">My Profile</h2>
        
        <div style="background:white; border-radius:16px; padding:25px 20px; box-shadow:0 4px 15px rgba(0,0,0,0.05); text-align:center;">
            <?php if(!empty($emp_data['photo_path'])): ?>
                <div style="width:80px; height:80px; border-radius:50%; margin:0 auto 15px auto; overflow:hidden; border:3px solid var(--primary); box-shadow:0 4px 10px rgba(0,0,0,0.1);">
                    <img src="../uploads/employees/<?php echo htmlspecialchars($emp_data['photo_path']); ?>" style="width:100%; height:100%; object-fit:cover;">
                </div>
            <?php else: ?>
                <div style="width:80px; height:80px; border-radius:50%; background:linear-gradient(135deg, var(--primary), #3b82f6); color:white; display:flex; align-items:center; justify-content:center; font-size:32px; font-weight:bold; margin:0 auto 15px auto;">
                    <?php echo strtoupper(substr($fullname, 0, 1)); ?>
                </div>
            <?php endif; ?>"""

target2 = """            <form id="profileForm" onsubmit="saveProfile(event)">
                <input type="hidden" name="username" value="<?php echo htmlspecialchars($username); ?>">
                
                <div style="margin-bottom:15px;">"""

repl2 = """            <form id="profileForm" onsubmit="saveProfile(event)">
                <input type="hidden" name="username" value="<?php echo htmlspecialchars($username); ?>">
                
                <div style="margin-bottom:15px; text-align:left;">
                    <label style="display:block; font-size:13px; color:#64748b; margin-bottom:5px; font-weight:500;">Profile Photo</label>
                    <input type="file" name="profile_photo" accept="image/*" style="width:100%; padding:10px; border:1px dashed #cbd5e1; border-radius:10px; font-size:13px; background:#f8fafc;">
                </div>

                <div style="margin-bottom:15px; text-align:left;">"""

prof = prof.replace(target1, repl1)
prof = prof.replace(target2, repl2)

with open(prof_path, 'w', encoding='utf-8') as f:
    f.write(prof)
print("Updated profile.php UI")
