import re

file_path = r'c:\Users\pc\Downloads\client mgmt2\search_track.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# Replace toggleFilterDrawer and add new JS functions
old_js = """    function toggleFilterDrawer() {
        document.getElementById('crm-filters-drawer').classList.toggle('active');
    }"""

new_js = """    // Advanced Filter JS
    function toggleFilterDrawer() {
        toggleAdvFilters();
    }
    
    function toggleAdvFilters() {
        const body = document.getElementById('advFilterBody');
        const arrow = document.getElementById('filterToggleArrow');
        body.classList.toggle('open');
        arrow.innerText = body.classList.contains('open') ? '▲ Collapse' : '▼ Expand';
    }

    function setQuickFilter(status, btn) {
        document.querySelectorAll('.qf-chip').forEach(c => c.classList.remove('active'));
        btn.classList.add('active');
        
        if (status === 'all') {
            document.getElementById('filter-status').value = '';
        } else {
            document.getElementById('filter-status').value = status;
        }
        triggerSearch();
    }

    function resetAllFilters() {
        document.getElementById('search-query').value = '';
        document.getElementById('filter-status').value = '';
        document.getElementById('filter-type').value = '';
        document.getElementById('filter-staff').value = '';
        document.getElementById('filter-bank').value = '';
        document.getElementById('filter-aging').value = '';
        document.getElementById('filter-sort').value = 'newest';
        document.getElementById('filter-date-from').value = '';
        document.getElementById('filter-date-to').value = '';
        document.getElementById('filter-amt-min').value = '';
        document.getElementById('filter-amt-max').value = '';
        
        document.querySelectorAll('.qf-chip').forEach(c => c.classList.remove('active'));
        document.querySelector('.qf-chip').classList.add('active'); // Set 'All' active
        
        triggerSearch();
    }

    function updateFilterChips() {
        const chips = [];
        const status = document.getElementById('filter-status').value;
        const type = document.getElementById('filter-type').value;
        const staff = document.getElementById('filter-staff').value;
        const bank = document.getElementById('filter-bank').value;
        const aging = document.getElementById('filter-aging').value;
        const dFrom = document.getElementById('filter-date-from').value;
        const dTo = document.getElementById('filter-date-to').value;
        const aMin = document.getElementById('filter-amt-min').value;
        const aMax = document.getElementById('filter-amt-max').value;
        
        if(status) chips.push(`Status: ${status}`);
        if(type) chips.push(`Type: ${type}`);
        if(staff) chips.push(`Staff: ${staff}`);
        if(bank) chips.push(`Bank: ${bank}`);
        if(aging) chips.push(`Aging: ${aging} days`);
        if(dFrom || dTo) chips.push(`Date: ${dFrom} to ${dTo}`);
        if(aMin || aMax) chips.push(`Amt: ${aMin} to ${aMax}`);
        
        const container = document.getElementById('activeFilterChips');
        const badge = document.getElementById('filterCountBadge');
        
        container.innerHTML = chips.map(c => `<span class="filter-chip">${c}</span>`).join('');
        
        if(chips.length > 0) {
            badge.style.display = 'inline-block';
            badge.innerText = `${chips.length} active`;
        } else {
            badge.style.display = 'none';
        }
    }"""
content = content.replace(old_js, new_js)


# Replace triggerSearch content
old_triggerSearch = """        const query = document.getElementById('search-query').value;
        const status = document.getElementById('filter-status').value;
        const type = document.getElementById('filter-type').value;
        
        const params = new URLSearchParams({
            api: 'search_applicants',
            query: query,
            status: status,
            loan_type: type,
            offset: currentOffset
        });"""

new_triggerSearch = """        updateFilterChips();
        
        const params = new URLSearchParams({
            api: 'search_applicants',
            query: document.getElementById('search-query').value,
            status: document.getElementById('filter-status').value,
            loan_type: document.getElementById('filter-type').value,
            staff: document.getElementById('filter-staff').value,
            bank: document.getElementById('filter-bank').value,
            aging: document.getElementById('filter-aging').value,
            sort: document.getElementById('filter-sort').value,
            date_from: document.getElementById('filter-date-from').value,
            date_to: document.getElementById('filter-date-to').value,
            amt_min: document.getElementById('filter-amt-min').value,
            amt_max: document.getElementById('filter-amt-max').value,
            offset: currentOffset
        });"""
content = content.replace(old_triggerSearch, new_triggerSearch)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("JS for filters updated in search_track.php")
