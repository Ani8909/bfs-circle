<?php
session_start();
/**
 * AuraCRM - Professional Client Management & Tracking System
 * Features:
 * - SQLite Zero-Config Database
 * - Modern Slate & Orange Dashboard Dashboard with Chart.js
 * - Permanent Client Entry Form (No Edit/Delete Rules)
 * - Complete CRM Search & Tracking (Expandable visual pipeline)
 * - Email Dispatch Module with Quill.js Editor & Attachment handler
 * - Quotation Builder with auto GST math & browser-based PDF printing (html2pdf)
 * - Real-time statistics, search, and dynamic status workflows
 */

// Error configuration for local development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database path configuration
$db_file = __DIR__ . '/crm.db';
$uploads_dir = __DIR__ . '/uploads';

// Initialize directory for attachments
if (!file_exists($uploads_dir)) {
    mkdir($uploads_dir, 0777, true);
}

try {
    $db = new PDO("sqlite:" . $db_file);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Core database schema initialization
    $db->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE NOT NULL,
        password_hash TEXT NOT NULL,
        role TEXT NOT NULL DEFAULT 'Staff', -- 'Admin', 'Staff'
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // Create default admin if no users exist
    $user_count = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
    if ($user_count == 0) {
        $default_hash = password_hash('admin123', PASSWORD_DEFAULT);
        $db->exec("INSERT INTO users (username, password_hash, role) VALUES ('admin', '$default_hash', 'Admin')");
    }

    try {
        $db->exec("ALTER TABLE users ADD COLUMN is_active INTEGER DEFAULT 1");
    } catch (Exception $e) {}
    
    try {
        $db->exec("ALTER TABLE users ADD COLUMN session_token TEXT");
    } catch (Exception $e) {}

    $db->exec("CREATE TABLE IF NOT EXISTS email_templates (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        template_name TEXT NOT NULL,
        type TEXT NOT NULL,
        subject TEXT NOT NULL,
        body TEXT NOT NULL,
        attachment_name TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    $db->exec("CREATE TABLE IF NOT EXISTS presentations (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        original_name TEXT NOT NULL,
        filename TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS leads (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        lead_name TEXT NOT NULL,
        company_name TEXT,
        mobile TEXT NOT NULL,
        email TEXT,
        lead_source TEXT DEFAULT 'Cold Call',
        assigned_to TEXT,
        priority TEXT DEFAULT 'Warm',
        stage TEXT DEFAULT 'New Lead',
        notes TEXT,
        location TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    try { $db->exec("ALTER TABLE leads ADD COLUMN location TEXT"); } catch (Exception $e) {}

    $db->exec("CREATE TABLE IF NOT EXISTS pre_leads (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        company_name TEXT,
        mobile TEXT NOT NULL,
        email TEXT,
        source TEXT DEFAULT 'Unknown',
        status TEXT DEFAULT 'Not Contacted',
        assigned_to TEXT,
        location TEXT,
        notes TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    try { $db->exec("ALTER TABLE leads ADD COLUMN location TEXT"); } catch (Exception $e) {}

    $db->exec("CREATE TABLE IF NOT EXISTS clients (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        company_name TEXT UNIQUE NOT NULL,
        business_type TEXT NOT NULL,
        industry_sector TEXT,
        gstin TEXT,
        pan TEXT,
        website TEXT,
        turnover TEXT,
        employees INTEGER,
        contact_name TEXT NOT NULL,
        designation TEXT,
        mobile TEXT NOT NULL,
        whatsapp TEXT,
        email TEXT NOT NULL,
        alternate_email TEXT,
        linkedin TEXT,
        address_line1 TEXT NOT NULL,
        address_line2 TEXT,
        city TEXT NOT NULL,
        state TEXT NOT NULL,
        pincode TEXT NOT NULL,
        country TEXT NOT NULL,
        bank_name TEXT,
        account_number TEXT,
        ifsc_code TEXT,
        lead_source TEXT NOT NULL,
        priority TEXT NOT NULL, -- 'Hot', 'Warm', 'Cold'
        added_by TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        remarks TEXT,
        overall_status TEXT DEFAULT 'New' -- 'New', 'Contacted', 'In Negotiation', 'Closed Won', 'Closed Lost'
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS communications (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        client_id INTEGER,
        type TEXT NOT NULL, -- 'Pitch', 'PPT', 'Custom Mail', 'Quotation'
        subject TEXT NOT NULL,
        body TEXT NOT NULL,
        cc TEXT,
        attachment_name TEXT,
        sent_by TEXT NOT NULL,
        sent_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(client_id) REFERENCES clients(id)
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS quotations (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        client_id INTEGER,
        quotation_number TEXT UNIQUE NOT NULL,
        status TEXT DEFAULT 'Pending', -- 'Pending', 'Approved', 'Rejected'
        subtotal REAL NOT NULL,
        gst_amount REAL NOT NULL,
        total_amount REAL NOT NULL,
        items_json TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(client_id) REFERENCES clients(id)
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS activities (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        description TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS company_profile (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        company_name TEXT DEFAULT 'Aura CRM Solutions',
        address_line1 TEXT DEFAULT 'Plot 12, Hitech Lane, Bandra West',
        address_line2 TEXT DEFAULT '',
        city TEXT DEFAULT 'Mumbai',
        state TEXT DEFAULT 'Maharashtra',
        pincode TEXT DEFAULT '400050',
        country TEXT DEFAULT 'India',
        gstin TEXT DEFAULT '27AABCA9087A1Z0',
        email TEXT DEFAULT 'info@auracrm.com',
        mobile TEXT DEFAULT '9876543210',
        contact_person TEXT DEFAULT 'Rahul Sharma',
        bank_name TEXT DEFAULT 'HDFC Bank',
        account_number TEXT DEFAULT '50100987654321',
        ifsc_code TEXT DEFAULT 'HDFC0001234'
    )");

    $prof_count = $db->query("SELECT COUNT(*) FROM company_profile")->fetchColumn();
    if ($prof_count == 0) {
        $db->exec("INSERT INTO company_profile (company_name, address_line1, address_line2, city, state, pincode, country, gstin, email, mobile, contact_person, bank_name, account_number, ifsc_code) 
                   VALUES ('Aura CRM Solutions', 'Plot 12, Hitech Lane, Bandra West', '', 'Mumbai', 'Maharashtra', '400050', 'India', '27AABCA9087A1Z0', 'info@auracrm.com', '9876543210', 'Rahul Sharma', 'HDFC Bank', '50100987654321', 'HDFC0001234')");
    }

    // Safe SQLite Alter Table for SMTP fields
    try { $db->exec("ALTER TABLE company_profile ADD COLUMN smtp_host TEXT DEFAULT ''"); } catch (Exception $e) {}
    try { $db->exec("ALTER TABLE company_profile ADD COLUMN smtp_port TEXT DEFAULT ''"); } catch (Exception $e) {}
    try { $db->exec("ALTER TABLE company_profile ADD COLUMN smtp_username TEXT DEFAULT ''"); } catch (Exception $e) {}
    try { $db->exec("ALTER TABLE company_profile ADD COLUMN smtp_password TEXT DEFAULT ''"); } catch (Exception $e) {}
    try { $db->exec("ALTER TABLE company_profile ADD COLUMN smtp_encryption TEXT DEFAULT ''"); } catch (Exception $e) {}

    // Populate database with mock data if fresh install (improves dashboard visual styling on launch)
    $count = $db->query("SELECT COUNT(*) FROM clients")->fetchColumn();
    if ($count == 0) {
        $dummy_clients = [
            [
                'company_name' => 'Apex Industries', 'business_type' => 'Manufacturer', 'industry_sector' => 'Automotive',
                'gstin' => '27AAACA1234A1Z1', 'pan' => 'AAACA1234A', 'website' => 'https://apexind.com',
                'turnover' => '₹5-10 Crores', 'employees' => 120, 'contact_name' => 'Amit Sharma', 'designation' => 'Director',
                'mobile' => '9876543210', 'whatsapp' => '9876543210', 'email' => 'amit@apexind.com', 'alternate_email' => 'info@apexind.com',
                'linkedin' => 'https://linkedin.com/in/amit-apex', 'address_line1' => 'Plot 45, MIDC Industrial Area',
                'address_line2' => 'Andheri East', 'city' => 'Mumbai', 'state' => 'Maharashtra', 'pincode' => '400093', 'country' => 'India',
                'bank_name' => 'HDFC Bank', 'account_number' => '50100234567890', 'ifsc_code' => 'HDFC0000060',
                'lead_source' => 'Website', 'priority' => 'Hot', 'added_by' => 'Rahul Sharma',
                'remarks' => 'Very interested in our new industrial solutions package.', 'overall_status' => 'In Negotiation',
                'created_at' => date('Y-m-d H:i:s', strtotime('-4 months'))
            ],
            [
                'company_name' => 'Zenith Retail', 'business_type' => 'Retailer', 'industry_sector' => 'Consumer Goods',
                'gstin' => '24AABCB5678B1Z2', 'pan' => 'AABCB5678B', 'website' => 'https://zenithretail.in',
                'turnover' => '₹1-5 Crores', 'employees' => 45, 'contact_name' => 'Priya Patel', 'designation' => 'Purchase Manager',
                'mobile' => '9123456789', 'whatsapp' => '9123456789', 'email' => 'priya@zenithretail.in', 'alternate_email' => 'purchase@zenithretail.in',
                'linkedin' => '', 'address_line1' => '102 Royal Arcade', 'address_line2' => 'C.G. Road', 'city' => 'Ahmedabad',
                'state' => 'Gujarat', 'pincode' => '380009', 'country' => 'India', 'bank_name' => 'ICICI Bank',
                'account_number' => '000405001234', 'ifsc_code' => 'ICIC0000004', 'lead_source' => 'Exhibition',
                'priority' => 'Warm', 'added_by' => 'Karan Singh', 'remarks' => 'Met at PlastIndia Expo. Requested basic presentation deck.',
                'overall_status' => 'Contacted', 'created_at' => date('Y-m-d H:i:s', strtotime('-3 months'))
            ],
            [
                'company_name' => 'Nova Software Solutions', 'business_type' => 'Service', 'industry_sector' => 'Information Technology',
                'gstin' => '29AABCC9012C1Z3', 'pan' => 'AABCC9012C', 'website' => 'https://novasoft.io',
                'turnover' => '₹10-25 Crores', 'employees' => 250, 'contact_name' => 'Rohan Murthy', 'designation' => 'CTO',
                'mobile' => '8887776665', 'whatsapp' => '8887776665', 'email' => 'rohan@novasoft.io', 'alternate_email' => '',
                'linkedin' => 'https://linkedin.com/in/rohan-nova', 'address_line1' => 'Block B, Tech Park', 'address_line2' => 'Whitefield',
                'city' => 'Bengaluru', 'state' => 'Karnataka', 'pincode' => '560066', 'country' => 'India', 'bank_name' => 'Axis Bank',
                'account_number' => '912010045678901', 'ifsc_code' => 'UTIB0000010', 'lead_source' => 'Reference',
                'priority' => 'Hot', 'added_by' => 'Rahul Sharma', 'remarks' => 'Requires customized software integration. Closed successfully.',
                'overall_status' => 'Closed Won', 'created_at' => date('Y-m-d H:i:s', strtotime('-5 months'))
            ],
            [
                'company_name' => 'Matrix Logistics', 'business_type' => 'Service', 'industry_sector' => 'Logistics',
                'gstin' => '19AABCD3456D1Z4', 'pan' => 'AABCD3456D', 'website' => 'https://matrixlogistics.com',
                'turnover' => '₹1-5 Crores', 'employees' => 80, 'contact_name' => 'Subhash Bose', 'designation' => 'Operations Head',
                'mobile' => '7776665554', 'whatsapp' => '', 'email' => 'subhash@matrixlogistics.com', 'alternate_email' => '',
                'linkedin' => '', 'address_line1' => 'Salt Lake Sector V', 'address_line2' => 'Near College More', 'city' => 'Kolkata',
                'state' => 'West Bengal', 'pincode' => '700091', 'country' => 'India', 'bank_name' => '', 'account_number' => '',
                'ifsc_code' => '', 'lead_source' => 'Cold Call', 'priority' => 'Cold', 'added_by' => 'Anjali Mehta',
                'remarks' => 'Cold entry created. Need to pitch next week.', 'overall_status' => 'New',
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 months'))
            ],
            [
                'company_name' => 'Elite Exports', 'business_type' => 'Trader', 'industry_sector' => 'Textiles',
                'gstin' => '07AABCE7890E1Z5', 'pan' => 'AABCE7890E', 'website' => 'https://eliteexports.com',
                'turnover' => '₹25-50 Crores', 'employees' => 150, 'contact_name' => 'Vikram Goel', 'designation' => 'Managing Director',
                'mobile' => '9998887776', 'whatsapp' => '9998887776', 'email' => 'vikram@eliteexports.com', 'alternate_email' => '',
                'linkedin' => '', 'address_line1' => 'Okhla Phase III', 'address_line2' => '', 'city' => 'Delhi',
                'state' => 'Delhi', 'pincode' => '110020', 'country' => 'India', 'bank_name' => '', 'account_number' => '', 'ifsc_code' => '',
                'lead_source' => 'Website', 'priority' => 'Hot', 'added_by' => 'Anjali Mehta', 'remarks' => 'Requested quote, rejected due to budget limitations.',
                'overall_status' => 'Closed Lost', 'created_at' => date('Y-m-d H:i:s', strtotime('-2 months'))
            ]
        ];

        $stmt = $db->prepare("INSERT INTO clients (company_name, business_type, industry_sector, gstin, pan, website, turnover, employees, contact_name, designation, mobile, whatsapp, email, alternate_email, linkedin, address_line1, address_line2, city, state, pincode, country, bank_name, account_number, ifsc_code, lead_source, priority, added_by, remarks, overall_status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $client_ids = [];
        foreach ($dummy_clients as $c) {
            $stmt->execute([
                $c['company_name'], $c['business_type'], $c['industry_sector'], $c['gstin'], $c['pan'], $c['website'], $c['turnover'], $c['employees'], $c['contact_name'], $c['designation'], $c['mobile'], $c['whatsapp'], $c['email'], $c['alternate_email'], $c['linkedin'], $c['address_line1'], $c['address_line2'], $c['city'], $c['state'], $c['pincode'], $c['country'], $c['bank_name'], $c['account_number'], $c['ifsc_code'], $c['lead_source'], $c['priority'], $c['added_by'], $c['remarks'], $c['overall_status'], $c['created_at']
            ]);
            $client_ids[$c['company_name']] = $db->lastInsertId();
        }

        // Mock Communications
        $dummy_comms = [
            [
                'client_id' => $client_ids['Apex Industries'], 'type' => 'Pitch', 'subject' => 'Introduction to Custom ERP Solutions',
                'body' => '<p>Dear Amit,</p><p>It was a pleasure speaking with you today. Here is the pitch for our ERP system...</p>',
                'cc' => 'sales@auracrm.com', 'sent_by' => 'Rahul Sharma', 'sent_at' => date('Y-m-d H:i:s', strtotime('-25 days'))
            ],
            [
                'client_id' => $client_ids['Apex Industries'], 'type' => 'PPT', 'subject' => 'Enterprise CRM Proposal Deck',
                'body' => '<p>Hi Amit,</p><p>As requested, please find attached our comprehensive presentation slides outlining features and integrations.</p>',
                'cc' => '', 'sent_by' => 'Rahul Sharma', 'sent_at' => date('Y-m-d H:i:s', strtotime('-15 days'))
            ],
            [
                'client_id' => $client_ids['Apex Industries'], 'type' => 'Custom Mail', 'subject' => 'Answering Queries on API and Hosting Security',
                'body' => '<p>Dear Amit,</p><p>Regarding your queries on security audits and compliance, here are the technical certificates...</p>',
                'cc' => '', 'sent_by' => 'Rahul Sharma', 'sent_at' => date('Y-m-d H:i:s', strtotime('-10 days'))
            ],
            [
                'client_id' => $client_ids['Zenith Retail'], 'type' => 'Pitch', 'subject' => 'Retail Automation Offer',
                'body' => '<p>Dear Priya,</p><p>Following our conversation at PlastIndia, we offer automated barcode systems...</p>',
                'cc' => '', 'sent_by' => 'Karan Singh', 'sent_at' => date('Y-m-d H:i:s', strtotime('-5 days'))
            ],
            [
                'client_id' => $client_ids['Nova Software Solutions'], 'type' => 'Pitch', 'subject' => 'Cloud Strategy Consulting Proposal',
                'body' => '<p>Dear Rohan,</p><p>Sharing the technical proposal to migrate database workloads to AWS serverless infra.</p>',
                'cc' => '', 'sent_by' => 'Rahul Sharma', 'sent_at' => date('Y-m-d H:i:s', strtotime('-40 days'))
            ],
            [
                'client_id' => $client_ids['Elite Exports'], 'type' => 'Pitch', 'subject' => 'Export Documentation App Demo',
                'body' => '<p>Dear Vikram,</p><p>Here are the workflow automation blueprints for custom billing integration.</p>',
                'cc' => '', 'sent_by' => 'Anjali Mehta', 'sent_at' => date('Y-m-d H:i:s', strtotime('-20 days'))
            ]
        ];

        $stmt_comm = $db->prepare("INSERT INTO communications (client_id, type, subject, body, cc, sent_by, sent_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
        foreach ($dummy_comms as $comm) {
            $stmt_comm->execute([$comm['client_id'], $comm['type'], $comm['subject'], $comm['body'], $comm['cc'], $comm['sent_by'], $comm['sent_at']]);
        }

        // Mock Quotations
        $dummy_quotes = [
            [
                'client_id' => $client_ids['Apex Industries'], 'quotation_number' => 'Q001', 'status' => 'Pending',
                'subtotal' => 150000.00, 'gst_amount' => 27000.00, 'total_amount' => 177000.00,
                'items_json' => json_encode([
                    ['name' => 'Custom ERP Development & Deploy', 'qty' => 1, 'rate' => 100000.00, 'gst_rate' => 18, 'total' => 118000.00],
                    ['name' => 'API Integrations & Payment Gateway setup', 'qty' => 1, 'rate' => 50000.00, 'gst_rate' => 18, 'total' => 59000.00]
                ]),
                'created_at' => date('Y-m-d H:i:s', strtotime('-8 days'))
            ],
            [
                'client_id' => $client_ids['Nova Software Solutions'], 'quotation_number' => 'Q002', 'status' => 'Approved',
                'subtotal' => 850000.00, 'gst_amount' => 153000.00, 'total_amount' => 1003000.00,
                'items_json' => json_encode([
                    ['name' => 'Cloud Optimization & Infrastructure Build', 'qty' => 1, 'rate' => 850000.00, 'gst_rate' => 18, 'total' => 1003000.00]
                ]),
                'created_at' => date('Y-m-d H:i:s', strtotime('-30 days'))
            ],
            [
                'client_id' => $client_ids['Elite Exports'], 'quotation_number' => 'Q003', 'status' => 'Rejected',
                'subtotal' => 320000.00, 'gst_amount' => 57600.00, 'total_amount' => 377600.00,
                'items_json' => json_encode([
                    ['name' => 'Logistics Dashboard & Mobile Client', 'qty' => 1, 'rate' => 320000.00, 'gst_rate' => 18, 'total' => 377600.00]
                ]),
                'created_at' => date('Y-m-d H:i:s', strtotime('-18 days'))
            ]
        ];

        $stmt_quote = $db->prepare("INSERT INTO quotations (client_id, quotation_number, status, subtotal, gst_amount, total_amount, items_json, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($dummy_quotes as $q) {
            $stmt_quote->execute([$q['client_id'], $q['quotation_number'], $q['status'], $q['subtotal'], $q['gst_amount'], $q['total_amount'], $q['items_json'], $q['created_at']]);
        }

        // Mock Activity Logs
        $dummy_activities = [
            ['description' => 'Apex Industries added by Rahul Sharma — 4 months ago', 'created_at' => date('Y-m-d H:i:s', strtotime('-4 months'))],
            ['description' => 'Zenith Retail added by Karan Singh — 3 months ago', 'created_at' => date('Y-m-d H:i:s', strtotime('-3 months'))],
            ['description' => 'Nova Software Solutions added by Rahul Sharma — 5 months ago', 'created_at' => date('Y-m-d H:i:s', strtotime('-5 months'))],
            ['description' => 'Elite Exports added by Anjali Mehta — 2 months ago', 'created_at' => date('Y-m-d H:i:s', strtotime('-2 months'))],
            ['description' => 'Matrix Logistics added by Anjali Mehta — Today 10am', 'created_at' => date('Y-m-d H:i:s', strtotime('-3 hours'))],
            ['description' => 'Email Pitch sent to Apex Industries by Rahul Sharma — 25 days ago', 'created_at' => date('Y-m-d H:i:s', strtotime('-25 days'))],
            ['description' => 'Presentation Deck (PPT) sent to Apex Industries by Rahul Sharma — 15 days ago', 'created_at' => date('Y-m-d H:i:s', strtotime('-15 days'))],
            ['description' => 'Quotation Q001 (₹1,77,000) created for Apex Industries — 8 days ago', 'created_at' => date('Y-m-d H:i:s', strtotime('-8 days'))],
            ['description' => 'Quotation Q002 approved for Nova Software Solutions — 30 days ago', 'created_at' => date('Y-m-d H:i:s', strtotime('-30 days'))],
            ['description' => 'Quotation Q003 rejected by Elite Exports — 18 days ago', 'created_at' => date('Y-m-d H:i:s', strtotime('-18 days'))],
            ['description' => 'Email Pitch sent to Zenith Retail by Karan Singh — 5 days ago', 'created_at' => date('Y-m-d H:i:s', strtotime('-5 days'))]
        ];

        $stmt_act = $db->prepare("INSERT INTO activities (description, created_at) VALUES (?, ?)");
        foreach ($dummy_activities as $act) {
            $stmt_act->execute([$act['description'], $act['created_at']]);
        }
    }
} catch (PDOException $e) {
    die("Database Initialization Failed: " . $e->getMessage());
}

// Load company profile details
$profile = $db->query("SELECT * FROM company_profile LIMIT 1")->fetch();

// ==========================================
//  PHP API ENDPOINTS (JSON responses)
// ==========================================

function return_json($data, $status = 200) {
    header('Content-Type: application/json');
    http_response_code($status);
    echo json_encode($data);
    exit;
}

if (isset($_GET['api'])) {
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
                    return_json(['success' => true, 'message' => 'Login successful']);
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

            case 'get_users':
                if (($_SESSION['role'] ?? '') !== 'Admin') return_json(['error' => 'Admin privileges required'], 403);
                $stmt = $db->query("SELECT id, username, role, is_active, created_at FROM users ORDER BY created_at ASC");
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
                $stmt = $db->prepare("DELETE FROM email_templates WHERE id = ?");
                $stmt->execute([$id]);
                return_json(['success' => true, 'message' => 'Template deleted']);
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
                
                $filename = $db->query("SELECT filename FROM presentations WHERE id = " . (int)$id)->fetchColumn();
                if ($filename && file_exists(__DIR__ . '/uploads/' . $filename)) {
                    @unlink(__DIR__ . '/uploads/' . $filename);
                }
                
                $stmt = $db->prepare("DELETE FROM presentations WHERE id = ?");
                $stmt->execute([$id]);
                return_json(['success' => true, 'message' => 'Presentation deleted']);
                break;

            case 'bulk_upload_leads':
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') return_json(['error' => 'Invalid Request'], 405);
                $leads_json = $_POST['leads_json'] ?? '[]';
                $leads = json_decode($leads_json, true);
                if (!$leads || !is_array($leads)) return_json(['error' => 'Invalid data format'], 400);

                $db->beginTransaction();
                try {
                    $stmt = $db->prepare("INSERT INTO leads (lead_name, company_name, mobile, email, lead_source, priority, stage, assigned_to, location, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    foreach ($leads as $lead) {
                        $stmt->execute([
                            $lead['lead_name'] ?? '',
                            $lead['company_name'] ?? '',
                            $lead['mobile'] ?? '',
                            $lead['email'] ?? '',
                            $lead['lead_source'] ?? 'Cold Call',
                            $lead['priority'] ?? 'Warm',
                            $lead['stage'] ?? 'New Lead',
                            $lead['assigned_to'] ?? '',
                            $lead['location'] ?? '',
                            $lead['notes'] ?? ''
                        ]);
                    }
                    $db->commit();
                    return_json(['success' => count($leads) . ' leads uploaded successfully']);
                } catch (Exception $e) {
                    $db->rollBack();
                    return_json(['error' => 'Database error: ' . $e->getMessage()], 500);
                }
                break;

            case 'save_lead':
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') return_json(['error' => 'Invalid Request'], 405);
                $lead_id     = isset($_POST['lead_id']) && (int)$_POST['lead_id'] > 0 ? (int)$_POST['lead_id'] : null;
                
                if ($lead_id && ($_SESSION['role'] ?? '') !== 'Admin') {
                    return_json(['error' => 'Admin privileges required to edit leads'], 403);
                }

                $lead_name   = trim($_POST['lead_name'] ?? '');
                $company     = trim($_POST['company_name'] ?? '');
                $mobile      = trim($_POST['mobile'] ?? '');
                $email       = trim($_POST['email'] ?? '');
                $source      = trim($_POST['lead_source'] ?? 'Cold Call');
                $assigned_to = trim($_POST['assigned_to'] ?? '');
                $priority    = trim($_POST['priority'] ?? 'Warm');
                $stage       = trim($_POST['stage'] ?? 'New Lead');
                $notes       = trim($_POST['notes'] ?? '');
                if (empty($lead_name) || empty($mobile)) return_json(['error' => 'Lead name and mobile are required'], 400);
                if ($lead_id) {
                    $stmt = $db->prepare("UPDATE leads SET lead_name=?,company_name=?,mobile=?,email=?,lead_source=?,assigned_to=?,priority=?,stage=?,notes=? WHERE id=?");
                    $stmt->execute([$lead_name,$company,$mobile,$email,$source,$assigned_to,$priority,$stage,$notes,$lead_id]);
                    log_activity("Updated lead: $lead_name");
                    return_json(['success' => true, 'message' => 'Lead updated successfully']);
                } else {
                    $stmt = $db->prepare("INSERT INTO leads (lead_name,company_name,mobile,email,lead_source,assigned_to,priority,stage,notes) VALUES (?,?,?,?,?,?,?,?,?)");
                    $stmt->execute([$lead_name,$company,$mobile,$email,$source,$assigned_to,$priority,$stage,$notes]);
                    log_activity("New lead added: $lead_name (" . ($company ?: 'Individual') . ")");
                    return_json(['success' => true, 'message' => "Lead '$lead_name' added successfully!"]);
                }
                break;

            case 'get_preleads':
                $stmt = $db->query("SELECT * FROM pre_leads ORDER BY created_at DESC");
                return_json($stmt->fetchAll());
                break;

            case 'save_prelead':
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') return_json(['error' => 'Invalid Request'], 405);
                $id = isset($_POST['id']) && (int)$_POST['id'] > 0 ? (int)$_POST['id'] : null;
                
                if ($id && ($_SESSION['role'] ?? '') !== 'Admin') {
                    return_json(['error' => 'Admin privileges required to edit pre-leads'], 403);
                }

                $name = trim($_POST['name'] ?? '');
                $company = trim($_POST['company_name'] ?? '');
                $mobile = trim($_POST['mobile'] ?? '');
                $email = trim($_POST['email'] ?? '');
                $source = trim($_POST['source'] ?? 'Unknown');
                $status = trim($_POST['status'] ?? 'Not Contacted');
                $assigned_to = trim($_POST['assigned_to'] ?? '');
                $location = trim($_POST['location'] ?? '');
                $notes = trim($_POST['notes'] ?? '');

                if (empty($name) || empty($mobile)) return_json(['error' => 'Name and Mobile are required'], 400);

                if ($id) {
                    $stmt = $db->prepare("UPDATE pre_leads SET name=?, company_name=?, mobile=?, email=?, source=?, status=?, assigned_to=?, location=?, notes=? WHERE id=?");
                    $stmt->execute([$name, $company, $mobile, $email, $source, $status, $assigned_to, $location, $notes, $id]);
                    log_activity("Updated pre-lead: $name");
                    return_json(['success' => true, 'message' => 'Pre-Lead updated!']);
                } else {
                    $stmt = $db->prepare("INSERT INTO pre_leads (name, company_name, mobile, email, source, status, assigned_to, location, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$name, $company, $mobile, $email, $source, $status, $assigned_to, $location, $notes]);
                    log_activity("Added new pre-lead: $name");
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
                log_activity("Deleted pre-lead: $prelead_name");
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
                    
                    $db->prepare("DELETE FROM pre_leads WHERE id = ?")->execute([$id]);
                    $db->commit();
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
                $db->prepare("UPDATE pre_leads SET status = ? WHERE id = ?")->execute([$status, $id]);
                return_json(['success' => true]);
                break;

            case 'bulk_upload_preleads':
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') return_json(['error' => 'Invalid Request'], 405);
                $leads_json = $_POST['leads_json'] ?? '[]';
                $leads = json_decode($leads_json, true);
                if (!$leads || !is_array($leads)) return_json(['error' => 'Invalid data format'], 400);

                $db->beginTransaction();
                try {
                    $stmt = $db->prepare("INSERT INTO pre_leads (name, company_name, mobile, email, source, status, assigned_to, location, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    foreach ($leads as $lead) {
                        $stmt->execute([
                            $lead['lead_name'] ?? '',
                            $lead['company_name'] ?? '',
                            $lead['mobile'] ?? '',
                            $lead['email'] ?? '',
                            $lead['lead_source'] ?? 'Unknown',
                            'Not Contacted',
                            $lead['assigned_to'] ?? '',
                            $lead['location'] ?? '',
                            $lead['notes'] ?? ''
                        ]);
                    }
                    $db->commit();
                    return_json(['success' => count($leads) . ' Pre-Leads uploaded successfully']);
                } catch (Exception $e) {
                    $db->rollBack();
                    return_json(['error' => 'Database error: ' . $e->getMessage()], 500);
                }
                break;

            case 'get_leads':
                $s          = $_GET['search'] ?? '';
                $f_stage    = $_GET['stage'] ?? '';
                $f_priority = $_GET['priority'] ?? '';
                $f_assigned = $_GET['assigned_to'] ?? '';
                $sql_l      = "SELECT * FROM leads WHERE 1=1";
                $params_l   = [];
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
                $db->prepare("UPDATE leads SET stage=? WHERE id=?")->execute([$stage,$id]);
                return_json(['success' => true, 'message' => 'Stage updated']);
                break;

            case 'delete_lead':
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') return_json(['error' => 'Invalid Request'], 405);
                if (($_SESSION['role'] ?? '') !== 'Admin') return_json(['error' => 'Admin privileges required to delete leads'], 403);
                $id = (int)($_POST['id'] ?? 0);
                if (!$id) return_json(['error' => 'Missing ID'], 400);
                $lead_name = $db->query("SELECT lead_name FROM leads WHERE id=$id")->fetchColumn();
                $db->prepare("DELETE FROM leads WHERE id=?")->execute([$id]);
                log_activity("Deleted lead: $lead_name");
                return_json(['success' => true, 'message' => 'Lead deleted']);
                break;

            case 'stats':
                // Total Clients
                $total_clients = $db->query("SELECT COUNT(*) FROM clients")->fetchColumn();
                
                // Emails Sent Today
                $emails_today = $db->query("SELECT COUNT(*) FROM communications WHERE date(sent_at) = date('now', 'localtime')")->fetchColumn();
                
                // Quotations This Month
                $quotes_this_month = $db->query("SELECT COUNT(*) FROM quotations WHERE strftime('%Y-%m', created_at) = strftime('%Y-%m', 'now', 'localtime')")->fetchColumn();
                
                // Pending Follow-ups (Hot leads not closed/won/lost)
                $pending_followups = $db->query("SELECT COUNT(*) FROM clients WHERE priority = 'Hot' AND overall_status IN ('New', 'Contacted', 'In Negotiation')")->fetchColumn();
                
                // Total Quotation Value (₹)
                $total_quote_value = $db->query("SELECT SUM(total_amount) FROM quotations")->fetchColumn() ?: 0;
                
                // Clients with No Quotation Yet
                $no_quotation_clients = $db->query("SELECT COUNT(*) FROM clients WHERE id NOT IN (SELECT DISTINCT client_id FROM quotations)")->fetchColumn();
                
                // Active Staff Members
                $active_staff = $db->query("SELECT COUNT(*) FROM users WHERE is_active = 1")->fetchColumn();
                // Lead stats
                $total_leads = $db->query("SELECT COUNT(*) FROM leads")->fetchColumn();
                $hot_leads   = $db->query("SELECT COUNT(*) FROM leads WHERE priority='Hot' AND stage NOT IN ('Won','Lost')")->fetchColumn();
                
                return_json([
                    'total_clients'        => (int)$total_clients,
                    'emails_today'         => (int)$emails_today,
                    'quotes_this_month'    => (int)$quotes_this_month,
                    'pending_followups'    => (int)$pending_followups,
                    'total_quote_value'    => (float)$total_quote_value,
                    'no_quotation_clients' => (int)$no_quotation_clients,
                    'active_staff'         => (int)$active_staff,
                    'total_leads'          => (int)$total_leads,
                    'hot_leads'            => (int)$hot_leads,
                ]);
                break;

            case 'charts_data':
                // 1. Client growth graph (month-wise)
                $growth_query = $db->query("SELECT strftime('%Y-%m', created_at) as month, COUNT(*) as count FROM clients GROUP BY month ORDER BY month ASC");
                $growth_data = [];
                while ($row = $growth_query->fetch()) {
                    $dateObj = DateTime::createFromFormat('!Y-m', $row['month']);
                    $monthName = $dateObj ? $dateObj->format('M Y') : $row['month'];
                    $growth_data[] = ['label' => $monthName, 'value' => (int)$row['count']];
                }
                
                // 2. Communication Funnel
                // Stages: Pitch -> PPT -> Custom Mail -> Quotation -> Closed
                $pitch_count = $db->query("SELECT COUNT(DISTINCT client_id) FROM communications WHERE type = 'Pitch'")->fetchColumn();
                $ppt_count = $db->query("SELECT COUNT(DISTINCT client_id) FROM communications WHERE type = 'PPT'")->fetchColumn();
                $mail_count = $db->query("SELECT COUNT(DISTINCT client_id) FROM communications WHERE type = 'Custom Mail'")->fetchColumn();
                $quote_count = $db->query("SELECT COUNT(DISTINCT client_id) FROM quotations")->fetchColumn();
                $closed_count = $db->query("SELECT COUNT(*) FROM clients WHERE overall_status = 'Closed Won'")->fetchColumn();
                
                $funnel = [
                    ['stage' => 'Pitch Sent', 'count' => (int)$pitch_count],
                    ['stage' => 'PPT Shared', 'count' => (int)$ppt_count],
                    ['stage' => 'Custom Mail', 'count' => (int)$mail_count],
                    ['stage' => 'Quotation Sent', 'count' => (int)$quote_count],
                    ['stage' => 'Deals Won', 'count' => (int)$closed_count]
                ];
                
                // 3. Top clients by quotation value
                $top_clients_query = $db->query("
                    SELECT c.company_name, SUM(q.total_amount) as total_val 
                    FROM clients c 
                    JOIN quotations q ON c.id = q.client_id 
                    GROUP BY c.id 
                    ORDER BY total_val DESC 
                    LIMIT 5
                ");
                $top_clients = [];
                while ($row = $top_clients_query->fetch()) {
                    $top_clients[] = ['name' => $row['company_name'], 'value' => (float)$row['total_val']];
                }
                
                // 4. This week activity summary (last 7 days activity counts)
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
                $activities = $db->query("SELECT description, created_at FROM activities ORDER BY id DESC LIMIT 200")->fetchAll();
                foreach ($activities as &$act) {
                    $act['created_at_formatted'] = date('d M Y, h:i A', strtotime($act['created_at']));
                }
                return_json($activities);
                break;

            case 'recent_activities':
                $activities = $db->query("SELECT description, created_at FROM activities ORDER BY id DESC LIMIT 10")->fetchAll();
                // Format relative time
                foreach ($activities as &$act) {
                    $timestamp = strtotime($act['created_at']);
                    $diff = time() - $timestamp;
                    
                    if ($diff < 60) {
                        $act['time_formatted'] = 'Just now';
                    } elseif ($diff < 3600) {
                        $act['time_formatted'] = round($diff / 60) . ' mins ago';
                    } elseif ($diff < 86400) {
                        $act['time_formatted'] = round($diff / 3600) . ' hrs ago';
                    } else {
                        // Check if today
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
                
                // Append quotation details and checklists
                foreach ($clients as &$c) {
                    // Communications status tracking
                    $c['pitch_sent'] = $db->query("SELECT sent_at FROM communications WHERE client_id = {$c['id']} AND type = 'Pitch' ORDER BY id DESC LIMIT 1")->fetchColumn() ?: null;
                    $c['ppt_sent'] = $db->query("SELECT sent_at FROM communications WHERE client_id = {$c['id']} AND type = 'PPT' ORDER BY id DESC LIMIT 1")->fetchColumn() ?: null;
                    $c['mail_sent'] = $db->query("SELECT sent_at FROM communications WHERE client_id = {$c['id']} AND type = 'Custom Mail' ORDER BY id DESC LIMIT 1")->fetchColumn() ?: null;
                    $c['quotation_sent'] = $db->query("SELECT created_at FROM quotations WHERE client_id = {$c['id']} ORDER BY id DESC LIMIT 1")->fetchColumn() ?: null;
                    
                    // All client quotations
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
                
                // Grab communications logs
                $comms = $db->prepare("SELECT * FROM communications WHERE client_id = ? ORDER BY id DESC");
                $comms->execute([$id]);
                $c['communications_logs'] = $comms->fetchAll();
                
                // Grab quotations
                $quotes = $db->prepare("SELECT * FROM quotations WHERE client_id = ? ORDER BY id DESC");
                $quotes->execute([$id]);
                $c['quotations'] = $quotes->fetchAll();
                
                // Communication checklist helper timestamps
                $c['pitch_sent'] = $db->query("SELECT sent_at FROM communications WHERE client_id = $id AND type = 'Pitch' ORDER BY id DESC LIMIT 1")->fetchColumn() ?: null;
                $c['ppt_sent'] = $db->query("SELECT sent_at FROM communications WHERE client_id = $id AND type = 'PPT' ORDER BY id DESC LIMIT 1")->fetchColumn() ?: null;
                $c['mail_sent'] = $db->query("SELECT sent_at FROM communications WHERE client_id = $id AND type = 'Custom Mail' ORDER BY id DESC LIMIT 1")->fetchColumn() ?: null;
                $c['quotation_sent'] = $db->query("SELECT created_at FROM quotations WHERE client_id = $id ORDER BY id DESC LIMIT 1")->fetchColumn() ?: null;
                
                return_json($c);
                break;

            case 'get_unique_filters':
                $cities = $db->query("SELECT DISTINCT city FROM clients WHERE city != '' ORDER BY city")->fetchAll(PDO::FETCH_COLUMN);
                $states = $db->query("SELECT DISTINCT state FROM clients WHERE state != '' ORDER BY state")->fetchAll(PDO::FETCH_COLUMN);
                $added_by = $db->query("SELECT DISTINCT added_by FROM clients WHERE added_by != '' ORDER BY added_by")->fetchAll(PDO::FETCH_COLUMN);
                
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
                
                // Input reading
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
                
                // Basic Validation
                if (empty($company_name) || empty($business_type) || empty($contact_name) || empty($mobile) || empty($email) || empty($address_line1) || empty($city) || empty($state) || empty($pincode) || empty($country)) {
                    return_json(['error' => 'Please fill in all mandatory fields.'], 400);
                }
                
                // Check if company exists
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
                    lead_source, priority, added_by, remarks, overall_status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'New')";
                
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    $company_name, $business_type, $industry_sector, $gstin, $pan, $website, $turnover, $employees,
                    $contact_name, $designation, $mobile, $whatsapp, $email, $alternate_email, $linkedin,
                    $address_line1, $address_line2, $city, $state, $pincode, $country,
                    $bank_name, $account_number, $ifsc_code,
                    $lead_source, $priority, $added_by, $remarks
                ]);
                
                // Log Action
                $action_desc = "{$company_name} added by {$added_by} — Today " . date('h:i A');
                log_activity($action_desc);
                
                return_json(['success' => true, 'message' => 'Client profile locked & registered successfully!']);
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
                
                // Check if client exists
                $client_query = $db->prepare("SELECT company_name, email, overall_status FROM clients WHERE id = ?");
                $client_query->execute([$client_id]);
                $client = $client_query->fetch();
                
                if (!$client) {
                    return_json(['error' => 'Recipient Client not found.'], 404);
                }
                
                // Handle optional file attachment upload
                $attachment_name = null;
                $template_id = $_POST['template_id'] ?? null;
                $ppt_id = $_POST['ppt_id'] ?? null;

                if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
                    $file_tmp = $_FILES['attachment']['tmp_name'];
                    $orig_name = basename($_FILES['attachment']['name']);
                    $file_ext = pathinfo($orig_name, PATHINFO_EXTENSION);
                    // Safe name
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
                    // Use template's attachment if available
                    $tmpl = $db->prepare("SELECT attachment_name FROM email_templates WHERE id = ?");
                    $tmpl->execute([$template_id]);
                    $tmpl_res = $tmpl->fetch();
                    if ($tmpl_res && !empty($tmpl_res['attachment_name'])) {
                        $attachment_name = $tmpl_res['attachment_name'];
                    }
                }
                
                // Insert Communication record
                $stmt = $db->prepare("INSERT INTO communications (client_id, type, subject, body, cc, attachment_name, sent_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$client_id, $type, $subject, $body, $cc, $attachment_name, $sent_by]);
                
                // Status Transition Automation
                $new_status = $client['overall_status'];
                if ($client['overall_status'] === 'New') {
                    $new_status = 'Contacted';
                    $upd = $db->prepare("UPDATE clients SET overall_status = 'Contacted' WHERE id = ?");
                    $upd->execute([$client_id]);
                }
                
                // Log activity feed
                $act_desc = "{$type} email sent to {$client['company_name']} by {$sent_by} — " . date('h:i A');
                log_activity($act_desc);
                
                return_json([
                    'success' => true, 
                    'message' => 'Email simulated & tracker updated!', 
                    'new_status' => $new_status,
                    'attachment' => $attachment_name
                ]);
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
                
                // Query client name
                $c_name = $db->query("SELECT company_name FROM clients WHERE id = $client_id")->fetchColumn();
                if (!$c_name) {
                    return_json(['error' => 'Client not found'], 404);
                }
                
                // Auto-generate Q### format ID
                $max_id = $db->query("SELECT MAX(id) FROM quotations")->fetchColumn() ?: 0;
                $next_num = $max_id + 1;
                $quotation_number = "Q" . str_pad($next_num, 3, '0', STR_PAD_LEFT);
                
                $stmt = $db->prepare("INSERT INTO quotations (client_id, quotation_number, status, subtotal, gst_amount, total_amount, items_json) VALUES (?, ?, 'Pending', ?, ?, ?, ?)");
                $stmt->execute([$client_id, $quotation_number, $subtotal, $gst_amount, $total_amount, $items_json]);
                
                // Update client communication state -> Negotiation (If New or Contacted)
                $current_status = $db->query("SELECT overall_status FROM clients WHERE id = $client_id")->fetchColumn();
                if (in_array($current_status, ['New', 'Contacted'])) {
                    $db->query("UPDATE clients SET overall_status = 'In Negotiation' WHERE id = $client_id");
                }
                
                // Write Activity logs
                $act_desc = "Quotation {$quotation_number} (₹" . number_format($total_amount, 2, '.', ',') . ") drafted for {$c_name}";
                log_activity($act_desc);
                
                return_json(['success' => true, 'quotation_number' => $quotation_number, 'message' => "Quotation {$quotation_number} saved successfully!"]);
                break;

            case 'update_quotation_status':
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                    return_json(['error' => 'Invalid Request Method'], 405);
                }
                
                $quote_id = (int)$_POST['quote_id'];
                $status = $_POST['status']; // 'Pending', 'Approved', 'Rejected'
                
                if (!in_array($status, ['Pending', 'Approved', 'Rejected'])) {
                    return_json(['error' => 'Invalid status option.'], 400);
                }
                
                $q_info = $db->query("SELECT q.quotation_number, q.client_id, c.company_name FROM quotations q JOIN clients c ON q.client_id = c.id WHERE q.id = $quote_id")->fetch();
                if (!$q_info) {
                    return_json(['error' => 'Quotation not found.'], 404);
                }
                
                // Update quote status
                $stmt = $db->prepare("UPDATE quotations SET status = ? WHERE id = ?");
                $stmt->execute([$status, $quote_id]);
                
                // Automatically adjust client status
                $client_status = 'In Negotiation';
                if ($status === 'Approved') {
                    $client_status = 'Closed Won';
                } elseif ($status === 'Rejected') {
                    // Check if they have another approved quotation
                    $other_approved = $db->query("SELECT COUNT(*) FROM quotations WHERE client_id = {$q_info['client_id']} AND status = 'Approved'")->fetchColumn();
                    $client_status = ($other_approved > 0) ? 'Closed Won' : 'Closed Lost';
                }
                
                $db->query("UPDATE clients SET overall_status = '$client_status' WHERE id = {$q_info['client_id']}");
                
                // Insert activity log
                $act_desc = "Quotation {$q_info['quotation_number']} updated to {$status} for {$q_info['company_name']}";
                log_activity($act_desc);
                
                return_json(['success' => true, 'message' => 'Status updated successfully', 'client_status' => $client_status]);
                break;

            case 'quotation_list':
                $search = isset($_GET['search']) ? trim($_GET['search']) : '';
                $status = isset($_GET['status']) ? trim($_GET['status']) : '';
                
                $sql = "SELECT q.*, c.company_name, c.email as client_email, c.city, c.state FROM quotations q JOIN clients c ON q.client_id = c.id WHERE 1=1";
                $params = [];
                
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
                
                // Summary calculations
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
                
                // SMTP Fields
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
                
                // Log activity
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
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AuraCRM - Professional Client Management System</title>
    
    <!-- Google Fonts Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Lucide Icons for clean iconography -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Quill Snow Rich Text Editor Stylesheet & Script -->
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
    
    <!-- HTML to PDF conversion bundle library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    
    <!-- SheetJS for Bulk Upload Parsing -->
    <script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>
    
    <!-- Vanilla CSS Design System -->
    <style>
        :root {
            /* Orange & Slate Premium Palette */
            --primary: #f97316;
            --primary-hover: #ea580c;
            --primary-light: #fff7ed;
            --primary-border: #ffedd5;
            --primary-glow: rgba(249, 115, 22, 0.15);
            
            --bg-main: #f8fafc;
            --bg-card: #ffffff;
            --sidebar-bg: #0f172a;
            --sidebar-hover: #1e293b;
            
            --text-primary: #1e293b;
            --text-muted: #64748b;
            --text-light: #94a3b8;
            
            /* Status Accents */
            --status-new: #3b82f6;
            --status-new-light: #eff6ff;
            --status-contacted: #f97316;
            --status-contacted-light: #fff7ed;
            --status-negotiation: #f59e0b;
            --status-negotiation-light: #fef3c7;
            --status-won: #10b981;
            --status-won-light: #ecfdf5;
            --status-lost: #ef4444;
            --status-lost-light: #fef2f2;
            
            --radius-sm: 6px;
            --radius-md: 10px;
            --radius-lg: 16px;
            
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
            --shadow-lg: 0 10px 15px -3px rgba(15, 23, 42, 0.05), 0 4px 6px -4px rgba(15, 23, 42, 0.05);
            
            --transition-fast: 0.15s ease;
            --transition-normal: 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        body {
            background-color: var(--bg-main);
            color: var(--text-primary);
            display: flex;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Sidebar Navigation Layout */
        aside {
            width: 260px;
            background-color: var(--sidebar-bg);
            color: #ffffff;
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            transition: var(--transition-normal);
        }

        .brand-container {
            padding: 24px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .brand-logo {
            background-color: var(--primary);
            color: white;
            width: 38px;
            height: 38px;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 20px;
            box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3);
        }

        .brand-name {
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            font-size: 21px;
            letter-spacing: -0.5px;
            background: linear-gradient(135deg, #ffffff 30%, #ffedd5 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .sidebar-menu {
            list-style: none;
            padding: 24px 12px;
            display: flex;
            flex-direction: column;
            gap: 6px;
            flex-grow: 1;
        }

        .menu-item a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: var(--text-light);
            text-decoration: none;
            border-radius: var(--radius-md);
            font-size: 14px;
            font-weight: 500;
            transition: var(--transition-fast);
        }

        .menu-item a:hover {
            color: white;
            background-color: var(--sidebar-hover);
        }

        .menu-item.active a {
            background-color: var(--primary);
            color: white;
            box-shadow: 0 4px 14px rgba(249, 115, 22, 0.25);
        }

        .sidebar-footer {
            padding: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            font-size: 12px;
            color: var(--text-light);
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        /* Main Workspace Container */
        main {
            margin-left: 260px;
            flex-grow: 1;
            padding: 32px;
            min-width: 0; /* prevents flex blowout */
            transition: var(--transition-normal);
        }

        header.main-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 28px;
        }

        .page-title h1 {
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            font-size: 26px;
            color: var(--text-primary);
        }

        .page-title p {
            font-size: 14px;
            color: var(--text-muted);
            margin-top: 4px;
        }

        .user-pill {
            display: flex;
            align-items: center;
            gap: 10px;
            background: white;
            padding: 8px 16px;
            border-radius: 30px;
            box-shadow: var(--shadow-sm);
            border: 1px solid #e2e8f0;
            font-size: 14px;
            font-weight: 500;
        }

        .user-avatar {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background-color: var(--primary-light);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 12px;
        }

        /* View State Management */
        .view-container {
            display: none;
            animation: fadeIn 0.3s ease-in-out;
        }

        .view-container.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Dashboard Stats Grid styling */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background-color: var(--bg-card);
            padding: 24px;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            border: 1px solid #f1f5f9;
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;
            transition: transform var(--transition-fast), box-shadow var(--transition-fast);
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary-border);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background-color: var(--primary);
            opacity: 0;
            transition: var(--transition-fast);
        }

        .stat-card:hover::before {
            opacity: 1;
        }

        .stat-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .stat-label {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-icon-wrapper {
            background-color: var(--primary-light);
            color: var(--primary);
            width: 36px;
            height: 36px;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stat-value {
            font-family: 'Outfit', sans-serif;
            font-size: 28px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 4px;
        }

        /* Dashboard Charts and Feeds Layout */
        .dashboard-layout-row {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 24px;
            margin-bottom: 24px;
        }

        .charts-row-inner {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 24px;
        }

        .card {
            background-color: var(--bg-card);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            border: 1px solid #f1f5f9;
            padding: 24px;
            margin-bottom: 24px;
        }

        .card-title-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .card-title-bar h2 {
            font-family: 'Outfit', sans-serif;
            font-size: 18px;
            font-weight: 600;
            color: var(--text-primary);
        }

        /* Funnel CSS styling */
        .funnel-container {
            display: flex;
            flex-direction: column;
            gap: 12px;
            padding: 10px 0;
        }

        .funnel-stage {
            display: flex;
            align-items: center;
            position: relative;
        }

        .funnel-label {
            width: 110px;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-primary);
        }

        .funnel-bar-wrapper {
            flex-grow: 1;
            background: #f1f5f9;
            height: 24px;
            border-radius: var(--radius-sm);
            overflow: hidden;
            position: relative;
        }

        .funnel-bar {
            height: 100%;
            background: linear-gradient(90deg, #f97316 0%, #ea580c 100%);
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding-right: 12px;
            transition: width 1s ease-in-out;
        }

        .funnel-count {
            color: white;
            font-size: 11px;
            font-weight: 700;
        }

        /* Activity Feed styling */
        .activity-feed {
            display: flex;
            flex-direction: column;
            gap: 16px;
            max-height: 480px;
            overflow-y: auto;
        }

        .activity-item {
            display: flex;
            gap: 12px;
            padding-bottom: 14px;
            border-bottom: 1px dashed #e2e8f0;
        }

        .activity-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .activity-bullet {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: var(--primary);
            margin-top: 5px;
            flex-shrink: 0;
        }

        .activity-content {
            flex-grow: 1;
        }

        .activity-text {
            font-size: 13px;
            line-height: 1.5;
            color: var(--text-primary);
        }

        .activity-time {
            font-size: 11px;
            color: var(--text-light);
            margin-top: 4px;
        }

        /* Client Registration Form */
        .form-section-title {
            font-family: 'Outfit', sans-serif;
            font-size: 15px;
            font-weight: 700;
            color: var(--primary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 20px 0 12px 0;
            padding-bottom: 6px;
            border-bottom: 2px solid var(--primary-border);
        }

        .form-section-title:first-of-type {
            margin-top: 0;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 18px;
            margin-bottom: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        label {
            font-size: 13px;
            font-weight: 500;
            color: var(--text-primary);
        }

        label.required::after {
            content: ' *';
            color: var(--danger);
        }

        input[type="text"],
        input[type="email"],
        input[type="number"],
        input[type="url"],
        select,
        textarea {
            padding: 10px 14px;
            border: 1px solid #cbd5e1;
            border-radius: var(--radius-md);
            font-size: 14px;
            color: var(--text-primary);
            background-color: #ffffff;
            transition: border-color var(--transition-fast), box-shadow var(--transition-fast);
            width: 100%;
        }

        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-glow);
        }

        .badge-locked {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background-color: var(--primary-light);
            color: var(--primary);
            padding: 6px 12px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 600;
            border: 1px solid var(--primary-border);
            margin-left: auto;
        }

        .header-action-container {
            display: flex;
            align-items: center;
            gap: 12px;
            width: 100%;
        }

        /* Buttons custom styling */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 18px;
            border-radius: var(--radius-md);
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: var(--transition-fast);
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
        }

        .btn-secondary {
            background-color: #f1f5f9;
            color: var(--text-primary);
            border: 1px solid #cbd5e1;
        }

        .btn-secondary:hover {
            background-color: #e2e8f0;
        }

        .btn-accent {
            background-color: var(--sidebar-bg);
            color: white;
        }

        .btn-accent:hover {
            background-color: var(--sidebar-hover);
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 24px;
            padding-top: 18px;
            border-top: 1px solid #e2e8f0;
        }

        /* Complete Tracking System (Module 2) */
        .crm-search-bar {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
        }

        .search-input-wrapper {
            position: relative;
            flex-grow: 1;
        }

        .search-input-wrapper input {
            padding-left: 42px;
        }

        .search-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
            pointer-events: none;
        }

        .filters-toggle-btn {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            padding: 0 14px;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--text-primary);
            transition: var(--transition-fast);
        }

        .filters-toggle-btn:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        .filters-drawer {
            display: none;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 14px;
            background: #f1f5f9;
            padding: 16px;
            border-radius: var(--radius-md);
            margin-bottom: 20px;
            border: 1px solid #e2e8f0;
        }

        .filters-drawer.active {
            display: grid;
        }

        /* CRM Two Column Layout */
        .crm-layout {
            display: grid;
            grid-template-columns: 400px 1fr;
            gap: 24px;
            align-items: start;
        }

        .client-list-pane {
            max-height: 650px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 12px;
            padding-right: 4px;
        }

        .client-card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: var(--radius-md);
            padding: 16px;
            cursor: pointer;
            transition: var(--transition-fast);
            position: relative;
        }

        .client-card:hover {
            border-color: var(--primary);
            box-shadow: var(--shadow-sm);
        }

        .client-card.selected {
            border-color: var(--primary);
            background-color: var(--primary-light);
            box-shadow: var(--shadow-md);
        }

        .client-card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 8px;
        }

        .client-card-title {
            font-size: 15px;
            font-weight: 700;
            color: var(--text-primary);
        }

        .client-card-meta {
            font-size: 12px;
            color: var(--text-muted);
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .badge {
            font-size: 11px;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 12px;
            text-transform: uppercase;
        }

        .badge-hot { background-color: var(--status-lost-light); color: var(--status-lost); }
        .badge-warm { background-color: var(--status-negotiation-light); color: var(--status-negotiation); }
        .badge-cold { background-color: var(--status-new-light); color: var(--status-new); }

        .badge-status {
            font-size: 11px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 30px;
        }
        .badge-status.new { background-color: var(--status-new-light); color: var(--status-new); }
        .badge-status.contacted { background-color: var(--status-contacted-light); color: var(--status-contacted); }
        .badge-status.negotiation { background-color: var(--status-negotiation-light); color: var(--status-negotiation); }
        .badge-status.won { background-color: var(--status-won-light); color: var(--status-won); }
        .badge-status.lost { background-color: var(--status-lost-light); color: var(--status-lost); }

        /* Detail Profile Pane */
        .client-detail-pane {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: var(--radius-lg);
            padding: 24px;
            min-height: 500px;
            position: sticky;
            top: 24px;
        }

        .detail-placeholder {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 400px;
            color: var(--text-light);
            text-align: center;
            gap: 16px;
        }

        .detail-placeholder i {
            color: var(--text-light);
        }

        .detail-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }

        .detail-company-title {
            font-family: 'Outfit', sans-serif;
            font-size: 22px;
            font-weight: 800;
            color: var(--text-primary);
        }

        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 24px;
        }

        .detail-block-title {
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-muted);
            margin-bottom: 10px;
        }

        .detail-field {
            margin-bottom: 8px;
            font-size: 13.5px;
        }

        .detail-field span {
            font-weight: 600;
            color: var(--text-primary);
        }

        .detail-field label {
            color: var(--text-muted);
            font-weight: 400;
            display: inline-block;
            width: 120px;
        }

        /* Interactive Checklist for pipeline tracking */
        .pipeline-tracker {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin: 20px 0;
            background: #f8fafc;
            padding: 16px;
            border-radius: var(--radius-md);
            border: 1px solid #e2e8f0;
        }

        .pipeline-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            gap: 6px;
            position: relative;
        }

        .pipeline-icon-circle {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #e2e8f0;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            z-index: 2;
        }

        .pipeline-step.completed .pipeline-icon-circle {
            background-color: var(--success);
            color: white;
        }

        .pipeline-step.active .pipeline-icon-circle {
            background-color: var(--primary);
            color: white;
            box-shadow: 0 0 0 4px var(--primary-glow);
        }

        .pipeline-step-label {
            font-size: 11px;
            font-weight: 700;
            color: var(--text-primary);
        }

        .pipeline-step-date {
            font-size: 10px;
            color: var(--text-muted);
        }

        /* Detail Section Tabs */
        .tabs {
            display: flex;
            border-bottom: 1px solid #e2e8f0;
            margin-bottom: 16px;
            gap: 16px;
        }

        .tab {
            padding: 8px 4px 12px 4px;
            font-size: 14px;
            font-weight: 600;
            color: var(--text-muted);
            cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: var(--transition-fast);
        }

        .tab.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        /* History Logs list view */
        .history-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
            max-height: 250px;
            overflow-y: auto;
        }

        .history-item {
            background: #f8fafc;
            padding: 12px;
            border-radius: var(--radius-md);
            border-left: 3px solid var(--primary);
        }

        .history-item-header {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .history-item-body {
            font-size: 12.5px;
            color: var(--text-primary);
            line-height: 1.4;
        }

        /* Email dispatcher custom styling */
        .email-form-card {
            background: white;
            padding: 24px;
            border-radius: var(--radius-lg);
            border: 1px solid #e2e8f0;
        }

        /* Editor height setup */
        #email-body-editor {
            height: 220px;
            border-bottom-left-radius: var(--radius-md);
            border-bottom-right-radius: var(--radius-md);
            font-family: 'Inter', sans-serif;
        }

        .ql-toolbar {
            border-top-left-radius: var(--radius-md);
            border-top-right-radius: var(--radius-md);
        }

        /* Quotation Builder Layout */
        .quote-builder-layout {
            background: white;
            border-radius: var(--radius-lg);
            border: 1px solid #e2e8f0;
            padding: 24px;
        }

        .quote-meta-row {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 18px;
            margin-bottom: 24px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .items-table th {
            text-align: left;
            padding: 12px;
            background-color: #f8fafc;
            font-size: 12px;
            font-weight: 700;
            color: var(--text-muted);
            border-bottom: 2px solid #e2e8f0;
            text-transform: uppercase;
        }

        .items-table td {
            padding: 10px;
            border-bottom: 1px solid #e2e8f0;
        }

        .items-table input,
        .items-table select {
            padding: 8px 10px;
        }

        .delete-row-btn {
            background: transparent;
            border: none;
            color: var(--danger);
            cursor: pointer;
            padding: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .delete-row-btn:hover {
            background-color: var(--danger-light);
            border-radius: 4px;
        }

        .summary-block-wrapper {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
        }

        .summary-block {
            width: 320px;
            background-color: #f8fafc;
            padding: 16px;
            border-radius: var(--radius-md);
            border: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            font-size: 13.5px;
        }

        .summary-row.grand-total {
            font-size: 16px;
            font-weight: 700;
            border-top: 1px solid #cbd5e1;
            padding-top: 8px;
            color: var(--primary);
        }

        /* Quotation List design */
        .quotation-list-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
        }

        .quotation-list-table th {
            text-align: left;
            padding: 14px;
            background: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
            font-size: 12px;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
        }

        .quotation-list-table td {
            padding: 14px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 13.5px;
        }

        .status-pill-select {
            border: 1px solid #cbd5e1;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            background-color: white;
            cursor: pointer;
        }

        .status-pill-select:focus {
            outline: none;
        }

        .status-pill-select.Pending { border-color: var(--warning); color: var(--warning); background-color: var(--warning-light); }
        .status-pill-select.Approved { border-color: var(--success); color: var(--success); background-color: var(--status-won-light); }
        .status-pill-select.Rejected { border-color: var(--danger); color: var(--danger); background-color: var(--danger-light); }

        /* Notification Popup system */
        .toast-container {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .toast {
            background: #1e293b;
            color: white;
            padding: 14px 20px;
            border-radius: var(--radius-md);
            font-size: 13.5px;
            font-weight: 500;
            box-shadow: var(--shadow-lg);
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideInRight 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            min-width: 280px;
            border-left: 4px solid var(--primary);
        }

        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        /* Printable PDF Template container */
        #invoice-print-container {
            display: none;
        }

        /* Media Queries for Responsive adjustments */
        @media (max-width: 1024px) {
            aside {
                width: 70px;
                overflow: hidden;
            }
            aside .brand-name,
            aside .sidebar-footer,
            aside .menu-text {
                display: none;
            }
            aside .brand-container {
                padding: 16px;
                justify-content: center;
            }
            main {
                margin-left: 70px;
                padding: 20px;
            }
            .dashboard-layout-row {
                grid-template-columns: 1fr;
            }
            .crm-layout {
                grid-template-columns: 1fr;
            }
            .client-detail-pane {
                position: relative;
                top: 0;
            }
        }

        @media (max-width: 768px) {
            .charts-row-inner {
                grid-template-columns: 1fr;
            }
            .quote-meta-row {
                grid-template-columns: 1fr;
            }
        }
        /* Modal Styles */
        .modal {
            display: none; 
            position: fixed; 
            z-index: 1000; 
            left: 0; 
            top: 0; 
            width: 100%; 
            height: 100%; 
            overflow: auto; 
            background-color: rgba(0,0,0,0.5); 
            align-items: center; 
            justify-content: center;
        }
        .modal-content {
            background-color: var(--bg-card);
            margin: 10% auto;
            padding: 30px;
            border: 1px solid var(--border);
            width: 90%;
            max-width: 550px;
            border-radius: 16px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.3);
            position: relative;
            animation: modalFadeIn 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
        @keyframes modalFadeIn {
            from {opacity: 0; transform: translateY(-20px);}
            to {opacity: 1; transform: translateY(0);}
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border);
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .modal-header h2 {
            margin: 0;
            font-size: 18px;
            color: var(--text);
        }
        .close {
            color: #aaa;
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover, .close:focus {
            color: var(--danger);
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
<?php if (!isset($_SESSION['user_id'])): ?>
    <!-- LOGIN SCREEN -->
    <div style="display: flex; height: 100vh; align-items: center; justify-content: center; background: var(--bg-color);">
        <div class="card" style="width: 100%; max-width: 400px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
            <div class="brand-container" style="justify-content: center; margin-bottom: 2rem;">
                <div class="brand-logo">A</div>
                <div class="brand-name" style="color: var(--text);">AuraCRM</div>
            </div>
            <h2 style="text-align: center; margin-bottom: 1.5rem;">Welcome Back</h2>
            <form id="login-form" onsubmit="handleLogin(event)">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" required autofocus>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 10px;">Secure Login</button>
            </form>
            <script>
                function handleLogin(e) {
                    e.preventDefault();
                    const fd = new FormData(e.target);
                    fetch('?api=login', {method: 'POST', body: fd})
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) location.reload();
                        else alert(data.error);
                    });
                }
            </script>
        </div>
    </div>
<?php else: ?>
    <!-- Sidebar Navigation -->
    <aside>
        <div class="brand-container">
            <div class="brand-logo">A</div>
            <div class="brand-name">AuraCRM</div>
        </div>
        <ul class="sidebar-menu">
            <li class="menu-item active" data-view="dashboard">
                <a href="#dashboard">
                    <i data-lucide="layout-dashboard"></i>
                    <span class="menu-text">Dashboard</span>
                </a>
            </li>
            <li class="menu-item" data-view="preleads">
                <a href="#preleads">
                    <i data-lucide="inbox"></i>
                    <span class="menu-text">Pre-Leads (Raw Data)</span>
                </a>
            </li>
            <li class="menu-item" data-view="leads">
                <a href="#leads">
                    <i data-lucide="target"></i>
                    <span class="menu-text">Lead Management</span>
                </a>
            </li>
            <li class="menu-item" data-view="add-client">
                <a href="#add-client">
                    <i data-lucide="user-plus"></i>
                    <span class="menu-text">Add Client</span>
                </a>
            </li>
            <li class="menu-item" data-view="search-crm">
                <a href="#search-crm">
                    <i data-lucide="search"></i>
                    <span class="menu-text">Search & CRM Track</span>
                </a>
            </li>
            <li class="menu-item" data-view="send-email">
                <a href="#send-email">
                    <i data-lucide="mail"></i>
                    <span class="menu-text">Send Email</span>
                </a>
            </li>
            <li class="menu-item" data-view="create-quotation">
                <a href="#create-quotation">
                    <i data-lucide="file-plus"></i>
                    <span class="menu-text">Quotation Builder</span>
                </a>
            </li>
            <li class="menu-item" data-view="quotation-list">
                <a href="#quotation-list">
                    <i data-lucide="file-text"></i>
                    <span class="menu-text">Quotation List</span>
                </a>
            </li>
            <?php if (($_SESSION['role'] ?? '') === 'Admin'): ?>
              <li class="menu-item" data-view="activity-logs">
                  <a href="#activity-logs">
                      <i data-lucide="activity"></i>
                      <span class="menu-text">Activity Logs</span>
                  </a>
              </li>
              <?php endif; ?>
              <li class="menu-item" data-view="settings">
                <a href="#settings">
                    <i data-lucide="settings"></i>
                    <span class="menu-text">CRM Settings</span>
                </a>
            </li>
        </ul>
        <div class="sidebar-footer">
            <div>Logged in as:</div>
            <strong id="sidebar-user-name" style="color: white;"><?php echo htmlspecialchars($_SESSION['username']); ?></strong>
            <span style="font-size: 10px; color: var(--text-light); margin-top: 4px; display:block;">Role: <?php echo htmlspecialchars($_SESSION['role']); ?></span>
            <button onclick="logout()" class="btn btn-secondary" style="width: 100%; margin-top: 10px; padding: 5px; font-size: 12px; background: transparent; border: 1px solid var(--border); color: var(--text-light);">Logout</button>
            <script>
                function logout() {
                    fetch('?api=logout').then(() => location.reload());
                }
            </script>
        </div>
    </aside>

    <!-- Main Content Workspace -->
    <main>
        
        <!-- Header -->
        <header class="main-header">
            <div class="page-title">
                <h1 id="view-title">Dashboard</h1>
                <p id="view-subtitle">AuraCRM Operations Control Panel</p>
            </div>
            <div class="user-pill">
                <div class="user-avatar" id="header-user-avatar"><?php 
                    $names = explode(' ', $profile['contact_person']);
                    $initials = '';
                    foreach ($names as $n) {
                        if (!empty($n)) $initials .= strtoupper(substr($n, 0, 1));
                    }
                    echo htmlspecialchars(substr($initials, 0, 2));
                ?></div>
                <span id="header-user-name"><?php echo htmlspecialchars($profile['contact_person']); ?></span>
            </div>
        </header>

        <!-- ==========================================
              VIEW 1: DASHBOARD LANDING SCREEN
             ========================================== -->
        <div id="view-dashboard" class="view-container active">
            <!-- Stats row -->
            <div class="stats-grid">
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin'): ?>
                <div class="stat-card" style="border-left: 4px solid var(--primary); cursor: pointer;" onclick="document.querySelector('.menu-item[data-view=\'settings\']').click();">
                    <div class="stat-card-header">
                        <span class="stat-label">Active Staff</span>
                        <div class="stat-icon-wrapper" style="background: var(--primary-light); color: var(--primary);"><i data-lucide="shield-check"></i></div>
                    </div>
                    <div class="stat-value" id="stat-active-staff">-</div>
                    <span style="font-size: 11px; color: var(--text-muted);">Click to manage users</span>
                </div>
                <?php endif; ?>
                <div class="stat-card">
                    <div class="stat-card-header">
                        <span class="stat-label">Total Clients</span>
                        <div class="stat-icon-wrapper"><i data-lucide="users"></i></div>
                    </div>
                    <div class="stat-value" id="stat-total-clients">-</div>
                    <span style="font-size: 11px; color: var(--text-muted);">Database registered accounts</span>
                </div>
                <div class="stat-card">
                    <div class="stat-card-header">
                        <span class="stat-label">Emails Sent Today</span>
                        <div class="stat-icon-wrapper"><i data-lucide="send"></i></div>
                    </div>
                    <div class="stat-value" id="stat-emails-today">-</div>
                    <span style="font-size: 11px; color: var(--text-muted);">Dispatched communications</span>
                </div>
                <div class="stat-card">
                    <div class="stat-card-header">
                        <span class="stat-label">Quotations This Month</span>
                        <div class="stat-icon-wrapper"><i data-lucide="file-check"></i></div>
                    </div>
                    <div class="stat-value" id="stat-quotes-month">-</div>
                    <span style="font-size: 11px; color: var(--text-muted);">Drafted proposal invoices</span>
                </div>
                <div class="stat-card">
                    <div class="stat-card-header">
                        <span class="stat-label">Pending Follow-ups</span>
                        <div class="stat-icon-wrapper" style="background-color: var(--status-lost-light); color: var(--status-lost);"><i data-lucide="clock"></i></div>
                    </div>
                    <div class="stat-value" id="stat-pending-followups">-</div>
                    <span style="font-size: 11px; color: var(--status-lost); font-weight: 500;">Hot Leads requiring follow-up</span>
                </div>
                <div class="stat-card">
                    <div class="stat-card-header">
                        <span class="stat-label">Total Quotation Value</span>
                        <div class="stat-icon-wrapper"><i data-lucide="indian-rupee"></i></div>
                    </div>
                    <div class="stat-value" id="stat-total-val">-</div>
                    <span style="font-size: 11px; color: var(--text-muted);">Sum of generated quotes</span>
                </div>
                <div class="stat-card">
                    <div class="stat-card-header">
                        <span class="stat-label">No Quotations Yet</span>
                        <div class="stat-icon-wrapper" style="background-color: var(--status-negotiation-light); color: var(--status-negotiation);"><i data-lucide="alert-circle"></i></div>
                    </div>
                    <div class="stat-value" id="stat-no-quotation">-</div>
                    <span style="font-size: 11px; color: var(--text-muted);">Clients at introductory stage</span>
                </div>
            </div>

            <!-- Dashboard Row 2: Charts and Activities -->
            <div class="dashboard-layout-row">
                <!-- Left panel: Grid of Charts -->
                <div>
                    <div class="charts-row-inner">
                        <!-- Growth Line Chart -->
                        <div class="card" style="margin-bottom: 0;">
                            <div class="card-title-bar">
                                <h2>Client Growth (Month-wise)</h2>
                                <i data-lucide="trending-up" style="color: var(--primary);"></i>
                            </div>
                            <div style="height: 220px; position: relative;">
                                <canvas id="chart-client-growth"></canvas>
                            </div>
                        </div>
                        
                        <!-- Communication Conversion Funnel -->
                        <div class="card" style="margin-bottom: 0;">
                            <div class="card-title-bar">
                                <h2>Communication Funnel</h2>
                                <i data-lucide="filter" style="color: var(--primary);"></i>
                            </div>
                            <div class="funnel-container" id="funnel-visualization-container">
                                <!-- Generated Dynamically by JS -->
                            </div>
                        </div>
                    </div>
                    
                    <div class="charts-row-inner" style="margin-top: 24px; margin-bottom: 0;">
                        <!-- Top Clients by quotation value -->
                        <div class="card" style="margin-bottom: 0;">
                            <div class="card-title-bar">
                                <h2>Top Clients by Quotation Value</h2>
                                <i data-lucide="award" style="color: var(--primary);"></i>
                            </div>
                            <div style="height: 220px; position: relative;">
                                <canvas id="chart-top-clients"></canvas>
                            </div>
                        </div>

                        <!-- Weekly activities counts -->
                        <div class="card" style="margin-bottom: 0;">
                            <div class="card-title-bar">
                                <h2>Weekly CRM Interactions</h2>
                                <i data-lucide="calendar" style="color: var(--primary);"></i>
                            </div>
                            <div style="height: 220px; position: relative;">
                                <canvas id="chart-weekly-activity"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right panel: Activity Log -->
                <div class="card" style="margin-bottom: 0;">
                    <div class="card-title-bar">
                        <h2>Recent Activity Feed</h2>
                        <i data-lucide="bell" style="color: var(--primary);"></i>
                    </div>
                    <div class="activity-feed" id="dashboard-activity-feed">
                        <!-- Loaded via API -->
                    </div>
                </div>
            </div>
        </div>

        <!-- ==========================================
              VIEW 2: CLIENT REGISTRATION FORM
             ========================================== -->
        <div id="view-add-client" class="view-container">
            <form id="client-registration-form" onsubmit="saveClient(event)">
                <div class="card">
                    <div class="card-title-bar">
                        <h2>Add New Client Account</h2>
                        <div class="badge-locked"><i data-lucide="lock"></i> Locked Entry — Permanent Record</div>
                    </div>
                    
                    <!-- Business Information -->
                    <div class="form-section-title">🏢 Business Information</div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="required">Business / Company Name</label>
                            <input type="text" name="company_name" placeholder="e.g. Acme Corporation" required>
                        </div>
                        <div class="form-group">
                            <label class="required">Business Type</label>
                            <select name="business_type" required>
                                <option value="" disabled selected>Select Business Type</option>
                                <option value="Manufacturer">Manufacturer</option>
                                <option value="Trader">Trader</option>
                                <option value="Retailer">Retailer</option>
                                <option value="Service">Service Provider</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Industry Sector</label>
                            <input type="text" name="industry_sector" placeholder="e.g. Automobile, Chemical">
                        </div>
                        <div class="form-group">
                            <label>GSTIN Number</label>
                            <input type="text" name="gstin" placeholder="15-digit GSTIN ID" minlength="15" maxlength="15">
                        </div>
                        <div class="form-group">
                            <label>PAN Number</label>
                            <input type="text" name="pan" placeholder="10-digit PAN ID" minlength="10" maxlength="10">
                        </div>
                        <div class="form-group">
                            <label>Website URL</label>
                            <input type="url" name="website" placeholder="https://example.com">
                        </div>
                        <div class="form-group">
                            <label>Annual Turnover (approx.)</label>
                            <select name="turnover">
                                <option value="" disabled selected>Select Turnover Tier</option>
                                <option value="Under ₹1 Crore">Under ₹1 Crore</option>
                                <option value="₹1-5 Crores">₹1-5 Crores</option>
                                <option value="₹5-10 Crores">₹5-10 Crores</option>
                                <option value="₹10-25 Crores">₹10-25 Crores</option>
                                <option value="₹25-50 Crores">₹25-50 Crores</option>
                                <option value="₹50+ Crores">₹50+ Crores</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>No. of Employees</label>
                            <input type="number" name="employees" placeholder="e.g. 80">
                        </div>
                    </div>

                    <!-- Contact Person details -->
                    <div class="form-section-title">👨‍💼 Contact Person Details</div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="required">Contact Person Name</label>
                            <input type="text" name="contact_name" placeholder="Full legal name" required>
                        </div>
                        <div class="form-group">
                            <label>Designation / Role</label>
                            <input type="text" name="designation" placeholder="e.g. Purchase Head, Manager">
                        </div>
                        <div class="form-group">
                            <label class="required">Mobile Number</label>
                            <input type="text" name="mobile" placeholder="10-digit mobile code" required>
                        </div>
                        <div class="form-group">
                            <label>WhatsApp Number</label>
                            <input type="text" name="whatsapp" placeholder="WhatsApp contact info">
                        </div>
                        <div class="form-group">
                            <label class="required">Email Address</label>
                            <input type="email" name="email" placeholder="official@company.com" required>
                        </div>
                        <div class="form-group">
                            <label>Alternate Email</label>
                            <input type="email" name="alternate_email" placeholder="backup@company.com">
                        </div>
                        <div class="form-group">
                            <label>LinkedIn Profile</label>
                            <input type="url" name="linkedin" placeholder="LinkedIn Profile URL">
                        </div>
                    </div>

                    <!-- Address details -->
                    <div class="form-section-title">📍 Address Details</div>
                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label class="required">Address Line 1</label>
                            <input type="text" name="address_line1" placeholder="Plot No, Street name, Area" required>
                        </div>
                        <div class="form-group full-width">
                            <label>Address Line 2</label>
                            <input type="text" name="address_line2" placeholder="Suite, floor, landmark">
                        </div>
                        <div class="form-group">
                            <label class="required">City</label>
                            <input type="text" name="city" placeholder="City name" required>
                        </div>
                        <div class="form-group">
                            <label class="required">State</label>
                            <input type="text" name="state" placeholder="State/UT name" required>
                        </div>
                        <div class="form-group">
                            <label class="required">Pincode</label>
                            <input type="text" name="pincode" placeholder="6-digit postal pincode" required>
                        </div>
                        <div class="form-group">
                            <label class="required">Country</label>
                            <input type="text" name="country" value="India" required>
                        </div>
                    </div>

                    <!-- Bank Details -->
                    <div class="form-section-title">🏦 Bank Details (Optional)</div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Bank Name</label>
                            <input type="text" name="bank_name" placeholder="e.g. HDFC Bank">
                        </div>
                        <div class="form-group">
                            <label>Account Number</label>
                            <input type="text" name="account_number" placeholder="Bank savings/current account no.">
                        </div>
                        <div class="form-group">
                            <label>IFSC Code</label>
                            <input type="text" name="ifsc_code" placeholder="11-character IFSC Code" minlength="11" maxlength="11">
                        </div>
                    </div>

                    <!-- Other metadata info -->
                    <div class="form-section-title">📝 Other Info</div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="required">Lead Source</label>
                            <select name="lead_source" required>
                                <option value="" disabled selected>Select Source</option>
                                <option value="Reference">Reference</option>
                                <option value="Cold Call">Cold Call</option>
                                <option value="Website">Website</option>
                                <option value="Exhibition">Exhibition</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="required">Priority Level</label>
                            <select name="priority" required>
                                <option value="Hot">🔥 Hot</option>
                                <option value="Warm" selected>☀️ Warm</option>
                                <option value="Cold">❄️ Cold</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="required">Added By</label>
                            <input type="text" name="added_by" value="<?php echo htmlspecialchars($profile['contact_person']); ?>" required>
                        </div>
                        <div class="form-group full-width">
                            <label>Remarks / Notes</label>
                            <textarea name="remarks" rows="3" placeholder="Enter key conversational remarks here..."></textarea>
                        </div>
                    </div>

                    <!-- Action panel -->
                    <div class="form-actions">
                        <button type="reset" class="btn btn-secondary">Clear Inputs</button>
                        <button type="submit" class="btn btn-primary"><i data-lucide="shield-check"></i> Lock & Save Account</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- ==========================================
              VIEW 3: COMPLETE SEARCH & CRM TRACKING
             ========================================== -->
        <div id="view-search-crm" class="view-container">
            <!-- Search bar -->
            <div class="crm-search-bar">
                <div class="search-input-wrapper">
                    <i data-lucide="search" class="search-icon"></i>
                    <input type="text" id="search-query" placeholder="Type Client Company Name / GSTIN / Contact Name / Email / City..." oninput="triggerSearch()">
                </div>
                <button class="filters-toggle-btn" onclick="toggleFilterDrawer()"><i data-lucide="sliders-horizontal" style="margin-right: 6px;"></i> Filters</button>
            </div>

            <!-- Filters Drawer -->
            <div class="filters-drawer" id="crm-filters-drawer">
                <div class="form-group">
                    <label>By Status</label>
                    <select id="filter-status" onchange="triggerSearch()">
                        <option value="">All Statuses</option>
                        <option value="New">🔵 New</option>
                        <option value="Contacted">🟠 Contacted</option>
                        <option value="In Negotiation">🟡 In Negotiation</option>
                        <option value="Closed Won">🟢 Closed Won</option>
                        <option value="Closed Lost">🔴 Closed Lost</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>By Priority</label>
                    <select id="filter-priority" onchange="triggerSearch()">
                        <option value="">All Priorities</option>
                        <option value="Hot">Hot</option>
                        <option value="Warm">Warm</option>
                        <option value="Cold">Cold</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>By City</label>
                    <select id="filter-city" onchange="triggerSearch()">
                        <option value="">All Cities</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>By Added By</label>
                    <select id="filter-added-by" onchange="triggerSearch()">
                        <option value="">All Staff</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Registered Start</label>
                    <input type="date" id="filter-date-start" onchange="triggerSearch()">
                </div>
                <div class="form-group">
                    <label>Registered End</label>
                    <input type="date" id="filter-date-end" onchange="triggerSearch()">
                </div>
            </div>

            <!-- CRM Layout Grid -->
            <div class="crm-layout">
                <!-- Left panel: Card Lists -->
                <div class="client-list-pane" id="crm-client-list">
                    <!-- Loaded dynamically -->
                </div>

                <!-- Right panel: Live Tracker Details -->
                <div class="client-detail-pane" id="crm-detail-pane">
                    <div class="detail-placeholder">
                        <i data-lucide="eye" style="width: 64px; height: 64px; stroke-width: 1.5;"></i>
                        <div>
                            <h3>No Client Selected</h3>
                            <p style="font-size: 13.5px; margin-top: 6px;">Select a client from the search results to view their CRM history, communications, and quotation statuses.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ==========================================
              VIEW 4: SEND EMAIL MODULE
             ========================================== -->
        <div id="view-send-email" class="view-container">
            <div class="email-form-card">
                <form id="email-sender-form" onsubmit="dispatchEmail(event)" enctype="multipart/form-data">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="required">Select Recipient Client</label>
                            <select name="client_id" id="email-to-select" required>
                                <option value="" disabled selected>Choose client account...</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>CC</label>
                            <input type="email" name="cc" placeholder="cc@yourcompany.com">
                        </div>
                        <div class="form-group">
                            <label class="required">Communication Stage Category</label>
                            <select name="type" required>
                                <option value="Pitch">Pitch Sent</option>
                                <option value="PPT">PPT Shared</option>
                                <option value="Custom Mail" selected>Custom Mail / Updates</option>
                                <option value="Quotation">Quotation Sent</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="required">Dispatched By</label>
                            <input type="text" name="sent_by" value="<?php echo htmlspecialchars($profile['contact_person']); ?>" required>
                        </div>
                        <div class="form-group full-width" style="background: var(--bg-color); padding: 10px; border-radius: 6px; border: 1px dashed var(--border);">
                            <label style="display: flex; justify-content: space-between;">
                                <span>Load Saved Template (Optional)</span>
                                <a href="#" onclick="document.querySelector('.menu-item[data-view=\'settings\']').click(); return false;" style="font-size: 12px; color: var(--primary);">Manage Templates</a>
                            </label>
                            <select id="email-template-select" onchange="applyEmailTemplate()">
                                <option value="" selected>-- Select a template to auto-fill --</option>
                            </select>
                            <input type="hidden" name="template_id" id="email-template-id-hidden">
                            <div id="email-template-attachment-badge" style="display:none; font-size: 11px; margin-top: 6px; color: var(--secondary);"></div>
                        </div>
                        
                        <div class="form-group full-width" style="background: var(--bg-color); padding: 10px; border-radius: 6px; border: 1px dashed var(--border); margin-top: 10px;">
                            <label style="display: flex; justify-content: space-between;">
                                <span>Attach Saved Presentation (Optional)</span>
                                <a href="#" onclick="document.querySelector('.menu-item[data-view=\'settings\']').click(); return false;" style="font-size: 12px; color: var(--primary);">Manage PPTs</a>
                            </label>
                            <select name="ppt_id" id="saved-ppt-select">
                                <option value="">-- Select Presentation --</option>
                            </select>
                        </div>
                        <div class="form-group full-width">
                            <label class="required">Subject</label>
                            <input type="text" name="subject" placeholder="Enter clear descriptive email subject" required>
                        </div>
                        <div class="form-group full-width">
                            <label class="required">Email Message (Rich Text Description)</label>
                            <!-- Quill Rich Text Editor Div -->
                            <div id="email-body-editor"></div>
                            <input type="hidden" name="body" id="email-body-hidden">
                        </div>
                        <div class="form-group">
                            <label>Attachment Document</label>
                            <input type="file" name="attachment" style="border: 1px solid #cbd5e1; padding: 8px;">
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary"><i data-lucide="send"></i> Dispatch simulated email</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ==========================================
              VIEW 5: QUOTATION BUILDER
             ========================================== -->
        <div id="view-create-quotation" class="view-container">
            <div class="quote-builder-layout">
                <form id="quotation-builder-form" onsubmit="saveQuotation(event)">
                    <!-- Quote Header info -->
                    <div class="quote-meta-row">
                        <div class="form-group">
                            <label class="required">Client Company Account</label>
                            <select id="quote-client-select" onchange="autofillQuoteClient()" required>
                                <option value="" disabled selected>Choose client account...</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Quotation Number</label>
                            <input type="text" id="quote-number-display" value="Auto-generated on Save" disabled style="background-color: #f1f5f9; font-weight: bold; color: var(--primary);">
                        </div>
                        <div class="form-group">
                            <label class="required">Date</label>
                            <input type="date" id="quote-date" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>

                    <!-- Selected Client Meta Card -->
                    <div id="quote-client-details-card" style="display: none; background: var(--primary-light); padding: 14px; border-radius: var(--radius-md); border: 1px solid var(--primary-border); margin-bottom: 24px;">
                        <h4 id="qc-company" style="color: var(--primary); font-family: 'Outfit'; margin-bottom: 6px;">Apex Industries</h4>
                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; font-size: 12px; color: var(--text-muted);">
                            <div><strong>GSTIN:</strong> <span id="qc-gstin">-</span></div>
                            <div><strong>Email:</strong> <span id="qc-email">-</span></div>
                            <div><strong>Billing Address:</strong> <span id="qc-address">-</span></div>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <h3 style="font-family: 'Outfit'; font-size: 15px; margin-bottom: 12px; color: var(--text-primary);">Line Items</h3>
                    <table class="items-table" id="quote-items-table">
                        <thead>
                            <tr>
                                <th style="width: 40%;">Item Description</th>
                                <th style="width: 10%;">Qty</th>
                                <th style="width: 15%;">Rate (₹)</th>
                                <th style="width: 15%;">Taxable Value (₹)</th>
                                <th style="width: 15%;">GST Rate</th>
                                <th style="width: 5%;"></th>
                            </tr>
                        </thead>
                        <tbody id="quote-items-tbody">
                            <!-- Rows added dynamically -->
                        </tbody>
                    </table>

                    <button type="button" class="btn btn-secondary" style="margin-bottom: 24px;" onclick="addQuotationRow()"><i data-lucide="plus" style="width: 16px;"></i> Add Row</button>

                    <!-- Summary & Math Details -->
                    <div class="summary-block-wrapper">
                        <div class="summary-block">
                            <div class="summary-row">
                                <span>Subtotal (Taxable):</span>
                                <strong id="quote-subtotal">₹0.00</strong>
                            </div>
                            <div class="summary-row">
                                <span>GST Amount:</span>
                                <strong id="quote-gst">₹0.00</strong>
                            </div>
                            <div class="summary-row grand-total">
                                <span>Grand Total:</span>
                                <strong id="quote-grand-total">₹0.00</strong>
                            </div>
                        </div>
                    </div>
                <div class="form-actions">
                        <button type="submit" class="btn btn-primary"><i data-lucide="save"></i> Save Quotation</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ==========================================
             PRE-LEADS VIEW
             ========================================== -->
        <div id="view-preleads" class="view-container">
            <!-- Stats Bar -->
            <div class="stats-grid" style="grid-template-columns: repeat(3,1fr); margin-bottom:1.5rem;">
                <div class="stat-card" style="border-left:4px solid #f59e0b;">
                    <div class="stat-card-header"><span class="stat-label">Total Prospects</span><i data-lucide="inbox" style="color:#f59e0b;width:20px;height:20px;"></i></div>
                    <div class="stat-value" id="prelead-stat-total">-</div>
                </div>
                <div class="stat-card" style="border-left:4px solid #10b981;">
                    <div class="stat-card-header"><span class="stat-label">Interested</span><i data-lucide="thumbs-up" style="color:#10b981;width:20px;height:20px;"></i></div>
                    <div class="stat-value" id="prelead-stat-interested">-</div>
                </div>
                <div class="stat-card" style="border-left:4px solid #ef4444;">
                    <div class="stat-card-header"><span class="stat-label">Junk</span><i data-lucide="trash-2" style="color:#ef4444;width:20px;height:20px;"></i></div>
                    <div class="stat-value" id="prelead-stat-junk">-</div>
                </div>
            </div>

            <div class="flex-row" style="gap:20px; align-items:flex-start;">
                <!-- Add Form -->
                <div class="card" style="flex:0 0 300px; position:sticky; top:20px;">
                    <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
                        <h2><i data-lucide="plus"></i> Add Pre-Lead</h2>
                        <button class="btn btn-secondary" onclick="openPreLeadBulkUploadModal()" style="font-size:12px; padding:6px 12px;"><i data-lucide="upload" style="width:14px;height:14px;margin-right:4px;"></i> Bulk Upload</button>
                    </div>
                    <div class="card-body">
                        <form id="prelead-form" onsubmit="savePreLead(event)">
                            <input type="hidden" name="action" value="save_prelead">
                            <input type="hidden" name="id" id="prelead_id" value="">
                            
                            <div class="form-group">
                                <label>Name *</label>
                                <input type="text" name="name" id="pl_name" required>
                            </div>
                            <div class="form-group">
                                <label>Mobile *</label>
                                <input type="text" name="mobile" id="pl_mobile" required>
                            </div>
                            <div class="form-group">
                                <label>Company / Location</label>
                                <input type="text" name="company_name" id="pl_company">
                            </div>
                            <div class="form-group">
                                <label>Source</label>
                                <select name="source" id="pl_source">
                                    <option value="Unknown">Unknown</option>
                                    <option value="Website">Website</option>
                                    <option value="Justdial">Justdial</option>
                                    <option value="Reference">Reference</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Notes</label>
                                <textarea name="notes" id="pl_notes" rows="2"></textarea>
                            </div>
                            
                            <div style="display:flex; gap:10px; margin-top:15px;">
                                <button type="submit" class="btn btn-primary" style="flex:1;">Save</button>
                                <button type="button" class="btn btn-secondary" onclick="resetPreLeadForm()">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Table -->
                <div class="card" style="flex:1;">
                    <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
                        <h2><i data-lucide="list"></i> All Pre-Leads</h2>
                        <input type="text" id="prelead-search" placeholder="Search mobile/name..." class="search-box" onkeyup="loadPreLeads()" style="max-width:200px;">
                    </div>
                    <div class="card-body" style="padding:0; overflow-x:auto;">
                        <table class="data-table" id="preleads-table">
                            <thead>
                                <tr>
                                    <th>NAME / COMPANY</th>
                                    <th>MOBILE</th>
                                    <th>SOURCE</th>
                                    <th>STATUS</th>
                                    <th>ACTIONS</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ==========================================
             LEAD MANAGEMENT VIEW
             ========================================== -->
        <div id="view-leads" class="view-container">
            <!-- Stats Bar -->
            <div class="stats-grid" style="grid-template-columns: repeat(4,1fr); margin-bottom:1.5rem;">
                <div class="stat-card" style="border-left:4px solid #6366f1;">
                    <div class="stat-card-header"><span class="stat-label">Total Leads</span><i data-lucide="target" style="color:#6366f1;width:20px;height:20px;"></i></div>
                    <div class="stat-value" id="lead-stat-total">—</div>
                </div>
                <div class="stat-card" style="border-left:4px solid var(--danger);">
                    <div class="stat-card-header"><span class="stat-label">Hot Leads</span><i data-lucide="flame" style="color:var(--danger);width:20px;height:20px;"></i></div>
                    <div class="stat-value" id="lead-stat-hot">—</div>
                </div>
                <div class="stat-card" style="border-left:4px solid var(--success);">
                    <div class="stat-card-header"><span class="stat-label">Won Leads</span><i data-lucide="trophy" style="color:var(--success);width:20px;height:20px;"></i></div>
                    <div class="stat-value" id="lead-stat-won">—</div>
                </div>
                <div class="stat-card" style="border-left:4px solid var(--secondary);">
                    <div class="stat-card-header"><span class="stat-label">In Progress</span><i data-lucide="clock" style="color:var(--secondary);width:20px;height:20px;"></i></div>
                    <div class="stat-value" id="lead-stat-progress">—</div>
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 2fr; gap:1.5rem; align-items:start;">
                <!-- ADD / EDIT LEAD FORM -->
                <div class="card" style="position:sticky;top:20px;">
                    <div class="card-title-bar">
                        <h2 id="lead-form-title">➕ New Lead</h2>
                        <button type="button" class="btn btn-secondary" style="padding:4px 10px;font-size:12px;margin-left:auto;" onclick="openBulkUploadModal()">📤 Bulk Upload</button>
                    </div>
                    <form id="lead-form" onsubmit="saveLead(event)">
                        <input type="hidden" name="lead_id" id="lead-id-hidden" value="">
                        <div class="form-group">
                            <label class="required">Contact Name</label>
                            <input type="text" name="lead_name" id="lf-name" placeholder="e.g. Rajesh Gupta" required>
                        </div>
                        <div class="form-group">
                            <label>Company Name</label>
                            <input type="text" name="company_name" id="lf-company" placeholder="e.g. Infosys Ltd.">
                        </div>
                        <div class="form-group">
                            <label class="required">Mobile</label>
                            <input type="tel" name="mobile" id="lf-mobile" placeholder="10-digit number" required>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" id="lf-email" placeholder="contact@company.com">
                        </div>
                        <div class="form-grid" style="grid-template-columns:1fr 1fr;">
                            <div class="form-group">
                                <label>Lead Source</label>
                                <select name="lead_source" id="lf-source">
                                    <option>Cold Call</option>
                                    <option>Website</option>
                                    <option>Reference</option>
                                    <option>LinkedIn</option>
                                    <option>Exhibition</option>
                                    <option>Other</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Priority</label>
                                <select name="priority" id="lf-priority">
                                    <option value="Hot">🔴 Hot</option>
                                    <option value="Warm" selected>🟡 Warm</option>
                                    <option value="Cold">🔵 Cold</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Stage</label>
                            <select name="stage" id="lf-stage">
                                <option value="New Lead">New Lead</option>
                                <option value="Contacted">Contacted</option>
                                <option value="Interested">Interested</option>
                                <option value="Proposal Sent">Proposal Sent</option>
                                <option value="Negotiation">Negotiation</option>
                                <option value="Won">Won ✅</option>
                                <option value="Lost">Lost ❌</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Assigned To</label>
                            <input type="text" name="assigned_to" id="lf-assigned" placeholder="Staff member name">
                        </div>
                        <div class="form-group">
                            <label>Notes / Remarks</label>
                            <textarea name="notes" id="lf-notes" rows="3" placeholder="Any remarks about this lead..."></textarea>
                        </div>
                        <div class="form-actions" style="gap:8px;">
                            <button type="submit" class="btn btn-secondary" id="lead-submit-btn">Save Lead</button>
                            <button type="button" class="btn" style="background:var(--bg-color);border:1px solid var(--border);color:var(--text);" onclick="resetLeadForm()">Cancel</button>
                        </div>
                    </form>
                </div>

                <!-- LEADS TABLE -->
                <div class="card">
                    <div class="card-title-bar" style="flex-wrap:wrap; gap:10px;">
                        <h2>📋 All Leads</h2>
                        <div style="display:flex;gap:8px;flex-wrap:wrap;">
                            <input type="text" id="lead-search" placeholder="Search name / company / mobile..." style="padding:6px 12px;border:1px solid var(--border);border-radius:6px;background:var(--card-bg);color:var(--text);font-size:13px;min-width:200px;" oninput="loadLeads()">
                            <select id="lead-filter-stage" onchange="loadLeads()" style="padding:6px 10px;border:1px solid var(--border);border-radius:6px;background:var(--card-bg);color:var(--text);font-size:13px;">
                                <option value="">All Stages</option>
                                <option value="New Lead">New Lead</option>
                                <option value="Contacted">Contacted</option>
                                <option value="Interested">Interested</option>
                                <option value="Proposal Sent">Proposal Sent</option>
                                <option value="Negotiation">Negotiation</option>
                                <option value="Won">Won ✅</option>
                                <option value="Lost">Lost ❌</option>
                            </select>
                            <select id="lead-filter-priority" onchange="loadLeads()" style="padding:6px 10px;border:1px solid var(--border);border-radius:6px;background:var(--card-bg);color:var(--text);font-size:13px;">
                                <option value="">All Priorities</option>
                                <option value="Hot">🔴 Hot</option>
                                <option value="Warm">🟡 Warm</option>
                                <option value="Cold">🔵 Cold</option>
                            </select>
                        </div>
                    </div>

                    <!-- Pipeline Summary Bar -->
                    <div id="lead-pipeline-bar" style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:1rem;padding:12px;background:var(--bg-color);border-radius:8px;"></div>

                    <div style="overflow-x:auto;">
                        <table style="width:100%;border-collapse:collapse;font-size:13px;">
                            <thead>
                                <tr style="background:var(--bg-color);color:var(--text-light);font-size:11px;text-transform:uppercase;letter-spacing:0.5px;">
                                    <th style="padding:10px 12px;text-align:left;">Contact / Company</th>
                                    <th style="padding:10px 12px;text-align:left;">Mobile</th>
                                    <th style="padding:10px 12px;text-align:left;">Source</th>
                                    <th style="padding:10px 12px;text-align:left;">Priority</th>
                                    <th style="padding:10px 12px;text-align:left;">Stage</th>
                                    <th style="padding:10px 12px;text-align:left;">Assigned</th>
                                    <th style="padding:10px 12px;text-align:left;">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="leads-table-body">
                                <tr><td colspan="7" style="padding:30px;text-align:center;color:var(--text-light);">Loading leads...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ==========================================
              VIEW 6: QUOTATION LIST
             ========================================== -->
        <div id="view-quotation-list" class="view-container">
            <div class="card">
                <!-- Search & Filters -->
                <div class="crm-search-bar">
                    <div class="search-input-wrapper">
                        <i data-lucide="search" class="search-icon"></i>
                        <input type="text" id="quote-search" placeholder="Search by Client Name or Quotation number..." oninput="loadQuotationList()">
                    </div>
                    <div class="form-group" style="width: 200px;">
                        <select id="quote-filter-status" onchange="loadQuotationList()">
                            <option value="">All Statuses</option>
                            <option value="Pending">Pending</option>
                            <option value="Approved">Approved</option>
                            <option value="Rejected">Rejected</option>
                        </select>
                    </div>
                </div>

                <!-- Quotation Value summary dashboard metrics -->
                <div class="stats-grid" style="grid-template-columns: repeat(4, 1fr); margin-top: 14px; margin-bottom: 24px;">
                    <div class="stat-card" style="padding: 16px;">
                        <span class="stat-label" style="font-size: 11px;">Total Drafted Value</span>
                        <div class="stat-value" id="qs-total" style="font-size: 20px; margin-top: 4px;">₹0.00</div>
                    </div>
                    <div class="stat-card" style="padding: 16px; border-left: 4px solid var(--warning);">
                        <span class="stat-label" style="font-size: 11px; color: var(--warning);">Pending Value</span>
                        <div class="stat-value" id="qs-pending" style="font-size: 20px; color: var(--warning); margin-top: 4px;">₹0.00</div>
                    </div>
                    <div class="stat-card" style="padding: 16px; border-left: 4px solid var(--success);">
                        <span class="stat-label" style="font-size: 11px; color: var(--success);">Approved (Won) Value</span>
                        <div class="stat-value" id="qs-approved" style="font-size: 20px; color: var(--success); margin-top: 4px;">₹0.00</div>
                    </div>
                    <div class="stat-card" style="padding: 16px; border-left: 4px solid var(--danger);">
                        <span class="stat-label" style="font-size: 11px; color: var(--danger);">Rejected Value</span>
                        <div class="stat-value" id="qs-rejected" style="font-size: 20px; color: var(--danger); margin-top: 4px;">₹0.00</div>
                    </div>
                </div>

                <!-- Data Table -->
                <div style="overflow-x: auto;">
                    <table class="quotation-list-table">
                        <thead>
                            <tr>
                                <th style="width: 10%;">Quote No.</th>
                                <th style="width: 25%;">Client Company Name</th>
                                <th style="width: 15%;">Subtotal (₹)</th>
                                <th style="width: 15%;">GST Amount (₹)</th>
                                <th style="width: 15%;">Total Value (₹)</th>
                                <th style="width: 12%;">Status</th>
                                <th style="width: 8%;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="quotation-list-tbody">
                            <!-- Loaded via API -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ==========================================
              VIEW 7: SETTINGS SCREEN
             ========================================== -->
        <div id="view-activity-logs" class="view-container">
            <div class="card">
                <div class="card-title-bar">
                    <h2>System Audit & Activity Logs</h2>
                    <div class="badge-locked" style="background-color: var(--secondary-light); color: var(--secondary);"><i data-lucide="shield"></i> Admin Only</div>
                </div>
                <div style="padding: 20px;">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Date & Time</th>
                                    <th>Action Log Details</th>
                                </tr>
                            </thead>
                            <tbody id="full-activity-logs-tbody">
                                <!-- Loaded via JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div id="view-settings" class="view-container">
            <form id="settings-form" onsubmit="saveCompanySettings(event)">
                <div class="card">
                    <div class="card-title-bar">
                        <h2>CRM Settings & Company Profile</h2>
                        <div class="badge-locked" style="background-color: var(--primary-light); color: var(--primary);"><i data-lucide="settings"></i> Configurations</div>
                    </div>
                    
                    <div class="form-section-title">🏢 Company Profile & Billing Details</div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="required">Company / Business Name</label>
                            <input type="text" name="company_name" value="<?php echo htmlspecialchars($profile['company_name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="required">GSTIN Number</label>
                            <input type="text" name="gstin" value="<?php echo htmlspecialchars($profile['gstin']); ?>" placeholder="15-digit GSTIN ID" minlength="15" maxlength="15" required>
                        </div>
                        <div class="form-group">
                            <label class="required">Billing Address Line 1</label>
                            <input type="text" name="address_line1" value="<?php echo htmlspecialchars($profile['address_line1']); ?>" placeholder="Plot No, Street, Area" required>
                        </div>
                        <div class="form-group">
                            <label>Billing Address Line 2</label>
                            <input type="text" name="address_line2" value="<?php echo htmlspecialchars($profile['address_line2'] ?? ''); ?>" placeholder="Floor, Wing, Landmark">
                        </div>
                        <div class="form-group">
                            <label class="required">City</label>
                            <input type="text" name="city" value="<?php echo htmlspecialchars($profile['city']); ?>" placeholder="e.g. Mumbai" required>
                        </div>
                        <div class="form-group">
                            <label class="required">State</label>
                            <input type="text" name="state" value="<?php echo htmlspecialchars($profile['state']); ?>" placeholder="e.g. Maharashtra" required>
                        </div>
                        <div class="form-group">
                            <label class="required">Pincode</label>
                            <input type="text" name="pincode" value="<?php echo htmlspecialchars($profile['pincode']); ?>" placeholder="6-digit Pincode" minlength="6" maxlength="6" required>
                        </div>
                        <div class="form-group">
                            <label class="required">Country</label>
                            <input type="text" name="country" value="<?php echo htmlspecialchars($profile['country']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="required">Support / Billing Email</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($profile['email']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Contact Phone Number</label>
                            <input type="text" name="mobile" value="<?php echo htmlspecialchars($profile['mobile'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="form-section-title">🏦 Bank Payment Details (Shown on Invoices)</div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Bank Name</label>
                            <input type="text" name="bank_name" value="<?php echo htmlspecialchars($profile['bank_name'] ?? ''); ?>" placeholder="e.g. HDFC Bank">
                        </div>
                        <div class="form-group">
                            <label>Account Number</label>
                            <input type="text" name="account_number" value="<?php echo htmlspecialchars($profile['account_number'] ?? ''); ?>" placeholder="e.g. 50100987654321">
                        </div>
                        <div class="form-group">
                            <label>IFSC Code</label>
                            <input type="text" name="ifsc_code" value="<?php echo htmlspecialchars($profile['ifsc_code'] ?? ''); ?>" placeholder="11-character IFSC" minlength="11" maxlength="11">
                        </div>
                    </div>

                    <div class="form-section-title">👤 User / Staff Account</div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="required">Staff Contact Person (Staff Name)</label>
                            <input type="text" name="contact_person" value="<?php echo htmlspecialchars($profile['contact_person']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-section-title">📧 SMTP Email Configuration</div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>SMTP Host</label>
                            <input type="text" name="smtp_host" value="<?php echo htmlspecialchars($profile['smtp_host'] ?? ''); ?>" placeholder="e.g. smtp.gmail.com">
                        </div>
                        <div class="form-group">
                            <label>SMTP Port</label>
                            <input type="text" name="smtp_port" value="<?php echo htmlspecialchars($profile['smtp_port'] ?? ''); ?>" placeholder="e.g. 587 or 465">
                        </div>
                        <div class="form-group">
                            <label>SMTP Username</label>
                            <input type="text" name="smtp_username" value="<?php echo htmlspecialchars($profile['smtp_username'] ?? ''); ?>" placeholder="e.g. you@gmail.com">
                        </div>
                        <div class="form-group">
                            <label>SMTP Password / App Password</label>
                            <input type="password" name="smtp_password" value="<?php echo htmlspecialchars($profile['smtp_password'] ?? ''); ?>" placeholder="Enter password">
                        </div>
                        <div class="form-group">
                            <label>Encryption</label>
                            <select name="smtp_encryption" style="width: 100%; padding: 0.8rem 1rem; border: 1px solid var(--border); border-radius: 6px; font-family: inherit; font-size: 0.95rem; background-color: var(--card-bg);">
                                <option value="" <?php echo ($profile['smtp_encryption'] ?? '') === '' ? 'selected' : ''; ?>>None</option>
                                <option value="tls" <?php echo ($profile['smtp_encryption'] ?? '') === 'tls' ? 'selected' : ''; ?>>TLS</option>
                                <option value="ssl" <?php echo ($profile['smtp_encryption'] ?? '') === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary" id="save-settings-btn">Save Configurations</button>
                    </div>
                </div>
            </form>

            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin'): ?>
            <div style="margin-top: 3rem;"></div>
            <form id="create-user-form" onsubmit="createUser(event)">
                <div class="card">
                    <div class="card-title-bar">
                        <h2>Manage Users</h2>
                        <div class="badge-locked" style="background-color: var(--secondary-light); color: var(--secondary);"><i data-lucide="shield"></i> Admin Only</div>
                    </div>
                    <div class="form-section-title">➕ Register New Staff</div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="required">Username</label>
                            <input type="text" name="username" required>
                        </div>
                        <div class="form-group">
                            <label class="required">Password</label>
                            <input type="password" name="password" required minlength="6">
                        </div>
                        <div class="form-group" style="display: none;">
                            <label>Role</label>
                            <input type="hidden" name="role" value="Staff">
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-secondary">Create User</button>
                    </div>
                    <div class="form-section-title" style="margin-top: 2rem;">👥 Existing Users</div>
                    <div id="users-list-container">
                        <p style="color: var(--text-light); font-size: 0.9rem;">Loading users...</p>
                    </div>
                </div>
            </form>
            <script>
                function loadUsersList() {
                    fetch('?api=get_users')
                    .then(res => res.json())
                    .then(data => {
                        const container = document.getElementById('users-list-container');
                        let html = '<table class="data-table"><thead><tr><th>Username</th><th>Role</th><th>Status</th><th>Actions</th></tr></thead><tbody>';
                        data.forEach(u => {
                            let statusBadge = u.is_active == 1 ? '<span class="status-badge status-new" style="background:#dcfce7; color:#166534;">Active</span>' : '<span class="status-badge status-closed-lost">Deactivated</span>';
                            let actionBtn = '';
                            let deleteBtn = '';
                            if (u.role !== 'Admin') {
                                actionBtn = u.is_active == 1 ? `<button type="button" class="btn btn-secondary" style="font-size: 11px; padding: 3px 6px;" onclick="toggleUserStatus(${u.id})">Deactivate</button>` : `<button type="button" class="btn btn-primary" style="font-size: 11px; padding: 3px 6px;" onclick="toggleUserStatus(${u.id})">Activate</button>`;
                                deleteBtn = `<button type="button" class="btn btn-danger" style="font-size: 11px; padding: 3px 6px; background: #fee2e2; color: #ef4444; border: 1px solid #fca5a5;" onclick="deleteUser(${u.id})">Delete</button>`;
                            }
                            
                            html += `<tr>
                                <td><strong>${u.username}</strong></td>
                                <td>${u.role}</td>
                                <td>${statusBadge}</td>
                                <td style="display:flex; gap: 5px;">${actionBtn} ${deleteBtn}</td>
                            </tr>`;
                        });
                        html += '</tbody></table>';
                        container.innerHTML = html;
                    });
                }

                function toggleUserStatus(id) {
                    if (!confirm("Are you sure you want to change this user's status?")) return;
                    let fd = new FormData(); fd.append('id', id);
                    fetch('?api=toggle_user_status', {method: 'POST', body: fd})
                    .then(r => r.json()).then(d => {
                        if(d.success) { showNotification(d.message, 'success'); loadUsersList(); }
                        else showNotification(d.error, 'error');
                    });
                }

                function deleteUser(id) {
                    if (!confirm("Are you sure you want to permanently DELETE this user?")) return;
                    let fd = new FormData(); fd.append('id', id);
                    fetch('?api=delete_user', {method: 'POST', body: fd})
                    .then(r => r.json()).then(d => {
                        if(d.success) { showNotification(d.message, 'success'); loadUsersList(); }
                        else showNotification(d.error, 'error');
                    });
                }

                function createUser(e) {
                    e.preventDefault();
                    const fd = new FormData(e.target);
                    fetch('?api=create_user', {method: 'POST', body: fd})
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            showNotification(data.message, 'success');
                            e.target.reset();
                            loadUsersList();
                        } else {
                            showNotification(data.error, 'error');
                        }
                    });
                }
                document.addEventListener('DOMContentLoaded', loadUsersList);
            </script>
            <?php endif; ?>

            <!-- EMAIL TEMPLATES MANAGER -->
            <div style="margin-top: 3rem;"></div>
            <div class="card">
                <div class="card-title-bar">
                    <h2>Email Templates Manager</h2>
                </div>
                <div class="form-section-title">➕ Create New Template</div>
                <form id="create-template-form" onsubmit="saveTemplate(event)" enctype="multipart/form-data">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="required">Template Name</label>
                            <input type="text" name="template_name" placeholder="e.g. Standard Pitch" required>
                        </div>
                        <div class="form-group">
                            <label class="required">Type</label>
                            <select name="type" required>
                                <option value="Pitch">Pitch</option>
                                <option value="PPT">PPT</option>
                                <option value="Custom Mail">Custom Mail</option>
                            </select>
                        </div>
                        <div class="form-group full-width">
                            <label class="required">Subject Line</label>
                            <input type="text" name="subject" placeholder="Email Subject" required>
                        </div>
                        <div class="form-group full-width">
                            <label class="required">Email Body</label>
                            <textarea name="body" rows="6" placeholder="Write your template text here..." required></textarea>
                        </div>
                        <div class="form-group full-width">
                            <label>Default Attachment (Optional PPT/PDF)</label>
                            <input type="file" name="attachment" accept=".ppt,.pptx,.pdf,.doc,.docx,.xls,.xlsx,.zip">
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-secondary">Save Template</button>
                    </div>
                </form>
                
                <div class="form-section-title" style="margin-top: 2rem;">📚 Saved Templates</div>
                <div id="templates-list-container">
                    <p style="color: var(--text-light); font-size: 0.9rem;">Loading templates...</p>
                </div>
            </div>
            <script>
                function loadTemplatesList() {
                    fetch('?api=get_templates')
                    .then(res => res.json())
                    .then(data => {
                        window.globalEmailTemplates = data;
                        
                        // Populate templates list in Settings
                        const container = document.getElementById('templates-list-container');
                        if (data.length === 0) {
                            container.innerHTML = '<p style="color: var(--text-light); font-size: 0.9rem;">No templates saved yet.</p>';
                        } else {
                            let html = '<ul style="list-style:none; padding:0; margin:0;">';
                            data.forEach(t => {
                                let attachBadge = t.attachment_name ? `<span style="font-size: 11px; background: var(--secondary-light); color: var(--secondary); padding: 2px 6px; border-radius: 4px; margin-left: 8px;">📎 Attached File</span>` : '';
                                html += `<li style="padding: 1rem; border: 1px solid var(--border); border-radius: 6px; margin-bottom: 0.8rem; display: flex; justify-content: space-between; align-items: center;">
                                    <div>
                                        <strong style="display:block;">${t.template_name} (${t.type})</strong>
                                        <span style="font-size: 13px; color: var(--text-light);">Sub: ${t.subject}</span>
                                        ${attachBadge}
                                    </div>
                                    <button type="button" class="btn btn-danger" style="padding: 0.4rem 0.8rem; font-size: 12px; background: #fee2e2; color: #ef4444; border: 1px solid #fca5a5; border-radius: 6px; cursor: pointer;" onclick="deleteTemplate(${t.id})">Delete</button>
                                </li>`;
                            });
                            html += '</ul>';
                            container.innerHTML = html;
                        }

                        // Populate dropdown in Send Email view
                        const dropdown = document.getElementById('email-template-select');
                        if (dropdown) {
                            dropdown.innerHTML = '<option value="" selected>-- Select a template to auto-fill --</option>';
                            data.forEach(t => {
                                dropdown.innerHTML += `<option value="${t.id}">${t.template_name} (${t.type})</option>`;
                            });
                        }
                    });
                }
                function saveTemplate(e) {
                    e.preventDefault();
                    const fd = new FormData(e.target);
                    fetch('?api=save_template', {method: 'POST', body: fd})
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            showNotification(data.message, 'success');
                            e.target.reset();
                            loadTemplatesList();
                        } else {
                            showNotification(data.error, 'error');
                        }
                    });
                }
                function deleteTemplate(id) {
                    if (!confirm('Are you sure you want to delete this template?')) return;
                    const fd = new FormData();
                    fd.append('id', id);
                    fetch('?api=delete_template', {method: 'POST', body: fd})
                    .then(() => {
                        showNotification('Template deleted', 'success');
                        loadTemplatesList();
                    });
                }
                
                function applyEmailTemplate() {
                    const select = document.getElementById('email-template-select');
                    const templateId = select.value;
                    const badge = document.getElementById('email-template-attachment-badge');
                    document.getElementById('email-template-id-hidden').value = templateId;
                    
                    if (!templateId) {
                        document.querySelector('input[name="subject"]').value = '';
                        if (window.quillEmailEditor) window.quillEmailEditor.root.innerHTML = '';
                        badge.style.display = 'none';
                        return;
                    }
                    
                    const t = window.globalEmailTemplates.find(x => x.id == templateId);
                    if (t) {
                        document.querySelector('input[name="subject"]').value = t.subject;
                        if (window.quillEmailEditor) window.quillEmailEditor.root.innerHTML = t.body;
                        document.querySelector('select[name="type"]').value = t.type;
                        
                        if (t.attachment_name) {
                            badge.textContent = `📎 Will automatically attach: ${t.attachment_name}`;
                            badge.style.display = 'block';
                        } else {
                            badge.style.display = 'none';
                        }
                    }
                }
                
                function loadPptsList() {
                    fetch('?api=get_ppts')
                    .then(res => res.json())
                    .then(data => {
                        const selectEl = document.getElementById('saved-ppt-select');
                        if (selectEl) {
                            let optionsHtml = '<option value="">-- Select Presentation --</option>';
                            data.forEach(p => {
                                optionsHtml += `<option value="${p.id}">${p.original_name}</option>`;
                            });
                            selectEl.innerHTML = optionsHtml;
                        }
                        
                        const container = document.getElementById('ppts-list-container');
                        if (container) {
                            if (data.length === 0) {
                                container.innerHTML = '<p style="color: var(--text-light); font-size: 0.9rem;">No presentations saved yet.</p>';
                            } else {
                                let html = '<ul style="list-style:none; padding:0; margin:0;">';
                                data.forEach(p => {
                                    html += `<li style="padding: 10px; border: 1px solid var(--border); margin-bottom: 5px; border-radius: 6px; display:flex; justify-content:space-between; align-items:center; background: var(--card-bg);">
                                        <div><strong>${p.original_name}</strong> <span style="font-size:11px; color:var(--text-muted); margin-left: 10px;">${p.filename}</span></div>
                                        <button type="button" class="btn btn-danger" style="padding: 4px 8px; font-size: 11px;" onclick="deletePpt(${p.id})">Delete</button>
                                    </li>`;
                                });
                                html += '</ul>';
                                container.innerHTML = html;
                            }
                        }
                    });
                }
                
                function savePpt(e) {
                    e.preventDefault();
                    const fd = new FormData(e.target);
                    fetch('?api=upload_ppt', {method:'POST', body:fd})
                    .then(res => res.json())
                    .then(data => {
                        if(data.success) {
                            showNotification(data.message, 'success');
                            e.target.reset();
                            loadPptsList();
                        } else showNotification(data.error, 'error');
                    });
                }
                
                function deletePpt(id) {
                    if(!confirm('Are you sure you want to delete this presentation?')) return;
                    let fd = new FormData(); fd.append('id', id);
                    fetch('?api=delete_ppt', {method:'POST', body:fd}).then(r=>r.json()).then(d=>{
                        showNotification('Presentation deleted', 'success');
                        loadPptsList();
                    });
                }
                
                document.addEventListener('DOMContentLoaded', () => {
                    loadTemplatesList();
                    loadPptsList();
                });
            </script>
            
            <!-- PPT LIBRARY MANAGER -->
            <div style="margin-top: 3rem;"></div>
            <div class="card">
                <div class="card-title-bar">
                    <h2>Presentation (PPT) Library</h2>
                </div>
                <div class="form-section-title">➕ Upload New Presentation</div>
                <form id="ppt-upload-form" onsubmit="savePpt(event)">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="required">Presentation Title</label>
                            <input type="text" name="original_name" required placeholder="e.g. Corporate Deck 2026">
                        </div>
                        <div class="form-group">
                            <label class="required">File Attachment</label>
                            <input type="file" name="ppt_file" required>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-secondary">Upload Presentation</button>
                    </div>
                </form>
                
                <div class="form-section-title" style="margin-top: 2rem;">📚 Saved Presentations</div>
                <div id="ppts-list-container">
                    <p style="color: var(--text-light); font-size: 0.9rem;">Loading presentations...</p>
                </div>
            </div>        </div>

    </main>

    <!-- Visual Container holding printable invoice layouts dynamically -->
    <div id="invoice-print-container">
        <!-- Generated on PDF creation request -->
    </div>

    <!-- Notification Toast System -->
    <div class="toast-container" id="toast-container"></div>

    <!-- Bulk Upload Modal -->
    <div id="bulk-upload-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>📤 Upload Leads Data</h2>
                <span class="close" onclick="closeBulkUploadModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div class="form-group" style="text-align:center; margin-bottom: 20px;">
                    <label for="bulk-upload-file" class="btn btn-primary" style="display:inline-flex; align-items:center; justify-content:center; cursor:pointer; padding: 14px 28px; font-size: 16px; border-radius: 8px; width: 100%;">
                        <i data-lucide="upload-cloud" style="width:20px;height:20px;margin-right:8px;"></i>
                        Select File to Upload
                    </label>
                    <input type="file" id="bulk-upload-file" accept=".csv, .xls, .xlsx" onchange="handleBulkFileSelect(event)" style="display:none;">
                    <p style="font-size:12px;color:var(--text-light);margin-top:10px;">Supported formats: CSV, XLS, XLSX</p>
                </div>
                
                <div id="bulk-preview-container" style="display:none; margin-top:15px; border-top: 1px solid var(--border); padding-top: 15px;">
                    <p style="font-weight:600; color:var(--success); text-align:center; margin-bottom:15px;">✅ <span id="bulk-record-count">0</span> records parsed successfully.</p>
                    
                    <div style="background:var(--bg-main); border-radius:8px; padding: 15px;">
                        <label style="display:flex; align-items:center; gap:8px; font-weight:600; cursor:pointer;">
                            <input type="checkbox" id="bulk-split-checkbox" onchange="toggleSplitOptions()"> Smart Split Data (Optional)
                        </label>
                        
                        <div id="split-options-container" style="display:none; margin-top:10px; padding-left:25px;">
                            <label style="display:block; margin-bottom:5px;"><input type="radio" name="split_type" value="random" onchange="renderSplitUI()"> 🎲 Split Randomly (Even/Odd)</label>
                            <label style="display:block; margin-bottom:5px;"><input type="radio" name="split_type" value="serial" onchange="renderSplitUI()"> 🔢 Split by Serial</label>
                            <label style="display:block; margin-bottom:5px;"><input type="radio" name="split_type" value="location" onchange="renderSplitUI()"> 📍 Split by Location</label>
                            
                            <div id="dynamic-split-ui" style="margin-top:15px; padding:10px; border:1px solid var(--border); border-radius:6px; background:var(--bg-card); display:none;">
                            </div>
                        </div>
                    </div>
                    
                    <button class="btn btn-primary" id="btn-save-bulk" style="margin-top:20px; width:100%; padding:12px; font-size:16px;" onclick="processBulkUpload()">Save All Leads</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripting Area -->
    <script>
        // Init Global state references
        let companyProfile = <?php echo json_encode($profile); ?>;
        let currentUser = <?php echo isset($_SESSION['user_id']) ? json_encode(['username' => $_SESSION['username'], 'role' => $_SESSION['role']]) : 'null'; ?>;
        let clientGrowthChart = null;
        let topClientsChart = null;
        let weeklyActivityChart = null;
        let quillEmailEditor = null;
        let crmClientList = []; // stores local copy of fetched search results
        window.globalEmailTemplates = [];

        // Document Load entry triggers
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
            
            // Switch tabs dynamically
            initNavigation();
            
            // Render widgets on homepage
            loadDashboardStats();
            loadDashboardCharts();
            loadRecentActivities();
            
            // Warm up filter select lists
            loadFilterOptions();
            
            // Setup rich text editor
            quillEmailEditor = new Quill('#email-body-editor', {
                theme: 'snow',
                placeholder: 'Write your official pitch/proposals/quotation email here...',
                modules: {
                    toolbar: [
                        ['bold', 'italic', 'underline'],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        ['clean']
                    ]
                }
            });
            
            // Populate select lists after editor ready - only load if on relevant view
            if (document.getElementById('view-send-email')?.classList.contains('active') ||
                document.getElementById('view-create-quotation')?.classList.contains('active')) {
                refreshClientDropdowns();
            }
        });

        // Toggle mobile aside layout, view redirection and UI updates
        function initNavigation() {
            document.querySelectorAll('.sidebar-menu .menu-item').forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Mark sidebar active
                    document.querySelectorAll('.sidebar-menu .menu-item').forEach(li => li.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Toggle visibility of panels
                    const targetView = this.getAttribute('data-view');
                    document.querySelectorAll('.view-container').forEach(view => view.classList.remove('active'));
                    
                    const activeViewEl = document.getElementById('view-' + targetView);
                    activeViewEl.classList.add('active');
                    
                    // Adjust Header Text Details
                    const titles = {
                        'dashboard': { title: 'Dashboard', sub: 'AuraCRM Operations Control Panel' },
                        'preleads': { title: 'Pre-Leads (Raw Data)', sub: 'Manage raw data and unverified prospects' },
                        'leads': { title: 'Lead Management', sub: '🎯 Capture, track and convert leads into clients' },
                        'add-client': { title: 'Register Client Account', sub: '🔒 Lock-in customer profile parameters permanently' },
                        'search-crm': { title: 'Search & Tracking Dashboard', sub: 'Interactive CRM conversion tracker and client card catalog' },
                        'send-email': { title: 'Communication Center', sub: 'Compose and dispatch simulated customer interaction emails' },
                        'create-quotation': { title: 'Quotation Builder Suite', sub: 'Create items proposals with instant Indian GST taxation logic' },
                        'quotation-list': { title: 'Invoice Ledgers', sub: 'Audit and track approved client quotations' },
                        'settings': { title: 'CRM Settings & Configurations', sub: 'Manage company profile, GST details, billing parameters, and default user' },
                        'activity-logs': { title: 'Activity Audit Logs', sub: 'System tracking of all staff actions and record updates' }
                    };
                    
                    document.getElementById('view-title').innerText = titles[targetView] ? titles[targetView].title : 'Configurations';
                    document.getElementById('view-subtitle').innerText = titles[targetView] ? titles[targetView].sub : '';
                    
                    // Refresh data based on view
                    if (targetView === 'dashboard') {
                        loadDashboardStats();
                        loadDashboardCharts();
                        loadRecentActivities();
                    } else if (targetView === 'leads') {
                        loadLeads();
                    } else if (targetView === 'search-crm') {
                        loadFilterOptions();
                        triggerSearch();
                    } else if (targetView === 'send-email') {
                        refreshClientDropdowns();
                    } else if (targetView === 'create-quotation') {
                        refreshClientDropdowns();
                        resetQuotationForm();
                    } else if (targetView === 'quotation-list') {
                        loadQuotationList();
                    }
                });
            });
        }

        // ==========================================
        //  TOAST SYSTEM
        // ==========================================
        function showNotification(message, icon = 'info') {
            if (message === 'SESSION_EXPIRED') {
                alert('Your session has expired because your account was logged in from another device.');
                window.location.reload();
                return;
            }
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = 'toast';
            
            let iconMarkup = `<i data-lucide="info" style="color: var(--primary);"></i>`;
            if (icon === 'success') iconMarkup = `<i data-lucide="check-circle" style="color: var(--success);"></i>`;
            else if (icon === 'error') iconMarkup = `<i data-lucide="alert-triangle" style="color: var(--danger);"></i>`;
            
            toast.innerHTML = `
                ${iconMarkup}
                <span>${message}</span>
            `;
            
            container.appendChild(toast);
            lucide.createIcons();
            
            setTimeout(() => {
                toast.style.animation = 'slideInRight 0.3s reverse forwards';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        // Indian Currency Formatter (Lakhs, Crores format helper)
        function formatIndianCurrency(amount) {
            const x = amount.toString().split('.');
            let lastThree = x[0].substring(x[0].length - 3);
            const otherSymbols = x[0].substring(0, x[0].length - 3);
            if (otherSymbols !== '') {
                lastThree = ',' + lastThree;
            }
            let res = otherSymbols.replace(/\B(?=(\d{2})+(?!\d))/g, ",") + lastThree;
            if (x.length > 1) {
                res += '.' + x[1].substring(0, 2);
            }
            return '₹' + res;
        }

        // ==========================================
        //  DASHBOARD WIDGETS AND API PIPES
        // ==========================================
        async function loadDashboardStats() {
            try {
                const response = await fetch('?api=stats');
                const data = await response.json();
                
                document.getElementById('stat-total-clients').innerText = data.total_clients;
                document.getElementById('stat-emails-today').innerText = data.emails_today;
                document.getElementById('stat-quotes-month').innerText = data.quotes_this_month;
                document.getElementById('stat-pending-followups').innerText = data.pending_followups;
                document.getElementById('stat-total-val').innerText = formatIndianCurrency(data.total_quote_value);
                document.getElementById('stat-no-quotation').innerText = data.no_quotation_clients;
                if (document.getElementById('stat-active-staff')) {
                    document.getElementById('stat-active-staff').innerText = data.active_staff;
                }
            } catch (err) {
                showNotification('Failed to retrieve dashboard stats summary metrics.', 'error');
            }
        }

        async function loadDashboardCharts() {
            try {
                const response = await fetch('?api=charts_data');
                const data = await response.json();
                
                // 1. Client growth line Chart
                const growthLabels = data.growth.map(i => i.label);
                const growthValues = data.growth.map(i => i.value);
                
                if (clientGrowthChart) clientGrowthChart.destroy();
                const ctxGrowth = document.getElementById('chart-client-growth').getContext('2d');
                clientGrowthChart = new Chart(ctxGrowth, {
                    type: 'line',
                    data: {
                        labels: growthLabels,
                        datasets: [{
                            label: 'Registered Accounts',
                            data: growthValues,
                            borderColor: '#f97316',
                            backgroundColor: 'rgba(249, 115, 22, 0.08)',
                            fill: true,
                            tension: 0.4,
                            borderWidth: 3,
                            pointBackgroundColor: '#ea580c',
                            pointHoverRadius: 7
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { grid: { color: '#f1f5f9' }, ticks: { stepSize: 1 } },
                            x: { grid: { display: false } }
                        }
                    }
                });

                // 2. Custom Funnel builder UI
                const funnelContainer = document.getElementById('funnel-visualization-container');
                funnelContainer.innerHTML = '';
                
                // Calculate scale divisor
                const maxVal = Math.max(...data.funnel.map(f => f.count), 1);
                
                data.funnel.forEach(f => {
                    const pct = maxVal > 0 ? (f.count / maxVal) * 100 : 0;
                    const stageEl = document.createElement('div');
                    stageEl.className = 'funnel-stage';
                    stageEl.innerHTML = `
                        <div class="funnel-label">${f.stage}</div>
                        <div class="funnel-bar-wrapper">
                            <div class="funnel-bar" style="width: ${pct}%">
                                <span class="funnel-count">${f.count} Accounts</span>
                            </div>
                        </div>
                    `;
                    funnelContainer.appendChild(stageEl);
                });

                // 3. Top clients chart
                const topLabels = data.top_clients.map(i => i.name);
                const topValues = data.top_clients.map(i => i.value);
                
                if (topClientsChart) topClientsChart.destroy();
                const ctxTop = document.getElementById('chart-top-clients').getContext('2d');
                topClientsChart = new Chart(ctxTop, {
                    type: 'bar',
                    data: {
                        labels: topLabels,
                        datasets: [{
                            data: topValues,
                            backgroundColor: '#f97316',
                            hoverBackgroundColor: '#ea580c',
                            borderRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { grid: { color: '#f1f5f9' } },
                            x: { grid: { display: false } }
                        }
                    }
                });

                // 4. Weekly Activity summary
                const actLabels = data.activity_weekly.map(i => i.label);
                const actValues = data.activity_weekly.map(i => i.value);
                
                if (weeklyActivityChart) weeklyActivityChart.destroy();
                const ctxAct = document.getElementById('chart-weekly-activity').getContext('2d');
                weeklyActivityChart = new Chart(ctxAct, {
                    type: 'bar',
                    data: {
                        labels: actLabels,
                        datasets: [{
                            data: actValues,
                            backgroundColor: '#0f172a',
                            borderRadius: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { grid: { color: '#f1f5f9' }, ticks: { stepSize: 1 } },
                            x: { grid: { display: false } }
                        }
                    }
                });

            } catch (err) {
                console.error(err);
                showNotification('Failed to generate interactive dashboard charts.', 'error');
            }
        }

        async function loadFullActivityLogs() {
            try {
                const response = await fetch('?api=get_activity_logs');
                const data = await response.json();
                const container = document.getElementById('full-activity-logs-tbody');
                if (data.length === 0) {
                    container.innerHTML = '<tr><td colspan="2" style="text-align:center;">No activities found</td></tr>';
                    return;
                }
                let html = '';
                data.forEach(act => {
                    let descHtml = act.description;
                    // highlight username
                    descHtml = descHtml.replace(/^\[(.*?)\]/, '<span class="badge" style="background:var(--primary);color:white;">$1</span>');
                    html += `<tr>
                        <td style="white-space:nowrap; color:var(--text-light); font-size:12px;">${act.created_at_formatted}</td>
                        <td>${descHtml}</td>
                    </tr>`;
                });
                container.innerHTML = html;
            } catch (e) {
                console.error(e);
            }
        }

        async function loadRecentActivities() {
            try {
                const response = await fetch('?api=recent_activities');
                const data = await response.json();
                const container = document.getElementById('dashboard-activity-feed');
                
                container.innerHTML = '';
                if (data.length === 0) {
                    container.innerHTML = '<div style="color: var(--text-light); text-align: center; padding: 20px;">No activities logged yet.</div>';
                    return;
                }
                
                data.forEach(act => {
                    const item = document.createElement('div');
                    item.className = 'activity-item';
                    item.innerHTML = `
                        <div class="activity-bullet"></div>
                        <div class="activity-content">
                            <div class="activity-text">${act.description}</div>
                            <div class="activity-time">${act.time_formatted}</div>
                        </div>
                    `;
                    container.appendChild(item);
                });
            } catch (err) {
                showNotification('Failed to sync recent activities.', 'error');
            }
        }

        // ==========================================
        //  CLIENT REGISTRATION MODULE (No Edit/Delete rule)
        // ==========================================
        async function saveClient(event) {
            event.preventDefault();
            const form = document.getElementById('client-registration-form');
            const formData = new FormData(form);
            
            try {
                const response = await fetch('?api=add_client', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (response.ok && data.success) {
                    showNotification(data.message, 'success');
                    form.reset();
                    refreshClientDropdowns();
                    // Auto redirect to tracker
                    setTimeout(() => {
                        document.querySelector('[data-view="search-crm"]').click();
                    }, 500);
                } else {
                    showNotification(data.error || 'Registration failed.', 'error');
                }
            } catch (err) {
                showNotification('Connection failure in client registration.', 'error');
            }
        }

        // Populate unique fields for searching & filtering
        async function loadFilterOptions() {
            try {
                const res = await fetch('?api=get_unique_filters');
                const data = await res.json();
                
                const citySelect = document.getElementById('filter-city');
                citySelect.innerHTML = '<option value="">All Cities</option>';
                data.cities.forEach(c => {
                    citySelect.innerHTML += `<option value="${c}">${c}</option>`;
                });
                
                const addedSelect = document.getElementById('filter-added-by');
                addedSelect.innerHTML = '<option value="">All Staff</option>';
                data.added_by.forEach(u => {
                    addedSelect.innerHTML += `<option value="${u}">${u}</option>`;
                });
            } catch (err) {
                console.error('Filter synchronization failure', err);
            }
        }

        // ==========================================
        //  MODULE 2: COMPLETE CRM TRACKING & SEARCH
        // ==========================================
        function toggleFilterDrawer() {
            document.getElementById('crm-filters-drawer').classList.toggle('active');
        }

        async function triggerSearch() {
            const query = document.getElementById('search-query').value;
            const status = document.getElementById('filter-status').value;
            const priority = document.getElementById('filter-priority').value;
            const city = document.getElementById('filter-city').value;
            const addedBy = document.getElementById('filter-added-by').value;
            const dateStart = document.getElementById('filter-date-start').value;
            const dateEnd = document.getElementById('filter-date-end').value;
            
            const params = new URLSearchParams({
                api: 'search_clients',
                query: query,
                status: status,
                priority: priority,
                city: city,
                added_by: addedBy,
                date_start: dateStart,
                date_end: dateEnd
            });
            
            try {
                const response = await fetch('?' + params.toString());
                const clients = await response.json();
                crmClientList = clients;
                
                const container = document.getElementById('crm-client-list');
                container.innerHTML = '';
                
                if (clients.length === 0) {
                    container.innerHTML = '<div style="background: white; border: 1px solid #e2e8f0; text-align: center; color: var(--text-light); padding: 40px; border-radius: var(--radius-md);">No records match criteria.</div>';
                    return;
                }
                
                clients.forEach(c => {
                    const card = document.createElement('div');
                    card.className = 'client-card';
                    card.id = 'crm-client-card-' + c.id;
                    card.onclick = () => selectClientCard(c.id);
                    
                    const priorityClass = 'badge-' + c.priority.toLowerCase();
                    const statusClass = c.overall_status.toLowerCase().replace(' ', '');
                    
                    card.innerHTML = `
                        <div class="client-card-header">
                            <span class="client-card-title">${c.company_name}</span>
                            <span class="badge ${priorityClass}">${c.priority}</span>
                        </div>
                        <div class="client-card-meta">
                            <i data-lucide="user" style="width: 12px; height: 12px;"></i>
                            <span>${c.contact_name} — ${c.designation || 'Client Contact'}</span>
                        </div>
                        <div class="client-card-meta">
                            <i data-lucide="phone" style="width: 12px; height: 12px;"></i>
                            <span>${c.mobile}</span>
                        </div>
                        <div class="client-card-meta">
                            <i data-lucide="map-pin" style="width: 12px; height: 12px;"></i>
                            <span>${c.city}, ${c.state}</span>
                        </div>
                        <div style="display:flex; justify-content: space-between; align-items: center; margin-top: 12px; border-top: 1px dashed #e2e8f0; padding-top: 8px;">
                            <span class="badge-status ${statusClass}">${c.overall_status}</span>
                            <span style="font-size: 11px; color: var(--text-muted);">By: ${c.added_by}</span>
                        </div>
                    `;
                    container.appendChild(card);
                });
                lucide.createIcons();
            } catch (err) {
                showNotification('CRM Search request failed.', 'error');
            }
        }

        async function selectClientCard(id) {
            // Unselect all cards
            document.querySelectorAll('.client-card').forEach(el => el.classList.remove('selected'));
            
            const selectedCard = document.getElementById('crm-client-card-' + id);
            if (selectedCard) selectedCard.classList.add('selected');
            
            try {
                const response = await fetch(`?api=client_details&id=${id}`);
                const c = await response.json();
                
                const pane = document.getElementById('crm-detail-pane');
                pane.innerHTML = '';
                
                // Pipeline tracker checklist dates math
                const pitchDate = c.pitch_sent ? formatDate(c.pitch_sent) : 'Not Sent';
                const pptDate = c.ppt_sent ? formatDate(c.ppt_sent) : 'Not Shared';
                const mailDate = c.mail_sent ? formatDate(c.mail_sent) : 'Not Sent';
                const quoteDate = c.quotation_sent ? formatDate(c.quotation_sent) : 'No Quotation';
                
                const stepsClassList = {
                    pitch: c.pitch_sent ? 'completed' : '',
                    ppt: c.ppt_sent ? 'completed' : '',
                    mail: c.mail_sent ? 'completed' : '',
                    quote: c.quotation_sent ? 'completed' : ''
                };
                
                // Active highlighting
                if (c.overall_status === 'Contacted') stepsClassList.pitch = 'active';
                if (c.overall_status === 'In Negotiation') stepsClassList.quote = 'active';
                
                // Construct quotes summary markup
                let quotesHtml = '';
                if (c.quotations && c.quotations.length > 0) {
                    quotesHtml = c.quotations.map(q => {
                        const amt = formatIndianCurrency(q.total_amount);
                        const statusClass = q.status.toLowerCase();
                        return `
                            <div style="display:flex; justify-content: space-between; align-items:center; background:#f8fafc; padding: 10px; border-radius: var(--radius-sm); border:1px solid #e2e8f0; margin-bottom: 8px;">
                                <div>
                                    <strong style="color:var(--primary); font-size:13.5px;">${q.quotation_number}</strong>
                                    <span style="color: var(--text-muted); font-size:12px; margin-left: 10px;">Created on: ${formatDate(q.created_at)}</span>
                                </div>
                                <div style="display:flex; align-items:center; gap: 12px;">
                                    <strong style="font-size:13.5px;">${amt}</strong>
                                    <span class="badge-status ${q.status.toLowerCase()}">${q.status}</span>
                                </div>
                            </div>
                        `;
                    }).join('');
                } else {
                    quotesHtml = '<div style="color: var(--text-light); text-align:center; padding: 20px; font-size:13px;">No quotations generated yet for this company.</div>';
                }

                // Construct communications list
                let commsHtml = '';
                if (c.communications_logs && c.communications_logs.length > 0) {
                    commsHtml = c.communications_logs.map(log => `
                        <div class="history-item">
                            <div class="history-item-header">
                                <span style="color: var(--primary); font-weight:700;">${log.type}</span>
                                <span style="color: var(--text-muted);">${formatDate(log.sent_at)}</span>
                            </div>
                            <div class="history-item-body">
                                <strong>Subject:</strong> ${log.subject}<br>
                                <span style="font-size:11px; color: var(--text-muted);">Sent by: ${log.sent_by}</span>
                            </div>
                        </div>
                    `).join('');
                } else {
                    commsHtml = '<div style="color: var(--text-light); text-align:center; padding: 20px; font-size:13px;">No communication entries logged.</div>';
                }

                pane.innerHTML = `
                    <div class="detail-header">
                        <div>
                            <div class="detail-company-title">${c.company_name}</div>
                            <div style="font-size: 13.5px; color: var(--text-muted); margin-top: 4px;">Sector: ${c.industry_sector || 'N/A'} | Type: ${c.business_type}</div>
                        </div>
                        <div style="text-align: right;">
                            <span class="badge-status ${c.overall_status.toLowerCase().replace(' ', '')}" style="font-size: 13px; padding: 6px 14px;">${c.overall_status}</span>
                            <div style="font-size:11px; color: var(--text-light); margin-top: 6px;">Added by ${c.added_by}</div>
                        </div>
                    </div>
                    
                    <!-- CRM Funnel Checklist -->
                    <div class="detail-block-title">Pipeline Tracking Status</div>
                    <div class="pipeline-tracker">
                        <div class="pipeline-step ${stepsClassList.pitch}">
                            <div class="pipeline-icon-circle"><i data-lucide="${c.pitch_sent ? 'check':'mail'}" style="width:14px;"></i></div>
                            <span class="pipeline-step-label">Pitch Sent</span>
                            <span class="pipeline-step-date">${pitchDate}</span>
                        </div>
                        <div class="pipeline-step ${stepsClassList.ppt}">
                            <div class="pipeline-icon-circle"><i data-lucide="${c.ppt_sent ? 'check':'file-presentation'}" style="width:14px;"></i></div>
                            <span class="pipeline-step-label">PPT Shared</span>
                            <span class="pipeline-step-date">${pptDate}</span>
                        </div>
                        <div class="pipeline-step ${stepsClassList.mail}">
                            <div class="pipeline-icon-circle"><i data-lucide="${c.mail_sent ? 'check':'sparkles'}" style="width:14px;"></i></div>
                            <span class="pipeline-step-label">Custom Mail</span>
                            <span class="pipeline-step-date">${mailDate}</span>
                        </div>
                        <div class="pipeline-step ${stepsClassList.quote}">
                            <div class="pipeline-icon-circle"><i data-lucide="${c.quotation_sent ? 'check':'file-text'}" style="width:14px;"></i></div>
                            <span class="pipeline-step-label">Quote Sent</span>
                            <span class="pipeline-step-date">${quoteDate}</span>
                        </div>
                    </div>

                    <!-- Client Detail Info Blocks -->
                    <div class="detail-grid">
                        <div>
                            <div class="detail-block-title">Company Info</div>
                            <div class="detail-field"><label>GSTIN:</label> <span>${c.gstin || 'Not Available'}</span></div>
                            <div class="detail-field"><label>PAN No:</label> <span>${c.pan || 'Not Available'}</span></div>
                            <div class="detail-field"><label>Website:</label> <span>${c.website ? `<a href="${c.website}" target="_blank" style="color:var(--primary);">${c.website}</a>` : 'N/A'}</span></div>
                            <div class="detail-field"><label>Turnover:</label> <span>${c.turnover || 'N/A'}</span></div>
                            <div class="detail-field"><label>Employees:</label> <span>${c.employees || 'N/A'}</span></div>
                        </div>
                        <div>
                            <div class="detail-block-title">Key Contact Details</div>
                            <div class="detail-field"><label>Contact Name:</label> <span>${c.contact_name}</span></div>
                            <div class="detail-field"><label>Role:</label> <span>${c.designation || 'N/A'}</span></div>
                            <div class="detail-field"><label>Mobile:</label> <span>${c.mobile}</span></div>
                            <div class="detail-field"><label>WhatsApp:</label> <span>${c.whatsapp || 'N/A'}</span></div>
                            <div class="detail-field"><label>Email:</label> <span><a href="mailto:${c.email}" style="color:var(--primary);">${c.email}</a></span></div>
                        </div>
                    </div>

                    <div style="margin-bottom: 24px;">
                        <div class="detail-block-title">Address coordinates</div>
                        <div style="font-size: 13.5px; line-height:1.5;">
                            ${c.address_line1}, ${c.address_line2 ? c.address_line2 + ',' : ''}<br>
                            ${c.city}, ${c.state} - ${c.pincode}, ${c.country}
                        </div>
                    </div>
                    
                    <!-- Quick action buttons -->
                    <div style="display:flex; gap:10px; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid #f1f5f9;">
                        <button class="btn btn-primary" onclick="initiateEmailForClient(${c.id}, '${c.company_name}')"><i data-lucide="mail"></i> Send Mail</button>
                        <button class="btn btn-secondary" onclick="initiateQuotationForClient(${c.id})"><i data-lucide="file-signature"></i> Create Quote</button>
                    </div>

                    <!-- Tabs Details (History & Quotes) -->
                    <div class="tabs">
                        <div class="tab active" onclick="switchDetailTab('history')" id="tab-btn-history">Communication Logs</div>
                        <div class="tab" onclick="switchDetailTab('quotes')" id="tab-btn-quotes">Quotations Created</div>
                    </div>

                    <div class="tab-content active" id="tab-content-history">
                        <div class="history-list">
                            ${commsHtml}
                        </div>
                    </div>
                    
                    <div class="tab-content" id="tab-content-quotes">
                        <div style="max-height: 250px; overflow-y:auto;">
                            ${quotesHtml}
                        </div>
                    </div>
                `;
                lucide.createIcons();
            } catch (err) {
                console.error(err);
                showNotification('Could not load client CRM profile details.', 'error');
            }
        }

        // Helper Tab Switches inside profile pane
        function switchDetailTab(tabName) {
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            
            document.getElementById('tab-btn-' + tabName).classList.add('active');
            document.getElementById('tab-content-' + tabName).classList.add('active');
        }

        // Date String cleanups helper
        function formatDate(sqlDate) {
            if (!sqlDate) return 'N/A';
            const date = new Date(sqlDate);
            return date.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
        }

        // ==========================================
        //  RECIPIENT DROPDOWN POPULATOR
        // ==========================================
        async function refreshClientDropdowns() {
            try {
                const response = await fetch('?api=search_clients');
                if (!response.ok) return; // silently skip if session expired or unauthorized
                const clients = await response.json();
                if (!Array.isArray(clients)) return;
                
                const emailSelect = document.getElementById('email-to-select');
                const quoteSelect = document.getElementById('quote-client-select');
                
                const optHtml = clients.map(c => `<option value="${c.id}">${c.company_name} (${c.contact_name})</option>`).join('');
                
                if (emailSelect) emailSelect.innerHTML = '<option value="" disabled selected>Choose client account...</option>' + optHtml;
                if (quoteSelect) quoteSelect.innerHTML = '<option value="" disabled selected>Choose client account...</option>' + optHtml;
            } catch (err) {
                // Silent fail — do not show error toast on initial load
                console.warn('refreshClientDropdowns:', err);
            }
        }

        // ==========================================
        //  EMAIL DISPATCH (Simulated)
        // ==========================================
        function initiateEmailForClient(clientId, companyName) {
            document.querySelector('[data-view="send-email"]').click();
            setTimeout(() => {
                document.getElementById('email-to-select').value = clientId;
            }, 100);
        }

        async function dispatchEmail(e) {
            e.preventDefault();
            
            // Sync editor contents to hidden payload field
            const hiddenBodyInput = document.getElementById('email-body-hidden');
            hiddenBodyInput.value = quillEmailEditor.getSemanticHTML();
            
            const form = document.getElementById('email-sender-form');
            const formData = new FormData(form);
            
            try {
                const response = await fetch('?api=send_email', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (response.ok && data.success) {
                    showNotification(data.message, 'success');
                    form.reset();
                    quillEmailEditor.setText(''); // Empty editor
                    
                    // Redirect back to CRM search tracker
                    setTimeout(() => {
                        document.querySelector('[data-view="search-crm"]').click();
                    }, 500);
                } else {
                    showNotification(data.error || 'Simulated mail routing failed.', 'error');
                }
            } catch (err) {
                showNotification('Email sending operation failed.', 'error');
            }
        }

        // ==========================================
        //  QUOTATION BUILDER & GST CALCULATOR
        // ==========================================
        function initiateQuotationForClient(clientId) {
            document.querySelector('[data-view="create-quotation"]').click();
            setTimeout(() => {
                document.getElementById('quote-client-select').value = clientId;
                autofillQuoteClient();
            }, 100);
        }

        async function autofillQuoteClient() {
            const clientId = document.getElementById('quote-client-select').value;
            if (!clientId) return;
            
            try {
                const response = await fetch(`?api=client_details&id=${clientId}`);
                const c = await response.json();
                
                document.getElementById('quote-client-details-card').style.display = 'block';
                document.getElementById('qc-company').innerText = c.company_name;
                document.getElementById('qc-gstin').innerText = c.gstin || 'No GSTIN Entered';
                document.getElementById('qc-email').innerText = c.email;
                document.getElementById('qc-address').innerText = `${c.address_line1}, ${c.city}, ${c.state} - ${c.pincode}`;
            } catch (err) {
                showNotification('Autofill metadata mapping failed.', 'error');
            }
        }

        function resetQuotationForm() {
            document.getElementById('quotation-builder-form').reset();
            document.getElementById('quote-items-tbody').innerHTML = '';
            document.getElementById('quote-client-details-card').style.display = 'none';
            document.getElementById('quote-number-display').value = 'Auto-generated on Save';
            
            // Add single initial item row
            addQuotationRow();
            calculateQuotationTotals();
        }

        function addQuotationRow() {
            const tbody = document.getElementById('quote-items-tbody');
            const rowId = 'row-' + Date.now();
            
            const tr = document.createElement('tr');
            tr.id = rowId;
            tr.innerHTML = `
                <td><input type="text" placeholder="Item/Service name description..." required class="item-name"></td>
                <td><input type="number" min="1" value="1" required class="item-qty" oninput="calculateRowMath('${rowId}')"></td>
                <td><input type="number" min="0.01" step="0.01" placeholder="0.00" required class="item-rate" oninput="calculateRowMath('${rowId}')"></td>
                <td><input type="text" readonly value="₹0.00" class="item-taxval" style="background:#f1f5f9; font-weight: 500;"></td>
                <td>
                    <select class="item-gst" onchange="calculateRowMath('${rowId}')">
                        <option value="0">0% Exempt</option>
                        <option value="5">5% SGST+CGST</option>
                        <option value="12">12% Standard</option>
                        <option value="18" selected>18% Standard</option>
                        <option value="28">28% Premium</option>
                    </select>
                </td>
                <td><button type="button" class="delete-row-btn" onclick="removeQuotationRow('${rowId}')"><i data-lucide="trash-2" style="width:16px;"></i></button></td>
            `;
            
            tbody.appendChild(tr);
            lucide.createIcons();
            calculateQuotationTotals();
        }

        function removeQuotationRow(rowId) {
            const row = document.getElementById(rowId);
            const tbody = document.getElementById('quote-items-tbody');
            
            if (tbody.children.length > 1) {
                row.remove();
                calculateQuotationTotals();
            } else {
                showNotification('At least one item line must exist in quotes.', 'warning');
            }
        }

        function calculateRowMath(rowId) {
            const row = document.getElementById(rowId);
            const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
            const rate = parseFloat(row.querySelector('.item-rate').value) || 0;
            
            const taxVal = qty * rate;
            row.querySelector('.item-taxval').value = formatIndianCurrency(taxVal);
            
            calculateQuotationTotals();
        }

        function calculateQuotationTotals() {
            let subtotal = 0;
            let totalGst = 0;
            
            document.querySelectorAll('#quote-items-tbody tr').forEach(row => {
                const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
                const rate = parseFloat(row.querySelector('.item-rate').value) || 0;
                const gstRate = parseFloat(row.querySelector('.item-gst').value) || 0;
                
                const taxVal = qty * rate;
                const gstVal = taxVal * (gstRate / 100);
                
                subtotal += taxVal;
                totalGst += gstVal;
            });
            
            const grandTotal = subtotal + totalGst;
            
            document.getElementById('quote-subtotal').innerText = formatIndianCurrency(subtotal);
            document.getElementById('quote-gst').innerText = formatIndianCurrency(totalGst);
            document.getElementById('quote-grand-total').innerText = formatIndianCurrency(grandTotal);
        }

        async function saveQuotation(e) {
            e.preventDefault();
            
            const clientId = document.getElementById('quote-client-select').value;
            if (!clientId) {
                showNotification('Please select a client account.', 'warning');
                return;
            }
            
            // Gather items matrix
            const items = [];
            let errorFlag = false;
            
            let subtotal = 0;
            let totalGst = 0;
            
            document.querySelectorAll('#quote-items-tbody tr').forEach(row => {
                const name = row.querySelector('.item-name').value.trim();
                const qty = parseInt(row.querySelector('.item-qty').value) || 0;
                const rate = parseFloat(row.querySelector('.item-rate').value) || 0;
                const gstRate = parseFloat(row.querySelector('.item-gst').value) || 0;
                
                if (name === '' || qty <= 0 || rate <= 0) {
                    errorFlag = true;
                }
                
                const taxVal = qty * rate;
                const gstVal = taxVal * (gstRate / 100);
                
                subtotal += taxVal;
                totalGst += gstVal;
                
                items.push({
                    name: name,
                    qty: qty,
                    rate: rate,
                    gst_rate: gstRate,
                    total: taxVal + gstVal
                });
            });
            
            if (errorFlag) {
                showNotification('Please verify all row descriptions, rates, and values.', 'warning');
                return;
            }
            
            const grandTotal = subtotal + totalGst;
            
            const payload = new URLSearchParams({
                client_id: clientId,
                subtotal: subtotal.toFixed(2),
                gst_amount: totalGst.toFixed(2),
                total_amount: grandTotal.toFixed(2),
                items_json: JSON.stringify(items)
            });
            
            try {
                const response = await fetch('?api=save_quotation', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: payload.toString()
                });
                
                const data = await response.json();
                
                if (response.ok && data.success) {
                    showNotification(data.message, 'success');
                    
                    // Direct redirect to ledgers page
                    setTimeout(() => {
                        document.querySelector('[data-view="quotation-list"]').click();
                    }, 500);
                } else {
                    showNotification(data.error || 'Failed to save quotation.', 'error');
                }
            } catch (err) {
                showNotification('Quotation save error.', 'error');
            }
        }

        // ==========================================
        //  QUOTATION LEDGER AND AUDIT PIPES
        // ==========================================
        // ==========================================
        //  LEAD MANAGEMENT FUNCTIONS
        // ==========================================

        const STAGE_COLORS = {
            'New Lead':      '#6366f1',
            'Contacted':     '#f59e0b',
            'Interested':    '#3b82f6',
            'Proposal Sent': '#8b5cf6',
            'Negotiation':   '#f97316',
            'Won':           '#10b981',
            'Lost':          '#ef4444'
        };

        const PRIORITY_BADGE = {
            'Hot':  '<span style="background:#fee2e2;color:#ef4444;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600;">🔴 Hot</span>',
            'Warm': '<span style="background:#fef9c3;color:#ca8a04;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600;">🟡 Warm</span>',
            'Cold': '<span style="background:#dbeafe;color:#2563eb;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600;">🔵 Cold</span>'
        };

        // ==========================================
        // PRE-LEADS JAVASCRIPT LOGIC
        // ==========================================
        async function loadPreLeads() {
            const res = await fetch('?api=get_preleads');
            const data = await res.json();
            
            const tbody = document.querySelector('#preleads-table tbody');
            tbody.innerHTML = '';
            
            let total = 0, interested = 0, junk = 0;
            
            const search = document.getElementById('prelead-search')?.value.toLowerCase() || '';
            
            data.forEach(p => {
                if(search && !p.name.toLowerCase().includes(search) && !p.mobile.includes(search)) return;
                
                total++;
                if(p.status === 'Interested') interested++;
                if(p.status === 'Junk') junk++;
                
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>
                        <div style="font-weight:600;">${p.name}</div>
                        <div style="font-size:12px;color:var(--text-light);">${p.company_name || p.location || '-'}</div>
                    </td>
                    <td>${p.mobile}<br><span style="font-size:11px;color:#888;">${p.email || ''}</span></td>
                    <td><span class="badge" style="background:#f1f5f9;color:#475569;">${p.source}</span></td>
                    <td>
                        <select onchange="updatePreLeadStatus(${p.id}, this.value)" style="padding:4px; border-radius:4px; font-size:12px;">
                            <option value="Not Contacted" ${p.status==='Not Contacted'?'selected':''}>Not Contacted</option>
                            <option value="Interested" ${p.status==='Interested'?'selected':''}>Interested</option>
                            <option value="Junk" ${p.status==='Junk'?'selected':''}>Junk</option>
                        </select>
                    </td>
                    <td>
                        <div style="display:flex;gap:5px;">
                            ${currentUser && currentUser.role === 'Admin' ? `<button class="btn btn-secondary" onclick="editPreLead(${p.id})" style="padding:4px 8px;" title="Edit"><i data-lucide="edit" style="width:14px;height:14px;"></i></button>` : ''}
                            <button class="btn btn-primary" onclick="promotePreLead(${p.id})" style="padding:4px 8px; font-size:12px;" title="Promote to Lead"><i data-lucide="rocket" style="width:14px;height:14px;"></i> Promote</button>
                            ${currentUser && currentUser.role === 'Admin' ? `<button class="btn btn-danger" onclick="deletePreLead(${p.id})" style="padding:4px 8px;" title="Delete"><i data-lucide="trash-2" style="width:14px;height:14px;"></i></button>` : ''}
                        </div>
                    </td>
                `;
                tbody.appendChild(tr);
            });
            
            document.getElementById('prelead-stat-total').innerText = total;
            document.getElementById('prelead-stat-interested').innerText = interested;
            document.getElementById('prelead-stat-junk').innerText = junk;
            lucide.createIcons();
        }

        async function savePreLead(e) {
            e.preventDefault();
            const fd = new FormData(e.target);
            const res = await fetch('?api=save_prelead', { method:'POST', body:fd });
            const data = await res.json();
            if(data.success) {
                showNotification(data.message, 'success');
                resetPreLeadForm();
                loadPreLeads();
            } else {
                showNotification(data.error, 'error');
            }
        }

        function resetPreLeadForm() {
            document.getElementById('prelead-form').reset();
            document.getElementById('prelead_id').value = '';
        }

        async function deletePreLead(id) {
            if(!confirm("Are you sure you want to delete this raw data?")) return;
            const fd = new FormData(); fd.append('id', id);
            const res = await fetch('?api=delete_prelead', { method:'POST', body:fd });
            const data = await res.json();
            if(data.success) { showNotification(data.message, 'success'); loadPreLeads(); }
            else showNotification(data.error, 'error');
        }

        async function promotePreLead(id) {
            if(!confirm("Promote this prospect to your main Leads CRM?")) return;
            const fd = new FormData(); fd.append('id', id);
            const res = await fetch('?api=promote_prelead', { method:'POST', body:fd });
            const data = await res.json();
            if(data.success) { 
                showNotification("🚀 " + data.message, 'success'); 
                loadPreLeads(); 
                loadLeads(); // refresh main leads
            } else {
                showNotification(data.error, 'error');
            }
        }

        async function updatePreLeadStatus(id, status) {
            const fd = new FormData(); fd.append('id', id); fd.append('status', status);
            await fetch('?api=update_prelead_status', { method:'POST', body:fd });
            loadPreLeads();
        }
        
        function openPreLeadBulkUploadModal() {
            // Re-using the bulk modal but setting an indicator it's for pre-leads
            document.getElementById('bulk-upload-modal').style.display = 'flex';
            // We set a global variable to indicate destination
            window.bulkUploadDestination = 'pre_leads';
        }

        // Run loadPreLeads periodically or when clicking the tab
        document.querySelector('[data-view="preleads"]')?.addEventListener('click', () => {
            loadPreLeads();
        });

        async function loadLeads() {
            const search   = document.getElementById('lead-search')?.value || '';
            const stage    = document.getElementById('lead-filter-stage')?.value || '';
            const priority = document.getElementById('lead-filter-priority')?.value || '';
            const params   = new URLSearchParams({ api: 'get_leads', search, stage, priority });

            try {
                const res = await fetch('?' + params.toString());
                if (!res.ok) return;
                const leads = await res.json();

                // Update stat cards
                const total    = leads.length;
                const hot      = leads.filter(l => l.priority === 'Hot' && l.stage !== 'Won' && l.stage !== 'Lost').length;
                const won      = leads.filter(l => l.stage === 'Won').length;
                const progress = leads.filter(l => l.stage !== 'Won' && l.stage !== 'Lost').length;

                const setEl = (id, v) => { const el = document.getElementById(id); if(el) el.innerText = v; };
                setEl('lead-stat-total',    total);
                setEl('lead-stat-hot',      hot);
                setEl('lead-stat-won',      won);
                setEl('lead-stat-progress', progress);

                // Pipeline Bar counts
                const stageCounts = {};
                leads.forEach(l => { stageCounts[l.stage] = (stageCounts[l.stage] || 0) + 1; });
                const pipelineBar = document.getElementById('lead-pipeline-bar');
                if (pipelineBar) {
                    const stages = ['New Lead','Contacted','Interested','Proposal Sent','Negotiation','Won','Lost'];
                    pipelineBar.innerHTML = stages.map(s => {
                        const cnt = stageCounts[s] || 0;
                        const col = STAGE_COLORS[s];
                        return `<div onclick="document.getElementById('lead-filter-stage').value='${s}'; loadLeads();"
                            style="flex:1;min-width:80px;text-align:center;padding:8px 6px;border-radius:8px;background:${col}18;border:1px solid ${col}44;cursor:pointer;transition:all .2s;"
                            onmouseover="this.style.background='${col}33'" onmouseout="this.style.background='${col}18'">
                            <div style="font-size:18px;font-weight:700;color:${col};">${cnt}</div>
                            <div style="font-size:10px;color:${col};font-weight:600;">${s}</div>
                        </div>`;
                    }).join('');
                }

                // Table rows
                const tbody = document.getElementById('leads-table-body');
                if (!tbody) return;
                if (leads.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="7" style="padding:40px;text-align:center;color:var(--text-light);">No leads found. Add your first lead using the form!</td></tr>';
                    return;
                }
                tbody.innerHTML = leads.map(l => {
                    const col = STAGE_COLORS[l.stage] || '#64748b';
                    return `<tr style="border-bottom:1px solid var(--border);transition:background .15s;" onmouseover="this.style.background='var(--bg-color)'" onmouseout="this.style.background=''">
                        <td style="padding:12px;">
                            <div style="font-weight:600;color:var(--text);">${l.lead_name}</div>
                            <div style="font-size:11px;color:var(--text-light);">${l.company_name || '—'}</div>
                        </td>
                        <td style="padding:12px;font-size:13px;">${l.mobile}</td>
                        <td style="padding:12px;font-size:12px;color:var(--text-light);">${l.lead_source}</td>
                        <td style="padding:12px;">${PRIORITY_BADGE[l.priority] || l.priority}</td>
                        <td style="padding:12px;">
                            <select onchange="quickUpdateStage(${l.id}, this.value)" style="padding:3px 8px;border:none;border-radius:20px;font-size:12px;font-weight:600;background:${col}20;color:${col};cursor:pointer;outline:none;">
                                ${['New Lead','Contacted','Interested','Proposal Sent','Negotiation','Won','Lost'].map(s =>
                                    `<option value="${s}" ${l.stage===s?'selected':''}>${s}</option>`
                                ).join('')}
                            </select>
                        </td>
                        <td style="padding:12px;font-size:12px;color:var(--text-light);">${l.assigned_to || '—'}</td>
                        <td style="padding:12px;">
                            <div style="display:flex;gap:6px;">
                                ${currentUser && currentUser.role === 'Admin' ? `<button class="btn btn-secondary" style="padding:4px 10px;font-size:11px;" onclick="editLead(${l.id})" title="Edit">✏️</button>` : ''}
                                <button class="btn btn-secondary" style="padding:4px 10px;font-size:11px;background:#dcfce7;color:#166534;border:none;" onclick="convertToClient(${l.id})" title="Convert to Client">🔄</button>
                                ${currentUser && currentUser.role === 'Admin' ? `<button class="btn btn-danger" style="padding:4px 10px;font-size:11px;" onclick="deleteLead(${l.id})" title="Delete">🗑️</button>` : ''}
                            </div>
                        </td>
                    </tr>`;
                }).join('');
                lucide.createIcons();
            } catch(err) {
                console.warn('loadLeads error:', err);
            }
        }

        async function saveLead(e) {
            e.preventDefault();
            const fd = new FormData(e.target);
            const res = await fetch('?api=save_lead', { method: 'POST', body: fd });
            const data = await res.json();
            if (data.success) {
                showNotification(data.message, 'success');
                resetLeadForm();
                loadLeads();
            } else {
                showNotification(data.error || 'Failed to save lead', 'error');
            }
        }

        async function editLead(id) {
            try {
                console.log('editLead called for id:', id);
                const res = await fetch(`?api=get_lead_detail&id=${id}`);
                const text = await res.text();
                console.log('editLead API response:', text);
                const l = JSON.parse(text);
                
                if (l.error) {
                    showNotification(l.error, 'error');
                    return;
                }

                document.getElementById('lead-id-hidden').value = l.id;
                document.getElementById('lf-name').value     = l.lead_name || '';
                document.getElementById('lf-company').value  = l.company_name || '';
                document.getElementById('lf-mobile').value   = l.mobile || '';
                document.getElementById('lf-email').value    = l.email || '';
                document.getElementById('lf-source').value   = l.lead_source || '';
                document.getElementById('lf-priority').value = l.priority || '';
                document.getElementById('lf-stage').value    = l.stage || '';
                document.getElementById('lf-assigned').value = l.assigned_to || '';
                document.getElementById('lf-notes').value    = l.notes || '';
                
                document.getElementById('lead-form-title').innerText = '✏️ Edit Lead';
                document.getElementById('lead-submit-btn').innerText = 'Update Lead';
                document.getElementById('lead-form').scrollIntoView({ behavior: 'smooth', block: 'start' });
            } catch(err) {
                console.error('editLead error:', err);
                showNotification('Could not load lead details. Check console.', 'error');
            }
        }

        async function quickUpdateStage(id, stage) {
            const fd = new FormData();
            fd.append('id', id); fd.append('stage', stage);
            const res = await fetch('?api=update_lead_stage', { method: 'POST', body: fd });
            const data = await res.json();
            if (data.success) {
                showNotification(`Stage updated to "${stage}"`, 'success');
                loadLeads();
            } else {
                showNotification(data.error || 'Stage update failed', 'error');
            }
        }

        async function deleteLead(id) {
            if (!confirm('Are you sure you want to delete this lead?')) return;
            const fd = new FormData(); fd.append('id', id);
            const res = await fetch('?api=delete_lead', { method: 'POST', body: fd });
            const data = await res.json();
            if (data.success) {
                showNotification('Lead deleted', 'success');
                loadLeads();
            } else {
                showNotification(data.error || 'Failed to delete lead', 'error');
            }
        }

        function convertToClient(id) {
            fetch(`?api=get_lead_detail&id=${id}`)
            .then(r => r.json())
            .then(l => {
                // Switch to Add Client view and pre-fill fields
                document.querySelector('.menu-item[data-view="add-client"]').click();
                setTimeout(() => {
                    const setVal = (sel, val) => { const el = document.querySelector(sel); if(el && val) el.value = val; };
                    setVal('#client-registration-form input[name="contact_name"]', l.lead_name);
                    setVal('#client-registration-form input[name="company_name"]', l.company_name);
                    setVal('#client-registration-form input[name="mobile"]',       l.mobile);
                    setVal('#client-registration-form input[name="email"]',        l.email);
                    setVal('#client-registration-form select[name="lead_source"]', l.lead_source);
                    setVal('#client-registration-form select[name="priority"]',    l.priority);
                    if (l.location) {
                        setVal('#client-registration-form input[name="city"]', l.location);
                    }
                    showNotification('Lead data pre-filled! Complete the client registration form.', 'info');
                }, 400);
            });
        }

        function resetLeadForm() {
            document.getElementById('lead-form').reset();
            document.getElementById('lead-id-hidden').value = '';
            document.getElementById('lead-form-title').innerText = '➕ New Lead';
            document.getElementById('lead-submit-btn').innerText = 'Save Lead';
        }

        async function loadQuotationList() {
            const search = document.getElementById('quote-search').value;
            const status = document.getElementById('quote-filter-status').value;
            
            const params = new URLSearchParams({
                api: 'quotation_list',
                search: search,
                status: status
            });
            
            try {
                const res = await fetch('?' + params.toString());
                if (!res.ok) return; // silently skip if unauthorized
                const data = await res.json();
                if (!data || !data.summary) return;
                
                // Set summaries
                document.getElementById('qs-total').innerText = formatIndianCurrency(data.summary.total_value);
                document.getElementById('qs-pending').innerText = formatIndianCurrency(data.summary.pending_value);
                document.getElementById('qs-approved').innerText = formatIndianCurrency(data.summary.approved_value);
                document.getElementById('qs-rejected').innerText = formatIndianCurrency(data.summary.rejected_value);
                
                const tbody = document.getElementById('quotation-list-tbody');
                tbody.innerHTML = '';
                
                if (data.quotations.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; color: var(--text-light); padding: 30px;">No quotations matches requirements.</td></tr>';
                    return;
                }
                
                data.quotations.forEach(q => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td><strong>${q.quotation_number}</strong></td>
                        <td>
                            <strong style="color:var(--text-primary);">${q.company_name}</strong><br>
                            <span style="font-size:11px; color: var(--text-muted);">${q.client_email} | ${q.city}</span>
                        </td>
                        <td>${formatIndianCurrency(q.subtotal)}</td>
                        <td>${formatIndianCurrency(q.gst_amount)}</td>
                        <td><strong style="color:var(--primary);">${formatIndianCurrency(q.total_amount)}</strong></td>
                        <td>
                            <select class="status-pill-select ${q.status}" onchange="updateQuoteStatus(${q.id}, this.value)">
                                <option value="Pending" ${q.status === 'Pending' ? 'selected' : ''}>Pending</option>
                                <option value="Approved" ${q.status === 'Approved' ? 'selected' : ''}>Approved</option>
                                <option value="Rejected" ${q.status === 'Rejected' ? 'selected' : ''}>Rejected</option>
                            </select>
                        </td>
                        <td>
                            <div style="display:flex; gap:8px;">
                                <button class="btn btn-secondary" style="padding: 6px 10px;" onclick="printQuotationPDF(${q.id})" title="Print or Save PDF"><i data-lucide="printer" style="width:14px; height:14px;"></i></button>
                                <button class="btn btn-secondary" style="padding: 6px 10px;" onclick="emailQuotationQuick(${q.id}, '${q.client_email}', '${q.company_name}')" title="Email Quotation"><i data-lucide="mail" style="width:14px; height:14px;"></i></button>
                            </div>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
                lucide.createIcons();
            } catch (err) {
                showNotification('Failed to retrieve quotation ledger data.', 'error');
            }
        }

        async function updateQuoteStatus(quoteId, newStatus) {
            const payload = new URLSearchParams({
                quote_id: quoteId,
                status: newStatus
            });
            
            try {
                const response = await fetch('?api=update_quotation_status', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: payload.toString()
                });
                const data = await response.json();
                
                if (response.ok && data.success) {
                    showNotification(data.message, 'success');
                    loadQuotationList(); // refresh
                } else {
                    showNotification(data.error || 'Failed to adjust quotation status.', 'error');
                }
            } catch (err) {
                showNotification('Status transaction execution failed.', 'error');
            }
        }

        // ==========================================
        //  PDF PRINT ENGINE & VISUAL EMULATOR
        // ==========================================
        async function printQuotationPDF(quoteId) {
            try {
                // Fetch quotation metadata details from client card info api
                const response = await fetch(`?api=quotation_list`);
                const data = await response.json();
                const quote = data.quotations.find(q => q.id == quoteId);
                
                if (!quote) {
                    showNotification('Quotation details trace missing.', 'error');
                    return;
                }
                
                // Query client address specs
                const cliRes = await fetch(`?api=client_details&id=${quote.client_id}`);
                const cli = await cliRes.json();
                
                const items = JSON.parse(quote.items_json);
                const printContainer = document.getElementById('invoice-print-container');
                printContainer.innerHTML = '';
                
                // Build a beautiful printable A4 corporate invoice template
                const template = document.createElement('div');
                template.style.padding = '30px';
                template.style.background = '#ffffff';
                template.style.color = '#1e293b';
                template.style.fontFamily = 'Inter, sans-serif';
                template.style.fontSize = '12px';
                template.style.lineHeight = '1.6';
                
                let rowsHtml = items.map((item, idx) => `
                    <tr>
                        <td style="border: 1px solid #cbd5e1; padding: 10px; text-align: center;">${idx + 1}</td>
                        <td style="border: 1px solid #cbd5e1; padding: 10px;">${item.name}</td>
                        <td style="border: 1px solid #cbd5e1; padding: 10px; text-align: center;">${item.qty}</td>
                        <td style="border: 1px solid #cbd5e1; padding: 10px; text-align: right;">${formatIndianCurrency(item.rate)}</td>
                        <td style="border: 1px solid #cbd5e1; padding: 10px; text-align: center;">${item.gst_rate}%</td>
                        <td style="border: 1px solid #cbd5e1; padding: 10px; text-align: right; font-weight:600;">${formatIndianCurrency(item.total)}</td>
                    </tr>
                `).join('');
                
                let isSameState = false;
                if (cli.state && companyProfile.state) {
                    isSameState = cli.state.trim().toLowerCase() === companyProfile.state.trim().toLowerCase();
                }
                
                let cgstAmount = 0;
                let sgstAmount = 0;
                let igstAmount = 0;
                if (isSameState) {
                    cgstAmount = quote.gst_amount / 2;
                    sgstAmount = quote.gst_amount / 2;
                } else {
                    igstAmount = quote.gst_amount;
                }
                
                let taxBreakdownHtml = '';
                if (isSameState) {
                    taxBreakdownHtml = `
                        <tr>
                            <td style="padding: 6px 0; color:#64748b;">CGST:</td>
                            <td style="padding: 6px 0; text-align: right; font-weight:600;">${formatIndianCurrency(cgstAmount)}</td>
                        </tr>
                        <tr>
                            <td style="padding: 6px 0; color:#64748b; border-bottom: 1px solid #cbd5e1;">SGST:</td>
                            <td style="padding: 6px 0; text-align: right; font-weight:600; border-bottom: 1px solid #cbd5e1;">${formatIndianCurrency(sgstAmount)}</td>
                        </tr>
                    `;
                } else {
                    taxBreakdownHtml = `
                        <tr>
                            <td style="padding: 6px 0; color:#64748b; border-bottom: 1px solid #cbd5e1;">IGST:</td>
                            <td style="padding: 6px 0; text-align: right; font-weight:600; border-bottom: 1px solid #cbd5e1;">${formatIndianCurrency(igstAmount)}</td>
                        </tr>
                    `;
                }
                
                let bankDetailsHtml = '';
                if (companyProfile.bank_name && companyProfile.account_number) {
                    bankDetailsHtml = `
                        <div style="margin-top: 15px; border: 1px dashed #cbd5e1; padding: 10px; border-radius: 6px; background-color: #f8fafc;">
                            <strong style="color:#ea580c; font-size: 10px; text-transform: uppercase;">Bank Account Details:</strong>
                            <div style="font-size:10px; color:#475569; margin-top: 4px; line-height: 1.4;">
                                <strong>Bank Name:</strong> ${companyProfile.bank_name}<br>
                                <strong>Account Number:</strong> ${companyProfile.account_number}<br>
                                <strong>IFSC Code:</strong> ${companyProfile.ifsc_code || 'N/A'}
                            </div>
                        </div>
                    `;
                }

                template.innerHTML = `
                    <div style="display:flex; justify-content: space-between; align-items:flex-start; margin-bottom: 40px; border-bottom: 2px solid #ea580c; padding-bottom: 20px;">
                        <div>
                            <h1 style="color:#ea580c; font-family:'Outfit'; font-size: 26px; font-weight:800; text-transform: uppercase;">Quotation</h1>
                            <div style="font-size:11px; color:#64748b; margin-top:4px;">Draft ID: ${quote.quotation_number}</div>
                        </div>
                        <div style="text-align: right; font-size: 12px; color: #334155; line-height: 1.4;">
                            <strong style="font-size:18px; color: #0f172a;">${companyProfile.company_name}</strong><br>
                            ${companyProfile.address_line1}<br>
                            ${companyProfile.address_line2 ? companyProfile.address_line2 + '<br>' : ''}
                            ${companyProfile.city}, ${companyProfile.state} - ${companyProfile.pincode}<br>
                            <strong>GSTIN:</strong> ${companyProfile.gstin}<br>
                            <strong>Email:</strong> ${companyProfile.email} ${companyProfile.mobile ? '| <strong>Phone:</strong> ' + companyProfile.mobile : ''}
                        </div>
                    </div>
                    
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 40px;">
                        <div>
                            <strong style="text-transform: uppercase; color:#ea580c; font-size:11px;">Quotation Prepared For:</strong>
                            <div style="font-size:15px; font-weight:700; color: #0f172a; margin: 6px 0 4px 0;">${quote.company_name}</div>
                            <div style="color: #475569; line-height: 1.4;">
                                <strong>Contact:</strong> ${cli.contact_name} (${cli.designation})<br>
                                <strong>Email:</strong> ${cli.email} | <strong>Mobile:</strong> ${cli.mobile}<br>
                                <strong>GSTIN:</strong> ${cli.gstin || 'Not Provided'}<br>
                                <strong>Address:</strong> ${cli.address_line1}${cli.address_line2 ? ', ' + cli.address_line2 : ''}, ${cli.city}, ${cli.state} - ${cli.pincode}
                            </div>
                        </div>
                        <div style="text-align: right; color: #475569; line-height: 1.4;">
                            <strong style="text-transform: uppercase; color:#ea580c; font-size:11px;">Quotation Information:</strong>
                            <div style="margin-top: 6px;">
                                <strong>Date Issued:</strong> ${formatDate(quote.created_at)}<br>
                                <strong>Valid Until:</strong> ${formatDate(new Date(new Date(quote.created_at).getTime() + (30 * 24 * 60 * 60 * 1000)))} (30 Days)<br>
                                <strong>Status:</strong> <span style="font-weight: 600; color: ${quote.status === 'Approved' ? '#10b981' : quote.status === 'Rejected' ? '#ef4444' : '#f59e0b'};">${quote.status}</span><br>
                                <strong>Prepared By:</strong> ${companyProfile.contact_person}
                            </div>
                        </div>
                    </div>
                    
                    <table style="width:100%; border-collapse: collapse; margin-bottom: 30px;">
                        <thead>
                            <tr style="background-color: #f8fafc; color:#ea580c;">
                                <th style="border: 1px solid #cbd5e1; padding: 12px 10px; width: 6%; text-align: center;">S.No</th>
                                <th style="border: 1px solid #cbd5e1; padding: 12px 10px; width: 50%; text-align: left;">Item/Service Specification</th>
                                <th style="border: 1px solid #cbd5e1; padding: 12px 10px; width: 8%; text-align: center;">Qty</th>
                                <th style="border: 1px solid #cbd5e1; padding: 12px 10px; width: 14%; text-align: right;">Rate (₹)</th>
                                <th style="border: 1px solid #cbd5e1; padding: 12px 10px; width: 10%; text-align: center;">GST</th>
                                <th style="border: 1px solid #cbd5e1; padding: 12px 10px; width: 14%; text-align: right;">Total (₹)</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${rowsHtml}
                        </tbody>
                    </table>
                    
                    <div style="display:grid; grid-template-columns: 1.3fr 1fr; gap: 40px; margin-top: 40px;">
                        <div>
                            <strong style="color:#ea580c; font-size: 11px; text-transform: uppercase;">Terms & Conditions:</strong>
                            <ol style="font-size:10px; color:#64748b; padding-left:14px; margin-top:6px; line-height: 1.6;">
                                <li>Payment terms: 50% advance on approval, remaining 50% post implementation.</li>
                                <li>The rates listed are valid for 30 calendar days from issued date.</li>
                                <li>All disputes are subject to ${companyProfile.city || 'Mumbai'} jurisdiction.</li>
                            </ol>
                            ${bankDetailsHtml}
                        </div>
                        <div style="background-color: #f8fafc; padding: 15px; border-radius: 6px; border: 1px solid #cbd5e1; align-self: flex-start;">
                            <table style="width: 100%; border-collapse: collapse; font-size:13px; line-height: 1.6;">
                                <tr>
                                    <td style="padding: 6px 0; color:#64748b;">Subtotal (Taxable Value):</td>
                                    <td style="padding: 6px 0; text-align: right; font-weight:600;">${formatIndianCurrency(quote.subtotal)}</td>
                                </tr>
                                ${taxBreakdownHtml}
                                <tr>
                                    <td style="padding: 10px 0 0 0; font-size: 16px; font-weight:800; color:#ea580c; border-top: 1px solid #cbd5e1;">Grand Total:</td>
                                    <td style="padding: 10px 0 0 0; text-align: right; font-size: 16px; font-weight:800; color:#ea580c; border-top: 1px solid #cbd5e1;">${formatIndianCurrency(quote.total_amount)}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div style="margin-top: 80px; display:flex; justify-content: space-between; align-items:flex-end;">
                        <div>
                            <div style="border-bottom: 1px solid #cbd5e1; width: 160px; margin-bottom:6px;"></div>
                            <span style="font-size: 10px; color:#64748b; font-weight: 500;">Client Signature / Acceptor</span>
                        </div>
                        <div style="text-align: right;">
                            <div style="border-bottom: 1px solid #cbd5e1; width: 160px; margin-bottom:6px; margin-left: auto;"></div>
                            <span style="font-size: 10px; color:#ea580c; font-weight:700;">For ${companyProfile.company_name}</span>
                        </div>
                    </div>
                `;
                
                printContainer.appendChild(template);
                
                // Run HTML to PDF conversion engine download
                const opt = {
                    margin:       10,
                    filename:     `Quotation_${quote.quotation_number}_${quote.company_name.replace(/\s+/g, '_')}.pdf`,
                    image:        { type: 'jpeg', quality: 0.98 },
                    html2canvas:  { scale: 2 },
                    jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
                };
                
                showNotification('Generating PDF Quotation...', 'info');
                const pdfWindow = window.open('', '_blank');
                if (pdfWindow) {
                    pdfWindow.document.write('<div style="font-family:sans-serif; padding: 20px;">Generating PDF... Please wait.</div>');
                }
                
                html2pdf().set(opt).from(template).outputPdf('bloburl').then(function(pdfUrl) {
                    if (pdfWindow) {
                        pdfWindow.location.href = pdfUrl;
                    } else {
                        // Fallback if popup blocked
                        html2pdf().set(opt).from(template).save();
                    }
                });
                
            } catch (err) {
                console.error(err);
                showNotification('Could not export quotation to PDF format.', 'error');
            }
        }
        // Save Company Settings Form Handler
        function saveCompanySettings(e) {
            e.preventDefault();
            const btn = document.getElementById('save-settings-btn');
            btn.disabled = true;
            btn.innerText = 'Saving...';
            
            const formData = new FormData(document.getElementById('settings-form'));
            
            fetch('?api=save_settings', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    
                    // Update the local companyProfile variable
                    companyProfile.company_name = formData.get('company_name');
                    companyProfile.gstin = formData.get('gstin');
                    companyProfile.address_line1 = formData.get('address_line1');
                    companyProfile.address_line2 = formData.get('address_line2');
                    companyProfile.city = formData.get('city');
                    companyProfile.state = formData.get('state');
                    companyProfile.pincode = formData.get('pincode');
                    companyProfile.country = formData.get('country');
                    companyProfile.email = formData.get('email');
                    companyProfile.mobile = formData.get('mobile');
                    companyProfile.contact_person = formData.get('contact_person');
                    companyProfile.bank_name = formData.get('bank_name');
                    companyProfile.account_number = formData.get('account_number');
                    companyProfile.ifsc_code = formData.get('ifsc_code');
                    
                    // Dynamically update user avatar and user labels across UI
                    document.getElementById('sidebar-user-name').innerText = companyProfile.contact_person;
                    document.getElementById('header-user-name').innerText = companyProfile.contact_person;
                    
                    const names = companyProfile.contact_person.split(' ');
                    let initials = '';
                    names.forEach(n => {
                        if (n) initials += n[0];
                    });
                    initials = initials.toUpperCase().substring(0, 2);
                    document.getElementById('header-user-avatar').innerText = initials;
                    
                    const addedByEl = document.querySelector('input[name="added_by"]');
                    if (addedByEl) addedByEl.value = companyProfile.contact_person;
                    
                    const sentByEl = document.querySelector('input[name="sent_by"]');
                    if (sentByEl) sentByEl.value = companyProfile.contact_person;
                    
                } else {
                    showNotification(data.error || 'Failed to save settings.', 'error');
                }
            })
            .catch(err => {
                console.error(err);
                showNotification('Connection error while saving settings.', 'error');
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerText = 'Save Configurations';
            });
        }

        // Quick Email shortcut redirect from list view
        function emailQuotationQuick(quoteId, clientEmail, companyName) {
            document.querySelector('[data-view="send-email"]').click();
            
            // Wait for DOM values mapping
            setTimeout(() => {
                const toSelect = document.getElementById('email-to-select');
                // Find and select option matching company name
                for (let i = 0; i < toSelect.options.length; i++) {
                    if (toSelect.options[i].text.includes(companyName)) {
                        toSelect.selectedIndex = i;
                        break;
                    }
                }
                
                document.querySelector('select[name="type"]').value = 'Quotation';
                document.querySelector('input[name="subject"]').value = `Quotation Proposal Details for ${companyName}`;
                
                // Prefill template email details
                quillEmailEditor.root.innerHTML = `
                    <p>Dear Sir/Madam,</p>
                    <p>Please find enclosed our quotation proposal regarding the requirements discussed.</p>
                    <p>Kindly check the quotation details ledger in the portal attachment section and approve the transaction.</p>
                    <p>Thank you for your business!</p>
                `;
            }, 150);
        }
        // ==========================================
        //  BULK UPLOAD & SMART SPLIT
        // ==========================================
        let bulkParsedData = [];
        let systemUsersList = [];

        function openBulkUploadModal() {
            window.bulkUploadDestination = null;
            document.getElementById('bulk-upload-modal').style.display = 'flex';
            document.getElementById('bulk-upload-file').value = '';
            document.getElementById('bulk-preview-container').style.display = 'none';
            document.getElementById('bulk-split-checkbox').checked = false;
            toggleSplitOptions();
            
            // Pre-fetch users for dropdowns
            if(systemUsersList.length === 0) {
                fetch('?api=get_users')
                    .then(res => res.json())
                    .then(data => {
                        if(data.users) systemUsersList = data.users;
                    });
            }
        }

        function closeBulkUploadModal() {
            document.getElementById('bulk-upload-modal').style.display = 'none';
        }

        function handleBulkFileSelect(e) {
            const file = e.target.files[0];
            if(!file) return;
            const reader = new FileReader();
            reader.onload = function(evt) {
                try {
                    const data = evt.target.result;
                    const workbook = XLSX.read(data, {type: 'binary'});
                    const firstSheetName = workbook.SheetNames[0];
                    const worksheet = workbook.Sheets[firstSheetName];
                    let json = XLSX.utils.sheet_to_json(worksheet, {defval: ""});
                    
                    // Clean columns mapping
                    bulkParsedData = json.map(row => {
                        return {
                            lead_name: row['Contact Name'] || row['contact_name'] || row['lead_name'] || '',
                            company_name: row['Company Name'] || row['company_name'] || '',
                            mobile: row['Mobile'] || row['mobile'] || row['Phone'] || '',
                            email: row['Email'] || row['email'] || '',
                            lead_source: row['Lead Source'] || row['lead_source'] || 'Cold Call',
                            priority: row['Priority'] || row['priority'] || 'Warm',
                            stage: row['Stage'] || row['stage'] || 'New Lead',
                            assigned_to: row['Assigned To'] || row['assigned_to'] || '',
                            location: row['Location'] || row['location'] || row['City'] || '',
                            notes: row['Notes'] || row['notes'] || ''
                        };
                    }).filter(r => r.lead_name !== '' || r.mobile !== '');
                    
                    document.getElementById('bulk-record-count').innerText = bulkParsedData.length;
                    document.getElementById('bulk-preview-container').style.display = 'block';
                    renderSplitUI();
                } catch(err) {
                    console.error(err);
                    alert("Error parsing file. Ensure it is a valid CSV or Excel file.");
                }
            };
            reader.readAsBinaryString(file);
        }

        function toggleSplitOptions() {
            const isChecked = document.getElementById('bulk-split-checkbox').checked;
            document.getElementById('split-options-container').style.display = isChecked ? 'block' : 'none';
        }

        function renderSplitUI() {
            const uiContainer = document.getElementById('dynamic-split-ui');
            const splitType = document.querySelector('input[name="split_type"]:checked')?.value;
            if(!splitType) {
                uiContainer.style.display = 'none';
                return;
            }
            uiContainer.style.display = 'block';
            
            let userOptions = `<option value="">-- Select Staff --</option>` + systemUsersList.map(u => `<option value="${u.username}">${u.username}</option>`).join('');

            if(splitType === 'random') {
                uiContainer.innerHTML = `
                    <p style="font-size:13px;margin-bottom:10px;">Even rows assigned to Team A, Odd rows to Team B.</p>
                    <div style="display:flex; gap:10px;">
                        <div style="flex:1;"><label>Team A (Even)</label><select id="split-random-a" style="width:100%;padding:6px;border-radius:4px;border:1px solid var(--border);">${userOptions}</select></div>
                        <div style="flex:1;"><label>Team B (Odd)</label><select id="split-random-b" style="width:100%;padding:6px;border-radius:4px;border:1px solid var(--border);">${userOptions}</select></div>
                    </div>
                `;
            } 
            else if(splitType === 'serial') {
                uiContainer.innerHTML = `
                    <p style="font-size:13px;margin-bottom:10px;">Define ranges (e.g., 1-50, 51-100).</p>
                    <div id="serial-ranges-container">
                        <div class="serial-row" style="display:flex;gap:10px;margin-bottom:10px;">
                            <input type="number" placeholder="Start" class="s-start" style="width:70px;padding:4px;border:1px solid var(--border);border-radius:4px;">
                            <input type="number" placeholder="End" class="s-end" style="width:70px;padding:4px;border:1px solid var(--border);border-radius:4px;">
                            <select class="s-staff" style="flex:1;padding:4px;border:1px solid var(--border);border-radius:4px;">${userOptions}</select>
                        </div>
                    </div>
                    <button type="button" class="btn btn-secondary" style="padding:4px 10px;font-size:11px;" onclick="addSerialRow()">+ Add Range</button>
                `;
            }
            else if(splitType === 'location') {
                // Get unique locations
                const locations = [...new Set(bulkParsedData.map(r => String(r.location).trim()).filter(l => l !== ''))];
                if(locations.length === 0) {
                    uiContainer.innerHTML = `<p style="font-size:13px;color:red;">No locations found in the uploaded file.</p>`;
                    return;
                }
                
                let locHTML = `<p style="font-size:13px;margin-bottom:10px;">Assign staff for each location found in file:</p><div id="location-mapping-container">`;
                locations.forEach(loc => {
                    locHTML += `
                        <div class="loc-row" data-loc="${loc}" style="display:flex;gap:10px;margin-bottom:8px;align-items:center;">
                            <span style="flex:1;font-weight:600;">📍 ${loc}</span>
                            <select class="l-staff" style="flex:1;padding:4px;border:1px solid var(--border);border-radius:4px;">${userOptions}</select>
                        </div>
                    `;
                });
                locHTML += `</div>`;
                uiContainer.innerHTML = locHTML;
            }
        }
        
        function addSerialRow() {
            let userOptions = `<option value="">-- Select Staff --</option>` + systemUsersList.map(u => `<option value="${u.username}">${u.username}</option>`).join('');
            const row = document.createElement('div');
            row.className = 'serial-row';
            row.style.cssText = 'display:flex;gap:10px;margin-bottom:10px;';
            row.innerHTML = `
                <input type="number" placeholder="Start" class="s-start" style="width:70px;padding:4px;border:1px solid var(--border);border-radius:4px;">
                <input type="number" placeholder="End" class="s-end" style="width:70px;padding:4px;border:1px solid var(--border);border-radius:4px;">
                <select class="s-staff" style="flex:1;padding:4px;border:1px solid var(--border);border-radius:4px;">${userOptions}</select>
            `;
            document.getElementById('serial-ranges-container').appendChild(row);
        }

        async function processBulkUpload() {
            if(bulkParsedData.length === 0) return showNotification('No data to save.', 'error');
            
            const isChecked = document.getElementById('bulk-split-checkbox').checked;
            let finalData = [...bulkParsedData];
            
            if(isChecked) {
                const splitType = document.querySelector('input[name="split_type"]:checked')?.value;
                if(splitType === 'random') {
                    const teamA = document.getElementById('split-random-a').value;
                    const teamB = document.getElementById('split-random-b').value;
                    finalData.forEach((row, idx) => {
                        row.assigned_to = (idx % 2 === 0) ? teamA : teamB;
                    });
                }
                else if(splitType === 'serial') {
                    const rows = document.querySelectorAll('.serial-row');
                    let ranges = [];
                    rows.forEach(r => {
                        ranges.push({
                            start: parseInt(r.querySelector('.s-start').value),
                            end: parseInt(r.querySelector('.s-end').value),
                            staff: r.querySelector('.s-staff').value
                        });
                    });
                    
                    finalData.forEach((row, idx) => {
                        const serialNum = idx + 1;
                        let assigned = row.assigned_to;
                        for(let rng of ranges) {
                            if(rng.staff && !isNaN(rng.start) && !isNaN(rng.end) && serialNum >= rng.start && serialNum <= rng.end) {
                                assigned = rng.staff;
                                break;
                            }
                        }
                        row.assigned_to = assigned;
                    });
                }
                else if(splitType === 'location') {
                    const locRows = document.querySelectorAll('.loc-row');
                    let mapping = {};
                    locRows.forEach(r => {
                        const loc = r.getAttribute('data-loc');
                        const staff = r.querySelector('.l-staff').value;
                        if(staff) mapping[loc] = staff;
                    });
                    
                    finalData.forEach(row => {
                        if(row.location && mapping[row.location.trim()]) {
                            row.assigned_to = mapping[row.location.trim()];
                        }
                    });
                }
            }
            
            const btn = document.getElementById('btn-save-bulk');
            btn.innerText = 'Saving... Please wait';
            btn.disabled = true;
            
            try {
                const fd = new FormData();
                const action = window.bulkUploadDestination === 'pre_leads' ? 'bulk_upload_preleads' : 'bulk_upload_leads';
                fd.append('leads_json', JSON.stringify(finalData));
                
                const res = await fetch(`?api=${action}`, { method:'POST', body: fd });
                const json = await res.json();
                
                if(json.success) {
                    showNotification(json.success, 'success');
                    closeBulkUploadModal();
                    if(action === 'bulk_upload_preleads') {
                        loadPreLeads();
                    } else {
                        loadLeads();
                    }
                } else {
                    showNotification(json.error || 'Failed to bulk upload', 'error');
                }
            } catch(e) {
                console.error(e);
                showNotification('Connection Error during bulk upload.', 'error');
            } finally {
                btn.innerText = 'Save All Leads';
                btn.disabled = false;
            }
        }
    </script>
<?php endif; ?>
</body>
</html>
