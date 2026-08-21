import os

staff_idx_path = r'c:\Users\pc\Downloads\client mgmt2\staff\index.php'
with open(staff_idx_path, 'r', encoding='utf-8') as f:
    idx = f.read()

target = """            if (action === 'in') {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(async (pos) => {
                        await fetch('?api=x', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'api=punch_in' });
                        window.location.reload();
                    }, (err) => {
                        alert("GPS Permission is required to go On Duty.");
                        cb.checked = false;
                    });
                } else {
                    alert("GPS not supported.");
                    cb.checked = false;
                }
            } else {"""

repl = """            if (action === 'in') {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(async (pos) => {
                        await fetch('?api=x', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'api=punch_in' });
                        window.location.reload();
                    }, (err) => {
                        document.getElementById('gps-blocker').style.display = 'flex';
                        cb.checked = false;
                    }, { timeout: 5000 });
                } else {
                    alert("GPS not supported.");
                    cb.checked = false;
                }
            } else {"""

idx = idx.replace(target, repl)

with open(staff_idx_path, 'w', encoding='utf-8') as f:
    f.write(idx)
print("Updated toggleDuty alert to show detailed GPS Blocker")
