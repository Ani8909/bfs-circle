import os

api_path = r'c:\Users\pc\Downloads\client mgmt2\api.php'
with open(api_path, 'r', encoding='utf-8') as f:
    content = f.read()

target = """        case 'get_staff_360':
            $username = trim($_GET['username'] ?? '');"""

repl = """        case 'get_staff_360':
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
            $username = trim($_GET['username'] ?? '');"""

content = content.replace(target, repl)

with open(api_path, 'w', encoding='utf-8') as f:
    f.write(content)
