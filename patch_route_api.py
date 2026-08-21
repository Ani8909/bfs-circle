import os

route_path = r'c:\Users\pc\Downloads\client mgmt2\staff\my_route.php'
with open(route_path, 'r', encoding='utf-8') as f:
    route = f.read()

route = route.replace("fetch('../api.php?api=get_online_staff')", "fetch('../?api=get_online_staff')")

with open(route_path, 'w', encoding='utf-8') as f:
    f.write(route)
print("Fixed API call in my_route.php")
