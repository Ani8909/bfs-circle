import os

staff_idx_path = r'c:\Users\pc\Downloads\client mgmt2\staff\index.php'
with open(staff_idx_path, 'r', encoding='utf-8') as f:
    idx = f.read()

# I will find the gps-blocker block and replace it
# To do this safely, I'll extract everything between `<div id="gps-blocker"` and the end of that block.
# Actually, I'll just replace from `<!-- GPS Blocker Overlay (Detailed Instructions) -->` to the end of the div.

import re
pattern = re.compile(r'<!-- GPS Blocker Overlay \(Detailed Instructions\) -->.*?</div>\s*</div>\s*</div>\s*<button.*?</button>\s*</div>', re.DOTALL)
# Wait, my previous replacement was just one big div. Let me find the exact string.

# Let's just read the file and replace the whole block by finding the start and end.
start_marker = '<!-- GPS Blocker Overlay'
end_marker = '    <!-- 10x Field Force Tracker Engine -->'

start_idx = idx.find(start_marker)
end_idx = idx.find(end_marker)

if start_idx != -1 and end_idx != -1:
    old_block = idx[start_idx:end_idx]
    
    new_block = """<!-- GPS Blocker Overlay (Ultra Simple) -->
    <div id="gps-blocker" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15, 23, 42, 0.95); backdrop-filter: blur(10px); z-index:999999; flex-direction:column; align-items:center; justify-content:center; padding:20px; text-align:left;">
        
        <div style="width:100%; max-width:380px; background:white; border-radius:24px; padding:30px 20px; box-shadow:0 10px 40px rgba(0,0,0,0.5); text-align:center;">
            
            <div style="width:70px; height:70px; background:#fee2e2; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 15px auto;">
                <i class="fas fa-map-marker-alt" style="font-size:35px; color:#ef4444;"></i>
            </div>
            
            <h2 style="margin-bottom:20px; font-size:22px; color:#0f172a;">Location Required</h2>
            
            <!-- Step 1 -->
            <div style="background:#f8fafc; border-radius:12px; padding:15px; margin-bottom:15px; text-align:left; border:1px solid #e2e8f0;">
                <div style="font-weight:700; color:#3b82f6; font-size:14px; margin-bottom:8px;"><i class="fas fa-mobile-alt"></i> STEP 1: Phone GPS</div>
                <div style="font-size:13px; color:#475569; font-weight:600;">
                    ⏬ Swipe Down <i class="fas fa-arrow-right" style="color:#cbd5e1; margin:0 5px;"></i> 🎯 Turn ON Location
                </div>
            </div>

            <!-- Step 2 -->
            <div style="background:#f8fafc; border-radius:12px; padding:15px; margin-bottom:25px; text-align:left; border:1px solid #e2e8f0;">
                <div style="font-weight:700; color:#10b981; font-size:14px; margin-bottom:8px;"><i class="fab fa-chrome"></i> STEP 2: Browser Chrome</div>
                
                <div style="font-size:13px; color:#475569; font-weight:600; margin-bottom:12px;">
                    <i class="fas fa-lock" style="color:#64748b;"></i> Lock icon (top URL) <i class="fas fa-arrow-right" style="color:#cbd5e1; margin:0 5px;"></i> Permissions <i class="fas fa-arrow-right" style="color:#cbd5e1; margin:0 5px;"></i> Allow Location
                </div>
                
                <div style="font-size:11px; color:#94a3b8; font-weight:bold; text-transform:uppercase; text-align:center; margin-bottom:12px;">OR</div>
                
                <div style="font-size:13px; color:#475569; font-weight:600;">
                    <i class="fas fa-ellipsis-v" style="color:#64748b;"></i> 3 Dots <i class="fas fa-arrow-right" style="color:#cbd5e1; margin:0 5px;"></i> Settings <i class="fas fa-arrow-right" style="color:#cbd5e1; margin:0 5px;"></i> Site Settings <i class="fas fa-arrow-right" style="color:#cbd5e1; margin:0 5px;"></i> Location <i class="fas fa-arrow-right" style="color:#cbd5e1; margin:0 5px;"></i> Allow
                </div>
            </div>

            <button onclick="window.location.reload()" style="background:linear-gradient(135deg, #22c55e, #16a34a); color:white; padding:16px; border:none; border-radius:16px; font-weight:bold; font-size:16px; width:100%; box-shadow:0 4px 15px rgba(34, 197, 94, 0.3); cursor:pointer;">
                <i class="fas fa-check-circle"></i> MAINE ON KAR DIYA
            </button>
            <button onclick="document.getElementById('dutyToggle').checked = false; hideGpsBlocker();" style="background:none; border:none; color:#94a3b8; font-size:14px; margin-top:15px; font-weight:600; cursor:pointer;">Cancel</button>
        </div>
    </div>
"""
    
    idx = idx[:start_idx] + new_block + idx[end_idx:]
    
    with open(staff_idx_path, 'w', encoding='utf-8') as f:
        f.write(idx)
    print("Simplified the GPS blocker UI")
else:
    print("Markers not found.")
