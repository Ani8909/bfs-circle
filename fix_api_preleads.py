import re

file_path = r'c:\Users\pc\Downloads\client mgmt2\api.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# Replace get_preleads
get_preleads_start = content.find("case 'get_preleads':")
get_preleads_end = content.find("break;", get_preleads_start) + 6

new_get_preleads = """case 'get_preleads':
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
            
            $stmt = $db->prepare("UPDATE pre_leads SET status = ?, followup_date = ?, last_called_at = datetime('now', 'localtime'), call_count = call_count + 1, notes = IFNULL(notes,'') || '\n' || ? WHERE id = ?");
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
"""

content = content[:get_preleads_start] + new_get_preleads + content[get_preleads_end:]

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Updated get_preleads and added log_call in api.php")
