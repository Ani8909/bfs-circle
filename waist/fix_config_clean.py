import os

file_path = r'c:\Users\pc\Downloads\client mgmt2\config.php'
with open(file_path, 'r', encoding='utf-8') as f:
    lines = f.readlines()

new_lines = []
skip = False
for line in lines:
    if "$is_in_client_vault = " in line:
        skip = True
    
    if skip:
        if "Authentication check for page load" in line:
            skip = False
            new_lines.append("$is_in_client_vault = (strpos($_SERVER['SCRIPT_NAME'], '/client_vault/') !== false);\n")
            new_lines.append("$is_in_subfolder = $is_in_staff_dir || $is_in_agent_dir || $is_in_partner_dir || $is_in_builder_dir || $is_in_ca_dir || $is_in_client_vault || defined('IS_SUBFOLDER');\n\n")
            new_lines.append("  // Authentication check for page load (excluding login.php)\n")
        continue
    
    # Also handle if $is_in_subfolder was modified incorrectly earlier
    if "$is_in_subfolder =" in line and not skip:
        continue # we add it above
        
    new_lines.append(line)

with open(file_path, 'w', encoding='utf-8') as f:
    f.writelines(new_lines)

print("Cleaned up config.php")
