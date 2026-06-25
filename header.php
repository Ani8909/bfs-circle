<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' - AuraCRM' : 'AuraCRM - Client Management System'; ?></title>
    
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
        input[type="number"],
        input[type="url"],
        input[type="datetime-local"],
        input[type="file"],
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

        .btn-danger {
            background-color: var(--danger-light);
            color: var(--danger);
            border: 1px solid var(--danger);
        }

        .btn-danger:hover {
            background-color: var(--danger);
            color: white;
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
        
        /* Flex row helper */
        .flex-row {
            display: flex;
        }
        @media (max-width: 768px) {
            .flex-row {
                flex-direction: column;
            }
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
        <div class="brand-container">
            <div class="brand-logo">A</div>
            <div class="brand-name">AuraCRM</div>
        </div>
        <ul class="sidebar-menu">
            <?php if (($_SESSION['role'] ?? '') === 'Admin'): ?>
            <li class="menu-item <?php echo ($active_page === 'dashboard.php') ? 'active' : ''; ?>">
                <a href="dashboard.php">
                    <i data-lucide="layout-dashboard"></i>
                    <span class="menu-text">Dashboard</span>
                </a>
            </li>
            <li class="menu-item <?php echo ($active_page === 'pre_leads.php') ? 'active' : ''; ?>">
                <a href="pre_leads.php">
                    <i data-lucide="inbox"></i>
                    <span class="menu-text">Pre-Leads (Raw Data)</span>
                </a>
            </li>
            <li class="menu-item <?php echo ($active_page === 'leads.php') ? 'active' : ''; ?>">
                <a href="leads.php">
                    <i data-lucide="target"></i>
                    <span class="menu-text">Lead Management</span>
                </a>
            </li>
            <li class="menu-item <?php echo ($active_page === 'reminders.php') ? 'active' : ''; ?>">
                <a href="reminders.php">
                    <i data-lucide="bell"></i>
                    <span class="menu-text">Reminders</span>
                </a>
            </li>
            <li class="menu-item <?php echo ($active_page === 'add_client.php') ? 'active' : ''; ?>">
                <a href="add_client.php">
                    <i data-lucide="user-plus"></i>
                    <span class="menu-text">Add Client</span>
                </a>
            </li>
            <li class="menu-item <?php echo ($active_page === 'search_track.php') ? 'active' : ''; ?>">
                <a href="search_track.php">
                    <i data-lucide="search"></i>
                    <span class="menu-text">Search & CRM Track</span>
                </a>
            </li>
            <li class="menu-item <?php echo ($active_page === 'send_email.php') ? 'active' : ''; ?>">
                <a href="send_email.php">
                    <i data-lucide="mail"></i>
                    <span class="menu-text">Send Email</span>
                </a>
            </li>
            <li class="menu-item <?php echo ($active_page === 'quotation_builder.php') ? 'active' : ''; ?>">
                <a href="quotation_builder.php">
                    <i data-lucide="file-plus"></i>
                    <span class="menu-text">Quotation Builder</span>
                </a>
            </li>
            <li class="menu-item <?php echo ($active_page === 'quotation_list.php') ? 'active' : ''; ?>">
                <a href="quotation_list.php">
                    <i data-lucide="file-text"></i>
                    <span class="menu-text">Quotation List</span>
                </a>
            </li>
            <li class="menu-item <?php echo ($active_page === 'activity_log.php') ? 'active' : ''; ?>">
                <a href="activity_log.php">
                    <i data-lucide="activity"></i>
                    <span class="menu-text">Activity Logs</span>
                </a>
            </li>
            <li class="menu-item <?php echo ($active_page === 'settings.php') ? 'active' : ''; ?>">
                <a href="settings.php">
                    <i data-lucide="settings"></i>
                    <span class="menu-text">CRM Settings</span>
                </a>
            </li>
            <?php else: ?>
            <li class="menu-item <?php echo ($active_page === 'dashboard.php') ? 'active' : ''; ?>">
                <a href="dashboard.php">
                    <i data-lucide="layout-dashboard"></i>
                    <span class="menu-text">Dashboard</span>
                </a>
            </li>
            <li class="menu-item <?php echo ($active_page === 'pre_leads.php') ? 'active' : ''; ?>">
                <a href="pre_leads.php">
                    <i data-lucide="inbox"></i>
                    <span class="menu-text">My Pre-Leads (Raw Data)</span>
                </a>
            </li>
            <li class="menu-item <?php echo ($active_page === 'leads.php') ? 'active' : ''; ?>">
                <a href="leads.php">
                    <i data-lucide="target"></i>
                    <span class="menu-text">Lead Management</span>
                </a>
            </li>
            <li class="menu-item <?php echo ($active_page === 'reminders.php') ? 'active' : ''; ?>">
                <a href="reminders.php">
                    <i data-lucide="bell"></i>
                    <span class="menu-text">Reminders</span>
                </a>
            </li>
            <li class="menu-item <?php echo ($active_page === 'add_client.php') ? 'active' : ''; ?>">
                <a href="add_client.php">
                    <i data-lucide="user-plus"></i>
                    <span class="menu-text">Add Client</span>
                </a>
            </li>
            <li class="menu-item <?php echo ($active_page === 'search_track.php') ? 'active' : ''; ?>">
                <a href="search_track.php">
                    <i data-lucide="search"></i>
                    <span class="menu-text">Search & CRM Track</span>
                </a>
            </li>
            <li class="menu-item <?php echo ($active_page === 'send_email.php') ? 'active' : ''; ?>">
                <a href="send_email.php">
                    <i data-lucide="mail"></i>
                    <span class="menu-text">Send Email</span>
                </a>
            </li>
            <li class="menu-item <?php echo ($active_page === 'quotation_builder.php') ? 'active' : ''; ?>">
                <a href="quotation_builder.php">
                    <i data-lucide="file-plus"></i>
                    <span class="menu-text">Quotation Builder</span>
                </a>
            </li>
            <li class="menu-item <?php echo ($active_page === 'quotation_list.php') ? 'active' : ''; ?>">
                <a href="quotation_list.php">
                    <i data-lucide="file-text"></i>
                    <span class="menu-text">Quotation List</span>
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
        <header class="main-header">
            <div class="page-title">
                <h1 id="view-title"><?php echo htmlspecialchars($page_title ?? 'Dashboard'); ?></h1>
                <p id="view-subtitle"><?php echo htmlspecialchars($page_subtitle ?? ''); ?></p>
            </div>
            <div class="user-pill">
                <div class="user-avatar" id="header-user-avatar"><?php 
                    $current_username = $_SESSION['username'] ?? 'Unknown';
                    $stmtUser = $db->prepare("SELECT name FROM users WHERE username = ?");
                    $stmtUser->execute([$current_username]);
                    $uRow = $stmtUser->fetch();
                    $displayName = (!empty($uRow) && !empty($uRow['name'])) ? $uRow['name'] : $current_username;
                    
                    $names = explode(' ', $displayName);
                    $initials = '';
                    foreach ($names as $n) {
                        if (!empty($n)) $initials .= strtoupper(substr($n, 0, 1));
                    }
                    echo htmlspecialchars(substr($initials, 0, 2));
                ?></div>
                <span id="header-user-name"><?php echo htmlspecialchars($displayName); ?></span>
            </div>
        </header>
