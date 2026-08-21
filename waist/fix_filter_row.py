import re

file_path = r'c:\Users\pc\Downloads\client mgmt2\pre_leads.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# Locate the filter bar block
filter_start = content.find('<div class="filter-bar">')
if filter_start == -1:
    filter_start = content.find('<div class="filter-bar"') # in case it has inline styles

# Locate the end of the filter bar block
filter_end = content.find('<!-- Data Table -->')

# Replace the entire filter bar HTML
new_filter_bar = """<!-- Filter Bar -->
    <div class="filter-bar" style="display: flex; flex-direction: row; align-items: center; justify-content: flex-start; flex-wrap: nowrap; gap: 16px; overflow-x: auto; padding: 12px 20px; width: 100%; box-sizing: border-box;">
        
        <div style="position:relative; flex: 1; min-width: 250px;">
            <i data-lucide="search" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); width:16px; color:#94a3b8;"></i>
            <input type="text" id="searchInput" class="filter-input" placeholder="Search Phone, Name, Email..." style="padding-left:36px; width:100%; box-sizing:border-box; margin:0;" oninput="debounceLoad()">
        </div>
        
        <select id="statusFilter" class="filter-input" style="width: 200px; flex-shrink: 0; margin:0;" onchange="loadData()">
            <option value="">All Statuses</option>
            <option value="Not Contacted">Not Contacted</option>
            <option value="Follow Up">Follow Up</option>
            <option value="Interested">Interested</option>
            <option value="Not Interested">Not Interested</option>
            <option value="Junk">Junk</option>
        </select>
        
        <select id="intentFilter" class="filter-input" style="width: 200px; flex-shrink: 0; margin:0;" onchange="loadData()">
            <option value="">All Intents</option>
            <option value="Loan">Loan</option>
            <option value="Insurance">Insurance</option>
            <option value="Credit Card">Credit Card</option>
        </select>
        
        <button class="btn-secondary" style="padding:10px 18px; flex-shrink: 0; white-space: nowrap; margin:0;" onclick="resetFilters()">
            <i data-lucide="refresh-cw" style="width:14px;"></i> Reset
        </button>
        
    </div>
    
    """

content = content[:filter_start] + new_filter_bar + content[filter_end:]

# Update the filter-input CSS just to be safe so it doesn't take 100% width by default
content = content.replace('.filter-input { padding: 10px 16px;', '.filter-input { padding: 10px 16px; display: inline-block;')

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Filter bar forced to single row.")
