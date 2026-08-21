import os
import re

file_path = r'c:\Users\pc\Downloads\client mgmt2\admin_tracking.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# I will replace everything from <!-- Staff 360 Drawer --> down to the end of the <style> block related to s360.
# The JS logic will stay mostly the same but I'll update the JS rendering for timeline items to use the new CSS classes.

target_regex = re.compile(r'<!-- Staff 360 Drawer -->.*?</style>', re.DOTALL)

new_drawer = """<!-- Staff 360 Drawer -->
<div class="modal-overlay" id="s360-overlay" onclick="closeStaff360()" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.6); backdrop-filter:blur(2px); z-index:9999; transition: opacity 0.3s ease;"></div>
<div id="s360-drawer" style="position:fixed; top:0; right:-550px; width:480px; max-width:100%; height:100%; background:#f8fafc; z-index:10000; box-shadow:-10px 0 40px rgba(0,0,0,0.15); transition: right 0.4s cubic-bezier(0.16, 1, 0.3, 1); display:flex; flex-direction:column;">
    
    <!-- Header Section -->
    <div style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color:white; padding: 24px 24px 32px 24px; position:relative; border-bottom-left-radius: 24px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
        <!-- Top right close icon -->
        <i class="fas fa-times s360-close-btn" onclick="closeStaff360()"></i>
        
        <!-- Back button at the top left -->
        <div onclick="closeStaff360()" class="s360-back-btn">
            <i class="fas fa-arrow-left"></i> Back to Radar
        </div>
        
        <div style="display:flex; align-items:center; gap:20px;">
            <div id="s360-avatar" style="width:72px; height:72px; border-radius:50%; background:linear-gradient(135deg, #3b82f6, #2563eb); display:flex; align-items:center; justify-content:center; font-size:28px; font-weight:800; border:3px solid rgba(255,255,255,0.15); box-shadow: 0 4px 10px rgba(0,0,0,0.3); overflow:hidden;">A</div>
            <div>
                <h2 id="s360-name" style="font-size:22px; font-weight:700; margin:0 0 4px 0; letter-spacing: -0.02em;">Staff Name</h2>
                <div id="s360-role" style="font-size:13.5px; color:#94a3b8; margin-bottom:10px; font-weight:500;">EMP-ID | Role</div>
                <div id="s360-badge" style="display:inline-flex; align-items:center; padding:5px 12px; border-radius:30px; font-size:12px; font-weight:600; background:rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.05); backdrop-filter: blur(4px);">Status</div>
            </div>
        </div>
    </div>
    
    <!-- Floating Tabs -->
    <div style="padding: 0 24px; margin-top: -24px; position: relative; z-index: 2;">
        <div style="display:flex; background: #fff; border-radius: 12px; padding: 6px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border: 1px solid #e2e8f0;">
            <div class="s360-tab active" onclick="switchS360Tab('today')" id="tab-today"><i class="fas fa-circle" style="color:#ef4444; font-size:10px; margin-right:6px;"></i> Live Today</div>
            <div class="s360-tab" onclick="switchS360Tab('history')" id="tab-history"><i class="fas fa-history" style="color:#64748b; margin-right:6px;"></i> All-Time History</div>
        </div>
    </div>
    
    <!-- Content Body -->
    <div style="flex:1; overflow-y:auto; padding:24px;" id="s360-content">
        <!-- Live Today Tab -->
        <div id="s360-pane-today" class="s360-pane active">
            <h4 style="font-size:15px; font-weight:700; margin-bottom:20px; color:#1e293b; display:flex; align-items:center; gap:8px;">
                <i class="fas fa-map-signs" style="color:#3b82f6;"></i> Today's Activity Timeline
            </h4>
            <div id="s360-timeline" class="s360-timeline-container">
                Loading...
            </div>
        </div>
        
        <!-- History Tab -->
        <div id="s360-pane-history" class="s360-pane" style="display:none;">
            <h4 style="font-size:15px; font-weight:700; margin-bottom:20px; color:#1e293b; display:flex; align-items:center; gap:8px;">
                <i class="fas fa-calendar-check" style="color:#3b82f6;"></i> Past 30 Days Attendance
            </h4>
            <div id="s360-history-table">
                Loading...
            </div>
        </div>
    </div>
</div>

<style>
/* Drawer Custom CSS */
.s360-close-btn { position:absolute; top:24px; right:24px; cursor:pointer; font-size:18px; color:rgba(255,255,255,0.6); background: rgba(255,255,255,0.05); width: 36px; height: 36px; display:flex; align-items:center; justify-content:center; border-radius: 50%; transition: 0.2s; }
.s360-close-btn:hover { background: rgba(255,255,255,0.15); color: #fff; transform: scale(1.05); }

.s360-back-btn { display:inline-flex; align-items:center; gap:6px; margin-bottom:20px; color:#94a3b8; font-size:13px; font-weight:600; cursor:pointer; transition:0.2s; padding: 6px 12px 6px 0; border-radius: 6px; }
.s360-back-btn:hover { color:#fff; transform: translateX(-3px); }

.s360-tab { flex:1; text-align:center; padding:10px 0; font-size:13.5px; font-weight:600; color:#64748b; cursor:pointer; border-radius: 8px; transition:all 0.2s; }
.s360-tab:hover { color:#0f172a; }
.s360-tab.active { color:#0f172a; background:#f1f5f9; box-shadow: inset 0 2px 4px rgba(0,0,0,0.02); }

.s360-timeline-container { border-left:2px dashed #cbd5e1; margin-left:14px; padding-left:24px; position:relative; }
.s360-tl-item { position:relative; margin-bottom:24px; }
.s360-tl-item::before { content:''; position:absolute; left:-31px; top:4px; width:12px; height:12px; border-radius:50%; background:#fff; border:3px solid #3b82f6; box-shadow: 0 0 0 4px #f8fafc; transition: 0.3s; }
.s360-tl-item:hover::before { transform: scale(1.2); background: #3b82f6; }

.s360-tl-item.shift-start::before { border-color: #10b981; }
.s360-tl-item.shift-end::before { border-color: #ef4444; }

.s360-tl-time { font-size:11.5px; font-weight:700; color:#64748b; margin-bottom:6px; display:inline-block; background: #e2e8f0; padding: 2px 8px; border-radius: 12px; }
.s360-tl-card { background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:16px; box-shadow:0 2px 5px rgba(0,0,0,0.02); transition: 0.3s; position: relative; overflow: hidden; }
.s360-tl-card:hover { transform: translateY(-3px); box-shadow:0 8px 15px rgba(0,0,0,0.05); border-color: #cbd5e1; }

.hist-row { display:flex; justify-content:space-between; padding:16px; background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; margin-bottom: 12px; box-shadow: 0 2px 5px rgba(0,0,0,0.02); transition: 0.2s; }
.hist-row:hover { border-color: #cbd5e1; transform: translateY(-2px); box-shadow:0 6px 12px rgba(0,0,0,0.05); }
.hist-date { font-weight: 700; color: #1e293b; font-size: 14px; margin-bottom: 4px; }
.hist-stat { font-size: 12.5px; color: #64748b; font-weight: 500; }
</style>"""

content = target_regex.sub(new_drawer, content)

# Now fix the JS side for timeline generation
target_js = re.compile(r"// Today's Timeline.*?// History Table", re.DOTALL)

new_js = """// Today's Timeline
        let tlHtml = '';
        if (data.today_attendance && data.today_attendance.punch_in) {
            tlHtml += `
                <div class="s360-tl-item shift-start">
                    <div class="s360-tl-time"><i class="far fa-clock"></i> ${data.today_attendance.punch_in.split(' ')[1]}</div>
                    <div class="s360-tl-card" style="background: linear-gradient(to right, #ecfdf5, #fff); border-left:4px solid #10b981;">
                        <div style="font-weight:700; color:#047857; font-size: 14.5px;"><i class="fas fa-sign-in-alt"></i> Shift Started (Punched In)</div>
                    </div>
                </div>
            `;
        }
        
        if (data.today_visits && data.today_visits.length > 0) {
            data.today_visits.forEach(v => {
                tlHtml += `
                    <div class="s360-tl-item">
                        <div class="s360-tl-time"><i class="far fa-clock"></i> ${v.check_in_time || 'Unknown Time'}</div>
                        <div class="s360-tl-card" style="border-left:4px solid #3b82f6;">
                            <div style="font-weight:800; color:#0f172a; margin-bottom:6px; font-size: 15px;">${v.firm_name}</div>
                            <div style="display:flex; align-items:center; gap:12px; font-size:12.5px; color:#475569; margin-bottom:8px; font-weight:500;">
                                <span><i class="fas fa-user-circle" style="color:#94a3b8;"></i> ${v.person_name}</span>
                                <span><i class="fas fa-phone-alt" style="color:#94a3b8;"></i> ${v.mobile}</span>
                            </div>
                            ${v.verified_address ? `<div style="font-size:12px; margin-top:8px; color:#64748b; background:#f1f5f9; padding:8px; border-radius:6px; display:inline-block;"><i class="fas fa-map-marker-alt" style="color:#ef4444;"></i> ${v.verified_address}</div>` : ''}
                            ${v.audio_path ? `<div style="margin-top:12px; border-top:1px dashed #e2e8f0; padding-top:12px;"><div style="font-size:11px; font-weight:700; color:#94a3b8; margin-bottom:4px; text-transform:uppercase;">Voice Note</div><audio controls style="width:100%; height:30px; outline:none;"><source src="${v.audio_path}"></audio></div>` : ''}
                        </div>
                    </div>
                `;
            });
        }
        
        if (data.today_attendance && data.today_attendance.punch_out) {
            tlHtml += `
                <div class="s360-tl-item shift-end">
                    <div class="s360-tl-time"><i class="far fa-clock"></i> ${data.today_attendance.punch_out.split(' ')[1]}</div>
                    <div class="s360-tl-card" style="background: linear-gradient(to right, #fef2f2, #fff); border-left:4px solid #ef4444;">
                        <div style="font-weight:700; color:#b91c1c; font-size: 14.5px;"><i class="fas fa-sign-out-alt"></i> Shift Ended (Punched Out)</div>
                    </div>
                </div>
            `;
        }
        
        if (tlHtml === '') tlHtml = '<div style="text-align:center; padding:40px 0; color:#94a3b8;"><i class="fas fa-inbox" style="font-size:32px; margin-bottom:12px; opacity:0.5;"></i><div style="font-size:14px; font-weight:500;">No activity logged today.</div></div>';
        document.getElementById('s360-timeline').innerHTML = tlHtml;
        
        // History Table"""

content = target_js.sub(new_js, content)


target_hist = re.compile(r"// History Table.*?document\.getElementById\('s360-history-table'\)\.innerHTML = hHtml;", re.DOTALL)
new_hist = """// History Table
        let hHtml = '';
        if (data.history && data.history.length > 0) {
            data.history.forEach(h => {
                hHtml += `
                    <div class="hist-row">
                        <div>
                            <div class="hist-date"><i class="far fa-calendar-alt" style="color:#64748b; margin-right:6px;"></i> ${h.att_date}</div>
                            <div class="hist-stat" style="display:flex; gap:12px; margin-top:6px;">
                                <span><i class="fas fa-sign-in-alt" style="color:#10b981;"></i> ${h.punch_in ? h.punch_in.split(' ')[1] : '--:--'}</span>
                                <span><i class="fas fa-sign-out-alt" style="color:#ef4444;"></i> ${h.punch_out ? h.punch_out.split(' ')[1] : 'Active'}</span>
                            </div>
                        </div>
                        <div style="text-align:right;">
                            <div style="font-weight:800; color:#3b82f6; font-size:15px; margin-bottom:4px;">${h.duration || '0h 0m'}</div>
                            <div style="font-size:11.5px; font-weight:600; color:#94a3b8; background:#f1f5f9; padding:2px 8px; border-radius:10px;">${h.total_distance || '0.0'} km</div>
                        </div>
                    </div>
                `;
            });
        } else {
            hHtml = '<div style="text-align:center; padding:40px 0; color:#94a3b8;"><i class="fas fa-history" style="font-size:32px; margin-bottom:12px; opacity:0.5;"></i><div style="font-size:14px; font-weight:500;">No past attendance found.</div></div>';
        }
        document.getElementById('s360-history-table').innerHTML = hHtml;"""

content = target_hist.sub(new_hist, content)


# Change the drawer width to 480px, it was originally 450px and slide in animation
content = content.replace("document.getElementById('s360-drawer').style.right = '-500px';", "document.getElementById('s360-drawer').style.right = '-600px';")

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Upgraded 360 Drawer UI significantly")
