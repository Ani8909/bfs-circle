import os
import glob

# Files to patch
files = glob.glob(r'c:\Users\pc\Downloads\client mgmt2\staff\*.php')
target_files = ['index.php', 'add_visit.php', 'visits.php', 'files.php', 'profile.php']

for file_path in files:
    if os.path.basename(file_path) not in target_files:
        continue
        
    with open(file_path, 'r', encoding='utf-8') as f:
        content = f.read()
        
    # Replace CSS to make it smaller and elegant
    css_target = """        .fab {
            position: absolute;
            top: -24px;
            left: 50%;
            transform: translateX(-50%);
            height: 56px;
            padding: 0 20px;
            border-radius: 28px;
            background: linear-gradient(135deg, #FF6B00 0%, #FF9A44 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            font-weight: 700;
            box-shadow: 0 8px 20px rgba(255, 107, 0, 0.4);
            text-decoration: none;
            white-space: nowrap;
            gap: 8px;
        }"""
    
    css_repl = """        .fab {
            position: absolute;
            top: -20px;
            left: 50%;
            transform: translateX(-50%);
            height: 44px;
            padding: 0 16px;
            border-radius: 22px;
            background: linear-gradient(135deg, #FF6B00 0%, #FF9A44 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 600;
            box-shadow: 0 6px 15px rgba(255, 107, 0, 0.35);
            text-decoration: none;
            white-space: nowrap;
            gap: 6px;
        }"""
        
    html_target = """        <div style="width: 140px; position: relative;">"""
    html_repl = """        <div style="width: 110px; position: relative;">"""
        
    content = content.replace(css_target, css_repl)
    content = content.replace(html_target, html_repl)
    
    with open(file_path, 'w', encoding='utf-8') as f:
        f.write(content)

print("Reduced FAB size in all files")
