<?php
require_once 'config.php';
$page_title = 'Edit Referral Profile';
$page_subtitle = '️ Update and complete referral partner details';
require_once 'header.php';

if (($_SESSION['role'] ?? '') !== 'Admin') {
    die("<div class='view-container'><h3>Access Denied. Admins only.</h3></div>");
}

$id = $_GET['id'] ?? null;
if (!$id) die("Missing ID");

$stmt = $db->prepare("SELECT * FROM referrals WHERE id = ?");
$stmt->execute([$id]);
$referral = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$referral) die("Referral not found");

$extra = json_decode($referral['extra_details'] ?? '{}', true);

function getVal($field, $ref, $ext) {
    if (isset($ref[$field])) return htmlspecialchars($ref[$field]);
    if (isset($ext[$field])) return htmlspecialchars($ext[$field]);
    return '';
}
?>

<style>
    /* Premium Form Styling */
    .form-section-title {
        background: #f8fafc;
        padding: 12px 16px;
        border-radius: 8px;
        font-family: 'Outfit', sans-serif;
        font-weight: 600;
        color: #0f172a;
        margin: 24px 0 16px 0;
        border-left: 4px solid var(--primary);
    }
    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 20px;
    }
    .form-group label {
        display: block;
        margin-bottom: 6px;
        font-weight: 500;
        font-size: 13px;
        color: var(--text-primary);
    }
    .form-group label.required::after {
        content: '*';
        color: var(--danger);
        margin-left: 4px;
    }
    .form-group input, .form-group select {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid var(--border);
        border-radius: 8px;
        font-size: 14px;
        transition: all 0.2s;
    }
    .form-group input:focus, .form-group select:focus {
        border-color: var(--primary);
        outline: none;
        box-shadow: 0 0 0 3px var(--primary-glow);
    }
    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        margin-top: 32px;
        padding-top: 24px;
        border-top: 1px solid var(--border);
    }
    #dynamic_fields_container {
        display: none;
        background: #fff7ed;
        padding: 20px;
        border-radius: 12px;
        border: 1px dashed #fdba74;
        margin-top: 16px;
    }
</style>

<div class="view-container">
    <div style="margin-bottom: 20px;">
        <a href="view_referral.php?id=<?php echo $id; ?>" class="btn btn-secondary" style="background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border:none;"><i data-lucide="arrow-left"></i> Back to Profile</a>
    </div>

    <div class="card">
        <form id="editReferralForm" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $id; ?>">

            <!-- 1. CATEGORY & CLASSIFICATION -->
            <div class="form-section-title" style="margin-top:0;">1. CATEGORY & CLASSIFICATION</div>
            <div class="form-grid">
                <div class="form-group">
                    <label for="referrer_type" class="required">Partner Type</label>
                    <select name="referrer_type" id="referrer_type" required>
                        <option value="">-- Select Type --</option>
                        <?php 
                        $types = ['Builder / Real Estate', 'Chartered Accountant (CA)', 'Financial Advisor / DSA', 'Individual Agent', 'Existing Customer', 'Employee Referral'];
                        foreach($types as $t) {
                            $sel = ($referral['referrer_type'] == $t) ? 'selected' : '';
                            echo "<option value=\"$t\" $sel>$t</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div id="dynamic_fields_container" class="form-grid">
                <!-- Dynamically populated via JS -->
            </div>

            <!-- 2. BASIC DETAILS -->
            <div class="form-section-title">2. BASIC DETAILS</div>
            <div class="form-grid">
                <div class="form-group">
                    <label for="full_name" class="required">Full Name (As per Aadhar/PAN)</label>
                    <input type="text" name="full_name" id="full_name" value="<?php echo getVal('full_name', $referral, $extra); ?>" required placeholder="John Doe">
                </div>
                <div class="form-group">
                    <label for="dob" class="required">Date of Birth</label>
                    <input type="date" name="dob" id="dob" value="<?php echo getVal('dob', $referral, $extra); ?>" required>
                </div>
                <div class="form-group">
                    <label for="mobile" class="required">Mobile Number</label>
                    <input type="text" name="mobile" id="mobile" value="<?php echo getVal('mobile', $referral, $extra); ?>" required pattern="[0-9]{10}" placeholder="10-digit number">
                </div>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" name="email" id="email" value="<?php echo getVal('email', $referral, $extra); ?>" placeholder="john@example.com">
                </div>
                <div class="form-group">
                    <label for="city_state">City & State</label>
                    <input type="text" name="city_state" id="city_state" value="<?php echo getVal('city_state', $referral, $extra); ?>" placeholder="e.g. Agra, Uttar Pradesh">
                </div>
            </div>

            <!-- 3. BANKING & COMMISSION -->
            <div class="form-section-title">3. BANKING & COMMISSION STRUCTURE</div>
            <div class="form-grid">
                <div class="form-group">
                    <label for="account_name">Account Holder Name</label>
                    <input type="text" name="account_name" id="account_name" value="<?php echo getVal('account_name', $referral, $extra); ?>" placeholder="As per bank record">
                </div>
                <div class="form-group">
                    <label for="bank_name">Bank Name</label>
                    <input type="text" name="bank_name" id="bank_name" value="<?php echo getVal('bank_name', $referral, $extra); ?>" placeholder="e.g. HDFC Bank">
                </div>
                <div class="form-group">
                    <label for="account_number">Account Number</label>
                    <input type="text" name="account_number" id="account_number" value="<?php echo getVal('account_number', $referral, $extra); ?>" placeholder="Account Number">
                </div>
                <div class="form-group">
                    <label for="ifsc_code">IFSC Code</label>
                    <input type="text" name="ifsc_code" id="ifsc_code" value="<?php echo getVal('ifsc_code', $referral, $extra); ?>" placeholder="e.g. HDFC0001234">
                </div>
                <div class="form-group">
                    <label for="upi_id">UPI ID (Optional)</label>
                    <input type="text" name="upi_id" id="upi_id" value="<?php echo getVal('upi_id', $referral, $extra); ?>" placeholder="e.g. mobilenumber@upi">
                </div>
                <div class="form-group" style="grid-column: 1 / -1; margin-top: 10px;">
                    <div style="background: var(--bg-main); padding: 16px; border-radius: 8px; display: grid; grid-template-columns: 1fr 1fr; gap: 20px; border: 1px dashed var(--border);">
                        <div>
                            <label for="commission_rate" style="color:var(--primary); font-weight: 600;">Agreed Commission / Slab Rate</label>
                            <input type="text" name="commission_rate" id="commission_rate" value="<?php echo getVal('commission_rate', $referral, $extra); ?>" placeholder="e.g. 0.5% per disbursement">
                        </div>
                        <div>
                            <label for="payout_frequency">Payout Frequency</label>
                            <select name="payout_frequency" id="payout_frequency">
                                <?php 
                                $freqs = ['Monthly', 'Per Disbursement', 'Quarterly'];
                                foreach($freqs as $f) {
                                    $sel = ($referral['payout_frequency'] == $f) ? 'selected' : '';
                                    echo "<option value=\"$f\" $sel>$f</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. KYC & DOCUMENTS -->
            <div class="form-section-title">4. KYC & DOCUMENTS</div>
            <div class="form-grid">
                <div class="form-group">
                    <label for="pan_number" class="required">PAN Card Number</label>
                    <input type="text" name="pan_number" id="pan_number" value="<?php echo getVal('pan_number', $referral, $extra); ?>" required placeholder="ABCDE1234F">
                </div>
                <div class="form-group">
                    <label for="aadhar_number" class="required">Aadhar Card Number</label>
                    <input type="text" name="aadhar_number" id="aadhar_number" value="<?php echo getVal('aadhar_number', $referral, $extra); ?>" required pattern="[0-9]{12}" placeholder="12-digit Aadhar number">
                </div>
                <div class="form-group">
                    <label style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">Bank Document (Max 5MB)</label>
                    <div style="display: flex; gap: 8px; align-items: center; margin-top: 8px; padding: 8px 12px; background: #f8fafc; border: 1px solid var(--border); border-radius: 8px; height: 42px; overflow:hidden;">
                        <input type="file" name="bank_document" id="bank_document" style="display:none;" onchange="updateFileName(this, 'bank_doc_wrapper', 'bank_doc_empty', 'bank_doc_preview')" accept="image/*,.pdf">
                        
                        <button type="button" class="btn btn-sm" style="flex-shrink:0; background: white; color: #475569; border: 1px solid #cbd5e1; padding: 4px 10px;" onclick="document.getElementById('bank_document').click()">
                            <i data-lucide="upload" style="width:14px;height:14px;"></i> Choose File
                        </button>
                        
                        <?php if(!empty($referral['bank_document_path'])): ?>
                            <a href="#" onclick="openPreviewModal('<?php echo htmlspecialchars($referral['bank_document_path']); ?>')" class="btn btn-sm" style="flex-shrink:0; background:#ecfdf5; color:#059669; border: 1px solid #a7f3d0; padding: 4px 8px;">
                                <i data-lucide="eye" style="width:14px;height:14px;"></i> View Saved
                            </a>
                        <?php endif; ?>
                        
                        <div id="bank_doc_wrapper" style="display:none; flex:1; align-items:center; gap:4px; min-width:0; margin-left: 8px;">
                            <a id="bank_doc_preview" href="#" style="flex:1; font-size:12px; color:#f97316; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; text-decoration:none;"> file</a>
                            <button type="button" onclick="clearFileSelection('bank_document', 'bank_doc_wrapper', 'bank_doc_empty')" style="flex-shrink:0; background:none; border:none; color:#ef4444; cursor:pointer; padding:2px;"><i data-lucide="x" style="width:16px;height:16px;"></i></button>
                        </div>
                        
                        <span id="bank_doc_empty" style="font-size: 12px; color: var(--text-muted); margin-left: auto; flex-shrink:0;">No new file</span>
                    </div>
                </div>

                <div class="form-group">
                    <label style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">PAN Card Scan (Max 5MB)</label>
                    <div style="display: flex; gap: 8px; align-items: center; margin-top: 8px; padding: 8px 12px; background: #f8fafc; border: 1px solid var(--border); border-radius: 8px; height: 42px; overflow:hidden;">
                        <input type="file" name="pan_document" id="pan_document" style="display:none;" onchange="updateFileName(this, 'pan_doc_wrapper', 'pan_doc_empty', 'pan_doc_preview')" accept="image/*,.pdf">
                        
                        <button type="button" class="btn btn-sm" style="flex-shrink:0; background: white; color: #475569; border: 1px solid #cbd5e1; padding: 4px 10px;" onclick="document.getElementById('pan_document').click()">
                            <i data-lucide="upload" style="width:14px;height:14px;"></i> Choose File
                        </button>
                        
                        <?php if(!empty($referral['pan_document_path'])): ?>
                            <a href="#" onclick="openPreviewModal('<?php echo htmlspecialchars($referral['pan_document_path']); ?>')" class="btn btn-sm" style="flex-shrink:0; background:#ecfdf5; color:#059669; border: 1px solid #a7f3d0; padding: 4px 8px;">
                                <i data-lucide="eye" style="width:14px;height:14px;"></i> View Saved
                            </a>
                        <?php endif; ?>
                        
                        <div id="pan_doc_wrapper" style="display:none; flex:1; align-items:center; gap:4px; min-width:0; margin-left: 8px;">
                            <a id="pan_doc_preview" href="#" style="flex:1; font-size:12px; color:#f97316; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; text-decoration:none;"> file</a>
                            <button type="button" onclick="clearFileSelection('pan_document', 'pan_doc_wrapper', 'pan_doc_empty')" style="flex-shrink:0; background:none; border:none; color:#ef4444; cursor:pointer; padding:2px;"><i data-lucide="x" style="width:16px;height:16px;"></i></button>
                        </div>
                        
                        <span id="pan_doc_empty" style="font-size: 12px; color: var(--text-muted); margin-left: auto; flex-shrink:0;">No new file</span>
                    </div>
                </div>
            </div>
                  <div class="form-group">
                      <label style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">Aadhar Card Scan (Max 5MB)</label>
                      <div style="display: flex; gap: 8px; align-items: center; margin-top: 8px; padding: 8px 12px; background: #f8fafc; border: 1px solid var(--border); border-radius: 8px; height: 42px; overflow:hidden;">
                          <input type="file" name="aadhar_document" id="aadhar_document" style="display:none;" onchange="updateFileName(this, 'aadhar_doc_wrapper', 'aadhar_doc_empty', 'aadhar_doc_preview')" accept="image/*,.pdf">
                          
                          <button type="button" class="btn btn-sm" style="flex-shrink:0; background: white; color: #475569; border: 1px solid #cbd5e1; padding: 4px 10px;" onclick="document.getElementById('aadhar_document').click()">
                              <i data-lucide="upload" style="width:14px;height:14px;"></i> Choose File
                          </button>
                          
                          <div id="aadhar_doc_wrapper" style="flex:1; display:flex; align-items:center; overflow:hidden;">
                              <?php if(!empty($referral['aadhar_document_path'])): ?>
                              <a id="aadhar_doc_preview" href="<?php echo htmlspecialchars($referral['aadhar_document_path']); ?>" target="_blank" style="flex:1; font-size:12px; color:#f97316; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; text-decoration:none;"><i data-lucide="external-link" style="width:12px;height:12px;display:inline-block;vertical-align:middle;margin-right:4px;"></i> View File</a>
                              <span id="aadhar_doc_empty" style="display:none; font-size:12px; color:#94a3b8; font-style:italic; white-space:nowrap;">No file chosen</span>
                              <?php else: ?>
                              <a id="aadhar_doc_preview" href="#" target="_blank" style="display:none; flex:1; font-size:12px; color:#f97316; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; text-decoration:none;"><i data-lucide="external-link" style="width:12px;height:12px;display:inline-block;vertical-align:middle;margin-right:4px;"></i> View File</a>
                              <span id="aadhar_doc_empty" style="font-size:12px; color:#94a3b8; font-style:italic; white-space:nowrap;">No file chosen</span>
                              <?php endif; ?>
                          </div>
                      </div>
                  </div>

            <!-- 5. STATUS & MANAGEMENT -->
            <div class="form-section-title">5. STATUS & MANAGEMENT</div>
            <div class="form-grid">
                <div class="form-group">
                    <label for="mapped_branch">Branch Office</label>
                    <input type="text" name="mapped_branch" id="mapped_branch" value="Sanjay Place" readonly style="background-color: #f1f5f9; cursor: not-allowed; color: var(--text-muted);">
                </div>
                <div class="form-group">
                    <label for="assigned_rm">Assigned RM (Relationship Manager)</label>
                    <select name="assigned_rm" id="assigned_rm" class="user-select">
                        <option value="<?php echo getVal('assigned_rm', $referral, $extra); ?>"><?php echo getVal('assigned_rm', $referral, $extra); ?></option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="status">Referral Status</label>
                    <select name="status" id="status">
                        <?php 
                        $stats = ['Active', 'Pending Approval', 'Blocked'];
                        foreach($stats as $s) {
                            $sel = ($referral['status'] == $s) ? 'selected' : '';
                            echo "<option value=\"$s\" $sel>$s</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="form-actions">
                <a href="view_referral.php?id=<?php echo $id; ?>" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary"><i data-lucide="check"></i> Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- Document Preview Modal -->
<div id="previewModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:9999; align-items:center; justify-content:center; flex-direction:column; padding:20px;">
    <div style="width:100%; max-width:800px; display:flex; justify-content:flex-end; margin-bottom:10px;">
        <button type="button" onclick="closePreviewModal()" style="background:white; border:none; padding:8px; border-radius:50%; cursor:pointer; display:flex; align-items:center; justify-content:center;">
            <i data-lucide="x" style="width:20px; height:20px; color:#ef4444;"></i>
        </button>
    </div>
    <div id="previewContent" style="width:100%; max-width:800px; height:80vh; background:white; border-radius:8px; overflow:hidden; display:flex; align-items:center; justify-content:center; padding:10px;">
    </div>
</div>

<script>
    // Pass existing PHP vars to JS
    const existingDynamicData = <?php echo json_encode($extra); ?>;

    function clearFileSelection(inputId, wrapperId, emptyId) {
        const input = document.getElementById(inputId);
        input.value = "";
        document.getElementById(wrapperId).style.display = 'none';
        if (document.getElementById(emptyId)) document.getElementById(emptyId).style.display = 'block';
        lucide.createIcons();
    }

    function updateFileName(input, wrapperId, emptyId, previewId) {
        const wrapperEl = document.getElementById(wrapperId);
        const previewEl = document.getElementById(previewId);
        const emptyEl = document.getElementById(emptyId);
        
        if (input.files && input.files.length > 0) {
            const file = input.files[0];
            if (file.size > 5 * 1024 * 1024) { // 5MB limit
                alert("File size must be less than 5MB!");
                clearFileSelection(input.id, wrapperId, emptyId);
                return;
            }
            wrapperEl.style.display = 'flex';
            if (emptyEl) emptyEl.style.display = 'none';
            previewEl.textContent = " " + file.name;
            
            const fileUrl = URL.createObjectURL(file);
            const isPdf = file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf');
            previewEl.onclick = function(e) {
                e.preventDefault();
                openPreviewModal(fileUrl, isPdf);
            };
            lucide.createIcons();
        } else {
            clearFileSelection(input.id, wrapperId, emptyId);
        }
    }

    function openPreviewModal(url, isPdf) {
        if(event) event.preventDefault();
        const modal = document.getElementById('previewModal');
        const content = document.getElementById('previewContent');
        if (isPdf || url.toLowerCase().endsWith('.pdf')) {
            content.innerHTML = `<iframe src="${url}" style="width:100%; height:100%; border:none;"></iframe>`;
        } else {
            content.innerHTML = `<img src="${url}" style="max-width:100%; max-height:100%; object-fit:contain;" />`;
        }
        modal.style.display = 'flex';
    }

    function closePreviewModal() {
        document.getElementById('previewModal').style.display = 'none';
        document.getElementById('previewContent').innerHTML = '';
    }
    
    function populateDynamicFields(val) {
        const dynamicContainer = document.getElementById('dynamic_fields_container');
        let html = '';
        
        if (val === 'Builder / Real Estate') {
            html = `
                <div class="form-group" style="grid-column: 1 / -1; margin-bottom: 8px;"><strong style="color:var(--primary);"> Builder / Real Estate Details</strong></div>
                <div class="form-group"><label>Company / Builder Name</label><input type="text" name="company_name" placeholder="e.g. Skyline Builders" value="${existingDynamicData.company_name || ''}"></div>
                <div class="form-group"><label>RERA Registration No.</label><input type="text" name="rera_no" placeholder="RERA Number" value="${existingDynamicData.rera_no || ''}"></div>
                <div class="form-group"><label>GST Number</label><input type="text" name="gst_no" placeholder="15-digit GSTIN" value="${existingDynamicData.gst_no || ''}"></div>
                <div class="form-group"><label>Website</label><input type="url" name="website" placeholder="https://www.example.com" value="${existingDynamicData.website || ''}"></div>
            `;
        } else if (val === 'Chartered Accountant (CA)' || val === 'Financial Advisor / DSA') {
            const label = val === 'Chartered Accountant (CA)' ? 'ICAI Membership No.' : 'DSA License No.';
            html = `
                <div class="form-group" style="grid-column: 1 / -1; margin-bottom: 8px;"><strong style="color:var(--primary);"> Firm / Agency Details</strong></div>
                <div class="form-group"><label>Firm / Agency Name</label><input type="text" name="company_name" placeholder="Name of Firm" value="${existingDynamicData.company_name || ''}"></div>
                <div class="form-group"><label>${label}</label><input type="text" name="registration_number" placeholder="${label}" value="${existingDynamicData.registration_number || ''}"></div>
                <div class="form-group"><label>GST Number</label><input type="text" name="gst_no" placeholder="15-digit GSTIN" value="${existingDynamicData.gst_no || ''}"></div>
                <div class="form-group"><label>Website</label><input type="url" name="website" placeholder="https://www.example.com" value="${existingDynamicData.website || ''}"></div>
            `;
        } else if (val === 'Individual Agent') {
            html = `
                <div class="form-group" style="grid-column: 1 / -1; margin-bottom: 8px;"><strong style="color:var(--primary);"> Agent Details</strong></div>
                <div class="form-group"><label>Occupation</label><input type="text" name="occupation" placeholder="Current Occupation" value="${existingDynamicData.occupation || ''}"></div>
            `;
        } else if (val === 'Existing Customer') {
            html = `
                <div class="form-group" style="grid-column: 1 / -1; margin-bottom: 8px;"><strong style="color:var(--primary);"> Customer Details</strong></div>
                <div class="form-group"><label>Existing Loan Account / Applicant ID</label><input type="text" name="existing_loan_id" placeholder="Loan ID" value="${existingDynamicData.existing_loan_id || ''}"></div>
            `;
        } else if (val === 'Employee Referral') {
            html = `
                <div class="form-group" style="grid-column: 1 / -1; margin-bottom: 8px;"><strong style="color:var(--primary);"> Employee Details</strong></div>
                <div class="form-group"><label>Employee ID</label><input type="text" name="employee_id" placeholder="EMP-1234" value="${existingDynamicData.employee_id || ''}"></div>
                <div class="form-group"><label>Department</label><input type="text" name="department" placeholder="e.g. Sales" value="${existingDynamicData.department || ''}"></div>
            `;
        }
        
        if (html !== '') {
            dynamicContainer.innerHTML = html;
            dynamicContainer.style.display = 'grid';
            
            // Hide banking section for Employee
            const bankingSection = document.querySelectorAll('.form-section-title')[2]; // 0:Category, 1:Basic, 2:Banking
            const bankingGrid = bankingSection.nextElementSibling;
            if (val === 'Employee Referral') {
                bankingSection.style.display = 'none';
                bankingGrid.style.display = 'none';
            } else {
                bankingSection.style.display = 'block';
                bankingGrid.style.display = 'grid';
            }
        } else {
            dynamicContainer.innerHTML = '';
            dynamicContainer.style.display = 'none';
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const typeSelect = document.getElementById('referrer_type');
        
        // Initial load
        populateDynamicFields(typeSelect.value);

        typeSelect.addEventListener('change', function() {
            populateDynamicFields(this.value);
        });
        
    });

document.getElementById('editReferralForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = this.querySelector('button[type="submit"]');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i data-lucide="loader" class="spin"></i> Saving...';
    btn.disabled = true;

    try {
        const formData = new FormData(this);
        const res = await fetch('?api=edit_referral', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        
        if (data.success) {
            showNotification(data.message, 'success');
            setTimeout(() => {
                window.location.href = 'view_referral.php?id=<?php echo $id; ?>';
            }, 1000);
        } else {
            showNotification(data.error || 'Update failed', 'error');
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    } catch(err) {
        showNotification('Network error occurred.', 'error');
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
});
</script>

<?php require_once 'footer.php'; ?>
