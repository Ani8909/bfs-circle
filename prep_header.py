import os
import re

file_path = r'c:\Users\pc\Downloads\client mgmt2\header.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# Add base_path logic
if "$base_path" not in content:
    content = content.replace("<head>", "<?php $base_path = defined('IS_SUBFOLDER') ? '../' : ''; ?>\n<head>")

# Replace hrefs
content = re.sub(r'href="([a-zA-Z0-9_]+\.php)"', r'href="<?php echo $base_path; ?>\1"', content)

# Replace src="logo.png"
content = re.sub(r'src="logo\.png"', r'src="<?php echo $base_path; ?>logo.png"', content)

# Add link for "Client Vault" to sidebar
# I will find where `reminders.php` is and add the Client Vault menu right after it, or after applicants_list.php
target_menu = """<li <?php if($current_page == 'applicants_list.php') echo 'class="active"'; ?>>
                      <a href="<?php echo $base_path; ?>applicants_list.php"><i data-lucide="file-text"></i> Loan Applications</a>
                  </li>"""
                  
repl_menu = """<li <?php if($current_page == 'applicants_list.php') echo 'class="active"'; ?>>
                      <a href="<?php echo $base_path; ?>applicants_list.php"><i data-lucide="file-text"></i> Loan Applications</a>
                  </li>
                  <li <?php if($current_page == 'client_vault') echo 'class="active"'; ?>>
                      <a href="<?php echo $base_path; ?>client_vault/index.php"><i data-lucide="shield-check"></i> Client Vault</a>
                  </li>"""
                  
content = content.replace(target_menu, repl_menu)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Updated header.php for subfolder support and added Client Vault link.")
