import os

file_path = r'c:\Users\pc\Downloads\client mgmt2\admin_tracking.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Update the table button
target_btn = """                      <td>
                          <button class="btn-route" onclick="viewRoute('<?= $uname ?>')">
                              <i data-lucide="map"></i> View Route Map
                          </button>
                      </td>"""
repl_btn = """                      <td>
                          <button class="btn-route" style="background:#4f46e5;" onclick="openStaff360('<?= $uname ?>')">
                              <i class="fas fa-id-card"></i> 360° Profile
                          </button>
                      </td>"""

# Handle the alternate case where data is missing
target_btn2 = """                      <td>
                          <button class="btn-route" style="background:#94a3b8; cursor:not-allowed;" disabled>
                              <i data-lucide="map-pin-off"></i> No Data Today
                          </button>
                      </td>"""
repl_btn2 = """                      <td>
                          <button class="btn-route" style="background:#4f46e5;" onclick="openStaff360('<?= $uname ?>')">
                              <i class="fas fa-id-card"></i> 360° Profile
                          </button>
                      </td>"""

content = content.replace(target_btn, repl_btn).replace(target_btn2, repl_btn2)

# 2. Add Drawer HTML and CSS before script tags
drawer_html = """
<!-- Staff 360 Drawer -->
<div class="modal-overlay" id="s360-overlay" onclick="closeStaff360()" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999;"></div>
<div id="s360-drawer" style="position:fixed; top:0; right:-500px; width:450px; height:100%; background:#fff; z-index:10000; box-shadow:-5px 0 25px rgba(0,0,0,0.1); transition:0.3s; display:flex; flex-direction:column;">
    <div style="background:linear-gradient(135deg, #1e293b, #0f172a); color:white; padding:20px; position:relative;">
        <i class="fas fa-times" onclick="closeStaff360()" style="position:absolute; top:20px; right:20px; cursor:pointer; font-size:18px; color:rgba(255,255,255,0.7);"></i>
        <div style="display:flex; align-items:center; gap:16px;">
            <div id="s360-avatar" style="width:60px; height:60px; border-radius:50%; background:#3b82f6; display:flex; align-items:center; justify-content:center; font-size:24px; font-weight:bold; border:2px solid rgba(255,255,255,0.2);">A</div>
            <div>
                <h2 id="s360-name" style="font-size:18px; margin:0 0 4px 0;">Staff Name</h2>
                <div id="s360-role" style="font-size:13px; color:#cbd5e1; margin-bottom:6px;">EMP-ID | Role</div>
                <div id="s360-badge" style="display:inline-block; padding:4px 10px; border-radius:20px; font-size:11px; font-weight:600; background:rgba(255,255,255,0.1);">Status</div>
            </div>
        </div>
    </div>
    
    <div style="display:flex; border-bottom:1px solid #e2e8f0; background:#f8fafc;">
        <div class="s360-tab active" onclick="switchS360Tab('today')" id="tab-today">🔴 Live Today</div>
        <div class="s360-tab" onclick="switchS360Tab('history')" id="tab-history">🕒 All-Time History</div>
    </div>
    
    <div style="flex:1; overflow-y:auto; padding:20px;" id="s360-content">
        <!-- Live Today Tab -->
        <div id="s360-pane-today" class="s360-pane active">
            <h4 style="font-size:14px; margin-bottom:12px; color:#334155;">Today's Activity Timeline</h4>
            <div id="s360-timeline" style="border-left:2px solid #e2e8f0; margin-left:10px; padding-left:16px; position:relative;">
                Loading...
            </div>
        </div>
        
        <!-- History Tab -->
        <div id="s360-pane-history" class="s360-pane" style="display:none;">
            <h4 style="font-size:14px; margin-bottom:12px; color:#334155;">Past 30 Days Attendance</h4>
            <div id="s360-history-table">
                Loading...
            </div>
        </div>
    </div>
</div>

<style>
.s360-tab { flex:1; text-align:center; padding:12px 0; font-size:13px; font-weight:600; color:#64748b; cursor:pointer; border-bottom:2px solid transparent; transition:0.2s; }
.s360-tab:hover { color:#3b82f6; background:#f1f5f9; }
.s360-tab.active { color:#3b82f6; border-bottom:2px solid #3b82f6; background:#fff; }
.s360-tl-item { position:relative; margin-bottom:20px; }
.s360-tl-item::before { content:''; position:absolute; left:-21px; top:2px; width:10px; height:10px; border-radius:50%; background:#3b82f6; border:2px solid #fff; box-shadow:0 0 0 1px #cbd5e1; }
.s360-tl-time { font-size:11.5px; font-weight:700; color:#64748b; margin-bottom:4px; }
.s360-tl-card { background:#fff; border:1px solid #e2e8f0; border-radius:8px; padding:12px; box-shadow:0 1px 3px rgba(0,0,0,0.05); }
.hist-row { display:flex; justify-content:space-between; padding:12px 0; border-bottom:1px solid #e2e8f0; }
</style>

<script>
function openStaff360(username) {
    document.getElementById('s360-overlay').style.display = 'block';
    document.getElementById('s360-drawer').style.right = '0';
    loadStaff360(username);
}

function closeStaff360() {
    document.getElementById('s360-overlay').style.display = 'none';
    document.getElementById('s360-drawer').style.right = '-500px';
}

function switchS360Tab(tab) {
    document.querySelectorAll('.s360-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.s360-pane').forEach(p => p.style.display = 'none');
    document.getElementById('tab-' + tab).classList.add('active');
    document.getElementById('s360-pane-' + tab).style.display = 'block';
}

async function loadStaff360(username) {
    document.getElementById('s360-timeline').innerHTML = '<p>Loading...</p>';
    document.getElementById('s360-history-table').innerHTML = '<p>Loading...</p>';
    
    try {
        const res = await fetch(`?api=get_staff_360&username=${username}`);
        const data = await res.json();
        
        // Profile mapping
        const p = data.profile;
        document.getElementById('s360-name').innerText = p.full_name || username;
        document.getElementById('s360-avatar').innerText = (p.full_name || username).charAt(0).toUpperCase();
        document.getElementById('s360-role').innerText = p.role + " | " + username;
        
        let badge = '';
        if (p.current_status === 'Online') badge = `<span style="color:#4ade80;"><i class="fas fa-circle"></i> On Duty</span>`;
        else badge = `<span style="color:#f87171;"><i class="fas fa-circle"></i> Off Duty</span>`;
        
        if (p.current_battery) badge += ` &nbsp;|&nbsp; 🔋 ${p.current_battery}`;
        document.getElementById('s360-badge').innerHTML = badge;
        
        // Today's Timeline
        let tlHtml = '';
        if (data.today_attendance && data.today_attendance.punch_in) {
            tlHtml += `
                <div class="s360-tl-item">
                    <div class="s360-tl-time">${data.today_attendance.punch_in.split(' ')[1]}</div>
                    <div class="s360-tl-card" style="border-left:3px solid #10b981;">
                        <div style="font-weight:600; color:#10b981;"><i class="fas fa-sign-in-alt"></i> Punched In (Shift Started)</div>
                    </div>
                </div>
            `;
        }
        
        if (data.today_visits && data.today_visits.length > 0) {
            data.today_visits.forEach(v => {
                tlHtml += `
                    <div class="s360-tl-item">
                        <div class="s360-tl-time">${v.check_in_time || 'Unknown Time'}</div>
                        <div class="s360-tl-card">
                            <div style="font-weight:700; color:#334155; margin-bottom:4px;">${v.firm_name}</div>
                            <div style="font-size:12px; color:#64748b;">👤 ${v.person_name} | 📞 ${v.mobile}</div>
                            ${v.verified_address ? `<div style="font-size:11px; margin-top:6px; color:#94a3b8;"><i class="fas fa-map-marker-alt"></i> ${v.verified_address}</div>` : ''}
                            ${v.audio_path ? `<audio controls style="width:100%; height:25px; margin-top:8px;"><source src="${v.audio_path}"></audio>` : ''}
                        </div>
                    </div>
                `;
            });
        }
        
        if (data.today_attendance && data.today_attendance.punch_out) {
            tlHtml += `
                <div class="s360-tl-item">
                    <div class="s360-tl-time">${data.today_attendance.punch_out.split(' ')[1]}</div>
                    <div class="s360-tl-card" style="border-left:3px solid #ef4444;">
                        <div style="font-weight:600; color:#ef4444;"><i class="fas fa-sign-out-alt"></i> Punched Out (Shift Ended)</div>
                    </div>
                </div>
            `;
        }
        
        if (tlHtml === '') tlHtml = '<div style="font-size:13px; color:#94a3b8;">No activity logged today.</div>';
        document.getElementById('s360-timeline').innerHTML = tlHtml;
        
        // History Table
        let hHtml = '';
        if (data.history && data.history.length > 0) {
            data.history.forEach(h => {
                hHtml += `
                    <div class="hist-row">
                        <div>
                            <div style="font-weight:600; font-size:13px; color:#334155;">${h.att_date}</div>
                            <div style="font-size:11px; color:#94a3b8;">${h.punch_in ? h.punch_in.split(' ')[1] : '--'} to ${h.punch_out ? h.punch_out.split(' ')[1] : '--'}</div>
                        </div>
                        <div style="text-align:right;">
                            <div style="font-weight:700; font-size:13px; color:#10b981;">${h.duration}</div>
                            <div style="font-size:11px; color:#64748b;">${parseFloat(h.total_distance||0).toFixed(1)} km</div>
                        </div>
                    </div>
                `;
            });
        } else {
            hHtml = '<div style="font-size:13px; color:#94a3b8;">No past attendance found.</div>';
        }
        document.getElementById('s360-history-table').innerHTML = hHtml;
        
    } catch (e) {
        document.getElementById('s360-timeline').innerHTML = '<p style="color:red;">Error loading data.</p>';
        console.error(e);
    }
}
</script>
"""

# Insert before closing script tag or at end of body if we prefer
if "<!-- Route Map Modal -->" in content:
    content = content.replace("<!-- Route Map Modal -->", drawer_html + "\n<!-- Route Map Modal -->")
else:
    # Append before closing php tag or at the very end
    content += drawer_html

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Updated admin_tracking.php with 360 Drawer")
