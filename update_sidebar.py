import os

file_path = r'c:\Users\pc\Downloads\client mgmt2\field_visits.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# Add Smart Info Block in the UI
target_ui = """            <h3 style="font-size:15px;margin-bottom:16px;">Follow-up History</h3>"""
repl_ui = """            <div id="smart-visit-info" style="display:none; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:12px; margin-bottom:16px;"></div>
            
            <h3 style="font-size:15px;margin-bottom:16px;">Follow-up History</h3>"""
content = content.replace(target_ui, repl_ui)

# Add logic in openSidebar JS
target_js = """                // --- End Live Tracking ---
                
                let tlHtml = '';"""
                
repl_js = """                // --- End Live Tracking ---
                
                // --- Smart Visit Info ---
                const smartBlock = document.getElementById('smart-visit-info');
                let smartHtml = '<h4 style="font-size:13px; font-weight:600; color:#334155; margin-bottom:8px; display:flex; justify-content:space-between;"><span><i class="fas fa-microchip" style="color:#3b82f6;"></i> Smart Check-in Data</span></h4>';
                
                let hasSmartData = false;
                
                if (v.check_in_time) {
                    hasSmartData = true;
                    smartHtml += `<div style="font-size:12px; color:#475569; margin-bottom:4px;"><b>Check-in:</b> ${v.check_in_time}</div>`;
                }
                if (v.check_out_time) {
                    hasSmartData = true;
                    smartHtml += `<div style="font-size:12px; color:#475569; margin-bottom:4px;"><b>Check-out:</b> ${v.check_out_time}</div>`;
                }
                if (v.verified_address) {
                    hasSmartData = true;
                    smartHtml += `<div style="font-size:12px; color:#475569; margin-bottom:4px;"><b>GPS Address:</b> ${v.verified_address}</div>`;
                }
                if (v.audio_path) {
                    hasSmartData = true;
                    smartHtml += `<div style="margin-top:8px; font-size:12px; font-weight:600; color:#334155;">Voice Recording:</div>
                                  <audio controls style="width:100%; height:32px; margin-top:4px; outline:none;">
                                      <source src="${v.audio_path}" type="audio/webm">
                                      Your browser does not support the audio element.
                                  </audio>`;
                }
                
                if (v.remarks && v.remarks.includes('GPS Distance Alert')) {
                    hasSmartData = true;
                    smartHtml += `<div style="margin-top:8px; background:#fee2e2; color:#991b1b; padding:8px; border-radius:6px; font-size:11.5px; border:1px solid #fca5a5;"><i class="fas fa-exclamation-triangle"></i> <b>System Alert:</b> Distance Check Failed at Check-out.</div>`;
                }
                
                if (hasSmartData) {
                    smartBlock.innerHTML = smartHtml;
                    smartBlock.style.display = 'block';
                } else {
                    smartBlock.style.display = 'none';
                }
                
                // Manually trigger map size recalculation
                setTimeout(() => { if (sidebarMap) sidebarMap.invalidateSize(); }, 300);

                let tlHtml = '';"""

content = content.replace(target_js, repl_js)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Updated field_visits.php sidebar")
