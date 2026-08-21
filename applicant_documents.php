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

$page_title = 'Applicant Documents';
$page_subtitle = ' Phase 2: Upload KYC & Loan Specific Documents';
require_once 'header.php';
?>

<div id="view-applicant-docs" class="view-container">
    <div style="margin-bottom: 20px;">
        <a href="applicants_list.php" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px;"><i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i> Back to Applicants</a>
    </div>
    <div class="dashboard-layout-row" style="grid-template-columns: 1fr 2fr; gap: 24px;">
        
        <!-- Applicant Summary Side Panel -->
        <div class="card" style="align-self: start;">
            <div class="card-title-bar">
                <h3>Applicant Profile</h3>
                <div class="badge-status contacted">Phase 2</div>
            </div>
            <div style="margin-top: 16px;">
                <div style="font-size: 20px; font-weight: 700; color: var(--text-primary); margin-bottom: 4px;">
                    <?php echo htmlspecialchars($applicant['customer_name'] ?? ''); ?>
                </div>
                <div style="color: var(--text-muted); font-size: 13px; margin-bottom: 16px;">
                    Loan ID: <strong><?php echo htmlspecialchars($applicant['loan_id'] ?? ''); ?></strong>
                </div>
                
                <table class="data-table" style="font-size: 13px;">
                    <tr><th>Loan Type</th><td><?php echo htmlspecialchars($applicant['loan_type'] ?? ''); ?></td></tr>
                    <tr><th>Sub-Type</th><td><?php echo htmlspecialchars($applicant['loan_sub_type'] ?? '-'); ?></td></tr>
                    <tr><th>Amount</th><td>₹<?php echo number_format((float)($applicant['loan_amount_requested'] ?? 0)); ?></td></tr>
                    <tr><th>Employment</th><td id="emp_type"><?php echo htmlspecialchars($applicant['employment_type'] ?? '-'); ?></td></tr>
                </table>
            </div>

            <div style="margin-top: 24px;">
                <a href="applicant_disbursements.php?id=<?php echo $id; ?>" id="proceed-btn" class="btn btn-secondary" style="width: 100%; pointer-events: none; opacity: 0.6; display: flex; flex-direction: column; line-height: 1.4; padding: 12px;">
                    <span style="font-weight: 600;">Phase 3 (Disbursements) &rarr;</span>
                    <span id="proceed-btn-sub" style="font-size: 11px; font-weight: normal;">Checking mandatory documents...</span>
                </a>
            </div>
        </div>

        <!-- Document Upload Area -->
        <div class="card">
            <div class="card-title-bar">
                <h3>Upload Documents</h3>
                <i data-lucide="upload-cloud" style="color: var(--primary);"></i>
            </div>
            
            <div style="background: #eff6ff; padding: 16px; border-radius: var(--radius-md); border: 1px solid #bfdbfe; margin-bottom: 24px;">
                <h4 style="color: #1e40af; margin-bottom: 8px; font-size: 14px;"><i data-lucide="info" style="width:16px; height:16px; vertical-align: text-bottom;"></i> Required Documents Checklist</h4>
                <ul id="doc-checklist" style="font-size: 13px; color: #1e3a8a; padding-left: 20px; line-height: 1.6;">
                    <!-- Populated via JS based on loan/emp type -->
                </ul>
            </div>

            <form id="doc-upload-form" onsubmit="uploadDocument(event)" enctype="multipart/form-data">
                <input type="hidden" name="applicant_id" value="<?php echo $id; ?>">
                
                <div class="form-grid" style="grid-template-columns: 1fr 1fr;">
                    <div class="form-group">
                        <label class="required">Document Category</label>
                        <select name="document_category" id="doc_category" required>
                            <option value="" disabled selected>Select Category</option>
                            <option value="Basic KYC">Basic KYC (PAN, Aadhaar, Photo, Address)</option>
                            <option value="Income Proof">Income Proof (Salary, ITR, Bank Stmt)</option>
                            <option value="Property / Asset Docs">Property / Asset Docs</option>
                            <option value="Vehicle Docs">Vehicle Docs (Quotation, RC)</option>
                            <option value="Co-Applicant Docs">Co-Applicant Docs</option>
                            <option value="Other">Other / Additional</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="required">Specific Document Name</label>
                        <input type="text" name="document_name" placeholder="e.g. PAN Card Front" required>
                    </div>
                </div>
                
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label class="required">Document File</label>
                    <div style="display: flex; gap: 16px; align-items: stretch;">
                        <div style="flex: 1; border: 2px dashed var(--border); padding: 24px; border-radius: var(--radius-md); text-align: center; background: var(--bg-secondary); cursor: pointer; position: relative;" onclick="document.getElementById('file-input').click()">
                            <i data-lucide="upload-cloud" style="width: 32px; height: 32px; color: var(--primary); margin-bottom: 8px;"></i>
                            <h4 style="margin: 0 0 4px 0; color: var(--text-primary);">Upload File</h4>
                            <p style="margin: 0; font-size: 12px; color: var(--text-muted);">Select a PDF/Image</p>
                            <input type="file" name="document_file" id="file-input" accept="image/*, .pdf" capture="environment" required style="opacity: 0; position: absolute; top: 0; left: 0; width: 100%; height: 100%; cursor: pointer;" onchange="updateFileName(this)">
                            <div id="file-name-display" style="margin-top: 12px; font-weight: bold; color: var(--success); font-size: 13px;"></div>
                        </div>
                        <div style="flex: 1; border: 2px dashed var(--primary); padding: 24px; border-radius: var(--radius-md); text-align: center; background: rgba(255,122,0,0.05); cursor: pointer; position: relative;" onclick="startScanner()">
                            <i data-lucide="printer" style="width: 32px; height: 32px; color: var(--primary); margin-bottom: 8px;"></i>
                            <h4 style="margin: 0 0 4px 0; color: var(--primary);">Scan from Printer</h4>
                            <p style="margin: 0; font-size: 12px; color: var(--text-muted);">Scan directly from hardware</p>
                            <div id="scan-status" style="margin-top: 12px; font-weight: bold; color: var(--success); font-size: 13px;"></div>
                        </div>
                    </div>
                    
                    <!-- Scanned Images Staging Area -->
                    <div id="scanned-preview-container" style="display:flex; flex-wrap:wrap; gap:12px; margin-top:16px;"></div>
                </div>

                <div class="form-actions" style="margin-top: 16px; grid-column: 1 / -1;">
                    <button type="submit" class="btn btn-primary" id="upload-btn" style="width: 100%; padding: 12px;"><i data-lucide="upload"></i> Upload & Save Document</button>
                </div>
            </form>

            <!-- Uploaded Documents List -->
            <div style="margin-top: 40px;">
                <h4 style="margin-bottom: 16px; border-bottom: 1px solid var(--border); padding-bottom: 8px;">Uploaded Documents</h4>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Document Name</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th style="text-align:right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="docs-list">
                            <tr><td colspan="4" style="text-align:center;">Loading documents...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </div>
</div>

<script>
    const loanType = "<?php echo addslashes($applicant['loan_type']); ?>";
    const empType = "<?php echo addslashes($applicant['employment_type']); ?>";
    const applicantId = <?php echo $id; ?>;

    function buildChecklist() {
        const ul = document.getElementById('doc-checklist');
        let html = `
            <li><strong>Mandatory KYC:</strong> PAN Card, Aadhaar Card (Front & Back), Passport Photo</li>
        `;
        
        if (empType === 'Salaried') {
            html += `<li><strong>Income Proof (Salaried):</strong> Last 3 Months Salary Slips, Last 6 Months Bank Statement, Form 16 / ITR</li>`;
        } else {
            html += `<li><strong>Income Proof (Self-Employed/Business):</strong> Last 2 Years ITR with Computation, Last 6-12 Months Bank Statement, Business Registration (GST/Udyam/Trade License)</li>`;
        }

        if (loanType === 'Home Loan') {
            html += `<li><strong>Property Docs:</strong> Property Papers (Registry, Khatoni, Map, Chain Docs, Estimate)</li>`;
        } else if (loanType === 'Vehicle Loan') {
            html += `<li><strong>Vehicle Docs:</strong> Quotation / Proforma Invoice, RC (if Used Vehicle)</li>`;
        } else if (loanType === 'Gold Loan') {
            html += `<li><strong>Gold Docs:</strong> Gold Valuation Report</li>`;
        }

        html += `<li><strong>Co-Applicant:</strong> PAN, Aadhaar, Income Proof (If applicable)</li>`;
        
        ul.innerHTML = html;
    }

    async function loadDocuments() {
        try {
            const res = await fetch(`?api=get_applicant_documents&applicant_id=${applicantId}`);
            const docs = await res.json();
            const tbody = document.getElementById('docs-list');
            
            // Check mandatory completeness
            try {
                const appRes = await fetch(`?api=applicant_full_details&id=${applicantId}`);
                const appData = await appRes.json();
                const btn = document.getElementById('proceed-btn');
                const sub = document.getElementById('proceed-btn-sub');
                if (appData && !appData.error) {
                    if (appData.phase2_completion === 100) {
                        btn.style.pointerEvents = 'auto';
                        btn.style.opacity = '1';
                        btn.classList.remove('btn-secondary');
                        btn.classList.add('btn-primary');
                        sub.innerText = "All mandatory documents uploaded";
                    } else {
                        btn.style.pointerEvents = 'none';
                        btn.style.opacity = '0.6';
                        btn.classList.remove('btn-primary');
                        btn.classList.add('btn-secondary');
                        sub.innerText = `Upload mandatory docs to unlock (${appData.phase2_completion}%)`;
                    }
                }
            } catch(e) { console.error("Error checking phase 2 completion"); }

            if (docs && docs.length > 0) {
                tbody.innerHTML = '';
                docs.forEach(doc => {
                    const date = new Date(doc.uploaded_at).toLocaleDateString();
                    let statusBadge = '';
                    if (doc.status === 'Verified') statusBadge = '<span class="badge badge-success" style="font-size:11px;">Verified</span>';
                    else if (doc.status === 'Rejected') statusBadge = '<span class="badge badge-danger" style="font-size:11px;">Rejected</span>';
                    else statusBadge = '<span class="badge badge-warning" style="font-size:11px;">Pending</span>';
                    
                    const notesHtml = doc.notes ? `<div style="font-size:11px; color:var(--text-muted); margin-top:4px;">Note: ${doc.notes}</div>` : '';
                    
                    tbody.innerHTML += `
                        <tr>
                            <td><span class="badge badge-info">${doc.document_category}</span></td>
                            <td>
                                <strong>${doc.document_name}</strong>
                                ${notesHtml}
                            </td>
                            <td>${statusBadge}</td>
                            <td style="color:var(--text-muted); font-size:12px;">${date}</td>
                            <td style="text-align:right;">
                                <a href="${doc.file_path}" target="_blank" class="btn btn-secondary" style="padding: 4px 8px; font-size: 12px;" title="View Document"><i data-lucide="eye" style="width:14px; height:14px;"></i></a>
                                <button onclick="updateDocStatus(${doc.id})" class="btn btn-secondary" style="padding: 4px 8px; font-size: 12px; margin-left: 4px;" title="Update Status"><i data-lucide="check-circle" style="width:14px; height:14px;"></i></button>
                                <button onclick="deleteDoc(${doc.id})" class="btn btn-danger" style="padding: 4px 8px; font-size: 12px; margin-left: 4px;" title="Delete"><i data-lucide="trash" style="width:14px; height:14px;"></i></button>
                            </td>
                        </tr>
                    `;
                });
                lucide.createIcons();
            } else {
                tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; color:var(--text-muted);">No documents uploaded yet.</td></tr>';
            }
        } catch (e) {
            console.error(e);
        }
    }

    let scannedImagesArray = [];

    function startScanner() {
        if(typeof scanner === 'undefined') {
            showNotification("Scanner.js is loading. Please wait.", "error");
            return;
        }
        scanner.scan(displayImagesOnPage, {
            "output_settings": [ { "type": "return-base64", "format": "jpg" } ]
        });
    }

    function displayImagesOnPage(successful, mesg, response) {
        if(!successful) {
            console.error('Failed: ' + mesg);
            if(mesg.toLowerCase().indexOf('user cancel') === -1) showNotification('Scanner error: ' + mesg, 'error');
            return;
        }
        const scannedImages = scanner.getScannedImages(response, true, false);
        if(scannedImages.length > 0) {
            scannedImages.forEach(img => {
                scannedImagesArray.push(img.src);
            });
            
            renderScannedPreviews();
            
            document.getElementById('scan-status').innerText = scannedImagesArray.length + ' page(s) scanned.';
            document.getElementById('file-name-display').innerText = ''; 
            document.getElementById('file-input').value = ''; 
            document.getElementById('file-input').removeAttribute('required');
            showNotification("Scan captured successfully", "success");
        }
    }

    function renderScannedPreviews() {
        const container = document.getElementById('scanned-preview-container');
        container.innerHTML = '';
        scannedImagesArray.forEach((imgSrc, index) => {
            container.innerHTML += `
                <div style="position:relative; width:120px; height:160px; border:1px solid var(--border); border-radius:4px; overflow:hidden; background:#f8fafc; margin-right:10px; margin-bottom:10px; display:inline-block; box-shadow:0 2px 5px rgba(0,0,0,0.1);">
                    <img src="${imgSrc}" style="width:100%; height:100%; object-fit:contain; background:#000;">
                    <div style="position:absolute; top:4px; left:4px; background:rgba(0,0,0,0.7); color:#fff; font-size:11px; padding:2px 6px; border-radius:12px; font-weight:bold;">Page ${index + 1}</div>
                    <button type="button" onclick="removeScannedImage(${index})" style="position:absolute; top:4px; right:4px; border:none; background:var(--danger); color:white; width:22px; height:22px; border-radius:50%; font-size:12px; font-weight:bold; cursor:pointer; display:flex; align-items:center; justify-content:center; padding:0;" title="Remove this page">×</button>
                    <button type="button" onclick="removeScannedImage(${index})" style="position:absolute; bottom:0; width:100%; border:none; background:var(--danger); color:white; padding:6px; font-size:11px; font-weight:bold; cursor:pointer; opacity:0.9;">️ Delete Scan</button>
                </div>
            `;
        });
        
        if (scannedImagesArray.length === 0) {
            document.getElementById('scan-status').innerText = '';
            document.getElementById('file-input').setAttribute('required', 'required');
        } else {
            document.getElementById('scan-status').innerText = scannedImagesArray.length + ' page(s) scanned.';
        }
    }

    function removeScannedImage(index) {
        if(confirm("Remove this scanned page?")) {
            scannedImagesArray.splice(index, 1);
            renderScannedPreviews();
        }
    }

    function updateFileName(input) {
        if(input.files && input.files.length > 0) {
            document.getElementById('file-name-display').innerText = input.files[0].name;
            document.getElementById('scan-status').innerText = '';
            scannedImagesArray = [];
            renderScannedPreviews();
            document.getElementById('file-input').setAttribute('required', 'required');
        }
    }

    async function uploadDocument(e) {
        e.preventDefault();
        const form = document.getElementById('doc-upload-form');
        const btn = document.getElementById('upload-btn');
        const formData = new FormData(form);
        
        btn.disabled = true;
        btn.innerHTML = 'Processing...';

        if (scannedImagesArray.length > 0) {
            try {
                const { jsPDF } = window.jspdf;
                const pdf = new jsPDF('p', 'mm', 'a4');
                const pageWidth = pdf.internal.pageSize.getWidth();
                const pageHeight = pdf.internal.pageSize.getHeight();

                for (let i = 0; i < scannedImagesArray.length; i++) {
                    if (i > 0) pdf.addPage();
                    // Assuming A4 proportions for scanned documents.
                    pdf.addImage(scannedImagesArray[i], 'JPEG', 0, 0, pageWidth, pageHeight);
                }
                
                const pdfBlob = pdf.output('blob');
                const pdfFile = new File([pdfBlob], "scanned_document_" + Date.now() + ".pdf", { type: "application/pdf" });
                formData.set('document_file', pdfFile);
            } catch (err) {
                console.error(err);
                showNotification('Error generating PDF', 'error');
                btn.disabled = false;
                btn.innerHTML = '<i data-lucide="upload"></i> Upload & Save Document';
                lucide.createIcons();
                return;
            }
        }

        if (!formData.get('document_file') || formData.get('document_file').size === 0) {
            showNotification('Please upload a file or scan a document', 'error');
            btn.disabled = false;
            btn.innerHTML = '<i data-lucide="upload"></i> Upload & Save Document';
            lucide.createIcons();
            return;
        }
        
        btn.innerHTML = 'Uploading...';
        
        try {
            const res = await fetch('?api=upload_applicant_document', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();
            if (res.ok && data.success) {
                showNotification(data.message, 'success');
                form.reset();
                scannedImagesArray = [];
                renderScannedPreviews();
                document.getElementById('file-name-display').innerText = '';
                document.getElementById('file-input').setAttribute('required', 'required');
                loadDocuments();
            } else {
                showNotification(data.error || 'Upload failed', 'error');
            }
        } catch (err) {
            showNotification('Connection error', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i data-lucide="upload"></i> Upload Document';
            lucide.createIcons();
        }
    }

    async function deleteDoc(id) {
        if(!confirm("Are you sure you want to delete this document?")) return;
        
        const formData = new FormData();
        formData.append('id', id);
        
        try {
            const res = await fetch('?api=delete_applicant_document', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();
            if(data.success) {
                showNotification("Document deleted", "success");
                loadDocuments();
            }
        } catch (e) {
            showNotification('Connection error', 'error');
        }
    }

    async function updateDocStatus(id) {
        const status = prompt("Enter new status (Pending, Verified, Rejected):", "Verified");
        if (!status) return;
        
        let notes = "";
        if (status === "Rejected") {
            notes = prompt("Enter reason for rejection:");
        }
        
        const formData = new FormData();
        formData.append('id', id);
        formData.append('status', status);
        formData.append('notes', notes);
        
        try {
            const res = await fetch('?api=update_document_status', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();
            if(data.success) {
                showNotification("Status updated", "success");
                loadDocuments();
            }
        } catch (e) {
            showNotification('Connection error', 'error');
        }
    }
    
    function updateFileName(input) {
        const display = document.getElementById('file-name-display');
        if (input.files && input.files[0]) {
            display.textContent = 'Selected File: ' + input.files[0].name;
        } else {
            display.textContent = '';
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        buildChecklist();
        loadDocuments();
    });
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdn.asprise.com/scannerjs/scanner.js"></script>

<?php require_once 'footer.php'; ?>
