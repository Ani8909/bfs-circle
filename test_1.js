
    let currentApp = {};

    function openBankDispatchModal(id, cName, lId, lType, amt, docsCount) {
        currentApp = { id, cName, lId, lType, amt, docsCount };
        
        document.getElementById('modal_applicant_id').value = id;
        
        const safeName = cName.replace(/[^A-Za-z0-9_-]/g, '_');
        document.getElementById('modal_attachment_name').innerText = `Bundle_${lId}_${safeName}.zip`;
        document.getElementById('modal_attachment_desc').innerText = `Auto-generated ZIP containing Applicant Profile Summary + ${docsCount} Uploaded Documents`;
        
        if(!CKEDITOR.instances.modal_body) {
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
        }
        
        updateBankDispatchSubjectAndBody();
        document.getElementById('email-modal').style.display = 'flex';
        if(window.lucide) lucide.createIcons();
            const realBtn = document.getElementById('real-load-more-btn');
            if (realBtn) realBtn.innerText = '↓ Load More Records ↓';
    }

    function updateBankDispatchSubjectAndBody() {
        const bank = document.getElementById('modal_bank_name').value;
        const bName = document.getElementById('modal_banker_name').value;
        const subjEl = document.getElementById('modal_subject');
        
        let subj = `New ${currentApp.lType} Application - ${currentApp.cName} [ID: ${currentApp.lId}]`;
        if (bank) subj += ` for ${bank}`;
        subjEl.value = subj;

        let salutation = bName ? `Dear ${bName},` : `Dear Sir/Madam,`;
        
        const amtFormat = new Intl.NumberFormat('en-IN').format(currentApp.amt);
        
        const newBody = `${salutation}<br><br>

Please find attached the bundled ZIP file containing the complete KYC and property documents for the loan application of <strong>${currentApp.cName}</strong>.<br><br>

<strong>Applicant Name:</strong> ${currentApp.cName}<br>
<strong>Loan Type:</strong> ${currentApp.lType}<br>
<strong>Requested Amount:</strong> INR ${amtFormat}<br>
<strong>Total Documents Attached:</strong> ${currentApp.docsCount}<br><br>

Kindly review the file and let us know the sanction details.<br><br>

Regards,<br>
BFS Financial Services Sourcing Team`;

        if (CKEDITOR.instances.modal_body) {
            CKEDITOR.instances.modal_body.setData(newBody);
        } else {
            document.getElementById('modal_body').value = newBody.replace(/<br>/g, "\n").replace(/<[^>]+>/g, '');
        }
    }

    function closeBankDispatchModal() {
        document.getElementById('email-modal').style.display = 'none';
    }

    function closeBankDispatchModalOutside(event) {
        if (event.target.id === 'email-modal') {
            closeBankDispatchModal();
        }
    }
    
    async function sendBankDispatchEmail(e) {
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
                closeBankDispatchModal();
                form.reset();
            } else {
                showNotification(data.error || 'Failed to send email', 'error');
            }
        } catch(err) {
            showNotification('Network error', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = 'Dispatch Email';
        }
    }
