with open("applicants_list.php", "r", encoding="utf-8") as f:
    c = f.read()

c = c.replace("<td style=\"color:var(--text-muted); font-size:12px;\">${app.added_by || 'System'}</td>", "<td style=\"color:var(--text-muted); font-size:12px;\">${app.added_by || 'System'}</td>\n                            <td style=\"color:var(--text-muted); font-size:12px;\">${app.created_at ? app.created_at.split(' ')[0] : '-'}</td>")

with open("applicants_list.php", "w", encoding="utf-8") as f:
    f.write(c)
print("Fixed columns")
