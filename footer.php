    </main>

    <!-- Visual Container holding printable invoice layouts dynamically -->
    <div id="invoice-print-container"></div>

    <!-- Template Preview Modal -->
    <div id="template-preview-modal" class="modal">
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h2>📝 Template Preview</h2>
                <span class="close" onclick="closeTemplatePreviewModal()">&times;</span>
            </div>
            <div class="modal-body" id="template-preview-body" style="white-space: pre-wrap; font-size: 14px; color: var(--text-primary); padding: 15px 0;">
            </div>
            <div class="modal-footer" id="template-preview-footer" style="padding-top: 15px; border-top: 1px solid var(--border); display: none;">
            </div>
        </div>
    </div>
    
    <!-- Reminder Modal -->
    <div id="reminder-modal" class="modal">
        <div class="modal-content" style="max-width:400px;">
            <div class="modal-header">
                <h2>⏰ Set Reminder</h2>
                <span class="close" onclick="closeReminderModal()">&times;</span>
            </div>
            <div class="modal-body">
                <form id="reminder-form" onsubmit="handleGlobalReminderSubmit(event)">
                    <input type="hidden" id="rem_lead_type" name="lead_type">
                    <input type="hidden" id="rem_lead_id" name="lead_id">
                    <div class="form-group" style="margin-bottom: 12px;">
                        <label>Date & Time</label>
                        <input type="datetime-local" id="rem_date" name="remind_at" required>
                    </div>
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label>Notes (e.g. Call back regarding pricing)</label>
                        <textarea id="rem_notes" name="notes" rows="3" placeholder="Add specific task remarks..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width:100%;">Save Reminder</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Notification Toast System -->
    <div class="toast-container" id="toast-container"></div>

    <!-- Bulk Upload Modal -->
    <div id="bulk-upload-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>📤 Upload Leads Data</h2>
                <span class="close" onclick="closeBulkUploadModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div class="form-group" style="text-align:center; margin-bottom: 20px;">
                    <label for="bulk-upload-file" class="btn btn-primary" style="display:inline-flex; align-items:center; justify-content:center; cursor:pointer; padding: 14px 28px; font-size: 16px; border-radius: 8px; width: 100%;">
                        <i data-lucide="upload-cloud" style="width:20px;height:20px;margin-right:8px;"></i>
                        Select File to Upload
                    </label>
                    <input type="file" id="bulk-upload-file" accept=".csv, .xls, .xlsx" onchange="handleBulkFileSelect(event)" style="display:none;">
                    <p style="font-size:12px;color:var(--text-light);margin-top:10px;">Supported formats: CSV, XLS, XLSX</p>
                </div>
                
                <div id="bulk-preview-container" style="display:none; margin-top:15px; border-top: 1px solid var(--border); padding-top: 15px;">
                    <p style="font-weight:600; color:var(--success); text-align:center; margin-bottom:15px;">✅ <span id="bulk-record-count">0</span> records parsed successfully.</p>
                    
                    <div style="background:var(--bg-main); border-radius:8px; padding: 15px;">
                        <label style="display:flex; align-items:center; gap:8px; font-weight:600; cursor:pointer;">
                            <input type="checkbox" id="bulk-split-checkbox" onchange="toggleSplitOptions()"> Smart Split Data (Optional)
                        </label>
                        
                        <div id="split-options-container" style="display:none; margin-top:10px; padding-left:25px;">
                            <label style="display:block; margin-bottom:5px;"><input type="radio" name="split_type" value="random" onchange="renderSplitUI()"> 🎲 Split Randomly (Even/Odd)</label>
                            <label style="display:block; margin-bottom:5px;"><input type="radio" name="split_type" value="serial" onchange="renderSplitUI()"> 🔢 Split by Serial</label>
                            <label style="display:block; margin-bottom:5px;"><input type="radio" name="split_type" value="location" onchange="renderSplitUI()"> 📍 Split by Location</label>
                            
                            <div id="dynamic-split-ui" style="margin-top:15px; padding:10px; border:1px solid var(--border); border-radius:6px; background:var(--bg-card); display:none;">
                            </div>
                        </div>
                    </div>
                    
                    <button class="btn btn-primary" id="btn-save-bulk" style="margin-top:20px; width:100%; padding:12px; font-size:16px;" onclick="processBulkUpload()">Save All Records</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Lucide icons initialization
        lucide.createIcons();

        // Modals global management
        function closeTemplatePreviewModal() {
            document.getElementById("template-preview-modal").style.display = "none";
        }
        
        function showTemplatePreview(id) {
            const t = window.globalEmailTemplates ? window.globalEmailTemplates.find(x => x.id == id) : null;
            if(!t) return;
            let bodyContent = `<strong>Subject:</strong> ${t.subject}\n\n${t.body}`;
            document.getElementById("template-preview-body").innerHTML = bodyContent;
            
            const footer = document.getElementById("template-preview-footer");
            if(t.attachment_name) {
                footer.style.display = "block";
                footer.innerHTML = `<a href="uploads/${t.attachment_name}" target="_blank" class="btn btn-secondary" style="text-decoration:none;">📎 Download Attachment</a>`;
            } else {
                footer.style.display = "none";
            }
            document.getElementById("template-preview-modal").style.display = "flex";
        }

        async function approveDeleteTemplate(id) {
            if(!confirm("Permanently delete this template?")) return;
            let fd = new FormData(); fd.append("id", id);
            let res = await fetch("?api=approve_delete_template", {method:"POST", body:fd});
            let json = await res.json();
            showNotification(json.message || (json.error ? "Error" : "Success"), json.success ? "success" : "error");
            if(json.success && typeof loadTemplatesList === 'function') loadTemplatesList();
        }
        
        async function rejectDeleteTemplate(id) {
            let fd = new FormData(); fd.append("id", id);
            let res = await fetch("?api=reject_delete_template", {method:"POST", body:fd});
            let json = await res.json();
            showNotification(json.message || (json.error ? "Error" : "Success"), json.success ? "success" : "error");
            if(json.success && typeof loadTemplatesList === 'function') loadTemplatesList();
        }
        
        async function approveDeletePpt(id) {
            if(!confirm("Permanently delete this presentation?")) return;
            let fd = new FormData(); fd.append("id", id);
            let res = await fetch("?api=approve_delete_ppt", {method:"POST", body:fd});
            let json = await res.json();
            showNotification(json.message || (json.error ? "Error" : "Success"), json.success ? "success" : "error");
            if(json.success && typeof loadPptsList === 'function') loadPptsList();
        }
        
        async function rejectDeletePpt(id) {
            let fd = new FormData(); fd.append("id", id);
            let res = await fetch("?api=reject_delete_ppt", {method:"POST", body:fd});
            let json = await res.json();
            showNotification(json.message || (json.error ? "Error" : "Success"), json.success ? "success" : "error");
            if(json.success && typeof loadPptsList === 'function') loadPptsList();
        }

        function openReminderModal(type, id) {
            document.getElementById('rem_lead_type').value = type;
            document.getElementById('rem_lead_id').value = id;
            document.getElementById('reminder-modal').style.display = 'flex';
        }
        
        function closeReminderModal() {
            document.getElementById('reminder-modal').style.display = 'none';
        }

        async function handleGlobalReminderSubmit(e) {
            e.preventDefault();
            let fd = new FormData(e.target);
            fd.append("api", "save_reminder");
            let res = await fetch("?api=save_reminder", {method:"POST", body:fd});
            let json = await res.json();
            if(json.success) {
                showNotification("Reminder set successfully", "success");
                closeReminderModal();
                if (typeof loadReminders === 'function') {
                    loadReminders();
                }
            } else {
                showNotification(json.error || "Failed to save reminder", "error");
            }
        }

        // Bulk uploads UI elements mapping
        let bulkParsedData = [];
        let systemUsersList = [];

        function openBulkUploadModal(destination = 'leads') {
            window.bulkUploadDestination = destination;
            document.getElementById('bulk-upload-modal').style.display = 'flex';
            document.getElementById('bulk-upload-file').value = '';
            document.getElementById('bulk-preview-container').style.display = 'none';
            document.getElementById('bulk-split-checkbox').checked = false;
            toggleSplitOptions();
            
            if(systemUsersList.length === 0) {
                fetch('?api=get_users')
                    .then(res => res.json())
                    .then(data => {
                        if(Array.isArray(data)) systemUsersList = data;
                    })
                    .catch(err => console.error("Error pre-fetching users: ", err));
            }
        }

        function closeBulkUploadModal() {
            document.getElementById('bulk-upload-modal').style.display = 'none';
        }

        function handleBulkFileSelect(e) {
            const file = e.target.files[0];
            if(!file) return;
            const reader = new FileReader();
            reader.onload = function(evt) {
                try {
                    const data = evt.target.result;
                    const workbook = XLSX.read(data, {type: 'binary'});
                    const firstSheetName = workbook.SheetNames[0];
                    const worksheet = workbook.Sheets[firstSheetName];
                    let json = XLSX.utils.sheet_to_json(worksheet, {defval: ""});
                    
                    bulkParsedData = json.map(row => {
                        return {
                            lead_name: row['Contact Name'] || row['contact_name'] || row['lead_name'] || '',
                            company_name: row['Company Name'] || row['company_name'] || '',
                            mobile: row['Mobile'] || row['mobile'] || row['Phone'] || '',
                            email: row['Email'] || row['email'] || '',
                            lead_source: row['Lead Source'] || row['lead_source'] || 'Cold Call',
                            priority: row['Priority'] || row['priority'] || 'Warm',
                            stage: row['Stage'] || row['stage'] || 'New Lead',
                            assigned_to: row['Assigned To'] || row['assigned_to'] || '',
                            location: row['Location'] || row['location'] || row['City'] || '',
                            notes: row['Notes'] || row['notes'] || ''
                        };
                    }).filter(r => r.lead_name !== '' || r.mobile !== '');
                    
                    document.getElementById('bulk-record-count').innerText = bulkParsedData.length;
                    document.getElementById('bulk-preview-container').style.display = 'block';
                    renderSplitUI();
                } catch(err) {
                    console.error(err);
                    alert("Error parsing file. Ensure it is a valid CSV or Excel file.");
                }
            };
            reader.readAsBinaryString(file);
        }

        function toggleSplitOptions() {
            const isChecked = document.getElementById('bulk-split-checkbox').checked;
            document.getElementById('split-options-container').style.display = isChecked ? 'block' : 'none';
        }

        function renderSplitUI() {
            const uiContainer = document.getElementById('dynamic-split-ui');
            const splitType = document.querySelector('input[name="split_type"]:checked')?.value;
            if(!splitType) {
                uiContainer.style.display = 'none';
                return;
            }
            uiContainer.style.display = 'block';
            
            let userOptions = `<option value="">-- Select Staff --</option>` + systemUsersList.map(u => `<option value="${u.username}">${u.username}</option>`).join('');
            let multiUserOptions = systemUsersList.map(u => `<option value="${u.username}">${u.username}</option>`).join('');

            if(splitType === 'random') {
                uiContainer.innerHTML = `
                    <p style="font-size:13px;margin-bottom:10px;">Select multiple staff per team (Hold Ctrl/Cmd to select multiple). Even rows to Team A, Odd rows to Team B.</p>
                    <div style="display:flex; gap:10px;">
                        <div style="flex:1;"><label style="font-size:12px;font-weight:600;">Team A (Even rows)</label><select id="split-random-a" multiple style="width:100%;height:100px;padding:6px;border-radius:4px;border:1px solid var(--border);">${multiUserOptions}</select></div>
                        <div style="flex:1;"><label style="font-size:12px;font-weight:600;">Team B (Odd rows)</label><select id="split-random-b" multiple style="width:100%;height:100px;padding:6px;border-radius:4px;border:1px solid var(--border);">${multiUserOptions}</select></div>
                    </div>
                `;
            } 
            else if(splitType === 'serial') {
                uiContainer.innerHTML = `
                    <p style="font-size:13px;margin-bottom:10px;">Define ranges (e.g., 1-50, 51-100).</p>
                    <div id="serial-ranges-container">
                        <div class="serial-row" style="display:flex;gap:10px;margin-bottom:10px;">
                            <input type="number" placeholder="Start" class="s-start" style="width:70px;padding:4px;border:1px solid var(--border);border-radius:4px;">
                            <input type="number" placeholder="End" class="s-end" style="width:70px;padding:4px;border:1px solid var(--border);border-radius:4px;">
                            <select class="s-staff" style="flex:1;padding:4px;border:1px solid var(--border);border-radius:4px;">${userOptions}</select>
                        </div>
                    </div>
                    <button type="button" class="btn btn-secondary" style="padding:4px 10px;font-size:11px;" onclick="addSerialRow()">+ Add Range</button>
                `;
            }
            else if(splitType === 'location') {
                const locations = [...new Set(bulkParsedData.map(r => String(r.location).trim()).filter(l => l !== ''))];
                if(locations.length === 0) {
                    uiContainer.innerHTML = `<p style="font-size:13px;color:red;">No locations found in the uploaded file.</p>`;
                    return;
                }
                
                let locHTML = `<p style="font-size:13px;margin-bottom:10px;">Assign staff for each location found in file:</p><div id="location-mapping-container">`;
                locations.forEach(loc => {
                    locHTML += `
                        <div class="loc-row" data-loc="${loc}" style="display:flex;gap:10px;margin-bottom:8px;align-items:center;">
                            <span style="flex:1;font-weight:600;font-size:12px;">📍 ${loc}</span>
                            <select class="l-staff" style="flex:1;padding:4px;border:1px solid var(--border);border-radius:4px;">${userOptions}</select>
                        </div>
                    `;
                });
                locHTML += `</div>`;
                uiContainer.innerHTML = locHTML;
            }
        }
        
        function addSerialRow() {
            let userOptions = `<option value="">-- Select Staff --</option>` + systemUsersList.map(u => `<option value="${u.username}">${u.username}</option>`).join('');
            const row = document.createElement('div');
            row.className = 'serial-row';
            row.style.cssText = 'display:flex;gap:10px;margin-bottom:10px;';
            row.innerHTML = `
                <input type="number" placeholder="Start" class="s-start" style="width:70px;padding:4px;border:1px solid var(--border);border-radius:4px;">
                <input type="number" placeholder="End" class="s-end" style="width:70px;padding:4px;border:1px solid var(--border);border-radius:4px;">
                <select class="s-staff" style="flex:1;padding:4px;border:1px solid var(--border);border-radius:4px;">${userOptions}</select>
            `;
            document.getElementById('serial-ranges-container').appendChild(row);
        }

        async function processBulkUpload() {
            if(bulkParsedData.length === 0) return showNotification('No data to save.', 'error');
            
            const isChecked = document.getElementById('bulk-split-checkbox').checked;
            let finalData = [...bulkParsedData];
            
            if(isChecked) {
                const splitType = document.querySelector('input[name="split_type"]:checked')?.value;
                if(splitType === 'random') {
                    const teamASelect = document.getElementById('split-random-a');
                    const teamBSelect = document.getElementById('split-random-b');
                    const teamA = Array.from(teamASelect.selectedOptions).map(opt => opt.value);
                    const teamB = Array.from(teamBSelect.selectedOptions).map(opt => opt.value);
                    
                    let aIndex = 0;
                    let bIndex = 0;
                    
                    finalData.forEach((row, idx) => {
                        if (idx % 2 === 0) {
                            if (teamA.length > 0) {
                                row.assigned_to = teamA[aIndex % teamA.length];
                                aIndex++;
                            }
                        } else {
                            if (teamB.length > 0) {
                                row.assigned_to = teamB[bIndex % teamB.length];
                                bIndex++;
                            }
                        }
                    });
                }
                else if(splitType === 'serial') {
                    const rows = document.querySelectorAll('.serial-row');
                    let ranges = [];
                    rows.forEach(r => {
                        ranges.push({
                            start: parseInt(r.querySelector('.s-start').value),
                            end: parseInt(r.querySelector('.s-end').value),
                            staff: r.querySelector('.s-staff').value
                        });
                    });
                    
                    finalData.forEach((row, idx) => {
                        const serialNum = idx + 1;
                        let assigned = row.assigned_to;
                        for(let rng of ranges) {
                            if(rng.staff && !isNaN(rng.start) && !isNaN(rng.end) && serialNum >= rng.start && serialNum <= rng.end) {
                                assigned = rng.staff;
                                break;
                            }
                        }
                        row.assigned_to = assigned;
                    });
                }
                else if(splitType === 'location') {
                    const locRows = document.querySelectorAll('.loc-row');
                    let mapping = {};
                    locRows.forEach(r => {
                        const loc = r.getAttribute('data-loc');
                        const staff = r.querySelector('.l-staff').value;
                        if(staff) mapping[loc] = staff;
                    });
                    
                    finalData.forEach(row => {
                        if(row.location && mapping[row.location.trim()]) {
                            row.assigned_to = mapping[row.location.trim()];
                        }
                    });
                }
            }
            
            const btn = document.getElementById('btn-save-bulk');
            btn.innerText = 'Saving... Please wait';
            btn.disabled = true;
            
            try {
                const fd = new FormData();
                const action = window.bulkUploadDestination === 'pre_leads' ? 'bulk_upload_preleads' : 'bulk_upload_leads';
                fd.append('leads_json', JSON.stringify(finalData));
                
                const res = await fetch(`?api=${action}`, { method:'POST', body: fd });
                const json = await res.json();
                
                if(json.success) {
                    showNotification(json.message || "Bulk upload successful!", 'success');
                    closeBulkUploadModal();
                    if(typeof loadPreLeads === 'function' && window.bulkUploadDestination === 'pre_leads') {
                        loadPreLeads();
                    } else if(typeof loadLeads === 'function') {
                        loadLeads();
                    }
                } else {
                    showNotification(json.error || 'Failed to bulk upload', 'error');
                }
            } catch(e) {
                console.error(e);
                showNotification('Connection Error during bulk upload.', 'error');
            } finally {
                btn.innerText = 'Save All Records';
                btn.disabled = false;
            }
        }
    </script>
</body>
</html>
