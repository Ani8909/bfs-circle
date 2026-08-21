import os

api_path = r'c:\Users\pc\Downloads\client mgmt2\api.php'
with open(api_path, 'r', encoding='utf-8') as f:
    content = f.read()

target = """            // Today's Field Visits
            $visit_stmt = $db->prepare("SELECT firm_name, person_name, mobile, check_in_time, check_out_time, verified_address, audio_path, remarks FROM field_visits WHERE executive_name = ? AND visit_date = ? ORDER BY id ASC");"""

repl = """            // Today's Field Visits
            $visit_stmt = $db->prepare("SELECT firm_name, person_name, mobile, check_in_time, check_out_time, verified_address, audio_path FROM field_visits WHERE executive_name = ? AND visit_date = ? ORDER BY id ASC");"""

content = content.replace(target, repl)

with open(api_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Fixed api.php query for field_visits")
