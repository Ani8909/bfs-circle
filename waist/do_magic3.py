import sys, re
with open('applicants_list.php', 'r', encoding='utf-8') as f:
    c = f.read()

c = c.replace('<th>Loan ID</th>', '<th>Applicant ID</th>')
c = c.replace('<th>Added By</th>', '<th>Added By</th>\n                        <th>Date Added</th>')
c = re.sub(r'<td class="search-field"><strong[^>]+>\$\{app\.loan_id\}</strong></td>', r'<td class="search-field"><strong></strong></td>', c)

# Fix completion colors
c = re.sub(r'color: #ef4444', r'color: #64748b', c)
c = re.sub(r'color: #10b981', r'color: #0f172a', c)

# Add Date column in rows
row_added = r'<td style="color:var(--text-muted); font-size:12px;"></td>'
new_cols = r'<td style="color:var(--text-muted); font-size:12px;"></td>\n                            <td style="color:var(--text-muted); font-size:12px;"></td>'
c = c.replace(row_added, new_cols)

# Replace getStatusBadge entirely
old_func_match = re.search(r'function getStatusBadge\(status\) \{.*?(?=function formatAmt)', c, re.DOTALL)
if old_func_match:
    new_badge_func = '''function getStatusBadge(status) {
        let icon = '';
        if(status === 'Phase 1') icon = 'user-check';
        else if(status === 'Phase 2') icon = 'file-text';
        else if(status === 'Phase 3') icon = 'coins';
        else if(status === 'Phase 4') icon = 'landmark';
        else if(status === 'Completed') icon = 'check-circle';
        else if(status === 'Rejected') icon = 'x-circle';
        
        const title = status === 'Phase 1' ? 'Phase 1 (KYC)' : 
                      status === 'Phase 2' ? 'Phase 2 (Docs)' : 
                      status === 'Phase 3' ? 'Phase 3 (Disburse)' : 
                      status === 'Phase 4' ? 'Phase 4 (Bank)' : status;
                      
        return <span class="badge" style="background:#fff; color:#0f172a; border:1px solid #cbd5e1; font-weight:600; padding:4px 8px; border-radius:6px;"><i data-lucide="" style="width:12px; height:12px;"></i> </span>;
    }
    
    '''
    c = c[:old_func_match.start()] + new_badge_func + c[old_func_match.end():]

# Also let's fix colspan from 8 to 9
c = c.replace('colspan="8"', 'colspan="9"')

with open('applicants_list.php', 'w', encoding='utf-8') as f:
    f.write(c)

print('Success')
