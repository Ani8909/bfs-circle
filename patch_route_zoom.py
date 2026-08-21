import os

route_path = r'c:\Users\pc\Downloads\client mgmt2\staff\my_route.php'
with open(route_path, 'r', encoding='utf-8') as f:
    route = f.read()

target = """        const map = L.map('map').setView([20.5937, 78.9629], 5);"""
repl = """        // Default to India, but much closer (City level zoom)
        const map = L.map('map').setView([20.5937, 78.9629], 5);
        
        // As soon as page loads, try to find the user's exact current city/street
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(pos => {
                const lat = pos.coords.latitude;
                const lon = pos.coords.longitude;
                // Fly directly to the staff's current city/street with high zoom (15)
                map.flyTo([lat, lon], 15, {
                    animate: true,
                    duration: 1.5
                });
                
                // Add a pulse marker for their exact current location
                L.circleMarker([lat, lon], {
                    radius: 8,
                    fillColor: "#3b82f6",
                    color: "#ffffff",
                    weight: 3,
                    opacity: 1,
                    fillOpacity: 0.9
                }).addTo(map).bindPopup("You are here").openPopup();
            }, err => {
                console.log("Could not get initial location for zoom.");
            });
        }"""

route = route.replace(target, repl)

with open(route_path, 'w', encoding='utf-8') as f:
    f.write(route)
print("Updated my_route.php with auto-zoom to current city")
