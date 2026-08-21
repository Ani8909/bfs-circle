import os

api_path = r'c:\Users\pc\Downloads\client mgmt2\api.php'
with open(api_path, 'r', encoding='utf-8') as f:
    api = f.read()

target = """        case 'punch_out':"""

repl = """        case 'update_staff_profile':
            if (!isset($_SESSION['user_id'])) { echo json_encode(['error' => 'Not logged in']); exit; }
            $u = $_SESSION['username'];
            $uid = $_SESSION['user_id'];
            
            $mobile = $_POST['mobile'] ?? '';
            $p_email = $_POST['personal_email'] ?? '';
            $pass = $_POST['password'] ?? '';
            
            try {
                // Handle file upload
                $photo_sql = "";
                $params = [$mobile, $p_email];
                
                if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
                    $ext = strtolower(pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION));
                    $allowed = ['jpg','jpeg','png'];
                    if (in_array($ext, $allowed)) {
                        $new_name = 'profile_' . $uid . '_' . time() . '.' . $ext;
                        $dest = __DIR__ . '/uploads/employees/' . $new_name;
                        if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $dest)) {
                            $photo_sql = ", photo_path = ?";
                            $params[] = $new_name;
                        }
                    }
                }
                
                $params[] = $uid; // for WHERE clause
                
                $db->beginTransaction();
                
                $upd_emp = $db->prepare("UPDATE employees SET mobile = ?, personal_email = ? $photo_sql WHERE user_id = ?");
                $upd_emp->execute($params);
                
                if (!empty($pass)) {
                    $hash = password_hash($pass, PASSWORD_DEFAULT);
                    $upd_usr = $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                    $upd_usr->execute([$hash, $uid]);
                }
                
                $db->commit();
                echo json_encode(['success' => true]);
            } catch(Exception $e) {
                $db->rollBack();
                echo json_encode(['error' => $e->getMessage()]);
            }
            break;

        case 'punch_out':"""

if "update_staff_profile" not in api:
    api = api.replace(target, repl)
    with open(api_path, 'w', encoding='utf-8') as f:
        f.write(api)
    print("Added update_staff_profile to api.php")
else:
    print("Already exists")
