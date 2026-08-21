import os
import re

staff_idx_path = r'c:\Users\pc\Downloads\client mgmt2\staff\index.php'
with open(staff_idx_path, 'r', encoding='utf-8') as f:
    idx = f.read()

# Fix CSS for header to make it wave/curved and soft
css_target = """        .app-header {
            background: var(--primary);
            color: white;
            padding: 24px 16px 60px 16px;
            border-bottom-left-radius: 24px;
            border-bottom-right-radius: 24px;
        }"""
css_repl = """        .app-header {
            background: linear-gradient(135deg, #FF8C20 0%, #E66A00 100%);
            color: white;
            padding: 24px 16px 70px 16px;
            border-bottom-left-radius: 35px;
            border-bottom-right-radius: 35px;
            box-shadow: 0 4px 15px rgba(230,106,0,0.3);
        }"""
idx = idx.replace(css_target, css_repl)

# Extract Timeline Anchor and remove from grid
timeline_block = """        <a href="my_route.php" style="display:block; text-decoration:none; background: linear-gradient(135deg, #1e293b, #0f172a); color: white; padding: 16px; border-radius: 16px; margin-top: -8px; margin-bottom: 24px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); position:relative; overflow:hidden;">
            <div style="display:flex; align-items:center; justify-content:space-between; z-index:2; position:relative;">
                <div style="display:flex; align-items:center; gap:12px;">
                    <div style="width:40px; height:40px; background:rgba(255,255,255,0.1); border-radius:10px; display:flex; align-items:center; justify-content:center;">
                        <i class="fas fa-route" style="font-size:20px; color:#4ade80;"></i>
                    </div>
                    <div>
                        <div style="font-weight:600; font-size:15px;">My Timeline & Route</div>
                        <div style="font-size:12px; color:#94a3b8; margin-top:2px;">Track your today's field journey</div>
                    </div>
                </div>
                <i class="fas fa-chevron-right" style="color:#94a3b8;"></i>
            </div>
            <i class="fas fa-map-marked-alt" style="position:absolute; right:-10px; bottom:-10px; font-size:70px; opacity:0.05; z-index:1; transform:rotate(-10deg);"></i>
        </a>"""

if timeline_block in idx:
    idx = idx.replace(timeline_block, "")
    
    # Re-insert Timeline Anchor outside of metrics-grid, right after the end of metrics-grid </div>
    target_grid_end = """        <div class="metric-card">
            <div class="metric-icon"><i class="fas fa-users"></i></div>
            <div class="metric-value" id="total_leads">0</div>
            <div class="metric-label">Generated Leads</div>
        </div>
    </div>"""
    
    repl_grid_end = """        <div class="metric-card">
            <div class="metric-icon"><i class="fas fa-users"></i></div>
            <div class="metric-value" id="total_leads">0</div>
            <div class="metric-label">Generated Leads</div>
        </div>
    </div>
    
    <div style="padding: 0 16px; margin-top: 16px;">
        <a href="my_route.php" style="display:block; text-decoration:none; background: linear-gradient(135deg, #1e293b, #0f172a); color: white; padding: 18px; border-radius: 20px; box-shadow: 0 8px 20px rgba(0,0,0,0.1); position:relative; overflow:hidden;">
            <div style="display:flex; align-items:center; justify-content:space-between; z-index:2; position:relative;">
                <div style="display:flex; align-items:center; gap:16px;">
                    <div style="width:48px; height:48px; background:rgba(255,255,255,0.1); border-radius:12px; display:flex; align-items:center; justify-content:center;">
                        <i class="fas fa-route" style="font-size:24px; color:#4ade80;"></i>
                    </div>
                    <div>
                        <div style="font-weight:700; font-size:16px; letter-spacing:0.3px;">My Timeline & Route</div>
                        <div style="font-size:13px; color:#94a3b8; margin-top:4px;">Track your today's field journey</div>
                    </div>
                </div>
                <div style="width:32px; height:32px; background:rgba(255,255,255,0.05); border-radius:50%; display:flex; align-items:center; justify-content:center;">
                    <i class="fas fa-chevron-right" style="color:#94a3b8; font-size:14px;"></i>
                </div>
            </div>
            <i class="fas fa-map-marked-alt" style="position:absolute; right:-15px; bottom:-15px; font-size:90px; opacity:0.04; z-index:1; transform:rotate(-15deg);"></i>
        </a>
    </div>"""
    
    idx = idx.replace(target_grid_end, repl_grid_end)

with open(staff_idx_path, 'w', encoding='utf-8') as f:
    f.write(idx)
print("Applied UI Beautification")
