import sys

with open('reminders.php', 'r', encoding='utf-8') as f:
    rem = f.read()

new_init = '''// Init
document.addEventListener('DOMContentLoaded', () => {
    loadReminders();
    setInterval(loadReminders, 120000); // Auto-refresh every 2 min
    
    // Auto-open modal if URL param
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('new')) {
        let prefill = {};
        if (urlParams.get('type')) prefill.reference_type = urlParams.get('type');
        if (urlParams.get('id')) prefill.id = urlParams.get('id');
        if (urlParams.get('name')) prefill.label = urlParams.get('name');
        openAddModal(prefill);
    }
});'''

rem = rem.replace('''// Init
document.addEventListener('DOMContentLoaded', () => {
    loadReminders();
    setInterval(loadReminders, 120000); // Auto-refresh every 2 min
});''', new_init)

with open('reminders.php', 'w', encoding='utf-8') as f:
    f.write(rem)

print('Success')
