<?php
require_once __DIR__ . '/config.php';

$page_title = "CRM Settings";
$page_subtitle = "Command Center: Profile, Integrations, and Access Management";
require_once __DIR__ . '/header.php';

$loan_products = isset($profile['loan_products']) && $profile['loan_products'] ? json_decode($profile['loan_products'], true) : ['Home Loan', 'Personal Loan', 'Business Loan'];
if(!is_array($loan_products)) $loan_products = ['Home Loan', 'Personal Loan', 'Business Loan'];

$lead_sources = isset($profile['lead_sources']) && $profile['lead_sources'] ? json_decode($profile['lead_sources'], true) : ['Facebook Ads', 'Direct Walk-in', 'Referral'];
if(!is_array($lead_sources)) $lead_sources = ['Facebook Ads', 'Direct Walk-in', 'Referral'];

$global_tds = $profile['global_tds'] ?? 5;
$lead_auto_assign = $profile['lead_auto_assign'] ?? 'Round Robin (Distribute Equally)';
?>

<style>
    .settings-layout { display: flex; gap: 24px; align-items: flex-start; }
    .settings-sidebar { width: 260px; background: white; border-radius: var(--radius-lg); border: 1px solid var(--border); box-shadow: var(--shadow-sm); padding: 12px 0; position: sticky; top: 24px; flex-shrink: 0; }
    .settings-menu-item { display: flex; align-items: center; gap: 12px; padding: 14px 20px; color: var(--text-muted); font-weight: 600; font-size: 14.5px; cursor: pointer; transition: all 0.2s; border-left: 3px solid transparent; }
    .settings-menu-item:hover { background: var(--bg-main); color: var(--primary); }
    .settings-menu-item.active { background: var(--primary-light); color: var(--primary); border-left-color: var(--primary); }
    .settings-menu-item i { width: 18px; height: 18px; }
    
    .settings-content { flex: 1; min-width: 0; }
    .settings-panel { display: none; animation: fadeIn 0.3s ease-in-out; }
    .settings-panel.active { display: block; }
    
    @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
    
    .panel-card { background: var(--bg-card); border-radius: var(--radius-lg); box-shadow: var(--shadow-md); border: 1px solid #f1f5f9; padding: 24px; margin-bottom: 30px; }
    .panel-header { border-bottom: 1px solid #f1f5f9; padding-bottom: 15px; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; }
    .panel-title { font-family: 'Outfit', sans-serif; font-weight: 700; font-size: 19px; color: var(--text-primary); margin: 0; }
    .panel-subtitle { font-family: 'Outfit', sans-serif; font-weight: 600; font-size: 15px; color: var(--primary); margin: 25px 0 15px 0; border-bottom: 1px dashed var(--primary-border); padding-bottom: 8px; }
    
    /* Responsive */
    @media (max-width: 768px) {
        .settings-layout { flex-direction: column; }
        .settings-sidebar { width: 100%; position: static; display: flex; overflow-x: auto; padding: 0; border-radius: 8px; }
        .settings-menu-item { white-space: nowrap; border-left: none; border-bottom: 3px solid transparent; }
        .settings-menu-item.active { border-left-color: transparent; border-bottom-color: var(--primary); }
    }
</style>

<div class="view-container">
    <div class="settings-layout">
        
        <!-- SIDEBAR NAVIGATION -->
        <div class="settings-sidebar">
            <div class="settings-menu-item active" onclick="switchSettingsTab('profile', this)">
                <i data-lucide="building"></i> Company Profile
            </div>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin'): ?>
            <div class="settings-menu-item" onclick="switchSettingsTab('crmconfig', this)">
                <i data-lucide="settings-2"></i> CRM Config & Rules
            </div>
            <?php endif; ?>
            <div class="settings-menu-item" onclick="switchSettingsTab('integrations', this)">
                <i data-lucide="plug"></i> Integrations (API)
            </div>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin'): ?>
            <div class="settings-menu-item" onclick="switchSettingsTab('users', this)">
                <i data-lucide="users"></i> Manage Users
            </div>
            <?php endif; ?>
            <div class="settings-menu-item" onclick="switchSettingsTab('templates', this)">
                <i data-lucide="mail"></i> Email Templates
            </div>
            <div class="settings-menu-item" onclick="switchSettingsTab('ppts', this)">
                <i data-lucide="file-presentation"></i> Presentations
            </div>
        </div>

        <!-- SETTINGS CONTENT -->
        <div class="settings-content">
            
            <form id="settings-form" onsubmit="saveCompanySettings(event)">
                <!-- TAB: COMPANY PROFILE -->
                <div id="tab-profile" class="settings-panel active">
                    <div class="panel-card">
                        <div class="panel-header">
                            <h2 class="panel-title">Company Profile & Branding</h2>
                            <button type="submit" class="btn btn-primary" style="padding: 8px 16px; font-size: 13px; gap: 6px;"><i data-lucide="save" style="width: 14px;"></i> Save Profile</button>
                        </div>
                        
                        <div class="panel-subtitle" style="margin-top:0;">Business Identity</div>
                        <div class="form-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                            <div class="form-group">
                                <label class="required" style="display: block; font-weight: 500; font-size: 13px; margin-bottom: 6px;">Company / Business Name</label>
                                <input type="text" name="company_name" value="<?php echo htmlspecialchars($profile['company_name'] ?? ''); ?>" required style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 14px;">
                            </div>
                            <div class="form-group">
                                <label class="required" style="display: block; font-weight: 500; font-size: 13px; margin-bottom: 6px;">GSTIN Number</label>
                                <input type="text" name="gstin" value="<?php echo htmlspecialchars($profile['gstin'] ?? ''); ?>" placeholder="15-digit GSTIN ID" minlength="15" maxlength="15" required style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 14px;">
                            </div>
                            <div class="form-group">
                                <label class="required" style="display: block; font-weight: 500; font-size: 13px; margin-bottom: 6px;">Support / Billing Email</label>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($profile['email'] ?? ''); ?>" required style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 14px;">
                            </div>
                            <div class="form-group">
                                <label style="display: block; font-weight: 500; font-size: 13px; margin-bottom: 6px;">Contact Phone Number</label>
                                <input type="text" name="mobile" value="<?php echo htmlspecialchars($profile['mobile'] ?? ''); ?>" style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 14px;">
                            </div>
                        </div>

                        <div class="panel-subtitle">Address Details</div>
                        <div class="form-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                            <div class="form-group">
                                <label class="required" style="display: block; font-weight: 500; font-size: 13px; margin-bottom: 6px;">Billing Address Line 1</label>
                                <input type="text" name="address_line1" value="<?php echo htmlspecialchars($profile['address_line1'] ?? ''); ?>" required style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 14px;">
                            </div>
                            <div class="form-group">
                                <label style="display: block; font-weight: 500; font-size: 13px; margin-bottom: 6px;">Billing Address Line 2</label>
                                <input type="text" name="address_line2" value="<?php echo htmlspecialchars($profile['address_line2'] ?? ''); ?>" style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 14px;">
                            </div>
                            <div class="form-group">
                                <label class="required" style="display: block; font-weight: 500; font-size: 13px; margin-bottom: 6px;">City</label>
                                <input type="text" name="city" value="<?php echo htmlspecialchars($profile['city'] ?? ''); ?>" required style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 14px;">
                            </div>
                            <div class="form-group">
                                <label class="required" style="display: block; font-weight: 500; font-size: 13px; margin-bottom: 6px;">State</label>
                                <input type="text" name="state" value="<?php echo htmlspecialchars($profile['state'] ?? ''); ?>" required style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 14px;">
                            </div>
                            <div class="form-group">
                                <label class="required" style="display: block; font-weight: 500; font-size: 13px; margin-bottom: 6px;">Pincode</label>
                                <input type="text" name="pincode" value="<?php echo htmlspecialchars($profile['pincode'] ?? ''); ?>" required style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 14px;">
                            </div>
                            <div class="form-group">
                                <label class="required" style="display: block; font-weight: 500; font-size: 13px; margin-bottom: 6px;">Country</label>
                                <input type="text" name="country" value="<?php echo htmlspecialchars($profile['country'] ?? ''); ?>" required style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 14px;">
                            </div>
                        </div>
                        
                        <div class="panel-subtitle">Bank Details (For Invoicing)</div>
                        <div class="form-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                            <div class="form-group">
                                <label style="display: block; font-weight: 500; font-size: 13px; margin-bottom: 6px;">Bank Name</label>
                                <input type="text" name="bank_name" value="<?php echo htmlspecialchars($profile['bank_name'] ?? ''); ?>" style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 14px;">
                            </div>
                            <div class="form-group">
                                <label style="display: block; font-weight: 500; font-size: 13px; margin-bottom: 6px;">Account Number</label>
                                <input type="text" name="account_number" value="<?php echo htmlspecialchars($profile['account_number'] ?? ''); ?>" style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 14px;">
                            </div>
                            <div class="form-group">
                                <label style="display: block; font-weight: 500; font-size: 13px; margin-bottom: 6px;">IFSC Code</label>
                                <input type="text" name="ifsc_code" value="<?php echo htmlspecialchars($profile['ifsc_code'] ?? ''); ?>" style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 14px;">
                            </div>
                        </div>

                        <div class="panel-subtitle">Primary Contact Person</div>
                        <div class="form-group">
                            <label class="required" style="display: block; font-weight: 500; font-size: 13px; margin-bottom: 6px;">Staff / Admin Full Name</label>
                            <input type="text" name="contact_person" value="<?php echo htmlspecialchars($profile['contact_person'] ?? ''); ?>" required style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 14px;">
                        </div>
                    </div>
                </div>

                <!-- TAB: INTEGRATIONS (SMTP, ETC) -->
                <div id="tab-integrations" class="settings-panel">
                    <div class="panel-card">
                        <div class="panel-header">
                            <h2 class="panel-title">Integrations & API Settings</h2>
                            <button type="submit" class="btn btn-primary" style="padding: 8px 16px; font-size: 13px; gap: 6px;"><i data-lucide="save" style="width: 14px;"></i> Save Setup</button>
                        </div>
                        
                        <div class="panel-subtitle" style="margin-top:0;"><i data-lucide="mail" style="display:inline; width:16px; margin-right:5px;"></i> SMTP Email Configuration</div>
                        <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 15px;">Configure your SMTP server to allow the CRM to send automated emails, notifications, and templates.</p>
                        <div class="form-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                            <div class="form-group">
                                <label style="display: block; font-weight: 500; font-size: 13px; margin-bottom: 6px;">SMTP Host</label>
                                <input type="text" name="smtp_host" value="<?php echo htmlspecialchars($profile['smtp_host'] ?? ''); ?>" placeholder="e.g. smtp.gmail.com" style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 14px;">
                            </div>
                            <div class="form-group">
                                <label style="display: block; font-weight: 500; font-size: 13px; margin-bottom: 6px;">SMTP Port</label>
                                <input type="text" name="smtp_port" value="<?php echo htmlspecialchars($profile['smtp_port'] ?? ''); ?>" placeholder="e.g. 587 or 465" style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 14px;">
                            </div>
                            <div class="form-group">
                                <label style="display: block; font-weight: 500; font-size: 13px; margin-bottom: 6px;">SMTP Username</label>
                                <input type="text" name="smtp_username" value="<?php echo htmlspecialchars($profile['smtp_username'] ?? ''); ?>" placeholder="e.g. you@gmail.com" style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 14px;">
                            </div>
                            <div class="form-group">
                                <label style="display: block; font-weight: 500; font-size: 13px; margin-bottom: 6px;">SMTP Password / App Password</label>
                                <input type="password" name="smtp_password" value="<?php echo htmlspecialchars($profile['smtp_password'] ?? ''); ?>" placeholder="Enter password" style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 14px;">
                            </div>
                            <div class="form-group">
                                <label style="display: block; font-weight: 500; font-size: 13px; margin-bottom: 6px;">Encryption</label>
                                <select name="smtp_encryption" style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 14px; background: white;">
                                    <option value="" <?php echo ($profile['smtp_encryption'] ?? '') === '' ? 'selected' : ''; ?>>None</option>
                                    <option value="tls" <?php echo ($profile['smtp_encryption'] ?? '') === 'tls' ? 'selected' : ''; ?>>TLS</option>
                                    <option value="ssl" <?php echo ($profile['smtp_encryption'] ?? '') === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                                </select>
                            </div>
                        </div>

                        <div class="panel-subtitle" style="margin-top: 30px;"><i data-lucide="message-circle" style="display:inline; width:16px; margin-right:5px;"></i> WhatsApp Cloud API (Coming Soon)</div>
                        <div style="padding: 15px; background: var(--bg-main); border-radius: var(--radius-sm); border: 1px dashed var(--border); color: var(--text-muted); font-size: 13px;">
                            WhatsApp Business API integration will be enabled here in a future update to automate loan status messages.
                        </div>
                    </div>
                </div>
            </form>

            <!-- TAB: CRM ADVANCED CONFIGURATIONS -->
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin'): ?>
            <div id="tab-crmconfig" class="settings-panel">
                <div class="panel-card">
                    <div class="panel-header">
                        <h2 class="panel-title">CRM Config & Rules</h2>
                        <div class="badge-locked" style="background-color: #e0e7ff; color: #4338ca; padding: 6px 12px; border-radius: var(--radius-sm); font-size: 12px; font-weight: 600;">
                            Global Logic Setup
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr; gap: 30px;">
                        
                        <!-- Automations & Rules -->
                        <div style="border: 1px solid var(--border); padding: 20px; border-radius: var(--radius-md); background: #ffffff; box-shadow: 0 2px 5px rgba(0,0,0,0.02);">
                            <h3 style="font-size: 15px; font-weight: 700; color: var(--primary); margin-bottom: 15px; display:flex; align-items:center; gap:8px;"><i data-lucide="zap" style="width:18px;"></i> Automations & Global Rules</h3>
                            
                            <form onsubmit="saveCrmRules(event)" id="rules-form">
                                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                                    <div>
                                        <label style="display: block; font-weight: 600; font-size: 13px; margin-bottom: 6px;">Global TDS Deduction (%)</label>
                                        <input type="number" step="0.1" name="global_tds" value="<?php echo htmlspecialchars($global_tds); ?>" style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 14px;">
                                        <span style="font-size: 12px; color: var(--text-muted); margin-top:4px; display:block;">Automatically applied to all CA/Partner payouts.</span>
                                    </div>
                                    <div>
                                        <label style="display: block; font-weight: 600; font-size: 13px; margin-bottom: 6px;">Lead Auto-Assign Logic</label>
                                        <select name="lead_auto_assign" style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 14px; background: white;">
                                            <option value="Round Robin (Distribute Equally)" <?php echo $lead_auto_assign == 'Round Robin (Distribute Equally)' ? 'selected':''; ?>>Round Robin (Distribute Equally)</option>
                                            <option value="Assign to Admin Only" <?php echo $lead_auto_assign == 'Assign to Admin Only' ? 'selected':''; ?>>Assign to Admin Only</option>
                                            <option value="Assign by City (Advanced)" <?php echo $lead_auto_assign == 'Assign by City (Advanced)' ? 'selected':''; ?>>Assign by City (Advanced)</option>
                                        </select>
                                        <span style="font-size: 12px; color: var(--text-muted); margin-top:4px; display:block;">How new web leads are handled.</span>
                                    </div>
                                    <div>
                                        <button type="submit" class="btn btn-primary" style="margin-top: 25px; padding: 10px 16px; font-size: 14px;"><i data-lucide="save" style="width:16px;"></i> Save Rules</button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Loan Products Manager -->
                        <div style="border: 1px solid var(--border); padding: 20px; border-radius: var(--radius-md); background: #ffffff; box-shadow: 0 2px 5px rgba(0,0,0,0.02);">
                            <h3 style="font-size: 15px; font-weight: 700; color: var(--primary); margin-bottom: 10px; display:flex; align-items:center; gap:8px;"><i data-lucide="briefcase" style="width:18px;"></i> Manage Loan Products</h3>
                            <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 15px;">Add or remove loan categories that agents can select when adding leads.</p>
                            <form onsubmit="addLoanProduct(event)">
                                <div style="display:flex; gap:10px; margin-bottom: 20px;">
                                    <input type="text" id="new-loan-product" placeholder="e.g. Education Loan" required style="flex:1; padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 14px;">
                                    <button type="submit" class="btn btn-secondary" style="padding: 10px 20px; font-weight:600;">Add</button>
                                </div>
                            </form>
                            <div style="display:flex; flex-wrap:wrap; gap:10px;" id="loan-products-list">
                                <?php foreach($loan_products as $idx => $lp): ?>
                                <span style="background: var(--bg-main); border: 1px solid var(--border); padding: 6px 12px; border-radius: 6px; font-size: 13px; font-weight: 600;">
                                    <?php echo htmlspecialchars($lp); ?> 
                                    <i data-lucide="x" style="width:14px; cursor:pointer; color:#ef4444; margin-left:6px; vertical-align:middle;" onclick="removeListSetting('loan', <?php echo $idx; ?>)"></i>
                                </span>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Lead Sources Manager -->
                        <div style="border: 1px solid var(--border); padding: 20px; border-radius: var(--radius-md); background: #ffffff; box-shadow: 0 2px 5px rgba(0,0,0,0.02);">
                            <h3 style="font-size: 15px; font-weight: 700; color: var(--primary); margin-bottom: 10px; display:flex; align-items:center; gap:8px;"><i data-lucide="filter" style="width:18px;"></i> Manage Lead Sources</h3>
                            <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 15px;">Configure where your leads are coming from for better analytics tracking.</p>
                            <form onsubmit="addLeadSource(event)">
                                <div style="display:flex; gap:10px; margin-bottom: 20px;">
                                    <input type="text" id="new-lead-source" placeholder="e.g. JustDial" required style="flex:1; padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 14px;">
                                    <button type="submit" class="btn btn-secondary" style="padding: 10px 20px; font-weight:600;">Add</button>
                                </div>
                            </form>
                            <div style="display:flex; flex-wrap:wrap; gap:10px;" id="lead-sources-list">
                                <?php foreach($lead_sources as $idx => $ls): ?>
                                <span style="background: var(--bg-main); border: 1px solid var(--border); padding: 6px 12px; border-radius: 6px; font-size: 13px; font-weight: 600;">
                                    <?php echo htmlspecialchars($ls); ?> 
                                    <i data-lucide="x" style="width:14px; cursor:pointer; color:#ef4444; margin-left:6px; vertical-align:middle;" onclick="removeListSetting('source', <?php echo $idx; ?>)"></i>
                                </span>
                                <?php endforeach; ?>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- TAB: MANAGE USERS -->
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin'): ?>
            <div id="tab-users" class="settings-panel">
                <div class="panel-card">
                    <div class="panel-header">
                        <h2 class="panel-title">Manage Users & Access Control</h2>
                    </div>
                    
                    <form id="create-user-form" onsubmit="createUser(event)">
                        <div class="panel-subtitle" style="margin-top: 0;">Register New User</div>
                        <div class="form-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; align-items: flex-end;">
                            <div class="form-group">
                                <label class="required" style="display: block; font-weight: 500; font-size: 13px; margin-bottom: 6px;">Full Name</label>
                                <input type="text" name="name" required placeholder="User Full Name" style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 14px;">
                            </div>
                            <div class="form-group">
                                <label class="required" style="display: block; font-weight: 500; font-size: 13px; margin-bottom: 6px;">Role</label>
                                <select name="role" id="new_user_role" onchange="toggleUserFields()" required style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 14px; background: white;">
                                    <option value="Staff">Staff</option>
                                    <option value="Agent">Agent (Individual)</option>
                                    <option value="CA">Chartered Accountant</option>
                                    <option value="Builder">Builder</option>
                                    <option value="Partner">Partner (DSA)</option>
                                </select>
                            </div>
                            <div class="form-group" id="staff_type_group">
                                <label class="required" style="display: block; font-weight: 500; font-size: 13px; margin-bottom: 6px;">Staff Type</label>
                                <select name="staff_type" style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 14px; background: white;">
                                    <option value="In-Office">In-Office</option>
                                    <option value="Field">Field</option>
                                </select>
                            </div>
                            <div class="form-group" id="dashboard_access_group">
                                <label class="required" style="display: block; font-weight: 500; font-size: 13px; margin-bottom: 6px;">Dashboard Access?</label>
                                <select name="has_dashboard" id="has_dashboard" onchange="toggleUserFields()" style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 14px; background: white;">
                                    <option value="1">Yes (Provide Login)</option>
                                    <option value="0">No (Directory Only)</option>
                                </select>
                            </div>
                            <div class="form-group" id="login_id_group">
                                <label class="required" style="display: block; font-weight: 500; font-size: 13px; margin-bottom: 6px;">Login ID</label>
                                <input type="text" name="username" id="username_input" required placeholder="Unique Login ID" style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 14px;">
                            </div>
                            <div class="form-group" id="password_group">
                                <label class="required" style="display: block; font-weight: 500; font-size: 13px; margin-bottom: 6px;">Password</label>
                                <input type="password" name="password" id="password_input" required minlength="6" placeholder="At least 6 chars" style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 14px;">
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 10px 14px; font-weight: 600; border-radius: var(--radius-sm); cursor: pointer;"><i data-lucide="plus" style="width: 16px;"></i> Add User</button>
                            </div>
                        </div>
                    </form>
                    
                    <div class="panel-subtitle" style="margin-top: 30px;">Existing CRM Users</div>
                    <div id="users-list-container">
                        <p style="color: var(--text-light); font-size: 0.9rem;">Loading user roster...</p>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- TAB: EMAIL TEMPLATES -->
            <div id="tab-templates" class="settings-panel">
                <div class="panel-card">
                    <div class="panel-header">
                        <h2 class="panel-title">Email Templates Manager</h2>
                    </div>
                    
                    <form id="create-template-form" onsubmit="saveTemplate(event)" enctype="multipart/form-data">
                        <div class="panel-subtitle" style="margin-top:0;">Create New Template</div>
                        <div class="form-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                            <div class="form-group">
                                <label class="required" style="display: block; font-weight: 500; font-size: 13px; margin-bottom: 6px;">Template Name</label>
                                <input type="text" name="template_name" placeholder="e.g. Standard Pitch" required style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 14px;">
                            </div>
                            <div class="form-group">
                                <label class="required" style="display: block; font-weight: 500; font-size: 13px; margin-bottom: 6px;">Template Type</label>
                                <select name="type" required style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 14px; background: white;">
                                    <option value="Pitch">Pitch</option>
                                    <option value="PPT">PPT</option>
                                    <option value="Custom Mail">Custom Mail</option>
                                </select>
                            </div>
                            <div class="form-group" style="grid-column: 1 / -1;">
                                <label class="required" style="display: block; font-weight: 500; font-size: 13px; margin-bottom: 6px;">Subject Line</label>
                                <input type="text" name="subject" placeholder="Email Subject" required style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 14px;">
                            </div>
                            <div class="form-group" style="grid-column: 1 / -1;">
                                <label class="required" style="display: block; font-weight: 500; font-size: 13px; margin-bottom: 6px;">Email Body</label>
                                <textarea name="body" rows="6" placeholder="Write your template text here..." required style="width: 100%; padding: 12px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 14px; font-family: inherit; line-height: 1.5;"></textarea>
                            </div>
                            <div class="form-group" style="grid-column: 1 / -1;">
                                <label style="display: block; font-weight: 500; font-size: 13px; margin-bottom: 6px;">Default Attachment (Optional PDF / Document / PPT)</label>
                                <input type="file" name="attachment" accept=".ppt,.pptx,.pdf,.doc,.docx,.xls,.xlsx,.zip" style="width: 100%; padding: 8px 0; font-size: 14px;">
                            </div>
                        </div>
                        <div style="margin-top: 20px; display: flex; justify-content: flex-end;">
                            <button type="submit" class="btn btn-primary" style="padding: 10px 20px; font-weight: 600; border-radius: var(--radius-md); cursor: pointer;"><i data-lucide="plus" style="width: 16px;"></i> Save Template</button>
                        </div>
                    </form>
                    
                    <div class="panel-subtitle" style="margin-top: 30px;">Saved Email Templates</div>
                    <div id="templates-list-container">
                        <p style="color: var(--text-light); font-size: 0.9rem;">Loading templates list...</p>
                    </div>
                </div>
            </div>

            <!-- TAB: PPT LIBRARY -->
            <div id="tab-ppts" class="settings-panel">
                <div class="panel-card">
                    <div class="panel-header">
                        <h2 class="panel-title">Presentation (PPT) Library</h2>
                    </div>
                    
                    <form id="ppt-upload-form" onsubmit="savePpt(event)" enctype="multipart/form-data">
                        <div class="panel-subtitle" style="margin-top: 0;">Upload New Presentation Deck</div>
                        <div class="form-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; align-items: flex-end;">
                            <div class="form-group">
                                <label class="required" style="display: block; font-weight: 500; font-size: 13px; margin-bottom: 6px;">Presentation Title</label>
                                <input type="text" name="original_name" required placeholder="e.g. Corporate Pitch Deck 2026" style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 14px;">
                            </div>
                            <div class="form-group">
                                <label class="required" style="display: block; font-weight: 500; font-size: 13px; margin-bottom: 6px;">File Attachment (.ppt, .pptx, .pdf)</label>
                                <input type="file" name="ppt_file" required accept=".ppt,.pptx,.pdf" style="width: 100%; padding: 8px 0; font-size: 14px;">
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 10px 14px; font-weight: 600; border-radius: var(--radius-sm); cursor: pointer;"><i data-lucide="upload" style="width: 16px;"></i> Upload Deck</button>
                            </div>
                        </div>
                    </form>
                    
                    <div class="panel-subtitle" style="margin-top: 30px;">Saved Presentation Files</div>
                    <div id="ppts-list-container">
                        <p style="color: var(--text-light); font-size: 0.9rem;">Loading presentation list...</p>
                    </div>
                </div>
            </div>

        </div> <!-- END SETTINGS CONTENT -->
    </div> <!-- END LAYOUT -->
</div>

<!-- INLINE JS LOGIC -->
<script>
    let loanProductsArr = <?php echo json_encode($loan_products); ?>;
    let leadSourcesArr = <?php echo json_encode($lead_sources); ?>;

    // Tab Switch Logic
    function switchSettingsTab(tabId, el) {
        document.querySelectorAll('.settings-menu-item').forEach(m => m.classList.remove('active'));
        el.classList.add('active');
        document.querySelectorAll('.settings-panel').forEach(p => p.classList.remove('active'));
        document.getElementById('tab-' + tabId).classList.add('active');
        localStorage.setItem('activeSettingsTab', tabId);
    }
    
    document.addEventListener('DOMContentLoaded', () => {
        let lastTab = localStorage.getItem('activeSettingsTab');
        if(lastTab) {
            let tabToActivate = document.querySelector(`.settings-menu-item[onclick*="${lastTab}"]`);
            if(tabToActivate) { switchSettingsTab(lastTab, tabToActivate); }
        }
    });

    // Save CRM Rules (Global TDS, Assignment)
    function saveCrmRules(e) {
        e.preventDefault();
        const fd = new FormData(e.target);
        fd.append('is_partial_config', '1');
        fetch('?api=save_settings', { method: 'POST', body: fd })
        .then(r => r.json()).then(data => {
            if (data.success) showNotification('Global rules applied successfully.', 'success');
            else showNotification(data.error, 'error');
        });
    }

    // Add Loan Product
    function addLoanProduct(e) {
        e.preventDefault();
        const val = document.getElementById('new-loan-product').value.trim();
        if(!val) return;
        loanProductsArr.push(val);
        saveListSetting('loan_products', JSON.stringify(loanProductsArr), () => {
            location.reload(); // Reload to refresh list layout
        });
    }

    // Add Lead Source
    function addLeadSource(e) {
        e.preventDefault();
        const val = document.getElementById('new-lead-source').value.trim();
        if(!val) return;
        leadSourcesArr.push(val);
        saveListSetting('lead_sources', JSON.stringify(leadSourcesArr), () => {
            location.reload();
        });
    }

    // Remove Item from array and save
    function removeListSetting(type, index) {
        if(!confirm('Delete this item?')) return;
        if(type === 'loan') {
            loanProductsArr.splice(index, 1);
            saveListSetting('loan_products', JSON.stringify(loanProductsArr), () => location.reload());
        } else {
            leadSourcesArr.splice(index, 1);
            saveListSetting('lead_sources', JSON.stringify(leadSourcesArr), () => location.reload());
        }
    }

    function saveListSetting(key, valStr, callback) {
        const fd = new FormData();
        fd.append('is_partial_config', '1');
        fd.append(key, valStr);
        fetch('?api=save_settings', { method: 'POST', body: fd })
        .then(r => r.json()).then(data => {
            if(data.success) callback();
            else showNotification(data.error, 'error');
        });
    }


    // Save Company Settings Form Handler
    function saveCompanySettings(e) {
        e.preventDefault();
        const btns = document.querySelectorAll('#settings-form button[type="submit"]');
        btns.forEach(b => b.disabled = true);
        
        const formData = new FormData(document.getElementById('settings-form'));
        fetch('?api=save_settings', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                // Dynamically update UI variables
                companyProfile.company_name = formData.get('company_name');
                companyProfile.contact_person = formData.get('contact_person');
                const headerUser = document.getElementById('header-user-name');
                if (headerUser) headerUser.innerText = companyProfile.contact_person;
            } else {
                showNotification(data.error || 'Failed to save settings.', 'error');
            }
        })
        .finally(() => {
            btns.forEach(b => b.disabled = false);
        });
    }

    // Load templates list in setting screen
    function loadTemplatesList() {
        fetch('?api=get_templates')
        .then(res => res.json())
        .then(data => {
            const container = document.getElementById('templates-list-container');
            if (!container) return;
            if (data.length === 0) {
                container.innerHTML = '<p style="color: var(--text-light); font-size: 0.9rem;">No templates saved yet.</p>';
            } else {
                let html = '<table class="data-table" style="width: 100%; border-collapse: collapse; text-align: left; font-size: 14px;">' +
                           '<thead><tr style="border-bottom: 2px solid var(--border); color: var(--text-muted); font-weight:600;"><th style="padding:12px;">Template Details</th><th style="padding:12px; text-align:right;">Actions</th></tr></thead><tbody>';
                data.forEach(t => {
                    let attachBadge = t.attachment_name ? `<span style="font-size: 11px; background: var(--primary-light); color: var(--primary); padding: 2px 6px; border-radius: 4px; margin-left: 8px; font-weight:500;"> ${t.attachment_name}</span>` : '';
                    let buttonsHtml = `<button type="button" class="btn btn-danger" style="padding: 6px 12px; font-size: 12px; border-radius: 6px; cursor: pointer;" onclick="deleteTemplate(${t.id})">Delete</button>`;
                    
                    html += `<tr style="border-bottom: 1px solid var(--border);">
                        <td style="padding:14px 12px;">
                            <strong style="display:inline-block; color:var(--primary); font-weight:600; margin-bottom:4px;">${t.template_name} (${t.type})</strong>
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
            if (data.success) { showNotification(data.message, 'success'); e.target.reset(); loadTemplatesList(); } 
            else { showNotification(data.error || 'Failed.', 'error'); }
        });
    }

    // Delete Email Template
    function deleteTemplate(id) {
        if (!confirm('Are you sure you want to delete this template?')) return;
        const fd = new FormData(); fd.append('id', id);
        fetch('?api=delete_template', {method: 'POST', body: fd})
        .then(r => r.json()).then(d => { showNotification(d.message, d.success ? 'success' : 'error'); loadTemplatesList(); });
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
                    let buttonsHtml = `<button type="button" class="btn btn-danger" style="padding: 6px 12px; font-size: 12px; border-radius: 6px; cursor: pointer;" onclick="deletePpt(${p.id})">Delete</button>`;
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
        fetch('?api=upload_ppt', {method: 'POST', body: new FormData(e.target)})
        .then(res => res.json()).then(data => {
            if(data.success) { showNotification(data.message, 'success'); e.target.reset(); loadPptsList(); } 
            else { showNotification(data.error || 'Failed.', 'error'); }
        });
    }

    // Delete Presentation
    function deletePpt(id) {
        if(!confirm('Are you sure you want to delete this deck?')) return;
        let fd = new FormData(); fd.append('id', id);
        fetch('?api=delete_ppt', {method: 'POST', body: fd})
        .then(r => r.json()).then(d => { showNotification(d.message, d.success ? 'success' : 'error'); loadPptsList(); });
    }

    // Admin User management scripts
    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin'): ?>
    function toggleUserFields() {
        const role = document.getElementById('new_user_role').value;
        const hasDash = document.getElementById('has_dashboard').value;
        const staffTypeGroup = document.getElementById('staff_type_group');
        const dashAccessGroup = document.getElementById('dashboard_access_group');
        const loginIdGroup = document.getElementById('login_id_group');
        const passwordGroup = document.getElementById('password_group');
        const usernameInput = document.getElementById('username_input');
        const passwordInput = document.getElementById('password_input');
        
        if (role === 'Staff') {
            staffTypeGroup.style.display = 'block';
            dashAccessGroup.style.display = 'block';
        } else {
            staffTypeGroup.style.display = 'none';
            dashAccessGroup.style.display = 'none';
        }
        
        if ((role === 'Staff' && hasDash == '0')) {
            loginIdGroup.style.display = 'none';
            passwordGroup.style.display = 'none';
            usernameInput.removeAttribute('required');
            passwordInput.removeAttribute('required');
        } else {
            loginIdGroup.style.display = 'block';
            passwordGroup.style.display = 'block';
            usernameInput.setAttribute('required', 'required');
            passwordInput.setAttribute('required', 'required');
        }
    }

    function loadUsersList() {
        fetch('?api=get_users')
        .then(res => res.json())
        .then(data => {
            const container = document.getElementById('users-list-container');
            if (!container) return;
            let html = '<table class="data-table" style="width: 100%; border-collapse: collapse; text-align: left; font-size: 14px;">' +
                       '<thead><tr style="border-bottom: 2px solid var(--border); color: var(--text-muted); font-weight:600;"><th style="padding:12px;">User Info</th><th style="padding:12px;">Role & Type</th><th style="padding:12px;">Login Details</th><th style="padding:12px;">Status</th><th style="padding:12px; text-align:right;">Actions</th></tr></thead><tbody>';
            data.forEach(u => {
                let statusBadge = u.is_active == 1 ? '<span class="status-badge" style="background:#dcfce7; color:#166534; padding: 4px 8px; border-radius: 6px; font-size: 12px; font-weight:600;">Active</span>' : '<span class="status-badge" style="background:#fee2e2; color:#991b1b; padding: 4px 8px; border-radius: 6px; font-size: 12px; font-weight:600;">Deactivated</span>';
                let actionBtn = ''; let deleteBtn = '';
                if (u.role !== 'Admin') {
                    actionBtn = u.is_active == 1 ? `<button type="button" class="btn btn-secondary" style="font-size: 12px; padding: 6px 10px; cursor:pointer;" onclick="toggleUserStatus(${u.id})">Deactivate</button>` : `<button type="button" class="btn btn-primary" style="font-size: 12px; padding: 6px 10px; cursor:pointer;" onclick="toggleUserStatus(${u.id})">Activate</button>`;
                    deleteBtn = `<button type="button" class="btn btn-danger" style="font-size: 12px; padding: 6px 10px; margin-left:5px; cursor: pointer;" onclick="deleteUser(${u.id})">Delete</button>`;
                }
                
                let roleTypeDisplay = u.role;
                if (u.role === 'Staff' && u.staff_type) {
                    roleTypeDisplay += ` <small style="color:var(--text-light)">(${u.staff_type})</small>`;
                }
                
                let loginDetails = `<div style="font-size:12px; color:var(--text-light);">No Dashboard Access</div>`;
                if (u.has_dashboard == 1 || u.has_dashboard == null) {
                    loginDetails = `<strong>ID:</strong> ${u.username}<br><strong>Pwd:</strong> ${u.plain_password ? u.plain_password : '<i>Hidden</i>'}`;
                }
                
                html += `<tr style="border-bottom: 1px solid var(--border);">
                    <td style="padding:14px 12px;"><strong>${u.name ? u.name : u.username}</strong></td>
                    <td style="padding:14px 12px;"><span style="background:var(--bg-main); padding: 4px 8px; border-radius: 4px; font-size: 13px; font-weight:600; border: 1px solid var(--border);">${roleTypeDisplay}</span></td>
                    <td style="padding:14px 12px; font-size:13px;">${loginDetails}</td>
                    <td style="padding:14px 12px;">${statusBadge}</td>
                    <td style="padding:14px 12px; text-align:right;">${actionBtn} ${deleteBtn}</td>
                </tr>`;
            });
            html += '</tbody></table>';
            container.innerHTML = html;
        });
    }

    function toggleUserStatus(id) {
        if (!confirm("Change this user's status?")) return;
        let fd = new FormData(); fd.append('id', id);
        fetch('?api=toggle_user_status', {method: 'POST', body: fd})
        .then(r => r.json()).then(d => { showNotification(d.message, d.success ? 'success' : 'error'); loadUsersList(); });
    }

    function deleteUser(id) {
        if (!confirm("Permanently DELETE this user?")) return;
        let fd = new FormData(); fd.append('id', id);
        fetch('?api=delete_user', {method: 'POST', body: fd})
        .then(r => r.json()).then(d => { showNotification(d.message, d.success ? 'success' : 'error'); loadUsersList(); });
    }

    function createUser(e) {
        e.preventDefault();
        fetch('?api=create_user', {method: 'POST', body: new FormData(e.target)})
        .then(res => res.json()).then(data => {
            if (data.success) { showNotification(data.message, 'success'); e.target.reset(); loadUsersList(); } 
            else { showNotification(data.error, 'error'); }
        });
    }
    <?php endif; ?>

    document.addEventListener('DOMContentLoaded', () => {
        loadTemplatesList();
        loadPptsList();
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin'): ?>
        loadUsersList();
        toggleUserFields();
        <?php endif; ?>
    });
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
