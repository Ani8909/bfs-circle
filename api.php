<?php
// api.php - BFS Financial Services backend API endpoints dispatcher

if (!isset($_GET['api'])) {
    return_json(['error' => 'API command not specified.'], 400);
}

$action = $_GET['api'];

try {
    // Authentication check for protected APIs
    $public_apis = ['login'];
    if (!in_array($action, $public_apis) && isset($_SESSION['user_id'])) {
        $stmt = $db->prepare("SELECT session_token FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $db_token = $stmt->fetchColumn();
        if ($db_token && $db_token !== ($_SESSION['session_token'] ?? '')) {
            session_destroy();
            return_json(['error' => 'SESSION_EXPIRED'], 401);
        }
    }
    
    if (!in_array($action, $public_apis) && !isset($_SESSION['user_id'])) {
        return_json(['error' => 'Unauthorized Access. Please login.'], 401);
    }

    switch ($action) {
        case 'add_employee':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return_json(['error' => 'Invalid Request'], 405);
            $full_name = trim($_POST['full_name'] ?? '');
            $username = trim($_POST['username'] ?? '');
            $password = trim($_POST['password'] ?? '');
            $role = trim($_POST['role'] ?? 'Staff');
            $mobile = trim($_POST['mobile'] ?? '');
            
            if(empty($full_name) || empty($username) || empty($password)) {
                return_json(['error' => 'Name, Username, and Password are required.'], 400);
            }
            
            try {
                $db->beginTransaction();
                
                // 1. Create User
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("INSERT INTO users (username, name, password_hash, role) VALUES (?, ?, ?, ?)");
                $stmt->execute([$username, $full_name, $hash, $role]);
                $user_id = $db->lastInsertId();
                
                // 2. Create Employee profile

                $dept = trim($_POST['department'] ?? 'General');
                $desig = trim($_POST['designation'] ?? 'Staff');
                $acc = trim($_POST['access_role'] ?? 'Staff');
                $emp_id = 'EMP' . date('Ym') . rand(100, 999);
                $stmt2 = $db->prepare("INSERT INTO employees (user_id, emp_id, full_name, mobile, personal_email, department, designation, access_role) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt2->execute([$user_id, $emp_id, $full_name, $mobile, $username, $dept, $desig, $acc]);

                
                $db->commit();
                
                log_activity("Added new employee: $full_name", "staff_hrms.php");
                return_json(['success' => true, 'message' => 'Employee onboarded successfully!']);
            } catch (PDOException $e) {
                $db->rollBack();
                if(strpos($e->getMessage(), 'UNIQUE') !== false) {
                    return_json(['error' => 'Username/Email already exists.']);
                }
                return_json(['error' => 'Database error: ' . $e->getMessage()]);
            }
            break;

        case 'ping':
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
            $stmt = $db->prepare("UPDATE users SET last_active = datetime('now', 'localtime'), last_ip = ? WHERE id = ?");
            $stmt->execute([$ip, $_SESSION['user_id']]);
            return_json(['success' => true]);
            break;
            
        case 'get_online_staff':
            if (($_SESSION['role'] ?? '') !== 'Admin') return_json(['error' => 'Admin privileges required'], 403);
            $stmt = $db->query("SELECT id, username, name, role, last_ip, last_active FROM users WHERE last_active >= datetime('now', 'localtime', '-2 minutes') AND role = 'Staff' ORDER BY last_active DESC");
            return_json($stmt->fetchAll());
            break;

        case 'login':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return_json(['error' => 'Invalid Request'], 405);
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            
            $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password_hash'])) {
                if (isset($user['is_active']) && $user['is_active'] == 0) {
                    return_json(['error' => 'Your account has been deactivated by Admin.'], 403);
                }
                $token = bin2hex(random_bytes(16));
                $db->prepare("UPDATE users SET session_token = ? WHERE id = ?")->execute([$token, $user['id']]);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['session_token'] = $token;
                return_json(['success' => true, 'message' => 'Login successful', 'role' => $user['role']]);
            }
            return_json(['error' => 'Invalid username or password'], 401);
            break;

        case 'logout':
            session_destroy();
            return_json(['success' => true, 'message' => 'Logged out successfully']);
            break;

        case 'create_user':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return_json(['error' => 'Invalid Request'], 405);
            if (($_SESSION['role'] ?? '') !== 'Admin') return_json(['error' => 'Admin privileges required'], 403);
            
            $name = trim($_POST['name'] ?? '');
            $role = $_POST['role'] ?? 'Staff';
            $staff_type = $_POST['staff_type'] ?? 'In-Office';
            $has_dashboard = isset($_POST['has_dashboard']) ? (int)$_POST['has_dashboard'] : 1;
            
            if ($has_dashboard === 1) {
                $username = trim($_POST['username'] ?? '');
                $password = $_POST['password'] ?? '';
                if (empty($username) || empty($password)) return_json(['error' => 'Username and password required'], 400);
            } else {
                // Generate a unique username for non-dashboard staff
                $username = 'staff_' . time() . rand(10, 99);
                $password = bin2hex(random_bytes(4)); // random password
            }
            
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $plain_password = $has_dashboard === 1 ? $password : null;
            
            try {
                $stmt = $db->prepare("INSERT INTO users (username, name, password_hash, role, is_active, staff_type, has_dashboard, plain_password) VALUES (?, ?, ?, ?, 1, ?, ?, ?)");
                $stmt->execute([$username, $name, $hash, $role, $staff_type, $has_dashboard, $plain_password]);
                return_json(['success' => true, 'message' => 'User created successfully']);
            } catch (Exception $e) {
                return_json(['error' => 'Username might already exist'], 400);
            }
            break;


        case 'bulk_assign':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return_json(['error' => 'Invalid Request'], 405);
            if (($_SESSION['role'] ?? '') !== 'Admin') return_json(['error' => 'Admin privileges required'], 403);
            
            $type = $_POST['type'] ?? '';
            $assigned_to = $_POST['assigned_to'] ?? '';
            $ids_json = $_POST['ids'] ?? '[]';
            
            $ids = json_decode($ids_json, true);
            if (!is_array($ids) || empty($ids)) return_json(['error' => 'No records selected'], 400);
            if (empty($assigned_to)) return_json(['error' => 'No staff selected'], 400);
            
            $table = $type === 'leads' ? 'leads' : 'pre_leads';
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            
            $params = array_merge([$assigned_to], $ids);
            
            $stmt = $db->prepare("UPDATE $table SET assigned_to = ? WHERE id IN ($placeholders)");
            $stmt->execute($params);
            
            $currentUser = $_SESSION['username'] ?? 'Unknown';
            $c = count($ids);
            log_activity("Bulk assigned $c $type to $assigned_to");
            
            return_json(['success' => true]);
            break;
            
        case 'bulk_delete':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return_json(['error' => 'Invalid Request'], 405);
            if (($_SESSION['role'] ?? '') !== 'Admin') return_json(['error' => 'Admin privileges required'], 403);
            
            $type = $_POST['type'] ?? '';
            $ids_json = $_POST['ids'] ?? '[]';
            
            $ids = json_decode($ids_json, true);
            if (!is_array($ids) || empty($ids)) return_json(['error' => 'No records selected'], 400);
            
            $table = $type === 'leads' ? 'leads' : 'pre_leads';
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            
            $stmt = $db->prepare("DELETE FROM $table WHERE id IN ($placeholders)");
            $stmt->execute($ids);
            
            $currentUser = $_SESSION['username'] ?? 'Unknown';
            $c = count($ids);
            log_activity("Bulk deleted $c $type");
            
            return_json(['success' => true]);
            break;
            
        case 'reassign_client':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return_json(['error' => 'Invalid Request'], 405);
            if (($_SESSION['role'] ?? '') !== 'Admin') return_json(['error' => 'Admin privileges required'], 403);
            
            $client_id = (int)($_POST['client_id'] ?? 0);
            $new_staff = trim($_POST['new_staff'] ?? '');
            
            if (!$client_id || empty($new_staff)) return_json(['error' => 'Missing data'], 400);
            
            $stmt = $db->prepare("UPDATE clients SET assigned_to = ? WHERE id = ?");
            $stmt->execute([$new_staff, $client_id]);
            
            log_activity("Reassigned client ID $client_id to $new_staff");
            return_json(['success' => true]);
            break;
            
        case 'get_users':
            $stmt = $db->query("SELECT id, username, name, role, is_active, created_at, staff_type, has_dashboard, plain_password FROM users ORDER BY created_at ASC");
            return_json($stmt->fetchAll());
            break;
            
        case 'toggle_user_status':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return_json(['error' => 'Invalid Request'], 405);
            if (($_SESSION['role'] ?? '') !== 'Admin') return_json(['error' => 'Admin privileges required'], 403);
            $id = $_POST['id'] ?? null;
            if (!$id) return_json(['error' => 'Missing ID'], 400);
            if ($id == $_SESSION['user_id']) return_json(['error' => 'Cannot deactivate yourself'], 400);
            
            $target_role = $db->query("SELECT role FROM users WHERE id = " . (int)$id)->fetchColumn();
            if ($target_role === 'Admin') return_json(['error' => 'Cannot deactivate the Admin account'], 400);

            $stmt = $db->prepare("UPDATE users SET is_active = CASE WHEN is_active = 1 THEN 0 ELSE 1 END WHERE id = ?");
            $stmt->execute([$id]);
            return_json(['success' => true, 'message' => 'Status updated']);
            break;
            
        case 'delete_user':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return_json(['error' => 'Invalid Request'], 405);
            if (($_SESSION['role'] ?? '') !== 'Admin') return_json(['error' => 'Admin privileges required'], 403);
            $id = $_POST['id'] ?? null;
            if (!$id) return_json(['error' => 'Missing ID'], 400);
            if ($id == $_SESSION['user_id']) return_json(['error' => 'Cannot delete yourself'], 400);
            
            $target_role = $db->query("SELECT role FROM users WHERE id = " . (int)$id)->fetchColumn();
            if ($target_role === 'Admin') return_json(['error' => 'Cannot delete the Admin account'], 400);

            $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
            return_json(['success' => true, 'message' => 'User deleted']);
            break;

        case 'get_templates':
            $type = $_GET['type'] ?? '';
            if ($type) {
                $stmt = $db->prepare("SELECT * FROM email_templates WHERE type = ? ORDER BY template_name ASC");
                $stmt->execute([$type]);
            } else {
                $stmt = $db->query("SELECT * FROM email_templates ORDER BY type ASC, template_name ASC");
            }
            return_json($stmt->fetchAll());
            break;

        case 'save_template':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return_json(['error' => 'Invalid Request'], 405);
            $id = $_POST['id'] ?? null;
            $name = trim($_POST['template_name'] ?? '');
            $type = $_POST['type'] ?? '';
            $subject = trim($_POST['subject'] ?? '');
            $body = trim($_POST['body'] ?? '');
            
            if (empty($name) || empty($type) || empty($subject) || empty($body)) {
                return_json(['error' => 'All fields are required'], 400);
            }
            
            $attachment_name = null;
            if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = __DIR__ . '/uploads/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                $filename = time() . '_' . preg_replace("/[^a-zA-Z0-9.-]/", "_", $_FILES['attachment']['name']);
                if (move_uploaded_file($_FILES['attachment']['tmp_name'], $upload_dir . $filename)) {
                    $attachment_name = $filename;
                }
            }

            if ($id) {
                if ($attachment_name) {
                    $stmt = $db->prepare("UPDATE email_templates SET template_name=?, type=?, subject=?, body=?, attachment_name=? WHERE id=?");
                    $stmt->execute([$name, $type, $subject, $body, $attachment_name, $id]);
                } else {
                    $stmt = $db->prepare("UPDATE email_templates SET template_name=?, type=?, subject=?, body=? WHERE id=?");
                    $stmt->execute([$name, $type, $subject, $body, $id]);
                }
            } else {
                $stmt = $db->prepare("INSERT INTO email_templates (template_name, type, subject, body, attachment_name) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$name, $type, $subject, $body, $attachment_name]);
            }
            return_json(['success' => true, 'message' => 'Template saved successfully']);
            break;

        case 'delete_template':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return_json(['error' => 'Invalid Request'], 405);
            $id = $_POST['id'] ?? null;
            if (!$id) return_json(['error' => 'Missing ID'], 400);
            
            if (($_SESSION['role'] ?? '') === 'Admin') {
                $stmt = $db->prepare("DELETE FROM email_templates WHERE id = ?");
                $stmt->execute([$id]);
                log_activity("Admin deleted email template ID: $id");
                return_json(['success' => true, 'message' => 'Template deleted']);
            } else {
                $stmt = $db->prepare("UPDATE email_templates SET delete_requested = 1 WHERE id = ?");
                $stmt->execute([$id]);
                log_activity("Staff requested deletion for email template ID: $id");
                return_json(['success' => true, 'message' => 'Delete request sent to Admin']);
            }
            break;
            
        case 'approve_delete_template':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ($_SESSION['role'] ?? '') !== 'Admin') return_json(['error' => 'Unauthorized'], 403);
            $id = $_POST['id'] ?? null;
            $db->prepare("DELETE FROM email_templates WHERE id = ?")->execute([$id]);
            log_activity("Admin approved deletion of email template ID: $id");
            return_json(['success' => true, 'message' => 'Template deleted permanently']);
            break;
            
        case 'reject_delete_template':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ($_SESSION['role'] ?? '') !== 'Admin') return_json(['error' => 'Unauthorized'], 403);
            $id = $_POST['id'] ?? null;
            $db->prepare("UPDATE email_templates SET delete_requested = 0 WHERE id = ?")->execute([$id]);
            log_activity("Admin rejected deletion for email template ID: $id");
            return_json(['success' => true, 'message' => 'Delete request rejected']);
            break;

        case 'get_ppts':
            $stmt = $db->query("SELECT * FROM presentations ORDER BY created_at DESC");
            return_json($stmt->fetchAll());
            break;
            
        case 'upload_ppt':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return_json(['error' => 'Invalid Request'], 405);
            $original_name = trim($_POST['original_name'] ?? '');
            
            if (empty($original_name)) return_json(['error' => 'Presentation title is required'], 400);
            
            if (!isset($_FILES['ppt_file']) || $_FILES['ppt_file']['error'] !== UPLOAD_ERR_OK) {
                return_json(['error' => 'File upload failed or no file selected'], 400);
            }
            
            $upload_dir = __DIR__ . '/uploads/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            $filename = time() . '_ppt_' . preg_replace("/[^a-zA-Z0-9.-]/", "_", $_FILES['ppt_file']['name']);
            
            if (move_uploaded_file($_FILES['ppt_file']['tmp_name'], $upload_dir . $filename)) {
                $stmt = $db->prepare("INSERT INTO presentations (original_name, filename) VALUES (?, ?)");
                $stmt->execute([$original_name, $filename]);
                return_json(['success' => true, 'message' => 'Presentation uploaded successfully']);
            }
            return_json(['error' => 'Failed to move uploaded file'], 500);
            break;
            
        case 'delete_ppt':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return_json(['error' => 'Invalid Request'], 405);
            $id = $_POST['id'] ?? null;
            if (!$id) return_json(['error' => 'Missing ID'], 400);
            
            if (($_SESSION['role'] ?? '') === 'Admin') {
                $filename = $db->query("SELECT filename FROM presentations WHERE id = " . (int)$id)->fetchColumn();
                if ($filename && file_exists(__DIR__ . '/uploads/' . $filename)) {
                    @unlink(__DIR__ . '/uploads/' . $filename);
                }
                $stmt = $db->prepare("DELETE FROM presentations WHERE id = ?");
                $stmt->execute([$id]);
                log_activity("Admin deleted presentation ID: $id");
                return_json(['success' => true, 'message' => 'Presentation deleted']);
            } else {
                $stmt = $db->prepare("UPDATE presentations SET delete_requested = 1 WHERE id = ?");
                $stmt->execute([$id]);
                log_activity("Staff requested deletion for presentation ID: $id");
                return_json(['success' => true, 'message' => 'Delete request sent to Admin']);
            }
            break;
            
        case 'approve_delete_ppt':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ($_SESSION['role'] ?? '') !== 'Admin') return_json(['error' => 'Unauthorized'], 403);
            $id = $_POST['id'] ?? null;
            $filename = $db->query("SELECT filename FROM presentations WHERE id = " . (int)$id)->fetchColumn();
            if ($filename && file_exists(__DIR__ . '/uploads/' . $filename)) {
                @unlink(__DIR__ . '/uploads/' . $filename);
            }
            $db->prepare("DELETE FROM presentations WHERE id = ?")->execute([$id]);
            log_activity("Admin approved deletion of presentation ID: $id");
            return_json(['success' => true, 'message' => 'Presentation deleted permanently']);
            break;
            
        case 'reject_delete_ppt':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ($_SESSION['role'] ?? '') !== 'Admin') return_json(['error' => 'Unauthorized'], 403);
            $id = $_POST['id'] ?? null;
            $db->prepare("UPDATE presentations SET delete_requested = 0 WHERE id = ?")->execute([$id]);
            log_activity("Admin rejected deletion for presentation ID: $id");
            return_json(['success' => true, 'message' => 'Delete request rejected']);
            break;

        case 'bulk_upload_leads':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return_json(['error' => 'Invalid Request'], 405);
            $leads_json = $_POST['leads_json'] ?? '[]';
            $leads = json_decode($leads_json, true);
            if (!$leads || !is_array($leads)) return_json(['error' => 'Invalid data format'], 400);

            $db->beginTransaction();
            try {
                $existing_mobiles = $db->query("SELECT mobile FROM leads WHERE mobile != ''")->fetchAll(PDO::FETCH_COLUMN);
                $existing_emails = $db->query("SELECT email FROM leads WHERE email != ''")->fetchAll(PDO::FETCH_COLUMN);
                $existing_mobiles_map = array_flip($existing_mobiles);
                $existing_emails_map = array_flip($existing_emails);

                $stmt = $db->prepare("INSERT INTO leads (lead_name, company_name, mobile, email, lead_source, priority, stage, assigned_to, location, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $upload_count = 0;
                foreach ($leads as $lead) {
                    $mob = trim($lead['mobile'] ?? '');
                    $eml = trim($lead['email'] ?? '');
                    if ($mob !== '' && isset($existing_mobiles_map[$mob])) continue;
                    if ($eml !== '' && isset($existing_emails_map[$eml])) continue;
                    if ($mob !== '') $existing_mobiles_map[$mob] = true;
                    if ($eml !== '') $existing_emails_map[$eml] = true;
                    
                    $upload_count++;
                    $stmt->execute([
                        $lead['lead_name'] ?? '',
                        $lead['company_name'] ?? '',
                        $mob,
                        $eml,
                        $lead['lead_source'] ?? 'Cold Call',
                        $lead['priority'] ?? 'Warm',
                        $lead['stage'] ?? 'New Lead',
                        $lead['assigned_to'] ?? '',
                        $lead['location'] ?? '',
                        $lead['notes'] ?? ''
                    ]);
                }
                $db->commit();
                log_activity("Bulk uploaded " . $upload_count . " new leads");
                return_json(['success' => true, 'message' => $upload_count . ' new leads uploaded successfully (duplicates skipped)']);
            } catch (Exception $e) {
                $db->rollBack();
                return_json(['error' => 'Database error: ' . $e->getMessage()], 500);
            }
            break;

        case 'save_lead':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return_json(['error' => 'Invalid Request'], 405);
            $lead_id     = isset($_POST['lead_id']) && (int)$_POST['lead_id'] > 0 ? (int)$_POST['lead_id'] : null;
            
            $role = $_SESSION['role'] ?? '';
            $username = $_SESSION['username'] ?? '';

            $lead_name   = trim($_POST['lead_name'] ?? '');
            $company     = trim($_POST['company_name'] ?? '');
            $mobile      = trim($_POST['mobile'] ?? '');
            $email       = trim($_POST['email'] ?? '');
            $source      = trim($_POST['lead_source'] ?? 'Cold Call');
            $assigned_to = trim($_POST['assigned_to'] ?? '');
            if ($role !== 'Admin') $assigned_to = $username;
            $priority    = trim($_POST['priority'] ?? 'Warm');
            $stage       = trim($_POST['stage'] ?? 'New Lead');
            $notes       = trim($_POST['notes'] ?? '');

            if (empty($lead_name)) return_json(['error' => 'Lead name is required'], 400);

            if ($lead_id) {
                $existing = $db->query("SELECT * FROM leads WHERE id=$lead_id")->fetch();
                if ($role !== 'Admin' && $existing['assigned_to'] !== $username) {
                    return_json(['error' => 'Unauthorized to edit this lead'], 403);
                }
                if ($role !== 'Admin') {
                    $mobile = $existing['mobile'];
                    $email = $existing['email'];
                }
                
                $stmt = $db->prepare("UPDATE leads SET lead_name=?,company_name=?,mobile=?,email=?,lead_source=?,assigned_to=?,priority=?,stage=?,notes=? WHERE id=?");
                $stmt->execute([$lead_name,$company,$mobile,$email,$source,$assigned_to,$priority,$stage,$notes,$lead_id]);
                log_activity("Updated lead: $lead_name", "leads.php?edit_lead=$lead_id");
                return_json(['success' => true, 'message' => 'Lead updated successfully']);
            } else {
                if (empty($mobile)) return_json(['error' => 'Mobile is required'], 400);
                $stmt = $db->prepare("INSERT INTO leads (lead_name,company_name,mobile,email,lead_source,assigned_to,priority,stage,notes) VALUES (?,?,?,?,?,?,?,?,?)");
                $stmt->execute([$lead_name,$company,$mobile,$email,$source,$assigned_to,$priority,$stage,$notes]);
                $new_id = $db->lastInsertId();
                log_activity("New lead added: $lead_name (" . ($company ?: 'Individual') . ")", "leads.php?edit_lead=$new_id");
                return_json(['success' => true, 'message' => "Lead '$lead_name' added successfully!"]);
            }
            break;

        case 'get_preleads':
            $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
            $limit = 10;
            $offset = ($page - 1) * $limit;
            $search = isset($_GET['search']) ? trim($_GET['search']) : '';
            $tab = isset($_GET['tab']) ? trim($_GET['tab']) : 'new';
            $status = isset($_GET['status']) ? trim($_GET['status']) : '';
            $source = isset($_GET['source']) ? trim($_GET['source']) : '';
            $date_range = isset($_GET['date_range']) ? trim($_GET['date_range']) : '';
            
            $where = "1=1";
            $params = [];
            
            if (($_SESSION['role'] ?? '') !== 'Admin') {
                $where .= " AND assigned_to = ?";
                $params[] = $_SESSION['username'] ?? '';
            }
            
            // Tab Filtering
            if ($tab === 'new') {
                $where .= " AND (status = 'Not Contacted' OR status IS NULL OR status = '') AND call_count = 0";
            } elseif ($tab === 'followup') {
                $where .= " AND (status = 'Interested' OR status = 'Follow Up' OR followup_date IS NOT NULL)";
            } elseif ($tab === 'archived') {
                $where .= " AND (status = 'Not Interested' OR status = 'Junk' OR status = 'Converted')";
            }
            
            if ($search !== '') {
                $where .= " AND (name LIKE ? OR mobile LIKE ? OR email LIKE ? OR company_name LIKE ?)";
                $sp = "%$search%";
                $params = array_merge($params, [$sp, $sp, $sp, $sp]);
            }
            
            if ($status !== '') {
                $where .= " AND status = ?";
                $params[] = $status;
            }
            if ($source !== '') {
                $where .= " AND source = ?";
                $params[] = $source;
            }
            
            // Total count
            $stmt_c = $db->prepare("SELECT COUNT(*) FROM pre_leads WHERE $where");
            $stmt_c->execute($params);
            $total = $stmt_c->fetchColumn();
            
            // Fetch records
            $stmt = $db->prepare("SELECT * FROM pre_leads WHERE $where ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
            $stmt->execute($params);
            
            return_json([
                'success' => true,
                'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
                'total' => $total,
                'page' => $page,
                'total_pages' => ceil($total / $limit)
            ]);
            break;
            
        case 'log_call':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return_json(['error' => 'Invalid Request'], 405);
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            $new_status = isset($_POST['status']) ? trim($_POST['status']) : '';
            $followup = isset($_POST['followup_date']) ? trim($_POST['followup_date']) : null;
            $note = isset($_POST['notes']) ? trim($_POST['notes']) : '';
            
            if (!$id) return_json(['error'=>'Missing ID']);
            
            $stmt = $db->prepare("UPDATE pre_leads SET status = ?, followup_date = ?, last_called_at = datetime('now', 'localtime'), call_count = call_count + 1, notes = IFNULL(notes,'') || '
' || ? WHERE id = ?");
            $stmt->execute([$new_status, $followup, date('Y-m-d H:i').": ".$note, $id]);
            
            // Auto Archive logic
            $check = $db->prepare("SELECT call_count FROM pre_leads WHERE id = ?");
            $check->execute([$id]);
            $cc = $check->fetchColumn();
            if ($cc >= 5 && $new_status !== 'Interested' && $new_status !== 'Converted') {
                $db->prepare("UPDATE pre_leads SET status = 'Junk' WHERE id = ?")->execute([$id]);
            }
            
            return_json(['success' => true]);
            break;


        case 'save_prelead':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return_json(['error' => 'Invalid Request'], 405);
            $id = isset($_POST['id']) && (int)$_POST['id'] > 0 ? (int)$_POST['id'] : null;
            
            $role = $_SESSION['role'] ?? '';
            $username = $_SESSION['username'] ?? '';

            $name = trim($_POST['name'] ?? '');
            $company = trim($_POST['company_name'] ?? '');
            $mobile = trim($_POST['mobile'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $source = trim($_POST['source'] ?? 'Unknown');
            $status = trim($_POST['status'] ?? 'Not Contacted');
            $assigned_to = trim($_POST['assigned_to'] ?? '');
            if ($role !== 'Admin') $assigned_to = $username;
            $location = trim($_POST['location'] ?? '');
            $notes = trim($_POST['notes'] ?? '');

            if (empty($name)) return_json(['error' => 'Name is required'], 400);

            if ($id) {
                $existing = $db->query("SELECT * FROM pre_leads WHERE id=$id")->fetch();
                if ($role !== 'Admin' && $existing['assigned_to'] !== $username) {
                    return_json(['error' => 'Unauthorized to edit this pre-lead'], 403);
                }
                if ($role !== 'Admin') {
                    $mobile = $existing['mobile'];
                    $email = $existing['email'];
                }

                $stmt = $db->prepare("UPDATE pre_leads SET name=?, company_name=?, mobile=?, email=?, source=?, status=?, assigned_to=?, location=?, notes=? WHERE id=?");
                $stmt->execute([$name, $company, $mobile, $email, $source, $status, $assigned_to, $location, $notes, $id]);
                log_activity("Updated pre-lead: $name", "pre_leads.php?edit_prelead=$id");
                return_json(['success' => true, 'message' => 'Pre-Lead updated!']);
            } else {
                if (empty($mobile)) return_json(['error' => 'Mobile is required'], 400);
                $stmt = $db->prepare("INSERT INTO pre_leads (name, company_name, mobile, email, source, status, assigned_to, location, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $company, $mobile, $email, $source, $status, $assigned_to, $location, $notes]);
                $new_id = $db->lastInsertId();
                log_activity("Added new pre-lead: $name", "pre_leads.php?edit_prelead=$new_id");
                return_json(['success' => true, 'message' => 'Pre-Lead added!']);
            }
            break;

        case 'delete_prelead':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return_json(['error' => 'Invalid Request'], 405);
            if (($_SESSION['role'] ?? '') !== 'Admin') return_json(['error' => 'Admin privileges required to delete pre-leads'], 403);
            $id = (int)($_POST['id'] ?? 0);
            if (!$id) return_json(['error' => 'Missing ID'], 400);
            $prelead_name = $db->query("SELECT name FROM pre_leads WHERE id=$id")->fetchColumn();
            $db->prepare("DELETE FROM pre_leads WHERE id = ?")->execute([$id]);
            log_activity("Deleted pre-lead: $prelead_name", "pre_leads.php");
            return_json(['success' => true, 'message' => 'Pre-Lead deleted']);
            break;

        case 'promote_prelead':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return_json(['error' => 'Invalid Request'], 405);
            $id = (int)($_POST['id'] ?? 0);
            if (!$id) return_json(['error' => 'Missing ID'], 400);
            
            $db->beginTransaction();
            try {
                $pre_lead = $db->query("SELECT * FROM pre_leads WHERE id = $id")->fetch();
                if (!$pre_lead) throw new Exception("Pre-Lead not found");

                $stmt = $db->prepare("INSERT INTO leads (lead_name, company_name, mobile, email, lead_source, priority, stage, assigned_to, location, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $pre_lead['name'],
                    $pre_lead['company_name'],
                    $pre_lead['mobile'],
                    $pre_lead['email'],
                    $pre_lead['source'],
                    'Warm',
                    'New Lead',
                    $pre_lead['assigned_to'],
                    $pre_lead['location'],
                    $pre_lead['notes']
                ]);
                $new_id = $db->lastInsertId();
                
                $db->prepare("DELETE FROM pre_leads WHERE id = ?")->execute([$id]);
                $db->commit();
                log_activity("Promoted pre-lead to lead: " . $pre_lead['name'], "leads.php?edit_lead=$new_id");
                return_json(['success' => true, 'message' => 'Promoted to CRM successfully!']);
            } catch (Exception $e) {
                $db->rollBack();
                return_json(['error' => $e->getMessage()], 500);
            }
            break;

        case 'update_prelead_status':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return_json(['error' => 'Invalid Request'], 405);
            $id = (int)($_POST['id'] ?? 0);
            $status = trim($_POST['status'] ?? '');
            if (!$id || !$status) return_json(['error' => 'Invalid inputs'], 400);
            
            $name = "Unknown";
            $l = $db->query("SELECT name FROM pre_leads WHERE id=$id")->fetch();
            if($l) $name = $l['name'];
            
            $db->prepare("UPDATE pre_leads SET status = ? WHERE id = ?")->execute([$status, $id]);
            log_activity("Updated pre-lead status to $status for: $name", "pre_leads.php?edit_prelead=$id");
            
            return_json(['success' => true]);
            break;

        case 'bulk_upload_preleads':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return_json(['error' => 'Invalid Request'], 405);
            $leads_json = $_POST['leads_json'] ?? '[]';
            $leads = json_decode($leads_json, true);
            if (!$leads || !is_array($leads)) return_json(['error' => 'Invalid data format'], 400);

            $db->beginTransaction();
            try {
                $existing_mobiles = $db->query("SELECT mobile FROM pre_leads WHERE mobile != ''")->fetchAll(PDO::FETCH_COLUMN);
                $existing_emails = $db->query("SELECT email FROM pre_leads WHERE email != ''")->fetchAll(PDO::FETCH_COLUMN);
                $existing_mobiles_map = array_flip($existing_mobiles);
                $existing_emails_map = array_flip($existing_emails);

                $stmt = $db->prepare("INSERT INTO pre_leads (name, company_name, mobile, email, source, status, assigned_to, location, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $upload_count = 0;
                foreach ($leads as $lead) {
                    $mob = trim($lead['mobile'] ?? '');
                    $eml = trim($lead['email'] ?? '');
                    if ($mob !== '' && isset($existing_mobiles_map[$mob])) continue;
                    if ($eml !== '' && isset($existing_emails_map[$eml])) continue;
                    if ($mob !== '') $existing_mobiles_map[$mob] = true;
                    if ($eml !== '') $existing_emails_map[$eml] = true;

                    $upload_count++;
                    $stmt->execute([
                        $lead['lead_name'] ?? '',
                        $lead['company_name'] ?? '',
                        $mob,
                        $eml,
                        $lead['lead_source'] ?? 'Unknown',
                        'Not Contacted',
                        $lead['assigned_to'] ?? '',
                        $lead['location'] ?? '',
                        $lead['notes'] ?? ''
                    ]);
                }
                $db->commit();
                log_activity("Bulk uploaded " . $upload_count . " new Pre-Leads", "pre_leads.php");
                return_json(['success' => true, 'message' => $upload_count . ' new Pre-Leads uploaded successfully (duplicates skipped)']);
            } catch (Exception $e) {
                $db->rollBack();
                return_json(['error' => 'Database error: ' . $e->getMessage()], 500);
            }
            break;
            
        case 'get_reminders':
            $role = $_SESSION['role'] ?? '';
            $username = $_SESSION['username'] ?? '';
            $search = $_GET['search'] ?? '';
            $date_filter = $_GET['date'] ?? '';
            $ref_type_filter = $_GET['ref_type'] ?? '';
            $category_filter = $_GET['category'] ?? '';
            $staff_filter = $_GET['assigned_to'] ?? '';
            $priority_filter = $_GET['priority'] ?? '';
            
            $now = date('Y-m-d H:i:s');
            $today_start = date('Y-m-d') . ' 00:00:00';
            $today_end   = date('Y-m-d') . ' 23:59:59';
            
            // Build WHERE conditions
            $where = "status IN ('Pending','Snoozed') AND (snoozed_until IS NULL OR snoozed_until <= ?)";
            $params_r = [$now];
            
            if ($role !== 'Admin') {
                $where .= " AND assigned_to = ?";
                $params_r[] = $username;
            } elseif ($staff_filter) {
                $where .= " AND assigned_to = ?";
                $params_r[] = $staff_filter;
            }
            if ($ref_type_filter) {
                $where .= " AND reference_type = ?";
                $params_r[] = $ref_type_filter;
            }
            if ($category_filter) {
                $where .= " AND reminder_category = ?";
                $params_r[] = $category_filter;
            }
            if ($priority_filter) {
                $where .= " AND priority = ?";
                $params_r[] = $priority_filter;
            }
            if ($date_filter === 'overdue') {
                $where .= " AND remind_at < ?";
                $params_r[] = $now;
            } elseif ($date_filter === 'today') {
                $where .= " AND remind_at BETWEEN ? AND ?";
                $params_r[] = $today_start;
                $params_r[] = $today_end;
            } elseif ($date_filter === 'upcoming') {
                $where .= " AND remind_at > ?";
                $params_r[] = $today_end;
            }
            if ($search) {
                $where .= " AND (title LIKE ? OR notes LIKE ? OR reference_label LIKE ?)";
                $params_r[] = "%$search%";
                $params_r[] = "%$search%";
                $params_r[] = "%$search%";
            }
            
            $stmt = $db->prepare("SELECT * FROM reminders WHERE $where ORDER BY 
                CASE WHEN remind_at < '$now' THEN 0 ELSE 1 END ASC,
                CASE priority WHEN 'High' THEN 0 WHEN 'Medium' THEN 1 ELSE 2 END ASC,
                remind_at ASC");
            $stmt->execute($params_r);
            $reminders = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return_json($reminders);
            break;

        case 'get_reminder_count':
            // Returns count of due/overdue reminders for bell badge
            $username = $_SESSION['username'] ?? '';
            $role = $_SESSION['role'] ?? '';
            $now = date('Y-m-d H:i:s');
            if ($role === 'Admin') {
                $s = $db->prepare("SELECT COUNT(*) FROM reminders WHERE status IN ('Pending','Snoozed') AND remind_at <= ? AND (snoozed_until IS NULL OR snoozed_until <= ?)");
                $s->execute([$now, $now]);
            } else {
                $s = $db->prepare("SELECT COUNT(*) FROM reminders WHERE status IN ('Pending','Snoozed') AND assigned_to = ? AND remind_at <= ? AND (snoozed_until IS NULL OR snoozed_until <= ?)");
                $s->execute([$username, $now, $now]);
            }
            $due_count = (int)$s->fetchColumn();
            
            // Also get top 5 upcoming/overdue for bell dropdown
            if ($role === 'Admin') {
                $s2 = $db->prepare("SELECT id, title, notes, remind_at, priority, reference_type, reference_label, reminder_category FROM reminders WHERE status IN ('Pending','Snoozed') AND (snoozed_until IS NULL OR snoozed_until <= ?) ORDER BY remind_at ASC LIMIT 5");
                $s2->execute([$now]);
            } else {
                $s2 = $db->prepare("SELECT id, title, notes, remind_at, priority, reference_type, reference_label, reminder_category FROM reminders WHERE status IN ('Pending','Snoozed') AND assigned_to = ? AND (snoozed_until IS NULL OR snoozed_until <= ?) ORDER BY remind_at ASC LIMIT 5");
                $s2->execute([$username, $now]);
            }
            return_json(['count' => $due_count, 'items' => $s2->fetchAll(PDO::FETCH_ASSOC)]);
            break;
            
        case 'save_reminder':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return_json(['error' => 'Invalid Request'], 405);
            // New advanced save_reminder — supports all entity types
            $title        = trim($_POST['title'] ?? '');
            $ref_type     = $_POST['reference_type'] ?? 'General';
            $ref_id       = trim($_POST['reference_id'] ?? '');
            $ref_label    = trim($_POST['reference_label'] ?? '');
            $remind_at    = $_POST['remind_at'] ?? '';
            $notes        = trim($_POST['notes'] ?? '');
            $priority     = $_POST['priority'] ?? 'Medium';
            $category     = $_POST['reminder_category'] ?? 'Follow-up';
            $recurrence   = $_POST['recurrence'] ?? 'None';
            $assigned_to  = $_POST['assigned_to'] ?? $_SESSION['username'];
            
            // Legacy support — if old lead_type/lead_id sent
            $lead_type = $_POST['lead_type'] ?? $ref_type;
            $lead_id   = (int)($_POST['lead_id'] ?? ($ref_type !== 'General' ? $ref_id : 0));
            
            // If ref_label not given, try to auto-fetch from DB
            if (empty($ref_label) && $ref_id) {
                if ($ref_type === 'Lead') {
                    $lbl = $db->query("SELECT customer_name FROM applicants WHERE id=" . (int)$ref_id)->fetchColumn();
                    if (!$lbl) $lbl = $db->query("SELECT lead_name FROM leads WHERE id=" . (int)$ref_id)->fetchColumn();
                    $ref_label = $lbl ?: '';
                } elseif ($ref_type === 'Banker') {
                    $lbl = $db->query("SELECT full_name || ' (' || bank_name || ')' FROM bankers WHERE id=" . (int)$ref_id)->fetchColumn();
                    $ref_label = $lbl ?: '';
                } elseif ($ref_type === 'Referral') {
                    $lbl = $db->query("SELECT full_name FROM referrals WHERE id=" . (int)$ref_id)->fetchColumn();
                    $ref_label = $lbl ?: '';
                } elseif ($ref_type === 'Pre-Lead') {
                    $lbl = $db->query("SELECT name FROM pre_leads WHERE id=" . (int)$ref_id)->fetchColumn();
                    $ref_label = $lbl ?: '';
                }
            }
            
            if (empty($title)) $title = $ref_label ? "Follow-up: $ref_label" : 'New Reminder';
            
            // Auto-assign to entity owner if staff not given
            if ($ref_type === 'Lead' && $ref_id) {
                $owner = $db->query("SELECT added_by FROM applicants WHERE id=" . (int)$ref_id)->fetchColumn();
                if (!$owner) $owner = $db->query("SELECT assigned_to FROM leads WHERE id=" . (int)$ref_id)->fetchColumn();
                if ($owner && ($assigned_to === $_SESSION['username'])) $assigned_to = $owner;
            }
            
            $stmt = $db->prepare("INSERT INTO reminders (lead_type, lead_id, assigned_to, remind_at, notes, title, priority, reference_type, reference_id, reference_label, reminder_category, recurrence) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$lead_type, $lead_id, $assigned_to, $remind_at, $notes, $title, $priority, $ref_type, $ref_id, $ref_label, $category, $recurrence]);
            $new_id = $db->lastInsertId();
            
            log_activity("Set reminder: '$title' for $ref_type" . ($ref_label ? " ($ref_label)" : '') . " at $remind_at", "reminders.php");
            return_json(['success' => true, 'message' => 'Reminder saved!', 'id' => $new_id]);
            break;

        case 'complete_reminder':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return_json(['error' => 'Invalid Request'], 405);
            $id = (int)($_POST['id'] ?? 0);
            if ($id) {
                $rem = $db->query("SELECT * FROM reminders WHERE id=$id")->fetch(PDO::FETCH_ASSOC);
                $db->prepare("UPDATE reminders SET status='Completed', completed_at=datetime('now'), completed_by=? WHERE id=?")->execute([$_SESSION['username'], $id]);
                $label = $rem ? ($rem['title'] ?: $rem['reference_label'] ?: 'reminder') : 'reminder';
                log_activity("Completed reminder: '$label'", "reminders.php");
                return_json(['success' => true]);
            }
            return_json(['error' => 'Missing ID'], 400);
            break;

        case 'snooze_reminder':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return_json(['error' => 'Invalid Request'], 405);
            $id = (int)($_POST['id'] ?? 0);
            $minutes = (int)($_POST['minutes'] ?? 60);
            if ($id && $minutes > 0) {
                $snooze_until = date('Y-m-d H:i:s', strtotime("+$minutes minutes"));
                $db->prepare("UPDATE reminders SET status='Snoozed', snoozed_until=? WHERE id=?")->execute([$snooze_until, $id]);
                return_json(['success' => true, 'snoozed_until' => $snooze_until]);
            }
            return_json(['error' => 'Invalid request'], 400);
            break;

        case 'search_entity':
            // Powers the "Linked To" live search in Add Reminder modal
            $type = $_GET['type'] ?? 'Lead';
            $q    = trim($_GET['q'] ?? '');
            if (empty($q)) { return_json([]); break; }
            $like = "%$q%";
            $results = [];
            if ($type === 'Lead') {
                // Search applicants (main leads)
                $s = $db->prepare("SELECT id, customer_name AS label, loan_id AS sub, overall_status AS status FROM applicants WHERE customer_name LIKE ? OR loan_id LIKE ? OR mobile LIKE ? LIMIT 10");
                $s->execute([$like, $like, $like]);
                foreach ($s->fetchAll(PDO::FETCH_ASSOC) as $r) {
                    $results[] = ['id' => $r['id'], 'label' => $r['label'], 'sub' => $r['sub'] . ' · ' . $r['status'], 'type' => 'Lead'];
                }
            } elseif ($type === 'Banker') {
                $s = $db->prepare("SELECT id, full_name || ' - ' || bank_name AS label, designation AS sub FROM bankers WHERE full_name LIKE ? OR bank_name LIKE ? OR official_email LIKE ? LIMIT 10");
                $s->execute([$like, $like, $like]);
                foreach ($s->fetchAll(PDO::FETCH_ASSOC) as $r) {
                    $results[] = ['id' => $r['id'], 'label' => $r['label'], 'sub' => $r['sub'], 'type' => 'Banker'];
                }
            } elseif ($type === 'Referral') {
                $s = $db->prepare("SELECT id, full_name AS label, referrer_type AS sub FROM referrals WHERE full_name LIKE ? OR mobile LIKE ? OR referral_id LIKE ? LIMIT 10");
                $s->execute([$like, $like, $like]);
                foreach ($s->fetchAll(PDO::FETCH_ASSOC) as $r) {
                    $results[] = ['id' => $r['id'], 'label' => $r['label'], 'sub' => $r['sub'], 'type' => 'Referral'];
                }
            } elseif ($type === 'Pre-Lead') {
                $s = $db->prepare("SELECT id, name AS label, mobile AS sub FROM pre_leads WHERE name LIKE ? OR mobile LIKE ? LIMIT 10");
                $s->execute([$like, $like]);
                foreach ($s->fetchAll(PDO::FETCH_ASSOC) as $r) {
                    $results[] = ['id' => $r['id'], 'label' => $r['label'], 'sub' => $r['sub'], 'type' => 'Pre-Lead'];
                }
            }
            return_json($results);
            break;

        case 'get_leads':
            $s          = $_GET['search'] ?? '';
            $f_stage    = $_GET['stage'] ?? '';
            $f_priority = $_GET['priority'] ?? '';
            $f_assigned = $_GET['assigned_to'] ?? '';
            $f_type     = $_GET['loan_type'] ?? '';
            $f_source   = $_GET['source'] ?? '';
            $f_date     = $_GET['date_range'] ?? '';
            $f_amount   = $_GET['min_amount'] ?? '';
            
            $sql_l      = "SELECT * FROM leads WHERE 1=1";
            $params_l   = [];
            
            if (($_SESSION['role'] ?? '') !== 'Admin') {
                $sql_l .= " AND assigned_to = ?";
                $params_l[] = $_SESSION['username'] ?? '';
            }
            
            if ($s) { 
                $sql_l .= " AND (lead_name LIKE ? OR company_name LIKE ? OR mobile LIKE ?)"; 
                $params_l = array_merge($params_l, ["%$s%","%$s%","%$s%"]); 
            }
            
            if ($f_stage)    { $sql_l .= " AND stage = ?";       $params_l[] = $f_stage; }
            if ($f_priority) { $sql_l .= " AND priority = ?";    $params_l[] = $f_priority; }
            if ($f_assigned) { $sql_l .= " AND assigned_to = ?"; $params_l[] = $f_assigned; }
            if ($f_type)     { $sql_l .= " AND requirement = ?"; $params_l[] = $f_type; }
            if ($f_source)   { $sql_l .= " AND lead_source = ?"; $params_l[] = $f_source; }
            if ($f_amount)   { $sql_l .= " AND CAST(loan_amount AS NUMERIC) >= ?"; $params_l[] = $f_amount; }
            
            if ($f_date) {
                if ($f_date === 'today') {
                    $sql_l .= " AND date(created_at) = date('now')";
                } elseif ($f_date === 'yesterday') {
                    $sql_l .= " AND date(created_at) = date('now', '-1 day')";
                } elseif ($f_date === 'last7') {
                    $sql_l .= " AND date(created_at) >= date('now', '-7 days')";
                } elseif ($f_date === 'this_month') {
                    $sql_l .= " AND strftime('%Y-%m', created_at) = strftime('%Y-%m', 'now')";
                }
            }
            
            $sql_l .= " ORDER BY created_at DESC";
            $stmt = $db->prepare($sql_l);
            $stmt->execute($params_l);
            return_json($stmt->fetchAll());
            break;

        case 'get_lead_detail':
            $id = (int)($_GET['id'] ?? 0);
            if (!$id) return_json(['error' => 'Missing ID'], 400);
            $lead = $db->query("SELECT * FROM leads WHERE id = $id")->fetch();
            if (!$lead) return_json(['error' => 'Lead not found'], 404);
            return_json($lead);
            break;

        case 'update_lead_stage':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return_json(['error' => 'Invalid Request'], 405);
            $id    = (int)($_POST['id'] ?? 0);
            $stage = trim($_POST['stage'] ?? '');
            $allowed_stages = ['New Lead','Contacted','Interested','Proposal Sent','Negotiation','Won','Lost'];
            if (!$id || !in_array($stage, $allowed_stages)) return_json(['error' => 'Invalid data'], 400);
            $lead = $db->query("SELECT lead_name FROM leads WHERE id = $id")->fetch();
            $lead_name = $lead ? $lead['lead_name'] : "Unknown";
            $db->prepare("UPDATE leads SET stage = ? WHERE id = ?")->execute([$stage, $id]);
            log_activity("Updated lead stage for $lead_name to $stage", "leads.php?edit_lead=$id");
            return_json(['success' => true, 'message' => 'Stage updated']);
            break;

        case 'delete_lead':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return_json(['error' => 'Invalid Request'], 405);
            if (($_SESSION['role'] ?? '') !== 'Admin') return_json(['error' => 'Admin privileges required to delete leads'], 403);
            $id = (int)($_POST['id'] ?? 0);
            if (!$id) return_json(['error' => 'Missing ID'], 400);
            $lead_name = $db->query("SELECT lead_name FROM leads WHERE id=$id")->fetchColumn();
            $db->prepare("DELETE FROM leads WHERE id=?")->execute([$id]);
            log_activity("Deleted lead: $lead_name", "leads.php");
            return_json(['success' => true, 'message' => 'Lead deleted']);
            break;

        case 'dashboard_analytics':
            $today = date('Y-m-d');
            $this_month = date('Y-m');
            $last_6_months = [];
            for ($i = 5; $i >= 0; $i--) {
                $last_6_months[] = date('Y-m', strtotime("-$i months"));
            }

            // 1. PRE-LEADS
            $pl_total    = (int)$db->query("SELECT COUNT(*) FROM pre_leads")->fetchColumn();
            $pl_new      = (int)$db->query("SELECT COUNT(*) FROM pre_leads WHERE (status='Not Contacted' OR status IS NULL OR status='') ")->fetchColumn();
            $pl_followup = (int)$db->query("SELECT COUNT(*) FROM pre_leads WHERE status='Follow Up' OR status='Interested'")->fetchColumn();
            $pl_junk     = (int)$db->query("SELECT COUNT(*) FROM pre_leads WHERE status='Not Interested' OR status='Junk'")->fetchColumn();

            // 2. LEADS
            $leads_total  = (int)$db->query("SELECT COUNT(*) FROM leads")->fetchColumn();
            $leads_hot    = (int)$db->query("SELECT COUNT(*) FROM leads WHERE priority='Hot'")->fetchColumn();
            $leads_month  = (int)$db->query("SELECT COUNT(*) FROM leads WHERE strftime('%Y-%m',created_at)='{$this_month}'")->fetchColumn();
            $leads_stages = $db->query("SELECT stage, COUNT(*) as cnt FROM leads GROUP BY stage")->fetchAll(PDO::FETCH_ASSOC);

            // 3. LOAN APPLICATIONS
            $apps_total     = (int)$db->query("SELECT COUNT(*) FROM applicants")->fetchColumn();
            $apps_active    = (int)$db->query("SELECT COUNT(*) FROM applicants WHERE overall_status NOT IN ('Completed','Rejected')")->fetchColumn();
            $apps_completed = (int)$db->query("SELECT COUNT(*) FROM applicants WHERE overall_status='Completed'")->fetchColumn();
            $apps_rejected  = (int)$db->query("SELECT COUNT(*) FROM applicants WHERE overall_status='Rejected'")->fetchColumn();
            $apps_disbursed = (float)($db->query("SELECT COALESCE(SUM(amount),0) FROM applicant_disbursements WHERE status='Disbursed'")->fetchColumn() ?: 0);
            $loan_types     = $db->query("SELECT loan_type, COUNT(*) as cnt FROM applicants GROUP BY loan_type ORDER BY cnt DESC LIMIT 6")->fetchAll(PDO::FETCH_ASSOC);

            $monthly_apps = [];
            foreach ($last_6_months as $m) {
                $cnt = (int)$db->query("SELECT COUNT(*) FROM applicants WHERE strftime('%Y-%m',created_at)='$m'")->fetchColumn();
                $monthly_apps[] = ['month' => date('M y', strtotime($m . '-01')), 'count' => $cnt];
            }

            // 4. FIELD VISITS
            $fv_total  = (int)$db->query("SELECT COUNT(*) FROM field_visits")->fetchColumn();
            $fv_today  = (int)$db->query("SELECT COUNT(*) FROM field_visits WHERE date(visit_date)='{$today}'")->fetchColumn();
            $fv_month  = (int)$db->query("SELECT COUNT(*) FROM field_visits WHERE strftime('%Y-%m',visit_date)='{$this_month}'")->fetchColumn();
            $fv_weekly = [];
            for ($i = 6; $i >= 0; $i--) {
                $d   = date('Y-m-d', strtotime("-$i days"));
                $cnt = (int)$db->query("SELECT COUNT(*) FROM field_visits WHERE date(visit_date)='$d'")->fetchColumn();
                $fv_weekly[] = ['day' => date('D', strtotime($d)), 'count' => $cnt];
            }

            // 5. CLIENT VAULT
            $vault_total = (int)$db->query("SELECT COUNT(*) FROM applicants WHERE overall_status='Completed'")->fetchColumn();
            $vault_prime = (int)$db->query("SELECT COUNT(*) FROM applicants WHERE overall_status='Completed' AND created_at < date('now','-6 months')")->fetchColumn();

            // 6. BANKERS
            $bankers_total  = (int)$db->query("SELECT COUNT(*) FROM bankers")->fetchColumn();
            $bankers_active = (int)$db->query("SELECT COUNT(*) FROM bankers WHERE status='Active'")->fetchColumn();
            $bank_assigned  = (int)$db->query("SELECT COUNT(*) FROM applicant_bank_assignments")->fetchColumn();
            $bank_approved  = (int)$db->query("SELECT COUNT(*) FROM applicant_bank_assignments WHERE status IN ('Approved','Sanctioned')")->fetchColumn();
            $bank_wise      = $db->query("SELECT bank_name, COUNT(*) as cnt FROM applicant_bank_assignments GROUP BY bank_name ORDER BY cnt DESC LIMIT 6")->fetchAll(PDO::FETCH_ASSOC);

            // 7. REFERRALS
            $referrals_total = (int)$db->query("SELECT COUNT(*) FROM referrals")->fetchColumn();
            $top_referrals   = $db->query("SELECT full_name as name FROM referrals WHERE status='Active' ORDER BY created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

            // 8. PAYOUTS
            try { $payout_total = (float)($db->query("SELECT COALESCE(SUM(net_payable),0) FROM payout_distributions WHERE status='Paid'")->fetchColumn() ?: 0); } catch (Exception $e) { $payout_total = 0; }
            try { $payout_month = (float)($db->query("SELECT COALESCE(SUM(net_payable),0) FROM payout_distributions WHERE status='Paid' AND strftime('%Y-%m',paid_on)='{$this_month}'")->fetchColumn() ?: 0); } catch (Exception $e) { $payout_month = 0; }

            // 9. STAFF
            $staff_total  = (int)$db->query("SELECT COUNT(*) FROM users WHERE role='Staff'")->fetchColumn();
            $staff_online = (int)$db->query("SELECT COUNT(*) FROM users WHERE role='Staff' AND last_active >= datetime('now','localtime','-2 minutes')")->fetchColumn();
            $staff_perf   = $db->query("SELECT added_by as name, COUNT(*) as cnt FROM applicants WHERE added_by IS NOT NULL AND added_by != '' GROUP BY added_by ORDER BY cnt DESC LIMIT 7")->fetchAll(PDO::FETCH_ASSOC);

            // 10. EMAILS / COMMUNICATIONS
            $emails_today = (int)$db->query("SELECT COUNT(*) FROM communications WHERE date(sent_at)='{$today}'")->fetchColumn();
            $emails_month = (int)$db->query("SELECT COUNT(*) FROM communications WHERE strftime('%Y-%m',sent_at)='{$this_month}'")->fetchColumn();

            // 11. REMINDERS
            $reminders_today   = (int)$db->query("SELECT COUNT(*) FROM reminders WHERE date(remind_at)='{$today}' AND status='Pending'")->fetchColumn();
            $reminders_overdue = (int)$db->query("SELECT COUNT(*) FROM reminders WHERE remind_at < '{$today}' AND status='Pending'")->fetchColumn();

            return_json([
                'pre_leads'    => ['total'=>$pl_total,'new'=>$pl_new,'followup'=>$pl_followup,'junk'=>$pl_junk],
                'leads'        => ['total'=>$leads_total,'hot'=>$leads_hot,'this_month'=>$leads_month,'stages'=>$leads_stages],
                'applications' => ['total'=>$apps_total,'active'=>$apps_active,'completed'=>$apps_completed,'rejected'=>$apps_rejected,'disbursed'=>$apps_disbursed,'loan_types'=>$loan_types,'monthly_growth'=>$monthly_apps],
                'field_visits' => ['total'=>$fv_total,'today'=>$fv_today,'this_month'=>$fv_month,'weekly'=>$fv_weekly],
                'client_vault' => ['total'=>$vault_total,'prime'=>$vault_prime],
                'bankers'      => ['total'=>$bankers_total,'active'=>$bankers_active,'assignments'=>$bank_assigned,'approved'=>$bank_approved,'bank_wise'=>$bank_wise],
                'referrals'    => ['total'=>$referrals_total,'top'=>$top_referrals],
                'payouts'      => ['total'=>$payout_total,'this_month'=>$payout_month],
                'staff'        => ['total'=>$staff_total,'online'=>$staff_online,'performance'=>$staff_perf],
                'emails'       => ['today'=>$emails_today,'this_month'=>$emails_month],
                'reminders'    => ['today'=>$reminders_today,'overdue'=>$reminders_overdue],
            ]);
            break;

        case 'stats':
            $username = $_SESSION['username'] ?? '';
            $isStaff = ($_SESSION['role'] ?? '') === 'Staff';
            $assignedFilter = $isStaff ? " WHERE assigned_to = '$username'" : "";
            $assignedFilterAnd = $isStaff ? " AND assigned_to = '$username'" : "";
            $quotationJoinFilter = $isStaff ? " JOIN clients c ON quotations.client_id = c.id WHERE c.assigned_to = '$username' AND " : " WHERE ";
            $quotationJoinFilterEmpty = $isStaff ? " JOIN clients c ON quotations.client_id = c.id WHERE c.assigned_to = '$username'" : "";
            $commJoinFilter = $isStaff ? " JOIN clients c ON communications.client_id = c.id WHERE c.assigned_to = '$username' AND " : " WHERE ";

            $total_clients = $db->query("SELECT COUNT(*) FROM clients$assignedFilter")->fetchColumn();
            $emails_today = $db->query("SELECT COUNT(*) FROM communications" . $commJoinFilter . "date(sent_at) = date('now', 'localtime')")->fetchColumn();
            $quotes_this_month = $db->query("SELECT COUNT(quotations.id) FROM quotations" . $quotationJoinFilter . "strftime('%Y-%m', quotations.created_at) = strftime('%Y-%m', 'now', 'localtime')")->fetchColumn();
            $pending_followups = $db->query("SELECT COUNT(*) FROM leads WHERE priority = 'Hot' AND stage IN ('New', 'Contacted', 'In Negotiation') $assignedFilterAnd")->fetchColumn();
            $total_quote_value = $db->query("SELECT SUM(quotations.total_amount) FROM quotations" . $quotationJoinFilterEmpty)->fetchColumn() ?: 0;
            $no_quotation_clients = $db->query("SELECT COUNT(*) FROM clients WHERE id NOT IN (SELECT DISTINCT client_id FROM quotations) $assignedFilterAnd")->fetchColumn();
            
            $total_staff = $db->query("SELECT COUNT(*) FROM users WHERE role = 'Staff'")->fetchColumn();
            $online_staff = $db->query("SELECT COUNT(*) FROM users WHERE role = 'Staff' AND last_active >= datetime('now', 'localtime', '-2 minutes')")->fetchColumn();
            $active_staff = $db->query("SELECT COUNT(*) FROM users WHERE is_active = 1")->fetchColumn();
            
            $total_leads = $db->query("SELECT COUNT(*) FROM leads$assignedFilter")->fetchColumn();
            $hot_leads   = $db->query("SELECT COUNT(*) FROM leads WHERE priority='Hot' AND stage NOT IN ('Won','Lost') $assignedFilterAnd")->fetchColumn();
            $total_leads_month = $db->query("SELECT COUNT(*) FROM leads WHERE strftime('%Y-%m', created_at) = strftime('%Y-%m', 'now', 'localtime') $assignedFilterAnd")->fetchColumn();
            $active_loan_files = $db->query("SELECT COUNT(*) FROM applicants WHERE overall_status NOT IN ('Completed', 'Rejected')")->fetchColumn();
            $total_disbursed_amt = $db->query("SELECT SUM(amount) FROM applicant_disbursements WHERE status = 'Disbursed'")->fetchColumn();
            $rejected_files = $db->query("SELECT COUNT(*) FROM applicants WHERE overall_status = 'Rejected'")->fetchColumn();
            
            return_json([
                'total_clients'        => (int)$total_clients,
                'emails_today'         => (int)$emails_today,
                'quotes_this_month'    => (int)$quotes_this_month,
                'pending_followups'    => (int)$pending_followups,
                'total_quote_value'    => (float)$total_quote_value,
                'no_quotation_clients' => (int)$no_quotation_clients,
                'total_staff'          => (int)$total_staff,
                'online_staff'         => (int)$online_staff,
                'active_staff'         => (int)$active_staff,
                'total_leads'          => (int)$total_leads,
                'hot_leads'            => (int)$hot_leads,
                'total_leads_month'    => (int)$total_leads_month,
                'active_loan_files'    => (int)$active_loan_files,
                'total_disbursed_amt'  => (float)$total_disbursed_amt,
                'rejected_files'       => (int)$rejected_files,
            ]);
            break;

        case 'charts_data':
            $username = $_SESSION['username'] ?? '';
            $isStaff = ($_SESSION['role'] ?? '') === 'Staff';
            $assignedFilter = $isStaff ? " WHERE assigned_to = '$username'" : "";
            $assignedFilterAnd = $isStaff ? " AND assigned_to = '$username'" : "";
            $commJoinFilter = $isStaff ? " JOIN clients c ON communications.client_id = c.id WHERE c.assigned_to = '$username' AND " : " WHERE ";
            $quoteJoinFilterEmpty = $isStaff ? " JOIN clients c ON quotations.client_id = c.id WHERE c.assigned_to = '$username'" : "";
            $topClientsFilter = $isStaff ? " WHERE c.assigned_to = '$username' " : "";

            $growth_query = $db->query("SELECT strftime('%Y-%m', created_at) as month, COUNT(*) as count FROM clients $assignedFilter GROUP BY month ORDER BY month ASC");
            $growth_data = [];
            while ($row = $growth_query->fetch()) {
                $dateObj = DateTime::createFromFormat('!Y-m', $row['month']);
                $monthName = $dateObj ? $dateObj->format('M Y') : $row['month'];
                $growth_data[] = ['label' => $monthName, 'value' => (int)$row['count']];
            }
            
            $pitch_count = $db->query("SELECT COUNT(DISTINCT communications.client_id) FROM communications $commJoinFilter communications.type = 'Pitch'")->fetchColumn();
            $ppt_count = $db->query("SELECT COUNT(DISTINCT communications.client_id) FROM communications $commJoinFilter communications.type = 'PPT'")->fetchColumn();
            $mail_count = $db->query("SELECT COUNT(DISTINCT communications.client_id) FROM communications $commJoinFilter communications.type = 'Custom Mail'")->fetchColumn();
            $quote_count = $db->query("SELECT COUNT(DISTINCT quotations.client_id) FROM quotations $quoteJoinFilterEmpty")->fetchColumn();
            $closed_count = $db->query("SELECT COUNT(*) FROM clients WHERE overall_status = 'Closed Won' $assignedFilterAnd")->fetchColumn();
            
            $funnel = [
                ['stage' => 'Pitch Sent', 'count' => (int)$pitch_count],
                ['stage' => 'PPT Shared', 'count' => (int)$ppt_count],
                ['stage' => 'Custom Mail', 'count' => (int)$mail_count],
                ['stage' => 'Quotation Sent', 'count' => (int)$quote_count],
                ['stage' => 'Deals Won', 'count' => (int)$closed_count]
            ];
            
            $top_clients_query = $db->query("
                SELECT c.company_name, SUM(q.total_amount) as total_val 
                FROM clients c 
                JOIN quotations q ON c.id = q.client_id 
                $topClientsFilter
                GROUP BY c.id 
                ORDER BY total_val DESC 
                LIMIT 5
            ");
            $top_clients = [];
            while ($row = $top_clients_query->fetch()) {
                $top_clients[] = ['name' => $row['company_name'], 'value' => (float)$row['total_val']];
            }
            
            $activity_summary = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime("-$i days"));
                $day_name = date('D', strtotime("-$i days"));
                $act_count = $db->query("SELECT COUNT(*) FROM activities WHERE date(created_at) = '$date'")->fetchColumn();
                $activity_summary[] = ['label' => $day_name, 'value' => (int)$act_count];
            }

            return_json([
                'growth' => $growth_data,
                'funnel' => $funnel,
                'top_clients' => $top_clients,
                'activity_weekly' => $activity_summary
            ]);
            break;

        case 'get_activity_logs':
            if (($_SESSION['role'] ?? '') !== 'Admin') return_json(['error' => 'Admin privileges required'], 403);
            $activities = $db->query("SELECT description, created_at, action_link FROM activities ORDER BY id DESC LIMIT 200")->fetchAll();
            foreach ($activities as &$act) {
                $act['created_at_formatted'] = date('d M Y, h:i A', strtotime($act['created_at'] . ' UTC'));
            }
            return_json($activities);
            break;

        case 'recent_activities':
            $user = $_GET['user'] ?? '';
            if (($_SESSION['role'] ?? '') === 'Staff') {
                $user = $_SESSION['username'] ?? '';
            }
            $days = (int)($_GET['days'] ?? 0);
            
            $sql = "SELECT description, created_at, action_link FROM activities WHERE 1=1";
            $params = [];
            if ($user) {
                $sql .= " AND (username = ? OR target_user = ? OR description LIKE ?)";
                $params[] = $user;
                $params[] = $user;
                $params[] = "%[$user]%";
            }
            if ($days > 0) {
                $sql .= " AND created_at >= date('now', '-$days days')";
            }
            $sql .= " ORDER BY id DESC LIMIT 50";
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $activities = $stmt->fetchAll();
            
            foreach ($activities as &$act) {
                $timestamp = strtotime($act['created_at'] . ' UTC');
                $diff = time() - $timestamp;
                
                if ($diff < 60) {
                    $act['time_formatted'] = 'Just now';
                } elseif ($diff < 3600) {
                    $act['time_formatted'] = round($diff / 60) . ' mins ago';
                } elseif ($diff < 86400) {
                    $act['time_formatted'] = round($diff / 3600) . ' hrs ago';
                } else {
                    if (date('Y-m-d', $timestamp) == date('Y-m-d')) {
                        $act['time_formatted'] = 'Today ' . date('h:i A', $timestamp);
                    } else {
                        $act['time_formatted'] = date('d M h:i A', $timestamp);
                    }
                }
            }
            return_json($activities);
            break;

        case 'search_clients':
            $query_term = isset($_GET['query']) ? trim($_GET['query']) : '';
            $status = isset($_GET['status']) ? trim($_GET['status']) : '';
            $priority = isset($_GET['priority']) ? trim($_GET['priority']) : '';
            $city = isset($_GET['city']) ? trim($_GET['city']) : '';
            $added_by = isset($_GET['added_by']) ? trim($_GET['added_by']) : '';
            $date_start = isset($_GET['date_start']) ? trim($_GET['date_start']) : '';
            $date_end = isset($_GET['date_end']) ? trim($_GET['date_end']) : '';
            
            $sql = "SELECT * FROM clients WHERE 1=1";
            $params = [];
            
            if (($_SESSION['role'] ?? '') !== 'Admin') {
                $sql .= " AND (assigned_to = :assigned_to OR added_by = :assigned_to)";
                $params[':assigned_to'] = $_SESSION['username'] ?? '';
            }
            
            if ($query_term !== '') {
                $sql .= " AND (company_name LIKE :q OR contact_name LIKE :q OR email LIKE :q OR city LIKE :q OR gstin LIKE :q)";
                $params[':q'] = "%$query_term%";
            }
            if ($status !== '') {
                $sql .= " AND overall_status = :status";
                $params[':status'] = $status;
            }
            if ($priority !== '') {
                $sql .= " AND priority = :priority";
                $params[':priority'] = $priority;
            }
            if ($city !== '') {
                $sql .= " AND city = :city";
                $params[':city'] = $city;
            }
            if ($added_by !== '') {
                $sql .= " AND added_by = :added_by";
                $params[':added_by'] = $added_by;
            }
            if ($date_start !== '') {
                $sql .= " AND date(created_at) >= :date_start";
                $params[':date_start'] = $date_start;
            }
            if ($date_end !== '') {
                $sql .= " AND date(created_at) <= :date_end";
                $params[':date_end'] = $date_end;
            }
            
            $sql .= " ORDER BY id DESC";
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $clients = $stmt->fetchAll();
            
            foreach ($clients as &$c) {
                $c['pitch_sent'] = $db->query("SELECT sent_at FROM communications WHERE client_id = {$c['id']} AND (type = 'Pitch' OR type = 'Call') ORDER BY id DESC LIMIT 1")->fetchColumn() ?: null;
                $c['ppt_sent'] = $db->query("SELECT sent_at FROM communications WHERE client_id = {$c['id']} AND type = 'PPT' ORDER BY id DESC LIMIT 1")->fetchColumn() ?: null;
                $c['mail_sent'] = $db->query("SELECT sent_at FROM communications WHERE client_id = {$c['id']} AND type = 'Custom Mail' ORDER BY id DESC LIMIT 1")->fetchColumn() ?: null;
                $c['quotation_sent'] = $db->query("SELECT created_at FROM quotations WHERE client_id = {$c['id']} ORDER BY id DESC LIMIT 1")->fetchColumn() ?: null;
                $c['quotations'] = $db->query("SELECT * FROM quotations WHERE client_id = {$c['id']} ORDER BY id DESC")->fetchAll();
            }
            
            return_json($clients);
            break;

        case 'client_details':
            $id = (int)$_GET['id'];
            $client = $db->prepare("SELECT * FROM clients WHERE id = ?");
            $client->execute([$id]);
            $c = $client->fetch();
            
            if (!$c) {
                return_json(['error' => 'Client not found'], 404);
            }
            
            if (($_SESSION['role'] ?? '') !== 'Admin' && $c['assigned_to'] !== ($_SESSION['username'] ?? '') && $c['added_by'] !== ($_SESSION['username'] ?? '')) {
                return_json(['error' => 'Unauthorized Access to this client\'s details.'], 403);
            }
            
            $comms = $db->prepare("SELECT * FROM communications WHERE client_id = ? ORDER BY id DESC");
            $comms->execute([$id]);
            $c['communications_logs'] = $comms->fetchAll();
            
            $quotes = $db->prepare("SELECT * FROM quotations WHERE client_id = ? ORDER BY id DESC");
            $quotes->execute([$id]);
            $c['quotations'] = $quotes->fetchAll();
            
            $c['pitch_sent'] = $db->query("SELECT sent_at FROM communications WHERE client_id = $id AND (type = 'Pitch' OR type = 'Call') ORDER BY id DESC LIMIT 1")->fetchColumn() ?: null;
            $c['ppt_sent'] = $db->query("SELECT sent_at FROM communications WHERE client_id = $id AND type = 'PPT' ORDER BY id DESC LIMIT 1")->fetchColumn() ?: null;
            $c['mail_sent'] = $db->query("SELECT sent_at FROM communications WHERE client_id = $id AND type = 'Custom Mail' ORDER BY id DESC LIMIT 1")->fetchColumn() ?: null;
            $c['quotation_sent'] = $db->query("SELECT created_at FROM quotations WHERE client_id = $id ORDER BY id DESC LIMIT 1")->fetchColumn() ?: null;
            
            return_json($c);
            break;

        case 'get_unique_filters':
            $cities = $db->query("SELECT DISTINCT city FROM clients WHERE city != '' ORDER BY city")->fetchAll(PDO::FETCH_COLUMN);
            $states = $db->query("SELECT DISTINCT state FROM clients WHERE state != '' ORDER BY state")->fetchAll(PDO::FETCH_COLUMN);
            $added_by = $db->query("SELECT username FROM users ORDER BY username")->fetchAll(PDO::FETCH_COLUMN);
            
            return_json([
                'cities' => $cities,
                'states' => $states,
                'added_by' => $added_by
            ]);
            break;

        case 'add_client':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                return_json(['error' => 'Invalid Request Method'], 405);
            }
            
            $company_name = trim($_POST['company_name'] ?? '');
            $business_type = trim($_POST['business_type'] ?? '');
            $industry_sector = trim($_POST['industry_sector'] ?? '');
            $gstin = strtoupper(trim($_POST['gstin'] ?? ''));
            $pan = strtoupper(trim($_POST['pan'] ?? ''));
            $website = trim($_POST['website'] ?? '');
            $turnover = trim($_POST['turnover'] ?? '');
            $employees = !empty($_POST['employees']) ? (int)$_POST['employees'] : null;
            
            $contact_name = trim($_POST['contact_name'] ?? '');
            $designation = trim($_POST['designation'] ?? '');
            $mobile = trim($_POST['mobile'] ?? '');
            $whatsapp = trim($_POST['whatsapp'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $alternate_email = trim($_POST['alternate_email'] ?? '');
            $linkedin = trim($_POST['linkedin'] ?? '');
            
            $address_line1 = trim($_POST['address_line1'] ?? '');
            $address_line2 = trim($_POST['address_line2'] ?? '');
            $city = trim($_POST['city'] ?? '');
            $state = trim($_POST['state'] ?? '');
            $pincode = trim($_POST['pincode'] ?? '');
            $country = trim($_POST['country'] ?? 'India');
            
            $bank_name = trim($_POST['bank_name'] ?? '');
            $account_number = trim($_POST['account_number'] ?? '');
            $ifsc_code = strtoupper(trim($_POST['ifsc_code'] ?? ''));
            
            $lead_source = trim($_POST['lead_source'] ?? '');
            $priority = trim($_POST['priority'] ?? 'Warm');
            $added_by = trim($_POST['added_by'] ?? 'System');
            $remarks = trim($_POST['remarks'] ?? '');
            $source_lead_id = isset($_POST['source_lead_id']) ? (int)$_POST['source_lead_id'] : null;

            $assigned_to = trim($_POST['assigned_to'] ?? '');
            if (empty($assigned_to) && ($_SESSION['role'] ?? '') === 'Staff') {
                $assigned_to = $_SESSION['username'] ?? '';
            }
            
            if (empty($company_name) || empty($business_type) || empty($contact_name) || empty($mobile) || empty($email) || empty($address_line1) || empty($city) || empty($state) || empty($pincode) || empty($country)) {
                return_json(['error' => 'Please fill in all mandatory fields.'], 400);
            }
            
            $chk = $db->prepare("SELECT COUNT(*) FROM clients WHERE company_name = ?");
            $chk->execute([$company_name]);
            if ($chk->fetchColumn() > 0) {
                return_json(['error' => "A client company named '{$company_name}' already exists."], 400);
            }
            
            $sql = "INSERT INTO clients (
                company_name, business_type, industry_sector, gstin, pan, website, turnover, employees,
                contact_name, designation, mobile, whatsapp, email, alternate_email, linkedin,
                address_line1, address_line2, city, state, pincode, country,
                bank_name, account_number, ifsc_code,
                lead_source, priority, added_by, remarks, overall_status, assigned_to
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'New', ?)";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([
                $company_name, $business_type, $industry_sector, $gstin, $pan, $website, $turnover, $employees,
                $contact_name, $designation, $mobile, $whatsapp, $email, $alternate_email, $linkedin,
                $address_line1, $address_line2, $city, $state, $pincode, $country,
                $bank_name, $account_number, $ifsc_code,
                $lead_source, $priority, $added_by, $remarks, $assigned_to
            ]);
            
            if ($source_lead_id) {
                $db->query("UPDATE leads SET stage='Won' WHERE id=$source_lead_id");
            }
            
            $new_id = $db->lastInsertId();
            $action_desc = "{$company_name} added by {$added_by} — Today " . date('h:i A');
            log_activity($action_desc, "search_track.php?view_client=$new_id");
            
            return_json(['success' => true, 'message' => 'Client profile locked & registered successfully!']);
            break;

        case 'log_call':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                return_json(['error' => 'Invalid Request Method'], 405);
            }
            
            $client_id = (int)$_POST['client_id'];
            $outcome = trim($_POST['outcome'] ?? '');
            $remarks = trim($_POST['remarks'] ?? '');
            $sent_by = $_SESSION['username'] ?? 'System';
            
            if (empty($client_id) || empty($outcome) || empty($remarks)) {
                return_json(['error' => 'Outcome and remarks are required fields.'], 400);
            }
            
            $client_query = $db->prepare("SELECT company_name, assigned_to FROM clients WHERE id = ?");
            $client_query->execute([$client_id]);
            $client = $client_query->fetch();
            
            if (!$client) {
                return_json(['error' => 'Client not found.'], 404);
            }
            
            $stmt = $db->prepare("INSERT INTO communications (client_id, type, subject, body, sent_by) VALUES (?, 'Call', ?, ?, ?)");
            $stmt->execute([$client_id, "Call Log: " . $outcome, $remarks, $sent_by]);
            
            // Update overall_status based on outcome
            $new_status = 'Pitched'; // default
            if ($outcome === 'Connected - Not Interested' || $outcome === 'No Answer' || $outcome === 'Number Busy / Invalid') {
                $new_status = 'Not Interested';
            } elseif ($outcome === 'Connected - Interested') {
                $new_status = 'Interested';
            } elseif ($outcome === 'Connected - Follow-up requested') {
                $new_status = 'Follow-up';
            }
            
            $update_stmt = $db->prepare("UPDATE clients SET overall_status = ? WHERE id = ?");
            $update_stmt->execute([$new_status, $client_id]);
            
            // Log to dashboard activities
            $act_desc = "Call made to {$client['company_name']} by {$sent_by} — Response: {$outcome}";
            log_activity($act_desc, "search_track.php?view_client=$client_id", $client['assigned_to']);
            
            return_json(['success' => true, 'message' => 'Call logged successfully!']);
            break;

        case 'log_ppt_shared':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                return_json(['error' => 'Invalid Request Method'], 405);
            }
            
            $client_id = (int)$_POST['client_id'];
            $remarks = trim($_POST['remarks'] ?? 'PPT shared manually via external platform (e.g. WhatsApp/Meeting).');
            $sent_by = $_SESSION['username'] ?? 'System';
            
            $client_query = $db->prepare("SELECT company_name, assigned_to FROM clients WHERE id = ?");
            $client_query->execute([$client_id]);
            $client = $client_query->fetch();
            
            if (!$client) {
                return_json(['error' => 'Client not found.'], 404);
            }
            
            $stmt = $db->prepare("INSERT INTO communications (client_id, type, subject, body, sent_by) VALUES (?, 'PPT', ?, ?, ?)");
            $stmt->execute([$client_id, "Manual Log: PPT Shared", $remarks, $sent_by]);
            
            $act_desc = "PPT manually marked as shared for {$client['company_name']} by {$sent_by}";
            log_activity($act_desc, "search_track.php?view_client=$client_id", $client['assigned_to']);
            
            return_json(['success' => true, 'message' => 'PPT marked as shared successfully!']);
            break;

        case 'send_email':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                return_json(['error' => 'Invalid Request Method'], 405);
            }
            
            $client_id = (int)$_POST['client_id'];
            $cc = trim($_POST['cc'] ?? '');
            $subject = trim($_POST['subject'] ?? '');
            $body = trim($_POST['body'] ?? '');
            $type = trim($_POST['type'] ?? 'Custom Mail');
            $sent_by = trim($_POST['sent_by'] ?? 'System');
            
            if (empty($client_id) || empty($subject) || empty($body)) {
                return_json(['error' => 'Client, Subject, and Body are required fields.'], 400);
            }
            
            $client_query = $db->prepare("SELECT company_name, email, overall_status, assigned_to FROM clients WHERE id = ?");
            $client_query->execute([$client_id]);
            $client = $client_query->fetch();
            
            if (!$client) {
                return_json(['error' => 'Recipient Client not found.'], 404);
            }
            
            $attachment_name = null;
            $template_id = $_POST['template_id'] ?? null;
            $ppt_id = $_POST['ppt_id'] ?? null;

            if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
                $file_tmp = $_FILES['attachment']['tmp_name'];
                $orig_name = basename($_FILES['attachment']['name']);
                $file_ext = pathinfo($orig_name, PATHINFO_EXTENSION);
                $safe_name = time() . '_' . preg_replace("/[^a-zA-Z0-9\._-]/", "", $orig_name);
                
                if (move_uploaded_file($file_tmp, $uploads_dir . '/' . $safe_name)) {
                    $attachment_name = $safe_name;
                }
            } elseif ($ppt_id) {
                $ppt_res = $db->query("SELECT filename FROM presentations WHERE id = " . (int)$ppt_id)->fetchColumn();
                if ($ppt_res) {
                    $attachment_name = $ppt_res;
                }
            } elseif ($template_id) {
                $tmpl = $db->prepare("SELECT attachment_name FROM email_templates WHERE id = ?");
                $tmpl->execute([$template_id]);
                $tmpl_res = $tmpl->fetch();
                if ($tmpl_res && !empty($tmpl_res['attachment_name'])) {
                    $attachment_name = $tmpl_res['attachment_name'];
                }
            }
            $profile_stmt = $db->query("SELECT smtp_host, smtp_port, smtp_username, smtp_password, smtp_encryption FROM company_profile LIMIT 1");
            $profile = $profile_stmt->fetch();
            
            if (!$profile || empty($profile['smtp_host']) || empty($profile['smtp_username'])) {
                return_json(['error' => 'SMTP is not configured in CRM Settings. Please ask Admin to configure it.'], 400);
            }

            require_once __DIR__ . '/libs/PHPMailer/Exception.php';
            require_once __DIR__ . '/libs/PHPMailer/PHPMailer.php';
            require_once __DIR__ . '/libs/PHPMailer/SMTP.php';

            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host       = $profile['smtp_host'];
                $mail->SMTPAuth   = true;
                $mail->Username   = $profile['smtp_username'];
                $mail->Password   = $profile['smtp_password'];
                if (strtolower($profile['smtp_encryption']) === 'tls') {
                    $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                } elseif (strtolower($profile['smtp_encryption']) === 'ssl') {
                    $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
                }
                $mail->Port       = (int)$profile['smtp_port'];

                // Recipients
                $mail->setFrom($profile['smtp_username'], 'BFS Financial Services');
                $mail->addAddress($client['email'], $client['company_name']);
                if (!empty($cc)) {
                    $mail->addCC($cc);
                }

                // Attachments
                if ($attachment_name) {
                    $mail->addAttachment($uploads_dir . '/' . $attachment_name);
                }

                // Content
                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body    = $body;
                $mail->AltBody = strip_tags($body);

                $mail->send();
                
                $stmt = $db->prepare("INSERT INTO communications (client_id, type, subject, body, cc, attachment_name, sent_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$client_id, $type, $subject, $body, $cc, $attachment_name, $sent_by]);
                
                $new_status = $client['overall_status'];
                if ($client['overall_status'] === 'New') {
                    $new_status = 'Contacted';
                    $upd = $db->prepare("UPDATE clients SET overall_status = 'Contacted' WHERE id = ?");
                    $upd->execute([$client_id]);
                }
                
                $act_desc = "{$type} email sent to {$client['company_name']} by {$sent_by} — " . date('h:i A');
                log_activity($act_desc, "search_track.php?view_client=$client_id", $client['assigned_to']);
                
                return_json([
                    'success' => true, 
                    'message' => 'Email sent successfully!', 
                    'new_status' => $new_status,
                    'attachment' => $attachment_name
                ]);
            } catch (Exception $e) {
                return_json(['error' => "Email sending failed: {$mail->ErrorInfo}"], 500);
            }
            break;

        case 'save_quotation':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                return_json(['error' => 'Invalid Request Method'], 405);
            }
            
            $client_id = (int)$_POST['client_id'];
            $subtotal = (float)$_POST['subtotal'];
            $gst_amount = (float)$_POST['gst_amount'];
            $total_amount = (float)$_POST['total_amount'];
            $items_json = $_POST['items_json'];
            
            if (empty($client_id) || empty($items_json)) {
                return_json(['error' => 'Invalid Client or Quotation Line items.'], 400);
            }
            
            $c_name = $db->query("SELECT company_name FROM clients WHERE id = $client_id")->fetchColumn();
            if (!$c_name) {
                return_json(['error' => 'Client not found'], 404);
            }
            
            $max_id = $db->query("SELECT MAX(id) FROM quotations")->fetchColumn() ?: 0;
            $next_num = $max_id + 1;
            $quotation_number = "Q" . str_pad($next_num, 3, '0', STR_PAD_LEFT);
            
            $stmt = $db->prepare("INSERT INTO quotations (client_id, quotation_number, status, subtotal, gst_amount, total_amount, items_json) VALUES (?, ?, 'Pending', ?, ?, ?, ?)");
            $stmt->execute([$client_id, $quotation_number, $subtotal, $gst_amount, $total_amount, $items_json]);
            
            $current_status = $db->query("SELECT overall_status FROM clients WHERE id = $client_id")->fetchColumn();
            if (in_array($current_status, ['New', 'Contacted'])) {
                $db->query("UPDATE clients SET overall_status = 'In Negotiation' WHERE id = $client_id");
            }
            
            $act_desc = "Quotation {$quotation_number} (Rs. " . number_format($total_amount, 2, '.', ',') . ") drafted for {$c_name}";
            log_activity($act_desc, "search_track.php?view_client=$client_id");
            
            $quote_id = $db->lastInsertId();
            return_json(['success' => true, 'quotation_number' => $quotation_number, 'quotation_id' => $quote_id, 'message' => "Quotation {$quotation_number} saved successfully!"]);
            break;

        case 'send_quotation_email':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                return_json(['error' => 'Invalid Request Method'], 405);
            }
            
            $client_id = (int)($_POST['client_id'] ?? 0);
            $quote_id = (int)($_POST['quote_id'] ?? 0);
            $subject = trim($_POST['subject'] ?? '');
            $body = trim($_POST['body'] ?? '');
            
            if (!$client_id || !$quote_id || empty($subject) || empty($body)) {
                return_json(['error' => 'Missing required fields.'], 400);
            }
            
            $client_query = $db->prepare("SELECT company_name, email, assigned_to FROM clients WHERE id = ?");
            $client_query->execute([$client_id]);
            $client = $client_query->fetch();
            if (!$client) {
                return_json(['error' => 'Client not found.'], 404);
            }
            
            $attachment_name = null;
            if (isset($_FILES['pdf_blob']) && $_FILES['pdf_blob']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = __DIR__ . '/uploads/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                
                $filename = 'Quotation_' . $quote_id . '_' . time() . '.pdf';
                if (move_uploaded_file($_FILES['pdf_blob']['tmp_name'], $upload_dir . $filename)) {
                    $attachment_name = $filename;
                }
            }
            
            if (!$attachment_name) {
                return_json(['error' => 'Failed to receive PDF document.'], 400);
            }
            
            $sent_by = $_SESSION['username'] ?? 'System';
            
            // Send via PHPMailer
            require_once __DIR__ . '/libs/PHPMailer/Exception.php';
            require_once __DIR__ . '/libs/PHPMailer/PHPMailer.php';
            require_once __DIR__ . '/libs/PHPMailer/SMTP.php';
            
            $company_profile = $db->query("SELECT * FROM company_profile LIMIT 1")->fetch();
            
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            try {
                if (!empty($company_profile['smtp_host'])) {
                    $mail->isSMTP();
                    $mail->Host       = $company_profile['smtp_host'];
                    $mail->SMTPAuth   = true;
                    $mail->Username   = $company_profile['smtp_username'];
                    $mail->Password   = $company_profile['smtp_password'];
                    $mail->SMTPSecure = $company_profile['smtp_encryption'] === 'ssl' ? PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS : PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = $company_profile['smtp_port'];
                }
                
                $mail->setFrom($company_profile['email'] ?? 'admin@BFS Financial Services.com', $company_profile['company_name'] ?? 'BFS Financial Services');
                $mail->addAddress($client['email'], $client['company_name']);
                
                $mail->addAttachment($upload_dir . $attachment_name, $attachment_name);
                
                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body    = $body;
                
                $mail->send();
                
                // Log communication
                $stmt = $db->prepare("INSERT INTO communications (client_id, type, subject, body, cc, attachment_name, sent_by) VALUES (?, 'Quotation', ?, ?, '', ?, ?)");
                $stmt->execute([$client_id, $subject, $body, $attachment_name, $sent_by]);
                
                $target_user = $client['assigned_to'] ?: null;
                log_activity("Quotation emailed to {$client['company_name']} by {$sent_by}", "search_track.php?view_client=$client_id", $target_user);
                
                return_json(['success' => true, 'message' => 'Quotation successfully saved and emailed!']);
            } catch (Exception $e) {
                return_json(['error' => "Quotation saved but email failed to send. Mailer Error: {$mail->ErrorInfo}"], 500);
            }
            break;

        case 'update_quotation_status':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                return_json(['error' => 'Invalid Request Method'], 405);
            }
            
            $quote_id = (int)$_POST['quote_id'];
            $status = $_POST['status'];
            
            if (!in_array($status, ['Pending', 'Approved', 'Rejected'])) {
                return_json(['error' => 'Invalid status option.'], 400);
            }
            
            $q_info = $db->query("SELECT q.quotation_number, q.client_id, c.company_name, c.assigned_to, c.added_by FROM quotations q JOIN clients c ON q.client_id = c.id WHERE q.id = $quote_id")->fetch();
            if (!$q_info) {
                return_json(['error' => 'Quotation not found.'], 404);
            }
            
            if (($_SESSION['role'] ?? '') !== 'Admin' && $q_info['assigned_to'] !== ($_SESSION['username'] ?? '') && $q_info['added_by'] !== ($_SESSION['username'] ?? '')) {
                return_json(['error' => 'Unauthorized to modify this quotation status.'], 403);
            }
            
            $stmt = $db->prepare("UPDATE quotations SET status = ? WHERE id = ?");
            $stmt->execute([$status, $quote_id]);
            
            $client_status = 'In Negotiation';
            if ($status === 'Approved') {
                $client_status = 'Closed Won';
            } elseif ($status === 'Rejected') {
                $other_approved = $db->query("SELECT COUNT(*) FROM quotations WHERE client_id = {$q_info['client_id']} AND status = 'Approved'")->fetchColumn();
                $client_status = ($other_approved > 0) ? 'Closed Won' : 'Closed Lost';
            }
            
            $db->query("UPDATE clients SET overall_status = '$client_status' WHERE id = {$q_info['client_id']}");
            
            $act_desc = "Quotation {$q_info['quotation_number']} updated to {$status} for {$q_info['company_name']}";
            log_activity($act_desc, "search_track.php?view_client={$q_info['client_id']}");
            
            return_json(['success' => true, 'message' => 'Status updated successfully', 'client_status' => $client_status]);
            break;

        case 'quotation_list':
            $search = isset($_GET['search']) ? trim($_GET['search']) : '';
            $status = isset($_GET['status']) ? trim($_GET['status']) : '';
            
            $sql = "SELECT q.*, c.company_name, c.email as client_email, c.city, c.state FROM quotations q JOIN clients c ON q.client_id = c.id WHERE 1=1";
            $params = [];
            
            if (($_SESSION['role'] ?? '') !== 'Admin') {
                $sql .= " AND (c.assigned_to = :assigned_to OR c.added_by = :assigned_to)";
                $params[':assigned_to'] = $_SESSION['username'] ?? '';
            }
            
            if ($search !== '') {
                $sql .= " AND (c.company_name LIKE :s OR q.quotation_number LIKE :s)";
                $params[':s'] = "%$search%";
            }
            if ($status !== '') {
                $sql .= " AND q.status = :status";
                $params[':status'] = $status;
            }
            
            $sql .= " ORDER BY q.id DESC";
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $quotes = $stmt->fetchAll();
            
            $summary = [
                'total_count' => count($quotes),
                'pending_value' => 0,
                'approved_value' => 0,
                'rejected_value' => 0,
                'total_value' => 0
            ];
            
            foreach ($quotes as $q) {
                $amt = (float)$q['total_amount'];
                $summary['total_value'] += $amt;
                
                if ($q['status'] === 'Pending') $summary['pending_value'] += $amt;
                elseif ($q['status'] === 'Approved') $summary['approved_value'] += $amt;
                elseif ($q['status'] === 'Rejected') $summary['rejected_value'] += $amt;
            }
            
            return_json([
                'quotations' => $quotes,
                'summary' => $summary
            ]);
            break;

        case 'save_settings':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                return_json(['error' => 'Invalid Request Method'], 405);
            }
            
            // Ensure advanced columns exist
            try { $db->exec("ALTER TABLE company_profile ADD COLUMN global_tds REAL DEFAULT 5.0"); } catch (Exception $e) {}
            try { $db->exec("ALTER TABLE company_profile ADD COLUMN lead_auto_assign TEXT DEFAULT 'Round Robin'"); } catch (Exception $e) {}
            try { $db->exec("ALTER TABLE company_profile ADD COLUMN loan_products TEXT"); } catch (Exception $e) {}
            try { $db->exec("ALTER TABLE company_profile ADD COLUMN lead_sources TEXT"); } catch (Exception $e) {}

            $company_name = trim($_POST['company_name'] ?? '');
            $address_line1 = trim($_POST['address_line1'] ?? '');
            $address_line2 = trim($_POST['address_line2'] ?? '');
            $city = trim($_POST['city'] ?? '');
            $state = trim($_POST['state'] ?? '');
            $pincode = trim($_POST['pincode'] ?? '');
            $country = trim($_POST['country'] ?? 'India');
            $gstin = strtoupper(trim($_POST['gstin'] ?? ''));
            $email = trim($_POST['email'] ?? '');
            $mobile = trim($_POST['mobile'] ?? '');
            $contact_person = trim($_POST['contact_person'] ?? '');
            $bank_name = trim($_POST['bank_name'] ?? '');
            $account_number = trim($_POST['account_number'] ?? '');
            $ifsc_code = strtoupper(trim($_POST['ifsc_code'] ?? ''));
            
            $smtp_host = trim($_POST['smtp_host'] ?? '');
            $smtp_port = trim($_POST['smtp_port'] ?? '');
            $smtp_username = trim($_POST['smtp_username'] ?? '');
            $smtp_password = trim($_POST['smtp_password'] ?? '');
            $smtp_encryption = trim($_POST['smtp_encryption'] ?? '');
            
            // Advanced Configs
            $global_tds = isset($_POST['global_tds']) ? (float)$_POST['global_tds'] : null;
            $lead_auto_assign = $_POST['lead_auto_assign'] ?? null;
            $loan_products = $_POST['loan_products'] ?? null;
            $lead_sources = $_POST['lead_sources'] ?? null;
            
            // We skip validation of core fields if we are ONLY saving Advanced Configs (via a partial form submission)
            $is_partial = isset($_POST['is_partial_config']);
            
            if (!$is_partial) {
                if (empty($company_name) || empty($address_line1) || empty($city) || empty($state) || empty($pincode) || empty($gstin) || empty($email) || empty($contact_person)) {
                    return_json(['error' => 'All fields except Address Line 2, Mobile, Bank Name, Account Number, IFSC, and SMTP are required.'], 400);
                }
                
                $stmt = $db->prepare("UPDATE company_profile SET company_name = ?, address_line1 = ?, address_line2 = ?, city = ?, state = ?, pincode = ?, country = ?, gstin = ?, email = ?, mobile = ?, contact_person = ?, bank_name = ?, account_number = ?, ifsc_code = ?, smtp_host = ?, smtp_port = ?, smtp_username = ?, smtp_password = ?, smtp_encryption = ?");
                $stmt->execute([$company_name, $address_line1, $address_line2, $city, $state, $pincode, $country, $gstin, $email, $mobile, $contact_person, $bank_name, $account_number, $ifsc_code, $smtp_host, $smtp_port, $smtp_username, $smtp_password, $smtp_encryption]);
            }
            
            // Save advanced configs if they were provided
            if ($global_tds !== null) {
                $db->prepare("UPDATE company_profile SET global_tds = ?")->execute([$global_tds]);
            }
            if ($lead_auto_assign !== null) {
                $db->prepare("UPDATE company_profile SET lead_auto_assign = ?")->execute([$lead_auto_assign]);
            }
            if ($loan_products !== null) {
                $db->prepare("UPDATE company_profile SET loan_products = ?")->execute([$loan_products]);
            }
            if ($lead_sources !== null) {
                $db->prepare("UPDATE company_profile SET lead_sources = ?")->execute([$lead_sources]);
            }
            
            $act_desc = "System settings updated by " . ($_SESSION['username'] ?? 'Admin') . " — Today " . date('h:i A');
            log_activity($act_desc);
            
            return_json(['success' => true, 'message' => 'Settings updated successfully!']);
            break;

        case 'get_staff_360':
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
            $username = trim($_GET['username'] ?? '');
            if (!$username) return_json(['error' => 'Missing username'], 400);

            // Basic Profile
            $stmt = $db->prepare("SELECT u.name as full_name, u.username, u.role, u.current_status, e.photo_path FROM users u LEFT JOIN employees e ON u.id = e.user_id WHERE u.username = ?");
            $stmt->execute([$username]);
            $profile = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Get live tracking data if available
            $track_stmt = $db->prepare("SELECT battery as current_battery, lat as current_lat, lon as current_lon, created_at as last_ping FROM staff_location_logs WHERE username = ? ORDER BY id DESC LIMIT 1");
            $track_stmt->execute([$username]);
            if ($track = $track_stmt->fetch(PDO::FETCH_ASSOC)) {
                $profile = array_merge($profile, $track);
            }

            if (!$profile) return_json(['error' => 'Staff not found'], 404);

            $today = date('Y-m-d');

            // Today's Attendance
            $att_stmt = $db->prepare("SELECT * FROM staff_attendance WHERE username = ? AND att_date = ? ORDER BY id DESC LIMIT 1");
            $att_stmt->execute([$username, $today]);
            $today_att = $att_stmt->fetch(PDO::FETCH_ASSOC);

            // Today's Field Visits
            $visit_stmt = $db->prepare("SELECT firm_name, person_name, mobile, check_in_time, check_out_time, verified_address, audio_path FROM field_visits WHERE executive_name = ? AND visit_date = ? ORDER BY id ASC");
            $visit_stmt->execute([$username, $today]);
            $today_visits = $visit_stmt->fetchAll(PDO::FETCH_ASSOC);

            // Attendance History (Last 30 Days)
            $hist_stmt = $db->prepare("SELECT att_date, punch_in, punch_out, total_distance FROM staff_attendance WHERE username = ? AND att_date <= ? ORDER BY att_date DESC LIMIT 30");
            $hist_stmt->execute([$username, $today]);
            $history = $hist_stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Format history
            $formatted_history = [];
            foreach ($history as $h) {
                $dur = "-";
                if ($h['punch_in']) {
                    $p_in = strtotime($h['punch_in']);
                    $p_out = $h['punch_out'] ? strtotime($h['punch_out']) : ($h['att_date'] == $today ? time() : strtotime($h['att_date'] . ' 18:00:00'));
                    $sec = max(0, $p_out - $p_in);
                    $hrs = floor($sec / 3600);
                    $mins = floor(($sec % 3600) / 60);
                    $dur = "{$hrs}h {$mins}m";
                }
                $h['duration'] = $dur;
                $formatted_history[] = $h;
            }

            return_json([
                'profile' => $profile,
                'today_attendance' => $today_att,
                'today_visits' => $today_visits,
                'history' => $formatted_history
            ]);
            break;

        case 'get_employee_productivity':
            $date = trim($_GET['date'] ?? date('Y-m-d'));
            $staff = $db->query("SELECT username FROM users WHERE role IN ('Staff', 'Admin')")->fetchAll(PDO::FETCH_COLUMN);
            $result = [];
            foreach ($staff as $username) {
                $total_actions = $db->query("SELECT COUNT(*) FROM activities WHERE username = '$username' AND date(created_at) = '$date'")->fetchColumn();
                $applicants_added = $db->query("SELECT COUNT(*) FROM activities WHERE username = '$username' AND date(created_at) = '$date' AND description LIKE '%applicant%'")->fetchColumn();
                $documents_uploaded = $db->query("SELECT COUNT(*) FROM activities WHERE username = '$username' AND date(created_at) = '$date' AND description LIKE '%document%'")->fetchColumn();
                $disbursements_processed = $db->query("SELECT COUNT(*) FROM activities WHERE username = '$username' AND date(created_at) = '$date' AND description LIKE '%disbursement%'")->fetchColumn();
                $banks_assigned = $db->query("SELECT COUNT(*) FROM activities WHERE username = '$username' AND date(created_at) = '$date' AND description LIKE '%bank%'")->fetchColumn();
                
                
                // Working hours calculation
                $att_stmt = $db->prepare("SELECT punch_in, punch_out FROM staff_attendance WHERE username = ? AND att_date = ? ORDER BY id DESC LIMIT 1");
                $att_stmt->execute([$username, $date]);
                $att = $att_stmt->fetch(PDO::FETCH_ASSOC);
                $working_hours = "0h 0m";
                $is_live = false;
                if ($att && $att['punch_in']) {
                    $p_in = strtotime($att['punch_in']);
                    $p_out = $att['punch_out'] ? strtotime($att['punch_out']) : time();
                    
                    if ($p_in > 0) {
                        // If it's a past date and no punch out, cap it at 8 hours or end of day. For simplicity, if not today and no punch_out, we consider it incomplete.
                        if ($date != date('Y-m-d') && !$att['punch_out']) {
                            $p_out = strtotime($date . " 18:00:00");
                        }
                        
                        $duration_secs = max(0, $p_out - $p_in);
                        $h = floor($duration_secs / 3600);
                        $m = floor(($duration_secs % 3600) / 60);
                        $working_hours = "{$h}h {$m}m";
                        if (!$att['punch_out'] && $date == date('Y-m-d')) {
                            $is_live = true;
                        }
                    }
                }

                if ($total_actions > 0 || true) {
                    $result[] = [
                        'username' => $username,
                        'working_hours' => $working_hours,
                        'is_live' => $is_live,
                        'total_actions' => (int)$total_actions,
                        'applicants_added' => (int)$applicants_added,
                        'documents_uploaded' => (int)$documents_uploaded,
                        'disbursements_processed' => (int)$disbursements_processed,
                        'banks_assigned' => (int)$banks_assigned
                    ];
                }
            }
            return_json($result);
            break;

        case 'get_employee_activity_timeline':
            $date = trim($_GET['date'] ?? date('Y-m-d'));
            $username = trim($_GET['username'] ?? '');
            $stmt = $db->prepare("SELECT created_at, description FROM activities WHERE username = ? AND date(created_at) = ? ORDER BY created_at DESC");
            $stmt->execute([$username, $date]);
            return_json($stmt->fetchAll(PDO::FETCH_ASSOC));
            break;


        
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

        case 'get_field_visits':
            $page = max(1, intval($_GET['page'] ?? 1));
            $limit = 10;
            $offset = ($page - 1) * $limit;
            $search = trim($_GET['search'] ?? '');
            $quality = trim($_GET['quality'] ?? '');
            $prof = trim($_GET['prof'] ?? '');
            
            $sql = "SELECT * FROM field_visits WHERE 1=1";
            $params = [];
            
            if (($_SESSION['role'] ?? '') !== 'Admin') {
                $sql .= " AND executive_name = ?";
                $params[] = $_SESSION['name'] ?? $_SESSION['username'];
            }
            if ($search) {
                $sql .= " AND (firm_name LIKE ? OR person_name LIKE ? OR mobile LIKE ?)";
                array_push($params, "%$search%", "%$search%", "%$search%");
            }
            if ($quality) {
                $sql .= " AND lead_quality = ?";
                $params[] = $quality;
            }
            if ($prof) {
                $sql .= " AND profession = ?";
                $params[] = $prof;
            }
            
            $stmt = $db->prepare("SELECT COUNT(*) FROM ($sql)");
            $stmt->execute($params);
            $total = $stmt->fetchColumn();
            
            $sql .= " ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return_json([
                'data' => $data,
                'total_records' => $total,
                'total_pages' => ceil($total / $limit)
            ]);
            break;

        case 'get_visit_details':
            $id = intval($_GET['id'] ?? 0);
            $stmt = $db->prepare("SELECT * FROM field_visits WHERE id = ?");
            $stmt->execute([$id]);
            $visit = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$visit) return_json(['error' => 'Not found'], 404);
            
            $stmt = $db->prepare("SELECT * FROM field_visit_followups WHERE visit_id = ? ORDER BY created_at DESC");
            $stmt->execute([$id]);
            $followups = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return_json(['visit' => $visit, 'followups' => $followups]);
            break;
            
        case 'add_visit_followup':
            $visit_id = (int)($_POST['visit_id'] ?? 0);
            $remarks = trim($_POST['remarks'] ?? '');
            $next_meeting_date = trim($_POST['next_meeting_date'] ?? '');
            $added_by = $_SESSION['name'] ?? $_SESSION['username'] ?? 'Admin';
            
            if (!$visit_id || !$remarks) {
                return_json(['error' => 'Missing fields'], 400);
            }
            $stmt = $db->prepare("INSERT INTO field_visit_followups (visit_id, remarks, next_meeting_date, added_by) VALUES (?, ?, ?, ?)");
            $stmt->execute([$visit_id, $remarks, $next_meeting_date, $added_by]);
            return_json(['success' => true]);
            break;

        case 'save_applicant':
            // Ensure co_applicants table exists
            $db->exec("CREATE TABLE IF NOT EXISTS co_applicants (
                id INTEGER PRIMARY KEY AUTOINCREMENT, 
                applicant_id INTEGER, 
                is_financial TEXT, 
                relationship TEXT, 
                full_name TEXT, 
                mobile TEXT, 
                email TEXT, 
                dob TEXT, 
                pan_number TEXT, 
                aadhar_number TEXT, 
                same_address INTEGER, 
                address TEXT, 
                pincode TEXT, 
                state TEXT, 
                city TEXT, 
                employment_type TEXT, 
                monthly_income REAL, 
                current_emis REAL
            )");

            $converted_lead_id = (int)($_POST['converted_lead_id'] ?? 0);
            $id = (int)($_POST['id'] ?? 0);
            $customer_name = trim($_POST['customer_name'] ?? '');
            $mobile = trim($_POST['mobile'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $address = trim($_POST['address'] ?? '');
            $city = trim($_POST['city'] ?? '');
            $state = trim($_POST['state'] ?? '');
            $pincode = trim($_POST['pincode'] ?? '');
            $pan_number = trim($_POST['pan_number'] ?? '');
            $aadhar_number = trim($_POST['aadhar_number'] ?? '');
            $employment_type = trim($_POST['employment_type'] ?? '');
            $monthly_income = (float)($_POST['monthly_income'] ?? 0);
            $loan_type = trim($_POST['loan_type'] ?? '');
            $loan_sub_type = trim($_POST['loan_sub_type'] ?? '');
            $loan_amount_requested = (float)($_POST['loan_amount_requested'] ?? 0);
            $tenure_months = (int)($_POST['tenure_months'] ?? 0);
            $lead_source = trim($_POST['lead_source'] ?? '');
            $referral_id = trim($_POST['referral_id'] ?? '');
            $employee_id = trim($_POST['employee_id'] ?? '');
            $added_by = $_SESSION['name'] ?? $_SESSION['username'] ?? 'Admin';
            
            if (!$customer_name || !$mobile) {
                return_json(['error' => 'Required fields missing'], 400);
            }
            
            if ($id > 0) {
                $stmt = $db->prepare("UPDATE applicants SET customer_name=?, mobile=?, email=?, address=?, city=?, state=?, pincode=?, pan_number=?, aadhar_number=?, employment_type=?, monthly_income=?, loan_type=?, loan_sub_type=?, loan_amount_requested=?, tenure_months=?, lead_source=?, referral_id=?, employee_id=? WHERE id=?");
                $stmt->execute([$customer_name, $mobile, $email, $address, $city, $state, $pincode, $pan_number, $aadhar_number, $employment_type, $monthly_income, $loan_type, $loan_sub_type, $loan_amount_requested, $tenure_months, $lead_source, $referral_id, $employee_id, $id]);
                $final_id = $id;
                $msg = 'Applicant updated';
            } else {
                $loan_id = 'L' . date('Ymd') . rand(1000, 9999);
                $stmt = $db->prepare("INSERT INTO applicants (loan_id, customer_name, mobile, email, address, city, state, pincode, pan_number, aadhar_number, employment_type, monthly_income, loan_type, loan_sub_type, loan_amount_requested, tenure_months, lead_source, referral_id, employee_id, added_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$loan_id, $customer_name, $mobile, $email, $address, $city, $state, $pincode, $pan_number, $aadhar_number, $employment_type, $monthly_income, $loan_type, $loan_sub_type, $loan_amount_requested, $tenure_months, $lead_source, $referral_id, $employee_id, $added_by]);
                $final_id = $db->lastInsertId();
                
                if ($converted_lead_id > 0) {
                    $db->prepare("UPDATE leads SET stage = 'Converted' WHERE id = ?")->execute([$converted_lead_id]);
                }
                $msg = 'Applicant registered';
            }

            // Save Co-Applicants logic
            $db->prepare("DELETE FROM co_applicants WHERE applicant_id = ?")->execute([$final_id]);
            
            if (!empty($_POST['coapp_name']) && is_array($_POST['coapp_name'])) {
                $co_stmt = $db->prepare("INSERT INTO co_applicants (applicant_id, is_financial, relationship, full_name, mobile, email, dob, pan_number, aadhar_number, same_address, address, pincode, state, city, employment_type, monthly_income, current_emis) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
                foreach ($_POST['coapp_name'] as $index => $name) {
                    if (trim($name) === '') continue; // Skip empties
                    
                    $r_financial = $_POST['coapp_is_financial'][$index] ?? 'No';
                    $r_rel = $_POST['coapp_relationship'][$index] ?? '';
                    $r_mob = $_POST['coapp_mobile'][$index] ?? '';
                    $r_em = $_POST['coapp_email'][$index] ?? '';
                    $r_dob = $_POST['coapp_dob'][$index] ?? '';
                    $r_pan = $_POST['coapp_pan'][$index] ?? '';
                    $r_aad = $_POST['coapp_aadhar'][$index] ?? '';
                    
                    $r_same = (int)($_POST['coapp_same_address'][$index] ?? 0);
                    $r_addr = $r_same ? $address : ($_POST['coapp_address'][$index] ?? '');
                    $r_pin = $r_same ? $pincode : ($_POST['coapp_pincode'][$index] ?? '');
                    $r_state = $r_same ? $state : ($_POST['coapp_state'][$index] ?? '');
                    $r_city = $r_same ? $city : ($_POST['coapp_city'][$index] ?? '');
                    
                    $r_emp = $_POST['coapp_emp_type'][$index] ?? '';
                    $r_inc = (float)($_POST['coapp_income'][$index] ?? 0);
                    $r_emi = (float)($_POST['coapp_emis'][$index] ?? 0);
                    
                    $co_stmt->execute([
                        $final_id, $r_financial, $r_rel, trim($name), $r_mob, $r_em, $r_dob, 
                        $r_pan, $r_aad, $r_same, $r_addr, $r_pin, $r_state, $r_city, 
                        $r_emp, $r_inc, $r_emi
                    ]);
                }
            }
            
            return_json(['success' => true, 'message' => $msg, 'id' => $final_id]);
            break;

        case 'get_applicants':
            $page = max(1, intval($_GET['page'] ?? 1));
            $limit = 20;
            $offset = ($page - 1) * $limit;
            $search = trim($_GET['search'] ?? '');
            
            $sql = "SELECT *, 100 as calculated_completion FROM applicants WHERE 1=1";
            $params = [];
            
            if ($search) {
                $sql .= " AND (customer_name LIKE ? OR loan_id LIKE ? OR mobile LIKE ?)";
                array_push($params, "%$search%", "%$search%", "%$search%");
            }
            
            $stmt = $db->prepare("SELECT COUNT(*) FROM ($sql)");
            $stmt->execute($params);
            $total = $stmt->fetchColumn();
            
            $sql .= " ORDER BY id DESC LIMIT $limit OFFSET $offset";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return_json($data);
            break;

        case 'get_applicant':
            $id = (int)($_GET['id'] ?? 0);
            $stmt = $db->prepare("SELECT * FROM applicants WHERE id = ?");
            $stmt->execute([$id]);
            $app = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$app) return_json(['error' => 'Not found'], 404);
            
            // Fetch co-applicants
            $co_stmt = $db->prepare("SELECT * FROM co_applicants WHERE applicant_id = ?");
            $co_stmt->execute([$id]);
            $app['co_applicants'] = $co_stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return_json($app);
            break;
            
        case 'delete_applicant':
            if (($_SESSION['role'] ?? '') !== 'Admin') return_json(['error' => 'Unauthorized'], 403);
            $id = (int)($_POST['id'] ?? 0);
            $db->prepare("DELETE FROM applicants WHERE id = ?")->execute([$id]);
            return_json(['success' => true]);
            break;


        case 'search_applicants':
            $query = trim($_GET['query'] ?? '');
            $status = trim($_GET['status'] ?? '');
            $loan_type = trim($_GET['loan_type'] ?? '');
            $staff = trim($_GET['staff'] ?? '');
            $bank = trim($_GET['bank'] ?? '');
            $aging = trim($_GET['aging'] ?? '');
            $sort = trim($_GET['sort'] ?? 'newest');
            $date_from = trim($_GET['date_from'] ?? '');
            $date_to = trim($_GET['date_to'] ?? '');
            $amt_min = trim($_GET['amt_min'] ?? '');
            $amt_max = trim($_GET['amt_max'] ?? '');
            
            // If filtering by bank, we need a JOIN
            if ($bank) {
                $sql = "SELECT DISTINCT a.id, a.loan_id, a.customer_name, a.mobile, a.loan_amount_requested as amount, a.loan_type, a.overall_status, a.created_at 
                        FROM applicants a 
                        JOIN applicant_bank_assignments b ON a.id = b.applicant_id 
                        WHERE b.bank_name = ?";
                $params = [$bank];
            } else {
                $sql = "SELECT id, loan_id, customer_name, mobile, loan_amount_requested as amount, loan_type, overall_status, created_at FROM applicants WHERE 1=1";
                $params = [];
            }
            
            if ($query) {
                if ($bank) {
                    $sql .= " AND (a.customer_name LIKE ? OR a.loan_id LIKE ? OR a.mobile LIKE ? OR a.pan_number LIKE ?)";
                } else {
                    $sql .= " AND (customer_name LIKE ? OR loan_id LIKE ? OR mobile LIKE ? OR pan_number LIKE ?)";
                }
                array_push($params, "%$query%", "%$query%", "%$query%", "%$query%");
            }
            if ($status) {
                $prefix = $bank ? "a." : "";
                $sql .= " AND {$prefix}overall_status = ?";
                $params[] = $status;
            }
            if ($loan_type) {
                $prefix = $bank ? "a." : "";
                $sql .= " AND {$prefix}loan_type = ?";
                $params[] = $loan_type;
            }
            if ($staff) {
                $prefix = $bank ? "a." : "";
                $sql .= " AND {$prefix}added_by = ?";
                $params[] = $staff;
            }
            if ($date_from) {
                $prefix = $bank ? "a." : "";
                $sql .= " AND DATE({$prefix}created_at) >= ?";
                $params[] = $date_from;
            }
            if ($date_to) {
                $prefix = $bank ? "a." : "";
                $sql .= " AND DATE({$prefix}created_at) <= ?";
                $params[] = $date_to;
            }
            if ($amt_min !== '') {
                $prefix = $bank ? "a." : "";
                $sql .= " AND {$prefix}loan_amount_requested >= ?";
                $params[] = $amt_min;
            }
            if ($amt_max !== '') {
                $prefix = $bank ? "a." : "";
                $sql .= " AND {$prefix}loan_amount_requested <= ?";
                $params[] = $amt_max;
            }
            if ($aging) {
                $prefix = $bank ? "a." : "";
                if ($aging === '0-7') {
                    $sql .= " AND (julianday('now') - julianday({$prefix}created_at)) <= 7";
                } elseif ($aging === '7-30') {
                    $sql .= " AND (julianday('now') - julianday({$prefix}created_at)) > 7 AND (julianday('now') - julianday({$prefix}created_at)) <= 30";
                } elseif ($aging === '30-90') {
                    $sql .= " AND (julianday('now') - julianday({$prefix}created_at)) > 30 AND (julianday('now') - julianday({$prefix}created_at)) <= 90";
                } elseif ($aging === '90+') {
                    $sql .= " AND (julianday('now') - julianday({$prefix}created_at)) > 90";
                }
            }
            
            // ORDER BY Logic
            $prefix = $bank ? "a." : "";
            if ($sort === 'oldest') {
                $sql .= " ORDER BY {$prefix}id ASC";
            } elseif ($sort === 'amount_high') {
                $sql .= " ORDER BY CAST({$prefix}loan_amount_requested AS REAL) DESC";
            } elseif ($sort === 'amount_low') {
                $sql .= " ORDER BY CAST({$prefix}loan_amount_requested AS REAL) ASC";
            } elseif ($sort === 'name_az') {
                $sql .= " ORDER BY {$prefix}customer_name ASC";
            } else {
                $sql .= " ORDER BY {$prefix}id DESC"; // newest default
            }
            
            $offset = (int)($_GET['offset'] ?? 0);
            $limit = 10;
            
            $sql .= " LIMIT $limit OFFSET $offset";
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            return_json($stmt->fetchAll(PDO::FETCH_ASSOC));
            break;

        case 'applicant_full_details':
            $id = (int)($_GET['id'] ?? 0);
            $stmt = $db->prepare("SELECT * FROM applicants WHERE id = ?");
            $stmt->execute([$id]);
            $app = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$app) return_json(['error' => 'Not found'], 404);
            
            // Phase 1 Calculation
            $phase1_fields = [
                'customer_name' => 'Name', 'mobile' => 'Mobile', 'email' => 'Email', 
                'pan_number' => 'PAN', 'aadhar_number' => 'Aadhaar', 'address' => 'Address', 
                'monthly_income' => 'Income', 'loan_type' => 'Loan Type'
            ];
            $phase1_missing = [];
            $phase1_filled = 0;
            foreach ($phase1_fields as $key => $label) {
                if (empty($app[$key])) {
                    $phase1_missing[] = $label;
                } else {
                    $phase1_filled++;
                }
            }
            $app['phase1_completion'] = round(($phase1_filled / count($phase1_fields)) * 100);
            $app['phase1_missing'] = $phase1_missing;

            // Phase 2 Calculation (Dynamic Checklist)
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
            
            // documents
            $stmt = $db->prepare("SELECT * FROM applicant_documents WHERE applicant_id = ?");
            $stmt->execute([$id]);
            $docs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $app['documents'] = $docs;
            
            // Match against mandatory
            $uploaded_categories = array_column($docs, 'document_category');
            $checklist = [];
            $checklist_met = 0;
            foreach ($mandatory_categories as $cat) {
                $is_up = in_array($cat, $uploaded_categories);
                if ($is_up) $checklist_met++;
                $checklist[] = ['category' => $cat, 'uploaded' => $is_up];
            }
            $app['phase2_checklist'] = $checklist;
            $app['phase2_completion'] = round(($checklist_met / count($mandatory_categories)) * 100);

            // disbursements
            $stmt = $db->prepare("SELECT * FROM applicant_disbursements WHERE applicant_id = ?");
            $stmt->execute([$id]);
            $app['disbursements'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // banks
            $stmt = $db->prepare("SELECT * FROM applicant_bank_assignments WHERE applicant_id = ?");
            $stmt->execute([$id]);
            $app['banks'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // timeline / activities
            $searchStr = "%id=$id%";
            $stmt = $db->prepare("SELECT * FROM activities WHERE action_link LIKE ? ORDER BY id DESC LIMIT 20");
            $stmt->execute([$searchStr]);
            $app['timeline'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // TAT Calculation
            $created_at = new DateTime($app['created_at']);
            $now = new DateTime();
            $diff = $now->diff($created_at);
            $app['tat_days'] = $diff->days;
            
            // Payout Distributions
            $stmt = $db->prepare("SELECT pd.*, u.name as payee_name FROM payout_distributions pd LEFT JOIN users u ON pd.payee_user_id = u.id WHERE pd.applicant_id = ?");
            $stmt->execute([$id]);
            $app['payouts'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return_json($app);
            break;
            
        case 'get_payouts':
            $page = max(1, (int)($_GET['page'] ?? 1));
            $limit = max(1, (int)($_GET['limit'] ?? 10));
            $offset = ($page - 1) * $limit;
            
            $where_clauses = ["1=1"];
            $params = [];

            if (!empty($_GET['status'])) {
                $where_clauses[] = "pd.status = ?";
                $params[] = $_GET['status'];
            }
            if (!empty($_GET['payee_type'])) {
                $where_clauses[] = "pd.payee_type = ?";
                $params[] = $_GET['payee_type'];
            }
            if (!empty($_GET['search'])) {
                $search = "%" . $_GET['search'] . "%";
                $where_clauses[] = "(a.customer_name LIKE ? OR a.loan_id LIKE ? OR u.name LIKE ?)";
                $params[] = $search;
                $params[] = $search;
                $params[] = $search;
            }

            $where_sql = implode(" AND ", $where_clauses);
            
            // Get total count
            $count_sql = "SELECT COUNT(*) FROM payout_distributions pd
                          LEFT JOIN applicants a ON pd.applicant_id = a.id
                          LEFT JOIN users u ON pd.payee_user_id = u.id
                          WHERE $where_sql";
            $stmtC = $db->prepare($count_sql);
            $stmtC->execute($params);
            $total_records = $stmtC->fetchColumn();
            
            // Get paginated data
            $sql = "SELECT pd.*, a.customer_name, a.loan_id, u.name as payee_name, u.role as payee_role
                    FROM payout_distributions pd
                    LEFT JOIN applicants a ON pd.applicant_id = a.id
                    LEFT JOIN users u ON pd.payee_user_id = u.id
                    WHERE $where_sql
                    ORDER BY pd.created_at DESC
                    LIMIT $limit OFFSET $offset";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $payouts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return_json([
                'success' => true,
                'payouts' => $payouts,
                'total' => $total_records,
                'page' => $page,
                'limit' => $limit,
                'total_pages' => ceil($total_records / $limit)
            ]);
            break;
            
        case 'add_applicant_note':
            $id = (int)($_POST['id'] ?? 0);
            $note = trim($_POST['note'] ?? '');
            if($id && $note) {
                log_activity("Internal Note: " . $note, "search_track.php?id=$id");
                return_json(['success' => true]);
            }
            return_json(['error' => 'Invalid data'], 400);
            break;


        case 'get_applicant_documents':
            $applicant_id = (int)($_GET['applicant_id'] ?? 0);
            $stmt = $db->prepare("SELECT * FROM applicant_documents WHERE applicant_id = ? ORDER BY id DESC");
            $stmt->execute([$applicant_id]);
            return_json($stmt->fetchAll(PDO::FETCH_ASSOC));
            break;

        case 'upload_applicant_document':
            $applicant_id = (int)($_POST['applicant_id'] ?? 0);
            $category = trim($_POST['document_category'] ?? '');
            $name = trim($_POST['document_name'] ?? '');
            $added_by = $_SESSION['name'] ?? $_SESSION['username'] ?? 'Admin';
            
            if (!$applicant_id || !$category || !$name || !isset($_FILES['document_file'])) {
                return_json(['error' => 'Missing fields'], 400);
            }
            
            $file = $_FILES['document_file'];
            if ($file['error'] !== UPLOAD_ERR_OK) {
                return_json(['error' => 'File upload error'], 400);
            }
            
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'pdf'])) {
                return_json(['error' => 'Invalid file format'], 400);
            }
            
            $filename = uniqid('doc_') . '.' . $ext;
            $upload_dir = 'uploads/documents/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            $filepath = $upload_dir . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                $stmt = $db->prepare("INSERT INTO applicant_documents (applicant_id, document_category, document_name, file_path, status) VALUES (?, ?, ?, ?, 'Pending')");
                $stmt->execute([$applicant_id, $category, $name, $filepath]);
                
                // Auto-advance to Phase 2 if currently in Phase 1
                $db->query("UPDATE applicants SET overall_status = 'Phase 2' WHERE id = $applicant_id AND overall_status = 'Phase 1'");
                
                log_activity("Uploaded document: $name ($category)", "applicant_documents.php?id=$applicant_id");
                
                return_json(['success' => true, 'message' => 'Document uploaded successfully']);
            }
            
            return_json(['error' => 'Failed to save file'], 500);
            break;

        case 'delete_applicant_document':
            $id = (int)($_POST['id'] ?? 0);
            $stmt = $db->prepare("SELECT file_path, applicant_id, document_name FROM applicant_documents WHERE id = ?");
            $stmt->execute([$id]);
            $doc = $stmt->fetch();
            
            if ($doc) {
                if (file_exists($doc['file_path'])) {
                    unlink($doc['file_path']);
                }
                $db->prepare("DELETE FROM applicant_documents WHERE id = ?")->execute([$id]);
                log_activity("Deleted document: {$doc['document_name']}", "applicant_documents.php?id={$doc['applicant_id']}");
                return_json(['success' => true]);
            }
            return_json(['error' => 'Not found'], 404);
            break;

        case 'update_document_status':
            $id = (int)($_POST['id'] ?? 0);
            $status = trim($_POST['status'] ?? '');
            $notes = trim($_POST['notes'] ?? '');
            
            $stmt = $db->prepare("UPDATE applicant_documents SET status = ?, notes = ? WHERE id = ?");
            $stmt->execute([$status, $notes, $id]);
            
            // log activity
            $stmt2 = $db->prepare("SELECT applicant_id, document_name FROM applicant_documents WHERE id = ?");
            $stmt2->execute([$id]);
            if ($doc = $stmt2->fetch()) {
                log_activity("Updated status to $status for document: {$doc['document_name']}", "applicant_documents.php?id={$doc['applicant_id']}");
            }
            return_json(['success' => true]);
            break;


        case 'get_states':
            $stmt = $db->query("SELECT DISTINCT state FROM pincodes ORDER BY state");
            return_json(['success' => true, 'states' => $stmt->fetchAll(PDO::FETCH_COLUMN)]);
            break;
            
        case 'get_cities':
            $state = trim($_GET['state'] ?? '');
            $stmt = $db->prepare("SELECT DISTINCT city FROM pincodes WHERE state = ? ORDER BY city");
            $stmt->execute([$state]);
            return_json(['success' => true, 'cities' => $stmt->fetchAll(PDO::FETCH_COLUMN)]);
            break;
            
        case 'verify_pincode':
            $pin = trim($_GET['pin'] ?? '');
            $stmt = $db->prepare("SELECT city, state FROM pincodes WHERE pincode = ?");
            $stmt->execute([$pin]);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($res) {
                return_json(['success' => true, 'city' => $res['city'], 'state' => $res['state']]);
            }
            return_json(['error' => 'Not found'], 404);
            break;


        case 'get_applicant_disbursements':
            $applicant_id = (int)($_GET['applicant_id'] ?? 0);
            $stmt = $db->prepare("SELECT * FROM applicant_disbursements WHERE applicant_id = ? ORDER BY phase_number ASC");
            $stmt->execute([$applicant_id]);
            return_json($stmt->fetchAll(PDO::FETCH_ASSOC));
            break;

        case 'save_applicant_disbursement':
            $applicant_id = (int)($_POST['applicant_id'] ?? 0);
            $phase_number = (int)($_POST['phase_number'] ?? 0);
            $phase_name = trim($_POST['phase_name'] ?? '');
            $amount = (float)($_POST['amount'] ?? 0);
            $status = trim($_POST['status'] ?? 'Pending');
            $disbursed_date = trim($_POST['disbursed_date'] ?? '');
            $remarks = trim($_POST['remarks'] ?? '');
            
            if (!$applicant_id || !$phase_number || !$amount) {
                return_json(['error' => 'Missing fields'], 400);
            }
            
            $stmt = $db->prepare("INSERT INTO applicant_disbursements (applicant_id, phase_number, phase_name, amount, status, disbursed_date, remarks) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$applicant_id, $phase_number, $phase_name, $amount, $status, $disbursed_date, $remarks]);
            
            log_activity("Added disbursement Phase $phase_number: $amount ($status)", "applicant_disbursements.php?id=$applicant_id");
            return_json(['success' => true]);
            break;

        case 'delete_applicant_disbursement':
            $id = (int)($_POST['id'] ?? 0);
            $stmt = $db->prepare("SELECT applicant_id, phase_number FROM applicant_disbursements WHERE id = ?");
            $stmt->execute([$id]);
            $disb = $stmt->fetch();
            if ($disb) {
                $db->prepare("DELETE FROM applicant_disbursements WHERE id = ?")->execute([$id]);
                log_activity("Deleted disbursement Phase {$disb['phase_number']}", "applicant_disbursements.php?id={$disb['applicant_id']}");
                return_json(['success' => true]);
            }
            return_json(['error' => 'Not found'], 404);
            break;

        case 'get_applicant_banks':
            $applicant_id = (int)($_GET['applicant_id'] ?? 0);
            $stmt = $db->prepare("SELECT * FROM applicant_bank_assignments WHERE applicant_id = ? ORDER BY id DESC");
            $stmt->execute([$applicant_id]);
            return_json($stmt->fetchAll(PDO::FETCH_ASSOC));
            break;

        case 'assign_applicant_bank':
            $applicant_id = (int)($_POST['applicant_id'] ?? 0);
            $bank_name = trim($_POST['bank_name'] ?? '');
            $status = trim($_POST['status'] ?? 'Pending');
            $notes = trim($_POST['notes'] ?? '');
            $assigned_by = $_SESSION['name'] ?? $_SESSION['username'] ?? 'Admin';
            
            if (!$applicant_id || !$bank_name) return_json(['error' => 'Missing fields'], 400);
            
            $stmt = $db->prepare("INSERT INTO applicant_bank_assignments (applicant_id, bank_name, status, rejection_reason, assigned_by) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$applicant_id, $bank_name, $status, $notes, $assigned_by]);
            
            log_activity("Assigned file to bank: $bank_name ($status)", "applicant_bank_assign.php?id=$applicant_id");
            return_json(['success' => true]);
            break;


        
        case 'add_referral':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return_json(['error' => 'Invalid Method'], 405);
            
            $referrer_type = trim($_POST['referrer_type'] ?? '');
            $full_name = trim($_POST['full_name'] ?? '');
            $dob = trim($_POST['dob'] ?? '');
            $mobile = trim($_POST['mobile'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $city_state = trim($_POST['city_state'] ?? '');
            $account_name = trim($_POST['account_name'] ?? '');
            $bank_name = trim($_POST['bank_name'] ?? '');
            $account_number = trim($_POST['account_number'] ?? '');
            $ifsc_code = trim($_POST['ifsc_code'] ?? '');
            $upi_id = trim($_POST['upi_id'] ?? '');
            $commission_rate = trim($_POST['commission_rate'] ?? '');
            $payout_frequency = trim($_POST['payout_frequency'] ?? '');
            $pan_number = trim($_POST['pan_number'] ?? '');
            $aadhar_number = trim($_POST['aadhar_number'] ?? '');
            $assigned_rm = trim($_POST['assigned_rm'] ?? '');
            $status = trim($_POST['status'] ?? 'Active');
            
            if (!$referrer_type || !$full_name || !$mobile) {
                return_json(['error' => 'Type, Name and Mobile are required.'], 400);
            }
            
            $bank_doc_path = '';
            $pan_doc_path = '';
            $aadhar_doc_path = '';
            
            if (!is_dir('uploads/referrals')) {
                @mkdir('uploads/referrals', 0777, true);
            }
            
            if (isset($_FILES['bank_document']) && $_FILES['bank_document']['error'] == 0) {
                $ext = pathinfo($_FILES['bank_document']['name'], PATHINFO_EXTENSION);
                $bank_doc_path = 'uploads/referrals/' . uniqid('bank_') . '.' . $ext;
                move_uploaded_file($_FILES['bank_document']['tmp_name'], $bank_doc_path);
            }
            if (isset($_FILES['pan_document']) && $_FILES['pan_document']['error'] == 0) {
                $ext = pathinfo($_FILES['pan_document']['name'], PATHINFO_EXTENSION);
                $pan_doc_path = 'uploads/referrals/' . uniqid('pan_') . '.' . $ext;
                move_uploaded_file($_FILES['pan_document']['tmp_name'], $pan_doc_path);
            }
            if (isset($_FILES['aadhar_document']) && $_FILES['aadhar_document']['error'] == 0) {
                $ext = pathinfo($_FILES['aadhar_document']['name'], PATHINFO_EXTENSION);
                $aadhar_doc_path = 'uploads/referrals/' . uniqid('aadhar_') . '.' . $ext;
                move_uploaded_file($_FILES['aadhar_document']['tmp_name'], $aadhar_doc_path);
            }
            
            $prefix = strtoupper(substr($referrer_type, 0, 3));
            $referral_id = "REF-" . $prefix . "-" . date('Ymd') . rand(100, 999);
            
            try {
                $stmt = $db->prepare("INSERT INTO referrals (referral_id, referrer_type, full_name, dob, mobile, email, city_state, account_name, bank_name, account_number, ifsc_code, upi_id, commission_rate, payout_frequency, pan_number, aadhar_number, bank_document_path, pan_document_path, aadhar_document_path, assigned_rm, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$referral_id, $referrer_type, $full_name, $dob, $mobile, $email, $city_state, $account_name, $bank_name, $account_number, $ifsc_code, $upi_id, $commission_rate, $payout_frequency, $pan_number, $aadhar_number, $bank_doc_path, $pan_doc_path, $aadhar_doc_path, $assigned_rm, $status]);
                
                
                // Also create a user account for them
                $username = trim($_POST['username'] ?? '');
                $password = trim($_POST['password'] ?? '');
                if (!empty($username) && !empty($password)) {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $role = 'DSA'; // Default role for referral partners
                    try {
                        $ustmt = $db->prepare("INSERT INTO users (username, name, password_hash, role) VALUES (?, ?, ?, ?)");
                        $ustmt->execute([$username, $full_name, $hash, $role]);
                    } catch (Exception $ue) {
                        // Ignore if username already exists, just don't create user
                    }
                }

                log_activity("Added new referral partner: $full_name", "referrals_list.php");
                return_json(['success' => true, 'message' => 'Referral Partner added successfully!', 'referral_id' => $referral_id]);
            } catch (Exception $e) {
                return_json(['error' => 'Database error: ' . $e->getMessage()], 400);
            }

        case 'edit_referral':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return_json(['error' => 'Invalid Method'], 405);
            $id = $_POST['id'] ?? '';
            if (!$id) return_json(['error' => 'Missing ID'], 400);
            
            $referrer_type = trim($_POST['referrer_type'] ?? '');
            $full_name = trim($_POST['full_name'] ?? '');
            $dob = trim($_POST['dob'] ?? '');
            $mobile = trim($_POST['mobile'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $city_state = trim($_POST['city_state'] ?? '');
            $account_name = trim($_POST['account_name'] ?? '');
            $bank_name = trim($_POST['bank_name'] ?? '');
            $account_number = trim($_POST['account_number'] ?? '');
            $ifsc_code = trim($_POST['ifsc_code'] ?? '');
            $upi_id = trim($_POST['upi_id'] ?? '');
            $commission_rate = trim($_POST['commission_rate'] ?? '');
            $payout_frequency = trim($_POST['payout_frequency'] ?? '');
            $pan_number = trim($_POST['pan_number'] ?? '');
            $aadhar_number = trim($_POST['aadhar_number'] ?? '');
            $assigned_rm = trim($_POST['assigned_rm'] ?? '');
            $status = trim($_POST['status'] ?? 'Active');
            
            $stmt = $db->prepare("SELECT bank_document_path, pan_document_path, aadhar_document_path FROM referrals WHERE id = ?");
            $stmt->execute([$id]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$existing) return_json(['error' => 'Not found'], 404);
            
            $bank_doc_path = $existing['bank_document_path'];
            $pan_doc_path = $existing['pan_document_path'];
            $aadhar_doc_path = $existing['aadhar_document_path'] ?? '';
            
            if (!is_dir('uploads/referrals')) {
                @mkdir('uploads/referrals', 0777, true);
            }
            
            if (isset($_FILES['bank_document']) && $_FILES['bank_document']['error'] == 0) {
                $ext = pathinfo($_FILES['bank_document']['name'], PATHINFO_EXTENSION);
                $bank_doc_path = 'uploads/referrals/' . uniqid('bank_') . '.' . $ext;
                move_uploaded_file($_FILES['bank_document']['tmp_name'], $bank_doc_path);
            }
            if (isset($_FILES['pan_document']) && $_FILES['pan_document']['error'] == 0) {
                $ext = pathinfo($_FILES['pan_document']['name'], PATHINFO_EXTENSION);
                $pan_doc_path = 'uploads/referrals/' . uniqid('pan_') . '.' . $ext;
                move_uploaded_file($_FILES['pan_document']['tmp_name'], $pan_doc_path);
            }
            if (isset($_FILES['aadhar_document']) && $_FILES['aadhar_document']['error'] == 0) {
                $ext = pathinfo($_FILES['aadhar_document']['name'], PATHINFO_EXTENSION);
                $aadhar_doc_path = 'uploads/referrals/' . uniqid('aadhar_') . '.' . $ext;
                move_uploaded_file($_FILES['aadhar_document']['tmp_name'], $aadhar_doc_path);
            }
            
            try {
                $stmt = $db->prepare("UPDATE referrals SET referrer_type=?, full_name=?, dob=?, mobile=?, email=?, city_state=?, account_name=?, bank_name=?, account_number=?, ifsc_code=?, upi_id=?, commission_rate=?, payout_frequency=?, pan_number=?, aadhar_number=?, bank_document_path=?, pan_document_path=?, aadhar_document_path=?, assigned_rm=?, status=? WHERE id=?");
                $stmt->execute([$referrer_type, $full_name, $dob, $mobile, $email, $city_state, $account_name, $bank_name, $account_number, $ifsc_code, $upi_id, $commission_rate, $payout_frequency, $pan_number, $aadhar_number, $bank_doc_path, $pan_doc_path, $aadhar_doc_path, $assigned_rm, $status, $id]);
                
                log_activity("Updated referral partner: $full_name", "referrals_list.php");
                return_json(['success' => true, 'message' => 'Referral Partner updated successfully!']);
            } catch (Exception $e) {
                return_json(['error' => 'Database error: ' . $e->getMessage()], 400);
            }


        case 'get_active_referrals':
            // Get from referrals table
            $stmt = $db->query("SELECT referral_id, full_name, referrer_type as type FROM referrals WHERE status = 'Active'");
            $refs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Also append users who act as partners (DSA, CA, Builder, etc.)
            $stmt2 = $db->query("SELECT username as referral_id, name as full_name, role as type FROM users WHERE is_active = 1 AND role IN ('DSA', 'CA', 'Builder', 'Partner')");
            $users = $stmt2->fetchAll(PDO::FETCH_ASSOC);
            
            // Merge and remove duplicates if any (though unlikely to overlap if ID structures are different)
            $all = array_merge($refs, $users);
            return_json($all);
            break;



        case 'update_bank_assignment':
            $assignment_id = (int)($_POST['assignment_id'] ?? 0);
            $status = trim($_POST['status'] ?? '');
            $reason = trim($_POST['rejection_reason'] ?? '');
            if (!$assignment_id || !$status) return_json(['error' => 'Missing fields'], 400);
            
            $stmt = $db->prepare("UPDATE applicant_bank_assignments SET status = ?, rejection_reason = ? WHERE id = ?");
            if ($stmt->execute([$status, $reason, $assignment_id])) {
                return_json(['success' => true, 'message' => 'Status updated!']);
            }
            return_json(['error' => 'Database error'], 500);
            break;
            
        case 'save_bank_feedback':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return_json(['error' => 'Invalid Request'], 405);
            $applicant_id = (int)($_POST['applicant_id'] ?? 0);
            if (!$applicant_id) return_json(['error' => 'Missing ID'], 400);
            
            $cibil = trim($_POST['cibil_score'] ?? '');
            $sanctioned = trim($_POST['sanctioned_amount'] ?? '');
            $roi = trim($_POST['interest_rate'] ?? '');
            $tenure = trim($_POST['tenure_months'] ?? '');
            $emi = trim($_POST['emi'] ?? '');
            $sanction_date = trim($_POST['sanction_date'] ?? date('Y-m-d'));
            
            // Ensure column exists
            try { $db->exec("ALTER TABLE applicants ADD COLUMN sanction_date TEXT"); } catch (Exception $e) { /* ignore */ }
            
            $stmt = $db->prepare("UPDATE applicants SET cibil_score = ?, sanctioned_amount = ?, interest_rate = ?, tenure_months = ?, emi = ?, sanction_date = ?, overall_status = 'Phase 3' WHERE id = ?");
            if($stmt->execute([$cibil, $sanctioned, $roi, $tenure, $emi, $sanction_date, $applicant_id])) {
                log_activity("Updated Banker Feedback & Sanction details", "applicant_disbursements.php?id=$applicant_id");
                return_json(['success' => true, 'message' => 'Banker feedback saved successfully!']);
            }
            return_json(['error' => 'Database error'], 500);
            break;


        case 'email_banker_zip':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return_json(['error' => 'Invalid Request Method'], 405);
            $applicant_id = (int)($_POST['applicant_id'] ?? 0);
            $banker_email = trim($_POST['banker_email'] ?? '');
            $cc_email = trim($_POST['cc_email'] ?? '');
            $subject = trim($_POST['subject'] ?? '');
            $body = trim($_POST['body'] ?? '');
            
            if (!$applicant_id || !$banker_email || !$subject) {
                return_json(['error' => 'Missing required fields'], 400);
            }
            
            $stmt = $db->prepare("SELECT * FROM applicants WHERE id = ?");
            $stmt->execute([$applicant_id]);
            $applicant = $stmt->fetch();
            if (!$applicant) return_json(['error' => 'Applicant not found'], 404);
            
            $loan_id = $applicant['loan_id'] ?: 'L'.$applicant_id;
            $customer_name = preg_replace('/[^A-Za-z0-9_-]/', '_', $applicant['customer_name']);
            $zip_filename = "Bundle_" . $loan_id . "_" . $customer_name . ".zip";
            
            $upload_dir = 'uploads/documents/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            $zip_path = $upload_dir . time() . '_' . $zip_filename;
            
            $zip = new ZipArchive();
            if ($zip->open($zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
                return_json(['error' => 'Failed to generate ZIP'], 500);
            }
            
            // 1. Text Summary
            $profile_text = "APPLICANT PROFILE\n=================\n";
            $profile_text .= "Name: " . $applicant['customer_name'] . "\nLoan Amount: INR " . $applicant['loan_amount_requested'] . "\n";
            $zip->addFromString("00_Applicant_Summary.txt", $profile_text);
            
            // 2. Add Documents
            $stmt_docs = $db->prepare("SELECT * FROM applicant_documents WHERE applicant_id = ?");
            $stmt_docs->execute([$applicant_id]);
            foreach ($stmt_docs->fetchAll(PDO::FETCH_ASSOC) as $doc) {
                if (file_exists($doc['file_path'])) {
                    $zip->addFile($doc['file_path'], "Documents/" . basename($doc['file_path']));
                }
            }
            $zip->close();
            
            // 3. Send via PHPMailer
            require_once __DIR__ . '/libs/PHPMailer/Exception.php';
            require_once __DIR__ . '/libs/PHPMailer/PHPMailer.php';
            require_once __DIR__ . '/libs/PHPMailer/SMTP.php';
            
            $company_profile = $db->query("SELECT * FROM company_profile LIMIT 1")->fetch();
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            try {
                if (!empty($company_profile['smtp_host'])) {
                    $mail->isSMTP();
                    $mail->Host = $company_profile['smtp_host'];
                    $mail->SMTPAuth = true;
                    $mail->Username = $company_profile['smtp_username'];
                    $mail->Password = $company_profile['smtp_password'];
                    if (strtolower($company_profile['smtp_encryption']) === 'ssl') {
                        $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
                    } else {
                        $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                    }
                    $mail->Port = (int)$company_profile['smtp_port'];
                }
                $mail->setFrom($company_profile['smtp_username'] ?? 'admin@BFS Financial Services.com', $company_profile['company_name'] ?? 'BFS Financial Services');
                $mail->addAddress($banker_email);
                if (!empty($cc_email)) {
                    $mail->addCC($cc_email);
                }
                $mail->Subject = $subject;
                $mail->isHTML(true);
                $mail->Body = $body;
                $mail->addAttachment($zip_path, $zip_filename);
                $mail->send();
                
                unlink($zip_path); // clean up
                
                // Auto-create a bank submission record
                $posted_bank = trim($_POST['bank_name'] ?? '');
                $bank_name = !empty($posted_bank) ? $posted_bank : "Banker (" . $banker_email . ")";
                $assigned_by = $_SESSION['name'] ?? $_SESSION['username'] ?? 'Admin';
                
                $stmt_assign = $db->prepare("INSERT INTO applicant_bank_assignments (applicant_id, bank_name, status, rejection_reason, assigned_by) VALUES (?, ?, 'Pending', 'Sent via Email', ?)");
                $stmt_assign->execute([$applicant_id, $bank_name, $assigned_by]);

                log_activity("Emailed ZIP bundle to banker ($banker_email)", "applicant_disbursements.php?id=$applicant_id");
                return_json(['success' => true, 'message' => 'Email sent successfully with ZIP attachment!']);
            } catch (Exception $e) {
                if (file_exists($zip_path)) unlink($zip_path);
                return_json(['error' => 'Mail error: ' . $mail->ErrorInfo], 500);
            }
            break;


        
        case 'add_custom_bank':
            if (($_SESSION['role'] ?? '') !== 'Admin') return_json(['error' => 'Unauthorized'], 403);
            $type = trim($_POST['bank_type'] ?? '');
            $name = trim($_POST['bank_name'] ?? '');
            
            if (!$type || !$name) return_json(['error' => 'Missing data'], 400);
            
            $js_file = __DIR__ . '/assets/js/banks_directory.js';
            if (!file_exists($js_file)) return_json(['error' => 'JS file not found'], 404);
            
            $content = file_get_contents($js_file);
            // We need to inject the new bank into the array for the specific type
            $search = '"' . $type . '": [';
            if (strpos($content, $search) !== false) {
                // Check if it already exists
                if (stripos($content, '"' . $name . '"') !== false) {
                    return_json(['success' => true, 'message' => 'Already exists']);
                }
                
                $replace = $search . "\n        \"" . $name . "\",";
                $new_content = str_replace($search, $replace, $content);
                file_put_contents($js_file, $new_content);
                return_json(['success' => true, 'message' => 'Bank added successfully']);
            } else {
                return_json(['error' => 'Bank type not found in directory'], 404);
            }

        case 'add_banker':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return_json(['error' => 'Invalid Method'], 405);
            $full_name = trim($_POST['full_name'] ?? '');
            $bank_type = trim($_POST['bank_type'] ?? 'Public Sector Bank');
            $bank_name = trim($_POST['bank_name'] ?? '');
            $designation = trim($_POST['designation'] ?? '');
            $state = trim($_POST['state'] ?? '');
            $city = trim($_POST['city'] ?? '');
            $pincode = trim($_POST['pincode'] ?? '');
            $address = trim($_POST['address'] ?? '');
            $ifsc_code = trim($_POST['ifsc_code'] ?? '');
            $contact_number = trim($_POST['contact_number'] ?? '');
            $official_email = trim($_POST['official_email'] ?? '');
            $loan_category = isset($_POST['loan_category']) ? (is_array($_POST['loan_category']) ? implode(',', $_POST['loan_category']) : trim($_POST['loan_category'])) : '';
            $min_limit = (float)($_POST['min_loan_limit'] ?? 0);
            $max_limit = (float)($_POST['max_loan_limit'] ?? 0);
            $dsa_code = trim($_POST['dsa_code'] ?? '');
            $employee_id = $_SESSION['user_id'] ?? 1;

            if(empty($full_name) || empty($bank_name)) return_json(['error' => 'Name and Bank Name are required.'], 400);

            $stmt = $db->prepare("INSERT INTO bankers (full_name, bank_type, bank_name, designation, state, city, pincode, address, ifsc_code, contact_number, official_email, loan_category, min_loan_limit, max_loan_limit, employee_id, dsa_code) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if($stmt->execute([$full_name, $bank_type, $bank_name, $designation, $state, $city, $pincode, $address, $ifsc_code, $contact_number, $official_email, $loan_category, $min_limit, $max_limit, $employee_id, $dsa_code])) {
                
                // Auto-save this branch into ifsc_master if it doesn't exist
                if (!empty($ifsc_code)) {
                    $check = $db->prepare("SELECT ifsc FROM ifsc_master WHERE ifsc = ?");
                    $check->execute([$ifsc_code]);
                    if (!$check->fetch()) {
                        $ins = $db->prepare("INSERT INTO ifsc_master (ifsc, bank, branch, address, city, state) VALUES (?, ?, ?, ?, ?, ?)");
                        $ins->execute([$ifsc_code, $bank_name, $city . ' BRANCH', $address, $city, $state]);
                    }
                }

                log_activity("Added Banker: $full_name ($bank_name)", "bankers_list.php");
                return_json(['success' => true, 'message' => 'Banker added successfully']);
            }
            return_json(['error' => 'Failed to add banker'], 500);
            break;

        case 'update_banker':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return_json(['error' => 'Invalid Method'], 405);
            $id = (int)($_POST['id'] ?? 0);
            $full_name = trim($_POST['full_name'] ?? '');
            $bank_type = trim($_POST['bank_type'] ?? 'Public Sector Bank');
            $bank_name = trim($_POST['bank_name'] ?? '');
            $designation = trim($_POST['designation'] ?? '');
            $state = trim($_POST['state'] ?? '');
            $city = trim($_POST['city'] ?? '');
            $pincode = trim($_POST['pincode'] ?? '');
            $address = trim($_POST['address'] ?? '');
            $ifsc_code = trim($_POST['ifsc_code'] ?? '');
            $contact_number = trim($_POST['contact_number'] ?? '');
            $official_email = trim($_POST['official_email'] ?? '');
            $loan_category = isset($_POST['loan_category']) ? (is_array($_POST['loan_category']) ? implode(',', $_POST['loan_category']) : trim($_POST['loan_category'])) : '';
            $min_limit = (float)($_POST['min_loan_limit'] ?? 0);
            $max_limit = (float)($_POST['max_loan_limit'] ?? 0);
            $dsa_code = trim($_POST['dsa_code'] ?? '');
            $status = trim($_POST['status'] ?? 'Active');

            if(!$id || empty($full_name) || empty($bank_name)) return_json(['error' => 'Invalid input.'], 400);

            $stmt = $db->prepare("UPDATE bankers SET full_name=?, bank_type=?, bank_name=?, designation=?, state=?, city=?, pincode=?, address=?, ifsc_code=?, contact_number=?, official_email=?, loan_category=?, min_loan_limit=?, max_loan_limit=?, dsa_code=?, status=? WHERE id=?");
            if($stmt->execute([$full_name, $bank_type, $bank_name, $designation, $state, $city, $pincode, $address, $ifsc_code, $contact_number, $official_email, $loan_category, $min_limit, $max_limit, $dsa_code, $status, $id])) {
                
                // Auto-save this branch into ifsc_master if it doesn't exist
                if (!empty($ifsc_code)) {
                    $check = $db->prepare("SELECT ifsc FROM ifsc_master WHERE ifsc = ?");
                    $check->execute([$ifsc_code]);
                    if (!$check->fetch()) {
                        $ins = $db->prepare("INSERT INTO ifsc_master (ifsc, bank, branch, address, city, state) VALUES (?, ?, ?, ?, ?, ?)");
                        $ins->execute([$ifsc_code, $bank_name, $city . ' BRANCH', $address, $city, $state]);
                    }
                }

                log_activity("Updated Banker: $full_name ($bank_name)", "bankers_list.php");
                return_json(['success' => true, 'message' => 'Banker updated successfully']);
            }
            return_json(['error' => 'Failed to update banker'], 500);
            break;

        
        case 'save_payout_rule':
            if ($_SESSION['role'] !== 'Admin') return_json(['error' => 'Unauthorized'], 403);
            $bank_name = trim($_POST['bank_name'] ?? '');
            $loan_type = trim($_POST['loan_type'] ?? '');
            $payout = (float)($_POST['payout_percentage'] ?? 0);
            
            // Check if exists
            $stmt = $db->prepare("SELECT id FROM bank_payout_settings WHERE bank_name = ? AND loan_type = ?");
            $stmt->execute([$bank_name, $loan_type]);
            $existing = $stmt->fetchColumn();
            
            if ($existing) {
                $stmt = $db->prepare("UPDATE bank_payout_settings SET payout_percentage = ? WHERE id = ?");
                $stmt->execute([$payout, $existing]);
            } else {
                $stmt = $db->prepare("INSERT INTO bank_payout_settings (bank_name, loan_type, payout_percentage) VALUES (?, ?, ?)");
                $stmt->execute([$bank_name, $loan_type, $payout]);
            }
            return_json(['success' => true]);
            break;
            
        case 'delete_payout_rule':
            if ($_SESSION['role'] !== 'Admin') return_json(['error' => 'Unauthorized'], 403);
            $id = (int)($_POST['id'] ?? 0);
            $db->prepare("DELETE FROM bank_payout_settings WHERE id = ?")->execute([$id]);
            return_json(['success' => true]);
            break;
            
        case 'get_payout_percent':
            $bank_name = trim($_POST['bank_name'] ?? '');
            $loan_type = trim($_POST['loan_type'] ?? '');
            
            // Priority: Specific Loan Type -> 'All' Loan Types -> 0
            $stmt = $db->prepare("SELECT payout_percentage FROM bank_payout_settings WHERE bank_name = ? AND (loan_type = ? OR loan_type = 'All') ORDER BY loan_type = 'All' ASC LIMIT 1");
            $stmt->execute([$bank_name, $loan_type]);
            $percent = $stmt->fetchColumn();
            return_json(['success' => true, 'payout' => $percent ?: 0]);
            break;

        
        case 'fetch_pincode':
            $pin = trim($_GET['pincode'] ?? '');
            if (!$pin || strlen($pin) !== 6) return_json(['error' => 'Invalid Pincode'], 400);
            
            $stmt = $db->prepare("SELECT city, state FROM pincode_master WHERE pincode = ?");
            $stmt->execute([$pin]);
            $info = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($info) {
                return_json(['success' => true, 'data' => $info]);
            } else {
                return_json(['error' => 'Pincode not found'], 404);
            }

        case 'fetch_ifsc':
            $ifsc = trim($_GET['ifsc'] ?? '');
            if (!$ifsc || strlen($ifsc) !== 11) return_json(['error' => 'Invalid IFSC code'], 400);
            
            $stmt = $db->prepare("SELECT * FROM ifsc_master WHERE ifsc = ? LIMIT 1");
            $stmt->execute([$ifsc]);
            $info = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($info) {
                return_json(['success' => true, 'data' => $info]);
            } else {
                // Fallback to Razorpay via PHP cURL to avoid frontend CORS/Adblock issues
                $ch = curl_init("https://ifsc.razorpay.com/" . $ifsc);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($httpCode === 200 && $response) {
                    $liveData = json_decode($response, true);
                    if ($liveData && isset($liveData['BANK'])) {
                        $info = [
                            'bank' => $liveData['BANK'],
                            'branch' => $liveData['BRANCH'],
                            'address' => $liveData['ADDRESS'],
                            'city' => $liveData['CITY'],
                            'state' => $liveData['STATE']
                        ];
                        // Cache it back to local DB so next time it's fast!
                        $insert = $db->prepare("INSERT OR REPLACE INTO ifsc_master (ifsc, bank, branch, address, city, state) VALUES (?, ?, ?, ?, ?, ?)");
                        $insert->execute([$ifsc, $info['bank'], $info['branch'], $info['address'], $info['city'], $info['state']]);
                        
                        return_json(['success' => true, 'data' => $info]);
                    }
                }
                
                return_json(['success' => false, 'message' => 'IFSC not found or invalid']);
            }
            break;
            
        case 'get_branches':
            $bank = trim($_GET['bank'] ?? '');
            $city = trim($_GET['city'] ?? '');
            if (!$bank || !$city) return_json(['success' => true, 'data' => []]);

            $stmt = $db->prepare("SELECT ifsc, branch, address FROM ifsc_master WHERE bank LIKE ? AND (city = ? OR city LIKE ? OR city LIKE ? OR city LIKE ?) LIMIT 100");
            $stmt->execute(['%'.$bank.'%', $city, $city . ' %', '% ' . $city, '% ' . $city . ' %']);
            $branches = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return_json(['success' => true, 'data' => $branches]);
            break;

        
        case 'staff_ping':
            if (!isset($_SESSION['username'])) { echo json_encode(['error' => 'Not logged in']); exit; }
            $u = $_SESSION['username'];
            $lat = $_POST['lat'] ?? '';
            $lon = $_POST['lon'] ?? '';
            $bat = $_POST['battery'] ?? '';
            $status = $_POST['status'] ?? 'Active';
            
            if($lat && $lon) {
                // Update user current state
                $now = date('Y-m-d H:i:s');
                $upd = $db->prepare("UPDATE users SET last_ping = ?, current_lat = ?, current_lon = ?, current_battery = ?, current_status = ? WHERE username = ?");
                $upd->execute([$now, $lat, $lon, $bat, $status, $u]);
                
                // Log location
                $log = $db->prepare("INSERT INTO staff_location_logs (username, lat, lon, battery, status) VALUES (?, ?, ?, ?, ?)");
                $log->execute([$u, $lat, $lon, $bat, $status]);
            }
            echo json_encode(['success' => true]);
            break;

        
        case 'get_staff_performance':
            if (!isset($_SESSION['user_id'])) { echo json_encode(['error' => 'Unauthorized']); exit; }
            $u = $_GET['username'] ?? $_SESSION['username'];
            
            // Total visits
            $v_stmt = $db->prepare("SELECT COUNT(*) FROM field_visits WHERE executive_name = ?");
            $v_stmt->execute([$u]);
            $tv = $v_stmt->fetchColumn();
            
            // Total leads (applicants generated by this user)
            $l_stmt = $db->prepare("SELECT COUNT(*) FROM applicants WHERE added_by = ?");
            $l_stmt->execute([$u]);
            $tl = $l_stmt->fetchColumn();
            
            // Get Employee Commission Rate
            $uid = $_SESSION['user_id'];
            $c_stmt = $db->prepare("SELECT commission_rate FROM employees WHERE user_id = ?");
            $c_stmt->execute([$uid]);
            $crate = $c_stmt->fetchColumn();
            $crate = $crate ? $crate : 0;
            
            // Get Total Commission Earned from payout_distributions
            $p_stmt = $db->prepare("SELECT SUM(net_payable) FROM payout_distributions WHERE payee_type = 'Staff' AND payee_user_id = ?");
            $p_stmt->execute([$uid]);
            $t_comm = $p_stmt->fetchColumn();
            $t_comm = $t_comm ? $t_comm : 0;
            
            echo json_encode([
                'total_visits' => $tv,
                'total_leads' => $tl,
                'total_commission' => $t_comm,
                'commission_rate' => $crate
            ]);
            break;

        case 'get_staff_recent_leads':
            if (!isset($_SESSION['user_id'])) { echo json_encode(['error' => 'Unauthorized']); exit; }
            $u = $_GET['username'] ?? $_SESSION['username'];
            
            $stmt = $db->prepare("SELECT id, customer_name, loan_type, loan_amount_requested, created_at, overall_status FROM applicants WHERE added_by = ? ORDER BY created_at DESC LIMIT 5");
            $stmt->execute([$u]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            break;

        
        case 'punch_in':
            if (!isset($_SESSION['username'])) exit;
            $u = $_SESSION['username'];
            $today = date('Y-m-d');
            $now = date('Y-m-d H:i:s');
            
            $stmt = $db->prepare("SELECT id FROM staff_attendance WHERE username = ? AND att_date = ?");
            $stmt->execute([$u, $today]);
            if (!$stmt->fetch()) {
                $ins = $db->prepare("INSERT INTO staff_attendance (username, att_date, punch_in, status) VALUES (?, ?, ?, 'Working')");
                $ins->execute([$u, $today, $now]);
            } else {
                $upd = $db->prepare("UPDATE staff_attendance SET punch_out = NULL, status = 'Working' WHERE username = ? AND att_date = ?");
                $upd->execute([$u, $today]);
            }
            
            $u_upd = $db->prepare("UPDATE users SET current_status = 'Online' WHERE username = ?");
            $u_upd->execute([$u]);
            
            echo json_encode(['success' => true]);
            break;
            
        case 'update_staff_profile':
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

        case 'punch_out':
            if (!isset($_SESSION['username'])) exit;
            $u = $_SESSION['username'];
            $today = date('Y-m-d');
            $now = date('Y-m-d H:i:s');
            $upd = $db->prepare("UPDATE staff_attendance SET punch_out = ?, status = 'Completed' WHERE username = ? AND att_date = ?");
            $upd->execute([$now, $u, $today]);
            
            // Also set user to Offline
            $u_upd = $db->prepare("UPDATE users SET current_status = 'Offline' WHERE username = ?");
            $u_upd->execute([$u]);
            
            echo json_encode(['success' => true]);
            break;

        default:
            return_json(['error' => 'API command not recognized.'], 404);
    }
} catch (Exception $e) {
    return_json(['error' => 'Server Error: ' . $e->getMessage()], 500);
}
