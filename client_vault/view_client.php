<?php
define('IS_SUBFOLDER', true);
require_once '../config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    die("Access Denied");
}

$client_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($client_id === 0) die("Invalid Client ID");

// Fetch Applicant
$stmt = $db->prepare("SELECT * FROM applicants WHERE id = ?");
$stmt->execute([$client_id]);
$client = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$client) die("Client not found.");

// Fetch Documents
$stmt_docs = $db->prepare("SELECT * FROM applicant_documents WHERE applicant_id = ? ORDER BY uploaded_at DESC");
$stmt_docs->execute([$client_id]);
$documents = $stmt_docs->fetchAll(PDO::FETCH_ASSOC);

// Fetch Bank Assignments
$stmt_bank = $db->prepare("SELECT * FROM applicant_bank_assignments WHERE applicant_id = ? ORDER BY id DESC");
$stmt_bank->execute([$client_id]);
$banks = $stmt_bank->fetchAll(PDO::FETCH_ASSOC);

// Fetch Co-applicants
$stmt_co = $db->prepare("SELECT * FROM co_applicants WHERE applicant_id = ?");
$stmt_co->execute([$client_id]);
$co_applicants = $stmt_co->fetchAll(PDO::FETCH_ASSOC);

// Fetch Disbursements
$stmt_disb = $db->prepare("SELECT * FROM applicant_disbursements WHERE applicant_id = ? ORDER BY disbursed_at ASC");
$stmt_disb->execute([$client_id]);
$disbursements = $stmt_disb->fetchAll(PDO::FETCH_ASSOC);

$current_page = 'client_vault';
$page_title = '360° Client Vault';
$page_subtitle = htmlspecialchars($client['customer_name']) . ' - Secured Profile';

require_once '../header.php';
?>

<style>
    .vault-hero { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); border-radius: 16px; padding: 32px; color: #fff; margin-bottom: 24px; display: flex; justify-content: space-between; align-items: flex-end; box-shadow: 0 10px 30px rgba(15,23,42,0.15); position: relative; overflow: hidden; }
    .vault-hero::after { content: ''; position: absolute; top: -50%; right: -10%; width: 500px; height: 500px; background: radial-gradient(circle, rgba(56,189,248,0.1) 0%, transparent 70%); border-radius: 50%; pointer-events: none; }
    .client-name-lg { font-size: 32px; font-weight: 800; letter-spacing: -0.5px; margin: 0 0 8px 0; }
    .client-tag { display: inline-flex; align-items: center; gap: 6px; background: rgba(255,255,255,0.1); padding: 6px 12px; border-radius: 20px; font-size: 13px; font-weight: 600; border: 1px solid rgba(255,255,255,0.1); }
    
    .grid-layout { display: grid; grid-template-columns: 2fr 1fr; gap: 24px; width: 100%; margin: 0 auto; }
    @media (max-width: 992px) { .grid-layout { grid-template-columns: 1fr; } }
    
    .section-card { background: #fff; border-radius: 16px; padding: 24px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); border: 1px solid #f1f5f9; margin-bottom: 24px; }
    .section-header { display: flex; align-items: center; gap: 10px; font-size: 18px; font-weight: 700; color: #0f172a; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid #f1f5f9; }
    
    .data-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; }
    .data-item { display: flex; flex-direction: column; gap: 4px; }
    .data-lbl { font-size: 12px; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
    .data-val { font-size: 15px; color: #1e293b; font-weight: 600; }
    
    .doc-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px; }
    .doc-card { display: flex; align-items: center; padding: 16px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; transition: all 0.2s; text-decoration: none; }
    .doc-card:hover { border-color: #3b82f6; box-shadow: 0 4px 12px rgba(59,130,246,0.1); transform: translateY(-2px); }
    .doc-icon-wrapper { width: 40px; height: 40px; border-radius: 10px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; margin-right: 16px; flex-shrink: 0; }
    
    .timeline { border-left: 2px solid #e2e8f0; padding-left: 24px; margin-left: 12px; }
    .timeline-item { position: relative; margin-bottom: 24px; }
    .timeline-item::before { content: ''; position: absolute; left: -31px; top: 0; width: 12px; height: 12px; border-radius: 50%; background: #3b82f6; border: 2px solid #fff; box-shadow: 0 0 0 2px #bae6fd; }
    .timeline-date { font-size: 12px; color: #64748b; font-weight: 600; margin-bottom: 4px; }
    .timeline-content { background: #f8fafc; padding: 16px; border-radius: 8px; border: 1px solid #e2e8f0; }
    
    .bank-badge { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; border-radius: 8px; font-weight: 600; font-size: 14px; }
    .pitch-btn-lg { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: #fff; border: none; padding: 14px 24px; border-radius: 12px; font-size: 16px; font-weight: 700; cursor: pointer; transition: 0.3s; display: flex; align-items: center; justify-content: center; gap: 10px; width: 100%; box-shadow: 0 4px 15px rgba(59,130,246,0.3); }
    .pitch-btn-lg:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(59,130,246,0.4); }
</style>

<div style="width: 100%; margin: 0 auto;">
    
    <div style="margin-bottom:16px;">
        <a href="index.php" style="color:#64748b; text-decoration:none; display:inline-flex; align-items:center; gap:6px; font-weight:600; font-size:14px;">
            <i data-lucide="arrow-left" style="width:16px;"></i> Back to Vault
        </a>
    </div>

    <!-- Hero Section -->
    <div class="vault-hero">
        <div style="position:relative; z-index:2;">
            <div style="font-size:13px; font-weight:600; color:#94a3b8; text-transform:uppercase; letter-spacing:1px; margin-bottom:8px;">Verified Client Profile</div>
            <h1 class="client-name-lg"><?= htmlspecialchars($client['customer_name']) ?></h1>
            <div style="display:flex; gap:12px; flex-wrap:wrap; margin-top:16px;">
                <div class="client-tag"><i data-lucide="phone" style="width:14px;"></i> <?= htmlspecialchars($client['mobile']) ?></div>
                <div class="client-tag"><i data-lucide="mail" style="width:14px;"></i> <?= htmlspecialchars($client['email'] ?: 'N/A') ?></div>
                <div class="client-tag"><i data-lucide="map-pin" style="width:14px;"></i> <?= htmlspecialchars($client['city'] . ', ' . $client['state']) ?></div>
            </div>
        </div>
        <div style="position:relative; z-index:2; text-align:right;">
            <div style="font-size:13px; font-weight:600; color:#94a3b8; text-transform:uppercase; margin-bottom:4px;">Loan Status</div>
            <div style="font-size:24px; font-weight:800; color:#34d399; display:flex; align-items:center; gap:8px; justify-content:flex-end;">
                <i data-lucide="check-circle" style="width:24px;"></i> <?= htmlspecialchars($client['overall_status']) ?>
            </div>
        </div>
    </div>
    
    <div class="grid-layout">
        <!-- Main Content -->
        <div>
            <!-- Financial Details -->
            <div class="section-card">
                <div class="section-header">
                    <i data-lucide="wallet" style="color:#3b82f6;"></i> Loan & Financial Details
                </div>
                <div class="data-grid">
                    <div class="data-item">
                        <span class="data-lbl">Loan Type</span>
                        <span class="data-val"><?= htmlspecialchars($client['loan_type']) ?></span>
                    </div>
                    <div class="data-item">
                        <span class="data-lbl">Requested Amount</span>
                        <span class="data-val">&#8377;<?= number_format((float)$client['loan_amount_requested'], 0) ?></span>
                    </div>
                    <div class="data-item">
                        <span class="data-lbl">Sanctioned Amount</span>
                        <span class="data-val" style="color:#059669;">&#8377;<?= number_format((float)$client['sanctioned_amount'], 0) ?></span>
                    </div>
                    <div class="data-item">
                        <span class="data-lbl">Monthly Income</span>
                        <span class="data-val">&#8377;<?= number_format((float)$client['monthly_income'], 0) ?></span>
                    </div>
                    <div class="data-item">
                        <span class="data-lbl">CIBIL Score</span>
                        <span class="data-val"><?= htmlspecialchars($client['cibil_score']) ?></span>
                    </div>
                    <div class="data-item">
                        <span class="data-lbl">Employment Type</span>
                        <span class="data-val"><?= htmlspecialchars($client['employment_type']) ?></span>
                    </div>
                </div>
                
                <?php if(count($banks) > 0): ?>
                <div style="margin-top: 24px; padding-top: 20px; border-top: 1px dashed #cbd5e1;">
                    <div class="data-lbl" style="margin-bottom:12px;">Assigned Bank / Financer</div>
                    <?php foreach($banks as $bank): ?>
                        <div class="bank-badge" style="<?= $bank['status'] == 'Approved' || $bank['status'] == 'Sanctioned' ? '' : 'background:#f1f5f9; color:#475569; border-color:#cbd5e1;' ?>">
                            <i data-lucide="building" style="width:16px;"></i> 
                            <?= htmlspecialchars($bank['bank_name']) ?> 
                            (<?= htmlspecialchars($bank['status']) ?>)
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- KYC & Documents -->
            <div class="section-card">
                <div class="section-header">
                    <i data-lucide="files" style="color:#f59e0b;"></i> Customer KYC & Documents
                </div>
                
                <div class="data-grid" style="margin-bottom:24px; padding-bottom:20px; border-bottom:1px dashed #cbd5e1;">
                    <div class="data-item">
                        <span class="data-lbl">PAN Number</span>
                        <span class="data-val" style="font-family:monospace; font-size:16px;"><?= htmlspecialchars($client['pan_number']) ?></span>
                    </div>
                    <div class="data-item">
                        <span class="data-lbl">Aadhar Number</span>
                        <span class="data-val" style="font-family:monospace; font-size:16px;"><?= htmlspecialchars($client['aadhar_number']) ?></span>
                    </div>
                </div>
                
                <div class="doc-grid">
                    <?php foreach($documents as $doc): ?>
                    <a href="../<?= htmlspecialchars($doc['file_path']) ?>" target="_blank" class="doc-card">
                        <div class="doc-icon-wrapper">
                            <i data-lucide="file-text" style="width:20px;"></i>
                        </div>
                        <div>
                            <div style="font-size:14px; font-weight:700; color:#0f172a; margin-bottom:2px;"><?= htmlspecialchars($doc['document_name']) ?></div>
                            <div style="font-size:12px; color:#64748b;"><?= htmlspecialchars($doc['document_category']) ?> â€¢ <?= date('M d, Y', strtotime($doc['uploaded_at'])) ?></div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                    <?php if(empty($documents)): ?>
                        <div style="color:#94a3b8; font-size:14px;">No documents attached.</div>
                    <?php endif; ?>
                </div>
            </div>
            
        </div>
        
        <!-- Sidebar -->
        <div>
            <!-- Action Pitch -->
            <div class="section-card" style="background: linear-gradient(180deg, #fff 0%, #f8fafc 100%);">
                <button class="pitch-btn-lg" onclick="openPitchModal('<?= $client['id'] ?>', '<?= htmlspecialchars(addslashes($client['customer_name'])) ?>')">
                    <i data-lucide="zap"></i> Start Cross-Sell Pitch
                </button>
                <p style="font-size:13px; color:#64748b; text-align:center; margin-top:12px; line-height:1.5;">
                    Client data and KYC are verified. One-click process to generate a new product pitch.
                </p>
            </div>
            
            <?php if(count($co_applicants) > 0): ?>
            <!-- Co-Applicants -->
            <div class="section-card">
                <div class="section-header" style="font-size:16px;">
                    <i data-lucide="users" style="color:#8b5cf6;"></i> Co-Applicants
                </div>
                <?php foreach($co_applicants as $co): ?>
                    <div style="padding:12px; background:#f8fafc; border-radius:8px; border:1px solid #e2e8f0; margin-bottom:12px;">
                        <div style="font-weight:700; color:#0f172a; font-size:14px;"><?= htmlspecialchars($co['full_name']) ?></div>
                        <div style="font-size:12px; color:#64748b; margin-top:4px;"><?= htmlspecialchars($co['relationship']) ?> â€¢ <?= htmlspecialchars($co['mobile']) ?></div>
                        <div style="font-size:12px; color:#64748b; margin-top:4px;">PAN: <?= htmlspecialchars($co['pan_number']) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <!-- Disbursement Timeline -->
            <div class="section-card">
                <div class="section-header" style="font-size:16px;">
                    <i data-lucide="clock" style="color:#10b981;"></i> Disbursement Logs
                </div>
                <?php if(count($disbursements) > 0): ?>
                <div class="timeline">
                    <?php foreach($disbursements as $disb): ?>
                    <div class="timeline-item">
                        <div class="timeline-date"><?= date('M d, Y h:i A', strtotime($disb['disbursed_at'])) ?></div>
                        <div class="timeline-content">
                            <div style="font-weight:700; color:#0f172a; font-size:14px; margin-bottom:4px;"><?= htmlspecialchars($disb['phase_name']) ?></div>
                            <div style="font-weight:700; color:#059669; font-size:16px;">&#8377;<?= number_format((float)$disb['amount'], 0) ?></div>
                            <div style="font-size:12px; color:#64748b; margin-top:4px;">Status: <?= htmlspecialchars($disb['status']) ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                    <div style="color:#94a3b8; font-size:13px; text-align:center;">No disbursement logs found.</div>
                <?php endif; ?>
            </div>
            
        </div>
    </div>
</div>

<!-- Pitch Product Modal -->
<div id="pitchModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15, 23, 42, 0.7); z-index:9999; align-items:center; justify-content:center; backdrop-filter:blur(4px);">
    <div style="background:#fff; width:100%; max-width:500px; border-radius:16px; padding:32px; box-shadow:0 20px 40px rgba(0,0,0,0.3); animation: slideUp 0.3s ease-out;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
            <h3 style="margin:0; font-size:20px; color:#0f172a; display:flex; align-items:center; gap:10px;"><i data-lucide="zap" style="color:#3b82f6;"></i> New Product Pitch</h3>
            <button onclick="document.getElementById('pitchModal').style.display='none'" style="background:none; border:none; cursor:pointer; color:#94a3b8; transition:0.2s;"><i data-lucide="x"></i></button>
        </div>
        
        <form method="POST" action="process_pitch.php">
            <input type="hidden" name="applicant_id" id="pitch_applicant_id">
            
            <div style="margin-bottom:20px;">
                <label style="display:block; font-size:13px; font-weight:700; color:#475569; margin-bottom:8px;">Selected Customer</label>
                <div style="background:#f8fafc; padding:12px 16px; border-radius:8px; border:1px solid #e2e8f0; font-weight:600; color:#1e293b; font-size:15px;" id="pitch_customer_display"></div>
                <input type="hidden" id="pitch_customer_name" name="customer_name">
            </div>
            
            <div style="margin-bottom:20px;">
                <label style="display:block; font-size:13px; font-weight:700; color:#475569; margin-bottom:8px;">New Product / Service</label>
                <select name="product_type" required style="width:100%; padding:12px; border:2px solid #e2e8f0; border-radius:8px; font-size:15px; outline:none; transition:0.2s; color:#0f172a; font-weight:500;">
                    <option value="">Select a product...</option>
                    <option value="Top-up Loan">Top-up Loan</option>
                    <option value="Personal Loan">Personal Loan</option>
                    <option value="Health Insurance">Health Insurance</option>
                    <option value="Life Insurance">Life Insurance</option>
                    <option value="Credit Card">Credit Card</option>
                    <option value="Other Service">Other Service</option>
                </select>
            </div>
            
            <div style="margin-bottom:28px;">
                <label style="display:block; font-size:13px; font-weight:700; color:#475569; margin-bottom:8px;">Pitch Details / Executive Notes</label>
                <textarea name="pitch_notes" rows="4" placeholder="E.g., Customer is interested in a 5L Top-up for home renovation. Call scheduled for tomorrow." style="width:100%; padding:12px; border:2px solid #e2e8f0; border-radius:8px; font-size:14px; outline:none; transition:0.2s; resize:vertical; font-family:inherit;"></textarea>
            </div>
            
            <div style="display:flex; justify-content:flex-end; gap:16px;">
                <button type="button" onclick="document.getElementById('pitchModal').style.display='none'" style="padding:12px 20px; background:#f1f5f9; color:#475569; border:none; border-radius:8px; font-weight:700; font-size:14px; cursor:pointer; transition:0.2s;">Cancel</button>
                <button type="submit" style="padding:12px 24px; background:#3b82f6; color:#fff; border:none; border-radius:8px; font-weight:700; font-size:14px; cursor:pointer; transition:0.2s; box-shadow:0 4px 12px rgba(59,130,246,0.3);">Launch Pitch & Create Lead</button>
            </div>
        </form>
    </div>
</div>

<style>
@keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
select:focus, textarea:focus { border-color: #3b82f6 !important; box-shadow: 0 0 0 3px rgba(59,130,246,0.1); }
</style>

<script>
function openPitchModal(id, name) {
    document.getElementById('pitch_applicant_id').value = id;
    document.getElementById('pitch_customer_name').value = name;
    document.getElementById('pitch_customer_display').innerText = name;
    document.getElementById('pitchModal').style.display = 'flex';
}
lucide.createIcons();
</script>

<?php require_once '../footer.php'; ?>
