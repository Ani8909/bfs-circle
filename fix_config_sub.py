import re

file_path = r'c:\Users\pc\Downloads\client mgmt2\config.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# Let's fix the mangled block
mangled = r"\$is_in_client_vault.*?\$is_in_ca_dir;"
fixed = """$is_in_client_vault = (strpos($_SERVER['SCRIPT_NAME'], '/client_vault/') !== false);
$is_in_subfolder = $is_in_staff_dir || $is_in_agent_dir || $is_in_partner_dir || $is_in_builder_dir || $is_in_ca_dir || $is_in_client_vault || defined('IS_SUBFOLDER');"""

content = re.sub(mangled, fixed, content, flags=re.DOTALL)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Fixed config.php subfolder logic")
