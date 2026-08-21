import sqlite3
import os
import re

db_path = r'c:\Users\pc\Downloads\client mgmt2\crm.db'
conn = sqlite3.connect(db_path)
cur = conn.cursor()
try:
    cur.execute("ALTER TABLE field_visits ADD COLUMN latitude TEXT")
    cur.execute("ALTER TABLE field_visits ADD COLUMN longitude TEXT")
except sqlite3.OperationalError:
    pass # columns might already exist
conn.commit()
conn.close()

# Update staff/add_visit.php
add_visit_path = r'c:\Users\pc\Downloads\client mgmt2\staff\add_visit.php'
with open(add_visit_path, 'r', encoding='utf-8') as f:
    av = f.read()

# Add hidden fields for lat/lon in the form
if 'name="longitude"' not in av:
    av = av.replace('<form', '<form') # just ensuring we find form
    # Find the submit button and insert hidden fields before it
    submit_str = '<button type="submit"'
    hidden_str = '<input type="hidden" name="latitude" id="lat"><input type="hidden" name="longitude" id="lon">\n                        <button type="submit"'
    av = av.replace(submit_str, hidden_str)

# Add geolocation script and Leaflet map to preview location
geo_script = """
    <!-- Leaflet JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const mapContainer = document.createElement('div');
            mapContainer.id = 'loc-map';
            mapContainer.style.height = '200px';
            mapContainer.style.borderRadius = '8px';
            mapContainer.style.marginTop = '15px';
            mapContainer.style.marginBottom = '15px';
            mapContainer.style.border = '1px solid #cbd5e1';
            
            // Insert map before submit button
            const form = document.querySelector('form');
            const submitBtn = document.querySelector('button[type="submit"]');
            submitBtn.parentNode.insertBefore(mapContainer, submitBtn);
            
            const latInp = document.getElementById('lat');
            const lonInp = document.getElementById('lon');
            
            let map = L.map('loc-map').setView([20.5937, 78.9629], 5); // Default India
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
            let marker;
            
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (pos) => {
                        const lat = pos.coords.latitude;
                        const lon = pos.coords.longitude;
                        latInp.value = lat;
                        lonInp.value = lon;
                        
                        map.setView([lat, lon], 15);
                        marker = L.marker([lat, lon]).addTo(map)
                            .bindPopup('Your Current Location').openPopup();
                            
                        const info = document.createElement('div');
                        info.innerHTML = '<small style="color:#10b981; font-weight:600;"><i data-lucide="check-circle"></i> Live GPS Location Captured</small>';
                        mapContainer.parentNode.insertBefore(info, mapContainer.nextSibling);
                        if(typeof lucide !== 'undefined') lucide.createIcons();
                    },
                    (err) => {
                        console.error(err);
                        const info = document.createElement('div');
                        info.innerHTML = '<small style="color:#ef4444; font-weight:600;"><i data-lucide="alert-circle"></i> Failed to capture GPS. Please enable Location permissions.</small>';
                        mapContainer.parentNode.insertBefore(info, mapContainer.nextSibling);
                        if(typeof lucide !== 'undefined') lucide.createIcons();
                    },
                    { enableHighAccuracy: true }
                );
            }
        });
    </script>
"""
if 'leaflet.js' not in av:
    av = av.replace('</script>\n</body>', geo_script + '\n</script>\n</body>')
    with open(add_visit_path, 'w', encoding='utf-8') as f:
        f.write(av)

# Update api.php (save_visit)
api_path = r'c:\Users\pc\Downloads\client mgmt2\api.php'
with open(api_path, 'r', encoding='utf-8') as f:
    api = f.read()

# Add latitude and longitude to save_visit case
old_api = """            $stmt = $db->prepare("INSERT INTO field_visits (visit_date, executive_name, person_name, mobile, alt_mobile, profession, custom_profession, firm_name, state, city, pincode, full_address, lead_quality, photo_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $vd, $ex, $pn, $mob, $alt, $prof, $cprof, $firm, $state, $city, $pin, $addr, $qual, $pp
            ]);"""
new_api = """            $lat = $_POST['latitude'] ?? null;
            $lon = $_POST['longitude'] ?? null;
            $stmt = $db->prepare("INSERT INTO field_visits (visit_date, executive_name, person_name, mobile, alt_mobile, profession, custom_profession, firm_name, state, city, pincode, full_address, lead_quality, photo_path, latitude, longitude) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $vd, $ex, $pn, $mob, $alt, $prof, $cprof, $firm, $state, $city, $pin, $addr, $qual, $pp, $lat, $lon
            ]);"""
if 'latitude, longitude' not in api:
    api = api.replace(old_api, new_api)
    with open(api_path, 'w', encoding='utf-8') as f:
        f.write(api)
        
# Update admin field_visits.php
admin_fv_path = r'c:\Users\pc\Downloads\client mgmt2\field_visits.php'
with open(admin_fv_path, 'r', encoding='utf-8') as f:
    afv = f.read()

# Add Leaflet map in the sidebar
afv_html_target = '<div class="fs-content">'
afv_html_replacement = """<div class="fs-content">
            <div id="sidebar-map" style="height: 200px; width: 100%; border-radius: 8px; margin-bottom: 16px; border: 1px solid #cbd5e1; display: none;"></div>"""
if 'sidebar-map' not in afv:
    afv = afv.replace(afv_html_target, afv_html_replacement)

# Include leaflet script
afv_script_target = '</style>'
afv_script_replacement = """</style>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    let sidebarMap = null;
    let sidebarMarker = null;
</script>"""
if 'leaflet.js' not in afv:
    afv = afv.replace(afv_script_target, afv_script_replacement)

# Update row HTML to pass lat/lon
old_btn = '<button class="btn-action" onclick="openSidebar(${row.id})">Follow-ups</button>'
new_btn = '<button class="btn-action" onclick="openSidebar(${row.id}, \'${row.latitude}\', \'${row.longitude}\')">Follow-ups / Location</button>'
afv = afv.replace(old_btn, new_btn)

# Update openSidebar function
old_open_sidebar = 'async function openSidebar(id) {'
new_open_sidebar = """async function openSidebar(id, lat, lon) {
            
            // Map handling
            const mapEl = document.getElementById('sidebar-map');
            if (lat && lon && lat !== 'null' && lon !== 'null') {
                mapEl.style.display = 'block';
                if (!sidebarMap) {
                    sidebarMap = L.map('sidebar-map');
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(sidebarMap);
                }
                const clat = parseFloat(lat);
                const clon = parseFloat(lon);
                sidebarMap.setView([clat, clon], 15);
                
                if (sidebarMarker) sidebarMap.removeLayer(sidebarMarker);
                sidebarMarker = L.marker([clat, clon]).addTo(sidebarMap)
                    .bindPopup('Executive Check-in Location').openPopup();
                
                // Fix map render issue inside modal
                setTimeout(() => sidebarMap.invalidateSize(), 300);
            } else {
                mapEl.style.display = 'none';
            }
"""
if 'sidebarMap.setView' not in afv:
    afv = afv.replace(old_open_sidebar, new_open_sidebar)
    with open(admin_fv_path, 'w', encoding='utf-8') as f:
        f.write(afv)

print("Location Tracking Feature integrated successfully!")
