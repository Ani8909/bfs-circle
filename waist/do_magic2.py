import sys

# 1. Update api.php safely
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

old_query = '''             = ->prepare("SELECT * FROM reminders WHERE  ORDER BY 
                CASE WHEN remind_at < '' THEN 0 ELSE 1 END ASC,
                CASE priority WHEN 'High' THEN 0 WHEN 'Medium' THEN 1 ELSE 2 END ASC,
                remind_at ASC");'''

new_query = '''             = str_replace("status", "r.status", );
             = str_replace("assigned_to", "r.assigned_to", );
             = str_replace("reference_type", "r.reference_type", );
             = str_replace("reminder_category", "r.reminder_category", );
             = str_replace("priority", "r.priority", );
             = str_replace("remind_at", "r.remind_at", );
            
             = ->prepare("SELECT r.*, 
                COALESCE(a.customer_name, ref.full_name, b.full_name, emp.full_name) as fetched_name,
                COALESCE(a.mobile, ref.mobile, b.contact_number, emp.official_email) as fetched_mobile
                FROM reminders r
                LEFT JOIN employees emp ON (r.reference_type = 'Staff') AND emp.user_id = COALESCE(NULLIF(r.reference_id, ''), r.lead_id)
                LEFT JOIN applicants a ON (r.reference_type = 'Lead' OR r.lead_type = 'Lead') AND a.id = COALESCE(NULLIF(r.reference_id, ''), r.lead_id)
                LEFT JOIN referrals ref ON (r.reference_type = 'Referral' OR r.lead_type = 'Referral') AND ref.id = COALESCE(NULLIF(r.reference_id, ''), r.lead_id)
                LEFT JOIN bankers b ON (r.reference_type = 'Banker' OR r.lead_type = 'Banker') AND b.id = COALESCE(NULLIF(r.reference_id, ''), r.lead_id)
                WHERE  
                ORDER BY 
                CASE WHEN r.remind_at < '' THEN 0 ELSE 1 END ASC,
                CASE r.priority WHEN 'High' THEN 0 WHEN 'Medium' THEN 1 ELSE 2 END ASC,
                r.remind_at ASC");'''

api = api.replace(old_query, new_query)

with open('api.php', 'w', encoding='utf-8') as f:
    f.write(api)

print('Success')
