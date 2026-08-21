import os

file_path = r'c:\Users\pc\Downloads\client mgmt2\client_vault\index.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# Replace any garbled Rupee symbols
content = content.replace("â‚¹", "&#8377;")
content = content.replace("₹", "&#8377;")

# Add a modal for the Pitch Product button at the end of the file before </body>/footer
modal_html = """
<!-- Pitch Product Modal -->
<div id="pitchModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15, 23, 42, 0.7); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:#fff; width:100%; max-width:500px; border-radius:12px; padding:24px; box-shadow:0 10px 25px rgba(0,0,0,0.2);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h3 style="margin:0; font-size:18px; color:#0f172a;"><i data-lucide="target" style="width:20px; color:#3b82f6; margin-right:8px; vertical-align:middle;"></i> Pitch New Product</h3>
            <button onclick="document.getElementById('pitchModal').style.display='none'" style="background:none; border:none; cursor:pointer; color:#64748b;"><i data-lucide="x"></i></button>
        </div>
        
        <form method="POST" action="process_pitch.php">
            <input type="hidden" name="applicant_id" id="pitch_applicant_id">
            
            <div style="margin-bottom:16px;">
                <label style="display:block; font-size:13px; font-weight:600; color:#475569; margin-bottom:6px;">Customer Name</label>
                <input type="text" id="pitch_customer_name" readonly style="width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:6px; background:#f8fafc; color:#64748b; font-size:14px; outline:none; box-sizing:border-box;">
            </div>
            
            <div style="margin-bottom:16px;">
                <label style="display:block; font-size:13px; font-weight:600; color:#475569; margin-bottom:6px;">New Product to Pitch</label>
                <select name="product_type" required style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:6px; font-size:14px; outline:none; box-sizing:border-box;">
                    <option value="">Select Product...</option>
                    <option value="Top-up Loan">Top-up Loan</option>
                    <option value="Personal Loan">Personal Loan</option>
                    <option value="Health Insurance">Health Insurance</option>
                    <option value="Life Insurance">Life Insurance</option>
                    <option value="Credit Card">Credit Card</option>
                </select>
            </div>
            
            <div style="margin-bottom:20px;">
                <label style="display:block; font-size:13px; font-weight:600; color:#475569; margin-bottom:6px;">Notes / Pitch Details</label>
                <textarea name="pitch_notes" rows="3" placeholder="E.g., Customer is interested in a 5L Top-up for home renovation..." style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:6px; font-size:14px; outline:none; box-sizing:border-box; resize:vertical;"></textarea>
            </div>
            
            <div style="display:flex; justify-content:flex-end; gap:12px;">
                <button type="button" onclick="document.getElementById('pitchModal').style.display='none'" style="padding:10px 16px; background:#f1f5f9; color:#475569; border:none; border-radius:6px; font-weight:600; cursor:pointer;">Cancel</button>
                <button type="submit" style="padding:10px 16px; background:#3b82f6; color:#fff; border:none; border-radius:6px; font-weight:600; cursor:pointer;">Create New Lead</button>
            </div>
        </form>
    </div>
</div>

<script>
function openPitchModal(id, name) {
    document.getElementById('pitch_applicant_id').value = id;
    document.getElementById('pitch_customer_name').value = name;
    document.getElementById('pitchModal').style.display = 'flex';
}
</script>
"""

# Replace the alert button with the modal trigger
content = content.replace(
    """<button class="btn-pitch" onclick="alert('Pitch feature coming soon! This will open a modal to log a new lead for this existing customer.')">""", 
    """<button class="btn-pitch" onclick="openPitchModal('<?= $c['id'] ?>', '<?= htmlspecialchars(addslashes($c['customer_name'])) ?>')">"""
)

if "pitchModal" not in content:
    content = content.replace("<?php require_once '../footer.php'; ?>", modal_html + "\n<?php require_once '../footer.php'; ?>")

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Updated index.php with Modal and fixed encoding")
