import os

route_path = r'c:\Users\pc\Downloads\client mgmt2\staff\my_route.php'
with open(route_path, 'r', encoding='utf-8') as f:
    route = f.read()

php_target = """<?php
require_once '../config.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
$username = $_SESSION['username'];
?>"""

php_repl = """<?php
require_once '../config.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
$username = $_SESSION['username'];
$user_id = $_SESSION['user_id'];

// Get staff's registered city
$stmt = $db->prepare("SELECT city FROM employees WHERE user_id = ?");
$stmt->execute([$user_id]);
$staff_city = $stmt->fetchColumn();
if (!$staff_city) $staff_city = 'India';
?>"""

route = route.replace(php_target, php_repl)

js_target = """        // Default to India, but much closer (City level zoom)
        const map = L.map('map').setView([20.5937, 78.9629], 5);
        
        // As soon as page loads, try to find the user's exact current city/street
        if (navigator.geolocation) {"""

js_repl = """        // Fetch staff city from PHP
        const staffCity = "<?php echo addslashes($staff_city); ?>";
        
        // Default to India
        const map = L.map('map').setView([20.5937, 78.9629], 5);
        
        // Auto-center map to the Staff's Registered City so it looks clean initially
        if (staffCity && staffCity.toLowerCase() !== 'india') {
            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(staffCity + ', India')}`)
                .then(r => r.json())
                .then(data => {
                    if (data && data.length > 0) {
                        // Fly to the registered city at a nice zoom level (12)
                        map.setView([data[0].lat, data[0].lon], 12);
                    }
                }).catch(e => console.log('Geocoding error:', e));
        }
        
        // As soon as page loads, try to find the user's exact current city/street
        if (navigator.geolocation) {"""

route = route.replace(js_target, js_repl)

with open(route_path, 'w', encoding='utf-8') as f:
    f.write(route)
print("Updated my_route.php with staff city geocoding")
