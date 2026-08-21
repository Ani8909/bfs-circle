import sys, re
with open("applicants_list.php", "r", encoding="utf-8") as f:
    c = f.read()

old_func_match = re.search(r'function getStatusBadge\(status\) \{.*?(?=function formatAmt)', c, re.DOTALL)
if old_func_match:
    new_badge_func = """function getStatusBadge(status) {
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
                      
        return `<span class="badge" style="background:#fff; color:#0f172a; border:1px solid #cbd5e1; font-weight:600; padding:4px 8px; border-radius:6px;"><i data-lucide="${icon}" style="width:12px; height:12px;"></i> ${title}</span>`;
    }
    
    """
    c = c[:old_func_match.start()] + new_badge_func + c[old_func_match.end():]

with open("applicants_list.php", "w", encoding="utf-8") as f:
    f.write(c)
