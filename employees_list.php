<?php
require_once 'config.php';
$page_title = 'Staff & HRMS Directory';
$page_subtitle = ' View and manage employee profiles and system access';
require_once 'header.php';

// Fetch employees with their active status from the users table
$stmt = $db->query("SELECT e.*, u.is_active, u.username FROM employees e JOIN users u ON e.user_id = u.id ORDER BY e.created_at DESC");
$employees = $stmt->fetchAll();
?>

<div id="view-employees" class="view-container">
    <div class="card">
        <div class="card-title-bar" style="flex-wrap: wrap; gap: 16px;">
            <h2 style="margin: 0;"><i data-lucide="users" style="vertical-align: middle; margin-right: 8px;"></i> Registered Staff Directory</h2>
            <div class="hr-header-actions">
                <div class="search-box">
                    <i data-lucide="search"></i>
                    <input type="text" id="staffSearch" placeholder="Search by name, ID, or phone..." onkeyup="filterStaff()">
                </div>
                <div class="filter-box">
                    <select id="staffDepartmentFilter" onchange="filterStaff()">
                        <option value="">All Departments</option>
                        <option value="Lead Generation Team">Lead Generation Team</option>
                        <option value="Digital Marketing Team">Digital Marketing Team</option>
                        <option value="Content & Education Team">Content & Education Team</option>
                        <option value="Customer Relationship Team">Customer Relationship Team</option>
                        <option value="IT & Systems Team">IT & Systems Team</option>
                        <option value="Operations & HR">Operations & HR</option>
                    </select>
                </div>
                <?php if (($_SESSION['role'] ?? '') === 'Admin'): ?>
                <a href="add_employee.php" class="btn btn-primary btn-add-emp"><i data-lucide="user-plus"></i> Add New Employee</a>
                <?php endif; ?>
            </div>
        </div>

        <style>
            .hr-header-actions {
                display: flex;
                flex-wrap: wrap;
                gap: 12px;
                align-items: center;
            }
            .search-box {
                position: relative;
                display: flex;
                align-items: center;
            }
            .search-box i {
                position: absolute;
                left: 12px;
                color: var(--text-muted);
                width: 16px;
                height: 16px;
            }
            .search-box input {
                padding: 10px 12px 10px 36px;
                border: 1px solid #e2e8f0;
                border-radius: 8px;
                width: 280px;
                font-size: 14px;
                transition: all 0.3s;
                background: #f8fafc;
            }
            .search-box input:focus {
                background: #fff;
                border-color: var(--primary);
                box-shadow: 0 0 0 3px rgba(239, 108, 0, 0.1);
                outline: none;
            }
            .filter-box select {
                padding: 10px 16px;
                border: 1px solid #e2e8f0;
                border-radius: 8px;
                font-size: 14px;
                background: #f8fafc;
                cursor: pointer;
                transition: all 0.3s;
                appearance: none;
                /* Add custom arrow */
                background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
                background-repeat: no-repeat;
                background-position: right 12px center;
                background-size: 16px;
                padding-right: 40px;
            }
            .filter-box select:focus {
                border-color: var(--primary);
                box-shadow: 0 0 0 3px rgba(239, 108, 0, 0.1);
                outline: none;
            }
            .btn-add-emp {
                padding: 10px 20px;
                border-radius: 8px;
                font-weight: 600;
                box-shadow: 0 4px 10px rgba(239, 108, 0, 0.2);
            }
            /* (Rest of existing styles below...) */
            .staff-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
                gap: 24px;
                padding: 20px;
                background: #f8fafc;
                border-radius: 0 0 12px 12px;
            }
            .staff-card {
                background: #ffffff;
                border-radius: 16px;
                padding: 24px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.04);
                border: 1px solid #e2e8f0;
                transition: all 0.3s ease;
                display: flex;
                flex-direction: column;
                align-items: center;
                text-align: center;
                position: relative;
                overflow: hidden;
            }
            .staff-card::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 4px;
                background: linear-gradient(90deg, #ff8a00, #e52e71);
                opacity: 0;
                transition: opacity 0.3s ease;
            }
            .staff-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 12px 24px rgba(255, 138, 0, 0.15);
                border-color: rgba(255, 138, 0, 0.3);
            }
            .staff-card:hover::before {
                opacity: 1;
            }
            .staff-avatar {
                width: 80px;
                height: 80px;
                border-radius: 50%;
                object-fit: cover;
                border: 3px solid #fff;
                box-shadow: 0 4px 10px rgba(0,0,0,0.1);
                margin-bottom: 12px;
            }
            .staff-avatar-placeholder {
                width: 80px;
                height: 80px;
                border-radius: 50%;
                background: linear-gradient(135deg, var(--primary-light), #ffe0b2);
                color: var(--primary);
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 32px;
                font-weight: 700;
                border: 3px solid #fff;
                box-shadow: 0 4px 10px rgba(0,0,0,0.1);
                margin-bottom: 12px;
            }
            .staff-name {
                font-size: 18px;
                font-weight: 700;
                color: var(--text-dark);
                margin: 0 0 4px 0;
            }
            .staff-role {
                font-size: 14px;
                color: var(--primary);
                font-weight: 600;
                margin: 0 0 12px 0;
            }
            .staff-dept {
                font-size: 13px;
                color: var(--text-muted);
                background: #f1f5f9;
                padding: 4px 12px;
                border-radius: 20px;
                margin-bottom: 16px;
            }
            .staff-contact {
                display: flex;
                flex-direction: column;
                gap: 8px;
                width: 100%;
                text-align: left;
                background: #fafafa;
                padding: 12px;
                border-radius: 8px;
                margin-bottom: 16px;
                font-size: 13px;
                color: var(--text-dark);
            }
            .staff-contact div {
                display: flex;
                align-items: center;
                gap: 8px;
            }
            .staff-contact i {
                color: var(--primary);
                width: 14px;
                height: 14px;
            }
            .staff-actions {
                width: 100%;
                display: flex;
                gap: 10px;
                margin-top: auto;
            }
            .staff-actions button {
                flex: 1;
                justify-content: center;
            }
            .status-badge-absolute {
                position: absolute;
                top: 12px;
                right: 12px;
            }
            .emp-id-badge {
                position: absolute;
                top: 12px;
                left: 12px;
                font-size: 11px;
                background: #e0e7ff;
                color: #4338ca;
                padding: 2px 8px;
                border-radius: 12px;
                font-weight: 600;
            }
            .empty-state {
                grid-column: 1 / -1;
                text-align: center;
                padding: 60px 20px;
                color: var(--text-muted);
            }
        </style>

        <div class="staff-grid" id="staffGrid">
            <?php if (empty($employees)): ?>
                <div class="empty-state">
                    <i data-lucide="users" style="width: 48px; height: 48px; margin-bottom: 16px; opacity: 0.5;"></i>
                    <h3>No Staff Registered Yet</h3>
                    <p>Click "Add New Employee" to start building your directory.</p>
                </div>
            <?php else: ?>
                <?php foreach ($employees as $emp): ?>
                    <div class="staff-card staff-row" data-department="<?php echo htmlspecialchars($emp['department']); ?>">
                        <span class="emp-id-badge"><?php echo htmlspecialchars($emp['emp_id']); ?></span>
                        
                        <?php if($emp['is_active']): ?>
                            <span class="badge badge-success status-badge-absolute">Active</span>
                        <?php else: ?>
                            <span class="badge badge-danger status-badge-absolute">Inactive</span>
                        <?php endif; ?>

                        <?php if(!empty($emp['photo_path']) && file_exists(__DIR__ . '/' . $emp['photo_path'])): ?>
                            <img src="<?php echo htmlspecialchars($emp['photo_path']); ?>" alt="Profile" class="staff-avatar">
                        <?php else: ?>
                            <div class="staff-avatar-placeholder">
                                <?php echo substr(htmlspecialchars($emp['full_name']), 0, 1); ?>
                            </div>
                        <?php endif; ?>

                        <h3 class="staff-name"><?php echo htmlspecialchars($emp['full_name']); ?></h3>
                        <div class="staff-role"><?php echo htmlspecialchars($emp['designation']); ?></div>
                        <div class="staff-dept"><?php echo htmlspecialchars($emp['department']); ?></div>

                        <div class="staff-contact">
                            <div><i data-lucide="phone"></i> <?php echo htmlspecialchars($emp['mobile']); ?></div>
                            <div><i data-lucide="mail"></i> <span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 180px;"><?php echo htmlspecialchars($emp['official_email']); ?></span></div>
                        </div>

                        <div class="staff-actions" style="flex-direction: column; gap: 8px;">
                            <button onclick="viewEmployee('<?php echo htmlspecialchars(json_encode($emp), ENT_QUOTES, 'UTF-8'); ?>')" class="btn btn-secondary" style="border:1px solid var(--primary); color:var(--primary); background:transparent;">
                                <i data-lucide="eye"></i> Full Profile
                            </button>
                            <a href="employee_performance_view.php?username=<?php echo urlencode($emp['username'] ?? ''); ?>" class="btn btn-primary" style="text-decoration:none; text-align:center;">
                                 View Performance
                            </a>
                            <?php if (($_SESSION['role'] ?? '') === 'Admin'): ?>
                            <button onclick="deleteEmployee(<?php echo $emp['id']; ?>, '<?php echo htmlspecialchars(addslashes($emp['full_name'])); ?>')" class="btn btn-danger" style="background:#ef4444; border:none; color:white; margin-top:8px;">
                                <i data-lucide="trash-2"></i> Delete
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Employee Details Modal -->
<div class="modal-overlay" id="employee-modal">
    <div class="modal-content" style="max-width:800px;">
        <div class="modal-header">
            <h3>Staff Profile Details</h3>
            <button class="modal-close" onclick="closeModal()"><i data-lucide="x"></i></button>
        </div>
        <div class="modal-body" id="employee-modal-body">
            <!-- Content will be injected by JS -->
        </div>
    </div>
</div>

<script>

    function deleteEmployee(empId, empName) {
        if (confirm('Are you sure you want to permanently delete ' + empName + '?')) {
            fetch('?api=delete_employee', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'emp_id=' + empId
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    alert('Employee deleted successfully.');
                    location.reload();
                } else {
                    alert(data.error || 'Failed to delete employee.');
                }
            });
        }
    }

    function viewEmployee(empJson) {
        const emp = JSON.parse(empJson);
        const body = document.getElementById('employee-modal-body');
        
        let teamDataHtml = '';
        try {
            const teamData = JSON.parse(emp.team_specific_data || '{}');
            if(Object.keys(teamData).length > 0) {
                teamDataHtml = `<h4 style="margin-top:20px; border-bottom:1px solid #eee; padding-bottom:10px;">Team Specific Operations</h4><div class="form-grid">`;
                for(const [key, value] of Object.entries(teamData)) {
                    // beautify key
                    const prettyKey = key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                    teamDataHtml += `<div><strong>${prettyKey}:</strong> ${value || 'N/A'}</div>`;
                }
                teamDataHtml += `</div>`;
            }
        } catch(e) {}

        const profileImg = emp.photo_path ? `<img src="${emp.photo_path}" style="width:100px; height:100px; border-radius:50%; object-fit:cover; border:3px solid var(--primary);">` : `<div style="width:100px; height:100px; border-radius:50%; background:var(--primary-light); color:var(--primary); display:flex; align-items:center; justify-content:center; font-size:40px; font-weight:bold; border:3px solid var(--primary);">${emp.full_name.charAt(0)}</div>`;

        body.innerHTML = `
            <div style="display:flex; gap:20px; align-items:center; margin-bottom:20px; background:#f8fafc; padding:20px; border-radius:8px;">
                ${profileImg}
                <div>
                    <h2 style="margin:0; color:var(--text-dark);">${emp.full_name} <span class="badge badge-info">${emp.emp_id}</span></h2>
                    <p style="margin:5px 0; font-size:16px; color:var(--text-muted);">${emp.designation} - ${emp.department}</p>
                    <span class="badge badge-${emp.is_active ? 'success' : 'danger'}">${emp.is_active ? 'Account Active' : 'Account Suspended'}</span>
                    <span class="badge badge-primary"><i data-lucide="shield" style="width:12px;height:12px;margin-right:4px;"></i>${emp.access_role}</span>
                </div>
            </div>

            <div class="form-grid">
                <div><strong>Mobile:</strong> ${emp.mobile}</div>
                <div><strong>Official Email:</strong> ${emp.official_email}</div>
                <div><strong>Personal Email:</strong> ${emp.personal_email || 'N/A'}</div>
                <div><strong>Date of Joining:</strong> ${emp.doj || 'N/A'}</div>
                <div><strong>Work Mode:</strong> ${emp.work_mode || 'N/A'}</div>
                <div><strong>Reporting Manager:</strong> ${emp.reporting_manager || 'N/A'}</div>
                <div style="grid-column: 1 / -1;"><strong>Current Address:</strong> ${emp.current_address}</div>
                <div style="grid-column: 1 / -1;"><strong>Permanent Address:</strong> ${emp.permanent_address}</div>
            </div>

            <h4 style="margin-top:20px; border-bottom:1px solid #eee; padding-bottom:10px;">Emergency Contact</h4>
            <div class="form-grid">
                <div><strong>Contact Name:</strong> ${emp.emergency_contact_name}</div>
                <div><strong>Relation:</strong> ${emp.emergency_relation}</div>
                <div><strong>Phone Number:</strong> ${emp.emergency_phone}</div>
            </div>

            ${teamDataHtml}

            <h4 style="margin-top:20px; border-bottom:1px solid #eee; padding-bottom:10px;">KYC & Documents</h4>
            <div class="form-grid">
                <div><strong>PAN Number:</strong> ${emp.pan_number || 'N/A'} 
                    ${emp.pan_path ? `<a href="${emp.pan_path}" target="_blank" class="btn btn-sm btn-secondary" style="margin-left:10px; padding:2px 8px;"><i data-lucide="download"></i> View</a>` : ''}
                </div>
                <div><strong>Aadhaar Number:</strong> ${emp.aadhar_number || 'N/A'}
                    ${emp.aadhar_path ? `<a href="${emp.aadhar_path}" target="_blank" class="btn btn-sm btn-secondary" style="margin-left:10px; padding:2px 8px;"><i data-lucide="download"></i> View</a>` : ''}
                </div>
            </div>
            
            <h4 style="margin-top:20px; border-bottom:1px solid #eee; padding-bottom:10px;">Salary Bank Account</h4>
            <div class="form-grid">
                <div><strong>Account Holder:</strong> ${emp.bank_holder_name || 'N/A'}</div>
                <div><strong>Account No:</strong> ${emp.bank_account_no || 'N/A'}</div>
                <div><strong>Bank Name:</strong> ${emp.bank_name || 'N/A'}</div>
                <div><strong>IFSC Code:</strong> ${emp.bank_ifsc || 'N/A'}</div>
            </div>
        `;
        
        lucide.createIcons();
        document.getElementById('employee-modal').style.display = 'flex';
    }

    function closeModal() {
        document.getElementById('employee-modal').style.display = 'none';
    }

    function filterStaff() {
        const searchQuery = document.getElementById('staffSearch').value.toLowerCase();
        const deptFilter = document.getElementById('staffDepartmentFilter').value;
        const rows = document.querySelectorAll('.staff-row');

        rows.forEach(row => {
            const textContent = row.textContent.toLowerCase();
            const department = row.getAttribute('data-department');
            
            const matchesSearch = textContent.includes(searchQuery);
            const matchesDept = deptFilter === "" || department === deptFilter;

            if (matchesSearch && matchesDept) {
                row.style.display = 'flex';
            } else {
                row.style.display = 'none';
            }
        });
    }
</script>

<?php require_once 'footer.php'; ?>
