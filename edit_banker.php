<?php
require_once 'config.php';
// Admin only access check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: dashboard.php");
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $db->prepare("SELECT * FROM bankers WHERE id = ?");
$stmt->execute([$id]);
$banker = $stmt->fetch();

if (!$banker) {
    die("Banker not found.");
}

$page_title = 'Edit Bank Contact';
$page_subtitle = ' Update relationship manager or banker contact details';
require_once 'header.php';

$selected_categories = explode(',', $banker['loan_category'] ?? '');
$selected_categories = array_map('trim', $selected_categories);
?>

<script src="assets/js/locations.js"></script>
<script>
    const preselectedState = <?php echo json_encode($banker['state'] ?? ''); ?>;
    const preselectedCity = <?php echo json_encode($banker['city'] ?? ''); ?>;
</script>

<div id="view-add-banker" class="view-container">
    <form id="banker-registration-form" onsubmit="saveBanker(event)">
        <input type="hidden" name="id" value="<?php echo $banker['id']; ?>">
        <div class="card">
            <div class="card-title-bar">
                <div style="display:flex; align-items:center; gap:15px;">
                    <a href="bankers_list.php" class="btn btn-secondary" style="padding:8px; border-radius:50%; width:36px; height:36px; display:flex; align-items:center; justify-content:center;" title="Back to List"><i data-lucide="arrow-left" style="width:18px;height:18px;margin:0;"></i></a>
                    <h2 style="margin:0;">Edit Bank Relationship Manager</h2>
                </div>
                <div class="badge-locked" style="background:#fef3c7; color:#d97706;"><i data-lucide="briefcase"></i> External Contact</div>
            </div>
            
            <!-- Bank & Contact Info -->
            <div class="form-section-title"> Bank Details</div>
            <div class="form-grid" style="grid-template-columns: repeat(2, 1fr);">
                <div class="form-group full-width" style="display:flex; justify-content:space-between; align-items:center; gap: 20px;">
                    <div style="flex-grow:1;">
                        <label class="required">Full Name</label>
                        <input type="text" name="full_name" placeholder="RM / Banker Name" value="<?php echo htmlspecialchars($banker['full_name'] ?? ''); ?>" required>
                    </div>
                    <div style="width:250px;">
                        <label class="required">Status</label>
                        <select name="status" required style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-weight: bold; color: <?php echo ($banker['status'] ?? 'Active') === 'Active' ? '#10b981' : '#ef4444'; ?>;" onchange="this.style.color = this.value === 'Active' ? '#10b981' : '#ef4444';">
                            <option value="Active" <?php echo ($banker['status'] ?? 'Active') === 'Active' ? 'selected' : ''; ?>>Active (Working)</option>
                            <option value="Inactive" <?php echo ($banker['status'] ?? '') === 'Inactive' ? 'selected' : ''; ?>>Inactive (Deactivated)</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="required">Bank Type</label>
                    <select name="bank_type" id="bank_type_select" required>
                        <option value="" disabled selected>Select Bank Type</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="required">Bank Name</label>
                    <select name="bank_name" id="bank_name_select" required>
                        <option value="" disabled selected>Select Type First</option>
                    </select>
                </div>
                
                <div class="form-group full-width" style="display:grid; grid-template-columns: repeat(3, 1fr); gap:20px;">
                    <div>
                        <label class="required" style="display:flex; justify-content:space-between;">
                            PIN Code
                            <span id="pincode-loading" style="display:none; font-size:12px; color:#3b82f6;">Fetching...</span>
                        </label>
                        <input type="text" name="pincode" id="pincode-input" placeholder="e.g. 400001" value="<?php echo htmlspecialchars($banker['pincode'] ?? ''); ?>" oninput="fetchPincodeDetails(this.value)" maxlength="6">
                    </div>
                    <div>
                        <label class="required">State</label>
                        <select name="state" id="state-select" required onchange="updateCities()">
                            <option value="" disabled selected>Select State</option>
                        </select>
                    </div>
                    <div>
                        <label class="required">City</label>
                        <select name="city" id="city-select" required>
                            <option value="" disabled selected>Select City first</option>
                        </select>
                    </div>
                </div>

                <div class="form-group full-width" style="display:grid; grid-template-columns: 1fr 2fr; gap:20px;">
                    <div>
                        <label class="required" style="display:flex; justify-content:space-between;">
                            IFSC Code 
                            <span id="ifsc-loading" style="display:none; font-size:12px; color:#3b82f6;">Fetching...</span>
                        </label>
                        <div style="display:flex; gap:10px;">
                            <input type="text" name="ifsc_code" id="ifsc_code_input" placeholder="e.g. SBIN0001234" pattern="^[A-Z]{4}0[A-Z0-9]{6}$" title="Enter a valid 11-character IFSC Code" value="<?php echo htmlspecialchars($banker['ifsc_code'] ?? ''); ?>" required style="text-transform: uppercase; width: 100%;" oninput="if(this.value.length === 11) fetchIfscDetails()" maxlength="11">
                            <select id="branch_dropdown" style="display:none; width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
                                <option value="">▼ Select Branch</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="required">Branch Street Address</label>
                        <input type="text" name="address" id="address_input" placeholder="Local branch street address or building" value="<?php echo htmlspecialchars($banker['address'] ?? ''); ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="required">Position / Designation</label>
                    <input type="text" name="designation" placeholder="e.g. Branch Manager, Loan Officer" value="<?php echo htmlspecialchars($banker['designation'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label>Our DSA Code (for this Bank) <span style="font-size:11px; color:#64748b;">(Optional)</span></label>
                    <input type="text" name="dsa_code" placeholder="e.g. DSA-HDFC-9923" value="<?php echo htmlspecialchars($banker['dsa_code'] ?? ''); ?>" style="text-transform: uppercase;">
                </div>
            </div>

            <!-- Contact Information -->
            <div class="form-section-title"> Contact Information</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="required">Phone Number</label>
                    <input type="text" name="contact_number" placeholder="Mobile / Desk Number" value="<?php echo htmlspecialchars($banker['contact_number'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label class="required">Email Address</label>
                    <input type="email" name="official_email" placeholder="official@bank.com" value="<?php echo htmlspecialchars($banker['official_email'] ?? ''); ?>" required>
                </div>
            </div>

            <!-- Loan Criteria -->
            <div class="form-section-title"> Loan Portfolio Criteria</div>
            <div class="form-grid">
                <div class="form-group full-width">
                    <label class="required">Loan Categories Handled</label>
                    <div style="display: flex; gap: 15px; flex-wrap: wrap; margin-top: 8px;">
                        <?php 
                        $opts = ['Home Loan', 'Personal Loan', 'Business Loan', 'Mortgage Loan', 'Education Loan', 'Vehicle Loan'];
                        foreach($opts as $opt): 
                            $checked = in_array($opt, $selected_categories) ? 'checked' : '';
                        ?>
                        <label style="display:flex; align-items:center; gap:5px; font-weight:normal; cursor:pointer;">
                            <input type="checkbox" name="loan_category[]" value="<?php echo $opt; ?>" <?php echo $checked; ?> style="width:18px;height:18px;"> <?php echo $opt; ?>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="form-group">
                    <label class="required">Min Loan Limit (₹)</label>
                    <input type="number" name="min_loan_limit" placeholder="e.g. 500000" min="0" value="<?php echo htmlspecialchars($banker['min_loan_limit']); ?>" required>
                </div>
                <div class="form-group">
                    <label class="required">Max Loan Limit (₹)</label>
                    <input type="number" name="max_loan_limit" placeholder="e.g. 50000000" min="0" value="<?php echo htmlspecialchars($banker['max_loan_limit']); ?>" required>
                </div>
            </div>

            <!-- Coverage Area -->
            <div class="form-section-title" style="margin-top:20px; color:#d97706; font-size:14px; border-bottom:1px solid #fde68a; padding-bottom:8px;"><i data-lucide="map" style="width:16px;height:16px;"></i> SERVICEABILITY & COVERAGE</div>
            <div class="form-grid" style="margin-top:15px; align-items: start;">
                <div class="form-group">
                    <label class="required">Coverage Type</label>
                    <select name="coverage_type" required onchange="toggleProfessionalCoverage(this.value)" style="padding: 12px; font-weight: 500;">
                        <option value="" disabled <?php echo empty($banker['coverage_type']) ? 'selected' : ''; ?>>Select Coverage Area Type</option>
                        <option value="PAN India" <?php echo ($banker['coverage_type']??'') == 'PAN India' ? 'selected' : ''; ?>> PAN India (All over India)</option>
                        <option value="State Level" <?php echo ($banker['coverage_type']??'') == 'State Level' ? 'selected' : ''; ?>>️ State Level (Specific States)</option>
                        <option value="City Level" <?php echo ($banker['coverage_type']??'') == 'City Level' ? 'selected' : ''; ?>>️ City Level (Specific Cities)</option>
                        <option value="Local Radius (KM)" <?php echo ($banker['coverage_type']??'') == 'Local Radius (KM)' ? 'selected' : ''; ?>> Local Radius (Specific Distance)</option>
                    </select>
                </div>
                
                <div class="form-group" id="professional_coverage_container" style="display:none; background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0; width: 100%;">
                    
                    <!-- Hidden input to store final string for DB -->
                    <input type="hidden" name="coverage_details" id="final_coverage_details" value="<?php echo htmlspecialchars($banker['coverage_details'] ?? ''); ?>">
                    
                    <!-- View for PAN India -->
                    <div id="cov_pan_india" style="display:none; text-align:center; color:#059669; padding: 10px;">
                        <i data-lucide="globe" style="width:32px; height:32px; margin-bottom:10px;"></i>
                        <div style="font-weight:600; font-size:15px;">Covering all states and cities across India.</div>
                    </div>
                    
                    <!-- View for State Level -->
                    <div id="cov_state_level" style="display:none;">
                        <label style="font-weight:600; margin-bottom:10px; display:block;">Select Serviceable States:</label>
                        <div id="states_grid" style="display:grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap:10px; max-height: 200px; overflow-y: auto; padding-right:10px;">
                            <!-- Populated by JS -->
                        </div>
                    </div>
                    
                    <!-- View for City Level -->
                    <div id="cov_city_level" style="display:none;">
                        <label style="font-weight:600; margin-bottom:10px; display:block;">Enter Serviceable Cities:</label>
                        <div style="display:flex; gap:10px; margin-bottom: 15px;">
                            <input type="text" id="city_input" list="all_cities_list" placeholder="Type a city and press Enter..." style="flex:1;">
                            <button type="button" class="btn btn-secondary" onclick="addCityChip()" style="padding: 10px 15px;">Add</button>
                        </div>
                        <datalist id="all_cities_list"></datalist>
                        <div id="city_chips_container" style="display:flex; flex-wrap:wrap; gap:8px;">
                            <!-- Chips go here -->
                        </div>
                    </div>
                    
                    <!-- View for Local Radius -->
                    <div id="cov_radius" style="display:none;">
                        <label style="font-weight:600; margin-bottom:10px; display:block;">Define Local Service Radius:</label>
                        <div style="display:flex; gap:15px;">
                            <div style="flex:1;">
                                <label style="font-size:12px; color:#64748b;">Distance (in KM)</label>
                                <input type="number" id="radius_km" placeholder="e.g. 50" min="1" oninput="updateRadiusFinal()">
                            </div>
                            <div style="flex:2;">
                                <label style="font-size:12px; color:#64748b;">Base Location / Pincode</label>
                                <input type="text" id="radius_base" placeholder="e.g. Mumbai 400001" oninput="updateRadiusFinal()">
                            </div>
                        </div>
                        <div id="radius_preview" style="margin-top:15px; font-size:13px; color:#3b82f6; font-weight:500;"></div>
                    </div>

                </div>
            </div>

            <!-- Action panel -->
            <div class="form-actions" style="margin-top: 30px; display: flex; justify-content: space-between; align-items: center;">
                <button type="submit" class="btn btn-primary"><i data-lucide="save"></i> Update Banker Details</button>
                <button type="button" class="btn" style="background:#fef2f2; color:#ef4444; border:1px solid #fca5a5;" onclick="deleteBanker(<?php echo $banker['id']; ?>)">
                    <i data-lucide="trash-2"></i> Delete Contact
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    // Initialize locations
    let selectedCities = [];
    const existingCoverageType = <?php echo json_encode($banker['coverage_type'] ?? ''); ?>;
    const existingCoverageDetails = <?php echo json_encode($banker['coverage_details'] ?? ''); ?>;

    document.addEventListener("DOMContentLoaded", () => {
        const stateSelect = document.getElementById('state-select');
        const statesGrid = document.getElementById('states_grid');
        const citiesList = document.getElementById('all_cities_list');
        let allCitiesArray = [];
        
        let prefilledStates = [];
        if(existingCoverageType === 'State Level') {
            prefilledStates = existingCoverageDetails.split(',').map(s => s.trim());
        }

        Object.keys(locationData).sort().forEach(state => {
            // Populate main form state dropdown
            const opt = document.createElement('option');
            opt.value = state;
            opt.textContent = state;
            if (state === preselectedState) {
                opt.selected = true;
            }
            stateSelect.appendChild(opt);

            // Populate coverage states grid
            const isChecked = prefilledStates.includes(state) ? 'checked' : '';
            const lbl = document.createElement('label');
            lbl.style.display = 'flex';
            lbl.style.alignItems = 'center';
            lbl.style.gap = '6px';
            lbl.style.fontSize = '13px';
            lbl.style.cursor = 'pointer';
            lbl.innerHTML = `<input type="checkbox" value="${state}" onchange="updateStatesFinal()" ${isChecked}> ${state}`;
            statesGrid.appendChild(lbl);

            // Collect all cities for the datalist
            allCitiesArray = allCitiesArray.concat(locationData[state]);
        });

        // Deduplicate and sort cities for datalist
        allCitiesArray = [...new Set(allCitiesArray)].sort();
        allCitiesArray.forEach(city => {
            const opt = document.createElement('option');
            opt.value = city;
            citiesList.appendChild(opt);
        });

        // Add Enter key listener for city input
        document.getElementById('city_input').addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                addCityChip();
            }
        });

        // Trigger city update if state is preselected
        if (preselectedState) {
            updateCities(preselectedCity);
        }

        // Initialize coverage fields
        const coverageType = document.querySelector('select[name="coverage_type"]').value;
        if (coverageType) {
            
            // Prefill cities if needed
            if (coverageType === 'City Level' && existingCoverageDetails) {
                selectedCities = existingCoverageDetails.split(',').map(c => c.trim()).filter(c => c);
                renderCityChips();
            }
            
            // Prefill radius if needed
            if (coverageType === 'Local Radius (KM)' && existingCoverageDetails) {
                const match = existingCoverageDetails.match(/^(\d+)\s+KM\s+radius\s+from\s+(.+)$/i);
                if (match) {
                    document.getElementById('radius_km').value = match[1];
                    document.getElementById('radius_base').value = match[2];
                    document.getElementById('radius_preview').innerText = `Final Area: ${existingCoverageDetails}`;
                }
            }

            toggleProfessionalCoverage(coverageType);
        }
    });

    function updateCities(selectedCity = null) {
        const stateSelect = document.getElementById('state-select');
        const citySelect = document.getElementById('city-select');
        const selectedState = stateSelect.value;
        
        citySelect.innerHTML = '<option value="" disabled selected>Select City</option>';
        if(selectedState && locationData[selectedState]) {
            locationData[selectedState].sort().forEach(city => {
                const opt = document.createElement('option');
                opt.value = city;
                opt.textContent = city;
                if (selectedCity && city === selectedCity) {
                    opt.selected = true;
                }
                citySelect.appendChild(opt);
            });
        }
    }

    function toggleProfessionalCoverage(type) {
        document.getElementById('professional_coverage_container').style.display = 'block';
        
        document.getElementById('cov_pan_india').style.display = 'none';
        document.getElementById('cov_state_level').style.display = 'none';
        document.getElementById('cov_city_level').style.display = 'none';
        document.getElementById('cov_radius').style.display = 'none';

        const finalInput = document.getElementById('final_coverage_details');

        if (type === 'PAN India') {
            document.getElementById('cov_pan_india').style.display = 'block';
            finalInput.value = 'All Over India';
        } else if (type === 'State Level') {
            document.getElementById('cov_state_level').style.display = 'block';
            updateStatesFinal();
        } else if (type === 'City Level') {
            document.getElementById('cov_city_level').style.display = 'block';
            updateCitiesFinal();
        } else if (type === 'Local Radius (KM)') {
            document.getElementById('cov_radius').style.display = 'block';
            updateRadiusFinal();
        }
    }

    function updateStatesFinal() {
        const checked = Array.from(document.querySelectorAll('#states_grid input:checked')).map(cb => cb.value);
        document.getElementById('final_coverage_details').value = checked.join(', ');
    }

    function addCityChip() {
        const input = document.getElementById('city_input');
        const val = input.value.trim();
        if(val && !selectedCities.includes(val)) {
            selectedCities.push(val);
            renderCityChips();
            input.value = '';
        }
    }

    function removeCityChip(city) {
        selectedCities = selectedCities.filter(c => c !== city);
        renderCityChips();
    }

    function renderCityChips() {
        const container = document.getElementById('city_chips_container');
        container.innerHTML = '';
        selectedCities.forEach(city => {
            const chip = document.createElement('div');
            chip.style.background = '#e0e7ff';
            chip.style.color = '#3730a3';
            chip.style.padding = '5px 12px';
            chip.style.borderRadius = '16px';
            chip.style.fontSize = '13px';
            chip.style.display = 'flex';
            chip.style.alignItems = 'center';
            chip.style.gap = '6px';
            chip.innerHTML = `<span>${city}</span> <i data-lucide="x" style="width:14px;height:14px;cursor:pointer;" onclick="removeCityChip('${city}')"></i>`;
            container.appendChild(chip);
        });
        lucide.createIcons();
        updateCitiesFinal();
    }

    function updateCitiesFinal() {
        document.getElementById('final_coverage_details').value = selectedCities.join(', ');
    }

    function updateRadiusFinal() {
        const km = document.getElementById('radius_km').value;
        const base = document.getElementById('radius_base').value;
        const finalInput = document.getElementById('final_coverage_details');
        const preview = document.getElementById('radius_preview');
        
        if(km && base) {
            const str = `${km} KM radius from ${base}`;
            finalInput.value = str;
            preview.innerText = `Final Area: ${str}`;
        } else {
            // retain existing value if they haven't typed yet, or empty it.
            // If they are editing, we don't want to clear it instantly on load, 
            // but the inputs are already pre-filled on load.
            finalInput.value = '';
            preview.innerText = '';
        }
    }

    async function saveBanker(event) {
        event.preventDefault();
        const form = document.getElementById('banker-registration-form');
        
        // Ensure at least one category is selected
        const checkboxes = form.querySelectorAll('input[name="loan_category[]"]:checked');
        if(checkboxes.length === 0) {
            showNotification('Please select at least one Loan Category.', 'error');
            return;
        }

        // Ensure coverage details is not empty unless it's PAN India
        const coverageType = form.querySelector('select[name="coverage_type"]').value;
        const finalCov = document.getElementById('final_coverage_details').value;
        if(!finalCov) {
            showNotification(`Please specify details for ${coverageType} coverage.`, 'warning');
            return;
        }

        const formData = new FormData(form);
        
        try {
            const response = await fetch('?api=update_banker', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (response.ok && data.success) {
                showNotification(data.message, 'success');
                setTimeout(() => {
                    location.href = 'bankers_list.php';
                }, 1000);
            } else {
                showNotification(data.error || 'Update failed.', 'error');
            }
        } catch (err) {
            showNotification('Connection failure.', 'error');
        }
    }

    function deleteBanker(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You are about to delete this Bank Contact permanently. This action cannot be undone.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('id', id);

                fetch('api.php?api=delete_banker', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Deleted!',
                            text: 'Bank contact has been deleted.',
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = 'bankers_list.php';
                        });
                    } else {
                        Swal.fire('Error!', data.error || 'Failed to delete contact.', 'error');
                    }
                })
                .catch(error => {
                    Swal.fire('Error!', 'A system error occurred.', 'error');
                });
            }
        });
    }

    // Initialize Bank Dropdowns
    document.write('<script src="assets/js/banks_directory.js"><\/script>');
    setTimeout(() => {
        populateBankDropdowns('bank_type_select', 'bank_name_select', <?php echo json_encode($banker['bank_type'] ?? ''); ?>, <?php echo json_encode($banker['bank_name'] ?? ''); ?>);
    }, 100);

    // Auto-fill via IFSC from Local Master Table
    async function fetchIfscDetails() {
        const ifsc = document.getElementById('ifsc_code_input').value.trim();
        if (!ifsc || ifsc.length !== 11) {
            return;
        }
        
        document.getElementById('ifsc-loading').style.display = 'inline';
        try {
            const res = await fetch(`?api=fetch_ifsc&ifsc=${ifsc}`);
            const data = await res.json();
            
            if (data.success && data.data) {
                const info = data.data;
                // Update State and City dropdowns dynamically
                const stateSelect = document.getElementById('state-select');
                
                // Find and select state case-insensitively
                Array.from(stateSelect.options).forEach(opt => {
                    if (opt.text.toUpperCase() === info.state.toUpperCase()) {
                        opt.selected = true;
                    }
                });
                
                updateCities(); // Populates cities for this state
                
                setTimeout(() => {
                    const citySelect = document.getElementById('city-select');
                    const normalizedCity = info.city.toUpperCase();
                    let matchFound = false;
                    
                    // First pass: exact match
                    Array.from(citySelect.options).forEach(opt => {
                        if (opt.value.toUpperCase() === normalizedCity) {
                            citySelect.value = opt.value;
                            matchFound = true;
                        }
                    });
                    
                    // Second pass: includes
                    if (!matchFound) {
                        Array.from(citySelect.options).forEach(opt => {
                            if (opt.value.toUpperCase().includes(normalizedCity) || normalizedCity.includes(opt.value.toUpperCase())) {
                                citySelect.value = opt.value;
                            }
                        });
                    }
                    
                    // Populate address
                    const addrInput = document.querySelector('input[name="address"]');
                    if (addrInput) addrInput.value = info.branch + ", " + info.address;
                    
                    // Try to auto-select bank type and name if they exist in directory
                    if (info.bank) {
                        const bankNameInput = document.getElementById('bank_name_select');
                        const bankTypeInput = document.getElementById('bank_type_select');
                        
                        let foundType = null;
                        let foundName = null;
                        for (const type in BANK_DIRECTORY) {
                            const match = BANK_DIRECTORY[type].find(b => info.bank.toUpperCase().includes(b.toUpperCase()) || b.toUpperCase().includes(info.bank.toUpperCase()));
                            if (match) {
                                foundType = type;
                                foundName = match;
                                break;
                            }
                        }
                        
                        if (foundType) {
                            bankTypeInput.value = foundType;
                            bankTypeInput.dispatchEvent(new Event('change'));
                            setTimeout(() => {
                                bankNameInput.value = foundName;
                            }, 50);
                        }
                    }
                    
                    showNotification('Bank details auto-filled successfully!', 'success');
                    document.getElementById('ifsc-loading').style.display = 'none';
                }, 100);
            } else {
                showNotification('IFSC not found. Please fill manually.', 'error');
                document.getElementById('ifsc-loading').style.display = 'none';
            }
        } catch (e) {
            showNotification('Error fetching IFSC details.', 'error');
            document.getElementById('ifsc-loading').style.display = 'none';
        }
    }

    async function fetchPincodeDetails(pincode) {
        if (pincode.length === 6) {
            document.getElementById('pincode-loading').style.display = 'inline';
            try {
                const response = await fetch(`https://api.postalpincode.in/pincode/${pincode}`);
                const data = await response.json();
                
                if (data[0].Status === 'Success') {
                    const state = data[0].PostOffice[0].State;
                    const district = data[0].PostOffice[0].District;
                    
                    const stateSelect = document.getElementById('state-select');
                    const citySelect = document.getElementById('city-select');
                    
                    // Match and select state
                    Array.from(stateSelect.options).forEach(opt => {
                        if (opt.text.toLowerCase() === state.toLowerCase()) {
                            opt.selected = true;
                            updateCities(); // Populates cities for the selected state
                        }
                    });
                    
                    // Allow cities to populate then select city
                    setTimeout(() => {
                        let matchFound = false;
                        
                        // First pass: Try exact match
                        Array.from(citySelect.options).forEach(opt => {
                            if (opt.text.toLowerCase() === district.toLowerCase()) {
                                opt.selected = true;
                                matchFound = true;
                            }
                        });
                        
                        // Second pass: Try includes/startsWith if no exact match
                        if (!matchFound) {
                            Array.from(citySelect.options).forEach(opt => {
                                if (opt.text.toLowerCase().includes(district.toLowerCase()) || district.toLowerCase().includes(opt.text.toLowerCase())) {
                                    opt.selected = true;
                                }
                            });
                        }
                        
                        document.getElementById('pincode-loading').style.display = 'none';
                        
                        // Automatically load branches now that City is selected!
                        if (typeof loadBranches === 'function') {
                            loadBranches();
                        }
                    }, 100);
                    
                } else {
                    document.getElementById('pincode-loading').style.display = 'none';
                }
            } catch (error) {
                console.error('Error fetching pincode details:', error);
                document.getElementById('pincode-loading').style.display = 'none';
            }
        }
    }

    async function loadBranches() {
        const bank = document.getElementById('bank_name_select').value;
        const city = document.getElementById('city-select').value;
        const dropdown = document.getElementById('branch_dropdown');
        const ifscInput = document.getElementById('ifsc_code_input');
        
        if (!bank || !city || bank === "" || city === "") {
            dropdown.innerHTML = '<option value="">▼ Select Branch</option>';
            dropdown.style.display = 'none';
            ifscInput.style.display = 'block';
            return;
        }

        try {
            const res = await fetch(`?api=get_branches&bank=${encodeURIComponent(bank)}&city=${encodeURIComponent(city)}`);
            const data = await res.json();
            
            dropdown.innerHTML = '<option value="">▼ Select Branch</option>';
            dropdown.innerHTML += '<option value="MANUAL">+ Branch Not Listed (Enter Manually)</option>';
            
            if (data.success && data.data && data.data.length > 0) {
                data.data.forEach(b => {
                    const opt = document.createElement('option');
                    opt.value = b.ifsc;
                    opt.textContent = `${b.branch} - ${b.address.substring(0, 40)}`;
                    dropdown.appendChild(opt);
                });
                dropdown.style.display = 'block';
                ifscInput.style.display = 'none'; // Hide manual input to encourage dropdown usage
                
                dropdown.onchange = function() {
                    if (this.value === 'MANUAL') {
                        dropdown.style.display = 'none';
                        ifscInput.style.display = 'block';
                        ifscInput.value = '';
                        ifscInput.focus();
                    } else if (this.value !== '') {
                        ifscInput.value = this.value;
                        fetchIfscDetails();
                    }
                };
            } else {
                dropdown.style.display = 'none';
                ifscInput.style.display = 'block';
            }
        } catch (e) {
            console.error('Error fetching branches:', e);
        }
    }

    // Attach listeners for dynamic IFSC fetching
    document.getElementById('bank_name_select').addEventListener('change', loadBranches);
    document.getElementById('city-select').addEventListener('change', loadBranches);

</script>

<?php require_once 'footer.php'; ?>
