import os

config_path = r'c:\Users\pc\Downloads\client mgmt2\config.php'
with open(config_path, 'r', encoding='utf-8') as f:
    config = f.read()

target = """// Redirect and protect pages based on user roles (only for page loads, not APIs)
if (isset($_SESSION['user_id']) && !isset($_GET['api'])) {
    $role = $_SESSION['role'] ?? 'Staff';
    if (($current_page === 'login.php' || $current_page === 'index.php' || $current_page === '') && !$is_in_subfolder) {
        if ($role === 'Staff') {
            header("Location: staff/index.php");
        } else if ($role === 'CA') {
            header("Location: ca/index.php");
        } else {
            header("Location: dashboard.php");
        }
        exit;
    }
}"""

repl = """// Redirect and protect pages based on user roles (only for page loads, not APIs)
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
}"""

config = config.replace(target, repl)

with open(config_path, 'w', encoding='utf-8') as f:
    f.write(config)
print("Applied strict security isolation in config.php")
