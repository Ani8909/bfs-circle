import os
import re

staff_idx_path = r'c:\Users\pc\Downloads\client mgmt2\staff\index.php'
with open(staff_idx_path, 'r', encoding='utf-8') as f:
    idx = f.read()

start_marker = '<!-- GPS Blocker Overlay (Professional UI) -->'
end_marker = '    <!-- 10x Field Force Tracker Engine -->'

start_idx = idx.find(start_marker)
end_idx = idx.find(end_marker)

if start_idx != -1 and end_idx != -1:
    new_block = """<!-- GPS Blocker Overlay (Professional UI v2) -->
    <div id="gps-blocker" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15, 23, 42, 0.7); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); z-index:999999; flex-direction:column; align-items:center; justify-content:center; padding:20px;">
        
        <div style="width:100%; max-width:360px; background:white; border-radius:28px; padding:32px 24px; box-shadow:0 24px 50px rgba(0,0,0,0.2); text-align:center;">
            
            <!-- Animated Pulse Icon -->
            <div style="position:relative; width:72px; height:72px; margin:0 auto 20px auto;">
                <div style="position:absolute; inset:0; background:#FFEDD5; border-radius:50%; animation: pulse 2s infinite;"></div>
                <div style="position:absolute; inset:4px; background:#FFEDD5; border-radius:50%; box-shadow:0 8px 16px rgba(255,122,0,0.15); display:flex; align-items:center; justify-content:center;">
                    <i class="fas fa-map-marker-alt" style="font-size:28px; color:#FF7A00;"></i>
                </div>
            </div>
            
            <h2 style="margin:0 0 6px 0; font-size:22px; color:#0F172A; font-weight:800; letter-spacing:-0.5px;">Location Required</h2>
            <p style="margin:0 0 24px 0; font-size:14px; color:#64748B; line-height:1.5;">On Duty jane ke liye Location chalu karna zaroori hai.</p>
            
            <!-- Step 1 Card -->
            <div style="background:#F8FAFC; border-radius:20px; padding:16px; margin-bottom:12px; display:flex; align-items:flex-start; gap:16px; text-align:left; border:1px solid #F1F5F9;">
                <div style="width:42px; height:42px; background:white; border-radius:12px; display:flex; align-items:center; justify-content:center; box-shadow:0 4px 10px rgba(0,0,0,0.04); flex-shrink:0;">
                    <i class="fas fa-mobile-alt" style="color:#3B82F6; font-size:20px;"></i>
                </div>
                <div style="flex:1;">
                    <div style="font-size:11px; font-weight:800; color:#3B82F6; text-transform:uppercase; letter-spacing:0.8px; margin-bottom:4px;">Step 1</div>
                    <div style="font-size:14px; color:#1E293B; font-weight:700; margin-bottom:4px;">Phone ka GPS ON karein</div>
                    <div style="font-size:12px; color:#475569; font-weight:500; line-height:1.4;">
                        Apne phone ki screen upar se niche kheenche (swipe down) aur <b>Location (GPS)</b> ko ON karein.
                    </div>
                </div>
            </div>

            <!-- Step 2 Card -->
            <div style="background:#F8FAFC; border-radius:20px; padding:16px; margin-bottom:28px; display:flex; align-items:flex-start; gap:16px; text-align:left; border:1px solid #F1F5F9;">
                <div style="width:42px; height:42px; background:white; border-radius:12px; display:flex; align-items:center; justify-content:center; box-shadow:0 4px 10px rgba(0,0,0,0.04); flex-shrink:0;">
                    <i class="fas fa-globe" style="color:#10B981; font-size:20px;"></i>
                </div>
                <div style="flex:1;">
                    <div style="font-size:11px; font-weight:800; color:#10B981; text-transform:uppercase; letter-spacing:0.8px; margin-bottom:4px;">Step 2</div>
                    <div style="font-size:14px; color:#1E293B; font-weight:700; margin-bottom:4px;">Browser Permission dein</div>
                    <div style="font-size:12px; color:#475569; font-weight:500; line-height:1.5;">
                        Upar Search Bar ke left mein chote se <b>🔒 Lock Icon</b> par click karein.<br>
                        Fir <b>Permissions <i class="fas fa-chevron-right" style="font-size:9px; color:#cbd5e1; margin:0 3px;"></i> Location</b> par jaakar <b>Allow</b> karein.
                    </div>
                </div>
            </div>

            <button onclick="window.location.reload()" style="background:linear-gradient(135deg, #FF7A00, #E66A00); color:white; padding:16px; border:none; border-radius:16px; font-weight:700; font-size:15px; width:100%; box-shadow:0 8px 25px rgba(255,122,0,0.3); cursor:pointer; margin-bottom:12px;">
                I've Enabled It - Refresh
            </button>
            <button onclick="document.getElementById('dutyToggle').checked = false; hideGpsBlocker();" style="background:transparent; border:none; color:#94A3B8; font-size:14px; font-weight:600; cursor:pointer; padding:8px;">
                Cancel
            </button>
        </div>
    </div>
    <style>
        @keyframes pulse {
            0% { transform: scale(1); opacity: 0.8; }
            50% { transform: scale(1.1); opacity: 0.4; }
            100% { transform: scale(1); opacity: 0.8; }
        }
    </style>
"""
    
    idx = idx[:start_idx] + new_block + idx[end_idx:]
    
    with open(staff_idx_path, 'w', encoding='utf-8') as f:
        f.write(idx)
    print("Fixed icon and expanded instructions clearly")
else:
    print("Markers not found.")
