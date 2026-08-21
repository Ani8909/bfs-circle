import os

staff_idx_path = r'c:\Users\pc\Downloads\client mgmt2\staff\index.php'
with open(staff_idx_path, 'r', encoding='utf-8') as f:
    idx = f.read()

target = """            <div class="metric-icon" style="z-index:2;"><i class="fas fa-wallet"></i></div>
            
            <!-- Background design element -->
            <i class="fas fa-coins" style="position:absolute; right:-10px; bottom:-10px; font-size:80px; opacity:0.05; z-index:1; transform:rotate(-15deg);"></i>
        </div>"""

repl = """            <div class="metric-icon" style="z-index:2;"><i class="fas fa-wallet"></i></div>
            
            <!-- Background design element -->
            <i class="fas fa-coins" style="position:absolute; right:-10px; bottom:-10px; font-size:80px; opacity:0.05; z-index:1; transform:rotate(-15deg);"></i>
        </div>

        <a href="my_route.php" style="display:block; text-decoration:none; background: linear-gradient(135deg, #1e293b, #0f172a); color: white; padding: 16px; border-radius: 16px; margin-top: -8px; margin-bottom: 24px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); position:relative; overflow:hidden;">
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

if "my_route.php" not in idx:
    idx = idx.replace(target, repl)
    with open(staff_idx_path, 'w', encoding='utf-8') as f:
        f.write(idx)
