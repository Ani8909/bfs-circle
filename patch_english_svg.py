import os
import re

staff_idx_path = r'c:\Users\pc\Downloads\client mgmt2\staff\index.php'
with open(staff_idx_path, 'r', encoding='utf-8') as f:
    idx = f.read()

start_marker = '<!-- GPS Blocker Overlay (Professional UI v2) -->'
end_marker = '    <!-- 10x Field Force Tracker Engine -->'

start_idx = idx.find(start_marker)
end_idx = idx.find(end_marker)

if start_idx != -1 and end_idx != -1:
    new_block = """<!-- GPS Blocker Overlay (Premium English + SVG) -->
    <div id="gps-blocker" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15, 23, 42, 0.75); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); z-index:999999; flex-direction:column; align-items:center; justify-content:center; padding:20px;">
        
        <div style="width:100%; max-width:380px; background:#ffffff; border-radius:28px; padding:36px 24px; box-shadow:0 24px 50px rgba(0,0,0,0.25); text-align:center;">
            
            <!-- Animated Pulse SVG Icon -->
            <div style="position:relative; width:72px; height:72px; margin:0 auto 20px auto;">
                <div style="position:absolute; inset:0; background:#FFEDD5; border-radius:50%; animation: pulse 2s infinite;"></div>
                <div style="position:absolute; inset:4px; background:#FFEDD5; border-radius:50%; box-shadow:0 8px 16px rgba(255,122,0,0.15); display:flex; align-items:center; justify-content:center;">
                    <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#FF7A00" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                        <circle cx="12" cy="10" r="3"></circle>
                    </svg>
                </div>
            </div>
            
            <h2 style="margin:0 0 8px 0; font-size:22px; color:#0F172A; font-weight:800; letter-spacing:-0.5px;">Location Access Required</h2>
            <p style="margin:0 0 28px 0; font-size:14px; color:#64748B; line-height:1.5;">To go On Duty, you need to enable location access for accurate field tracking.</p>
            
            <!-- Step 1 Card -->
            <div style="background:#F8FAFC; border-radius:20px; padding:18px; margin-bottom:12px; display:flex; align-items:flex-start; gap:16px; text-align:left; border:1px solid #E2E8F0;">
                <div style="width:42px; height:42px; background:white; border-radius:12px; display:flex; align-items:center; justify-content:center; box-shadow:0 4px 10px rgba(0,0,0,0.04); flex-shrink:0;">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#3B82F6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect>
                        <line x1="12" y1="18" x2="12.01" y2="18"></line>
                    </svg>
                </div>
                <div style="flex:1;">
                    <div style="font-size:11px; font-weight:800; color:#3B82F6; text-transform:uppercase; letter-spacing:0.8px; margin-bottom:4px;">Step 1</div>
                    <div style="font-size:15px; color:#1E293B; font-weight:700; margin-bottom:6px;">Enable Device GPS</div>
                    <div style="font-size:13px; color:#475569; font-weight:500; line-height:1.5;">
                        Swipe down from the top of your screen and ensure the <b>Location / GPS</b> toggle is turned ON.
                    </div>
                </div>
            </div>

            <!-- Step 2 Card -->
            <div style="background:#F8FAFC; border-radius:20px; padding:18px; margin-bottom:32px; display:flex; align-items:flex-start; gap:16px; text-align:left; border:1px solid #E2E8F0;">
                <div style="width:42px; height:42px; background:white; border-radius:12px; display:flex; align-items:center; justify-content:center; box-shadow:0 4px 10px rgba(0,0,0,0.04); flex-shrink:0;">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                </div>
                <div style="flex:1;">
                    <div style="font-size:11px; font-weight:800; color:#10B981; text-transform:uppercase; letter-spacing:0.8px; margin-bottom:4px;">Step 2</div>
                    <div style="font-size:15px; color:#1E293B; font-weight:700; margin-bottom:6px;">Allow Browser Access</div>
                    <div style="font-size:13px; color:#475569; font-weight:500; line-height:1.5;">
                        Tap the <b>🔒 Lock Icon</b> in your browser's address bar, go to <b>Permissions</b>, and set Location to <b>Allow</b>.
                    </div>
                </div>
            </div>

            <button onclick="window.location.reload()" style="background:linear-gradient(135deg, #FF7A00, #E66A00); color:white; padding:18px; border:none; border-radius:18px; font-weight:700; font-size:16px; width:100%; box-shadow:0 8px 25px rgba(255,122,0,0.3); cursor:pointer; margin-bottom:16px; transition: transform 0.2s, box-shadow 0.2s;">
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
            50% { transform: scale(1.1); opacity: 0.3; }
            100% { transform: scale(1); opacity: 0.8; }
        }
    </style>
"""
    
    idx = idx[:start_idx] + new_block + idx[end_idx:]
    
    with open(staff_idx_path, 'w', encoding='utf-8') as f:
        f.write(idx)
    print("Upgraded to English Content + Premium SVG Icons")
else:
    print("Markers not found.")
