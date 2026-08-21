import os

staff_idx_path = r'c:\Users\pc\Downloads\client mgmt2\staff\index.php'
with open(staff_idx_path, 'r', encoding='utf-8') as f:
    idx = f.read()

target_css = """        .company-logo-box {
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

repl_css = """        .company-logo-box {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            z-index: 2;
            padding-right: 8px;
            animation: floatLogo 4s ease-in-out infinite;
        }
        .company-logo-box img {
            height: 34px;
            object-fit: contain;
            position: relative;
            z-index: 2;
            /* Turn any colored logo into pure white */
            filter: brightness(0) invert(1) drop-shadow(0 2px 4px rgba(0,0,0,0.15));
        }
        @keyframes floatLogo {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-3px); }
            100% { transform: translateY(0px); }
        }"""

idx = idx.replace(target_css, repl_css)

with open(staff_idx_path, 'w', encoding='utf-8') as f:
    f.write(idx)
print("Turned logo completely white with no background")
