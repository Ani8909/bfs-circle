import sys

# 1. Update api.php
with open('api.php', 'r', encoding='utf-8') as f:
    api = f.read()

staff_block = '''            } elseif ( === 'Pre-Lead') {
                 = ->prepare("SELECT id, name AS label, mobile AS sub FROM pre_leads WHERE name LIKE ? OR mobile LIKE ? LIMIT 10");
                ->execute([, ]);
                foreach (->fetchAll(PDO::FETCH_ASSOC) as ) {
                    [] = ['id' => ['id'], 'label' => ['label'], 'sub' => ['sub'], 'type' => 'Pre-Lead'];
                }
            } elseif ( === 'Staff') {
                 = ->prepare("SELECT user_id as id, full_name AS label, department AS sub FROM employees WHERE full_name LIKE ? OR official_email LIKE ? LIMIT 10");
                ->execute([, ]);
                foreach (->fetchAll(PDO::FETCH_ASSOC) as ) {
                    [] = ['id' => ['id'], 'label' => ['label'], 'sub' => ['sub'], 'type' => 'Staff'];
                }
            }'''
api = api.replace('''            } elseif ( === 'Pre-Lead') {
                 = ->prepare("SELECT id, name AS label, mobile AS sub FROM pre_leads WHERE name LIKE ? OR mobile LIKE ? LIMIT 10");
                ->execute([, ]);
                foreach (->fetchAll(PDO::FETCH_ASSOC) as ) {
                    [] = ['id' => ['id'], 'label' => ['label'], 'sub' => ['sub'], 'type' => 'Pre-Lead'];
                }
            }''', staff_block)

# Fix API save_reminder
api = api.replace("LEFT JOIN applicants a ON", "LEFT JOIN employees emp ON (r.reference_type = 'Staff') AND emp.user_id = COALESCE(NULLIF(r.reference_id, ''), r.lead_id)\n                LEFT JOIN applicants a ON")
api = api.replace("COALESCE(a.customer_name, ref.full_name, b.full_name) as fetched_name", "COALESCE(a.customer_name, ref.full_name, b.full_name, emp.full_name) as fetched_name")
api = api.replace("COALESCE(a.mobile, ref.mobile, b.contact_number) as fetched_mobile", "COALESCE(a.mobile, ref.mobile, b.contact_number, emp.official_email) as fetched_mobile")

with open('api.php', 'w', encoding='utf-8') as f:
    f.write(api)

# 2. Update reminders.php
with open('reminders.php', 'r', encoding='utf-8') as f:
    rem = f.read()

# Add Staff to JS map
rem = rem.replace("'Pre-Lead': 'pre_leads.php?edit_prelead='", "'Pre-Lead': 'pre_leads.php?edit_prelead=', 'Staff': 'view_employee.php?id='")

# Add Staff to dropdown
rem = rem.replace('<option value="Pre-Lead">Pre-Lead</option>', '<option value="Pre-Lead">Pre-Lead</option>\n                          <option value="Staff">Staff / Employee</option>')

with open('reminders.php', 'w', encoding='utf-8') as f:
    f.write(rem)

print('Success')
