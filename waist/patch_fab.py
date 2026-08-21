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
        
    # Replace CSS
    css_target = """        .fab {
            position: absolute;
            top: -24px;
            left: 50%;
            transform: translateX(-50%);
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            box-shadow: 0 4px 10px rgba(255, 122, 0, 0.4);
            text-decoration: none;
        }"""
    
    css_repl = """        .fab {
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
        
    # HTML Target
    html_target = """        <div style="width: 60px; position: relative;">
            <a href="add_visit.php" class="fab">
                <i class="fas fa-plus"></i>
            </a>
        </div>"""
        
    html_repl = """        <div style="width: 140px; position: relative;">
            <a href="add_visit.php" class="fab">
                <i class="fas fa-plus"></i> <span>Start Visit</span>
            </a>
        </div>"""
        
    content = content.replace(css_target, css_repl)
    content = content.replace(html_target, html_repl)
    
    with open(file_path, 'w', encoding='utf-8') as f:
        f.write(content)

print("Updated FAB in all files")
