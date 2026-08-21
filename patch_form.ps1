$content = Get-Content -Raw "c:\Users\pc\Downloads\client mgmt2\apply.php"

$newForm = @"
            <div id="progress-container" style="margin-bottom: 24px;">
                <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:12px; font-weight:600; color:#64748b;">
                    <span id="step-text">Step 1 of 3: Personal Info</span>
                    <span id="step-percent">33%</span>
                </div>
                <div style="height:6px; background:#e2e8f0; border-radius:10px; overflow:hidden;">
                    <div id="progress-bar" style="height:100%; width:33%; background:var(--primary); transition:0.3s ease;"></div>
                </div>
            </div>

            <form id="applyForm" method="POST" enctype="multipart/form-data" onsubmit="return prepareSubmit()">
                
                <!-- STEP 1: Personal Details -->
                <div class="form-step" id="step1">
                    <h3 style="margin-top:0; color:var(--primary); font-family:'Outfit', sans-serif;">Personal Information</h3>
                    <div class="form-group">
                        <label>Full Name <span style="color:#ef4444">*</span></label>
                        <input type="text" name="name" required placeholder="Enter your full name">
                    </div>
                    <div class="form-group">
                        <label>Mobile Number <span style="color:#ef4444">*</span></label>
                        <input type="tel" name="mobile" required placeholder="10-digit mobile number" pattern="[0-9]{10}">
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" placeholder="your@email.com">
                    </div>
                    <button type="button" class="btn" onclick="nextStep(2)">Continue to Loan Details <i data-lucide="arrow-right"></i></button>
                </div>

                <!-- STEP 2: Loan Details -->
                <div class="form-step" id="step2" style="display:none;">
                    <h3 style="margin-top:0; color:var(--primary); font-family:'Outfit', sans-serif;">Loan Requirements</h3>
                    <div class="form-group">
                        <label class="required">Loan Type <span style="color:#ef4444">*</span></label>
                        <select name="loan_type" id="loan_type" required onchange="updateSubTypes()">
                            <option value="">-- Select Loan Type --</option>
                            <option value="Home Loan">Home Loan</option>
                            <option value="Loan Against Property (LAP)">Loan Against Property (LAP)</option>
                            <option value="Business Loan">Business Loan</option>
                            <option value="Personal Loan">Personal Loan</option>
                            <option value="Auto / Vehicle Loan">Auto / Vehicle Loan</option>
                            <option value="Commercial Property Loan">Commercial Property Loan</option>
                            <option value="Professional Loan">Professional Loan</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="required">Loan Sub Type <span style="color:#ef4444">*</span></label>
                        <select name="loan_sub_type" id="loan_sub_type" required>
                            <option value="">-- Select Sub Type --</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Required Loan Amount (₹) <span style="color:#ef4444">*</span></label>
                        <input type="text" id="amount_display" required placeholder="e.g. 5,00,000" oninput="formatAmount(this)">
                        <input type="hidden" name="amount" id="amount_real">
                    </div>
                    <div style="display:flex; gap:10px;">
                        <button type="button" class="btn" style="background:#e2e8f0; color:#475569;" onclick="prevStep(1)"><i data-lucide="arrow-left"></i> Back</button>
                        <button type="button" class="btn" onclick="nextStep(3)">Continue to Documents <i data-lucide="arrow-right"></i></button>
                    </div>
                </div>

                <!-- STEP 3: Address & Documents -->
                <div class="form-step" id="step3" style="display:none;">
                    <h3 style="margin-top:0; color:var(--primary); font-family:'Outfit', sans-serif;">Address & Documents</h3>
                    <div class="form-group">
                        <label>Complete Address <span style="color:#ef4444">*</span></label>
                        <textarea name="address" required placeholder="House/Flat No, Building Name, Street Area..." rows="2" style="resize:vertical;"></textarea>
                    </div>
                    <div class="form-group address-grid">
                        <div>
                            <label>Pincode <span style="color:#ef4444">*</span></label>
                            <input type="text" name="pincode" id="pincode_input" required placeholder="6-digit PIN" maxlength="6" pattern="\d{6}" oninput="fetchPinDetails(this.value)" style="margin-bottom:4px;">
                            <span id="pin_status" style="font-size:12px; color:var(--primary); font-weight:500;"></span>
                        </div>
                        <div>
                            <label>State <span style="color:#ef4444">*</span></label>
                            <input type="text" name="state" id="state_input" required placeholder="State">
                        </div>
                        <div class="full-width-desktop">
                            <label>City / District <span style="color:#ef4444">*</span></label>
                            <input type="text" name="city" id="city_input" required placeholder="City">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>PAN Number (Optional)</label>
                        <input type="text" name="pan" placeholder="ABCDE1234F" style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase()">
                    </div>
                    <div class="form-group">
                        <label>Aadhar Number (Optional)</label>
                        <input type="text" name="aadhar" placeholder="12-digit Aadhar" maxlength="12" pattern="\d{12}">
                    </div>
                    <div class="form-group" style="text-align:center;">
                        <label style="display:block; margin-bottom:10px;">Take a Photo <span style="color:#ef4444">*</span></label>
                        <input type="file" name="photo" id="photo" accept="image/*" capture="user" required style="display:none;" onchange="previewPhoto(event)">
                        <button type="button" onclick="document.getElementById('photo').click()" style="background:#f1f5f9; border:2px dashed #cbd5e1; padding:20px; border-radius:12px; width:100%; cursor:pointer; color:#64748b; font-weight:600;">
                            <i data-lucide="camera" style="width:32px; height:32px; margin-bottom:8px; display:block; margin:0 auto 8px;"></i>
                            Click to Capture Photo
                        </button>
                        <img id="photo-preview" style="display:none; width:100px; height:100px; object-fit:cover; border-radius:12px; margin: 15px auto 0; border:2px solid var(--primary);">
                    </div>
                    <div style="display:flex; gap:10px;">
                        <button type="button" class="btn" style="background:#e2e8f0; color:#475569;" onclick="prevStep(2)"><i data-lucide="arrow-left"></i> Back</button>
                        <button type="submit" class="btn"><i data-lucide="send"></i> Submit Application</button>
                    </div>
                </div>
            </form>
            
            <!-- TRUST BADGES -->
            <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-around; opacity: 0.7;">
                <div style="display: flex; flex-direction: column; align-items: center; gap: 4px;">
                    <i data-lucide="lock" style="width: 16px; height: 16px; color: #475569;"></i>
                    <span style="font-size: 10px; font-weight: 600; color: #64748b;">256-bit Secure</span>
                </div>
                <div style="display: flex; flex-direction: column; align-items: center; gap: 4px;">
                    <i data-lucide="shield" style="width: 16px; height: 16px; color: #475569;"></i>
                    <span style="font-size: 10px; font-weight: 600; color: #64748b;">Bank Level Security</span>
                </div>
                <div style="display: flex; flex-direction: column; align-items: center; gap: 4px;">
                    <i data-lucide="award" style="width: 16px; height: 16px; color: #475569;"></i>
                    <span style="font-size: 10px; font-weight: 600; color: #64748b;">Trusted Partner</span>
                </div>
            </div>
"@

$startTag = "<form method=`"POST`" enctype=`"multipart/form-data`">"
$endTag = "</form>"

$regex = "(?s)($startTag)(.*?)($endTag)"
$content = $content -replace $regex, $newForm

Set-Content -Path "c:\Users\pc\Downloads\client mgmt2\apply.php" -Value $content
