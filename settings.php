<?php
require_once __DIR__ . '/config.php';

$page_title = "CRM Settings";
$page_subtitle = "Company Profile, User Accounts, and Templates Configuration";
require_once __DIR__ . '/header.php';
?>

<div class="view-container">
    <!-- COMPANY PROFILE & SMTP SETTINGS -->
    <form id="settings-form" onsubmit="saveCompanySettings(event)">
        <div class="card" style="background: var(--bg-card); border-radius: var(--radius-lg); box-shadow: var(--shadow-md); border: 1px solid #f1f5f9; padding: 24px; margin-bottom: 30px;">
            <div class="card-title-bar" style="border-bottom: 1px solid #f1f5f9; padding-bottom: 15px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
                <h2 style="font-family: 'Outfit', sans-serif; font-weight: 700; font-size: 18px; color: var(--text-primary); margin: 0;">CRM Settings & Company Profile</h2>
                <div class="badge-locked" style="background-color: var(--primary-light); color: var(--primary); padding: 6px 12px; border-radius: var(--radius-sm); font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
                    <i data-lucide="settings" style="width: 14px; height: 14px;"></i> Configurations
                </div>
            </div>
            
            <div class="form-section-title" style="font-family: 'Outfit', sans-serif; font-weight: 600; font-size: 15px; color: var(--primary); margin: 25px 0 15px 0; border-bottom: 1px dashed var(--primary-border); padding-bottom: 8px;">🏢 Company Profile & Billing Details</div>
            <div class="form-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                <div class="form-group">
                    <label class="required" style="display: block; font-weight: 500; font-size: 13px; margin-bottom: 6px; color: var(--text-primary);">Company / Business Name</label>
                    <input type="text" name="company_name" value="<?php echo htmlspecialchars($profile['company_name']); ?>" required style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 14px;">
                </div>
                <div class="form-group">
                    <label class="required" style="display: block; font-weight: 500; font-size: 13px; margin-bottom: 6px; color: var(--text-primary);">GSTIN Number</label>
                    <input type="text" name="gstin" value="<?php echo htmlspecialchars($profile['gstin']); ?>" placeholder="15-digit GSTIN ID" minlength="15" maxlength="15" required style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 14px;">
                </div>
                <div class="form-group">
                    <label class="required" style="display: block; font-weight: 500; font-size: 13px; margin-bottom: 6px; color: var(--text-primary);">Billing Address Line 1</label>
                    <input type="text" name="address_line1" value="<?php echo htmlspecialchars($profile['address_line1']); ?>" placeholder="Plot No, Street, Area" required style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 14px;">
                </div>
                <div class="form-group">
                    <label style="display: block; font-weight: 500; font-size: 13px; margin-bottom: 6px; color: var(--text-primary);">Billing Address Line 2</label>
                    <input type="text" name="address_line2" value="<?php echo htmlspecialchars($profile['address_line2'] ?? ''); ?>" placeholder="Floor, Wing, Landmark" style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 14px;">
                </div>
                <div class="form-group">
                    <label class="required" style="display: block; font-weight: 500; font-size: 13px; margin-bottom: 6px; color: var(--text-primary);">City</label>
                    <input type="text" name="city" value="<?php echo htmlspecialchars($profile['city']); ?>" placeholder="e.g. Mumbai" required style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 14px;">
                </div>
                <div class="form-group">
                    <label class="required" style="display: block; font-weight: 500; font-size: 13px; margin-bottom: 6px; color: var(--text-primary);">State</label>
                    <input type="text" name="state" value="<?php echo htmlspecialchars($profile['state']); ?>" placeholder="e.g. Maharashtra" required style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 14px;">
                </div>
                <div class="form-group">
                    <label class="required" style="display: block; font-weight: 500; font-size: 13px; margin-bottom: 6px; color: var(--text-primary);">Pincode</label>
                    <input type="text" name="pincode" value="<?php echo htmlspecialchars($profile['pincode']); ?>" placeholder="6-digit Pincode" minlength="6" maxlength="6" required style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 14px;">
                </div>
                <div class="form-group">
                    <label class="required" style="display: block; font-weight: 500; font-size: 13px; margin-bottom: 6px; color: var(--text-primary);">Country</label>
                    <input type="text" name="country" value="<?php echo htmlspecialchars($profile['country']); ?>" required style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 14px;">
                </div>
                <div class="form-group">
                    <label class="required" style="display: block; font-weight: 500; font-size: 13px; margin-bottom: 6px; color: var(--text-primary);">Support / Billing Email</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($profile['email']); ?>" required style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 14px;">
                </div>
                <div class="form-group">
                    <label style="display: block; font-weight: 500; font-size: 13px; margin-bottom: 6px; color: var(--text-primary);">Contact Phone Number</label>
                    <input type="text" name="mobile" value="<?php echo htmlspecialchars($profile['mobile'] ?? ''); ?>" style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 14px;">
                </div>
            </div>

            <div class="form-section-title" style="font-family: 'Outfit', sans-serif; font-weight: 600; font-size: 15px; color: var(--primary); margin: 25px 0 15px 0; border-bottom: 1px dashed var(--primary-border); padding-bottom: 8px;">🏦 Bank Payment Details (Shown on Invoices)</div>
            <div class="form-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                <div class="form-group">
                    <label style="display: block; font-weight: 500; font-size: 13px; margin-bottom: 6px; color: var(--text-primary);">Bank Name</label>
                    <input type="text" name="bank_name" value="<?php echo htmlspecialchars($profile['bank_name'] ?? ''); ?>" placeholder="e.g. HDFC Bank" style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 14px;">
                </div>
                <div class="form-group">
                    <label style="display: block; font-weight: 500; font-size: 13px; margin-bottom: 6px; color: var(--text-primary);">Account Number</label>
                    <input type="text" name="account_number" value="<?php echo htmlspecialchars($profile['account_number'] ?? ''); ?>" placeholder="e.g. 50100987654321" style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 14px;">
                </div>
                <div class="form-group">
                    <label style="display: block; font-weight: 500; font-size: 13px; margin-bottom: 6px; color: var(--text-primary);">IFSC Code</label>
                    <input type="text" name="ifsc_code" value="<?php echo htmlspecialchars($profile['ifsc_code'] ?? ''); ?>" placeholder="11-character IFSC" minlength="11" maxlength="11" style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 14px;">
                </div>
            </div>

            <div class="form-section-title" style="font-family: 'Outfit', sans-serif; font-weight: 600; font-size: 15px; color: var(--primary); margin: 25px 0 15px 0; border-bottom: 1px dashed var(--primary-border); padding-bottom: 8px;">👤 User / Staff Account</div>
            <div class="form-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                <div class="form-group">
                    <label class="required" style="display: block; font-weight: 500; font-size: 13px; margin-bottom: 6px; color: var(--text-primary);">Staff Contact Person (Staff Name)</label>
                    <input type="text" name="contact_person" value="<?php echo htmlspecialchars($profile['contact_person']); ?>" required style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 14px;">
                </div>
            </div>
            
            <div class="form-section-title" style="font-family: 'Outfit', sans-serif; font-weight: 600; font-size: 15px; color: var(--primary); margin: 25px 0 15px 0; border-bottom: 1px dashed var(--primary-border); padding-bottom: 8px;">📧 SMTP Email Configuration</div>
            <div class="form-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                <div class="form-group">
                    <label style="display: block; font-weight: 500; font-size: 13px; margin-bottom: 6px; color: var(--text-primary);">SMTP Host</label>
                    <input type="text" name="smtp_host" value="<?php echo htmlspecialchars($profile['smtp_host'] ?? ''); ?>" placeholder="e.g. smtp.gmail.com" style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 14px;">
                </div>
                <div class="form-group">
                    <label style="display: block; font-weight: 500; font-size: 13px; margin-bottom: 6px; color: var(--text-primary);">SMTP Port</label>
                    <input type="text" name="smtp_port" value="<?php echo htmlspecialchars($profile['smtp_port'] ?? ''); ?>" placeholder="e.g. 587 or 465" style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 14px;">
                </div>
                <div class="form-group">
                    <label style="display: block; font-weight: 500; font-size: 13px; margin-bottom: 6px; color: var(--text-primary);">SMTP Username</label>
                    <input type="text" name="smtp_username" value="<?php echo htmlspecialchars($profile['smtp_username'] ?? ''); ?>" placeholder="e.g. you@gmail.com" style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 14px;">
                </div>
                <div class="form-group">
                    <label style="display: block; font-weight: 500; font-size: 13px; margin-bottom: 6px; color: var(--text-primary);">SMTP Password / App Password</label>
                    <input type="password" name="smtp_password" value="<?php echo htmlspecialchars($profile['smtp_password'] ?? ''); ?>" placeholder="Enter password" style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 14px;">
                </div>
                <div class="form-group">
                    <label style="display: block; font-weight: 500; font-size: 13px; margin-bottom: 6px; color: var(--text-primary);">Encryption</label>
                    <select name="smtp_encryption" style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 14px; background: white;">
                        <option value="" <?php echo ($profile['smtp_encryption'] ?? '') === '' ? 'selected' : ''; ?>>None</option>
                        <option value="tls" <?php echo ($profile['smtp_encryption'] ?? '') === 'tls' ? 'selected' : ''; ?>>TLS</option>
                        <option value="ssl" <?php echo ($profile['smtp_encryption'] ?? '') === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                    </select>
                </div>
            </div>
            
            <div style="margin-top: 25px; display: flex; justify-content: flex-end;">
                <button type="submit" class="btn btn-primary" id="save-settings-btn" style="padding: 10px 20px; font-weight: 600; border-radius: var(--radius-md); border: none; background: var(--primary); color: white; cursor: pointer; display: inline-flex; align-items: center; gap: 8px;">
                    <i data-lucide="save" style="width: 18px; height: 18px;"></i> Save Configurations
                </button>
            </div>
        </div>
    </form>

    <!-- ADMIN USER MANAGEMENT SECTION -->
    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin'): ?>
    <div class="card" style="background: var(--bg-card); border-radius: var(--radius-lg); box-shadow: var(--shadow-md); border: 1px solid #f1f5f9; padding: 24px; margin-bottom: 30px;">
        <div class="card-title-bar" style="border-bottom: 1px solid #f1f5f9; padding-bottom: 15px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
            <h2 style="font-family: 'Outfit', sans-serif; font-weight: 700; font-size: 18px; color: var(--text-primary); margin: 0;">Manage Users</h2>
            <div class="badge-locked" style="background-color: var(--primary-light); color: var(--primary); padding: 6px 12px; border-radius: var(--radius-sm); font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
                <i data-lucide="shield" style="width: 14px; height: 14px;"></i> Admin Only
            </div>
        </div>
        
        <form id="create-user-form" onsubmit="createUser(event)">
            <div class="form-section-title" style="font-family: 'Outfit', sans-serif; font-weight: 600; font-size: 15px; color: var(--primary); margin: 15px 0 10px 0;">➕ Register New Staff Account</div>
            <div class="form-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; align-items: flex-end;">
                <div class="form-group">
                    <label class="required" style="display: block; font-weight: 500; font-size: 13px; margin-bottom: 6px; color: var(--text-primary);">Full Name</label>
                    <input type="text" name="name" required placeholder="Staff Full Name" style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 14px;">
                </div>
                <div class="form-group">
                    <label class="required" style="display: block; font-weight: 500; font-size: 13px; margin-bottom: 6px; color: var(--text-primary);">Username / Login ID</label>
                    <input type="text" name="username" required placeholder="Login ID" style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 14px;">
                </div>
                <div class="form-group">
                    <label class="required" style="display: block; font-weight: 500; font-size: 13px; margin-bottom: 6px; color: var(--text-primary);">Password</label>
                    <input type="password" name="password" required minlength="6" placeholder="At least 6 chars" style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 14px;">
                </div>
                <input type="hidden" name="role" value="Staff">
                <div class="form-group">
                    <button type="submit" class="btn btn-secondary" style="width: 100%; padding: 10px 14px; font-weight: 600; border-radius: var(--radius-sm); cursor: pointer;">Create User</button>
                </div>
            </div>
        </form>
        
        <div class="form-section-title" style="font-family: 'Outfit', sans-serif; font-weight: 600; font-size: 15px; color: var(--text-primary); margin: 30px 0 15px 0; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px;">👥 Existing CRM Users</div>
        <div id="users-list-container">
            <p style="color: var(--text-light); font-size: 0.9rem;">Loading user roster...</p>
        </div>
    </div>
    <?php endif; ?>

    <!-- EMAIL TEMPLATES MANAGER -->
    <div class="card" style="background: var(--bg-card); border-radius: var(--radius-lg); box-shadow: var(--shadow-md); border: 1px solid #f1f5f9; padding: 24px; margin-bottom: 30px;">
        <div class="card-title-bar" style="border-bottom: 1px solid #f1f5f9; padding-bottom: 15px; margin-bottom: 20px;">
            <h2 style="font-family: 'Outfit', sans-serif; font-weight: 700; font-size: 18px; color: var(--text-primary); margin: 0;">Email Templates Manager</h2>
        </div>
        
        <form id="create-template-form" onsubmit="saveTemplate(event)" enctype="multipart/form-data">
            <div class="form-section-title" style="font-family: 'Outfit', sans-serif; font-weight: 600; font-size: 15px; color: var(--primary); margin: 15px 0 10px 0;">➕ Create New Pitch or Follow-up Template</div>
            <div class="form-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                <div class="form-group">
                    <label class="required" style="display: block; font-weight: 500; font-size: 13px; margin-bottom: 6px; color: var(--text-primary);">Template Name</label>
                    <input type="text" name="template_name" placeholder="e.g. Standard Pitch" required style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 14px;">
                </div>
                <div class="form-group">
                    <label class="required" style="display: block; font-weight: 500; font-size: 13px; margin-bottom: 6px; color: var(--text-primary);">Template Type</label>
                    <select name="type" required style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 14px; background: white;">
                        <option value="Pitch">Pitch</option>
                        <option value="PPT">PPT</option>
                        <option value="Custom Mail">Custom Mail</option>
                    </select>
                </div>
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label class="required" style="display: block; font-weight: 500; font-size: 13px; margin-bottom: 6px; color: var(--text-primary);">Subject Line</label>
                    <input type="text" name="subject" placeholder="Email Subject" required style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 14px;">
                </div>
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label class="required" style="display: block; font-weight: 500; font-size: 13px; margin-bottom: 6px; color: var(--text-primary);">Email Body</label>
                    <textarea name="body" rows="6" placeholder="Write your template text here..." required style="width: 100%; padding: 12px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 14px; font-family: inherit; line-height: 1.5;"></textarea>
                </div>
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label style="display: block; font-weight: 500; font-size: 13px; margin-bottom: 6px; color: var(--text-primary);">Default Attachment (Optional PDF / Document / Presentation)</label>
                    <input type="file" name="attachment" accept=".ppt,.pptx,.pdf,.doc,.docx,.xls,.xlsx,.zip" style="width: 100%; padding: 8px 0; font-size: 14px;">
                </div>
            </div>
            <div style="margin-top: 20px; display: flex; justify-content: flex-end;">
                <button type="submit" class="btn btn-secondary" style="padding: 10px 20px; font-weight: 600; border-radius: var(--radius-md); cursor: pointer;">Save Template</button>
            </div>
        </form>
        
        <div class="form-section-title" style="font-family: 'Outfit', sans-serif; font-weight: 600; font-size: 15px; color: var(--text-primary); margin: 30px 0 15px 0; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px;">📚 Saved Email Templates</div>
        <div id="templates-list-container">
            <p style="color: var(--text-light); font-size: 0.9rem;">Loading templates list...</p>
        </div>
    </div>

    <!-- PRESENTATION DECK LIBRARY -->
    <div class="card" style="background: var(--bg-card); border-radius: var(--radius-lg); box-shadow: var(--shadow-md); border: 1px solid #f1f5f9; padding: 24px; margin-bottom: 30px;">
        <div class="card-title-bar" style="border-bottom: 1px solid #f1f5f9; padding-bottom: 15px; margin-bottom: 20px;">
            <h2 style="font-family: 'Outfit', sans-serif; font-weight: 700; font-size: 18px; color: var(--text-primary); margin: 0;">Presentation (PPT) Library</h2>
        </div>
        
        <form id="ppt-upload-form" onsubmit="savePpt(event)" enctype="multipart/form-data">
            <div class="form-section-title" style="font-family: 'Outfit', sans-serif; font-weight: 600; font-size: 15px; color: var(--primary); margin: 15px 0 10px 0;">➕ Upload New Presentation Deck</div>
            <div class="form-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; align-items: flex-end;">
                <div class="form-group">
                    <label class="required" style="display: block; font-weight: 500; font-size: 13px; margin-bottom: 6px; color: var(--text-primary);">Presentation Title</label>
                    <input type="text" name="original_name" required placeholder="e.g. Corporate Pitch Deck 2026" style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 14px;">
                </div>
                <div class="form-group">
                    <label class="required" style="display: block; font-weight: 500; font-size: 13px; margin-bottom: 6px; color: var(--text-primary);">File Attachment (.ppt, .pptx, .pdf)</label>
                    <input type="file" name="ppt_file" required accept=".ppt,.pptx,.pdf" style="width: 100%; padding: 8px 0; font-size: 14px;">
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-secondary" style="width: 100%; padding: 10px 14px; font-weight: 600; border-radius: var(--radius-sm); cursor: pointer;">Upload Deck</button>
                </div>
            </div>
        </form>
        
        <div class="form-section-title" style="font-family: 'Outfit', sans-serif; font-weight: 600; font-size: 15px; color: var(--text-primary); margin: 30px 0 15px 0; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px;">📚 Saved Presentation Files</div>
        <div id="ppts-list-container">
            <p style="color: var(--text-light); font-size: 0.9rem;">Loading presentation list...</p>
        </div>
    </div>
</div>

<script>
    // Save Company Settings Form Handler
    function saveCompanySettings(e) {
        e.preventDefault();
        const btn = document.getElementById('save-settings-btn');
        btn.disabled = true;
        btn.innerText = 'Saving...';
        
        const formData = new FormData(document.getElementById('settings-form'));
        
        fetch('?api=save_settings', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                
                // Update local settings references
                companyProfile.company_name = formData.get('company_name');
                companyProfile.gstin = formData.get('gstin');
                companyProfile.address_line1 = formData.get('address_line1');
                companyProfile.address_line2 = formData.get('address_line2');
                companyProfile.city = formData.get('city');
                companyProfile.state = formData.get('state');
                companyProfile.pincode = formData.get('pincode');
                companyProfile.country = formData.get('country');
                companyProfile.email = formData.get('email');
                companyProfile.mobile = formData.get('mobile');
                companyProfile.contact_person = formData.get('contact_person');
                companyProfile.bank_name = formData.get('bank_name');
                companyProfile.account_number = formData.get('account_number');
                companyProfile.ifsc_code = formData.get('ifsc_code');
                
                // Dynamically update user avatar and user labels across header/sidebar
                const sidebarUser = document.getElementById('sidebar-user-name');
                const headerUser = document.getElementById('header-user-name');
                const headerAvatar = document.getElementById('header-user-avatar');
                
                if (sidebarUser) sidebarUser.innerText = companyProfile.contact_person;
                if (headerUser) headerUser.innerText = companyProfile.contact_person;
                
                if (headerAvatar) {
                    const names = companyProfile.contact_person.split(' ');
                    let initials = '';
                    names.forEach(n => {
                        if (n) initials += n[0];
                    });
                    initials = initials.toUpperCase().substring(0, 2);
                    headerAvatar.innerText = initials;
                }
            } else {
                showNotification(data.error || 'Failed to save settings.', 'error');
            }
        })
        .catch(err => {
            console.error(err);
            showNotification('Connection error while saving settings.', 'error');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i data-lucide="save" style="width: 18px; height: 18px;"></i> Save Configurations';
            lucide.createIcons();
        });
    }

    // Load templates list in setting screen
    function loadTemplatesList() {
        fetch('?api=get_templates')
        .then(res => res.json())
        .then(data => {
            window.globalEmailTemplates = data;
            
            const container = document.getElementById('templates-list-container');
            if (!container) return;

            if (data.length === 0) {
                container.innerHTML = '<p style="color: var(--text-light); font-size: 0.9rem;">No templates saved yet.</p>';
            } else {
                let html = '<table class="data-table" style="width: 100%; border-collapse: collapse; text-align: left; font-size: 14px;">' +
                           '<thead><tr style="border-bottom: 2px solid var(--border); color: var(--text-muted); font-weight:600;"><th style="padding:12px;">Template Details</th><th style="padding:12px; text-align:right;">Actions</th></tr></thead><tbody>';
                data.forEach(t => {
                    let attachBadge = t.attachment_name ? `<span style="font-size: 11px; background: var(--primary-light); color: var(--primary); padding: 2px 6px; border-radius: 4px; margin-left: 8px; font-weight:500;">📎 ${t.attachment_name}</span>` : '';
                    
                    let buttonsHtml = ``;
                    if (t.delete_requested == 1) {
                        if (currentUser && currentUser.role === 'Admin') {
                            buttonsHtml = `
                                <div style="display:flex; gap:0.5rem; justify-content:flex-end;">
                                    <button type="button" class="btn btn-secondary" onclick="approveDeleteTemplate(${t.id})" style="padding: 4px 8px; font-size: 12px; background: #fee2e2; color: #ef4444; border: 1px solid #fca5a5; border-radius: 6px; cursor: pointer;">Approve Delete</button>
                                    <button type="button" class="btn btn-secondary" onclick="rejectDeleteTemplate(${t.id})" style="padding: 4px 8px; font-size: 12px; border-radius: 6px; cursor: pointer;">Reject</button>
                                </div>
                            `;
                        } else {
                            buttonsHtml = `<span style="font-size:12px; color:#ef4444; border:1px solid #fca5a5; padding: 4px 8px; border-radius: 6px; background: #fee2e2; font-weight:600;">🔴 Pending Admin Deletion Approval</span>`;
                        }
                    } else {
                        buttonsHtml = `<button type="button" class="btn btn-danger" style="padding: 4px 8px; font-size: 12px; background: #fee2e2; color: #ef4444; border: 1px solid #fca5a5; border-radius: 6px; cursor: pointer;" onclick="deleteTemplate(${t.id})">Delete</button>`;
                    }
                    
                    html += `<tr style="border-bottom: 1px solid var(--border);">
                        <td style="padding:14px 12px;">
                            <strong style="display:inline-block; cursor:pointer; color:var(--primary); text-decoration:underline; font-weight:600; margin-bottom:4px;" onclick="showTemplatePreview(${t.id})">${t.template_name} (${t.type})</strong>
                            <div style="font-size: 13px; color: var(--text-muted);">Subject: ${t.subject} ${attachBadge}</div>
                        </td>
                        <td style="padding:14px 12px; text-align:right;">${buttonsHtml}</td>
                    </tr>`;
                });
                html += '</tbody></table>';
                container.innerHTML = html;
            }
        });
    }

    // Save Email Template Form submission
    function saveTemplate(e) {
        e.preventDefault();
        const fd = new FormData(e.target);
        fetch('?api=save_template', {method: 'POST', body: fd})
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                e.target.reset();
                loadTemplatesList();
            } else {
                showNotification(data.error || 'Failed to save template.', 'error');
            }
        });
    }

    // Trigger Delete Email Template
    function deleteTemplate(id) {
        if (!confirm('Are you sure you want to delete this template?')) return;
        const fd = new FormData();
        fd.append('id', id);
        fetch('?api=delete_template', {method: 'POST', body: fd})
        .then(r => r.json())
        .then(d => {
            showNotification(d.message || 'Deletion request sent', d.success ? 'success' : 'error');
            loadTemplatesList();
        });
    }

    // Load PDF/PPT Decks List
    function loadPptsList() {
        fetch('?api=get_ppts')
        .then(res => res.json())
        .then(data => {
            const container = document.getElementById('ppts-list-container');
            if (!container) return;

            if (data.length === 0) {
                container.innerHTML = '<p style="color: var(--text-light); font-size: 0.9rem;">No presentations uploaded yet.</p>';
            } else {
                let html = '<table class="data-table" style="width: 100%; border-collapse: collapse; text-align: left; font-size: 14px;">' +
                           '<thead><tr style="border-bottom: 2px solid var(--border); color: var(--text-muted); font-weight:600;"><th style="padding:12px;">Deck Title</th><th style="padding:12px; text-align:right;">Actions</th></tr></thead><tbody>';
                data.forEach(p => {
                    let buttonsHtml = ``;
                    if (p.delete_requested == 1) {
                        if (currentUser && currentUser.role === 'Admin') {
                            buttonsHtml = `
                                <div style="display:flex; gap:0.5rem; justify-content:flex-end;">
                                    <button type="button" class="btn btn-secondary" onclick="approveDeletePpt(${p.id})" style="padding: 4px 8px; font-size: 11px; background: #fee2e2; color: #ef4444; border: 1px solid #fca5a5; border-radius: 6px; cursor: pointer;">Approve Delete</button>
                                    <button type="button" class="btn btn-secondary" onclick="rejectDeletePpt(${p.id})" style="padding: 4px 8px; font-size: 11px; border-radius: 6px; cursor: pointer;">Reject</button>
                                </div>
                            `;
                        } else {
                            buttonsHtml = `<span style="font-size:11px; color:#ef4444; border:1px solid #fca5a5; padding: 4px 8px; border-radius: 6px; background: #fee2e2; font-weight:600;">🔴 Pending Approval</span>`;
                        }
                    } else {
                        buttonsHtml = `<button type="button" class="btn btn-danger" style="padding: 4px 8px; font-size: 11px; background: #fee2e2; color: #ef4444; border: 1px solid #fca5a5; border-radius: 6px; cursor: pointer;" onclick="deletePpt(${p.id})">Delete</button>`;
                    }

                    html += `<tr style="border-bottom: 1px solid var(--border);">
                        <td style="padding:14px 12px;">
                            <strong><a href="uploads/${p.filename}" target="_blank" style="color:var(--primary); text-decoration:underline; font-weight:600;">${p.original_name}</a></strong>
                            <div style="font-size: 11px; color: var(--text-light); margin-top:2px;">File: ${p.filename}</div>
                        </td>
                        <td style="padding:14px 12px; text-align:right;">${buttonsHtml}</td>
                    </tr>`;
                });
                html += '</tbody></table>';
                container.innerHTML = html;
            }
        });
    }

    // Save presentation uploader
    function savePpt(e) {
        e.preventDefault();
        const fd = new FormData(e.target);
        fetch('?api=upload_ppt', {method: 'POST', body: fd})
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                showNotification(data.message, 'success');
                e.target.reset();
                loadPptsList();
            } else {
                showNotification(data.error || 'Failed to upload ppt.', 'error');
            }
        });
    }

    // Delete Presentation
    function deletePpt(id) {
        if(!confirm('Are you sure you want to delete this presentation?')) return;
        let fd = new FormData();
        fd.append('id', id);
        fetch('?api=delete_ppt', {method: 'POST', body: fd})
        .then(r => r.json())
        .then(d => {
            showNotification(d.message || 'Deletion requested', d.success ? 'success' : 'error');
            loadPptsList();
        });
    }

    // Admin User management scripts
    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin'): ?>
    function loadUsersList() {
        fetch('?api=get_users')
        .then(res => res.json())
        .then(data => {
            const container = document.getElementById('users-list-container');
            if (!container) return;
            
            let html = '<table class="data-table" style="width: 100%; border-collapse: collapse; text-align: left; font-size: 14px;">' +
                       '<thead><tr style="border-bottom: 2px solid var(--border); color: var(--text-muted); font-weight:600;"><th style="padding:12px;">Name / Username</th><th style="padding:12px;">Role</th><th style="padding:12px;">Status</th><th style="padding:12px; text-align:right;">Actions</th></tr></thead><tbody>';
            data.forEach(u => {
                let statusBadge = u.is_active == 1 ? '<span class="status-badge status-won" style="background:#dcfce7; color:#166534; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight:600;">Active</span>' : '<span class="status-badge status-lost" style="background:#fee2e2; color:#991b1b; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight:600;">Deactivated</span>';
                let actionBtn = '';
                let deleteBtn = '';
                if (u.role !== 'Admin') {
                    actionBtn = u.is_active == 1 ? `<button type="button" class="btn btn-secondary" style="font-size: 11px; padding: 4px 8px; cursor:pointer;" onclick="toggleUserStatus(${u.id})">Deactivate</button>` : `<button type="button" class="btn btn-primary" style="font-size: 11px; padding: 4px 8px; cursor:pointer;" onclick="toggleUserStatus(${u.id})">Activate</button>`;
                    deleteBtn = `<button type="button" class="btn btn-danger" style="font-size: 11px; padding: 4px 8px; background: #fee2e2; color: #ef4444; border: 1px solid #fca5a5; border-radius: 6px; cursor: pointer; margin-left:5px;" onclick="deleteUser(${u.id})">Delete</button>`;
                }
                
                html += `<tr style="border-bottom: 1px solid var(--border);">
                    <td style="padding:14px 12px;"><strong>${u.name ? u.name : u.username}</strong> <div style="font-size:12px; color:var(--text-light);">@${u.username}</div></td>
                    <td style="padding:14px 12px;">${u.role}</td>
                    <td style="padding:14px 12px;">${statusBadge}</td>
                    <td style="padding:14px 12px; text-align:right;">${actionBtn} ${deleteBtn}</td>
                </tr>`;
            });
            html += '</tbody></table>';
            container.innerHTML = html;
        });
    }

    function toggleUserStatus(id) {
        if (!confirm("Are you sure you want to change this user's status?")) return;
        let fd = new FormData();
        fd.append('id', id);
        fetch('?api=toggle_user_status', {method: 'POST', body: fd})
        .then(r => r.json()).then(d => {
            if(d.success) { 
                showNotification(d.message, 'success'); 
                loadUsersList(); 
                if (typeof initUserSelects === 'function') initUserSelects();
            } else {
                showNotification(d.error, 'error');
            }
        });
    }

    function deleteUser(id) {
        if (!confirm("Are you sure you want to permanently DELETE this user?")) return;
        let fd = new FormData();
        fd.append('id', id);
        fetch('?api=delete_user', {method: 'POST', body: fd})
        .then(r => r.json()).then(d => {
            if(d.success) { 
                showNotification(d.message, 'success'); 
                loadUsersList(); 
                if (typeof initUserSelects === 'function') initUserSelects();
            } else {
                showNotification(d.error, 'error');
            }
        });
    }

    function createUser(e) {
        e.preventDefault();
        const fd = new FormData(e.target);
        fetch('?api=create_user', {method: 'POST', body: fd})
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                e.target.reset();
                loadUsersList();
                if (typeof initUserSelects === 'function') initUserSelects();
            } else {
                showNotification(data.error, 'error');
            }
        });
    }
    <?php endif; ?>

    document.addEventListener('DOMContentLoaded', () => {
        loadTemplatesList();
        loadPptsList();
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin'): ?>
        loadUsersList();
        <?php endif; ?>
    });
</script>

<?php
require_once __DIR__ . '/footer.php';
?>
