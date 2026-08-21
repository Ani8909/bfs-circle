import sys

with open('api.php', 'r', encoding='utf-8') as f:
    content = f.read()

old_query = '''            $stmt = $db->prepare("SELECT * FROM reminders WHERE $where ORDER BY 
                CASE WHEN remind_at < '$now' THEN 0 ELSE 1 END ASC,
                CASE priority WHEN 'High' THEN 0 WHEN 'Medium' THEN 1 ELSE 2 END ASC,
                remind_at ASC");'''

new_query = '''            $stmt = $db->prepare("SELECT r.*, 
                COALESCE(a.customer_name, ref.full_name, b.full_name) as fetched_name,
                COALESCE(a.mobile, ref.mobile, b.contact_number) as fetched_mobile
                FROM reminders r
                LEFT JOIN applicants a ON (r.reference_type = 'Lead' OR r.lead_type = 'Lead') AND a.id = COALESCE(NULLIF(r.reference_id, ''), r.lead_id)
                LEFT JOIN referrals ref ON (r.reference_type = 'Referral' OR r.lead_type = 'Referral') AND ref.id = COALESCE(NULLIF(r.reference_id, ''), r.lead_id)
                LEFT JOIN bankers b ON (r.reference_type = 'Banker' OR r.lead_type = 'Banker') AND b.id = COALESCE(NULLIF(r.reference_id, ''), r.lead_id)
                WHERE $where 
                ORDER BY 
                CASE WHEN r.remind_at < '$now' THEN 0 ELSE 1 END ASC,
                CASE r.priority WHEN 'High' THEN 0 WHEN 'Medium' THEN 1 ELSE 2 END ASC,
                r.remind_at ASC");'''

# Fix where clause for ambiguous columns
content = content.replace("status IN", "r.status IN")
content = content.replace("assigned_to =", "r.assigned_to =")
content = content.replace("reference_type =", "r.reference_type =")
content = content.replace("reminder_category =", "r.reminder_category =")
content = content.replace("priority =", "r.priority =")
content = content.replace("remind_at <", "r.remind_at <")
content = content.replace("remind_at >=", "r.remind_at >=")
content = content.replace("remind_at <=", "r.remind_at <=")
content = content.replace("date(remind_at) =", "date(r.remind_at) =")

content = content.replace(old_query, new_query)

with open('api.php', 'w', encoding='utf-8') as f:
    f.write(content)

print('Success')
