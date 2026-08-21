import os

# 1. Update admin field_visits.php to show the Verified Address
admin_fv_path = r'c:\Users\pc\Downloads\client mgmt2\field_visits.php'
with open(admin_fv_path, 'r', encoding='utf-8') as f:
    afv = f.read()

# Make openSidebar show verified address
old_btn = '<button class="btn-action" onclick="openSidebar(${row.id}, \'${row.latitude}\', \'${row.longitude}\')">Follow-ups / Location</button>'
new_btn = '<button class="btn-action" onclick="openSidebar(${row.id}, \'${row.latitude}\', \'${row.longitude}\', \'${row.verified_address}\')">Follow-ups / Location</button>'
afv = afv.replace(old_btn, new_btn)

# Update the openSidebar JS function definition
old_func = 'async function openSidebar(id, lat, lon) {'
new_func = """async function openSidebar(id, lat, lon, v_addr) {
            
            // Map handling
            const mapEl = document.getElementById('sidebar-map');
            
            // Add Verified Address Text
            let vAddrText = document.getElementById('v-addr-text');
            if(!vAddrText) {
                vAddrText = document.createElement('div');
                vAddrText.id = 'v-addr-text';
                vAddrText.style.padding = '10px';
                vAddrText.style.background = '#f0fdf4';
                vAddrText.style.border = '1px solid #bbf7d0';
                vAddrText.style.borderRadius = '8px';
                vAddrText.style.marginBottom = '15px';
                vAddrText.style.fontSize = '12px';
                vAddrText.style.color = '#166534';
                mapEl.parentNode.insertBefore(vAddrText, mapEl);
            }
            
            if (lat && lon && lat !== 'null' && lon !== 'null') {
                const cleanAddr = (v_addr && v_addr !== 'null' && v_addr !== 'undefined') ? v_addr : 'Address not decoded';
                vAddrText.innerHTML = '<div style="font-weight:700; margin-bottom:4px;"><i data-lucide="check-circle" style="width:14px;"></i> Verified Check-in Location:</div>' + cleanAddr;
                vAddrText.style.display = 'block';
                if(typeof lucide !== 'undefined') lucide.createIcons();
                
                mapEl.style.display = 'block';
                if (!sidebarMap) {
                    sidebarMap = L.map('sidebar-map');
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(sidebarMap);
                }
                const clat = parseFloat(lat);
                const clon = parseFloat(lon);
                sidebarMap.setView([clat, clon], 16);
                
                if (sidebarMarker) sidebarMap.removeLayer(sidebarMarker);
                sidebarMarker = L.marker([clat, clon]).addTo(sidebarMap)
                    .bindPopup(`<strong style="color:var(--primary);">Checked-in Address:</strong><br>${cleanAddr}`).openPopup();
                
                // Fix map render issue inside modal
                setTimeout(() => sidebarMap.invalidateSize(), 300);
            } else {
                mapEl.style.display = 'none';
                vAddrText.style.display = 'none';
            }
"""
if 'let vAddrText =' not in afv:
    afv = afv.replace(old_func, new_func)
    with open(admin_fv_path, 'w', encoding='utf-8') as f:
        f.write(afv)

# 2. Update staff/visits.php to display it as well
staff_v_path = r'c:\Users\pc\Downloads\client mgmt2\staff\visits.php'
with open(staff_v_path, 'r', encoding='utf-8') as f:
    sv = f.read()

target_html = """                            <div style="font-size:13px; color:#475569; margin-bottom:12px; line-height:1.5;">
                                <i class="fas fa-map-marker-alt" style="color:#94a3b8; width:16px;"></i> ${v.city}, ${v.state}
                                <br>
                                <i class="fas fa-phone-alt" style="color:#94a3b8; width:16px;"></i> ${v.mobile}
                            </div>"""
repl_html = """                            <div style="font-size:13px; color:#475569; margin-bottom:12px; line-height:1.5;">
                                <i class="fas fa-map-marker-alt" style="color:#94a3b8; width:16px;"></i> ${v.city}, ${v.state}
                                <br>
                                <i class="fas fa-phone-alt" style="color:#94a3b8; width:16px;"></i> ${v.mobile}
                            </div>
                            ${(v.verified_address && v.verified_address !== 'null') ? `
                            <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:8px; padding:10px; font-size:11.5px; color:#166534; margin-bottom:12px; line-height:1.4;">
                                <strong style="display:block; margin-bottom:4px;"><i class="fas fa-check-circle"></i> Live Location Captured</strong>
                                ${v.verified_address}
                            </div>` : ''}"""

if '<i class="fas fa-check-circle"></i> Live Location Captured' not in sv:
    sv = sv.replace(target_html, repl_html)
    with open(staff_v_path, 'w', encoding='utf-8') as f:
        f.write(sv)

print("Display patched successfully!")
