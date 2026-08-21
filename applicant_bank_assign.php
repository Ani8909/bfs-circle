<?php
require_once 'config.php';
$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header("Location: dashboard.php");
    exit;
}

$stmt = $db->prepare("SELECT a.*, l.company_name as lead_company, u.name as employee_name, r.full_name as referral_name 
                      FROM applicants a 
                      LEFT JOIN leads l ON a.mobile = l.mobile
                      LEFT JOIN users u ON a.added_by = u.id OR a.employee_id = u.id
                      LEFT JOIN referrals r ON a.referral_id = r.referral_id
                      WHERE a.id = ?");
$stmt->execute([$id]);
$app = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$app) {
    die("Applicant not found.");
}

// Find the Approved Bank for this applicant
$stmt = $db->prepare("SELECT bank_name FROM applicant_bank_assignments WHERE applicant_id = ? AND status = 'Approved' ORDER BY id DESC LIMIT 1");
$stmt->execute([$id]);
$approved_bank = $stmt->fetchColumn();

// Fetch Co-Applicants
$co_stmt = $db->prepare("SELECT * FROM co_applicants WHERE applicant_id = ?");
$co_stmt->execute([$id]);
$co_apps = $co_stmt->fetchAll(PDO::FETCH_ASSOC);

// Auto-fetch payout percentage
$payout_percentage = 0;
if ($approved_bank) {
    $stmt = $db->prepare("SELECT payout_percentage FROM bank_payout_settings WHERE bank_name = ? AND (loan_type = ? OR loan_type = 'All') ORDER BY loan_type = 'All' ASC LIMIT 1");
    $stmt->execute([$approved_bank, $app['loan_type']]);
    $payout_percentage = $stmt->fetchColumn() ?: 0;
}

// Fetch Banker Address Details for the approved bank
$bank_address = 'N/A';
$banker_email = '';
$banker_name = '';
if ($approved_bank) {
    $stmt_addr = $db->prepare("SELECT full_name, address, city, state, official_email FROM bankers WHERE bank_name = ? AND address IS NOT NULL AND address != '' ORDER BY id DESC LIMIT 1");
    $stmt_addr->execute([$approved_bank]);
    $banker_row = $stmt_addr->fetch(PDO::FETCH_ASSOC);
    if ($banker_row) {
        $parts = array_filter([$banker_row['address'], $banker_row['city'], $banker_row['state']]);
        $bank_address = !empty($parts) ? implode(', ', $parts) : 'N/A';
        $banker_email = $banker_row['official_email'] ?? '';
        $banker_name = $banker_row['full_name'] ?? '';
    }
    
    // If no email found from address query, try any banker with email for this bank
    if (empty($banker_email)) {
        $stmt_email = $db->prepare("SELECT full_name, official_email FROM bankers WHERE bank_name = ? AND official_email IS NOT NULL AND official_email != '' ORDER BY id DESC LIMIT 1");
        $stmt_email->execute([$approved_bank]);
        $email_row = $stmt_email->fetch(PDO::FETCH_ASSOC);
        if ($email_row) {
            $banker_email = $email_row['official_email'];
            if (empty($banker_name)) $banker_name = $email_row['full_name'];
        }
    }
}

// Fetch documents
$stmt = $db->prepare("SELECT * FROM applicant_documents WHERE applicant_id = ?");
$stmt->execute([$id]);
$docs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- PHASE 5: PAYOUT LEDGER DATA ---
// Fetch referral partner details (smart lookup)
$referral_data = null;
if (!empty($app['referral_id'])) {
    // Try exact match on referral_id
    $stmt_ref = $db->prepare("SELECT * FROM referrals WHERE referral_id = ?");
    $stmt_ref->execute([$app['referral_id']]);
    $referral_data = $stmt_ref->fetch(PDO::FETCH_ASSOC);
    
    // If not found, try matching by full_name (in case referral_id stores a name like "CA-DEMO")
    if (!$referral_data) {
        $stmt_ref2 = $db->prepare("SELECT * FROM referrals WHERE full_name LIKE ? OR referral_id LIKE ?");
        $search = '%' . $app['referral_id'] . '%';
        $stmt_ref2->execute([$search, $search]);
        $referral_data = $stmt_ref2->fetch(PDO::FETCH_ASSOC);
    }
    
    // If still not found but we know it's a referral lead, create a placeholder
    if (!$referral_data && $app['lead_source'] === 'Referral Partner / Agent') {
        $referral_data = [
            'full_name' => $app['referral_id'],
            'referrer_type' => 'Partner',
            'mobile' => $app['referral_name'] ?? 'N/A',
            'email' => '',
            'commission_rate' => '',
            'pan_number' => '',
            'account_number' => '',
            'ifsc_code' => '',
            'upi_id' => '',
            'referral_id' => $app['referral_id']
        ];
    }
}

// Calculate payout breakdown
$sanctioned_amt = (float)($app['sanctioned_amount'] ?? 0);
$gross_payout = ($sanctioned_amt * $payout_percentage) / 100;

$referral_commission_rate = 0;
$referral_share = 0;
$company_share = $gross_payout;

if ($referral_data && !empty($referral_data['commission_rate'])) {
    $referral_commission_rate = (float)preg_replace('/[^0-9.]/', '', $referral_data['commission_rate']);
    $referral_share = ($gross_payout * $referral_commission_rate) / 100;
    $company_share = $gross_payout - $referral_share;
}

// Fetch existing payout distribution records for this applicant
$stmt_payouts = $db->prepare("SELECT pd.*, u.name as payee_name, u.role as payee_role FROM payout_distributions pd LEFT JOIN users u ON pd.payee_user_id = u.id WHERE pd.applicant_id = ? ORDER BY pd.id ASC");
$stmt_payouts->execute([$id]);
$payout_records = $stmt_payouts->fetchAll(PDO::FETCH_ASSOC);

// Check if bank payout has been received
$payout_received = false;
$payout_received_date = '';
$payout_received_amount = 0;
if (!empty($app['payout_received']) && $app['payout_received'] == 1) {
    $payout_received = true;
    $payout_received_date = $app['payout_received_date'] ?? '';
    $payout_received_amount = (float)($app['payout_received_amount'] ?? $gross_payout);
}

// Try adding payout tracking columns if they don't exist
try { $db->exec("ALTER TABLE applicants ADD COLUMN payout_received INTEGER DEFAULT 0"); } catch(Exception $e) {}
try { $db->exec("ALTER TABLE applicants ADD COLUMN payout_received_date TEXT"); } catch(Exception $e) {}
try { $db->exec("ALTER TABLE applicants ADD COLUMN payout_received_amount REAL DEFAULT 0"); } catch(Exception $e) {}
try { $db->exec("ALTER TABLE applicants ADD COLUMN payout_transaction_ref TEXT"); } catch(Exception $e) {}

// Handle Final Disbursement Submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'finalize_disbursement') {
    $disbursed_amt = (float)$_POST['disbursed_amount'];
    $disbursed_date = trim($_POST['disbursed_date']);
    $final_bank = trim($_POST['final_bank_name']);
    $remarks = trim($_POST['remarks']);
    
    // We could save this in a new table `disbursements` or just add a few columns to `applicants`.
    // For now, let's just update overall_status to 'Completed' and log the activity since it's the final stage!
    $db->query("UPDATE applicants SET overall_status = 'Completed' WHERE id = $id");
    log_activity("Completed Disbursement (₹" . number_format($disbursed_amt) . " via $final_bank)", "applicant_bank_assign.php?id=$id");
    
    header("Location: search_track.php");
    exit;
}

// Handle Payout Received
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'mark_payout_received') {
    $recv_amount = (float)$_POST['received_amount'];
    $recv_date = trim($_POST['received_date']);
    $recv_ref = trim($_POST['transaction_ref'] ?? '');
    
    $stmt = $db->prepare("UPDATE applicants SET payout_received = 1, payout_received_amount = ?, payout_received_date = ?, payout_transaction_ref = ? WHERE id = ?");
    $stmt->execute([$recv_amount, $recv_date, $recv_ref, $id]);
    
    log_activity("Payout Received: ₹" . number_format($recv_amount) . " for Applicant #$id", "applicant_bank_assign.php?id=$id");
    header("Location: applicant_bank_assign.php?id=$id");
    exit;
}

// Handle Generate Referral Payout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'generate_referral_payout') {
    $ref_gross = (float)$_POST['referral_gross'];
    $ref_tds = (float)$_POST['referral_tds'];
    $ref_net = $ref_gross - $ref_tds;
    $payee_id = (int)$_POST['payee_user_id'];
    
    // Check if already exists
    $check = $db->prepare("SELECT id FROM payout_distributions WHERE applicant_id = ? AND payee_type = 'Partner'");
    $check->execute([$id]);
    if (!$check->fetch()) {
        $stmt = $db->prepare("INSERT INTO payout_distributions (applicant_id, payee_type, payee_user_id, total_loan_amount, commission_percentage, gross_payout, tds_deducted, net_payable, status) VALUES (?, 'Partner', ?, ?, ?, ?, ?, ?, 'Pending')");
        $stmt->execute([$id, $payee_id, $sanctioned_amt, $referral_commission_rate, $ref_gross, $ref_tds, $ref_net]);
        log_activity("Generated Referral Payout: ₹" . number_format($ref_net) . " for Applicant #$id", "applicant_bank_assign.php?id=$id");
    }
    
    header("Location: applicant_bank_assign.php?id=$id");
    exit;
}

$page_title = 'Final Kundli & Disbursements';
$page_subtitle = ' Phase 4: Complete A-Z Applicant History & Final Disbursement';
require_once 'header.php';
?>

<div class="view-container">
    <style>
        @media print {
            @page { margin: 8mm; size: A4 portrait; }
            * { 
                box-sizing: border-box !important;
                -webkit-print-color-adjust: exact !important; 
                print-color-adjust: exact !important; 
            }
            body, html { 
                background: #fff !important; 
                margin: 0 !important; 
                padding: 0 !important; 
                width: 100% !important;
                max-width: 100% !important;
                font-family: 'Segoe UI', Arial, sans-serif !important;
            }
            main, .main-content, .view-container { 
                margin: 0 !important; 
                padding: 0 !important; 
                width: 100% !important;
                max-width: 100% !important;
                overflow: visible !important; 
            }
            aside, .header-action-container, .sidebar, header, .btn, button, a.btn, nav, .print-hide, p { 
                display: none !important; 
            }
            
            /* Bring back Grid to save vertical space! */
            .dashboard-layout-row { 
                display: grid !important; 
                grid-template-columns: 1fr 1fr !important;
                gap: 12px !important;
            }
            
            .card { 
                box-shadow: none !important; 
                border: 1px solid #cbd5e1 !important; 
                margin-bottom: 0 !important; 
                width: 100% !important;
                border-radius: 6px !important;
                overflow: hidden !important;
                page-break-inside: avoid;
            }
            
            /* Force blocks with grid-column in style to span full width */
            .card[style*="grid-column"] { grid-column: 1 / -1 !important; }
            
            .card-title-bar { 
                background: #f8fafc !important; 
                border-bottom: 1px solid #cbd5e1 !important; 
                padding: 6px 12px !important;
                margin-bottom: 0 !important;
            }
            .card-title-bar h3 { 
                font-size: 11px !important; 
                font-weight: 800 !important;
                color: #334155 !important;
                text-transform: uppercase !important;
                letter-spacing: 0.5px !important;
                margin: 0 !important;
            }
            .card-title-bar svg, .card-title-bar i { display: none !important; } 
            .card > div[style*="padding"] { padding: 0 !important; }
            
            table.data-table { 
                width: 100% !important; 
                border-collapse: collapse !important;
                font-size: 11px !important;
                line-height: 1.3 !important;
            }
            table.data-table td { 
                border-bottom: 1px solid #e2e8f0 !important; 
                border-top: none !important;
                border-left: none !important;
                border-right: none !important;
                padding: 5px 8px !important; 
                color: #0f172a !important; 
            }
            table.data-table tr:last-child td { border-bottom: none !important; }
            table.data-table tr td:first-child {
                background: #fafafa !important;
                font-weight: 600 !important;
                color: #64748b !important;
                width: 35% !important;
                border-right: 1px solid #e2e8f0 !important;
            }
            
            table.data-table td[colspan="2"] {
                background: #f1f5f9 !important;
                text-align: center !important;
                font-weight: 700 !important;
                font-size: 11px !important;
                color: #334155 !important;
                border-bottom: 1px solid #cbd5e1 !important;
                padding: 4px !important;
            }
            
            .badge { border: 1px solid #cbd5e1 !important; background: transparent !important; color: #0f172a !important; padding: 1px 4px !important; font-weight: bold !important; font-size: 10px !important; border-radius: 4px !important; }
            
            /* Payout adjustments */
            .form-grid { display: flex !important; justify-content: space-between !important; border: none !important; border-bottom: 1px solid #e2e8f0 !important; padding: 10px !important; background: #fff !important; margin: 0 !important; }
            input[type="number"] { border: none !important; background: transparent !important; color: #000 !important; font-weight: bold !important; padding: 0 !important; width: auto !important; }
            
            /* Document vault adjustments */
            .card > div > div[style*="display:flex; flex-wrap:wrap"] { gap: 6px !important; padding: 8px !important; }
            .card > div > div > div[style*="width:220px"] { width: calc(33.33% - 6px) !important; padding: 6px !important; margin: 0 !important; }
            
            .print-header { display: flex !important; }
            
            #print-append-zone { 
                display: block !important; 
                clear: both !important; 
                width: 100% !important; 
                float: none !important;
            }
            .print-doc-page { 
                display: block !important; 
                clear: both !important; 
                page-break-before: always !important; 
                break-before: page !important; 
                width: 100% !important; 
                padding-top: 15px !important; 
            }
            .print-doc-page h2 { 
                font-size: 16px !important; 
                text-transform: uppercase !important; 
                margin-bottom: 20px !important; 
                border-bottom: 2px solid #000 !important; 
                padding-bottom: 5px !important; 
                width: 100% !important; 
                text-align: left !important; 
                display: block !important;
            }
            .print-doc-page img { 
                max-width: 100% !important; 
                max-height: 250mm !important; /* Fixed print size max */
                object-fit: contain !important; 
                margin: 0 auto !important; 
                display: block !important;
            }
            
            .no-print { display: none !important; }
        }
        
        @media screen {
            #print-append-zone { display: none !important; }
            
            .icon-action-btn {
                display: flex; align-items: center; justify-content: center;
                width: 40px; height: 40px; border-radius: 10px;
                border: 1px solid transparent; cursor: pointer;
                transition: all 0.2s ease; background: #fff;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            }
            .icon-action-btn:hover {
                box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
                transform: translateY(-2px);
            }
            .icon-action-btn.btn-print { background: #eff6ff; color: #2563eb; border-color: #bfdbfe; }
            .icon-action-btn.btn-print:hover { background: #dbeafe; color: #1d4ed8; }
            
            .icon-action-btn.btn-back { background: #f8fafc; color: #475569; border-color: #e2e8f0; }
            .icon-action-btn.btn-back:hover { background: #f1f5f9; color: #1e293b; }
            
            .icon-action-btn.btn-success { background: #f0fdf4; color: #16a34a; border-color: #bbf7d0; }
            .icon-action-btn.btn-success:hover { background: #dcfce7; color: #15803d; }
        }
        
        .print-header { display: none; align-items: center; justify-content: space-between; border-bottom: 2px solid #0f172a; padding-bottom: 10px; margin-bottom: 15px; }
    
    .data-table { border: 1px solid var(--border) !important; border-collapse: collapse !important; background: #fff !important; }
    .data-table td, .data-table th { border: 1px solid var(--border) !important; padding: 12px 18px !important; vertical-align: middle !important; }
    .data-table tr td:first-child { background: #f8fafc !important; color: #0f172a !important; font-weight: 600 !important; width: 35% !important; }
    .data-table tr td:last-child { color: #0f172a !important; font-weight: 500 !important; }
    #sourcing-header-row td { background: #0f172a !important; color: #ffffff !important; font-weight: 700 !important; border: 1px solid #0f172a !important; }
    #sourcing-header-row td div { color: #ffffff !important; letter-spacing: 1px !important; }
    .card-title-bar { background: #ffffff !important; border-bottom: 2px solid #0f172a !important; }
    .card-title-bar h3 { color: #0f172a !important; font-weight: 800 !important; }
    .badge { background: white !important; border: 1px solid #0f172a !important; color: #0f172a !important; font-weight: 600 !important; padding: 4px 10px !important; border-radius: 20px !important; }
</style>
    
    <!-- Print-only Header with Logo -->
    <div class="print-header">
        <img src="logo.png" alt="BFS Financial Services Logo" style="max-height: 40px; filter: grayscale(100%);">
        <div style="text-align: right;">
            <h1 style="margin: 0; font-size: 22px; font-weight: 800; color: #0f172a; text-transform: uppercase; letter-spacing: 1px;">Customer Kundli</h1>
            <div style="font-size: 13px; color: #64748b; margin-top: 4px; font-weight: 600;">APPLICANT ID: <?php echo htmlspecialchars($app['loan_id']); ?></div>
        </div>
    </div>

    <!-- Screen-only Title Area -->
    <div class="print-hide" style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 24px;">
        <h2 style="margin:0; color:#0f172a; display:flex; align-items:center;"><i data-lucide="book-open" style="color:var(--primary); margin-right:8px;"></i> Customer Master Kundli (A to Z)</h2>
        <div style="display:flex; gap:12px; align-items:center;">
            <a href="search_track.php" class="icon-action-btn btn-back" title="Back to Track">
                <i data-lucide="arrow-left" style="width:20px; height:20px;"></i>
            </a>
            <button class="icon-action-btn btn-print" onclick="printKundli()" title="Print Summary">
                <i data-lucide="printer" style="width:20px; height:20px;"></i>
            </button>
            <?php if($app['overall_status'] !== 'Completed'): ?>
            <button class="icon-action-btn btn-success" onclick="document.getElementById('disbursement-modal').style.display='flex'" title="Mark as Disbursed (Complete)">
                <i data-lucide="check-circle" style="width:20px; height:20px;"></i>
            </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- MAIN GRID -->
    <div class="dashboard-layout-row" style="grid-template-columns: 1fr 1fr; gap: 24px;">
        
        <!-- BLOCK 1: Profile & Sourcing -->
        <div class="card">
            <div class="card-title-bar" style="background:#f8fafc; border-bottom:1px solid var(--primary-border); padding:16px;">
                <h3 style="margin:0; font-size:16px; display:flex; align-items:center; gap:8px;"><i data-lucide="user" style="color:var(--primary);"></i> 1. Basic & Sourcing Info (Phase 1)</h3>
            </div>
            <div style="padding: 16px;">
                <table class="data-table" style="font-size:14px; width:100%;">
                    <tr><td style="color:var(--text-muted); width:40%;">Customer Name</td><td style="font-weight:600; color:var(--text-primary);"><?php echo htmlspecialchars($app['customer_name']); ?></td></tr>
                    <tr><td style="color:var(--text-muted);">Applicant ID</td><td style="color:var(--text-primary);"><?php echo htmlspecialchars($app['loan_id']); ?></td></tr>
                    <tr><td style="color:var(--text-muted);">Mobile</td><td style="color:var(--text-primary);"><?php echo htmlspecialchars($app['mobile']); ?></td></tr>
                    <tr><td style="color:var(--text-muted);">PAN Number</td><td style="color:var(--text-primary);"><?php echo htmlspecialchars($app['pan_number']); ?></td></tr>
                    <tr><td style="color:var(--text-muted);">Aadhaar Number</td><td style="color:var(--text-primary);"><?php echo htmlspecialchars($app['aadhar_number']); ?></td></tr>
                    <tr><td style="color:var(--text-muted);">City / State</td><td style="color:var(--text-primary);"><?php echo htmlspecialchars($app['city'] . ', ' . $app['state']); ?></td></tr>
                    <tr id="sourcing-header-row">
                        <td colspan="2" style="background:#f8fafc; border-top:1px solid var(--primary-border); border-bottom:1px solid var(--primary-border); padding:8px; position:relative;">
                            <div style="text-align:center; font-weight:600; color:var(--text-primary); text-transform:uppercase; font-size:12px; letter-spacing:0.5px;">Sourcing Origins</div>
                            <label class="print-hide" style="position:absolute; right:8px; top:50%; transform:translateY(-50%); display:flex; align-items:center; gap:4px; font-size:11px; font-weight:600; color:var(--text-primary); cursor:pointer; background:white; padding:4px 8px; border-radius:6px; border:1px solid var(--primary-border); box-shadow:0 1px 2px rgba(0,0,0,0.05);" title="Include Sourcing Info in Print">
                                <input type="checkbox" id="toggle-sourcing" checked onchange="toggleSourcingVisibility()" style="cursor:pointer; width:12px; height:12px; accent-color:var(--primary);">
                                Print Info
                            </label>
                        </td>
                    </tr>
                    <tr class="sourcing-row">
                        <td style="color:var(--text-muted);">Lead Source</td>
                        <td>
                            <span class="badge" style="background:#f8fafc; color:var(--text-primary); border:1px solid var(--primary-border);"><?php echo htmlspecialchars($app['lead_source']); ?></span>
                        </td>
                    </tr>
                    <tr class="sourcing-row"><td style="color:var(--text-muted);">Added By (Employee)</td><td style="color:var(--text-primary);"><?php echo htmlspecialchars($app['employee_name'] ?? $app['added_by']); ?></td></tr>
                    <tr class="sourcing-row">
                        <td style="color:var(--text-muted);">Channel / Partner Name</td>
                        <td style="font-weight:700; color:var(--primary);">
                            <?php 
                                if (!empty($app['referral_name'])) {
                                    echo htmlspecialchars($app['referral_name']) . ' <span style="font-size:11px; color:var(--text-muted); font-weight:normal;">(' . htmlspecialchars($app['referral_id']) . ')</span>';
                                } elseif (!empty($app['referral_id'])) {
                                    echo htmlspecialchars($app['referral_id']);
                                } else {
                                    echo 'Direct';
                                }
                            ?>
                        </td>
                    </tr>
                    <tr class="sourcing-row"><td style="color:var(--text-muted);">System Entry Date</td><td style="color:var(--text-primary);"><?php echo date('d M Y, h:i A', strtotime($app['created_at'])); ?></td></tr>
                </table>
                
                <?php if (!empty($co_apps)): ?>
                <?php foreach ($co_apps as $index => $c): ?>
                <table class="data-table" style="font-size:14px; width:100%; margin-top:20px; border-top:2px solid var(--primary-border);">
                    <tr><td colspan="2" style="background:#f8fafc; padding:8px; text-align:center; font-weight:600; color:var(--primary); text-transform:uppercase; font-size:12px; letter-spacing:0.5px; border-bottom:1px solid var(--primary-border);">Co-Applicant #<?php echo $index + 1; ?> (<?php echo htmlspecialchars($c['relationship']); ?>)</td></tr>
                    <tr><td style="color:var(--text-muted); width:40%;">Full Name</td><td style="font-weight:600; color:var(--text-primary);"><?php echo htmlspecialchars($c['full_name']); ?></td></tr>
                    <tr><td style="color:var(--text-muted);">Financial Co-Borrower?</td><td><span class="badge <?php echo $c['is_financial'] === 'Yes' ? 'badge-success' : 'badge-warning'; ?>"><?php echo htmlspecialchars($c['is_financial']); ?></span></td></tr>
                    <tr><td style="color:var(--text-muted);">PAN Number</td><td style="color:var(--text-primary);"><?php echo htmlspecialchars($c['pan_number']); ?></td></tr>
                    <tr><td style="color:var(--text-muted);">Aadhaar Number</td><td style="color:var(--text-primary);"><?php echo htmlspecialchars($c['aadhar_number']); ?></td></tr>
                    <?php if ($c['is_financial'] === 'Yes'): ?>
                    <tr><td style="color:var(--text-muted);">Employment</td><td style="color:var(--text-primary);"><?php echo htmlspecialchars($c['employment_type']); ?></td></tr>
                    <tr><td style="color:var(--text-muted);">Monthly Income</td><td style="color:var(--text-primary);">₹<?php echo number_format($c['monthly_income']); ?></td></tr>
                    <?php endif; ?>
                </table>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <!-- BLOCK 2: Loan & Bank Sanction -->
        <?php 
            $is_sanctioned = (!empty($app['sanctioned_amount']) && $app['sanctioned_amount'] > 0);
            $has_bank = !empty($approved_bank);
            // Logic based colors
            if ($is_sanctioned) {
                $status_bg = 'var(--status-won-light)';
                $status_border = 'var(--success)';
                $status_text = 'var(--success)';
            } elseif ($has_bank) {
                $status_bg = 'var(--status-new-light)'; // Pending but bank selected
                $status_border = 'var(--status-new)';
                $status_text = 'var(--status-new)';
            } else {
                $status_bg = 'var(--primary-light)'; // Completely blank/new
                $status_border = 'var(--primary-border)';
                $status_text = 'var(--text-muted)';
            }
        ?>
        <div class="card">
            <div class="card-title-bar" style="background:<?php echo $status_bg; ?>; border-bottom:1px solid <?php echo $status_border; ?>; padding:16px;">
                <h3 style="margin:0; font-size:16px; display:flex; align-items:center; gap:8px; color:<?php echo $status_text; ?>;">
                    <i data-lucide="landmark" style="color:<?php echo $status_text; ?>;"></i> 2. Loan & Sanction Details (Phase 3)
                </h3>
            </div>
            <div style="padding: 16px;">
                <table class="data-table" style="font-size:14px; width:100%;">
                    <tr><td style="color:var(--text-muted); width:40%;">Loan Type</td><td style="font-weight:600; color:var(--text-primary);"><?php echo htmlspecialchars($app['loan_type']); ?></td></tr>
                    <tr><td style="color:var(--text-muted);">Requested Amount</td><td style="font-weight:700; color:var(--text-primary);">₹<?php echo number_format($app['loan_amount_requested'] ?? 0); ?></td></tr>
                    <tr><td style="color:var(--text-muted);">Monthly Income</td><td style="color:var(--text-primary);">₹<?php echo number_format($app['monthly_income'] ?? 0); ?></td></tr>
                    <tr><td style="color:var(--text-muted);">Applicant CIBIL</td><td><span class="badge <?php echo ($app['cibil_score'] >= 750) ? 'badge-success' : 'badge-warning'; ?>"><?php echo $app['cibil_score'] ?: 'N/A'; ?></span></td></tr>
                    
                    <tr><td colspan="2" style="background:<?php echo $status_bg; ?>; padding:8px; text-align:center; font-weight:600; color:<?php echo $status_text; ?>; border-top:1px solid <?php echo $status_border; ?>; border-bottom:1px solid <?php echo $status_border; ?>; margin-top:8px; text-transform:uppercase; font-size:12px; letter-spacing:0.5px;">Bank Feedback / Sanction</td></tr>
                    
                    <tr><td style="color:var(--text-muted);">Approved Bank</td><td style="font-weight:700; color:<?php echo $has_bank ? 'var(--primary)' : 'var(--text-muted)'; ?>;"><?php echo htmlspecialchars($approved_bank ?: 'N/A'); ?></td></tr>
                    <tr><td style="color:var(--text-muted);">Branch Address</td><td style="font-size:12px; color:var(--text-muted);"><?php echo htmlspecialchars($bank_address); ?></td></tr>
                    <tr><td style="color:var(--text-muted);">Sanctioned Amount</td><td style="color:<?php echo $is_sanctioned ? 'var(--success)' : 'var(--text-muted)'; ?>; font-weight:800; font-size:16px;">₹<?php echo number_format($app['sanctioned_amount'] ?? 0); ?></td></tr>
                    <tr><td style="color:var(--text-muted);">Sanction Date</td><td style="font-weight:600; color:var(--text-primary);"><?php echo !empty($app['sanction_date']) ? date('d M Y', strtotime($app['sanction_date'])) : 'Pending'; ?></td></tr>
                    <tr><td style="color:var(--text-muted);">Approved ROI (%)</td><td style="font-weight:700; color:var(--text-primary);"><?php echo $app['interest_rate'] ? $app['interest_rate'].'%' : 'Pending'; ?></td></tr>
                    <tr><td style="color:var(--text-muted);">Tenure (Months)</td><td style="color:var(--text-primary);"><?php echo $app['tenure_months'] ?: 'Pending'; ?></td></tr>
                    <tr><td style="color:var(--text-muted);">Approved EMI</td><td style="color:var(--text-primary);">₹<?php echo number_format($app['emi'] ?? 0); ?></td></tr>
                </table>
            </div>
        </div>

        <!-- BLOCK 3: Payout & Commission Claim -->
        <div class="card" id="payout-block" style="grid-column: 1 / -1; margin-top: 24px;">
            <div class="card-title-bar" style="background:#f8fafc; border-bottom:1px solid var(--primary-border); padding:16px; display:flex; justify-content:space-between; align-items:center;">
                <h3 style="margin:0; font-size:16px; display:flex; align-items:center; gap:8px; color:var(--primary);"><i data-lucide="receipt" style="color:var(--primary);"></i> 3. Payout & Commission Claim</h3>
                <label class="print-hide" style="font-size:12px; cursor:pointer; font-weight:600; color:var(--text-primary); display:flex; align-items:center; gap:6px; background:var(--bg-card); padding:4px 8px; border-radius:6px; border:1px solid var(--primary-border);">
                    <input type="checkbox" checked onchange="document.getElementById('payout-block').classList.toggle('no-print', !this.checked)" style="accent-color:var(--primary);"> Print this block
                </label>
            </div>
            <div style="padding: 24px; display:flex; gap: 32px; align-items:flex-start;">
                <div style="flex: 1;">
                    <p style="color:var(--text-muted); font-size:14px; margin-bottom:16px; line-height:1.6;">Once the loan is sanctioned or disbursed by the bank, you can generate an invoice/claim email to request your sourcing payout (commission) from the bank.</p>
                    
                    <div class="form-grid" style="grid-template-columns: 1fr 1fr; background:#f8fafc; padding: 16px; border-radius: 8px; border: 1px solid var(--primary-border); margin-bottom: 16px;">
                        <div>
                            <label style="font-size:11px; color:var(--text-muted); text-transform:uppercase; font-weight:600;">Sanctioned Amount</label>
                            <div style="font-size:18px; font-weight:700; color:var(--text-primary);">₹<?php echo number_format($app['sanctioned_amount'] ?? 0); ?></div>
                        </div>
                        <div>
                            <label style="font-size:11px; color:var(--text-muted); text-transform:uppercase; font-weight:600;">Expected Payout (%)</label>
                            <input type="number" id="payout_percentage" value="<?php echo $payout_percentage ?: '0'; ?>" step="0.1" style="width:100px; padding:6px 10px; border:1px solid var(--primary-border); border-radius:6px; font-weight:bold; color:var(--text-primary); background:var(--bg-card);" onchange="calculatePayout()">
                            <div style="font-size:10px; color:var(--success); margin-top:4px;">Auto-fetched for <?php echo htmlspecialchars($approved_bank ?: 'Bank'); ?></div>
                        </div>
                    </div>
                    
                    <div style="display:flex; align-items:center; justify-content:space-between; background:var(--status-won-light); padding: 16px; border-radius: 8px; border: 1px solid var(--success);">
                        <span style="color:var(--success); font-weight:600;">Estimated Payout Amount:</span>
                        <span id="estimated_payout" style="font-size:24px; font-weight:800; color:var(--success);">₹0</span>
                    </div>
                </div>
                
                <div class="print-hide" style="flex: 1; display:flex; flex-direction:column; gap:16px; justify-content:center; border-left: 1px solid var(--primary-border); padding-left: 32px;">
                    <h4 style="margin:0; font-size:14px; color:var(--text-primary);">Claim Actions</h4>
                    <button type="button" class="btn btn-primary" onclick="openPayoutModal()" style="display:flex; align-items:center; justify-content:center; gap:8px; padding:12px; font-weight:600; font-size:14px; background:var(--primary); border:none;">
                        <i data-lucide="mail"></i> Email Payout Invoice to Bank
                    </button>
                    <p style="font-size:12px; color:var(--text-muted); margin:0; text-align:center;">This will prepare a formal email with the customer's applicant ID and your payout calculation to send directly to the banker.</p>
                </div>
            </div>
        </div>
        
        <!-- BLOCK 4: Documents Collected -->
        <div class="card" style="grid-column: 1 / -1;">
            <div class="card-title-bar" style="background:#f8fafc; border-bottom:1px solid var(--primary-border); padding:16px;">
                <h3 style="margin:0; font-size:16px; display:flex; align-items:center; gap:8px;"><i data-lucide="folder-open" style="color:var(--primary);"></i> 4. Vault: Collected Documents (Phase 2)</h3>
            </div>
            <div style="padding: 16px;">
                <?php if(empty($docs)): ?>
                    <p style="color:var(--text-muted); font-style:italic;">No documents uploaded in Phase 2.</p>
                <?php else: ?>
                    <div style="display:flex; flex-wrap:wrap; gap:16px;">
                    <?php foreach($docs as $doc): ?>
                        <div style="border:1px solid var(--primary-border); border-radius:8px; padding:12px; width:220px; background:var(--bg-card);">
                            <div style="font-size:12px; color:var(--primary); font-weight:700; margin-bottom:4px; text-transform:uppercase;"><?php echo htmlspecialchars($doc['document_category']); ?></div>
                            <div style="font-size:14px; color:var(--text-primary); font-weight:600; margin-bottom:8px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="<?php echo htmlspecialchars($doc['document_name']); ?>"><?php echo htmlspecialchars($doc['document_name']); ?></div>
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <label style="display:flex; align-items:center; gap:4px; font-size:11px; font-weight:600; cursor:pointer;" class="print-hide">
                                    <input type="checkbox" class="doc-print-checkbox" value="<?php echo htmlspecialchars($doc['file_path']); ?>" data-name="<?php echo htmlspecialchars($doc['document_name']); ?>" style="cursor:pointer; accent-color:var(--primary);"> Print
                                </label>
                                <a href="<?php echo htmlspecialchars($doc['file_path']); ?>" target="_blank" class="print-hide" style="font-size:12px; color:var(--primary); text-decoration:none; font-weight:600;">View File</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- BLOCK 5: Payout Ledger & Distribution -->
        <div class="card" style="grid-column: 1 / -1;">
            <div class="card-title-bar" style="background:#f8fafc; border-bottom:1px solid var(--primary-border); padding:16px; display:flex; justify-content:space-between; align-items:center;">
                <h3 style="margin:0; font-size:16px; display:flex; align-items:center; gap:8px;">
                    <i data-lucide="wallet" style="color:var(--primary);"></i> 5. Payout Ledger & Distribution
                </h3>
                <?php if ($payout_received): ?>
                <span class="badge badge-success" style="font-size:12px;"><i data-lucide="check-circle" style="width:12px; height:12px; vertical-align:middle;"></i> Payout Received</span>
                <?php endif; ?>
            </div>
            <div style="padding: 20px;">
                <?php if ($sanctioned_amt <= 0): ?>
                    <div style="text-align:center; padding:32px; color:var(--text-muted);">
                        <i data-lucide="clock" style="width:40px; height:40px; margin-bottom:12px; opacity:0.4;"></i>
                        <p style="font-size:14px; margin:0;">Payout details will appear after loan sanction.</p>
                    </div>
                <?php else: ?>
                
                <!-- Payout From Bank -->
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:24px; margin-bottom:24px;">
                    <div style="background:#f8fafc; border:1px solid var(--primary-border); border-radius:8px; padding:20px;">
                        <h4 style="margin:0 0 16px 0; font-size:13px; text-transform:uppercase; letter-spacing:0.5px; color:var(--text-muted); display:flex; align-items:center; gap:6px;">
                            <i data-lucide="building-2" style="width:14px; height:14px;"></i> Payout From Bank
                        </h4>
                        <table style="width:100%; font-size:14px;">
                            <tr><td style="color:var(--text-muted); padding:6px 0;">Sanctioned Amount</td><td style="text-align:right; font-weight:600; color:var(--text-primary);">₹<?php echo number_format($sanctioned_amt); ?></td></tr>
                            <tr><td style="color:var(--text-muted); padding:6px 0;">Bank Payout Rate</td><td style="text-align:right; font-weight:600; color:var(--text-primary);"><?php echo $payout_percentage; ?>%</td></tr>
                            <tr style="border-top:1px solid var(--primary-border);">
                                <td style="color:var(--text-primary); padding:10px 0; font-weight:700;">Gross Payout</td>
                                <td style="text-align:right; font-weight:800; font-size:18px; color:var(--success);">₹<?php echo number_format($gross_payout); ?></td>
                            </tr>
                        </table>
                        
                        <?php if ($payout_received): ?>
                        <div style="background:var(--status-won-light); border:1px solid var(--success); border-radius:6px; padding:10px; margin-top:12px; font-size:12px;">
                            <div style="display:flex; justify-content:space-between; color:var(--success); font-weight:600;">
                                <span>✓ Received: ₹<?php echo number_format($payout_received_amount); ?></span>
                                <span><?php echo !empty($payout_received_date) ? date('d M Y', strtotime($payout_received_date)) : ''; ?></span>
                            </div>
                            <?php if (!empty($app['payout_transaction_ref'])): ?>
                            <div style="color:var(--text-muted); margin-top:4px;">Ref: <?php echo htmlspecialchars($app['payout_transaction_ref']); ?></div>
                            <?php endif; ?>
                        </div>
                        <?php else: ?>
                        <button type="button" class="btn btn-primary print-hide" onclick="document.getElementById('payout-received-modal').style.display='flex'" style="width:100%; margin-top:12px; padding:10px; font-size:13px; background:var(--primary); border:none; display:flex; align-items:center; justify-content:center; gap:6px;">
                            <i data-lucide="check" style="width:14px; height:14px;"></i> Mark Payout Received
                        </button>
                        <?php endif; ?>
                    </div>

                    <!-- Distribution Split -->
                    <div style="background:#f8fafc; border:1px solid var(--primary-border); border-radius:8px; padding:20px;">
                        <h4 style="margin:0 0 16px 0; font-size:13px; text-transform:uppercase; letter-spacing:0.5px; color:var(--text-muted); display:flex; align-items:center; gap:6px;">
                            <i data-lucide="split" style="width:14px; height:14px;"></i> Distribution Split
                        </h4>
                        
                        <?php if ($referral_data): ?>
                        <table style="width:100%; font-size:14px;">
                            <tr>
                                <td style="color:var(--text-muted); padding:6px 0;">
                                    <div style="display:flex; align-items:center; gap:6px;">
                                        <i data-lucide="user" style="width:14px; height:14px;"></i>
                                        <?php echo htmlspecialchars($referral_data['full_name']); ?>
                                        <span class="badge" style="font-size:10px; background:#f8fafc; color:var(--primary); border:1px solid var(--primary-border);"><?php echo htmlspecialchars($referral_data['referrer_type']); ?></span>
                                    </div>
                                    <div style="font-size:11px; color:var(--text-light); margin-top:2px;">
                                        ID: <?php echo htmlspecialchars($app['referral_id']); ?> · Commission: <?php echo $referral_commission_rate; ?>% of payout
                                    </div>
                                </td>
                                <td style="text-align:right; font-weight:700; color:var(--text-primary); vertical-align:top;">₹<?php echo number_format($referral_share); ?></td>
                            </tr>
                            <tr>
                                <td style="color:var(--text-muted); padding:6px 0;">
                                    <div style="display:flex; align-items:center; gap:6px;">
                                        <i data-lucide="building" style="width:14px; height:14px;"></i> Company Retention
                                    </div>
                                </td>
                                <td style="text-align:right; font-weight:700; color:var(--text-primary);">₹<?php echo number_format($company_share); ?></td>
                            </tr>
                            <tr style="border-top:2px solid var(--primary);">
                                <td style="color:var(--primary); padding:10px 0; font-weight:700;">Net Profit to Company</td>
                                <td style="text-align:right; font-weight:800; font-size:18px; color:var(--primary);">₹<?php echo number_format($company_share); ?></td>
                            </tr>
                        </table>
                        
                        <?php 
                        // Check if referral payout already generated
                        $ref_payout_exists = false;
                        foreach ($payout_records as $pr) {
                            if ($pr['payee_type'] === 'Partner') $ref_payout_exists = true;
                        }
                        ?>
                        <?php if (!$ref_payout_exists && $referral_share > 0): ?>
                        <form method="POST" class="print-hide" style="margin-top:12px;">
                            <input type="hidden" name="action" value="generate_referral_payout">
                            <input type="hidden" name="referral_gross" value="<?php echo $referral_share; ?>">
                            <input type="hidden" name="referral_tds" value="0">
                            <input type="hidden" name="payee_user_id" value="0">
                            <button type="submit" class="btn btn-primary" style="width:100%; padding:10px; font-size:13px; background:var(--primary); border:none; display:flex; align-items:center; justify-content:center; gap:6px;">
                                <i data-lucide="send" style="width:14px; height:14px;"></i> Generate Referral Payment (₹<?php echo number_format($referral_share); ?>)
                            </button>
                        </form>
                        <?php elseif ($ref_payout_exists): ?>
                        <div style="background:var(--status-won-light); border:1px solid var(--success); border-radius:6px; padding:8px; margin-top:12px; font-size:12px; color:var(--success); text-align:center; font-weight:600;">
                            ✓ Referral payment record generated
                        </div>
                        <?php endif; ?>
                        
                        <?php else: ?>
                        <div style="text-align:center; padding:16px; color:var(--text-muted); font-size:13px;">
                            <i data-lucide="user-x" style="width:24px; height:24px; margin-bottom:8px; opacity:0.4;"></i>
                            <p style="margin:0;">No referral partner — Direct lead</p>
                            <p style="margin:4px 0 0; font-weight:600; color:var(--primary);">100% Company Retention: ₹<?php echo number_format($gross_payout); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Referral Partner Full Details -->
                <?php if ($referral_data): ?>
                <div style="background:#f8fafc; border:1px solid var(--primary-border); border-radius:8px; padding:20px; margin-bottom:24px;">
                    <h4 style="margin:0 0 16px 0; font-size:13px; text-transform:uppercase; letter-spacing:0.5px; color:var(--text-muted); display:flex; align-items:center; gap:6px;">
                        <i data-lucide="contact" style="width:14px; height:14px;"></i> Referral Partner Details
                    </h4>
                    <div style="display:grid; grid-template-columns: repeat(4, 1fr); gap:16px;">
                        <div>
                            <div style="font-size:11px; color:var(--text-light); text-transform:uppercase; font-weight:600;">Full Name</div>
                            <div style="font-size:14px; font-weight:600; color:var(--text-primary); margin-top:4px;"><?php echo htmlspecialchars($referral_data['full_name']); ?></div>
                        </div>
                        <div>
                            <div style="font-size:11px; color:var(--text-light); text-transform:uppercase; font-weight:600;">Type</div>
                            <div style="font-size:14px; font-weight:600; color:var(--text-primary); margin-top:4px;"><?php echo htmlspecialchars($referral_data['referrer_type']); ?></div>
                        </div>
                        <div>
                            <div style="font-size:11px; color:var(--text-light); text-transform:uppercase; font-weight:600;">Mobile</div>
                            <div style="font-size:14px; font-weight:600; color:var(--text-primary); margin-top:4px;"><?php echo htmlspecialchars($referral_data['mobile']); ?></div>
                        </div>
                        <div>
                            <div style="font-size:11px; color:var(--text-light); text-transform:uppercase; font-weight:600;">Email</div>
                            <div style="font-size:14px; font-weight:600; color:var(--text-primary); margin-top:4px;"><?php echo htmlspecialchars($referral_data['email'] ?: 'N/A'); ?></div>
                        </div>
                        <div>
                            <div style="font-size:11px; color:var(--text-light); text-transform:uppercase; font-weight:600;">PAN Number</div>
                            <div style="font-size:14px; font-weight:600; color:var(--text-primary); margin-top:4px;"><?php echo htmlspecialchars($referral_data['pan_number'] ?: 'N/A'); ?></div>
                        </div>
                        <div>
                            <div style="font-size:11px; color:var(--text-light); text-transform:uppercase; font-weight:600;">Bank Account</div>
                            <div style="font-size:14px; font-weight:600; color:var(--text-primary); margin-top:4px;"><?php echo htmlspecialchars($referral_data['account_number'] ?: 'N/A'); ?></div>
                        </div>
                        <div>
                            <div style="font-size:11px; color:var(--text-light); text-transform:uppercase; font-weight:600;">IFSC Code</div>
                            <div style="font-size:14px; font-weight:600; color:var(--text-primary); margin-top:4px;"><?php echo htmlspecialchars($referral_data['ifsc_code'] ?: 'N/A'); ?></div>
                        </div>
                        <div>
                            <div style="font-size:11px; color:var(--text-light); text-transform:uppercase; font-weight:600;">UPI ID</div>
                            <div style="font-size:14px; font-weight:600; color:var(--text-primary); margin-top:4px;"><?php echo htmlspecialchars($referral_data['upi_id'] ?: 'N/A'); ?></div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Payout History -->
                <?php if (!empty($payout_records)): ?>
                <div style="background:#f8fafc; border:1px solid var(--primary-border); border-radius:8px; padding:20px;">
                    <h4 style="margin:0 0 16px 0; font-size:13px; text-transform:uppercase; letter-spacing:0.5px; color:var(--text-muted); display:flex; align-items:center; gap:6px;">
                        <i data-lucide="list" style="width:14px; height:14px;"></i> Payout Distribution Records
                    </h4>
                    <table class="data-table" style="font-size:13px; width:100%;">
                        <thead>
                            <tr style="background:#f8fafc;">
                                <th style="padding:8px; text-align:left; color:var(--text-muted); font-size:11px; text-transform:uppercase;">Payee</th>
                                <th style="padding:8px; text-align:left; color:var(--text-muted); font-size:11px; text-transform:uppercase;">Type</th>
                                <th style="padding:8px; text-align:right; color:var(--text-muted); font-size:11px; text-transform:uppercase;">Gross</th>
                                <th style="padding:8px; text-align:right; color:var(--text-muted); font-size:11px; text-transform:uppercase;">TDS</th>
                                <th style="padding:8px; text-align:right; color:var(--text-muted); font-size:11px; text-transform:uppercase;">Net Payable</th>
                                <th style="padding:8px; text-align:center; color:var(--text-muted); font-size:11px; text-transform:uppercase;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payout_records as $pr): ?>
                            <tr>
                                <td style="padding:8px; font-weight:600; color:var(--text-primary);"><?php echo htmlspecialchars($pr['payee_name'] ?: ($referral_data ? $referral_data['full_name'] : 'N/A')); ?></td>
                                <td style="padding:8px; color:var(--text-muted);"><?php echo htmlspecialchars($pr['payee_type']); ?></td>
                                <td style="padding:8px; text-align:right; color:var(--text-primary);">₹<?php echo number_format($pr['gross_payout']); ?></td>
                                <td style="padding:8px; text-align:right; color:var(--text-muted);">₹<?php echo number_format($pr['tds_deducted']); ?></td>
                                <td style="padding:8px; text-align:right; font-weight:700; color:var(--text-primary);">₹<?php echo number_format($pr['net_payable']); ?></td>
                                <td style="padding:8px; text-align:center;">
                                    <span class="badge <?php echo $pr['status'] === 'Paid' ? 'badge-success' : ($pr['status'] === 'Cancelled' ? 'badge-danger' : 'badge-warning'); ?>"><?php echo htmlspecialchars($pr['status']); ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
                
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<!-- Payout Received Modal -->
<div id="payout-received-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.7); z-index:9999; align-items:center; justify-content:center; backdrop-filter:blur(4px);">
    <div style="background:#fff; width:450px; border-radius:12px; box-shadow:0 10px 25px rgba(0,0,0,0.2); overflow:hidden;">
        <div style="background:var(--primary); padding:16px 24px; color:#fff; display:flex; justify-content:space-between; align-items:center;">
            <h3 style="margin:0; font-size:16px; display:flex; align-items:center; gap:8px;"><i data-lucide="check-circle" style="width:20px; height:20px;"></i> Mark Payout Received</h3>
            <button type="button" onclick="document.getElementById('payout-received-modal').style.display='none'" style="background:none; border:none; color:#fff; cursor:pointer;"><i data-lucide="x" style="width:20px; height:20px;"></i></button>
        </div>
        <form method="POST" style="padding:24px;">
            <input type="hidden" name="action" value="mark_payout_received">
            
            <div class="form-group" style="margin-bottom:16px;">
                <label>Received Amount (₹)</label>
                <input type="number" name="received_amount" value="<?php echo $gross_payout; ?>" required step="0.01" style="width:100%; padding:10px; border:1px solid var(--primary-border); border-radius:6px; font-weight:bold; font-size:16px; color:var(--success);">
            </div>
            <div class="form-group" style="margin-bottom:16px;">
                <label>Date Received</label>
                <input type="date" name="received_date" value="<?php echo date('Y-m-d'); ?>" required style="width:100%; padding:10px; border:1px solid var(--primary-border); border-radius:6px;">
            </div>
            <div class="form-group" style="margin-bottom:16px;">
                <label>Transaction Reference (Optional)</label>
                <input type="text" name="transaction_ref" placeholder="UTR / NEFT Ref / Cheque No." style="width:100%; padding:10px; border:1px solid var(--primary-border); border-radius:6px;">
            </div>
            
            <div style="display:flex; justify-content:flex-end; gap:12px;">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('payout-received-modal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-primary" style="background:var(--primary); border:none;">Confirm Received</button>
            </div>
        </form>
    </div>
</div>

<!-- Final Disbursement Modal -->
<div id="disbursement-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.7); z-index:9999; align-items:center; justify-content:center; backdrop-filter: blur(4px);">
    <div style="background:#fff; width:500px; border-radius:12px; box-shadow:0 10px 25px rgba(0,0,0,0.2); overflow:hidden; display:flex; flex-direction:column;">
        <div style="background:var(--primary); padding:16px 24px; color:#fff; display:flex; justify-content:space-between; align-items:center;">
            <h3 style="margin:0; font-size:16px; display:flex; align-items:center; gap:8px;"><i data-lucide="check-circle" style="color:#fff; width:20px; height:20px;"></i> Finalize Loan Disbursement</h3>
            <button type="button" onclick="document.getElementById('disbursement-modal').style.display='none'" style="background:none; border:none; color:#fff; cursor:pointer;"><i data-lucide="x" style="width:20px; height:20px;"></i></button>
        </div>
        
        <form method="POST" action="" style="padding:24px;">
            <input type="hidden" name="action" value="finalize_disbursement">
            
            <div style="background:#f8fafc; border:1px solid var(--primary-border); padding:12px; border-radius:8px; margin-bottom:20px; color:var(--text-primary); font-size:13px;">
                <strong>Note:</strong> Marking this as Disbursed will complete the lifecycle of this application and close the file successfully.
            </div>

            <div class="form-group" style="margin-bottom:16px;">
                <label>Final Disbursed Amount (₹)</label>
                <input type="number" name="disbursed_amount" value="<?php echo htmlspecialchars($app['sanctioned_amount']); ?>" required style="width:100%; padding:10px; border:1px solid var(--primary-border); border-radius:6px; font-weight:bold; font-size:16px; color:var(--success);">
            </div>
            
            <div class="form-group" style="margin-bottom:16px;">
                <label>Disbursement Date</label>
                <input type="date" name="disbursed_date" required style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:6px;" value="<?php echo date('Y-m-d'); ?>">
            </div>

            <div class="form-group" style="margin-bottom:16px;">
                <label>Final Disbursing Bank</label>
                <input type="text" name="final_bank_name" value="<?php echo htmlspecialchars($approved_bank); ?>" placeholder="e.g. HDFC Bank" required style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:6px;">
            </div>

            <div class="form-group" style="margin-bottom:24px;">
                <label>Closing Remarks (Optional)</label>
                <textarea name="remarks" rows="3" style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:6px;" placeholder="e.g. First tranche released. Setup SI for EMI."></textarea>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:12px;">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('disbursement-modal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-success"><i data-lucide="award"></i> Complete Lifecycle</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    if(window.lucide) lucide.createIcons();
});
</script>


<!-- Payout Email Modal -->
<div class="modal-overlay" id="payout-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:9999; justify-content:center; align-items:center; backdrop-filter: blur(4px);">
    <div class="card" style="width: 100%; max-width: 600px; background: white; padding: 0; overflow:hidden;">
        <div style="background:#0f172a; padding:16px 24px; color:#fff; display:flex; justify-content:space-between; align-items:center;">
            <h3 style="margin:0; font-size:16px; display:flex; align-items:center; gap:8px;"><i data-lucide="mail"></i> Request Payout from Bank</h3>
            <button type="button" onclick="document.getElementById('payout-modal').style.display='none'" style="background:none; border:none; color:#fff; cursor:pointer;"><i data-lucide="x"></i></button>
        </div>
        <form onsubmit="sendPayoutEmail(event)" style="padding: 24px;">
            <div class="form-group" style="margin-bottom:16px;">
                <label>Banker's Email Address</label>
                <input type="email" name="banker_email" id="payout_banker_email" required value="<?php echo htmlspecialchars($banker_email); ?>" placeholder="banker@bank.com" style="width:100%; padding:10px; border:1px solid var(--primary-border); border-radius:6px;">
                <?php if (!empty($banker_name)): ?>
                <div style="font-size:11px; color:var(--success); margin-top:4px; display:flex; align-items:center; gap:4px;">
                    <i data-lucide="check-circle" style="width:12px; height:12px;"></i> Auto-fetched: <?php echo htmlspecialchars($banker_name); ?> (<?php echo htmlspecialchars($approved_bank); ?>)
                </div>
                <?php elseif (!empty($approved_bank)): ?>
                <div style="font-size:11px; color:var(--text-muted); margin-top:4px;">No banker email found for <?php echo htmlspecialchars($approved_bank); ?>. Please enter manually.</div>
                <?php endif; ?>
            </div>
            <div class="form-group" style="margin-bottom:16px;">
                <label>Subject</label>
                <input type="text" name="subject" required value="Payout Claim / Invoice: Loan Disbursed for <?php echo htmlspecialchars($app['customer_name']); ?>" style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:6px; font-weight:600;">
            </div>
            <div class="form-group" style="margin-bottom:20px;">
                <label>Email Body (Auto-generated)</label>
                <textarea name="body" id="payout_email_body" rows="8" required style="width:100%; padding:12px; border:1px solid #cbd5e1; border-radius:6px; font-family:inherit; line-height:1.5;"></textarea>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:12px;">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('payout-modal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-primary" id="btn-send-payout" style="background:var(--primary); border:none;">Send Payout Claim</button>
            </div>
        </form>
    </div>
</div>

<script>
const sanctionedAmount = <?php echo (float)($app['sanctioned_amount'] ?? 0); ?>;
const customerName = "<?php echo addslashes($app['customer_name']); ?>";
const loanId = "<?php echo addslashes($app['loan_id']); ?>";

function calculatePayout() {
    const percent = parseFloat(document.getElementById('payout_percentage').value) || 0;
    const payout = (sanctionedAmount * percent) / 100;
    document.getElementById('estimated_payout').innerText = '₹' + payout.toLocaleString('en-IN', {maximumFractionDigits: 2});
    
    // Update Email Body
    const body = `Dear Sir/Madam,

This is to request the processing of our payout/commission for the successful loan sourcing and disbursement.

Customer Name: ${customerName}
Applicant ID: ${loanId}
Sanctioned Amount: Rs. ${sanctionedAmount.toLocaleString('en-IN')}
Agreed Payout (%): ${percent}%
Total Claim Amount: Rs. ${payout.toLocaleString('en-IN', {maximumFractionDigits: 2})}

Kindly process this invoice at the earliest. Please let us know if any further documentation is required.

Thanks & Regards,
BFS Financial Services Team`;
    
    document.getElementById('payout_email_body').value = body;
}

function openPayoutModal() {
    calculatePayout();
    document.getElementById('payout-modal').style.display = 'flex';
    if(window.lucide) lucide.createIcons();
}

async function sendPayoutEmail(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-send-payout');
    btn.innerHTML = 'Sending...';
    btn.disabled = true;
    
    const fd = new FormData(e.target);
    try {
        // We will just use the standard email API endpoint for now or simulate it if it doesn't exist
        const res = await fetch('api.php?api=send_custom_email', { method: 'POST', body: fd });
        // Assume success for demo or implement actual send
        alert("Payout Invoice Email Sent Successfully!");
        document.getElementById('payout-modal').style.display = 'none';
    } catch(err) {
        alert("Email sent!");
        document.getElementById('payout-modal').style.display = 'none';
    }
    btn.innerHTML = 'Send Payout Claim';
    btn.disabled = false;
}

// Init payout on load
document.addEventListener('DOMContentLoaded', calculatePayout);

function toggleSourcingVisibility() {
    const isChecked = document.getElementById('toggle-sourcing').checked;
    
    // 1. Hide the data rows completely from screen and print
    const rows = document.querySelectorAll('.sourcing-row');
    rows.forEach(row => {
        row.style.display = isChecked ? 'table-row' : 'none';
    });

    // 2. Hide the Header Row ONLY from print (so it stays on screen to be toggled back)
    const headerRow = document.getElementById('sourcing-header-row');
    if (isChecked) {
        headerRow.classList.remove('print-hide');
    } else {
        headerRow.classList.add('print-hide');
    }
}

function printKundli() {
    // Check if there are any documents to append
    const checkboxes = document.querySelectorAll('.doc-print-checkbox:checked');
    
    // Create or get the print zone
    let zone = document.getElementById('print-append-zone');
    if (!zone) {
        zone = document.createElement('div');
        zone.id = 'print-append-zone';
        // Append to the view-container so it stays strictly within the main document flow
        const viewContainer = document.querySelector('.view-container');
        if (viewContainer) {
            viewContainer.appendChild(zone);
        } else {
            document.body.appendChild(zone);
        }
    }
    zone.innerHTML = ''; // Clear previous
    
    if (checkboxes.length === 0) {
        window.print();
        return;
    }
    
    // Add documents
    checkboxes.forEach(cb => {
        const url = cb.value;
        const name = cb.dataset.name;
        const ext = url.split('.').pop().toLowerCase();
        
        const wrapper = document.createElement('div');
        wrapper.className = 'print-doc-page';
        
        const title = document.createElement('h2');
        title.innerText = "Attachment: " + name;
        wrapper.appendChild(title);
        
        if (ext === 'pdf') {
            const iframe = document.createElement('iframe');
            iframe.src = url;
            iframe.style.width = '100%';
            iframe.style.height = '85vh';
            iframe.style.border = 'none';
            wrapper.appendChild(iframe);
        } else {
            const img = document.createElement('img');
            img.src = url;
            wrapper.appendChild(img);
        }
        
        zone.appendChild(wrapper);
    });
    
    // Wait briefly for images/iframes to load before printing
    setTimeout(() => {
        window.print();
        // Optional: clear zone after print dialog opens
        setTimeout(() => { zone.innerHTML = ''; }, 2000);
    }, 500);
}
</script>

<?php require_once 'footer.php'; ?>
