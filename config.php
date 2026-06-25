<?php
date_default_timezone_set('Asia/Kolkata');
session_start();

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
        name TEXT,
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

    try {
        $db->exec("ALTER TABLE users ADD COLUMN last_active DATETIME");
    } catch (Exception $e) {}

    try {
        $db->exec("ALTER TABLE users ADD COLUMN last_ip TEXT");
    } catch (Exception $e) {}

    $db->exec("CREATE TABLE IF NOT EXISTS email_templates (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        template_name TEXT NOT NULL,
        type TEXT NOT NULL,
        subject TEXT NOT NULL,
        body TEXT NOT NULL,
        attachment_name TEXT,
        delete_requested INTEGER DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    $db->exec("CREATE TABLE IF NOT EXISTS presentations (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        original_name TEXT NOT NULL,
        filename TEXT NOT NULL,
        delete_requested INTEGER DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    try { $db->exec("ALTER TABLE email_templates ADD COLUMN delete_requested INTEGER DEFAULT 0"); } catch (Exception $e) {}
    try { $db->exec("ALTER TABLE presentations ADD COLUMN delete_requested INTEGER DEFAULT 0"); } catch (Exception $e) {}

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
        overall_status TEXT DEFAULT 'New', -- 'New', 'Contacted', 'In Negotiation', 'Closed Won', 'Closed Lost'
        assigned_to TEXT
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
        username TEXT,
        description TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    try { $db->exec("ALTER TABLE activities ADD COLUMN username TEXT"); } catch (Exception $e) {}
    try { $db->exec("ALTER TABLE activities ADD COLUMN action_link TEXT"); } catch (Exception $e) {}
    try { $db->exec("ALTER TABLE activities ADD COLUMN target_user TEXT"); } catch (Exception $e) {}

    $db->exec("CREATE TABLE IF NOT EXISTS reminders (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        lead_type TEXT NOT NULL,
        lead_id INTEGER NOT NULL,
        assigned_to TEXT NOT NULL,
        remind_at DATETIME NOT NULL,
        notes TEXT,
        status TEXT DEFAULT 'Pending',
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

} catch (PDOException $e) {
    die("Database Initialization Failed: " . $e->getMessage());
}

// Load company profile details
$profile = $db->query("SELECT * FROM company_profile LIMIT 1")->fetch();

// Helper functions
function return_json($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function log_activity($description, $action_link = null, $target_user = null) {
    global $db;
    $user = $_SESSION['username'] ?? 'System';
    $desc = "[$user] " . $description;
    
    // Ensure columns exist
    try { $db->exec("ALTER TABLE activities ADD COLUMN action_link TEXT"); } catch (Exception $e) {}
    try { $db->exec("ALTER TABLE activities ADD COLUMN target_user TEXT"); } catch (Exception $e) {}
    
    $stmt = $db->prepare("INSERT INTO activities (username, description, action_link, target_user) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user, $desc, $action_link, $target_user]);
}

// Page access & API router
$current_page = basename($_SERVER['PHP_SELF']);
$is_in_staff_dir = (strpos($_SERVER['SCRIPT_NAME'], '/staff/') !== false);

// Authentication check for page load (excluding login.php)
if (!isset($_SESSION['user_id']) && $current_page !== 'login.php') {
    // If not logged in and calling an API, return unauthorized
    if (isset($_GET['api'])) {
        if ($_GET['api'] !== 'login') {
            return_json(['error' => 'Unauthorized Access. Please login.'], 401);
        }
    } else {
        $redirect_prefix = $is_in_staff_dir ? '../' : '';
        header("Location: " . $redirect_prefix . "login.php");
        exit;
    }
}

// Redirect and protect pages based on user roles (only for page loads, not APIs)
if (isset($_SESSION['user_id']) && !isset($_GET['api'])) {
    $role = $_SESSION['role'] ?? 'Staff';
    if ($current_page === 'login.php') {
        if ($role === 'Admin') {
            header("Location: dashboard.php");
        } else {
            header("Location: staff/dashboard.php");
        }
        exit;
    }
    
    // Protect root pages from Staff members
    if ($role === 'Staff' && !$is_in_staff_dir) {
        header("Location: staff/dashboard.php");
        exit;
    }
    
    // Protect staff pages from Admin users
    if ($role === 'Admin' && $is_in_staff_dir) {
        header("Location: ../dashboard.php");
        exit;
    }
}

// Handle API requests globally
if (isset($_GET['api'])) {
    require_once __DIR__ . '/api.php';
    exit;
}
