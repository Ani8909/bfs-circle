import os

staff_idx_path = r'c:\Users\pc\Downloads\client mgmt2\staff\index.php'
with open(staff_idx_path, 'r', encoding='utf-8') as f:
    idx = f.read()

target_css = """        .company-logo-glow {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            z-index: 2;
            padding: 4px;
        }
        .company-logo-glow img {
            position: relative;
            z-index: 2;
            height: 38px;
            object-fit: contain;
            filter: drop-shadow(0 4px 6px rgba(0,0,0,0.1));
        }
        .company-logo-glow::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(255,255,255,0.6) 0%, rgba(255,255,255,0) 70%);
            z-index: 1;
            animation: softGlow 2.5s ease-in-out infinite alternate;
            border-radius: 50%;
        }
        @keyframes softGlow {
            0% { transform: scale(0.9); opacity: 0.4; }
            100% { transform: scale(1.6); opacity: 1; }
        }"""

repl_css = """        .company-logo-box {
            background: rgba(255, 255, 255, 1);
            padding: 6px 12px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(255, 107, 0, 0.4);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            z-index: 2;
            animation: floatLogo 4s ease-in-out infinite;
        }
        .company-logo-box img {
            height: 28px;
            object-fit: contain;
            position: relative;
            z-index: 2;
        }
        @keyframes floatLogo {
            0% { transform: translateY(0px); box-shadow: 0 8px 20px rgba(255, 107, 0, 0.4); }
            50% { transform: translateY(-4px); box-shadow: 0 12px 25px rgba(255, 107, 0, 0.2); }
            100% { transform: translateY(0px); box-shadow: 0 8px 20px rgba(255, 107, 0, 0.4); }
        }"""

idx = idx.replace(target_css, repl_css)

target_html = """            <div class="company-logo-glow">
                <img src="../logo.png" alt="BFS Logo" onerror="this.style.display='none'">
            </div>"""

repl_html = """            <div class="company-logo-box">
                <img src="../logo.png" alt="BFS Logo" onerror="this.style.display='none'">
            </div>"""

idx = idx.replace(target_html, repl_html)

with open(staff_idx_path, 'w', encoding='utf-8') as f:
    f.write(idx)
print("Updated logo styling to a clean white box")
