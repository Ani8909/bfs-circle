import os

# 1. Update footer.php reminder-modal to use the advanced fields
footer_file = r'c:\Users\pc\Downloads\client mgmt2\footer.php'
with open(footer_file, 'r', encoding='utf-8') as f:
    footer_content = f.read()

# Replace the simple reminder-modal with the advanced one
old_modal = """    <!-- Reminder Modal -->
    <div id="reminder-modal" class="modal">
        <div class="modal-content" style="max-width:400px;">
            <div class="modal-header">
                <h2> Set Reminder</h2>
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
    </div>"""

new_modal = """    <!-- Advanced Global Reminder Modal -->
    <div id="reminder-modal" class="modal" style="z-index: 100000;">
        <div class="modal-content" style="max-width:550px;">
            <div class="modal-header">
                <h2 style="display:flex; align-items:center; gap:8px;"><i data-lucide="bell-ring" style="width:20px;height:20px;color:var(--primary);"></i> Set Reminder</h2>
                <span class="close" onclick="closeReminderModal()">&times;</span>
            </div>
            <div class="modal-body">
                <form id="reminder-form" onsubmit="handleGlobalReminderSubmit(event)">
                    <input type="hidden" id="rem_lead_type" name="reference_type">
                    <input type="hidden" id="rem_lead_id" name="reference_id">
                    <input type="hidden" id="rem_ref_label" name="reference_label">
                    
                    <div class="form-group" style="margin-bottom: 12px;">
                        <label style="font-size:13px; font-weight:600; color:var(--text-primary);">Title / Subject *</label>
                        <input type="text" id="rem_title" name="title" required placeholder="e.g. Call back regarding pricing" class="field-input">
                    </div>
                    
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; margin-bottom:12px;">
                        <div class="form-group">
                            <label style="font-size:13px; font-weight:600; color:var(--text-primary);">Date & Time *</label>
                            <input type="datetime-local" id="rem_date" name="remind_at" required class="field-input">
                        </div>
                        <div class="form-group">
                            <label style="font-size:13px; font-weight:600; color:var(--text-primary);">Priority</label>
                            <select id="rem_priority" name="priority" class="field-input">
                                <option value="High">High (Urgent)</option>
                                <option selected value="Medium">Medium (Standard)</option>
                                <option value="Low">Low</option>
                            </select>
                        </div>
                    </div>
                    
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; margin-bottom:12px;">
                        <div class="form-group">
                            <label style="font-size:13px; font-weight:600; color:var(--text-primary);">Category</label>
                            <select id="rem_category" name="reminder_category" class="field-input">
                                <option value="Follow-up">Follow-up</option>
                                <option value="Call Back">Call Back</option>
                                <option value="Bank Visit">Bank Visit</option>
                                <option value="Document Chase">Document Chase</option>
                                <option value="Payout Follow-up">Payout Follow-up</option>
                                <option value="Referral Meeting">Referral Meeting</option>
                                <option value="Field Visit">Field Visit</option>
                            </select>
                        </div>
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin'): ?>
                        <div class="form-group">
                            <label style="font-size:13px; font-weight:600; color:var(--text-primary);">Assign To</label>
                            <select id="rem_assigned" name="assigned_to" class="field-input">
                                <option value="<?= htmlspecialchars($_SESSION['username'] ?? '') ?>"><?= htmlspecialchars($_SESSION['username'] ?? '') ?> (Me)</option>
                                <?php 
                                    if(isset($db)) {
                                        $all_staff = $db->query("SELECT username FROM users WHERE is_active=1 ORDER BY username")->fetchAll(PDO::FETCH_COLUMN);
                                        foreach ($all_staff as $st) {
                                            if ($st !== ($_SESSION['username'] ?? '')) echo "<option value=\\"".htmlspecialchars($st)."\\">".htmlspecialchars($st)."</option>";
                                        }
                                    }
                                ?>
                            </select>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group" style="margin-bottom: 20px;">
                        <label style="font-size:13px; font-weight:600; color:var(--text-primary);">Notes (Optional)</label>
                        <textarea id="rem_notes" name="notes" rows="3" placeholder="Add specific task remarks..." class="field-input"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width:100%; padding:12px; font-weight:600; font-size:14px; display:flex; justify-content:center; align-items:center; gap:8px;"><i data-lucide="check-circle"></i> Save Professional Reminder</button>
                </form>
            </div>
        </div>
    </div>
    <style>
    .field-input { width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: 8px; font-family: 'Outfit', sans-serif; font-size: 14px; background: #fff; transition: all 0.2s; box-sizing: border-box; }
    .field-input:focus { border-color: var(--primary); outline: none; box-shadow: 0 0 0 3px rgba(15, 23, 42, 0.05); }
    </style>"""

footer_content = footer_content.replace(old_modal, new_modal)

# Also update the openReminderModal function to accept name
old_func = """        function openReminderModal(type, id) {
            document.getElementById('rem_lead_type').value = type;
            document.getElementById('rem_lead_id').value = id;
            document.getElementById('reminder-modal').style.display = 'flex';
        }"""
new_func = """        function openReminderModal(type, id, label = '') {
            if(document.getElementById('rem_lead_type')) document.getElementById('rem_lead_type').value = type;
            if(document.getElementById('rem_lead_id')) document.getElementById('rem_lead_id').value = id;
            if(document.getElementById('rem_ref_label')) document.getElementById('rem_ref_label').value = label;
            if(document.getElementById('rem_title')) document.getElementById('rem_title').value = "Follow-up: " + label;
            if(document.getElementById('reminder-modal')) document.getElementById('reminder-modal').style.display = 'flex';
            if(typeof lucide !== 'undefined') lucide.createIcons();
        }"""
footer_content = footer_content.replace(old_func, new_func)

with open(footer_file, 'w', encoding='utf-8') as f:
    f.write(footer_content)

# 2. Add button in search_track.php
track_file = r'c:\Users\pc\Downloads\client mgmt2\search_track.php'
with open(track_file, 'r', encoding='utf-8') as f:
    track_content = f.read()

btn_target = '<div class="detail-company-title" style="font-size: 24px; color:var(--text-primary); margin-bottom:4px;">${app.customer_name}</div>'
btn_replacement = '''<div style="display:flex; align-items:center; gap:12px; margin-bottom:4px;">
                            <div class="detail-company-title" style="font-size: 24px; color:var(--text-primary); margin:0;">${app.customer_name}</div>
                            <button onclick="openReminderModal('Lead', ${app.id}, '${app.customer_name}')" class="btn btn-secondary" style="padding:6px; height:32px; width:32px; border-radius:8px; display:inline-flex; align-items:center; justify-content:center; color:#f59e0b;" title="Set Lead Reminder"><i data-lucide="bell" style="width:16px; height:16px;"></i></button>
                        </div>'''
if btn_target in track_content:
    track_content = track_content.replace(btn_target, btn_replacement)
    with open(track_file, 'w', encoding='utf-8') as f:
        f.write(track_content)

# 3. Add button in bankers_list.php
bank_file = r'c:\Users\pc\Downloads\client mgmt2\bankers_list.php'
with open(bank_file, 'r', encoding='utf-8') as f:
    bank_content = f.read()

bank_target = """                                        <a href="view_banker.php?id=<?php echo $banker['id']; ?>" class="btn btn-sm btn-secondary" title="View Profile" style="padding: 6px 8px;" onclick="event.stopPropagation();">
                                            <i data-lucide="eye"></i>
                                        </a>"""
bank_replacement = """                                        <button class="btn btn-sm btn-secondary" title="Set Reminder" style="padding: 6px 8px; color:#f59e0b;" onclick="event.stopPropagation(); openReminderModal('Banker', <?php echo $banker['id']; ?>, '<?php echo addslashes(htmlspecialchars($banker['full_name'])); ?>')">
                                            <i data-lucide="bell"></i>
                                        </button>
                                        <a href="view_banker.php?id=<?php echo $banker['id']; ?>" class="btn btn-sm btn-secondary" title="View Profile" style="padding: 6px 8px;" onclick="event.stopPropagation();">
                                            <i data-lucide="eye"></i>
                                        </a>"""
if bank_target in bank_content:
    bank_content = bank_content.replace(bank_target, bank_replacement)
    with open(bank_file, 'w', encoding='utf-8') as f:
        f.write(bank_content)
        
# 4. Add button in referrals_list.php
ref_file = r'c:\Users\pc\Downloads\client mgmt2\referrals_list.php'
with open(ref_file, 'r', encoding='utf-8') as f:
    ref_content = f.read()

ref_target = """                                        <a href="view_referral.php?id=<?php echo $ref['id']; ?>" class="btn btn-sm btn-secondary" title="View Profile" style="padding: 6px 8px;">
                                            <i data-lucide="eye"></i>
                                        </a>"""
ref_replacement = """                                        <button class="btn btn-sm btn-secondary" title="Set Reminder" style="padding: 6px 8px; color:#f59e0b;" onclick="event.stopPropagation(); openReminderModal('Referral', <?php echo $ref['id']; ?>, '<?php echo addslashes(htmlspecialchars($ref['full_name'])); ?>')">
                                            <i data-lucide="bell"></i>
                                        </button>
                                        <a href="view_referral.php?id=<?php echo $ref['id']; ?>" class="btn btn-sm btn-secondary" title="View Profile" style="padding: 6px 8px;">
                                            <i data-lucide="eye"></i>
                                        </a>"""
if ref_target in ref_content:
    ref_content = ref_content.replace(ref_target, ref_replacement)
    with open(ref_file, 'w', encoding='utf-8') as f:
        f.write(ref_content)

print("Patch applied!")
