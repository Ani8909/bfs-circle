<?php
require_once '../config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Partner') {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$action = $_POST['action'] ?? '';
$user_id = $_SESSION['user_id'];

// Get partner name to set as 'added_by'
$stmt = $db->prepare("SELECT full_name FROM referrals WHERE user_id = ?");
$stmt->execute([$user_id]);
$partner = $stmt->fetch();
if (!$partner) {
    echo json_encode(['error' => 'Partner not found']);
    exit;
}
$partner_name = $partner['full_name'];

switch ($action) {
    case 'partner_add_lead':
        $lead_name = trim($_POST['name'] ?? $_POST['customer_name'] ?? $_POST['lead_name'] ?? '');
        $mobile = trim($_POST['phone'] ?? $_POST['mobile'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $loan_type = trim($_POST['loan_type'] ?? '');
        $loan_sub_type = trim($_POST['loan_sub_type'] ?? '');
        $requirement = $loan_type . ($loan_sub_type ? ' - ' . $loan_sub_type : '');
        $loan_amount = isset($_POST['loan_amount']) ? (float)$_POST['loan_amount'] : null;
        
        if (empty($lead_name) || empty($mobile)) {
            echo json_encode(['error' => 'Name and Mobile are required']);
            exit;
        }

        try {
            
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

$stmt = $db->prepare("INSERT INTO leads (lead_name, mobile, email, requirement, loan_amount, lead_source, stage, priority, added_by, photo) VALUES (?, ?, ?, ?, ?, 'Partner Referral', 'New Lead', 'Warm', ?, ?)");
$stmt->execute([$lead_name, $mobile, $email, $requirement, $loan_amount, $partner_name, $photo_path]);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;

    case 'partner_add_project':
        $project_name = trim($_POST['project_name'] ?? '');
        $location = trim($_POST['location'] ?? '');
        
        if (empty($project_name)) {
            echo json_encode(['error' => 'Project Name is required']);
            exit;
        }

        try {
            $stmt = $db->prepare("INSERT INTO partner_projects (partner_user_id, project_name, location) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $project_name, $location]);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;
        
    default:
        echo json_encode(['error' => 'Invalid action']);
        break;
}
