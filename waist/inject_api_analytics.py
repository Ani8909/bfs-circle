import re

file_path = r'c:\Users\pc\Downloads\client mgmt2\api.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

new_case = """        case 'dashboard_analytics':
            $today = date('Y-m-d');
            $this_month = date('Y-m');
            $last_6_months = [];
            for ($i = 5; $i >= 0; $i--) {
                $last_6_months[] = date('Y-m', strtotime("-$i months"));
            }

            // 1. PRE-LEADS
            $pl_total    = (int)$db->query("SELECT COUNT(*) FROM pre_leads")->fetchColumn();
            $pl_new      = (int)$db->query("SELECT COUNT(*) FROM pre_leads WHERE (status='Not Contacted' OR status IS NULL OR status='') AND call_count=0")->fetchColumn();
            $pl_followup = (int)$db->query("SELECT COUNT(*) FROM pre_leads WHERE status='Follow Up' OR status='Interested'")->fetchColumn();
            $pl_junk     = (int)$db->query("SELECT COUNT(*) FROM pre_leads WHERE status='Not Interested' OR status='Junk'")->fetchColumn();

            // 2. LEADS
            $leads_total  = (int)$db->query("SELECT COUNT(*) FROM leads")->fetchColumn();
            $leads_hot    = (int)$db->query("SELECT COUNT(*) FROM leads WHERE priority='Hot'")->fetchColumn();
            $leads_month  = (int)$db->query("SELECT COUNT(*) FROM leads WHERE strftime('%Y-%m',created_at)='{$this_month}'")->fetchColumn();
            $leads_stages = $db->query("SELECT stage, COUNT(*) as cnt FROM leads GROUP BY stage")->fetchAll(PDO::FETCH_ASSOC);

            // 3. LOAN APPLICATIONS
            $apps_total     = (int)$db->query("SELECT COUNT(*) FROM applicants")->fetchColumn();
            $apps_active    = (int)$db->query("SELECT COUNT(*) FROM applicants WHERE overall_status NOT IN ('Completed','Rejected')")->fetchColumn();
            $apps_completed = (int)$db->query("SELECT COUNT(*) FROM applicants WHERE overall_status='Completed'")->fetchColumn();
            $apps_rejected  = (int)$db->query("SELECT COUNT(*) FROM applicants WHERE overall_status='Rejected'")->fetchColumn();
            $apps_disbursed = (float)($db->query("SELECT COALESCE(SUM(amount),0) FROM applicant_disbursements WHERE status='Disbursed'")->fetchColumn() ?: 0);
            $loan_types     = $db->query("SELECT loan_type, COUNT(*) as cnt FROM applicants GROUP BY loan_type ORDER BY cnt DESC LIMIT 6")->fetchAll(PDO::FETCH_ASSOC);

            $monthly_apps = [];
            foreach ($last_6_months as $m) {
                $cnt = (int)$db->query("SELECT COUNT(*) FROM applicants WHERE strftime('%Y-%m',created_at)='$m'")->fetchColumn();
                $monthly_apps[] = ['month' => date('M y', strtotime($m . '-01')), 'count' => $cnt];
            }

            // 4. FIELD VISITS
            $fv_total  = (int)$db->query("SELECT COUNT(*) FROM field_visits")->fetchColumn();
            $fv_today  = (int)$db->query("SELECT COUNT(*) FROM field_visits WHERE date(visit_date)='{$today}'")->fetchColumn();
            $fv_month  = (int)$db->query("SELECT COUNT(*) FROM field_visits WHERE strftime('%Y-%m',visit_date)='{$this_month}'")->fetchColumn();
            $fv_weekly = [];
            for ($i = 6; $i >= 0; $i--) {
                $d   = date('Y-m-d', strtotime("-$i days"));
                $cnt = (int)$db->query("SELECT COUNT(*) FROM field_visits WHERE date(visit_date)='$d'")->fetchColumn();
                $fv_weekly[] = ['day' => date('D', strtotime($d)), 'count' => $cnt];
            }

            // 5. CLIENT VAULT
            $vault_total = (int)$db->query("SELECT COUNT(*) FROM applicants WHERE overall_status='Completed'")->fetchColumn();
            $vault_prime = (int)$db->query("SELECT COUNT(*) FROM applicants WHERE overall_status='Completed' AND created_at < date('now','-6 months')")->fetchColumn();

            // 6. BANKERS
            $bankers_total  = (int)$db->query("SELECT COUNT(*) FROM bankers")->fetchColumn();
            $bankers_active = (int)$db->query("SELECT COUNT(*) FROM bankers WHERE status='Active'")->fetchColumn();
            $bank_assigned  = (int)$db->query("SELECT COUNT(*) FROM applicant_bank_assignments")->fetchColumn();
            $bank_approved  = (int)$db->query("SELECT COUNT(*) FROM applicant_bank_assignments WHERE status IN ('Approved','Sanctioned')")->fetchColumn();
            $bank_wise      = $db->query("SELECT bank_name, COUNT(*) as cnt FROM applicant_bank_assignments GROUP BY bank_name ORDER BY cnt DESC LIMIT 6")->fetchAll(PDO::FETCH_ASSOC);

            // 7. REFERRALS
            $referrals_total = (int)$db->query("SELECT COUNT(*) FROM referrals")->fetchColumn();
            $top_referrals   = $db->query("SELECT full_name as name FROM referrals WHERE status='Active' ORDER BY created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

            // 8. PAYOUTS
            $payout_total = (float)($db->query("SELECT COALESCE(SUM(net_payable),0) FROM payout_distributions WHERE status='Paid'")->fetchColumn() ?: 0);
            $payout_month = (float)($db->query("SELECT COALESCE(SUM(net_payable),0) FROM payout_distributions WHERE status='Paid' AND strftime('%Y-%m',paid_on)='{$this_month}'")->fetchColumn() ?: 0);

            // 9. STAFF
            $staff_total  = (int)$db->query("SELECT COUNT(*) FROM users WHERE role='Staff'")->fetchColumn();
            $staff_online = (int)$db->query("SELECT COUNT(*) FROM users WHERE role='Staff' AND last_active >= datetime('now','localtime','-2 minutes')")->fetchColumn();
            $staff_perf   = $db->query("SELECT added_by as name, COUNT(*) as cnt FROM applicants WHERE added_by IS NOT NULL AND added_by != '' GROUP BY added_by ORDER BY cnt DESC LIMIT 7")->fetchAll(PDO::FETCH_ASSOC);

            // 10. EMAILS / COMMUNICATIONS
            $emails_today = (int)$db->query("SELECT COUNT(*) FROM communications WHERE date(sent_at)='{$today}'")->fetchColumn();
            $emails_month = (int)$db->query("SELECT COUNT(*) FROM communications WHERE strftime('%Y-%m',sent_at)='{$this_month}'")->fetchColumn();

            // 11. REMINDERS
            $reminders_today   = (int)$db->query("SELECT COUNT(*) FROM reminders WHERE date(remind_at)='{$today}' AND status='Pending'")->fetchColumn();
            $reminders_overdue = (int)$db->query("SELECT COUNT(*) FROM reminders WHERE remind_at < '{$today}' AND status='Pending'")->fetchColumn();

            return_json([
                'pre_leads'    => ['total'=>$pl_total,'new'=>$pl_new,'followup'=>$pl_followup,'junk'=>$pl_junk],
                'leads'        => ['total'=>$leads_total,'hot'=>$leads_hot,'this_month'=>$leads_month,'stages'=>$leads_stages],
                'applications' => ['total'=>$apps_total,'active'=>$apps_active,'completed'=>$apps_completed,'rejected'=>$apps_rejected,'disbursed'=>$apps_disbursed,'loan_types'=>$loan_types,'monthly_growth'=>$monthly_apps],
                'field_visits' => ['total'=>$fv_total,'today'=>$fv_today,'this_month'=>$fv_month,'weekly'=>$fv_weekly],
                'client_vault' => ['total'=>$vault_total,'prime'=>$vault_prime],
                'bankers'      => ['total'=>$bankers_total,'active'=>$bankers_active,'assignments'=>$bank_assigned,'approved'=>$bank_approved,'bank_wise'=>$bank_wise],
                'referrals'    => ['total'=>$referrals_total,'top'=>$top_referrals],
                'payouts'      => ['total'=>$payout_total,'this_month'=>$payout_month],
                'staff'        => ['total'=>$staff_total,'online'=>$staff_online,'performance'=>$staff_perf],
                'emails'       => ['today'=>$emails_today,'this_month'=>$emails_month],
                'reminders'    => ['today'=>$reminders_today,'overdue'=>$reminders_overdue],
            ]);
            break;

"""

# Insert before 'case 'stats':'
insert_point = content.find("        case 'stats':")
content = content[:insert_point] + new_case + content[insert_point:]

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("dashboard_analytics API endpoint added successfully")
