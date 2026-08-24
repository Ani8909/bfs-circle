<?php
require_once 'config.php';
$edit_id = $_GET['id'] ?? '';
$page_title = $edit_id ? 'Complete Applicant Profile' : 'Add Loan Applicant';
$page_subtitle = ' Phase 1: KYC & Origination';
require_once 'header.php';
?>

<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<style>
.ts-control { padding: 10px 14px !important; border-radius: 6px !important; border-color: #cbd5e1 !important; font-size: 14px !important; }
.ts-dropdown { font-size: 14px !important; border-radius: 6px !important; }
</style>

<div id="view-add-applicant" class="view-container">
    <form id="applicant-registration-form" onsubmit="saveApplicant(event)">
        <input type="hidden" name="converted_lead_id" id="converted_lead_id" value="">
        <input type="hidden" name="id" id="applicant_id" value="<?php echo htmlspecialchars($edit_id); ?>">
        <div class="card">
            <div class="card-title-bar">
                <h2><?php echo $edit_id ? 'Complete Missing Applicant Details' : 'Applicant Registration Form'; ?></h2>
                <div class="badge-status new"><i data-lucide="file-plus"></i> Phase 1: KYC & Origination</div>
            </div>
            
            <!-- 1. Basic Info -->
            <div class="form-section-title"> 1. Basic Information</div>
            <div class="form-grid">
                <div class="form-group full-width">
                    <label class="required">Customer Name</label>
                    <input type="text" name="customer_name" placeholder="Full name of applicant" required>
                </div>
                <div class="form-group">
                    <label class="required">Mobile Number</label>
                    <input type="text" name="mobile" placeholder="10-digit mobile number" required pattern="\d{10}">
                </div>
                <div class="form-group">
                    <label>Email ID</label>
                    <input type="email" name="email" placeholder="applicant@example.com">
                </div>
            </div>

            <!-- 2. Address Details -->
            <div class="form-section-title"> 2. Address & Location</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="required">Pincode</label>
                    <div style="position: relative;">
                        <input type="text" name="pincode" id="pincode" placeholder="6-digit pincode" pattern="\d{6}" maxlength="6" oninput="verifyPincode(this.value)">
                        <span id="pincode-status" style="position: absolute; right: 10px; top: 10px;"></span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="required">State</label>
                    <select name="state" id="state" onchange="loadCities(this.value)">
                        <option value="">Select State</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="required">City / District</label>
                    <select name="city" id="city">
                        <option value="">Select City</option>
                    </select>
                </div>
                <div class="form-group full-width">
                    <label class="required">Complete Address</label>
                    <textarea name="address" rows="2" placeholder="House/Flat No., Street, Area"></textarea>
                </div>
            </div>

            <!-- 3. Financial & KYC -->
            <div class="form-section-title"> 3. Financial & KYC Details</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="required">PAN Number</label>
                    <input type="text" name="pan_number" placeholder="ABCDE1234F" required maxlength="10">
                </div>
                <div class="form-group">
                    <label class="required">Aadhaar Card Number</label>
                    <input type="text" name="aadhar_number" placeholder="12-digit Aadhaar" required pattern="\d{12}">
                </div>
                <div class="form-group">
                    <label class="required">Employment Type</label>
                    <select name="employment_type" required>
                        <option value="" selected>Select Employment</option>
                        <option value="Salaried">Salaried</option>
                        <option value="Self-Employed">Self-Employed</option>
                        <option value="Business Owner">Business Owner</option>
                        <option value="Professional">Professional</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="required">Monthly Income (₹)</label>
                    <input type="number" name="monthly_income" placeholder="e.g. 50000" required>
                </div>
            </div>

            <!-- 4. Co-Applicant Details -->
            <div class="form-section-title" style="display:flex; justify-content:space-between; align-items:center;"> 
                <span>4. Co-Applicant Details (Optional)</span>
                <label style="font-size:14px; display:flex; align-items:center; gap:8px; cursor:pointer; font-weight:normal;">
                    <input type="checkbox" id="has_co_applicant" onchange="toggleCoApplicantSection()" style="width:16px; height:16px; accent-color:var(--primary);">
                    Include Co-Applicant?
                </label>
            </div>
            <div id="co_applicant_wrapper" style="display:none; background:#f8fafc; border:1px solid #cbd5e1; border-radius:8px; padding:20px; margin-bottom:32px;">
                <div id="co_applicants_container">
                    <!-- Dynamic blocks go here -->
                </div>
                <button type="button" class="btn btn-secondary" onclick="addCoApplicantBlock()" style="margin-top:16px; display:flex; align-items:center; gap:8px; background:white;">
                    <i data-lucide="plus" style="width:16px; height:16px;"></i> Add Another Co-Applicant
                </button>
            </div>

            <!-- 5. Loan Application -->
            
            <div class="form-section-title" style="display:flex; justify-content:space-between; align-items:center;">
                <span>5. Personal Discussion (PD) & Field Assessment (Optional)</span>
                <label style="font-size:14px; display:flex; align-items:center; gap:8px; cursor:pointer; font-weight:normal;">
                    <input type="checkbox" id="add_pd_toggle" onchange="document.getElementById('pd_wrapper').style.display = this.checked ? 'block' : 'none'" style="width:16px; height:16px;">
                    Add PD Assessment
                </label>
            </div>
            <div id="pd_wrapper" style="display:none; padding:16px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; margin-bottom:24px;">
                <div class="form-grid">
                <div class="form-group">
                    <label>PD Conducted By</label>
                    <input type="text" name="pd_conducted_by" id="pd_conducted_by" placeholder="Credit Officer Name">
                </div>
                <div class="form-group">
                    <label>PD Date & Time</label>
                    <input type="datetime-local" name="pd_date" id="pd_date">
                </div>
                <div class="form-group">
                    <label>PD Mode</label>
                    <div style="display:flex; gap:15px; align-items:center; height:42px;">
                        <label><input type="radio" name="pd_mode" value="Physical Visit"> Physical</label>
                        <label><input type="radio" name="pd_mode" value="Telephonic"> Telephonic</label>
                        <label><input type="radio" name="pd_mode" value="Video Verification"> Video</label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Business Board / Signage Seen</label>
                    <div style="display:flex; gap:15px; align-items:center; height:42px;">
                        <label><input type="radio" name="business_board_seen" value="Yes"> Yes</label>
                        <label><input type="radio" name="business_board_seen" value="No"> No</label>
                    </div>
                </div>
                <div class="form-group">
                    <label>Stock / Inventory Status</label>
                    <select name="stock_status" id="stock_status">
                        <option value="">-- Select --</option>
                        <option value="Nil">Nil</option>
                        <option value="Moderate">Moderate</option>
                        <option value="High">High</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Business Continuity / Stability</label>
                    <select name="business_stability" id="business_stability">
                        <option value="">-- Select --</option>
                        <option value="Less than 1 year">Less than 1 year</option>
                        <option value="1-3 years">1-3 years</option>
                        <option value="3+ years">3+ years</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Approx. Monthly Turnover (?)</label>
                    <input type="number" name="monthly_turnover" id="monthly_turnover" placeholder="e.g. 500000">
                </div>

                <div class="form-group">
                    <label>Residence Type</label>
                    <select name="residence_type" id="residence_type">
                        <option value="">-- Select --</option>
                        <option value="Owned">Owned</option>
                        <option value="Rented">Rented</option>
                        <option value="Ancestral">Ancestral</option>
                        <option value="Company Provided">Company Provided</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Years at Current Address</label>
                    <input type="number" step="0.1" name="years_at_address" id="years_at_address" placeholder="e.g. 5.5">
                </div>
                <div class="form-group">
                    <label>Locality Classification</label>
                    <select name="locality_classification" id="locality_classification">
                        <option value="">-- Select --</option>
                        <option value="Slum">Slum</option>
                        <option value="Lower-Middle">Lower-Middle</option>
                        <option value="Middle">Middle</option>
                        <option value="Upper-Middle">Upper-Middle</option>
                        <option value="Premium">Premium</option>
                    </select>
                </div>
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label>Neighbor Verification Feedback</label>
                    <textarea name="neighbor_feedback" id="neighbor_feedback" rows="2" placeholder="Summary of local feedback about applicant's reputation..."></textarea>
                </div>

                <div class="form-group" style="grid-column: 1 / -1;">
                    <label>Consumer Durables Observed</label>
                    <div style="display:flex; flex-wrap:wrap; gap:15px; margin-top:5px;">
                        <label><input type="checkbox" name="consumer_durables[]" value="AC"> AC</label>
                        <label><input type="checkbox" name="consumer_durables[]" value="Refrigerator"> Refrigerator</label>
                        <label><input type="checkbox" name="consumer_durables[]" value="Car"> Car</label>
                        <label><input type="checkbox" name="consumer_durables[]" value="Two-Wheeler"> Two-Wheeler</label>
                        <label><input type="checkbox" name="consumer_durables[]" value="Washing Machine"> Washing Machine</label>
                        <label><input type="checkbox" name="consumer_durables[]" value="Smart TV"> Smart TV</label>
                    </div>
                </div>

                <div class="form-group">
                    <label>Overall Lifestyle Score</label>
                    <select name="lifestyle_score" id="lifestyle_score">
                        <option value="">-- Select --</option>
                        <option value="Low">Low</option>
                        <option value="Average">Average</option>
                        <option value="Affluent">Affluent</option>
                    </select>
                </div>

                <div class="form-group" style="grid-column: 1 / -1;">
                    <label>Positive Triggers</label>
                    <div style="display:flex; flex-wrap:wrap; gap:15px; margin-top:5px;">
                        <label><input type="checkbox" name="positive_triggers[]" value="Clean track record"> Clean track record</label>
                        <label><input type="checkbox" name="positive_triggers[]" value="Stable business setup"> Stable business setup</label>
                        <label><input type="checkbox" name="positive_triggers[]" value="Good residential stability"> Good residential stability</label>
                    </div>
                </div>

                <div class="form-group" style="grid-column: 1 / -1;">
                    <label>Negative / Risk Triggers</label>
                    <div style="display:flex; flex-wrap:wrap; gap:15px; margin-top:5px;">
                        <label><input type="checkbox" name="negative_triggers[]" value="Aggressive behavior"> Aggressive behavior</label>
                        <label><input type="checkbox" name="negative_triggers[]" value="Inconsistent income proofs"> Inconsistent income proofs</label>
                        <label><input type="checkbox" name="negative_triggers[]" value="High existing liabilities"> High existing liabilities</label>
                        <label><input type="checkbox" name="negative_triggers[]" value="Suspicious documents"> Suspicious documents</label>
                    </div>
                </div>

                <div class="form-group">
                    <label>Final PD Status / Recommendation</label>
                    <select name="final_pd_status" id="final_pd_status">
                        <option value="">-- Select --</option>
                        <option value="Positive">Positive</option>
                        <option value="Negative">Negative</option>
                        <option value="Refer to Risk Manager">Refer to Risk Manager</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Recommended Loan Amount (?)</label>
                    <input type="number" name="recommended_loan_amount" id="recommended_loan_amount" placeholder="If differs from requested">
                </div>

                <div class="form-group">
                    <label>Upload PD Geotagged Photos / Report</label>
                    <input type="file" name="pd_report_file" id="pd_report_file" accept=".pdf, .jpg, .jpeg, .png">
                    <div id="pd_report_file_link" style="margin-top:5px; font-size:12px;"></div>
                </div>
            </div>
            </div>

            <div class="form-section-title"> 6. Loan Application Details</div>

            <div class="form-grid">
                <div class="form-group">
                    <label class="required">Loan Type</label>
                    <select name="loan_type" id="loan_type" onchange="updateSubTypes()">
                        <option value="" selected>Select Loan Type</option>
                        <option value="Home Loan">Home Loan</option>
                        <option value="Personal Loan">Personal Loan</option>
                        <option value="Vehicle Loan">Vehicle Loan</option>
                        <option value="Gold Loan">Gold Loan</option>
                        <option value="Business Loan">Business Loan</option>
                        <option value="Education Loan">Education Loan</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="required">Loan Sub-Type</label>
                    <select name="loan_sub_type" id="loan_sub_type">
                        <option value="" selected>Select Sub-Type First</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="required">Requested Loan Amount (₹)</label>
                    <input type="number" name="loan_amount_requested" id="loan_amount" placeholder="e.g. 1500000" required>
                </div>
                <div class="form-group">
                    <label class="required">Expected Tenure (Months)</label>
                    <input type="number" name="tenure_months" id="tenure" placeholder="e.g. 120" required>
                </div>
            </div>

            <!-- 6. Lead Source -->
            <div class="form-section-title"> 7. Lead Source & Referral</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="required">Lead Source</label>
                    <select name="lead_source" id="lead_source" onchange="toggleSourceFields()">
                        <option value="" selected>Select Lead Source</option>
                        <option value="Direct / Walk-in">Direct / Walk-in</option>
                        <option value="Referral Partner / Agent">Referral Partner / Agent</option>
                        <option value="Employee Referral">Employee Referral</option>
                        <option value="Digital / Social Media Campaign">Digital / Social Media Campaign</option>
                    </select>
                </div>
                
                <div class="form-group" id="referral_field" style="display:none;">
                    <label>Select Referral Partner</label>
                    <select name="referral_id" id="referral_dropdown">
                        <option value="">-- Loading Referrals --</option>
                    </select>
                </div>

                <div class="form-group" id="employee_field" style="display:none;">
                    <label>Select Referring Employee</label>
                    <select name="employee_id" id="employee_dropdown">
                        <option value="">-- Loading Employees --</option>
                    </select>
                </div>
            </div>

            <!-- Action panel -->
            <div class="form-actions" style="margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--border);">
                <button type="reset" class="btn btn-secondary">Clear Inputs</button>
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="check-circle"></i> 
                    <?php echo $edit_id ? 'Update Applicant Details' : 'Save Applicant & Proceed to Phase 2'; ?>
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    // --- CO-APPLICANT LOGIC START ---
    let coAppCount = 0;
    
    function toggleCoApplicantSection() {
        const isChecked = document.getElementById('has_co_applicant').checked;
        const wrapper = document.getElementById('co_applicant_wrapper');
        const container = document.getElementById('co_applicants_container');
        
        wrapper.style.display = isChecked ? 'block' : 'none';
        
        // Auto-add first block if none exist
        if (isChecked && container.children.length === 0) {
            addCoApplicantBlock();
        }
    }

    function addCoApplicantBlock() {
        coAppCount++;
        const index = coAppCount;
        
        const html = `
        <div id="coapp_block_${index}" style="background:white; border:1px solid #e2e8f0; border-radius:8px; padding:16px; margin-bottom:16px; position:relative;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; border-bottom:1px solid #f1f5f9; padding-bottom:12px;">
                <h4 style="margin:0; font-size:14px; color:#0f172a;"><i data-lucide="user-plus" style="width:16px; height:16px; vertical-align:middle; margin-right:4px;"></i> Co-Applicant #${index}</h4>
                ${index > 1 ? `<button type="button" onclick="document.getElementById('coapp_block_${index}').remove()" style="background:none; border:none; color:#ef4444; cursor:pointer; font-size:12px; font-weight:600;"><i data-lucide="trash-2" style="width:14px; height:14px; vertical-align:middle;"></i> Remove</button>` : ''}
            </div>
            
            <div class="form-grid" style="margin-bottom:16px;">
                <div class="form-group">
                    <label class="required">Relationship with Applicant</label>
                    <select name="coapp_relationship[]" id="coapp_rel_${index}">
                        <option value="" selected>Select Relation</option>
                        <option value="Spouse">Spouse</option>
                        <option value="Father">Father</option>
                        <option value="Mother">Mother</option>
                        <option value="Son">Son</option>
                        <option value="Daughter">Daughter</option>
                        <option value="Brother">Brother</option>
                        <option value="Business Partner">Business Partner</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="required">Income Considered for Loan?</label>
                    <div style="display:flex; gap:16px; padding-top:8px;">
                        <label style="cursor:pointer;"><input type="radio" name="coapp_financial_${index}" value="Yes" onchange="toggleFinancial(${index})"> Yes (Financial)</label>
                        <label style="cursor:pointer;"><input type="radio" name="coapp_financial_${index}" value="No" onchange="toggleFinancial(${index})"> No (Guarantor)</label>
                        <input type="hidden" name="coapp_is_financial[]" id="hidden_financial_${index}" value="">
                    </div>
                </div>
            </div>

            <div class="form-grid" style="margin-bottom:16px;">
                <div class="form-group"><label class="required">Full Name</label><input type="text" name="coapp_name[]" placeholder="Full Name"></div>
                <div class="form-group"><label class="required">Mobile Number</label><input type="text" name="coapp_mobile[]" placeholder="10-digit number" maxlength="10"></div>
                <div class="form-group"><label>Email ID</label><input type="email" name="coapp_email[]" placeholder="Optional"></div>
                <div class="form-group"><label class="required">Date of Birth</label><input type="date" name="coapp_dob[]"></div>
                <div class="form-group"><label class="required">PAN Number</label><input type="text" name="coapp_pan[]" placeholder="ABCDE1234F" style="text-transform:uppercase;"></div>
                <div class="form-group"><label class="required">Aadhaar Number</label><input type="text" name="coapp_aadhar[]" placeholder="12-digit Aadhaar" maxlength="12"></div>
            </div>

            <div style="margin-bottom:16px;">
                <label style="display:flex; align-items:center; gap:8px; font-size:13px; font-weight:600; cursor:pointer; margin-bottom:12px;">
                    <input type="checkbox" name="coapp_same_address_check_${index}" value="1" onchange="toggleCoappAddress(this, ${index})" style="accent-color:var(--primary);">
                    Same as Primary Applicant Address
                    <input type="hidden" name="coapp_same_address[]" id="hidden_same_address_${index}" value="0">
                </label>
                <div id="coapp_address_block_${index}" class="form-grid">
                    <div class="form-group full-width"><label class="required">Complete Address</label><input type="text" name="coapp_address[]" id="coapp_addr_${index}"></div>
                    <div class="form-group">
                        <label class="required">Pincode</label>
                        <div style="position: relative;">
                            <input type="text" name="coapp_pincode[]" id="coapp_pin_${index}" maxlength="6" pattern="\\d{6}" onkeyup="verifyPincode(this.value, false, 'coapp_pin_status_${index}', 'coapp_state_${index}', 'coapp_city_${index}')">
                            <span id="coapp_pin_status_${index}" style="position: absolute; right: 10px; top: 10px;"></span>
                        </div>
                    </div>
                    <div class="form-group"><label class="required">State</label><select name="coapp_state[]" id="coapp_state_${index}" onchange="loadCities(this.value, null, 'coapp_city_${index}')"><option value="">Select State</option></select></div>
                    <div class="form-group"><label class="required">City / District</label><select name="coapp_city[]" id="coapp_city_${index}"><option value="">Select City</option></select></div>
                </div>
            </div>

            <div id="coapp_financial_block_${index}" style="display:none; background:#f1f5f9; padding:16px; border-radius:6px;">
                <h5 style="margin:0 0 12px 0; font-size:13px; color:#475569;">Financial & Employment Details</h5>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="required">Employment Type</label>
                        <select name="coapp_emp_type[]" id="coapp_emp_${index}">
                            <option value="" selected>Select</option>
                            <option value="Salaried">Salaried</option>
                            <option value="Self-Employed Business">Self-Employed Business</option>
                            <option value="Self-Employed Professional">Self-Employed Professional</option>
                            <option value="Homemaker">Homemaker</option>
                            <option value="Retired">Retired</option>
                        </select>
                    </div>
                    <div class="form-group"><label class="required">Monthly Net Income (₹)</label><input type="number" name="coapp_income[]" id="coapp_inc_${index}" placeholder="0"></div>
                    <div class="form-group"><label>Current Monthly EMIs (₹)</label><input type="number" name="coapp_emis[]" id="coapp_emis_${index}" placeholder="Optional"></div>
                </div>
            </div>
        </div>`;
        
        document.getElementById('co_applicants_container').insertAdjacentHTML('beforeend', html);
        
        // Populate the state dropdown by copying from the primary state dropdown
        const mainStateHTML = document.getElementById('state').innerHTML;
        document.getElementById(`coapp_state_${index}`).innerHTML = mainStateHTML;
        
        // Init TomSelect for the new block
        initTS(`coapp_rel_${index}`);
        initTS(`coapp_state_${index}`);
        initTS(`coapp_city_${index}`);
        initTS(`coapp_emp_${index}`);
        
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function toggleFinancial(index) {
        const radios = document.getElementsByName(`coapp_financial_${index}`);
        let isFin = false;
        radios.forEach(r => { if(r.checked && r.value === 'Yes') isFin = true; });
        
        document.getElementById(`hidden_financial_${index}`).value = isFin ? 'Yes' : 'No';
        
        const block = document.getElementById(`coapp_financial_block_${index}`);
        block.style.display = isFin ? 'block' : 'none';
        
        // Toggle required attributes
        // document.getElementById(`coapp_emp_${index}`).required = isFin; // Removed to prevent silent TomSelect HTML5 validation failure
        document.getElementById(`coapp_inc_${index}`).required = isFin;
    }

    function toggleCoappAddress(checkbox, index) {
        document.getElementById(`hidden_same_address_${index}`).value = checkbox.checked ? '1' : '0';
        const block = document.getElementById(`coapp_address_block_${index}`);
        
        if (checkbox.checked) {
            block.style.display = 'none';
            // Copy values logic would go here ideally during save or dynamically
            document.getElementById(`coapp_addr_${index}`).required = false;
            document.getElementById(`coapp_pin_${index}`).required = false;
            document.getElementById(`coapp_state_${index}`).required = false;
            document.getElementById(`coapp_city_${index}`).required = false;
        } else {
            block.style.display = 'grid';
            
            
            
            
        }
    }
    // --- CO-APPLICANT LOGIC END ---

    document.addEventListener('DOMContentLoaded', function() {
        const leadDataStr = sessionStorage.getItem('convert_lead_data');
        if (leadDataStr) {
            try {
                const l = JSON.parse(leadDataStr);
                const setVal = (name, val) => {
                    const el = document.querySelector(`[name="${name}"]`);
                    if (el && val) el.value = val;
                };
                
                setVal('converted_lead_id', l.id);
                setVal('customer_name', l.lead_name);
                setVal('mobile', l.mobile);
                setVal('email', l.email);
                setVal('loan_amount_requested', l.loan_amount);
                
                if (l.requirement) {
                    const ltSelect = document.getElementById('loan_type');
                    let found = Array.from(ltSelect.options).some(o => o.value === l.requirement);
                    if (!found) {
                        let matched = Array.from(ltSelect.options).find(o => o.text.toLowerCase().includes(l.requirement.toLowerCase()) || l.requirement.toLowerCase().includes(o.text.toLowerCase()));
                        if (matched) {
                            ltSelect.value = matched.value;
                            found = true;
                        }
                    } else {
                        ltSelect.value = l.requirement;
                    }
                    if (found) {
                        updateSubTypes();
                    }
                }

                if (l.lead_source) {
                    const srcSelect = document.getElementById('lead_source');
                    let srcVal = '';
                    let lowerSrc = l.lead_source.toLowerCase();
                    if (lowerSrc.includes('referral') || lowerSrc.includes('partner')) srcVal = 'Referral Partner / Agent';
                    else if (lowerSrc.includes('employee') || lowerSrc.includes('staff')) srcVal = 'Employee Referral';
                    else if (lowerSrc.includes('website')) srcVal = 'Direct / Website';
                    else if (lowerSrc.includes('builder')) srcVal = 'Builder Tie-up';
                    else srcVal = 'Direct / Website';
                    
                    srcSelect.value = srcVal;
                    toggleSourceFields();
                    
                    if (l.added_by && l.added_by !== 'direct') {
                        setTimeout(() => {
                            if (srcVal === 'Referral Partner / Agent' || srcVal === 'Builder Tie-up') {
                                const refSelect = document.getElementById('referral_dropdown');
                                if (refSelect) {
                                    refSelect.value = l.added_by;
                                }
                            } else if (srcVal === 'Employee Referral') {
                                const empSelect = document.getElementById('employee_dropdown');
                                if (empSelect) {
                                    empSelect.value = l.added_by;
                                }
                            }
                        }, 500);
                    }
                }
            } catch(e) {
                console.error("Error parsing convert_lead_data", e);
            } finally {
                sessionStorage.removeItem('convert_lead_data');
            }
        }
    });

    const subTypeMap = {
        'Home Loan': ['Plot Purchase', 'New Flat Purchase', 'Home Construction', 'Renovation', 'Balance Transfer (Top-Up)'],
        'Personal Loan': ['Medical Emergency', 'Wedding', 'Travel', 'Debt Consolidation'],
        'Vehicle Loan': ['Two-Wheeler (New)', 'Two-Wheeler (Used)', 'Four-Wheeler (New)', 'Commercial Vehicle'],
        'Gold Loan': ['Standard Gold Jewel Loan', 'Agricultural Gold Loan'],
        'Business Loan': ['Working Capital', 'Equipment Machinery Purchase', 'MSME Expansion', 'Merchant Overdraft'],
        'Education Loan': ['Inland Studies (India)', 'Overseas Studies (Foreign)']
    };

    function updateSubTypes() {
        const typeSelect = document.getElementById('loan_type');
        const selected = typeSelect.value;
        const subSelect = document.getElementById('loan_sub_type');
        
        // If TomSelect is active on loan_sub_type, use its API
        if (tsInstances['loan_sub_type']) {
            const ts = tsInstances['loan_sub_type'];
            ts.clear();
            ts.clearOptions();
            ts.addOption({value: '', text: 'Select Sub-Type'});
            if (subTypeMap[selected]) {
                subTypeMap[selected].forEach(sub => {
                    ts.addOption({value: sub, text: sub});
                });
            }
            ts.refreshOptions(false);
        } else {
            subSelect.innerHTML = '<option value="">Select Sub-Type</option>';
            if (subTypeMap[selected]) {
                subTypeMap[selected].forEach(sub => {
                    let opt = document.createElement('option');
                    opt.value = sub;
                    opt.textContent = sub;
                    subSelect.appendChild(opt);
                });
            }
        }
    }

    // --- TOM SELECT HELPER ---
    let tsInstances = {};
    function initTS(elementId) {
        const el = document.getElementById(elementId);
        if (!el) return;
        if (tsInstances[elementId]) {
            tsInstances[elementId].destroy();
        }
        tsInstances[elementId] = new TomSelect(el, {
            create: false,
            sortField: { field: "text", direction: "asc" }
        });
    }

    async function loadCities(stateName, callback = null, cityId = 'city', defaultCity = null) {
        const cityEl = document.getElementById(cityId);
        
        let ts = tsInstances[cityId];
        if (!ts && cityEl) {
            initTS(cityId);
            ts = tsInstances[cityId];
        }
        
        if (ts) {
            ts.clear();
            ts.clearOptions();
            ts.addOption({value: '', text: 'Select City'});
        } else if (cityEl) {
            cityEl.innerHTML = '<option value="">Select City</option>';
        }

        if (!stateName) return;
        
        try {
            const res = await fetch(`?api=get_cities&state=${encodeURIComponent(stateName)}`);
            const data = await res.json();
            if (data && data.success) {
                data.cities.forEach(c => {
                    if (ts) {
                        ts.addOption({value: c, text: c});
                    } else if (cityEl) {
                        let opt = document.createElement('option');
                        opt.value = c;
                        opt.textContent = c;
                        cityEl.appendChild(opt);
                    }
                });
                
                if (ts && defaultCity) {
                    let exactMatch = data.cities.find(c => c.toLowerCase() === defaultCity.toLowerCase());
                    if (exactMatch) ts.setValue(exactMatch);
                } else if (!ts && cityEl && defaultCity) {
                    cityEl.value = defaultCity;
                }
            }
            if (callback) callback();
        } catch(e) {
            console.error(e);
        }
    }

    async function verifyPincode(pin, isLoad = false, statusId = 'pincode-status', stateId = 'state', cityId = 'city') {
        const statusEl = document.getElementById(statusId);
        const cityEl = document.getElementById(cityId);
        const stateEl = document.getElementById(stateId);
        
        if (pin.length === 6) {
            statusEl.innerHTML = '<i data-lucide="loader" class="spin" style="color: var(--primary); width: 18px; height: 18px;"></i>';
            if (window.lucide) lucide.createIcons();
            
            try {
                const res = await fetch(`?api=verify_pincode&pin=${pin}`);
                const data = await res.json();
                
                if (data && data.success) {
                    statusEl.innerHTML = '<i data-lucide="check-circle" style="color: var(--success); width: 18px; height: 18px;"></i>';
                    
                    if (tsInstances[stateId]) {
                        tsInstances[stateId].setValue(data.state, true); // true = silent, prevent double fetch
                    } else {
                        stateEl.value = data.state;
                    }
                    
                    // Pass data.city as the 4th parameter (defaultCity)
                    await loadCities(data.state, null, cityId, data.city);
                    
                } else {
                    statusEl.innerHTML = '<i data-lucide="x-circle" style="color: var(--danger); width: 18px; height: 18px;"></i>';
                }
                if (window.lucide) lucide.createIcons();
            } catch(e) {
                statusEl.innerHTML = '';
            }
        } else {
            statusEl.innerHTML = '';
        }
    }

    function toggleSourceFields() {
        const source = document.getElementById('lead_source').value;
        const refField = document.getElementById('referral_field');
        const empField = document.getElementById('employee_field');
        
        refField.style.display = (source === 'Referral Partner / Agent') ? 'block' : 'none';
        empField.style.display = (source === 'Employee Referral') ? 'block' : 'none';
    }

    async function loadStates() {
        try {
            const res = await fetch('?api=get_states');
            const data = await res.json();
            const stateEl = document.getElementById('state');
            stateEl.innerHTML = '<option value="">Select State</option>';
            if (data && data.success) {
                data.states.forEach(s => {
                    let opt = document.createElement('option');
                    opt.value = s;
                    opt.textContent = s;
                    stateEl.appendChild(opt);
                });
            }
            initTS('state');
            initTS('city');
        } catch(e) {
            initTS('state');
            initTS('city');
        }
    }



    async function loadSourceDropdowns() {
        try {
            // Load referrals
            const refRes = await fetch('?api=get_active_referrals');
            const refs = await refRes.json();
            const refDrop = document.getElementById('referral_dropdown');
            refDrop.innerHTML = '<option value="">-- Select Partner --</option>';
            if(refs && !refs.error) {
                refs.forEach(r => {
                    refDrop.innerHTML += `<option value="${r.referral_id}">${r.full_name} (${r.referral_id})</option>`;
                });
            }

            // Load employees (using get_users api)
            const empRes = await fetch('?api=get_users');
            const emps = await empRes.json();
            const empDrop = document.getElementById('employee_dropdown');
            empDrop.innerHTML = '<option value="">-- Select Employee --</option>';
            if(emps && !emps.error) {
                emps.forEach(u => {
                    if (u.is_active == 1) {
                        empDrop.innerHTML += `<option value="${u.username}">${u.name || u.username}</option>`;
                    }
                });
            }
            initTS('referral_dropdown');
            initTS('employee_dropdown');
        } catch (e) { 
            console.error('Error loading dropdowns', e); 
            initTS('referral_dropdown');
            initTS('employee_dropdown');
        }
    }

    async function saveApplicant(event) {
        event.preventDefault();
        const form = document.getElementById('applicant-registration-form');
        const formData = new FormData(form);
        
        try {
            const response = await fetch('?api=save_applicant', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (response.ok && data.success) {
                showNotification(data.message, 'success');
                setTimeout(() => {
                    // Redirect to Phase 2: Documents Upload
                    location.href = `applicant_documents.php?id=${data.id}`;
                }, 1500);
            } else {
                showNotification(data.error || 'Registration failed.', 'error');
            }
        } catch (err) {
            showNotification('Connection failure in applicant registration.', 'error');
        }
    }

    async function loadApplicantDetails(id) {
        try {
            const res = await fetch(`?api=get_applicant&id=${id}`);
            const data = await res.json();
            if(data && !data.error) {
                // First load cities for the applicant's state if present
                if (data.state) {
                    await loadCities(data.state, null, 'city', data.city);
                }

                const populateFields = (obj) => {
                    for (let key in obj) {
                        if (key === 'pd_report_path') continue;
                        const el = document.querySelector(`[name="${key}"]`);
                        if (el) {
                            let val = obj[key];
                            if (key === 'lead_source' && val) {
                                let srcVal = 'Direct / Website';
                                let lowerSrc = val.toLowerCase();
                                if (lowerSrc.includes('referral') || lowerSrc.includes('partner') || lowerSrc.includes('agent')) srcVal = 'Referral Partner / Agent';
                                else if (lowerSrc.includes('employee') || lowerSrc.includes('staff')) srcVal = 'Employee Referral';
                                else if (lowerSrc.includes('builder')) srcVal = 'Builder Tie-up';
                                val = srcVal;
                            }

                            if (el.id && tsInstances[el.id]) {
                                tsInstances[el.id].setValue(val);
                                el.value = val;
                            } else if (el.type !== 'radio' && el.type !== 'checkbox') {
                                el.value = val;
                            }
                        }
                        
                        const radios = document.querySelectorAll(`input[type="radio"][name="${key}"]`);
                        if (radios.length > 0 && obj[key]) {
                            radios.forEach(r => { if (r.value === obj[key]) r.checked = true; });
                        }
                        
                        const multiCheckboxes = document.querySelectorAll(`input[type="checkbox"][name="${key}\[\]"]`);
                        if (multiCheckboxes.length > 0 && obj[key]) {
                            const values = String(obj[key]).split(',').map(s => s.trim());
                            multiCheckboxes.forEach(cb => { if (values.includes(cb.value)) cb.checked = true; });
                        }
                    }
                };
                
                populateFields(data);
                
                if (data.pd_report && (data.pd_report.pd_conducted_by || data.pd_report.final_pd_status)) {
                    document.getElementById('add_pd_toggle').checked = true;
                    document.getElementById('pd_wrapper').style.display = 'block';
                    populateFields(data.pd_report);
                    if (data.pd_report.pd_report_path) {
                        const linkEl = document.getElementById('pd_report_file_link');
                        if (linkEl) {
                            linkEl.innerHTML = `<a href="${data.pd_report.pd_report_path}" target="_blank" style="color:var(--primary); font-weight: 500; display:inline-block; margin-top: 5px;"><i data-lucide="file-check" style="width:14px;height:14px;vertical-align:middle;"></i> View Existing Uploaded PD Report</a>`;
                            if (typeof lucide !== 'undefined') lucide.createIcons();
                        }
                    }
                }
                
                if (data.pincode && data.pincode.length === 6) {
                    verifyPincode(data.pincode, true);
                }
                
                if (data.loan_type) {
                    updateSubTypes();
                    setTimeout(() => {
                        const subTypeEl = document.getElementById('loan_sub_type');
                        if(subTypeEl) subTypeEl.value = data.loan_sub_type;
                    }, 50);
                }
                
                if (data.lead_source) {
                    toggleSourceFields();
                    setTimeout(() => {
                        let lowerSrc = data.lead_source.toLowerCase();
                        if((lowerSrc.includes('referral') || lowerSrc.includes('partner') || lowerSrc.includes('agent')) && data.referral_id) {
                            if (tsInstances['referral_dropdown']) {
                                tsInstances['referral_dropdown'].setValue(data.referral_id);
                            } else {
                                document.getElementById('referral_dropdown').value = data.referral_id;
                            }
                        } else if((lowerSrc.includes('employee') || lowerSrc.includes('staff')) && data.employee_id) {
                            if (tsInstances['employee_dropdown']) {
                                tsInstances['employee_dropdown'].setValue(data.employee_id);
                            } else {
                                document.getElementById('employee_dropdown').value = data.employee_id;
                            }
                        }
                    }, 600); // Wait a bit longer for async dropdowns to populate
                }

                if (data.co_applicants && data.co_applicants.length > 0) {
                    document.getElementById('has_co_applicant').checked = true;
                    toggleCoApplicantSection();
                    document.getElementById('co_applicants_container').innerHTML = ''; // clear auto-added block
                    coAppCount = 0; // reset
                    
                    for (const co of data.co_applicants) {
                        addCoApplicantBlock();
                        const idx = coAppCount;
                        
                        // Wait for cities to load if needed (state is populated synchronously inside addCoApplicantBlock)
                        if (co.state) {
                            // We need to wait for cities to load to populate the city dropdown
                            await loadCities(co.state, null, `coapp_city_${idx}`, co.city);
                        }
                        
                        document.getElementById(`coapp_rel_${idx}`).value = co.relationship;
                        const block = document.getElementById(`coapp_block_${idx}`);
                        block.querySelector(`[name="coapp_name[]"]`).value = co.full_name;
                        block.querySelector(`[name="coapp_mobile[]"]`).value = co.mobile;
                        block.querySelector(`[name="coapp_email[]"]`).value = co.email;
                        block.querySelector(`[name="coapp_dob[]"]`).value = co.dob;
                        block.querySelector(`[name="coapp_pan[]"]`).value = co.pan_number;
                        block.querySelector(`[name="coapp_aadhar[]"]`).value = co.aadhar_number;
                        
                        if (co.same_address == 1) {
                            document.getElementById(`coapp_same_${idx}`).checked = true;
                            toggleCoAppAddress(idx);
                        } else {
                            block.querySelector(`[name="coapp_address[]"]`).value = co.address;
                            block.querySelector(`[name="coapp_pincode[]"]`).value = co.pincode;
                            block.querySelector(`[name="coapp_state[]"]`).value = co.state;
                        }
                        
                        block.querySelector(`[name="coapp_emp_type[]"]`).value = co.employment_type;
                        block.querySelector(`[name="coapp_income[]"]`).value = co.monthly_income;
                        block.querySelector(`[name="coapp_emis[]"]`).value = co.current_emis;
                        
                        const radios = document.getElementsByName(`coapp_financial_${idx}`);
                        radios.forEach(r => { if(r.value === co.is_financial) r.checked = true; });
                        document.getElementById(`hidden_financial_${idx}`).value = co.is_financial;
                        if (co.is_financial === 'Yes') {
                            document.getElementById(`coapp_financial_block_${idx}`).style.display = 'block';
                        }
                        
                        // Refresh TS for this block
                        if (tsInstances[`coapp_rel_${idx}`]) tsInstances[`coapp_rel_${idx}`].setValue(co.relationship ? co.relationship.trim() : "");
                        if (tsInstances[`coapp_state_${idx}`]) tsInstances[`coapp_state_${idx}`].setValue(co.state);
                        if (tsInstances[`coapp_city_${idx}`]) tsInstances[`coapp_city_${idx}`].setValue(co.city);
                        if (tsInstances[`coapp_emp_${idx}`]) tsInstances[`coapp_emp_${idx}`].setValue(co.employment_type);
                    }
                }
            }
        } catch(e) {
            console.error('Failed to load applicant details', e);
        }
    }

    document.addEventListener('DOMContentLoaded', async () => {
        await loadStates();
        await loadSourceDropdowns();
        
        // Initialize TS on fixed static dropdowns
        initTS('loan_type');
        initTS('loan_sub_type');
        initTS('lead_source');
        
        const id = document.getElementById('applicant_id').value;
        if(id) {
            loadApplicantDetails(id);
        }
    });
</script>

<?php require_once 'footer.php'; ?>
