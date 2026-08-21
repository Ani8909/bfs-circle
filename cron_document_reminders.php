<?php
// cron_document_reminders.php
// Run this script via cron daily (e.g., 0 9 * * *) to send automated missing document reminders

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/libs/PHPMailer/Exception.php';
require_once __DIR__ . '/libs/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/libs/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// 1. Setup logging table
$db->exec("CREATE TABLE IF NOT EXISTS doc_reminders_log (applicant_id INTEGER, sent_date DATE)");

$today = date('Y-m-d');

// 2. Fetch all active applicants (not completed, not rejected) who have an email
$stmt = $db->query("SELECT * FROM applicants WHERE overall_status IN ('Phase 1', 'Phase 2') AND email IS NOT NULL AND email != ''");
$applicants = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sent_count = 0;

// Fetch company SMTP profile
$company = $db->query("SELECT * FROM company_profile LIMIT 1")->fetch(PDO::FETCH_ASSOC);

foreach ($applicants as $app) {
    $id = $app['id'];
    
    // Check if already sent today
    $logCheck = $db->prepare("SELECT 1 FROM doc_reminders_log WHERE applicant_id = ? AND sent_date = ?");
    $logCheck->execute([$id, $today]);
    if ($logCheck->fetch()) {
        continue; // Already sent today
    }
    
    // Determine missing documents (Phase 2 logic)
    $loan_type = $app['loan_type'] ?? '';
    $mandatory_categories = ['Basic KYC'];
    if (stripos($loan_type, 'Home') !== false) {
        $mandatory_categories = ['Basic KYC', 'Income Proof', 'Property / Asset Docs'];
    } elseif (stripos($loan_type, 'Business') !== false) {
        $mandatory_categories = ['Basic KYC', 'Income Proof', 'Business Proof'];
    } elseif (stripos($loan_type, 'Vehicle') !== false) {
        $mandatory_categories = ['Basic KYC', 'Income Proof', 'Vehicle Docs'];
    } else {
        $mandatory_categories = ['Basic KYC', 'Income Proof'];
    }
    
    // Get uploaded docs
    $docStmt = $db->prepare("SELECT document_category FROM applicant_documents WHERE applicant_id = ?");
    $docStmt->execute([$id]);
    $docs = $docStmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Find missing
    $missing_cats = [];
    foreach ($mandatory_categories as $cat) {
        if (!in_array($cat, $docs)) {
            $missing_cats[] = $cat;
        }
    }
    
    // If there are missing documents, BUT they have at least 1 document OR we just want to remind them anyway?
    // User said: "JESE HUMARE PASS KISI APPLICANT KE KUCH DOCUMENT AAGYE HE BUT USKE DOCCUMENT BAKI HE TO PER DAY USKO AUTO EMAIL JAYE"
    // "if some docs have arrived but some are remaining" -> count($docs) > 0 && count($missing_cats) > 0
    
    if (count($docs) > 0 && count($missing_cats) > 0) {
        // Send Email
        $mail = new PHPMailer(true);
        try {
            if (!empty($company['smtp_host'])) {
                $mail->isSMTP();
                $mail->Host       = $company['smtp_host'];
                $mail->SMTPAuth   = true;
                $mail->Username   = $company['smtp_user'];
                $mail->Password   = $company['smtp_pass'];
                if ($company['smtp_encryption'] === 'ssl') {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                } else {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                }
                $mail->Port       = $company['smtp_port'];
                $mail->setFrom($company['smtp_user'], $company['company_name'] ?? 'CRM Team');
            } else {
                // Fallback to PHP mail()
                $mail->isMail();
                $mail->setFrom('noreply@crm.local', 'CRM Team');
            }
            
            $mail->addAddress($app['email'], $app['customer_name']);
            $mail->isHTML(true);
            $mail->Subject = "Action Required: Pending Documents for your Loan Application (ID: {$app['loan_id']})";
            
            $missing_html = "<ul>";
            foreach ($missing_cats as $mc) {
                $missing_html .= "<li><strong>{$mc}</strong></li>";
            }
            $missing_html .= "</ul>";
            
            $company_name = $company['company_name'] ?? 'Our Company';
            
            $mail->Body = "
            <div style='font-family: Arial, sans-serif; color:#333; max-width:600px; margin:0 auto; border:1px solid #ddd; border-radius:8px; overflow:hidden;'>
                <div style='background:#0f172a; padding:15px; color:#fff; text-align:center;'>
                    <h2 style='margin:0;'>Missing Document Reminder</h2>
                </div>
                <div style='padding:20px;'>
                    <p>Dear <strong>" . htmlspecialchars($app['customer_name']) . "</strong>,</p>
                    <p>We have received some of your documents for your <strong>{$loan_type}</strong> application (Loan ID: {$app['loan_id']}), but a few mandatory documents are still pending.</p>
                    <p>To ensure smooth and fast processing of your application, please submit the following pending documents at your earliest convenience:</p>
                    {$missing_html}
                    <p>If you have already submitted these, please ignore this email or contact your loan executive.</p>
                    <br>
                    <p>Best regards,<br><strong>{$company_name}</strong></p>
                </div>
            </div>";
            
            $mail->send();
            
            // Log that it was sent today
            $logIns = $db->prepare("INSERT INTO doc_reminders_log (applicant_id, sent_date) VALUES (?, ?)");
            $logIns->execute([$id, $today]);
            
            $sent_count++;
            
        } catch (Exception $e) {
            // Log error or ignore
            error_log("Document Reminder Email failed for {$app['email']}: {$mail->ErrorInfo}");
        }
    }
}

echo json_encode(['success' => true, 'emails_sent' => $sent_count, 'date' => $today]);
?>
