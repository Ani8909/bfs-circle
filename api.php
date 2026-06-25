<?php
// api.php - AuraCRM backend API endpoints dispatcher

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
            
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $role = 'Staff'; // Force role to Staff
            
            if (empty($username) || empty($password)) return_json(['error' => 'Username and password required'], 400);
            
            $hash = password_hash($password, PASSWORD_DEFAULT);
            try {
                $stmt = $db->prepare("INSERT INTO users (username, password_hash, role, is_active) VALUES (?, ?, ?, 1)");
                $stmt->execute([$username, $hash, $role]);
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
            $stmt = $db->query("SELECT id, username, name, role, is_active, created_at FROM users ORDER BY created_at ASC");
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
            if (($_SESSION['role'] ?? '') !== 'Admin') {
                $stmt = $db->prepare("SELECT * FROM pre_leads WHERE assigned_to = ? ORDER BY created_at DESC");
                $stmt->execute([$_SESSION['username'] ?? '']);
            } else {
                $stmt = $db->query("SELECT * FROM pre_leads ORDER BY created_at DESC");
            }
            return_json($stmt->fetchAll());
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
            if ($role === 'Admin') {
                $stmt = $db->query("SELECT * FROM reminders WHERE status='Pending' ORDER BY remind_at ASC");
            } else {
                $stmt = $db->prepare("SELECT * FROM reminders WHERE assigned_to=? AND status='Pending' ORDER BY remind_at ASC");
                $stmt->execute([$username]);
            }
            return_json($stmt->fetchAll());
            break;
            
        case 'save_reminder':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return_json(['error' => 'Invalid Request'], 405);
            $lead_type = $_POST['lead_type'] ?? '';
            $lead_id = (int)($_POST['lead_id'] ?? 0);
            $remind_at = $_POST['remind_at'] ?? '';
            $notes = $_POST['notes'] ?? '';
            
            $assigned_to = $_SESSION['username'];
            $name = "Unknown";
            if ($lead_type === 'Lead') {
                $l = $db->query("SELECT assigned_to, lead_name FROM leads WHERE id=$lead_id")->fetch();
                if($l) {
                    $assigned_to = $l['assigned_to'];
                    $name = $l['lead_name'];
                }
            } else {
                $l = $db->query("SELECT assigned_to, name FROM pre_leads WHERE id=$lead_id")->fetch();
                if($l) {
                    $assigned_to = $l['assigned_to'];
                    $name = $l['name'];
                }
            }
            
            $stmt = $db->prepare("INSERT INTO reminders (lead_type, lead_id, assigned_to, remind_at, notes) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$lead_type, $lead_id, $assigned_to, $remind_at, $notes]);
            
            $action_url = ($lead_type === 'Lead') ? "leads.php?edit_lead=$lead_id" : "pre_leads.php?edit_prelead=$lead_id";
            log_activity("Set a new reminder for $lead_type: $name (at $remind_at)", $action_url);
            
            return_json(['success' => true, 'message' => 'Reminder saved successfully!']);
            break;

        case 'complete_reminder':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return_json(['error' => 'Invalid Request'], 405);
            $id = (int)($_POST['id'] ?? 0);
            if ($id) {
                $rem = $db->query("SELECT lead_type, lead_id FROM reminders WHERE id=$id")->fetch();
                $db->query("UPDATE reminders SET status='Completed' WHERE id=$id");
                if ($rem) {
                    $name = "Unknown";
                    $action_url = null;
                    if ($rem['lead_type'] === 'Lead') {
                        $l = $db->query("SELECT lead_name FROM leads WHERE id=" . $rem['lead_id'])->fetch();
                        if($l) $name = $l['lead_name'];
                        $action_url = "leads.php?edit_lead={$rem['lead_id']}";
                    } else {
                        $l = $db->query("SELECT name FROM pre_leads WHERE id=" . $rem['lead_id'])->fetch();
                        if($l) $name = $l['name'];
                        $action_url = "pre_leads.php?edit_prelead={$rem['lead_id']}";
                    }
                    log_activity("Completed reminder for {$rem['lead_type']}: $name", $action_url);
                } else {
                    log_activity("Completed a reminder", "dashboard.php");
                }
                return_json(['success' => true]);
            }
            return_json(['error' => 'Missing ID'], 400);
            break;

        case 'get_leads':
            $s          = $_GET['search'] ?? '';
            $f_stage    = $_GET['stage'] ?? '';
            $f_priority = $_GET['priority'] ?? '';
            $f_assigned = $_GET['assigned_to'] ?? '';
            $sql_l      = "SELECT * FROM leads WHERE 1=1";
            $params_l   = [];
            if (($_SESSION['role'] ?? '') !== 'Admin') {
                $sql_l .= " AND assigned_to = ?";
                $params_l[] = $_SESSION['username'] ?? '';
            }
            if ($s)          { $sql_l .= " AND (lead_name LIKE ? OR company_name LIKE ? OR mobile LIKE ?)"; $params_l = array_merge($params_l, ["%$s%","%$s%","%$s%"]); }
            if ($f_stage)    { $sql_l .= " AND stage = ?";       $params_l[] = $f_stage; }
            if ($f_priority) { $sql_l .= " AND priority = ?";    $params_l[] = $f_priority; }
            if ($f_assigned) { $sql_l .= " AND assigned_to = ?"; $params_l[] = $f_assigned; }
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
            $pending_followups = $db->query("SELECT COUNT(*) FROM clients WHERE priority = 'Hot' AND overall_status IN ('New', 'Contacted', 'In Negotiation') $assignedFilterAnd")->fetchColumn();
            $total_quote_value = $db->query("SELECT SUM(quotations.total_amount) FROM quotations" . $quotationJoinFilterEmpty)->fetchColumn() ?: 0;
            $no_quotation_clients = $db->query("SELECT COUNT(*) FROM clients WHERE id NOT IN (SELECT DISTINCT client_id FROM quotations) $assignedFilterAnd")->fetchColumn();
            
            $total_staff = $db->query("SELECT COUNT(*) FROM users WHERE role = 'Staff'")->fetchColumn();
            $online_staff = $db->query("SELECT COUNT(*) FROM users WHERE role = 'Staff' AND last_active >= datetime('now', 'localtime', '-2 minutes')")->fetchColumn();
            $active_staff = $db->query("SELECT COUNT(*) FROM users WHERE is_active = 1")->fetchColumn();
            
            $total_leads = $db->query("SELECT COUNT(*) FROM leads$assignedFilter")->fetchColumn();
            $hot_leads   = $db->query("SELECT COUNT(*) FROM leads WHERE priority='Hot' AND stage NOT IN ('Won','Lost') $assignedFilterAnd")->fetchColumn();
            
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
                $mail->setFrom($profile['smtp_username'], 'AuraCRM');
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
                
                $mail->setFrom($company_profile['email'] ?? 'admin@auracrm.com', $company_profile['company_name'] ?? 'AuraCRM');
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
            
            if (empty($company_name) || empty($address_line1) || empty($city) || empty($state) || empty($pincode) || empty($gstin) || empty($email) || empty($contact_person)) {
                return_json(['error' => 'All fields except Address Line 2, Mobile, Bank Name, Account Number, IFSC, and SMTP are required.'], 400);
            }
            
            $stmt = $db->prepare("UPDATE company_profile SET company_name = ?, address_line1 = ?, address_line2 = ?, city = ?, state = ?, pincode = ?, country = ?, gstin = ?, email = ?, mobile = ?, contact_person = ?, bank_name = ?, account_number = ?, ifsc_code = ?, smtp_host = ?, smtp_port = ?, smtp_username = ?, smtp_password = ?, smtp_encryption = ?");
            $stmt->execute([$company_name, $address_line1, $address_line2, $city, $state, $pincode, $country, $gstin, $email, $mobile, $contact_person, $bank_name, $account_number, $ifsc_code, $smtp_host, $smtp_port, $smtp_username, $smtp_password, $smtp_encryption]);
            
            $act_desc = "Company settings & profile updated by {$contact_person} — Today " . date('h:i A');
            log_activity($act_desc);
            
            return_json(['success' => true, 'message' => 'CRM Configurations updated successfully!']);
            break;

        default:
            return_json(['error' => 'API command not recognized.'], 404);
    }
} catch (Exception $e) {
    return_json(['error' => 'Server Error: ' . $e->getMessage()], 500);
}
