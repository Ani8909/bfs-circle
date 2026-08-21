import os

file_path = r'c:\Users\pc\Downloads\client mgmt2\field_visits.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

target = """        let currentPage = 1;
        let totalPages = 1;
        let sidebarMap = null;
        let sidebarMarker = null;"""

repl = """        let currentPage = 1;
        let totalPages = 1;"""

content = content.replace(target, repl)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)
print("Removed duplicate map variable declarations")
