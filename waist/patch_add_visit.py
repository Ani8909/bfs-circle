import os

file_path = r'c:\Users\pc\Downloads\client mgmt2\staff\add_visit.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Pincode API Logic + Check-in / Check-out Logic
js_additions = """
        // Pincode Auto-fill Logic
        document.getElementById('pincode').addEventListener('input', async function() {
            if (this.value.length === 6) {
                try {
                    const res = await fetch(`https://api.postalpincode.in/pincode/${this.value}`);
                    const data = await res.json();
                    if (data[0].Status === "Success") {
                        const postOffice = data[0].PostOffice[0];
                        const state = postOffice.State;
                        const district = postOffice.District;
                        
                        const stateSelect = document.getElementById('state');
                        stateSelect.innerHTML = `<option value="${state}" selected>${state}</option>`;
                        
                        const citySelect = document.getElementById('city');
                        citySelect.innerHTML = `<option value="${district}" selected>${district}</option>`;
                    }
                } catch(e) {
                    console.log("Pincode fetch error:", e);
                }
            }
        });

        // Smart Check-in System
        let checkInInterval;
        let checkInTimeObj = null;

        function startCheckIn() {
            document.getElementById('start_visit_btn').style.display = 'none';
            document.getElementById('meeting_in_progress').style.display = 'block';
            
            checkInTimeObj = new Date();
            // Format check-in for DB (MySQL datetime format)
            const pad = n => n<10 ? '0'+n : n;
            const yyyy = checkInTimeObj.getFullYear();
            const mm = pad(checkInTimeObj.getMonth()+1);
            const dd = pad(checkInTimeObj.getDate());
            const hh = pad(checkInTimeObj.getHours());
            const mi = pad(checkInTimeObj.getMinutes());
            const ss = pad(checkInTimeObj.getSeconds());
            
            document.getElementById('check_in_time').value = `${yyyy}-${mm}-${dd} ${hh}:${mi}:${ss}`;
            
            // Start Timer
            checkInInterval = setInterval(() => {
                const now = new Date();
                const diff = Math.floor((now - checkInTimeObj) / 1000);
                const hrs = Math.floor(diff / 3600);
                const mins = Math.floor((diff % 3600) / 60);
                const secs = diff % 60;
                document.getElementById('visit_timer').innerText = 
                    `${pad(hrs)}:${pad(mins)}:${pad(secs)}`;
            }, 1000);
            
            // Try fetching geo
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(pos => {
                    document.getElementById('lat').value = pos.coords.latitude;
                    document.getElementById('lon').value = pos.coords.longitude;
                });
            }
        }

        function endCheckIn() {
            clearInterval(checkInInterval);
            
            const now = new Date();
            const pad = n => n<10 ? '0'+n : n;
            const yyyy = now.getFullYear();
            const mm = pad(now.getMonth()+1);
            const dd = pad(now.getDate());
            const hh = pad(now.getHours());
            const mi = pad(now.getMinutes());
            const ss = pad(now.getSeconds());
            
            document.getElementById('check_out_time').value = `${yyyy}-${mm}-${dd} ${hh}:${mi}:${ss}`;
            
            // Hide Checkin UI, Show Real Form
            document.getElementById('smart_checkin_container').style.display = 'none';
            document.getElementById('main_form_container').style.display = 'block';
        }
"""

# Insert right after submitForm
content = content.replace("async function submitForm(e) {", js_additions + "\n        async function submitForm(e) {")

# 2. Add hidden fields for check-in/out
hidden_fields = """<form id="fieldVisitForm">
                <input type="hidden" id="check_in_time" name="check_in_time">
                <input type="hidden" id="check_out_time" name="check_out_time">"""
content = content.replace('<form id="fieldVisitForm">', hidden_fields)

# 3. Wrap the form content inside main_form_container and add checkin UI
form_start_target = """<form id="fieldVisitForm">"""
form_start_repl = """
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

            <form id="fieldVisitForm">
                <input type="hidden" id="check_in_time" name="check_in_time">
                <input type="hidden" id="check_out_time" name="check_out_time">
                <div id="main_form_container" style="display:none;">
"""
content = content.replace('<form id="fieldVisitForm">', form_start_repl)

# 4. Close the main_form_container
form_end_target = """                <button type="submit" style="width:100%; background:var(--primary); color:white; border:none; padding:15px; border-radius:10px; font-size:16px; font-weight:600; cursor:pointer; margin-bottom:30px; display:flex; justify-content:center; align-items:center; transition:background 0.2s;">
                    <i class="fas fa-paper-plane" style="margin-right:8px;"></i> Submit Record
                </button>
            </form>"""
form_end_repl = """                <button type="submit" style="width:100%; background:var(--primary); color:white; border:none; padding:15px; border-radius:10px; font-size:16px; font-weight:600; cursor:pointer; margin-bottom:30px; display:flex; justify-content:center; align-items:center; transition:background 0.2s;">
                    <i class="fas fa-paper-plane" style="margin-right:8px;"></i> Submit Record
                </button>
                </div> <!-- End main_form_container -->
            </form>"""
content = content.replace(form_end_target, form_end_repl)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)
print("Implemented Smart Check-in System and Pincode Logic")
