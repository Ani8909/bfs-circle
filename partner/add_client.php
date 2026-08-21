<?php
require_once 'includes/header.php';
?>

<div style="margin-bottom: 24px;">
    <h2 style="font-family: 'Outfit'; font-size: 24px; color: var(--text-primary);">Add Client</h2>
    <p style="color: var(--text-muted); font-size: 14px;">Onboard a new client for financial advisory</p>
</div>

<form id="addClientForm" class="card" enctype="multipart/form-data">
    <input type="hidden" name="action" value="partner_add_lead">
    
    <div class="form-group">
        <label>Client Full Name</label>
        <input type="text" name="name" required placeholder="e.g. John Doe">
    </div>
    
    <div class="form-group">
        <label>Mobile Number</label>
        <input type="tel" name="phone" required placeholder="10-digit mobile number" pattern="[0-9]{10}">
    </div>
    
    <div class="form-group">
        <label>Email Address (Optional)</label>
        <input type="text" name="email" placeholder="client@example.com">
    </div>
    
    <div class="form-group">
        <label>Loan Type</label>
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
        <label>Loan Sub Type</label>
        <select name="loan_sub_type" id="loan_sub_type" required>
            <option value="">-- Select Sub Type --</option>
        </select>
    </div>
    
    <div class="form-group">
        <label>Required Amount (₹)</label>
        <input type="number" name="loan_amount" required placeholder="e.g. 5000000">
    </div>
    
    
<div class="form-group" style="text-align:center; margin-bottom: 20px;">
    <label style="display:block; margin-bottom:10px; font-weight:600; font-size:13px; color:var(--text-main);">Take a Photo <span style="color:red">*</span></label>
    <input type="file" name="photo" id="photo" accept="image/*" capture="user" required style="display:none;" onchange="previewPhoto(event)">
    <button type="button" onclick="document.getElementById('photo').click()" style="background:#f1f5f9; border:2px dashed #cbd5e1; padding:20px; border-radius:12px; width:100%; cursor:pointer; color:#64748b; font-weight:600;">
        <i data-lucide="camera" style="width:32px; height:32px; margin-bottom:8px; display:block; margin:0 auto 8px;"></i>
        Click to Capture Photo
    </button>
    <img id="photo-preview" style="display:none; width:100px; height:100px; object-fit:cover; border-radius:12px; margin: 15px auto 0; border:2px solid var(--primary);">
</div>
<button type="submit" class="btn" id="submitBtn">
        Submit Client Profile
    </button>
</form>
<script>
function previewPhoto(event) {
    const file = event.target.files[0];
    if(file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = document.getElementById('photo-preview');
            img.src = e.target.result;
            img.style.display = 'block';
        }
        reader.readAsDataURL(file);
    }
}
</script>


<script>
const loanData = {
    "Home Loan": [
        "New Home Purchase", 
        "Balance Transfer & Top-up", 
        "Plot Purchase & Construction", 
        "Home Improvement / Renovation"
    ],
    "Loan Against Property (LAP)": [
        "Residential LAP", 
        "Commercial LAP", 
        "Industrial LAP", 
        "Lease Rental Discounting (LRD)"
    ],
    "Business Loan": [
        "Unsecured Business Loan", 
        "Machinery / Equipment Loan", 
        "Working Capital (CC/OD)", 
        "CGTMSE / Mudra Loan"
    ],
    "Personal Loan": [
        "Personal Loan for Salaried", 
        "Personal Loan for Self Employed", 
        "Medical Emergency Loan", 
        "Wedding Loan"
    ],
    "Auto / Vehicle Loan": [
        "New Car Loan", 
        "Used Car Loan", 
        "Commercial Vehicle Loan"
    ],
    "Commercial Property Loan": [
        "Office Space Purchase", 
        "Shop / Showroom Purchase", 
        "Warehouse / Godown Finance"
    ],
    "Professional Loan": [
        "Doctor Loan",
        "Chartered Accountant (CA) Loan",
        "Company Secretary (CS) Loan"
    ]
};

function updateSubTypes() {
    const typeSelect = document.getElementById('loan_type');
    const subTypeSelect = document.getElementById('loan_sub_type');
    
    subTypeSelect.innerHTML = '<option value="">-- Select Sub Type --</option>';
    
    const selectedType = typeSelect.value;
    if (selectedType && loanData[selectedType]) {
        loanData[selectedType].forEach(subType => {
            const option = document.createElement('option');
            option.value = subType;
            option.textContent = subType;
            subTypeSelect.appendChild(option);
        });
    }
}

document.getElementById('addClientForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = 'Submitting...';
    
    const fd = new FormData(this);
    
    fetch('partner_api.php', { method: 'POST', body: fd })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            Swal.fire({
                title: 'Success!',
                text: 'Client profile added to your portfolio.',
                icon: 'success',
                confirmButtonColor: '#0f172a'
            }).then(() => {
                window.location = 'clients.php';
            });
        } else {
            Swal.fire('Error', data.error || 'Failed to add client', 'error');
            btn.disabled = false;
            btn.innerHTML = 'Submit Client Profile';
        }
    })
    .catch(err => {
        Swal.fire('Error', 'Connection failed', 'error');
        btn.disabled = false;
        btn.innerHTML = 'Submit Client Profile';
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
