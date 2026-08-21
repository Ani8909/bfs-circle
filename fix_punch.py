import os

file_path = r'c:\Users\pc\Downloads\client mgmt2\staff\index.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

content = content.replace("fetch('?api=x', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'api=punch_in' });", 
                          "fetch('?api=punch_in', { method:'POST' });")
content = content.replace("fetch('?api=x', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'api=punch_out' });", 
                          "fetch('?api=punch_out', { method:'POST' });")

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Fixed punch_in and punch_out fetch URLs in index.php")
