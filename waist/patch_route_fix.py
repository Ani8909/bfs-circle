import os

route_path = r'c:\Users\pc\Downloads\client mgmt2\staff\my_route.php'
with open(route_path, 'r', encoding='utf-8') as f:
    route = f.read()

php_target = """// Get staff's registered city
$stmt = $db->prepare("SELECT city FROM employees WHERE user_id = ?");
$stmt->execute([$user_id]);
$staff_city = $stmt->fetchColumn();"""

php_repl = """// Get staff's registered address to zoom into their approximate area
$stmt = $db->prepare("SELECT current_address FROM employees WHERE user_id = ?");
$stmt->execute([$user_id]);
$staff_city = $stmt->fetchColumn();"""

route = route.replace(php_target, php_repl)

with open(route_path, 'w', encoding='utf-8') as f:
    f.write(route)
print("Fixed PDO error by querying current_address instead of city")
