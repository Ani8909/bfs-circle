<?php
require_once 'includes/header.php';

// Get Agent Details
$stmt = $db->prepare("SELECT * FROM referrals WHERE user_id = ?");
$stmt->execute([$agent_id]);
$agent = $stmt->fetch();
$referral_id = $agent['referral_id'] ?? '';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_name = trim($_POST['customer_name'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $loan_type = trim($_POST['loan_type'] ?? '');
    $loan_sub_type = trim($_POST['loan_sub_type'] ?? '');
    $requirement = $loan_type . ($loan_sub_type ? ' - ' . $loan_sub_type : '');
    $loan_amount = floatval($_POST['loan_amount'] ?? 0);
    $pan_number = trim($_POST['pan_number'] ?? '');
    $aadhar_number = trim($_POST['aadhar_number'] ?? '');
    
    if (empty($customer_name) || empty($mobile) || $loan_amount <= 0) {
        $error = "Please fill all required fields correctly.";
    } else {
        // Generate unique loan_id
        $loan_id = 'L' . date('Ymd') . rand(1000, 9999);
        
        $notes = ($pan_number ? "PAN: " . $pan_number : "") . ($aadhar_number ? " | Aadhar: " . $aadhar_number : "");
    
$photo_path = null;
if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = __DIR__ . '/../uploads/leads/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
    
    $tmp = $_FILES['photo']['tmp_name'];
    $filename = uniqid('lead_') . '.jpg';
    $dest = $upload_dir . $filename;
    
        $info = getimagesize($tmp);
    if ($info && function_exists('imagecreatefromjpeg')) {
        if ($info['mime'] == 'image/jpeg') $image = imagecreatefromjpeg($tmp);
        elseif ($info['mime'] == 'image/png') $image = imagecreatefrompng($tmp);
        elseif ($info['mime'] == 'image/gif') $image = imagecreatefromgif($tmp);
        else $image = function_exists('imagecreatefromstring') ? imagecreatefromstring(file_get_contents($tmp)) : false;
        
        if ($image) {
            $width = imagesx($image);
            $height = imagesy($image);
            $new_width = 400;
            $new_height = floor($height * ($new_width / $width));
            $tmp_img = imagecreatetruecolor($new_width, $new_height);
            imagecopyresampled($tmp_img, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
            imagejpeg($tmp_img, $dest, 50);
            imagedestroy($image);
            imagedestroy($tmp_img);
            $photo_path = 'uploads/leads/' . $filename;
        } else {
            if (move_uploaded_file($tmp, $dest)) $photo_path = 'uploads/leads/' . $filename;
        }
    } else {
        if (move_uploaded_file($tmp, $dest)) $photo_path = 'uploads/leads/' . $filename;
    }
}

$stmt_insert = $db->prepare("INSERT INTO leads (lead_name, mobile, email, requirement, loan_amount, notes, lead_source, stage, priority, added_by, photo) VALUES (?, ?, ?, ?, ?, ?, 'Agent Referral', 'New Lead', 'Warm', ?, ?)");
try {
$stmt_insert->execute([$customer_name, $mobile, $email, $requirement, $loan_amount, $notes, $agent_username, $photo_path]);
            $message = "Lead added successfully!";
        } catch(PDOException $e) {
            $error = "Error adding lead: " . $e->getMessage();
        }
    }
}
?>

<div style="margin-bottom: 20px;">
    <h2 style="font-family: 'Outfit'; font-size: 22px; color: var(--text-primary);">Add New Lead</h2>
    <p style="color: var(--text-muted); font-size: 14px;">Enter customer details to submit a new loan lead.</p>
</div>

<?php if($message): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        showNotification('<?php echo htmlspecialchars($message); ?>', 'success');
        setTimeout(() => { window.location.href = 'leads.php'; }, 1500);
    });
</script>
<?php endif; ?>

<?php if($error): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        showNotification('<?php echo htmlspecialchars($error); ?>', 'error');
    });
</script>
<?php endif; ?>

<div class="card">
    <form method="POST" action="" enctype="multipart/form-data">
        <div class="form-group">
            <label for="customer_name">Customer Name <span style="color:red">*</span></label>
            <input type="text" id="customer_name" name="customer_name" required placeholder="Full Name">
        </div>
        
        <div class="form-group">
            <label for="mobile">Mobile Number <span style="color:red">*</span></label>
            <input type="tel" id="mobile" name="mobile" required placeholder="10-digit mobile number" pattern="[0-9]{10}">
        </div>
        
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" placeholder="your@email.com">
        </div>
        
        <div class="form-group">
            <label for="loan_type">Loan Type <span style="color:red">*</span></label>
            <select id="loan_type" name="loan_type" required onchange="updateSubTypes()">
                <option value="">-- Select Category --</option>
                <option value="Secured Loans">Secured Loans</option>
                <option value="Unsecured Loans">Unsecured Loans</option>
                <option value="Working Capital">Working Capital</option>
                <option value="Project Finance">Project Finance</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="loan_sub_type">Loan Sub Type <span style="color:red">*</span></label>
            <select id="loan_sub_type" name="loan_sub_type" required>
                <option value="">-- Select Sub Type --</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="loan_amount">Required Loan Amount <span style="color:red">*</span></label>
            <input type="number" id="loan_amount" name="loan_amount" required placeholder="e.g. 500000" min="1000">
        </div>
        
        <div class="form-group">
            <label for="pan_number">PAN Number (Optional)</label>
            <input type="text" id="pan_number" name="pan_number" placeholder="ABCDE1234F">
        </div>
        
        <div class="form-group">
            <label for="aadhar_number">Aadhar Number (Optional)</label>
            <input type="text" id="aadhar_number" name="aadhar_number" placeholder="12-digit Aadhar">
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
<button type="submit" class="btn" style="margin-top: 10px;">Submit Lead</button>
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

</div>

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
</script>

<?php require_once 'includes/footer.php'; ?>
