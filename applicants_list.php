<?php
require_once 'config.php';
$page_title = 'Loan Applications Dashboard';
$page_subtitle = ' Track, Manage and Process Loan Applicant Files';
require_once 'header.php';
?>

<div id="view-applicants-list" class="view-container">
    <div class="card">
        <div class="card-title-bar">
            <h2>All Loan Applicants</h2>
            <div>
                <a href="add_applicant.php" class="btn btn-primary"><i data-lucide="plus"></i> New Applicant</a>
            </div>
        </div>

        <div class="filter-bar" style="display: flex; gap: 16px; margin-bottom: 24px; flex-wrap: wrap; align-items: center; background: var(--bg-secondary); padding: 16px; border-radius: var(--radius-md); border: 1px solid var(--border);">
            <div style="flex: 1; min-width: 250px; position: relative;">
                <i data-lucide="search" style="position: absolute; left: 12px; top: 10px; color: var(--text-muted); width: 18px; height: 18px;"></i>
                <input type="text" id="searchInput" placeholder="Search by Name, Loan ID, or Mobile..." style="width: 100%; padding: 10px 10px 10px 40px; border-radius: var(--radius-md); border: 1px solid var(--border);" onkeyup="filterTable()">
            </div>
            <div style="min-width: 150px;">
                <select id="stageFilter" onchange="filterTable()" style="width: 100%; padding: 10px; border-radius: var(--radius-md); border: 1px solid var(--border);">
                    <option value="">All Stages</option>
                    <option value="Phase 1">Phase 1 (KYC)</option>
                    <option value="Phase 2">Phase 2 (Docs)</option>
                    <option value="Phase 3">Phase 3 (Disburse)</option>
                    <option value="Phase 4">Phase 4 (Bank)</option>
                    <option value="Completed">Completed</option>
                    <option value="Rejected">Rejected</option>
                </select>
            </div>
            <div style="min-width: 150px;">
                <select id="typeFilter" onchange="filterTable()" style="width: 100%; padding: 10px; border-radius: var(--radius-md); border: 1px solid var(--border);">
                    <option value="">All Loan Types</option>
                    <option value="Home Loan">Home Loan</option>
                    <option value="Vehicle Loan">Vehicle Loan</option>
                    <option value="Personal Loan">Personal Loan</option>
                    <option value="Business Loan">Business Loan</option>
                    <option value="Gold Loan">Gold Loan</option>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Applicant ID</th>
                        <th>Applicant Name</th>
                        <th>Mobile</th>
                        <th>Loan Type</th>
                        <th>Amount (₹)</th>
                        <th>Current Stage</th>
                        <th>Added By</th>
                        <th>Date Added</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody id="applicants-tbody">
                    <tr>
                        <td colspan="9" style="text-align: center;">Loading applicants...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function getStatusBadge(status) {
        let icon = '';
        if(status === 'Phase 1') icon = 'user-check';
        else if(status === 'Phase 2') icon = 'file-text';
        else if(status === 'Phase 3') icon = 'coins';
        else if(status === 'Phase 4') icon = 'landmark';
        else if(status === 'Completed') icon = 'check-circle';
        else if(status === 'Rejected') icon = 'x-circle';
        
        const title = status === 'Phase 1' ? 'Phase 1 (KYC)' : 
                      status === 'Phase 2' ? 'Phase 2 (Docs)' : 
                      status === 'Phase 3' ? 'Phase 3 (Disburse)' : 
                      status === 'Phase 4' ? 'Phase 4 (Bank)' : status;
                      
        return `<span class="badge" style="background:#fff; color:#0f172a; border:1px solid #cbd5e1; font-weight:600; padding:4px 8px; border-radius:6px;"><i data-lucide="${icon}" style="width:12px; height:12px;"></i> ${title}</span>`;
    }
    
    function formatAmt(num) {
        return new Intl.NumberFormat('en-IN').format(num);
    }

    async function loadApplicants() {
        try {
            const res = await fetch('?api=get_applicants');
            const data = await res.json();
            const tbody = document.getElementById('applicants-tbody');
            
            if (data && data.length > 0) {
                tbody.innerHTML = '';
                data.forEach(app => {
                    const statusBadge = getStatusBadge(app.overall_status);
                    
                    let actionUrl = 'add_applicant.php';
                    let btnText = 'Continue';
                    let btnIcon = 'arrow-right';
                    let btnClass = 'btn-secondary';
                    
                    if(app.overall_status === 'Phase 1') {
                        actionUrl = `applicant_documents.php?id=${app.id}`;
                    } else if(app.overall_status === 'Phase 2') {
                        actionUrl = `applicant_disbursements.php?id=${app.id}`;
                    } else if(app.overall_status === 'Phase 3') {
                        actionUrl = `applicant_bank_assign.php?id=${app.id}`;
                    } else if(app.overall_status === 'Phase 4') {
                        actionUrl = `applicant_bank_assign.php?id=${app.id}`;
                        btnText = 'Update';
                    } else if(app.overall_status === 'Completed' || app.overall_status === 'Rejected') {
                        actionUrl = `applicant_bank_assign.php?id=${app.id}`; // Just view
                        btnText = 'View';
                        btnIcon = 'eye';
                    }

                    tbody.innerHTML += `
                        <tr class="applicant-row" data-stage="${app.overall_status}" data-type="${app.loan_type}">
                            <td class="search-field"><strong style="color:var(--text-primary); font-family:'Outfit';">${app.loan_id}</strong></td>
                            <td class="search-field">
                                <strong>${app.customer_name}</strong>
                                ${app.calculated_completion < 100 
                                    ? `<br><span style="font-size: 11px; color: #64748b; font-weight: 600;"><i data-lucide="alert-circle" style="width:10px;height:10px;"></i> ${app.calculated_completion}% Complete</span>`
                                    : `<br><span style="font-size: 11px; color: #0f172a; font-weight: 600;"><i data-lucide="check-circle" style="width:10px;height:10px;"></i> 100% Complete</span>`
                                }
                            </td>
                            <td class="search-field">${app.mobile}</td>
                            <td>${app.loan_type}</td>
                            <td>₹${formatAmt(app.loan_amount_requested)}</td>
                            <td>${statusBadge}</td>
                            <td style="color:var(--text-muted); font-size:12px;">${app.added_by || 'System'}</td>
                            <td style="color:var(--text-muted); font-size:12px;">${app.created_at ? app.created_at.split(' ')[0] : '-'}</td>
                            <td style="text-align: right;">
                                <a href="${actionUrl}" class="btn ${btnClass}" style="padding: 6px 12px; font-size: 12px;">
                                    <i data-lucide="${btnIcon}" style="width:14px; height:14px;"></i> ${btnText}
                                </a>
                            </td>
                        </tr>
                    `;
                });
                lucide.createIcons();
            } else {
                tbody.innerHTML = '<tr><td colspan="9" style="text-align: center; padding: 40px; color: var(--text-muted);">No loan applications found. Start by creating a New Applicant.</td></tr>';
            }
        } catch (e) {
            console.error('Error fetching applicants:', e);
            document.getElementById('applicants-tbody').innerHTML = '<tr><td colspan="9" style="text-align: center; color: var(--danger);">Failed to load data.</td></tr>';
        }
    }

    function filterTable() {
        const searchInput = document.getElementById('searchInput').value.toLowerCase();
        const stageFilter = document.getElementById('stageFilter').value;
        const typeFilter = document.getElementById('typeFilter').value;
        const rows = document.querySelectorAll('.applicant-row');
        
        let visibleCount = 0;
        
        rows.forEach(row => {
            const stage = row.getAttribute('data-stage');
            const type = row.getAttribute('data-type');
            
            // Collect text from searchable fields only
            let textData = "";
            row.querySelectorAll('.search-field').forEach(td => {
                textData += td.textContent.toLowerCase() + " ";
            });
            
            const matchSearch = textData.includes(searchInput);
            const matchStage = stageFilter === "" || stage === stageFilter;
            const matchType = typeFilter === "" || type === typeFilter;
            
            if (matchSearch && matchStage && matchType) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });
        
        let emptyRow = document.getElementById('empty-search-row');
        if (visibleCount === 0 && rows.length > 0) {
            if (!emptyRow) {
                emptyRow = document.createElement('tr');
                emptyRow.id = 'empty-search-row';
                emptyRow.innerHTML = '<td colspan="9" style="text-align: center; padding: 40px; color: var(--text-muted);">No matching applicants found.</td>';
                document.getElementById('applicants-tbody').appendChild(emptyRow);
            } else {
                emptyRow.style.display = '';
            }
        } else if (emptyRow) {
            emptyRow.style.display = 'none';
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        loadApplicants();
    });
</script>

<?php require_once 'footer.php'; ?>
