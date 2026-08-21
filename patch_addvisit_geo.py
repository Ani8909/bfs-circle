import os

add_visit_path = r'c:\Users\pc\Downloads\client mgmt2\staff\add_visit.php'
with open(add_visit_path, 'r', encoding='utf-8') as f:
    av = f.read()

# Replace the hidden fields and add verified_address
old_hidden = '<input type="hidden" name="latitude" id="lat"><input type="hidden" name="longitude" id="lon">'
new_hidden = '<input type="hidden" name="latitude" id="lat"><input type="hidden" name="longitude" id="lon"><input type="hidden" name="verified_address" id="v_addr">'
if old_hidden in av:
    av = av.replace(old_hidden, new_hidden)

# Replace the geolocation block
old_geo = """if (navigator.geolocation) {
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
            }"""

new_geo = """if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    async (pos) => {
                        const lat = pos.coords.latitude;
                        const lon = pos.coords.longitude;
                        latInp.value = lat;
                        lonInp.value = lon;
                        
                        map.setView([lat, lon], 15);
                        marker = L.marker([lat, lon]).addTo(map);
                            
                        const info = document.createElement('div');
                        info.style.padding = '12px';
                        info.style.background = '#f0fdf4';
                        info.style.border = '1px solid #bbf7d0';
                        info.style.borderRadius = '8px';
                        info.style.marginBottom = '20px';
                        info.innerHTML = '<div style="color:#15803d; font-weight:600; margin-bottom:4px; display:flex; align-items:center; gap:6px;"><i data-lucide="map-pin" style="width:16px;"></i> Live Location Verified</div><div id="live-address-txt" style="font-size:12px; color:#166534;">Fetching exact address...</div>';
                        mapContainer.parentNode.insertBefore(info, mapContainer.nextSibling);
                        if(typeof lucide !== 'undefined') lucide.createIcons();
                        
                        // 10x Feature: Reverse Geocoding via Nominatim
                        try {
                            const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}`);
                            const data = await res.json();
                            const addr = data.display_name || 'Address found';
                            document.getElementById('v_addr').value = addr;
                            document.getElementById('live-address-txt').innerText = addr;
                            marker.bindPopup(`<strong style="color:var(--primary);">Verified Address:</strong><br>${addr}`).openPopup();
                        } catch(e) {
                            document.getElementById('live-address-txt').innerText = 'Location captured, but address decoding failed.';
                            marker.bindPopup('Location Captured').openPopup();
                        }
                    },
                    (err) => {
                        console.error(err);
                        const info = document.createElement('div');
                        info.innerHTML = '<div style="padding:12px; background:#fef2f2; border:1px solid #fecaca; border-radius:8px; margin-bottom:20px; color:#b91c1c; font-weight:600; font-size:13px; display:flex; align-items:center; gap:6px;"><i data-lucide="alert-circle" style="width:16px;"></i> Error: Location access denied. Please enable GPS permissions to save verified visits.</div>';
                        mapContainer.parentNode.insertBefore(info, mapContainer.nextSibling);
                        if(typeof lucide !== 'undefined') lucide.createIcons();
                    },
                    { enableHighAccuracy: true }
                );
            }"""

if old_geo in av:
    av = av.replace(old_geo, new_geo)

with open(add_visit_path, 'w', encoding='utf-8') as f:
    f.write(av)
print("staff/add_visit.php updated successfully!")
