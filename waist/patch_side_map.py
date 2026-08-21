import os
import re

file_path = r'c:\Users\pc\Downloads\client mgmt2\field_visits.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Add Leaflet CSS/JS
leaflet_includes = """    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <!-- Leaflet for Maps -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>"""

content = content.replace("    <!-- Lucide Icons -->\n    <script src=\"https://unpkg.com/lucide@latest\"></script>", leaflet_includes)

# 2. Add map initialization script
map_init_script = """    <script>
        let currentPage = 1;
        let totalPages = 1;
        let sidebarMap = null;
        let sidebarMarker = null;

        function initSidebarMap(lat, lng, title, subtitle) {
            const mapDiv = document.getElementById('sidebar-map');
            mapDiv.style.display = 'block';
            
            if (!sidebarMap) {
                sidebarMap = L.map('sidebar-map').setView([lat, lng], 15);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19
                }).addTo(sidebarMap);
            } else {
                sidebarMap.setView([lat, lng], 15);
            }
            
            if (sidebarMarker) {
                sidebarMap.removeLayer(sidebarMarker);
            }
            
            sidebarMarker = L.marker([lat, lng]).addTo(sidebarMap)
                .bindPopup(`<b>${title}</b><br>${subtitle}`).openPopup();
                
            // Invalidate size after map becomes visible
            setTimeout(() => sidebarMap.invalidateSize(), 300);
        }"""

content = content.replace("""    <script>
        let currentPage = 1;
        let totalPages = 1;""", map_init_script)

# 3. Update openSidebar to handle live tracking
target_open_sidebar = """                if (v.photo_path) {
                    const img = document.getElementById('fs_photo');
                    img.src = v.photo_path;
                    img.style.display = 'block';
                    document.getElementById('fs_photo_link').href = v.photo_path;
                } else {
                    document.getElementById('fs_photo').style.display = 'none';
                }"""

repl_open_sidebar = """                if (v.photo_path) {
                    const img = document.getElementById('fs_photo');
                    img.src = v.photo_path;
                    img.style.display = 'block';
                    document.getElementById('fs_photo_link').href = v.photo_path;
                } else {
                    document.getElementById('fs_photo').style.display = 'none';
                }
                
                // --- Live Tracking Engine ---
                document.getElementById('sidebar-map').style.display = 'none';
                try {
                    const onlineRes = await fetch('?api=get_online_staff');
                    const onlineData = await onlineRes.json();
                    
                    const staffTracking = onlineData.find(s => s.username === v.executive_name);
                    
                    if (staffTracking && staffTracking.lat) {
                        initSidebarMap(staffTracking.lat, staffTracking.lon, 
                            "Live Location (" + v.executive_name.split('@')[0] + ")", 
                            "Battery: " + (staffTracking.battery || '--') + "%<br>Status: " + staffTracking.status);
                    } else if (v.latitude && v.longitude) {
                        // Fallback to capture location if offline
                        initSidebarMap(v.latitude, v.longitude, 
                            "Location at Capture", 
                            "Staff is currently offline.<br>This is where the lead was generated.");
                    }
                } catch(e) { console.log('Map Error:', e); }
                // --- End Live Tracking ---"""

content = content.replace(target_open_sidebar, repl_open_sidebar)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)
print("Added Live Tracking to side-panel")
