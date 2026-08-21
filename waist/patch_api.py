import os

file_path = r'c:\Users\pc\Downloads\client mgmt2\api.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# The API handler for save_field_visit
save_field_visit_code = """
        case 'save_field_visit':
            $executive_name = $_SESSION['username'] ?? 'Staff';
            $visit_date = $_POST['visit_date'] ?? date('Y-m-d');
            $person_name = trim($_POST['person_name'] ?? '');
            $mobile = trim($_POST['mobile'] ?? '');
            $alt_mobile = trim($_POST['alt_mobile'] ?? '');
            $profession = trim($_POST['profession'] ?? '');
            $custom_profession = trim($_POST['custom_profession'] ?? '');
            $firm_name = trim($_POST['firm_name'] ?? '');
            $state = trim($_POST['state'] ?? '');
            $city = trim($_POST['city'] ?? '');
            $pincode = trim($_POST['pincode'] ?? '');
            $full_address = trim($_POST['full_address'] ?? '');
            $lead_quality = trim($_POST['lead_quality'] ?? '');
            $remarks = trim($_POST['remarks'] ?? '');
            $next_meeting_date = trim($_POST['next_meeting_date'] ?? '');
            $latitude = trim($_POST['lat'] ?? '');
            $longitude = trim($_POST['lon'] ?? '');
            $verified_address = trim($_POST['v_addr'] ?? '');
            
            $check_in_time = trim($_POST['check_in_time'] ?? '');
            $check_out_time = trim($_POST['check_out_time'] ?? '');
            
            if (!$person_name || !$mobile || !$firm_name || !$state || !$city || !$lead_quality) {
                return_json(['error' => 'Please fill all required fields.'], 400);
            }

            // Handle Photo
            $photo_path = null;
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
                $filename = 'visit_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                $dest = __DIR__ . '/uploads/field_visits/' . $filename;
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $dest)) {
                    $photo_path = 'uploads/field_visits/' . $filename;
                }
            }
            
            // Handle Audio
            $audio_path = null;
            if (isset($_FILES['audio_file']) && $_FILES['audio_file']['error'] === UPLOAD_ERR_OK) {
                $ext = 'webm'; // Usually webm from mediaRecorder
                $filename = 'audio_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                $dest = __DIR__ . '/uploads/field_visits/' . $filename;
                if (move_uploaded_file($_FILES['audio_file']['tmp_name'], $dest)) {
                    $audio_path = 'uploads/field_visits/' . $filename;
                }
            }

            $stmt = $db->prepare("INSERT INTO field_visits 
                (visit_date, executive_name, person_name, mobile, alt_mobile, profession, custom_profession, firm_name, state, city, pincode, full_address, lead_quality, photo_path, audio_path, latitude, longitude, verified_address, check_in_time, check_out_time) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
            $stmt->execute([
                $visit_date, $executive_name, $person_name, $mobile, $alt_mobile, $profession, $custom_profession, 
                $firm_name, $state, $city, $pincode, $full_address, $lead_quality, $photo_path, $audio_path, 
                $latitude, $longitude, $verified_address, $check_in_time, $check_out_time
            ]);
            
            $visit_id = $db->lastInsertId();
            
            if ($remarks || $next_meeting_date) {
                $f_stmt = $db->prepare("INSERT INTO field_visit_followups (visit_id, remarks, next_meeting_date, added_by) VALUES (?, ?, ?, ?)");
                $f_stmt->execute([$visit_id, $remarks, $next_meeting_date, $executive_name]);
            }

            return_json(['success' => true, 'message' => 'Visit recorded successfully!']);
            break;
"""

content = content.replace("case 'get_field_visits':", save_field_visit_code + "\n        case 'get_field_visits':")

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)
print("Added save_field_visit to api.php")
