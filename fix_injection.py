import os

file_path = r'c:\Users\pc\Downloads\client mgmt2\staff\add_visit.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Insert Check-in UI and start of main_form_container
target_start = """        <form id="fieldVisitForm" onsubmit="submitForm(event)">
            <!-- Hidden Fields -->"""

repl_start = """        <form id="fieldVisitForm" onsubmit="submitForm(event)">
            <input type="hidden" id="check_in_time" name="check_in_time">
            <input type="hidden" id="check_out_time" name="check_out_time">
            
            <!-- Smart Check-in System UI -->
            <div id="smart_checkin_container" style="background:white; border-radius:24px; padding:30px 20px; box-shadow:0 12px 30px rgba(0,0,0,0.08); text-align:center; margin-bottom: 20px; border:1px solid #f1f5f9;">
                <div style="width:80px; height:80px; background:#fff3e0; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px;">
                    <i class="fas fa-handshake" style="font-size:32px; color:var(--primary);"></i>
                </div>
                <h2 style="font-size:20px; color:#1e293b; margin-bottom:8px;">Start Client Visit</h2>
                <p style="font-size:14px; color:#64748b; margin-bottom:30px;">Check-in securely to start recording your meeting time.</p>
                
                <button type="button" id="start_visit_btn" onclick="startCheckIn()" style="background:linear-gradient(135deg, #FF6B00 0%, #FF9A44 100%); color:white; border:none; width:100%; padding:18px; border-radius:16px; font-size:16px; font-weight:700; box-shadow:0 8px 20px rgba(255, 107, 0, 0.3); cursor:pointer;">
                    <i class="fas fa-sign-in-alt" style="margin-right:8px;"></i> Check-in & Start Visit
                </button>
                
                <div id="meeting_in_progress" style="display:none;">
                    <div style="font-size:13px; color:#10b981; font-weight:600; margin-bottom:8px; display:flex; align-items:center; justify-content:center; gap:6px;">
                        <span style="width:8px; height:8px; background:#10b981; border-radius:50%; display:inline-block; animation: pulse 1.5s infinite;"></span> Meeting in progress...
                    </div>
                    
                    <!-- Voice Recording Indicator -->
                    <div style="margin-bottom:15px; display:flex; align-items:center; justify-content:center; gap:8px; color:#ef4444; font-weight:600; font-size:14px;">
                        <i class="fas fa-microphone" style="animation: pulse 1s infinite;"></i> <span id="recording_status">Recording meeting audio...</span>
                    </div>

                    <div id="visit_timer" style="font-size:36px; font-weight:800; color:#0f172a; margin-bottom:24px; font-variant-numeric: tabular-nums;">00:00:00</div>
                    <button type="button" onclick="endCheckIn()" style="background:#0f172a; color:white; border:none; width:100%; padding:18px; border-radius:16px; font-size:16px; font-weight:700; box-shadow:0 8px 20px rgba(15, 23, 42, 0.2); cursor:pointer;">
                        <i class="fas fa-sign-out-alt" style="margin-right:8px;"></i> End Visit & Fill Form
                    </button>
                </div>
            </div>
            
            <style>
                @keyframes pulse {
                    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
                    70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
                    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
                }
            </style>

            <div id="main_form_container" style="display:none;">
            <!-- Hidden Fields -->"""

content = content.replace(target_start, repl_start)

# 2. Close main_form_container right before </form>
target_end = """        </form>"""
repl_end = """            </div> <!-- End main_form_container -->
        </form>"""

content = content.replace(target_end, repl_end)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Fixed UI injection for Smart Check-in System")
