import os

staff_idx_path = r'c:\Users\pc\Downloads\client mgmt2\staff\index.php'
with open(staff_idx_path, 'r', encoding='utf-8') as f:
    idx = f.read()

# 1. Update App Header CSS
target_css1 = """        /* App Header */
        .app-header {
            background: var(--primary);
            color: white;
            padding: 20px 16px;
            border-bottom-left-radius: 20px;
            border-bottom-right-radius: 20px;
            box-shadow: 0 4px 10px rgba(255, 122, 0, 0.2);
        }"""
repl_css1 = """        /* App Header */
        .app-header {
            background: linear-gradient(135deg, #FF6B00 0%, #FF9A44 100%);
            color: white;
            padding: 24px 16px 20px 16px;
            border-bottom-left-radius: 32px;
            border-bottom-right-radius: 32px;
            box-shadow: 0 10px 30px rgba(255, 107, 0, 0.3);
            position: relative;
            overflow: hidden;
        }
        .app-header::after {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 150px;
            height: 150px;
            background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0) 70%);
            border-radius: 50%;
        }"""
idx = idx.replace(target_css1, repl_css1)

# 2. Update Duty Container
target_css2 = """        /* Sleek Duty Toggle */
        .duty-container {
            background: rgba(0, 0, 0, 0.15);
            backdrop-filter: blur(10px);
            border-radius: 100px;
            padding: 8px 12px;
            display: inline-flex;
            align-items: center;
            gap: 12px;
            margin-top: 16px;
            border: 1px solid rgba(255,255,255,0.2);
        }"""
repl_css2 = """        /* Sleek Duty Toggle */
        .duty-container {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-radius: 100px;
            padding: 6px 12px;
            display: inline-flex;
            align-items: center;
            gap: 12px;
            margin-top: 10px;
            border: 1px solid rgba(255,255,255,0.3);
            box-shadow: inset 0 2px 4px rgba(255,255,255,0.1);
            position: relative;
            z-index: 2;
        }"""
idx = idx.replace(target_css2, repl_css2)

# 3. Update Metric Card Shadows and look
target_css3 = """        .metric-card {
            background: white;
            border-radius: 16px;
            padding: 16px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            text-align: center;
        }
        
        .metric-card.full-width {
            grid-column: 1 / -1;
            display: flex;
            align-items: center;
            justify-content: space-between;
            text-align: left;
            background: linear-gradient(135deg, #1E293B 0%, #0F172A 100%);
            color: white;
        }"""
repl_css3 = """        .metric-card {
            background: white;
            border-radius: 24px;
            padding: 20px;
            box-shadow: 0 12px 24px rgba(0,0,0,0.04);
            text-align: center;
            border: 1px solid rgba(0,0,0,0.02);
        }
        
        .metric-card.full-width {
            grid-column: 1 / -1;
            display: flex;
            align-items: center;
            justify-content: space-between;
            text-align: left;
            background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%);
            color: white;
            border-radius: 24px;
            box-shadow: 0 16px 32px rgba(15, 23, 42, 0.2);
            border: 1px solid rgba(255,255,255,0.1);
        }"""
idx = idx.replace(target_css3, repl_css3)

# 4. Update Tool Cards
target_css4 = """        .tool-card {
            min-width: 130px;
            background: white;
            border-radius: 16px;
            padding: 16px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.04);
            text-decoration: none;
            display: flex;
            flex-direction: column;
            gap: 12px;
            border: 1px solid #e2e8f0;
        }"""
repl_css4 = """        .tool-card {
            min-width: 130px;
            background: white;
            border-radius: 20px;
            padding: 18px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.03);
            text-decoration: none;
            display: flex;
            flex-direction: column;
            gap: 12px;
            border: 1px solid #f1f5f9;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .tool-card:active {
            transform: scale(0.96);
            box-shadow: 0 4px 8px rgba(0,0,0,0.02);
        }"""
idx = idx.replace(target_css4, repl_css4)


# 5. Fix Timeline Banner styling
target_html1 = """style="display:block; text-decoration:none; background: linear-gradient(135deg, 
#1e293b, #0f172a); color: white; padding: 18px; border-radius: 20px; box-shadow: 0 8px 20px rgba(0,0,0,0.1); 
position:relative; overflow:hidden;" """

repl_html1 = """style="display:block; text-decoration:none; background: linear-gradient(135deg, #1e293b 0%, #334155 100%); color: white; padding: 20px; border-radius: 24px; box-shadow: 0 12px 24px rgba(15, 23, 42, 0.15); position:relative; overflow:hidden; border: 1px solid rgba(255,255,255,0.05);" """

idx = idx.replace(target_html1.replace('\n', ''), repl_html1.replace('\n', ''))

# 6. Leaderboard Banner styling
target_html2 = """background:linear-gradient(135deg, #F59E0B, #D97706); border-radius:16px; padding:16px 20px; color:white; text-decoration:none; box-shadow:0 8px 15px rgba(245, 158, 11, 0.2);"""
repl_html2 = """background:linear-gradient(135deg, #FFB75E 0%, #ED8F03 100%); border-radius:24px; padding:20px; color:white; text-decoration:none; box-shadow:0 12px 24px rgba(237, 143, 3, 0.3); border:1px solid rgba(255,255,255,0.2);"""

idx = idx.replace(target_html2, repl_html2)

# Make sure tools-scroll padding is large enough
idx = idx.replace("padding: 4px 16px 20px 16px;", "padding: 8px 16px 24px 16px;")
idx = idx.replace("margin-top: -30px;", "margin-top: -35px;")


with open(staff_idx_path, 'w', encoding='utf-8') as f:
    f.write(idx)
print("Applied modern UI CSS")
