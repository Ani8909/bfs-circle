<?php
require_once 'config.php';
$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header("Location: applicants_list.php");
    exit;
}

$stmt = $db->prepare("SELECT * FROM applicants WHERE id = ?");
$stmt->execute([$id]);
$applicant = $stmt->fetch();

if (!$applicant) {
    die("Applicant not found.");
}

// Get distinct bank names
$stmt_banks = $db->query("SELECT DISTINCT bank_name FROM bankers WHERE bank_name IS NOT NULL AND bank_name != '' ORDER BY bank_name");
$banks = $stmt_banks->fetchAll(PDO::FETCH_COLUMN);

// Get document count for the modal
$doc_stmt = $db->prepare("SELECT COUNT(*) FROM applicant_documents WHERE applicant_id = ?");
$doc_stmt->execute([$id]);
$doc_count = $doc_stmt->fetchColumn();

// Get bank assignments
$bank_stmt = $db->prepare("SELECT * FROM applicant_bank_assignments WHERE applicant_id = ? ORDER BY id DESC");
$bank_stmt->execute([$id]);
$bank_assignments = $bank_stmt->fetchAll(PDO::FETCH_ASSOC);


$page_title = 'Bank Processing & Sanction';
$page_subtitle = ' Phase 3: Send file to Bank & Update Sanction Details';
require_once 'header.php';
?>
<style>
/* Premium SaaS UI for Phase 3 */
body {
    background-color: #f1f5f9;
}
.view-container {
    padding: 32px 40px;
    max-width: 1400px;
    margin: 0 auto;
}
.card {
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid rgba(226, 232, 240, 0.8);
    box-shadow: 0 4px 20px -4px rgba(0,0,0,0.05), 0 2px 8px -2px rgba(0,0,0,0.02);
    padding: 24px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.card:hover {
    box-shadow: 0 10px 30px -4px rgba(0,0,0,0.08), 0 4px 12px -2px rgba(0,0,0,0.04);
}
.card-title-bar {
    border-bottom: 1px solid #f1f5f9;
    padding-bottom: 16px;
    margin-bottom: 20px;
}
.card-title-bar h3 {
    font-size: 17px;
    font-weight: 700;
    color: #0f172a;
    letter-spacing: -0.02em;
}

/* Premium Buttons */
.btn-primary {
    background: linear-gradient(135deg, #0f172a 0%, #000000 100%);
    border: none;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    color: white;
    font-weight: 600;
    border-radius: 10px;
    transition: all 0.3s ease;
}
.btn-primary:hover {
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.3);
    transform: translateY(-1px);
}
.btn-secondary {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    color: #475569;
    font-weight: 600;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    transition: all 0.2s ease;
}
.btn-secondary:hover {
    background: #f8fafc;
    border-color: #94a3b8;
    color: #0f172a;
}
.btn-proceed {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    border: none;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);
    font-weight: 700;
    font-size: 14px;
    border-radius: 10px;
    padding: 14px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}
.btn-proceed:hover {
    box-shadow: 0 6px 16px rgba(16, 185, 129, 0.35);
    transform: translateY(-2px);
    color: white;
}

/* Modern Data Table */
.data-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}
.data-table th {
    background: #f8fafc;
    color: #64748b;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    padding: 12px 16px;
    border-bottom: 1px solid #e2e8f0;
}
.data-table td {
    padding: 16px;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
    color: #334155;
}
.data-table tbody tr {
    transition: background 0.2s;
}
.data-table tbody tr:hover {
    background: #fcfcfc;
}

/* Form Inputs */
.form-group input, .form-group select, .form-group textarea {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 12px 16px;
    font-size: 14px;
    color: #0f172a;
    transition: all 0.2s ease;
}
.form-group input:focus, .form-group select:focus, .form-group textarea:focus {
    background: #ffffff;
    border-color: #0f172a;
    box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.1);
    outline: none;
}
.form-group label {
    font-size: 12px;
    font-weight: 600;
    color: #475569;
    margin-bottom: 6px;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}
</style>
<?php
?>

<div id="view-applicant-bank" class="view-container">
    <div style="margin-bottom: 20px;">
        <a href="search_track.php" class="btn btn-secondary" style="display:inline-flex; align-items:center; gap:8px;"><i data-lucide="arrow-left" style="width:16px;"></i> Back to CRM Track</a>
    </div>
    <div class="dashboard-layout-row" style="grid-template-columns: 1fr 2fr; gap: 32px;">
        
        <!-- Applicant Summary Side Panel -->
        <div class="card" style="align-self: start;">
            <div class="card-title-bar">
                <h3>Applicant Profile</h3>
                <div class="badge-status negotiation">Phase 3</div>
            </div>
            <div style="margin-top: 16px;">
                <div style="font-size: 20px; font-weight: 700; color: var(--text-primary); margin-bottom: 4px;">
                    <?php echo htmlspecialchars($applicant['customer_name']); ?>
                </div>
                <div style="color: var(--text-muted); font-size: 13px; margin-bottom: 16px;">
                    Applicant ID: <strong><?php echo htmlspecialchars($applicant['loan_id']); ?></strong>
                </div>
                
                <div style="background: #f8fafc; border-radius: 12px; padding: 16px; margin-top: 20px;">
                    <div style="display:flex; justify-content:space-between; margin-bottom: 12px; border-bottom: 1px dashed #cbd5e1; padding-bottom: 12px;">
                        <span style="color:#64748b; font-size:12px; text-transform:uppercase; font-weight:600;">Loan Type</span>
                        <span style="font-weight:600; color:#0f172a; font-size:13px;"><?php echo htmlspecialchars($applicant['loan_type']); ?></span>
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-bottom: 12px; border-bottom: 1px dashed #cbd5e1; padding-bottom: 12px;">
                        <span style="color:#64748b; font-size:12px; text-transform:uppercase; font-weight:600;">Sub-Type</span>
                        <span style="font-weight:600; color:#0f172a; font-size:13px;"><?php echo htmlspecialchars($applicant['loan_sub_type']); ?></span>
                    </div>
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <span style="color:#64748b; font-size:12px; text-transform:uppercase; font-weight:600;">Req. Amount</span>
                        <span style="font-weight:800; color:#000000; font-size:18px;">₹<?php echo number_format($applicant['loan_amount_requested'] ?? 0); ?></span>
                    </div>
                </div>
            </div>

            <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--border);">
                <h4 style="margin-bottom:10px; font-size:13px; color:var(--text-muted);">Share File with Bank</h4>
                <div style="display:flex; flex-direction:column; gap:12px;">
                    <a href="generate_zip.php?id=<?php echo $id; ?>" target="_blank" class="btn btn-primary" style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px; padding: 12px;">
                        <i data-lucide="download" style="width:18px;"></i> Download Bundle (ZIP)
                    </a>
                    <button type="button" class="btn btn-secondary" onclick="openEmailModal()" style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px; padding: 12px; background: #f8fafc;">
                        <i data-lucide="mail" style="width:18px; color: #3b82f6;"></i> <span style="color: #334155;">Email directly to Banker</span>
                    </button>
                </div>
                <div style="font-size:11px; color:var(--text-muted); margin-top:8px; line-height: 1.4;">
                    Downloads a single ZIP file containing the applicant's profile details and all verified uploaded documents.
                </div>
            </div>

<?php
            $has_approved_bank = false;
            foreach ($bank_assignments as $ba) {
                if ($ba['status'] === 'Approved') {
                    $has_approved_bank = true;
                    break;
                }
            }
            ?>
            <div style="margin-top: 24px;">
                <button type="button" onclick="verifyAndProceed()" class="btn btn-proceed" style="width: 100%; text-align: center; display: block;">
                    Proceed to Phase 4 (Disbursements) &rarr;
                </button>
            </div>
        </div>

        <!-- Bank Feedback Form & Tracking -->
        <div style="display:flex; flex-direction:column; gap:24px;">
            <!-- Bank Submissions Tracking -->
            <div class="card">
                <div class="card-title-bar" style="display:flex; justify-content:space-between; align-items:center;">
                    <h3>Bank Submissions History</h3>
                    <button class="btn btn-secondary" onclick="document.getElementById('manual-bank-modal').style.display='flex';" style="padding: 6px 12px; font-size: 13px;">+ Log Submission</button>
                </div>
                <div style="background: #f0fdf4; padding: 14px 16px; border-radius: 10px; border: 1px solid #fde68a; margin-bottom: 20px; display:flex; gap:12px; align-items:center;">
                    <i data-lucide="shield-alert" style="color: #15803d; width: 20px; height: 20px; flex-shrink:0;"></i>
                    <p style="font-size: 13px; color: #92400e; margin:0; line-height:1.5;"><strong>Pro Tip:</strong> Keep track of all bank submissions here. If a bank rejects the file, update its status before sending it to a new bank.</p>
                </div>
                
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Bank / Assigned To</th>
                                <th>Status</th>
                                <th>Reason / Remarks</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($bank_assignments)): ?>
                            <tr><td colspan="4" style="text-align:center; color:var(--text-light); padding:20px;">No bank submissions yet. Use 'Email Banker' or 'Log Submission'.</td></tr>
                            <?php else: foreach($bank_assignments as $ba): ?>
                            <tr>
                                <td style="font-weight:600; font-size:13px;"><?php echo htmlspecialchars($ba['bank_name']); ?><br><span style="font-weight:400; font-size:11px; color:var(--text-light);"><?php echo date('d M Y', strtotime($ba['created_at'] ?? date('Y-m-d H:i:s'))); ?></span></td>
                                <td>
                                    <?php 
                                        $badge = 'badge-warning';
                                        if($ba['status'] == 'Approved') $badge = 'badge-success';
                                        if($ba['status'] == 'Rejected' || $ba['status'] == 'Customer Rejected') $badge = 'badge-danger';
                                    ?>
                                    <span class="badge <?php echo $badge; ?>"><?php echo htmlspecialchars($ba['status']); ?></span>
                                </td>
                                <td style="font-size:12px; max-width:200px;"><?php echo htmlspecialchars($ba['rejection_reason'] ?: '-'); ?></td>
                                <td>
                                    <button class="btn btn-secondary" style="padding: 4px 8px; font-size: 11px;" onclick="openUpdateStatusModal(<?php echo $ba['id']; ?>, '<?php echo $ba['status']; ?>', '<?php echo htmlspecialchars(addslashes($ba['rejection_reason'])); ?>')">Update Status</button>
                                </td>
                            </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Final Sanction Form -->
            <div class="card">
                <div class="card-title-bar">
                    <h3>Final Banker Feedback & Sanction</h3>
                    <i data-lucide="landmark" style="color: var(--primary);"></i>
                </div>
            
            <div style="background: #f0f9ff; padding: 16px; border-radius: 12px; border: 1px solid #bae6fd; margin-bottom: 24px; border-left: 4px solid #0ea5e9;">
                <h4 style="color: #0369a1; margin-bottom: 8px; font-size: 14px; display:flex; align-items:center; gap:6px;"><i data-lucide="info" style="width:18px; height:18px;"></i> Final Sanction Details</h4>
                <p style="font-size: 13px; color: #0c4a6e; margin:0; line-height: 1.6;">
                    Fill this out <strong>only when a bank has given final approval</strong>. These details will be locked in and shown to the customer/partner in the next phase.
                </p>
            </div>

            <form id="bank-feedback-form" onsubmit="saveBankFeedback(event)">
                <input type="hidden" name="applicant_id" value="<?php echo $id; ?>">
                
                <div class="form-grid" style="grid-template-columns: 1fr 1fr;">
                    <div class="form-group">
                        <label class="required">CIBIL Score</label>
                        <input type="number" name="cibil_score" id="cibil_score" placeholder="e.g. 750" value="<?php echo htmlspecialchars($applicant['cibil_score'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="required">Sanctioned Amount (₹)</label>
                        <input type="number" name="sanctioned_amount" id="sanctioned_amount" placeholder="e.g. 450000" value="<?php echo htmlspecialchars($applicant['sanctioned_amount'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="required">Rate of Interest (ROI %)</label>
                        <input type="number" step="0.01" name="interest_rate" id="interest_rate" placeholder="e.g. 8.5" value="<?php echo htmlspecialchars($applicant['interest_rate'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="required">Approved Tenure (Months)</label>
                        <input type="number" name="tenure_months" id="tenure_months" placeholder="e.g. 120" value="<?php echo htmlspecialchars($applicant['tenure_months'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Approved EMI (₹)</label>
                        <input type="number" step="0.01" name="emi" id="emi" placeholder="e.g. 5600" value="<?php echo htmlspecialchars($applicant['emi'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="required">Sanction Date</label>
                        <input type="date" name="sanction_date" id="sanction_date" required value="<?php echo htmlspecialchars($applicant['sanction_date'] ?? date('Y-m-d')); ?>">
                    </div>
                </div>
                
                <div class="form-actions" style="margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--border);">
                    <button type="submit" class="btn btn-primary" id="save-feedback-btn"><i data-lucide="save"></i> Save Bank Feedback</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    
    function openEmailModal() {
        document.getElementById('email-modal').style.display = 'flex';
        if(window.lucide) lucide.createIcons();
    }
    
    
    function closeEmailModalOutside(event) {
        // If the user clicked exactly on the overlay (not inside the modal content)
        if (event.target.id === 'email-modal') {
            closeEmailModal();
        }
    }

    function closeEmailModal() {
        document.getElementById('email-modal').style.display = 'none';
    }
    
    async function sendBankerEmail(e) {
        e.preventDefault();
        if (CKEDITOR.instances.modal_body) {
            CKEDITOR.instances.modal_body.updateElement();
        }
        const form = document.getElementById('email-banker-form');
        const formData = new FormData(form);
        const btn = document.getElementById('send-email-btn');
        
        btn.disabled = true;
        btn.innerHTML = 'Generating ZIP & Sending...';
        
        try {
            const res = await fetch('?api=email_banker_zip', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();
            
            if (res.ok && data.success) {
                showNotification(data.message, 'success');
                closeEmailModal();
                form.reset();
            } else {
                showNotification(data.error || 'Failed to send email', 'error');
            }
        } catch(err) {
            showNotification('Network error', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = 'Send Email (with ZIP)';
        }
    }

    async function saveBankFeedback(e) {
        e.preventDefault();
        const form = document.getElementById('bank-feedback-form');
        const formData = new FormData(form);
        const btn = document.getElementById('save-feedback-btn');
        
        btn.disabled = true;
        btn.innerHTML = 'Saving...';
        
        try {
            const res = await fetch('?api=save_bank_feedback', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();
            
            if (res.ok && data.success) {
                showNotification(data.message, 'success');
            } else {
                showNotification(data.error || 'Failed to save', 'error');
            }
        } catch(err) {
            showNotification('Network error', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i data-lucide="save"></i> Save Bank Feedback';
            lucide.createIcons();
        }
    }

    
</script>


<!-- Detailed Email Modal -->
<div id="email-modal" onclick="closeEmailModalOutside(event)" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.6); z-index:9999; align-items:center; justify-content:center; backdrop-filter: blur(4px);">
    <div id="email-modal-content" style="background:#fff; width:700px; min-width:500px; min-height:400px; border-radius:12px; box-shadow:0 10px 25px rgba(0,0,0,0.2); overflow:hidden; display:flex; flex-direction:column; max-height: 90vh; max-width: 95vw; resize:both;">
        
        <!-- Header -->
        <div style="background:var(--primary); padding:16px 24px; color:#fff; display:flex; justify-content:space-between; align-items:center;">
            <h3 style="margin:0; font-size:16px; display:flex; align-items:center; gap:8px;">
                <i data-lucide="send" style="color:#fff; width:20px; height:20px;"></i> Dispatch File to Banker
            </h3>
            <button type="button" onclick="closeEmailModal()" style="background:none; border:none; color:#fff; cursor:pointer; padding:4px;"><i data-lucide="x" style="width:20px; height:20px;"></i></button>
        </div>

        <div style="padding:24px; overflow-y:auto; flex:1;">
            <!-- Attachment Preview Box -->
            <div style="background:#f8fafc; border:1px dashed #cbd5e1; border-radius:8px; padding:16px; margin-bottom:24px; display:flex; align-items:center; gap:16px;">
                <div style="background:#e2e8f0; width:48px; height:48px; border-radius:8px; display:flex; align-items:center; justify-content:center; color:#475569;">
                    <i data-lucide="file-archive" style="width:24px; height:24px;"></i>
                </div>
                <div>
                    <div style="font-weight:600; color:#1e293b; font-size:14px;">Bundle_<?php echo htmlspecialchars($applicant['loan_id']); ?>_<?php echo preg_replace('/[^A-Za-z0-9_-]/', '_', $applicant['customer_name']); ?>.zip</div>
                    <div style="font-size:12px; color:#64748b; margin-top:4px;">Auto-generated ZIP containing Applicant Profile Summary + <?php echo $doc_count; ?> Uploaded Documents</div>
                </div>
                <div style="margin-left:auto;">
                    <span class="badge badge-success" style="font-size:11px;">Ready to Attach</span>
                </div>
            </div>

            <form id="email-banker-form" onsubmit="sendBankerEmail(event)">
                <input type="hidden" name="applicant_id" value="<?php echo $id; ?>">
                
                <div class="form-grid" style="grid-template-columns: 1fr 1fr; gap:16px;">
                    <div class="form-group">
                        <label>Bank Name</label>
                        <select name="bank_name" id="modal_bank_name" onchange="updateSubjectAndBody()" style="width:100%; padding:10px; border:1px solid var(--border); border-radius:6px;">
                            <option value="">-- Select Bank --</option>
                            <?php foreach($banks as $b): ?>
                                <option value="<?php echo htmlspecialchars($b); ?>"><?php echo htmlspecialchars($b); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Banker's Name <span style="font-size:11px; color:#64748b;">(Optional)</span></label>
                        <input type="text" name="banker_name" id="modal_banker_name" onkeyup="updateSubjectAndBody()" placeholder="e.g. Mr. Sharma" style="width:100%; padding:10px; border:1px solid var(--border); border-radius:6px;">
                    </div>

                    <div class="form-group">
                        <label class="required">To (Banker's Email)</label>
                        <input type="email" name="banker_email" required placeholder="banker@bank.com" style="width:100%; padding:10px; border:1px solid var(--border); border-radius:6px;">
                    </div>
                    <div class="form-group">
                        <label>CC <span style="font-size:11px; color:#64748b;">(Optional)</span></label>
                        <input type="email" name="cc_email" placeholder="manager@bank.com" style="width:100%; padding:10px; border:1px solid var(--border); border-radius:6px;">
                    </div>
                    
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label class="required">Subject</label>
                        <input type="text" name="subject" id="modal_subject" required value="New <?php echo htmlspecialchars($applicant['loan_type']); ?> Application - <?php echo htmlspecialchars($applicant['customer_name']); ?> [ID: <?php echo htmlspecialchars($applicant['loan_id']); ?>]" style="width:100%; padding:10px; border:1px solid var(--border); border-radius:6px; font-weight:600;">
                    </div>
                    
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label class="required">Email Body</label>
                        <textarea name="body" id="modal_body" rows="7" required style="width:100%; padding:12px; border:1px solid var(--border); border-radius:6px; font-family:inherit; line-height:1.5;">Dear Sir/Madam,

Please find attached the bundled ZIP file containing the complete KYC and property documents for the loan application of <?php echo htmlspecialchars($applicant['customer_name']); ?>.

Applicant Name: <?php echo htmlspecialchars($applicant['customer_name']); ?>
Loan Type: <?php echo htmlspecialchars($applicant['loan_type']); ?>
Requested Amount: INR <?php echo number_format($applicant['loan_amount_requested'] ?? 0); ?>
Total Documents Attached: <?php echo $doc_count; ?>

Kindly review the file and let us know the sanction details.

Regards,
BFS Financial Services Sourcing Team</textarea>
                    </div>
                </div>
            </form>
        </div>

        <!-- Footer -->
        <div style="background:#f8fafc; padding:16px 24px; border-top:1px solid var(--border); display:flex; justify-content:flex-end; gap:12px;">
            <button type="button" class="btn btn-secondary" onclick="closeEmailModal()" style="padding:10px 20px;">Cancel</button>
            <button type="submit" form="email-banker-form" class="btn btn-primary" id="send-email-btn" style="padding:10px 24px; font-weight:600;"><i data-lucide="send" style="width:16px;height:16px;"></i> Dispatch Email</button>
        </div>
    </div>
</div>
<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        if(window.lucide) lucide.createIcons();
        CKEDITOR.replace('modal_body', {
            height: 200,
            versionCheck: false,
            toolbar: [
                ['Bold', 'Italic', 'Underline', 'Strike'],
                ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent'],
                ['Link', 'Unlink'],
                ['Format', 'Font', 'FontSize']
            ]
        });
    });

    function updateSubjectAndBody() {
        const bank = document.getElementById('modal_bank_name').value;
        const bName = document.getElementById('modal_banker_name').value;
        const subjEl = document.getElementById('modal_subject');
        const bodyEl = document.getElementById('modal_body');
        
        const cName = "<?php echo addslashes($applicant['customer_name']); ?>";
        const lType = "<?php echo addslashes($applicant['loan_type']); ?>";
        const lId = "<?php echo addslashes($applicant['loan_id']); ?>";
        const amt = "<?php echo number_format($applicant['loan_amount_requested'] ?? 0); ?>";
        const docs = "<?php echo $doc_count; ?>";

        let subj = `New ${lType} Application - ${cName} [ID: ${lId}]`;
        if (bank) subj += ` for ${bank}`;
        subjEl.value = subj;

        let salutation = bName ? `Dear ${bName},` : `Dear Sir/Madam,`;
        
        const newBody = `${salutation}<br><br>

Please find attached the bundled ZIP file containing the complete KYC and property documents for the loan application of <strong>${cName}</strong>.<br><br>

<strong>Applicant Name:</strong> ${cName}<br>
<strong>Loan Type:</strong> ${lType}<br>
<strong>Requested Amount:</strong> INR ${amt}<br>
<strong>Total Documents Attached:</strong> ${docs}<br><br>

Kindly review the file and let us know the sanction details.<br><br>

Regards,<br>
BFS Financial Services Sourcing Team`;

        if (CKEDITOR.instances.modal_body) {
            CKEDITOR.instances.modal_body.setData(newBody);
        } else {
            bodyEl.value = newBody.replace(/<br>/g, "\n").replace(/<[^>]+>/g, '');
        }
    }
</script>


<!-- Manual Bank Submission Modal -->
<div class="modal-overlay" id="manual-bank-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; justify-content:center; align-items:center;">
    <div class="card" style="width: 100%; max-width: 400px; background: white; padding: 24px;">
        <h3 style="margin-bottom:16px;">Log Bank Submission</h3>
        <form onsubmit="logBankSubmission(event)">
            <input type="hidden" name="applicant_id" value="<?php echo $id; ?>">
            <div class="form-group">
                <label>Bank Name</label>
                <select name="bank_name" required>
                    <option value="">Select Bank...</option>
                    <?php foreach($banks as $b): ?><option value="<?php echo htmlspecialchars($b); ?>"><?php echo htmlspecialchars($b); ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Initial Remarks</label>
                <input type="text" name="notes" placeholder="e.g. Sent physical file">
            </div>
            <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:20px;">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('manual-bank-modal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-primary">Log Submission</button>
            </div>
        </form>
    </div>
</div>

<!-- Update Status Modal -->
<div class="modal-overlay" id="update-status-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; justify-content:center; align-items:center;">
    <div class="card" style="width: 100%; max-width: 400px; background: white; padding: 24px;">
        <h3 style="margin-bottom:16px;">Update Bank Status</h3>
        <form onsubmit="updateBankStatus(event)">
            <input type="hidden" name="assignment_id" id="update-assignment-id">
            <div class="form-group">
                <label>Status</label>
                <select name="status" id="update-status-val" required>
                    <option value="Pending">Pending</option>
                    <option value="Approved">Approved by Bank</option>
                    <option value="Rejected">Rejected by Bank</option>
                    <option value="Customer Rejected">Rejected by Customer (e.g. low amount/high ROI)</option>
                </select>
            </div>
            <div class="form-group">
                <label>Reason / Remarks (Important if Rejected)</label>
                <textarea name="rejection_reason" id="update-reason-val" rows="3" placeholder="Why was it rejected? Or what were the approved terms?"></textarea>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:20px;">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('update-status-modal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Status</button>
            </div>
        </form>
    </div>
</div>

<script>
function verifyAndProceed() {
    const hasApprovedBank = <?php echo $has_approved_bank ? 'true' : 'false'; ?>;
    
    // Check form fields directly from the DOM
    const cibil = document.getElementById('cibil_score').value.trim();
    const sanctioned = document.getElementById('sanctioned_amount').value.trim();
    const roi = document.getElementById('interest_rate').value.trim();
    const tenure = document.getElementById('tenure_months').value.trim();
    const emi = document.getElementById('emi').value.trim();
    const sdate = document.getElementById('sanction_date').value.trim();
    
    if (!hasApprovedBank) {
        showCustomAlert("Action Blocked", "You must log at least one bank submission and update its status to 'Approved by Bank' before proceeding.");
        return;
    }
    
    if (!cibil || !sanctioned || !roi || !tenure || !emi || !sdate) {
        showCustomAlert("Action Blocked", "Please fill out all 'Final Sanction Details' (CIBIL, Sanctioned Amount, ROI, Tenure, EMI, Date) and save them before proceeding.");
        return;
    }
    
    // If validated successfully, go to Phase 4
    window.location.href = "applicant_bank_assign.php?id=<?php echo $id; ?>";
}

function calculateEMI() {
    const principal = parseFloat(document.getElementById('sanctioned_amount').value);
    const annualROI = parseFloat(document.getElementById('interest_rate').value);
    const months = parseFloat(document.getElementById('tenure_months').value);
    const emiInput = document.getElementById('emi');

    if (isNaN(principal) || isNaN(annualROI) || isNaN(months) || principal <= 0 || months <= 0) {
        // If they manually cleared the values, let's not force clear it if they want to type manually,
        // but if we are auto-calculating we just won't update until valid.
        return;
    }

    if (annualROI === 0) {
        emiInput.value = Math.round(principal / months);
        return;
    }

    const monthlyRate = (annualROI / 12) / 100;
    const emi = (principal * monthlyRate * Math.pow(1 + monthlyRate, months)) / (Math.pow(1 + monthlyRate, months) - 1);
    
    emiInput.value = Math.round(emi);
}

document.addEventListener('DOMContentLoaded', () => {
    const pInput = document.getElementById('sanctioned_amount');
    const rInput = document.getElementById('interest_rate');
    const nInput = document.getElementById('tenure_months');
    
    if (pInput && rInput && nInput) {
        pInput.addEventListener('input', calculateEMI);
        rInput.addEventListener('input', calculateEMI);
        nInput.addEventListener('input', calculateEMI);
    }
});

async function logBankSubmission(e) {
    e.preventDefault();
    const fd = new FormData(e.target);
    try {
        const res = await fetch('?api=assign_applicant_bank', { method: 'POST', body: fd });
        const data = await res.json();
        if(data.success) location.reload();
        else alert(data.error);
    } catch(err) { alert('Error logging submission'); }
}

function openUpdateStatusModal(id, currentStatus, reason) {
    document.getElementById('update-assignment-id').value = id;
    document.getElementById('update-status-val').value = currentStatus;
    document.getElementById('update-reason-val').value = reason;
    document.getElementById('update-status-modal').style.display = 'flex';
}

async function updateBankStatus(e) {
    e.preventDefault();
    const fd = new FormData(e.target);
    try {
        const res = await fetch('?api=update_bank_assignment', { method: 'POST', body: fd });
        const data = await res.json();
        if(data.success) location.reload();
        else alert(data.error);
    } catch(err) { alert('Error updating status'); }
}
</script>

<?php require_once 'footer.php'; ?>
