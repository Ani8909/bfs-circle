import re

with open('api.php', 'r', encoding='utf-8') as f:
    content = f.read()

more_endpoints = '''
        case 'search_applicants':
            $query = trim($_GET['query'] ?? '');
            $status = trim($_GET['status'] ?? '');
            $loan_type = trim($_GET['loan_type'] ?? '');
            
            $sql = "SELECT id, loan_id, customer_name, mobile, loan_amount_requested as amount, loan_type, overall_status as current_stage FROM applicants WHERE 1=1";
            $params = [];
            
            if ($query) {
                $sql .= " AND (customer_name LIKE ? OR loan_id LIKE ? OR mobile LIKE ? OR pan_number LIKE ?)";
                array_push($params, "%$query%", "%$query%", "%$query%", "%$query%");
            }
            if ($status) {
                $sql .= " AND overall_status = ?";
                $params[] = $status;
            }
            if ($loan_type) {
                $sql .= " AND loan_type = ?";
                $params[] = $loan_type;
            }
            
            $sql .= " ORDER BY id DESC LIMIT 50";
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
            
            // documents
            $stmt = $db->prepare("SELECT * FROM applicant_documents WHERE applicant_id = ?");
            $stmt->execute([$id]);
            $app['documents'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // disbursements
            $stmt = $db->prepare("SELECT * FROM applicant_disbursements WHERE applicant_id = ?");
            $stmt->execute([$id]);
            $app['disbursements'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // banks
            $stmt = $db->prepare("SELECT * FROM applicant_bank_assignments WHERE applicant_id = ?");
            $stmt->execute([$id]);
            $app['banks'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return_json($app);
            break;
'''

old_default = '''        default:
            return_json(['error' => 'API command not recognized.'], 404);'''

new_content = more_endpoints + "\n" + old_default
content = content.replace(old_default, new_content)

with open('api.php', 'w', encoding='utf-8') as f:
    f.write(content)
print('Done adding more endpoints!')
