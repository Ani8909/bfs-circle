import os
import re

file_path = r'c:\Users\pc\Downloads\client mgmt2\header.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

target = """            <li class="menu-item <?php echo ($active_page === 'applicants_list.php') ? 'active' : ''; ?>">
                <a href="<?php echo $base_path; ?>applicants_list.php">
                    <i data-lucide="files"></i>
                    <span class="menu-text">Loan Applications</span>
                </a>
            </li>"""

repl = """            <li class="menu-item <?php echo ($active_page === 'applicants_list.php') ? 'active' : ''; ?>">
                <a href="<?php echo $base_path; ?>applicants_list.php">
                    <i data-lucide="files"></i>
                    <span class="menu-text">Loan Applications</span>
                </a>
            </li>
            
            <li class="menu-item <?php echo ($active_page === 'client_vault') ? 'active' : ''; ?>">
                <a href="<?php echo $base_path; ?>client_vault/index.php">
                    <i data-lucide="shield-check"></i>
                    <span class="menu-text">Client Vault</span>
                </a>
            </li>"""

content = content.replace(target, repl)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Added Client Vault to menu.")
