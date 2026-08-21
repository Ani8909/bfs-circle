<?php
require_once 'includes/header.php';

// Get Referral ID
$stmt_ref = $db->prepare("SELECT referral_id FROM referrals WHERE referrer_type = 'Builder' AND full_name = ? LIMIT 1");
$stmt_ref->execute([$builder_name]);
$referral_id = $stmt_ref->fetchColumn() ?: 'BLD-' . $builder_id;

$apply_link = "http://" . $_SERVER['HTTP_HOST'] . "/apply.php?ref=" . $referral_id;
$wa_text = urlencode("Hi! You can apply for your Home Loan through my official link here: " . $apply_link);
?>

<div style="margin-bottom: 24px;">
    <h2 style="font-family: 'Outfit'; font-size: 22px; color: var(--text-primary); display: flex; align-items: center; gap: 8px;">
        Marketing Tools
    </h2>
    <p style="color: var(--text-muted); font-size: 13px; margin-top: 4px;">Share your link & generate leads instantly</p>
</div>

<!-- Digital Link Card -->
<div class="card" style="margin-bottom: 24px; padding: 24px;">
    <h3 style="font-family: 'Outfit'; font-size: 16px; margin-bottom: 12px; display:flex; align-items:center; gap:8px;">
        <i data-lucide="link" style="color:var(--primary); width:18px;"></i> Your Application Link
    </h3>
    <div style="background: #f1f5f9; padding: 12px; border-radius: 12px; font-size: 13px; color: var(--text-muted); word-break: break-all; margin-bottom: 16px; border: 1px solid var(--border);">
        <?php echo $apply_link; ?>
    </div>
    
    <div style="display: flex; gap: 12px;">
        <button onclick="copyToClipboard('<?php echo $apply_link; ?>')" style="flex: 1; background: white; border: 1px solid var(--border); color: var(--text-primary); padding: 12px; border-radius: 12px; font-size: 13px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; box-shadow: 0 2px 5px rgba(0,0,0,0.02);">
            <i data-lucide="copy" style="width:16px;"></i> Copy Link
        </button>
        <a href="https://wa.me/?text=<?php echo $wa_text; ?>" target="_blank" style="flex: 1; background: #25D366; color: white; border: none; padding: 12px; border-radius: 12px; font-size: 13px; font-weight: 600; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 6px; box-shadow: 0 4px 10px rgba(37,211,102,0.3);">
            <i data-lucide="share-2" style="width:16px;"></i> WhatsApp
        </a>
    </div>
</div>

<!-- QR Code Flyer -->
<div class="card" style="margin-bottom: 24px; padding: 24px; text-align: center;">
    <h3 style="font-family: 'Outfit'; font-size: 16px; margin-bottom: 8px;">Sales Office QR Flyer</h3>
    <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 20px;">Print and display this in your site office so clients can scan & apply for loans instantly.</p>
    
    <div style="width: 200px; height: 280px; background: linear-gradient(180deg, var(--primary) 0%, var(--primary-hover) 100%); border-radius: 16px; margin: 0 auto 20px; position: relative; overflow: hidden; box-shadow: 0 10px 30px rgba(15, 23, 42, 0.2);">
        <!-- Flyer Mockup -->
        <div style="padding: 20px; color: white; text-align: center;">
            <div style="font-family:'Outfit'; font-weight:800; font-size:16px; margin-bottom:4px; color:var(--accent);">HOME LOANS</div>
            <div style="font-size:10px; opacity:0.8; margin-bottom:20px;">Scan to Apply Instantly</div>
            <div style="width: 120px; height: 120px; background: white; border-radius: 12px; margin: 0 auto; padding: 8px;">
                <!-- Using a public API for demo QR generation -->
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?php echo urlencode($apply_link); ?>" style="width:100%; height:100%;">
            </div>
            <div style="font-size:9px; opacity:0.6; margin-top:20px; text-transform:uppercase;">Powered by BFS Financial Services</div>
        </div>
    </div>
    
    <button onclick="alert('Downloading high-res PDF flyer...')" style="width: 100%; background: var(--primary); color: white; border: none; padding: 14px; border-radius: 12px; font-weight: 700; font-size: 14px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;">
        <i data-lucide="download" style="width:18px;"></i> Download PDF Flyer
    </button>
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert('Link copied to clipboard!');
    }, function(err) {
        alert('Could not copy text: ', err);
    });
}
</script>

<?php require_once 'includes/footer.php'; ?>
