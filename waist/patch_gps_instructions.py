import os

staff_idx_path = r'c:\Users\pc\Downloads\client mgmt2\staff\index.php'
with open(staff_idx_path, 'r', encoding='utf-8') as f:
    idx = f.read()

target = """    <!-- GPS Blocker Overlay -->
    <div id="gps-blocker" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.95); z-index:999999; flex-direction:column; align-items:center; justify-content:center; color:white; padding:20px; text-align:center;">
        <i class="fas fa-map-marker-slash" style="font-size:60px; color:#ef4444; margin-bottom:20px;"></i>
        <h2 style="margin-bottom:10px;">Location is Disabled!</h2>
        <p style="margin-bottom:20px; font-size:14px; opacity:0.8; max-width:300px;">You are currently <b>On Duty</b>. Company policy requires live GPS tracking to be active. Please enable your Location (GPS) and grant permission to continue using the app.</p>
        <button onclick="window.location.reload()" style="background:#22c55e; color:white; padding:12px 24px; border:none; border-radius:8px; font-weight:bold; font-size:16px;">I Have Enabled GPS - Refresh</button>
    </div>"""

repl = """    <!-- GPS Blocker Overlay (Detailed Instructions) -->
    <div id="gps-blocker" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15, 23, 42, 0.98); z-index:999999; flex-direction:column; align-items:center; justify-content:flex-start; color:white; padding:20px; text-align:left; overflow-y:auto;">
        
        <div style="text-align:center; margin-top:20px; margin-bottom:20px;">
            <i class="fas fa-map-marker-slash" style="font-size:50px; color:#ef4444; margin-bottom:15px;"></i>
            <h2 style="margin-bottom:10px; font-size:22px;">Location is Disabled!</h2>
            <p style="font-size:14px; color:#94a3b8;">Aap <b>On Duty</b> hain. Kaam karne ke liye Location/GPS permission dena zaroori hai. Niche diye gaye steps follow karein:</p>
        </div>

        <div style="width:100%; max-width:400px; background:rgba(255,255,255,0.05); border-radius:12px; padding:15px; margin-bottom:15px; border:1px solid rgba(255,255,255,0.1);">
            <h3 style="font-size:15px; color:#38bdf8; margin-bottom:10px;"><i class="fas fa-mobile-alt"></i> Step 1: Phone ka GPS ON karein</h3>
            
            <p style="font-size:13px; font-weight:bold; color:#f8fafc; margin-bottom:5px;">Android Phone mein:</p>
            <ul style="font-size:12px; color:#cbd5e1; margin-left:20px; margin-bottom:10px;">
                <li>Apni screen ko upar se niche khinch kar Notification Panel kholein.</li>
                <li>Wahan <b>Location</b> ya <b>GPS</b> ka icon hoga, us par tap karke ON karein.</li>
            </ul>

            <p style="font-size:13px; font-weight:bold; color:#f8fafc; margin-bottom:5px;">iPhone mein:</p>
            <ul style="font-size:12px; color:#cbd5e1; margin-left:20px;">
                <li>Phone ki Settings mein jayein.</li>
                <li><b>Privacy & Security > Location Services</b> par tap karke ise ON karein.</li>
            </ul>
        </div>

        <div style="width:100%; max-width:400px; background:rgba(255,255,255,0.05); border-radius:12px; padding:15px; margin-bottom:25px; border:1px solid rgba(255,255,255,0.1);">
            <h3 style="font-size:15px; color:#4ade80; margin-bottom:10px;"><i class="fab fa-chrome"></i> Step 2: Browser (Chrome) Permission</h3>
            
            <p style="font-size:13px; font-weight:bold; color:#f8fafc; margin-bottom:5px;">Sabse Aasan Tarika:</p>
            <ul style="font-size:12px; color:#cbd5e1; margin-left:20px; margin-bottom:10px;">
                <li>Browser mein upar jahan URL (link) likha hota hai, uske left side mein ek chota sa <b>Lock (🔒) icon</b> hoga, us par click karein.</li>
                <li>Wahan Permissions ya Location ka option aayega, use <b>Allow</b> kar dein.</li>
            </ul>

            <p style="font-size:13px; font-weight:bold; color:#f8fafc; margin-bottom:5px;">Agar Settings se theek karna ho:</p>
            <ul style="font-size:12px; color:#cbd5e1; margin-left:20px;">
                <li>Chrome browser kholen aur upar right corner mein <b>3 dots (⋮)</b> par click karein.</li>
                <li><b>Settings > Site Settings > Location</b> par click karein.</li>
                <li>Check karein ki web app "Blocked" list mein toh nahi hai. Agar hai, toh us par click karke <b>Allow</b> kar dein.</li>
            </ul>
        </div>

        <button onclick="window.location.reload()" style="background:linear-gradient(135deg, #22c55e, #16a34a); color:white; padding:15px 30px; border:none; border-radius:30px; font-weight:bold; font-size:16px; width:100%; max-width:400px; box-shadow:0 4px 15px rgba(34, 197, 94, 0.3); margin-bottom:30px;">
            <i class="fas fa-check-circle"></i> Maine ON kar diya - REFRESH
        </button>
    </div>"""

idx = idx.replace(target, repl)

with open(staff_idx_path, 'w', encoding='utf-8') as f:
    f.write(idx)
print("Updated GPS blocker with instructions")
