with open("applicants_list.php", "r", encoding="utf-8") as f:
    c = f.read()

c = c.replace('<td class="search-field"><strong></strong></td>', '<td class="search-field"><strong style="color:var(--text-primary); font-family:\'Outfit\';">${app.loan_id}</strong></td>')

with open("applicants_list.php", "w", encoding="utf-8") as f:
    f.write(c)
print("Fixed ID")
