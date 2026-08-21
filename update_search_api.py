import re

file_path = r'c:\Users\pc\Downloads\client mgmt2\api.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

old_api = """        case 'search_applicants':
            $query = trim($_GET['query'] ?? '');
            $status = trim($_GET['status'] ?? '');
            $loan_type = trim($_GET['loan_type'] ?? '');
            
            $sql = "SELECT id, loan_id, customer_name, mobile, loan_amount_requested as amount, loan_type, overall_status FROM applicants WHERE 1=1";
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
            
            $offset = (int)($_GET['offset'] ?? 0);
            $limit = 10;
            
            $sql .= " ORDER BY id DESC LIMIT $limit OFFSET $offset";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            
            // Get total count for checking if more data exists
            $countSql = "SELECT COUNT(*) FROM applicants WHERE 1=1" . substr($sql, strpos($sql, " AND"));
            // We need to clean up the count query logic
            
            // Actually, simpler way: fetch LIMIT 11. If we get 11, there's more. But let's just return the data and let frontend handle it.
            return_json($stmt->fetchAll(PDO::FETCH_ASSOC));
            break;"""

new_api = """        case 'search_applicants':
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
            break;"""

content = content.replace(old_api, new_api)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("API updated to handle all advanced filters.")
