<!DOCTYPE html>
<html lang="en">
<?php $base_path = defined('IS_SUBFOLDER') ? '../' : ''; ?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' - BFS Financial Services' : 'BFS Financial Services - Client Management System'; ?></title>
    
    <!-- PWA Installable App Configuration -->
    <link rel="manifest" href="<?php echo $base_path; ?>manifest.json">
    <meta name="theme-color" content="#0f172a">
    <link rel="apple-touch-icon" href="https://cdn-icons-png.flaticon.com/512/10311/10311651.png">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="mobile-web-app-capable" content="yes">
    
    <!-- Google Fonts Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Lucide Icons for clean iconography -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <style>
        /* Fix SweetAlert overlapping with Client Details Modal */
        .swal2-container { z-index: 99999 !important; }
    

        /* Premium Sidebar UI */
        aside {
            background: linear-gradient(180deg, #0b1121 0%, #111827 100%);
            box-shadow: 4px 0 30px rgba(0,0,0,0.25);
            border-right: 1px solid rgba(255,255,255,0.06);
        }
        .sidebar-menu {
            padding: 12px 16px;
        }
        .menu-item {
            margin-bottom: 8px;
        }
        .menu-item a {
            border-radius: 12px;
            padding: 12px 16px;
            color: #94a3b8;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            z-index: 1;
        }
        .menu-item a:hover {
            background: rgba(255,255,255,0.04);
            color: #f1f5f9;
            transform: translateX(6px);
        }
        
        @keyframes active-pulse {
            0% { box-shadow: inset 0 0 0 rgba(255, 255, 255, 0); }
            50% { box-shadow: inset 40px 0 40px -20px rgba(255, 255, 255, 0.15); }
            100% { box-shadow: inset 0 0 0 rgba(255, 255, 255, 0); }
        }
        @keyframes glow-line {
            0% { opacity: 0.5; box-shadow: 0 0 5px #ffffff; }
            50% { opacity: 1; box-shadow: 0 0 15px 4px #ffffff, 0 0 5px #cbd5e1; }
            100% { opacity: 0.5; box-shadow: 0 0 5px #ffffff; }
        }

        .menu-item.active a {
            background: linear-gradient(90deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0) 100%);
            color: #ffffff;
            border-radius: 12px;
            padding-left: 20px;
            animation: active-pulse 3s infinite ease-in-out;
            text-shadow: 0 0 10px rgba(255,255,255,0.2);
            font-weight: 600;
        }
        
        .menu-item.active a i {
            color: #ffffff;
            filter: drop-shadow(0 0 8px rgba(56, 189, 248, 0.6));
        }

        /* The Animated Glowing Line on the Left */
        .menu-item.active a::before {
            content: '';
            position: absolute;
            left: 0;
            top: 15%;
            bottom: 15%;
            width: 4px;
            background: #ffffff;
            border-radius: 0 4px 4px 0;
            animation: glow-line 2s infinite ease-in-out;
        }


</style>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
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
            /* Minimalist Monochrome Palette (Black & White Luxury) */
            --primary: #0f172a; /* Slate 900 / Deep Black */
            --primary-hover: #000000; /* Pure Black */
            --primary-light: #f1f5f9; /* Soft Grey */
            --primary-border: #e2e8f0; /* Standard Border */
            --primary-glow: rgba(0, 0, 0, 0.15);
            
            --bg-main: #f8fafc;
            --bg-card: #ffffff;
            --sidebar-bg: #000000;
            --sidebar-hover: #111827;
            
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
            
            --border: #e2e8f0;
            --danger: #ef4444;
            --danger-light: #fef2f2;
            --success: #10b981;
            --warning: #f59e0b;
            --warning-light: #fef3c7;
            
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

        /* --- SKELETON LOADERS CSS --- */
        @keyframes shimmer {
            0% { background-position: -1000px 0; }
            100% { background-position: 1000px 0; }
        }

        .skeleton {
            background: #f1f5f9;
            background-image: linear-gradient(90deg, #f1f5f9 0px, #e2e8f0 40px, #f1f5f9 80px);
            background-size: 1000px 100%;
            animation: shimmer 2s infinite linear;
            border-radius: var(--radius-sm);
        }

        .skeleton-text {
            height: 14px;
            margin-bottom: 8px;
            width: 100%;
        }
        
        .skeleton-text.short { width: 50%; }
        .skeleton-text.medium { width: 75%; }
        .skeleton-text.long { width: 90%; }
        .skeleton-title { height: 20px; width: 60%; margin-bottom: 12px; }

        .skeleton-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
        }

        .skeleton-row {
            display: flex;
            gap: 12px;
            align-items: center;
            padding: 12px;
            border-bottom: 1px solid var(--border);
        }

        .skeleton-card {
            padding: 20px;
            background: white;
            border-radius: var(--radius-md);
            border: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        /* -------------------------- */

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
            padding: 16px 12px;
            display: flex;
            flex-direction: column;
            gap: 4px;
            flex-grow: 1;
            overflow-y: auto;
        }

        /* Custom scrollbar for sidebar */
        .sidebar-menu::-webkit-scrollbar { width: 4px; }
        .sidebar-menu::-webkit-scrollbar-track { background: transparent; }
        .sidebar-menu::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 4px; }
        .sidebar-menu::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.2); }

        .menu-item a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 16px;
            color: var(--text-light);
            text-decoration: none;
            border-radius: var(--radius-md);
            font-size: 13.5px;
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
            display: block; /* always block since pages are separate files now */
            animation: fadeIn 0.3s ease-in-out;
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
        input[type="tel"],
        input[type="password"],
        input[type="number"],
        input[type="url"],
        input[type="datetime-local"],
        input[type="file"],
        input[type="date"],
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
            box-shadow: 0 4px 6px -1px var(--primary-glow);
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
            transform: translateY(-1px);
            box-shadow: 0 6px 12px -2px rgba(249, 115, 22, 0.3);
        }

        .btn-secondary {
            background-color: #f1f5f9;
            color: var(--text-primary);
            border: 1px solid #cbd5e1;
        }

        .btn-secondary:hover {
            background-color: #e2e8f0;
            transform: translateY(-1px);
            box-shadow: var(--shadow-sm);
        }

        .btn-accent {
            background-color: var(--sidebar-bg);
            color: white;
        }

        .btn-accent:hover {
            background-color: var(--sidebar-hover);
            transform: translateY(-1px);
        }

        .btn-danger {
            background-color: var(--danger-light);
            color: var(--danger);
            border: 1px solid var(--danger);
        }

        .btn-danger:hover {
            background-color: var(--danger);
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2);
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
            height: calc(100vh - 240px);
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

        .client-card.won {
            border: 1px solid var(--status-won);
            border-left: 4px solid var(--status-won) !important;
            background: linear-gradient(145deg, #ffffff 60%, var(--status-won-light) 100%) !important;
            box-shadow: 0 2px 10px rgba(16, 185, 129, 0.1);
        }
        
        .client-card.won:hover {
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.2);
            border-color: var(--status-won);
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

        /* Data Table Premium Design */
        .table-responsive {
            overflow-x: auto;
            border-radius: var(--radius-md);
            border: 1px solid var(--border);
            background: white;
            box-shadow: var(--shadow-sm);
            margin-top: 10px;
        }

        .data-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 13px; /* Reduced from 14px */
        }

        .data-table th {
            background-color: #f8fafc;
            color: var(--text-muted);
            font-size: 11px; /* Reduced from 12px */
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 12px 16px; /* Reduced padding */
            text-align: left;
            border-bottom: 2px solid var(--border);
            white-space: nowrap;
        }

        .data-table td {
            padding: 10px 16px; /* Reduced from 16px 20px */
            color: var(--text-primary);
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
            transition: background-color var(--transition-fast);
        }

        .data-table tbody tr:hover td {
            background-color: var(--bg-main);
        }

        .data-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Improved Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 700;
            padding: 5px 12px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }

        .badge-info { background-color: #eff6ff; color: #3b82f6; border: 1px solid #bfdbfe; }
        .badge-success { background-color: var(--status-won-light); color: var(--status-won); border: 1px solid #a7f3d0; }
        .badge-warning { background-color: var(--warning-light); color: var(--warning); border: 1px solid #fde68a; }
        .badge-danger { background-color: var(--danger-light); color: var(--danger); border: 1px solid #fecaca; }

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
            height: calc(100vh - 240px);
            overflow-y: auto;
            position: relative;
        }
        
        .client-list-pane::-webkit-scrollbar,
        .client-detail-pane::-webkit-scrollbar {
            width: 0px;
            background: transparent;
        }

        .client-detail-pane.won-theme {
            border: 2px solid var(--status-won);
            background: linear-gradient(to bottom right, #ffffff, var(--status-won-light) 150%);
            box-shadow: 0 4px 20px rgba(16, 185, 129, 0.15);
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
            grid-template-columns: repeat(5, 1fr);
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
            background-color: #0f172a;
            color: white;
        }

        @keyframes pulseGlow {
            0% { box-shadow: 0 0 0 0 rgba(14, 165, 233, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(14, 165, 233, 0); }
            100% { box-shadow: 0 0 0 0 rgba(14, 165, 233, 0); }
        }

        .pipeline-step.active .pipeline-icon-circle {
            background-color: #0ea5e9; /* Vibrant Sky Blue for In-Progress */
            color: white;
            animation: pulseGlow 1.5s infinite;
            border: 2px solid #bae6fd;
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
        
        /* Flex row helper */
        .flex-row {
            display: flex;
        }
        @media (max-width: 768px) {
            .flex-row {
                flex-direction: column;
            }
        }
    

        /* Premium Sidebar UI */
        aside {
            background: linear-gradient(180deg, #0b1121 0%, #111827 100%);
            box-shadow: 4px 0 30px rgba(0,0,0,0.25);
            border-right: 1px solid rgba(255,255,255,0.06);
        }
        .sidebar-menu {
            padding: 12px 16px;
        }
        .menu-item {
            margin-bottom: 8px;
        }
        .menu-item a {
            border-radius: 12px;
            padding: 12px 16px;
            color: #94a3b8;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            z-index: 1;
        }
        .menu-item a:hover {
            background: rgba(255,255,255,0.04);
            color: #f1f5f9;
            transform: translateX(6px);
        }
        
        @keyframes active-pulse {
            0% { box-shadow: inset 0 0 0 rgba(255, 255, 255, 0); }
            50% { box-shadow: inset 40px 0 40px -20px rgba(255, 255, 255, 0.15); }
            100% { box-shadow: inset 0 0 0 rgba(255, 255, 255, 0); }
        }
        @keyframes glow-line {
            0% { opacity: 0.5; box-shadow: 0 0 5px #ffffff; }
            50% { opacity: 1; box-shadow: 0 0 15px 4px #ffffff, 0 0 5px #cbd5e1; }
            100% { opacity: 0.5; box-shadow: 0 0 5px #ffffff; }
        }

        .menu-item.active a {
            background: linear-gradient(90deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0) 100%);
            color: #ffffff;
            border-radius: 12px;
            padding-left: 20px;
            animation: active-pulse 3s infinite ease-in-out;
            text-shadow: 0 0 10px rgba(255,255,255,0.2);
            font-weight: 600;
        }
        
        .menu-item.active a i {
            color: #ffffff;
            filter: drop-shadow(0 0 8px rgba(56, 189, 248, 0.6));
        }

        /* The Animated Glowing Line on the Left */
        .menu-item.active a::before {
            content: '';
            position: absolute;
            left: 0;
            top: 15%;
            bottom: 15%;
            width: 4px;
            background: #ffffff;
            border-radius: 0 4px 4px 0;
            animation: glow-line 2s infinite ease-in-out;
        }


</style>
    
    <script>
        // Global variables populated from PHP
        let companyProfile = <?php echo json_encode($profile); ?>;
        let currentUser = <?php echo isset($_SESSION['user_id']) ? json_encode(['username' => $_SESSION['username'], 'role' => $_SESSION['role']]) : 'null'; ?>;
        
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
            return 'Rs. ' + res;
        }
        
        // Toast notification system
        function showNotification(message, icon = 'info') {
            if (message === 'SESSION_EXPIRED') {
                alert('Your session has expired because your account was logged in from another device.');
                window.location.reload();
                return;
            }
            const container = document.getElementById('toast-container');
            if (!container) return;
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
        
        // User initialization helpers
        async function initUserSelects() {
            if (currentUser && currentUser.role === 'Admin') {
                document.querySelectorAll('.admin-only-field').forEach(el => el.style.display = 'block');
            }
            try {
                const res = await fetch('?api=get_users');
                const users = await res.json();
                if(users && !users.error) {
                    let opts = '<option value="">-- Unassigned --</option>';
                    users.forEach(u => opts += `<option value="${u.username}">${u.name ? u.name + ' (' + u.username + ')' : u.username}</option>`);
                    document.querySelectorAll('.user-select').forEach(sel => sel.innerHTML = opts);
                    
                    let filterOpts = '<option value=""> All Staff</option><option value="unassigned">-- Unassigned --</option>';
                    users.forEach(u => filterOpts += `<option value="${u.username}">${u.name ? u.name + ' (' + u.username + ')' : u.username}</option>`);
                    document.querySelectorAll('.user-filter-select').forEach(sel => sel.innerHTML = filterOpts);
                }
            } catch(e) {}
        }
        document.addEventListener("DOMContentLoaded", () => {
            initUserSelects();
            
            // Ping server every 60 seconds to update last_active and last_ip
            fetch('?api=ping', { method: 'POST' }).catch(() => {}); // Initial ping
            setInterval(() => {
                fetch('?api=ping', { method: 'POST' }).catch(() => {});
            }, 60000);
        });
        
        function logout() {
            fetch('?api=logout').then(() => {
                const inStaff = <?php echo (strpos($_SERVER['SCRIPT_NAME'], '/staff/') !== false) ? 'true' : 'false'; ?>;
                location.href = inStaff ? '../login.php' : 'login.php';
            });
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
</head>
<body>
    <?php $active_page = basename($_SERVER['PHP_SELF']); ?>
    <!-- Sidebar Navigation -->
    <aside>
        <div class="brand-container" style="display:flex; align-items:center; gap: 14px; padding: 24px 20px;">
            <div style="display: flex; align-items: center; justify-content: center; background: transparent; width: auto; height: auto;">
                <img src="<?php echo $base_path; ?>logo.png" alt="BFS Circle Logo" style="height: 40px; width: auto; object-fit: contain; filter: brightness(0) invert(1) drop-shadow(0 2px 8px rgba(255,255,255,0.2)); transition: transform 0.3s ease;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
            </div>
            <div class="brand-name" style="font-family: 'Outfit', sans-serif; font-size: 24px; font-weight: 800; color: #ffffff; letter-spacing: -0.5px; margin-left: 8px;">BFS Circle</div>
        </div>
        <ul class="sidebar-menu">
            <?php if (($_SESSION['role'] ?? '') === 'Admin'): ?>
            <li class="menu-item <?php echo ($active_page === 'dashboard.php') ? 'active' : ''; ?>">
                <a href="<?php echo $base_path; ?>dashboard.php">
                    <i data-lucide="layout-dashboard"></i>
                    <span class="menu-text">Dashboard</span>
                </a>
            </li>
            <li class="menu-item <?php echo ($active_page === 'pre_leads.php') ? 'active' : ''; ?>">
                <a href="<?php echo $base_path; ?>pre_leads.php">
                    <i data-lucide="inbox"></i>
                    <span class="menu-text">Pre-Leads (Raw Data)</span>
                </a>
            </li>
            <li class="menu-item <?php echo ($active_page === 'leads.php') ? 'active' : ''; ?>">
                <a href="<?php echo $base_path; ?>leads.php">
                    <i data-lucide="target"></i>
                    <span class="menu-text">Lead Management</span>
                </a>
            </li>
            <li class="menu-item <?php echo ($active_page === 'field_visits.php') ? 'active' : ''; ?>">
                <a href="<?php echo $base_path; ?>field_visits.php">
                    <i data-lucide="map-pin"></i>
                    <span class="menu-text">Field Visits</span>
                </a>
            </li>


            <li class="menu-item <?php echo ($active_page === 'reminders.php') ? 'active' : ''; ?>">
                <a href="<?php echo $base_path; ?>reminders.php">
                    <i data-lucide="bell"></i>
                    <span class="menu-text">Reminders</span>
                </a>
            </li>
            <li class="menu-item <?php echo ($active_page === 'applicants_list.php') ? 'active' : ''; ?>">
                <a href="<?php echo $base_path; ?>applicants_list.php">
                    <i data-lucide="files"></i>
                    <span class="menu-text">Loan Applications</span>
                </a>
            </li>
            
            <li class="menu-item <?php echo ($active_page === 'client_vault') ? 'active' : ''; ?>">
                <a href="<?php echo $base_path; ?>client_vault/index.php">
                    <i data-lucide="shield-check"></i>
                    <span class="menu-text">Client Vault</span>
                </a>
            </li>

            <li class="menu-item <?php echo ($active_page === 'search_track.php') ? 'active' : ''; ?>">
                <a href="<?php echo $base_path; ?>search_track.php">
                    <i data-lucide="search"></i>
                    <span class="menu-text">Search & CRM Track</span>
                </a>
            </li>
            <li class="menu-item <?php echo ($active_page === 'send_email.php') ? 'active' : ''; ?>">
                <a href="<?php echo $base_path; ?>send_email.php">
                    <i data-lucide="mail"></i>
                    <span class="menu-text">Send Email</span>
                </a>
            </li>
            <li class="menu-item <?php echo ($active_page === 'calculators.php') ? 'active' : ''; ?>">
                <a href="<?php echo $base_path; ?>calculators.php">
                    <i data-lucide="calculator"></i>
                    <span class="menu-text">Financial Calculators</span>
                </a>
            </li>
            <li class="menu-item <?php echo ($active_page === 'activity_log.php') ? 'active' : ''; ?>">
                <a href="<?php echo $base_path; ?>activity_log.php">
                    <i data-lucide="activity"></i>
                    <span class="menu-text">Activity Logs</span>
                </a>
            </li>
            <li class="menu-item <?php echo ($active_page === 'employee_activity.php') ? 'active' : ''; ?>">
                <a href="<?php echo $base_path; ?>employee_activity.php">
                    <i data-lucide="bar-chart-2"></i>
                    <span class="menu-text">Staff Productivity</span>
                </a>
            </li>

            <li class="menu-item <?php echo ($active_page === 'settings.php') ? 'active' : ''; ?>">
                <a href="<?php echo $base_path; ?>settings.php">
                    <i data-lucide="settings"></i>
                    <span class="menu-text">CRM Settings</span>
                </a>
            </li>
            <li class="menu-item <?php echo (basename($_SERVER['PHP_SELF']) === 'payout_distribution.php') ? 'active' : ''; ?>">
                <a href="<?php echo $base_path; ?>payout_distribution.php">
                    <i data-lucide="coins"></i>
                    <span class="menu-text">Payout Distribution</span>
                </a>
            </li>
            <li class="menu-item <?php echo ($active_page === 'payout_settings.php') ? 'active' : ''; ?>">
                <a href="<?php echo $base_path; ?>payout_settings.php">
                    <i data-lucide="percent"></i>
                    <span class="menu-text">Payout Master</span>
                </a>
            </li>
            <li class="menu-item <?php echo ($active_page === 'employees_list.php' || $active_page === 'add_employee.php' || $active_page === 'view_employee.php') ? 'active' : ''; ?>">
                <a href="<?php echo $base_path; ?>employees_list.php">
                    <i data-lucide="contact"></i>
                    <span class="menu-text">Staff & HRMS</span>
                </a>
            </li>
            <li class="menu-item <?php echo ($active_page === 'bankers_list.php' || $active_page === 'add_banker.php' || $active_page === 'view_banker.php') ? 'active' : ''; ?>">
                <a href="<?php echo $base_path; ?>bankers_list.php">
                    <i data-lucide="users"></i>
                    <span class="menu-text">Bankers Directory</span>
                </a>
            </li>
            <li class="menu-item <?php echo ($active_page === 'referrals_list.php' || $active_page === 'add_referral.php' || $active_page === 'view_referral.php') ? 'active' : ''; ?>">
                <a href="<?php echo $base_path; ?>referrals_list.php">
                    <i data-lucide="briefcase"></i>
                    <span class="menu-text">Referrals / DSA</span>
                </a>
            </li>
            <?php else: ?>
            <li class="menu-item <?php echo ($active_page === 'dashboard.php') ? 'active' : ''; ?>">
                <a href="<?php echo $base_path; ?>dashboard.php">
                    <i data-lucide="layout-dashboard"></i>
                    <span class="menu-text">Dashboard</span>
                </a>
            </li>
            <li class="menu-item <?php echo ($active_page === 'pre_leads.php') ? 'active' : ''; ?>">
                <a href="<?php echo $base_path; ?>pre_leads.php">
                    <i data-lucide="inbox"></i>
                    <span class="menu-text">My Pre-Leads (Raw Data)</span>
                </a>
            </li>
            <li class="menu-item <?php echo ($active_page === 'leads.php') ? 'active' : ''; ?>">
                <a href="<?php echo $base_path; ?>leads.php">
                    <i data-lucide="target"></i>
                    <span class="menu-text">Lead Management</span>
                </a>
            </li>

            <li class="menu-item <?php echo ($active_page === 'reminders.php') ? 'active' : ''; ?>">
                <a href="<?php echo $base_path; ?>reminders.php">
                    <i data-lucide="bell"></i>
                    <span class="menu-text">Reminders</span>
                </a>
            </li>
            <li class="menu-item <?php echo ($active_page === 'visits.php' || $active_page === 'my_route.php' || $active_page === 'add_visit.php') ? 'active' : ''; ?>">
                <a href="<?php echo $base_path; ?>visits.php">
                    <i data-lucide="map-pin"></i>
                    <span class="menu-text">My Field Visits</span>
                </a>
            </li>
            <li class="menu-item <?php echo ($active_page === 'applicants_list.php') ? 'active' : ''; ?>">
                <a href="<?php echo $base_path; ?>applicants_list.php">
                    <i data-lucide="files"></i>
                    <span class="menu-text">Loan Applications</span>
                </a>
            </li>
            
            <li class="menu-item <?php echo ($active_page === 'client_vault') ? 'active' : ''; ?>">
                <a href="<?php echo $base_path; ?>client_vault/index.php">
                    <i data-lucide="shield-check"></i>
                    <span class="menu-text">Client Vault</span>
                </a>
            </li>


            <li class="menu-item <?php echo ($active_page === 'search_track.php') ? 'active' : ''; ?>">
                <a href="<?php echo $base_path; ?>search_track.php">
                    <i data-lucide="search"></i>
                    <span class="menu-text">CRM Search</span>
                </a>
            </li>
            <li class="menu-item <?php echo ($active_page === 'send_email.php') ? 'active' : ''; ?>">
                <a href="<?php echo $base_path; ?>send_email.php">
                    <i data-lucide="mail"></i>
                    <span class="menu-text">Send Email</span>
                </a>
            </li>
            <li class="menu-item <?php echo ($active_page === 'calculators.php') ? 'active' : ''; ?>">
                <a href="<?php echo $base_path; ?>calculators.php">
                    <i data-lucide="calculator"></i>
                    <span class="menu-text">Financial Calculators</span>
                </a>
            </li>
            <li class="menu-item <?php echo ($active_page === 'activity_log.php') ? 'active' : ''; ?>">
                <a href="<?php echo $base_path; ?>activity_log.php">
                    <i data-lucide="activity"></i>
                    <span class="menu-text">Activity Logs</span>
                </a>
            </li>
            <li class="menu-item <?php echo ($active_page === 'employees_list.php' || $active_page === 'view_employee.php') ? 'active' : ''; ?>">
                <a href="<?php echo $base_path; ?>employees_list.php">
                    <i data-lucide="contact"></i>
                    <span class="menu-text">Staff & HRMS</span>
                </a>
            </li>
            <li class="menu-item <?php echo ($active_page === 'bankers_list.php' || $active_page === 'view_banker.php') ? 'active' : ''; ?>">
                <a href="<?php echo $base_path; ?>bankers_list.php">
                    <i data-lucide="users"></i>
                    <span class="menu-text">Bankers Directory</span>
                </a>
            </li>
            <li class="menu-item <?php echo ($active_page === 'referrals_list.php' || $active_page === 'view_referral.php') ? 'active' : ''; ?>">
                <a href="<?php echo $base_path; ?>referrals_list.php">
                    <i data-lucide="briefcase"></i>
                    <span class="menu-text">Referrals / DSA</span>
                </a>
            </li>
            <?php endif; ?>
        </ul>
        <div class="sidebar-footer">
            <div>Logged in as:</div>
            <strong id="sidebar-user-name" style="color: white;"><?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?></strong>
            <span style="font-size: 10px; color: var(--text-light); margin-top: 4px; display:block;">Role: <?php echo htmlspecialchars($_SESSION['role'] ?? ''); ?></span>
            <button onclick="logout()" class="btn btn-secondary" style="width: 100%; margin-top: 10px; padding: 5px; font-size: 12px; background: transparent; border: 1px solid rgba(255,255,255,0.15); color: var(--text-light);">Logout</button>
        </div>
    </aside>

    <!-- Main Content Workspace -->
    <main>
        
        <!-- Header -->
        <header class="main-header" style="border-bottom:1px solid #f1f5f9; padding-bottom:16px; margin-bottom:24px;">
            <div class="page-title">
                <h1 id="view-title" style="font-size:24px; font-weight:800; color:#0f172a; letter-spacing:-0.03em; margin:0; display:flex; align-items:center; gap:10px;">
                    <i data-lucide="layout-dashboard" style="color:#0f172a; width:24px; height:24px;"></i>
                    <?php echo htmlspecialchars($page_title ?? 'Dashboard'); ?>
                </h1>
                <p id="view-subtitle" style="margin:6px 0 0 34px; color:#64748b; font-size:13px; font-weight:500;"><?php echo htmlspecialchars($page_subtitle ?? ''); ?></p>
            </div>
        </header>

