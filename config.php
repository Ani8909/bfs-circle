<?php
date_default_timezone_set('Asia/Kolkata');
session_start();

if (isset($_GET['logout'])) {
    session_destroy();
    $_SESSION = [];
    $redirect_url = strpos($_SERVER['SCRIPT_NAME'], '/staff/') !== false || strpos($_SERVER['SCRIPT_NAME'], '/partner/') !== false || strpos($_SERVER['SCRIPT_NAME'], '/agent/') !== false || strpos($_SERVER['SCRIPT_NAME'], '/builder/') !== false ? '../login.php' : 'login.php';
    header("Location: " . $redirect_url);
    exit;
}

// Database path configuration
$db_file = __DIR__ . '/crm.db';
$uploads_dir = __DIR__ . '/uploads';

// Initialize directory for attachments
if (!file_exists($uploads_dir)) {
    mkdir($uploads_dir, 0777, true);
}
if (!file_exists($uploads_dir . '/bankers')) {
    mkdir($uploads_dir . '/bankers', 0777, true);
}
if (!file_exists($uploads_dir . '/referrals')) {
    mkdir($uploads_dir . '/referrals', 0777, true);
}
if (!file_exists($uploads_dir . '/applicants')) {
    mkdir($uploads_dir . '/applicants', 0777, true);
}
if (!file_exists($uploads_dir . '/employees')) {
    mkdir($uploads_dir . '/employees', 0777, true);
}
if (!file_exists($uploads_dir . '/field_visits')) {
    mkdir($uploads_dir . '/field_visits', 0777, true);
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

    
    try { $db->exec("ALTER TABLE users ADD COLUMN staff_type TEXT"); } catch (Exception $e) {}
    try { $db->exec("ALTER TABLE users ADD COLUMN has_dashboard INTEGER DEFAULT 1"); } catch (Exception $e) {}
    try { $db->exec("ALTER TABLE users ADD COLUMN plain_password TEXT"); } catch (Exception $e) {}
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

    try { $db->exec("ALTER TABLE leads ADD COLUMN requirement TEXT"); } catch (Exception $e) {}
    try { $db->exec("ALTER TABLE leads ADD COLUMN loan_amount REAL"); } catch (Exception $e) {}
    try { $db->exec("ALTER TABLE leads ADD COLUMN secondary_mobile TEXT"); } catch (Exception $e) {}
    try { $db->exec("ALTER TABLE leads ADD COLUMN source_data TEXT"); } catch (Exception $e) {}
    try { $db->exec("ALTER TABLE leads ADD COLUMN added_by TEXT"); } catch (Exception $e) {}
    try { $db->exec("ALTER TABLE leads ADD COLUMN photo TEXT"); } catch (Exception $e) {}

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

    try { $db->exec("ALTER TABLE pre_leads ADD COLUMN assigned_to TEXT"); } catch (Exception $e) {}
    try { $db->exec("ALTER TABLE pre_leads ADD COLUMN assigned_at DATETIME"); } catch (Exception $e) {}
    try { $db->exec("ALTER TABLE leads ADD COLUMN assigned_to TEXT"); } catch (Exception $e) {}
    try { $db->exec("ALTER TABLE leads ADD COLUMN assigned_at DATETIME"); } catch (Exception $e) {}
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
    
    try { $db->exec("ALTER TABLE clients ADD COLUMN assigned_to TEXT"); } catch (Exception $e) {}

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
        rejection_reason TEXT,
        subtotal REAL NOT NULL,
        gst_amount REAL NOT NULL,
        total_amount REAL NOT NULL,
        items_json TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(client_id) REFERENCES clients(id)
    )");
    
    try { $db->exec("ALTER TABLE quotations ADD COLUMN rejection_reason TEXT"); } catch (Exception $e) {}

    
    $db->exec("CREATE TABLE IF NOT EXISTS applicant_pd_reports (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        applicant_id INTEGER UNIQUE,
        pd_conducted_by TEXT,
        pd_date TEXT,
        pd_mode TEXT,
        business_board_seen TEXT,
        stock_status TEXT,
        business_stability TEXT,
        monthly_turnover REAL,
        residence_type TEXT,
        years_at_address REAL,
        locality_classification TEXT,
        neighbor_feedback TEXT,
        consumer_durables TEXT,
        lifestyle_score TEXT,
        positive_triggers TEXT,
        negative_triggers TEXT,
        recommended_loan_amount REAL,
        final_pd_status TEXT,
        pd_report_path TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (applicant_id) REFERENCES applicants(id)
    )");

    try { $db->exec("ALTER TABLE applicant_documents ADD COLUMN owner_type TEXT DEFAULT 'Applicant'"); } catch (Exception $e) {}
    try { $db->exec("ALTER TABLE applicant_documents ADD COLUMN owner_id INTEGER DEFAULT NULL"); } catch (Exception $e) {}

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
    // Advanced reminder columns - safe migration
    try { $db->exec("ALTER TABLE reminders ADD COLUMN title TEXT DEFAULT ''"); } catch (Exception $e) {}
    try { $db->exec("ALTER TABLE reminders ADD COLUMN priority TEXT DEFAULT 'Medium'"); } catch (Exception $e) {}
    try { $db->exec("ALTER TABLE reminders ADD COLUMN reference_type TEXT DEFAULT 'Lead'"); } catch (Exception $e) {}
    try { $db->exec("ALTER TABLE reminders ADD COLUMN reference_id TEXT DEFAULT ''"); } catch (Exception $e) {}
    try { $db->exec("ALTER TABLE reminders ADD COLUMN reference_label TEXT DEFAULT ''"); } catch (Exception $e) {}
    try { $db->exec("ALTER TABLE reminders ADD COLUMN reminder_category TEXT DEFAULT 'Follow-up'"); } catch (Exception $e) {}
    try { $db->exec("ALTER TABLE reminders ADD COLUMN recurrence TEXT DEFAULT 'None'"); } catch (Exception $e) {}
    try { $db->exec("ALTER TABLE reminders ADD COLUMN snoozed_until DATETIME"); } catch (Exception $e) {}
    try { $db->exec("ALTER TABLE reminders ADD COLUMN completed_at DATETIME"); } catch (Exception $e) {}
    try { $db->exec("ALTER TABLE reminders ADD COLUMN completed_by TEXT"); } catch (Exception $e) {}
    // Migrate old reminders - set reference_type and reference_id from lead_type/lead_id
    try { $db->exec("UPDATE reminders SET reference_type = lead_type, reference_id = CAST(lead_id AS TEXT) WHERE (reference_type IS NULL OR reference_type = '') AND lead_id > 0"); } catch (Exception $e) {}

    $db->exec("CREATE TABLE IF NOT EXISTS call_logs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        lead_type TEXT NOT NULL,
        lead_id INTEGER NOT NULL,
        caller TEXT NOT NULL,
        response TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS company_profile (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        company_name TEXT DEFAULT 'BFS Financial Services Solutions',
        address_line1 TEXT DEFAULT 'Plot 12, Hitech Lane, Bandra West',
        address_line2 TEXT DEFAULT '',
        city TEXT DEFAULT 'Mumbai',
        state TEXT DEFAULT 'Maharashtra',
        pincode TEXT DEFAULT '400050',
        country TEXT DEFAULT 'India',
        gstin TEXT DEFAULT '27AABCA9087A1Z0',
        email TEXT DEFAULT 'info@bfsBFS Financial Services.com',
        mobile TEXT DEFAULT '9876543210',
        contact_person TEXT DEFAULT 'Rahul Sharma',
        bank_name TEXT DEFAULT 'HDFC Bank',
        account_number TEXT DEFAULT '50100987654321',
        ifsc_code TEXT DEFAULT 'HDFC0001234'
    )");

    $prof_count = $db->query("SELECT COUNT(*) FROM company_profile")->fetchColumn();
    if ($prof_count == 0) {
        $db->exec("INSERT INTO company_profile (company_name, address_line1, address_line2, city, state, pincode, country, gstin, email, mobile, contact_person, bank_name, account_number, ifsc_code) 
                   VALUES ('BFS Financial Services Solutions', 'Plot 12, Hitech Lane, Bandra West', '', 'Mumbai', 'Maharashtra', '400050', 'India', '27AABCA9087A1Z0', 'info@bfsBFS Financial Services.com', '9876543210', 'Rahul Sharma', 'HDFC Bank', '50100987654321', 'HDFC0001234')");
    }

    // Safe SQLite Alter Table for SMTP fields
    try { $db->exec("ALTER TABLE company_profile ADD COLUMN smtp_host TEXT DEFAULT ''"); } catch (Exception $e) {}
    try { $db->exec("ALTER TABLE company_profile ADD COLUMN smtp_port TEXT DEFAULT ''"); } catch (Exception $e) {}
    try { $db->exec("ALTER TABLE company_profile ADD COLUMN smtp_username TEXT DEFAULT ''"); } catch (Exception $e) {}
    try { $db->exec("ALTER TABLE company_profile ADD COLUMN smtp_password TEXT DEFAULT ''"); } catch (Exception $e) {}
    try { $db->exec("ALTER TABLE company_profile ADD COLUMN smtp_encryption TEXT DEFAULT ''"); } catch (Exception $e) {}

    // Bankers table
    $db->exec("CREATE TABLE IF NOT EXISTS bankers (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        full_name TEXT NOT NULL,
        bank_name TEXT NOT NULL,
        designation TEXT,
        state TEXT,
        city TEXT,
        address TEXT,
        ifsc_code TEXT,
        contact_number TEXT NOT NULL,
        official_email TEXT,
        loan_category TEXT,
        min_loan_limit REAL DEFAULT 0,
        max_loan_limit REAL DEFAULT 0,
        coverage_type TEXT,
        coverage_details TEXT,
        employee_id TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    try { $db->exec("ALTER TABLE bankers ADD COLUMN coverage_type TEXT"); } catch (Exception $e) {}

    try { $db->exec("ALTER TABLE bankers ADD COLUMN bank_type TEXT"); } catch (Exception $e) {}
    try { $db->exec("ALTER TABLE bankers ADD COLUMN pincode TEXT"); } catch (Exception $e) {}
    try { $db->exec("ALTER TABLE bankers ADD COLUMN dsa_code TEXT"); } catch (Exception $e) {}
    
    $db->exec("CREATE TABLE IF NOT EXISTS ifsc_master (
        ifsc TEXT PRIMARY KEY,
        bank TEXT,
        branch TEXT,
        address TEXT,
        city TEXT,
        state TEXT
    )");

    try { $db->exec("ALTER TABLE bankers ADD COLUMN coverage_details TEXT"); } catch (Exception $e) {}

    // Employees / HRMS table
    $db->exec("CREATE TABLE IF NOT EXISTS employees (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER,
        emp_id TEXT UNIQUE NOT NULL,
        full_name TEXT NOT NULL,
        official_email TEXT UNIQUE,
        personal_email TEXT,
        mobile TEXT NOT NULL,
        emergency_contact_name TEXT,
        emergency_relation TEXT,
        emergency_phone TEXT,
        current_address TEXT,
        permanent_address TEXT,
        department TEXT NOT NULL,
        designation TEXT NOT NULL,
        reporting_manager TEXT,
        doj TEXT,
        access_role TEXT NOT NULL,
        work_mode TEXT,
        team_specific_data TEXT, -- JSON for conditional fields
        pan_number TEXT,
        aadhar_number TEXT,
        bank_holder_name TEXT,
        bank_account_no TEXT,
        bank_name TEXT,
        bank_ifsc TEXT,
        photo_path TEXT,
        aadhar_path TEXT,
        pan_path TEXT,
        commission_rate REAL DEFAULT 1.0,
        marksheet_path TEXT,
        relieving_letter_path TEXT,
        cheque_path TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(user_id) REFERENCES users(id)
    )");

    // Referrals table
    
try {
    $db->exec("ALTER TABLE referrals ADD COLUMN aadhar_document_path TEXT");
} catch (Exception $e) {}

    $db->exec("CREATE TABLE IF NOT EXISTS referrals (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        referral_id TEXT UNIQUE NOT NULL,
        referrer_type TEXT NOT NULL,
        full_name TEXT NOT NULL,
        dob TEXT,
        mobile TEXT NOT NULL,
        email TEXT,
        city_state TEXT,
        account_name TEXT,
        bank_name TEXT,
        account_number TEXT,
        ifsc_code TEXT,
        upi_id TEXT,
        commission_rate TEXT,
        payout_frequency TEXT,
        pan_number TEXT,
        aadhar_number TEXT,
        bank_document_path TEXT,
        pan_document_path TEXT,
        mapped_branch TEXT,
        assigned_rm TEXT,
        status TEXT DEFAULT 'Active',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // Applicants (Phase 1)
    $db->exec("CREATE TABLE IF NOT EXISTS applicants (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        loan_id TEXT UNIQUE NOT NULL,
        customer_name TEXT NOT NULL,
        mobile TEXT NOT NULL,
        email TEXT,
        address TEXT,
        city TEXT,
        state TEXT,
        pincode TEXT,
        pan_number TEXT,
        aadhar_number TEXT,
        employment_type TEXT,
        monthly_income REAL,
        cibil_score INTEGER,
        loan_type TEXT,
        loan_sub_type TEXT,
        loan_amount_requested REAL,
        interest_rate REAL,
        tenure_months INTEGER,
        emi REAL,
        lead_source TEXT,
        referral_id TEXT,
        employee_id TEXT,
        overall_status TEXT DEFAULT 'Phase 1', -- 'Phase 1', 'Phase 2', 'Phase 3', 'Phase 4', 'Completed', 'Rejected'
        added_by TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // Applicant Documents (Phase 2)
    $db->exec("CREATE TABLE IF NOT EXISTS applicant_documents (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        applicant_id INTEGER NOT NULL,
        document_category TEXT,
        document_name TEXT,
        file_path TEXT,
        uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(applicant_id) REFERENCES applicants(id)
    )");

    // Applicant Disbursements (Phase 3)
    $db->exec("CREATE TABLE IF NOT EXISTS applicant_disbursements (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        applicant_id INTEGER NOT NULL,
        phase_number INTEGER,
        phase_name TEXT,
        amount REAL,
        status TEXT DEFAULT 'Pending', -- 'Pending', 'Disbursed'
        disbursed_at DATETIME,
        remarks TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(applicant_id) REFERENCES applicants(id)
    )");

    // Applicant Bank Assignments (Phase 4)
    $db->exec("CREATE TABLE IF NOT EXISTS applicant_bank_assignments (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        applicant_id INTEGER NOT NULL,
        bank_name TEXT NOT NULL,
        assigned_date DATETIME DEFAULT CURRENT_TIMESTAMP,
        status TEXT DEFAULT 'Pending', -- 'Pending', 'Approved', 'Rejected'
        rejection_reason TEXT,
        approval_date DATETIME,
        FOREIGN KEY(applicant_id) REFERENCES applicants(id)
    )");

        $db->exec("CREATE TABLE IF NOT EXISTS staff_attendance (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT NOT NULL,
        att_date DATE NOT NULL,
        punch_in DATETIME,
        punch_out DATETIME,
        status TEXT DEFAULT 'Present',
        shift_duration TEXT
    )");
    
    // Check missing columns

    $db->exec("CREATE TABLE IF NOT EXISTS co_applicants (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        applicant_id INTEGER NOT NULL,
        is_financial INTEGER DEFAULT 0,
        relationship TEXT,
        full_name TEXT,
        mobile TEXT,
        email TEXT,
        dob TEXT,
        pan_number TEXT,
        aadhar_number TEXT,
        same_address INTEGER DEFAULT 0,
        address TEXT,
        pincode TEXT,
        state TEXT,
        city TEXT,
        employment_type TEXT,
        monthly_income REAL,
        current_emis REAL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(applicant_id) REFERENCES applicants(id)
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS bank_payout_settings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        bank_name TEXT NOT NULL,
        loan_type TEXT NOT NULL,
        payout_percentage REAL DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS staff_location_logs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT NOT NULL,
        lat TEXT,
        lon TEXT,
        battery TEXT,
        status TEXT,
        logged_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS payout_distributions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        payout_id INTEGER,
        recipient_type TEXT,
        recipient_id TEXT,
        amount REAL,
        status TEXT DEFAULT 'Pending',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
// Field Visits Module
    $db->exec("CREATE TABLE IF NOT EXISTS field_visits (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        visit_date DATE NOT NULL,
        executive_name TEXT NOT NULL,
        person_name TEXT NOT NULL,
        mobile TEXT NOT NULL,
        alt_mobile TEXT,
        profession TEXT NOT NULL,
        custom_profession TEXT,
        firm_name TEXT NOT NULL,
        state TEXT NOT NULL,
        city TEXT NOT NULL,
        full_address TEXT,
        pincode TEXT,
        lead_quality TEXT,
        photo_path TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    try { $db->exec("ALTER TABLE field_visits ADD COLUMN full_address TEXT"); } catch (Exception $e) {}
    
    $db->exec("CREATE TABLE IF NOT EXISTS field_visit_followups (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        visit_id INTEGER NOT NULL,
        remarks TEXT NOT NULL,
        next_meeting_date DATE,
        added_by TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(visit_id) REFERENCES field_visits(id)
    )");

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
$is_in_agent_dir = (strpos($_SERVER['SCRIPT_NAME'], '/agent/') !== false);
$is_in_partner_dir = (strpos($_SERVER['SCRIPT_NAME'], '/partner/') !== false);
$is_in_builder_dir = (strpos($_SERVER['SCRIPT_NAME'], '/builder/') !== false);
$is_in_ca_dir = (strpos($_SERVER['SCRIPT_NAME'], '/ca/') !== false);
$is_in_client_vault = (strpos($_SERVER['SCRIPT_NAME'], '/client_vault/') !== false);
$is_in_subfolder = $is_in_staff_dir || $is_in_agent_dir || $is_in_partner_dir || $is_in_builder_dir || $is_in_ca_dir || $is_in_client_vault || defined('IS_SUBFOLDER');

  // Authentication check for page load (excluding login.php)
if (!isset($_SESSION['user_id']) && $current_page !== 'login.php' && $current_page !== 'apply.php') {
    // If not logged in and calling an API, return unauthorized
    if (isset($_GET['api'])) {
        if ($_GET['api'] !== 'login') {
            return_json(['error' => 'Unauthorized Access. Please login.'], 401);
        }
    } else {
        $redirect_prefix = $is_in_subfolder ? '../' : '';
        header("Location: " . $redirect_prefix . "login.php");
        exit;
    }
}

// Redirect and protect pages based on user roles (only for page loads, not APIs)
if (isset($_SESSION['user_id']) && !isset($_GET['api'])) {
    $role = $_SESSION['role'] ?? 'Staff';
    
    // STRICT SECURITY ISOLATION: 
    // If a non-admin role tries to access ANY root admin file (e.g. leads.php, employees.php), force redirect them to their portal.
    if (!$is_in_subfolder && $current_page !== 'logout.php' && $current_page !== 'login.php' && $current_page !== 'apply.php') {
        if ($role === 'Staff') {
            header("Location: staff/index.php");
            exit;
        } else if ($role === 'CA') {
            header("Location: ca/index.php");
            exit;
        } else if ($role === 'Partner') {
            header("Location: partner/index.php");
            exit;
        } else if ($role === 'Agent') {
            header("Location: agent/index.php");
            exit;
        }
    }
    
    // For Admins/Managers hitting index or login
    if (($current_page === 'login.php' || $current_page === 'index.php' || $current_page === '') && !$is_in_subfolder) {
        header("Location: dashboard.php");
        exit;
    }
}

// Handle API requests globally
if (isset($_GET['api'])) {
    require_once __DIR__ . '/api.php';
    exit;
}
