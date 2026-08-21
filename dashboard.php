<?php
require_once 'config.php';

if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'Partner') { header('Location: partner/index.php'); exit; }
    elseif ($_SESSION['role'] === 'Agent') { header('Location: agent/index.php'); exit; }
    elseif ($_SESSION['role'] === 'Staff') { header('Location: staff/index.php'); exit; }
}

$page_title = 'Dashboard';
$page_subtitle = '';
require_once 'header.php';
?>
<style>
/* Dashboard only — remove page-title and main-header spacing */
.page-title { display: none !important; }
.main-header { padding: 0 !important; margin: 0 !important; border: none !important; min-height: 0 !important; }
header.main-header { padding-bottom: 0 !important; margin-bottom: 0 !important; }
</style>
<?php
?>

<style>
/* ====== BFS Circle Dashboard — Navy + Orange Premium Theme ====== */
:root {
    --navy: #0f172a;
    --navy-mid: #1e293b;
    --navy-light: #334155;
    --orange: #f97316;
    --orange-deep: #ea580c;
    --orange-light: #fff7ed;
    --green: #10b981;
    --green-light: #dcfce7;
    --red: #ef4444;
    --red-light: #fee2e2;
    --border: #e2e8f0;
    --bg: #f0f4f8;
    --text: #0f172a;
    --muted: #64748b;
}

.db-wrap { padding: 24px; background: var(--bg); min-height: 100vh; }

/* ===== HERO BANNER ===== */
.db-hero { background: linear-gradient(135deg, #0f172a 0%, #1e293b 55%, #0f172a 100%); border-radius: 20px; padding: 28px 32px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; position: relative; overflow: hidden; }
.db-hero::before { content: ''; position: absolute; top: -60px; right: -60px; width: 350px; height: 350px; background: radial-gradient(circle, rgba(249,115,22,0.18) 0%, transparent 65%); border-radius: 50%; pointer-events: none; }
.db-hero::after  { content: ''; position: absolute; bottom: -80px; left: 150px; width: 250px; height: 250px; background: radial-gradient(circle, rgba(249,115,22,0.08) 0%, transparent 70%); border-radius: 50%; pointer-events: none; }
.db-hero-left h2 { font-size: 26px; font-weight: 900; color: #fff; margin: 0 0 6px; letter-spacing: -0.5px; }
.db-hero-left p { font-size: 13px; color: #94a3b8; margin: 0; }
.db-hero-right { display: flex; gap: 32px; position: relative; z-index: 1; }
.hero-stat { text-align: center; }
.hero-stat-val { font-size: 30px; font-weight: 900; color: var(--orange); line-height: 1; }
.hero-stat-lbl { font-size: 11px; color: #94a3b8; font-weight: 700; margin-top: 5px; text-transform: uppercase; letter-spacing: 0.5px; }
.hero-divider { width: 1px; background: rgba(255,255,255,0.1); align-self: stretch; }

/* ===== TOP KPI STRIP ===== */
.kpi-strip { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px; }
@media (max-width: 1200px) { .kpi-strip { grid-template-columns: repeat(2, 1fr); } }

/* ===== PURE METRIC TILE — No Icon, Pure Data ===== */
.kpi { background: var(--navy); border-radius: 16px; padding: 22px 24px 18px; cursor: pointer; transition: all 0.3s cubic-bezier(.4,0,.2,1); box-shadow: 0 4px 20px rgba(15,23,42,0.25); position: relative; overflow: hidden; display: flex; flex-direction: column; justify-content: space-between; min-height: 110px; }
.kpi::before { content: ''; position: absolute; bottom: 0; right: 0; width: 80px; height: 80px; background: radial-gradient(circle, rgba(249,115,22,0.14) 0%, transparent 70%); border-radius: 50%; }
.kpi::after  { content: ''; position: absolute; top: 0; left: 0; width: 3px; height: 100%; background: var(--orange); border-radius: 3px 0 0 3px; }
.kpi:hover { transform: translateY(-5px); box-shadow: 0 18px 45px rgba(15,23,42,0.4); }
.kpi-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px; }
.kpi-val { font-size: 32px; font-weight: 900; color: #fff; line-height: 1; letter-spacing: -1px; }
.kpi-arrow { font-size: 18px; color: var(--orange); opacity: 0.7; }
.kpi-lbl { font-size: 10px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 5px; }
.kpi-sub { font-size: 11px; color: rgba(249,115,22,0.9); font-weight: 700; }

/* Light White variant */
.kpi.light { background: #fff; box-shadow: 0 2px 12px rgba(0,0,0,0.04); border: 1px solid var(--border); }
.kpi.light::after { background: var(--orange); }
.kpi.light .kpi-val { color: var(--navy); }
.kpi.light .kpi-lbl { color: var(--muted); }
.kpi.light .kpi-sub { color: var(--orange); }
.kpi.light:hover { box-shadow: 0 12px 32px rgba(249,115,22,0.15); border-color: rgba(249,115,22,0.4); }

/* Orange Accent variant */
.kpi.accent { background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); box-shadow: 0 6px 24px rgba(249,115,22,0.4); }
.kpi.accent::before { background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%); }
.kpi.accent::after { background: rgba(255,255,255,0.3); }
.kpi.accent .kpi-val { color: #fff; }
.kpi.accent .kpi-lbl { color: rgba(255,255,255,0.85); }
.kpi.accent .kpi-sub { color: rgba(255,255,255,0.95); }
.kpi.accent .kpi-arrow { color: rgba(255,255,255,0.7); }
.kpi.accent:hover { box-shadow: 0 18px 45px rgba(249,115,22,0.55); }

/* ===== SECTION ROWS ===== */
.db-row { display: grid; gap: 20px; margin-bottom: 20px; }
.db-row-2  { grid-template-columns: 1fr 1fr; }
.db-row-3  { grid-template-columns: 1fr 1fr 1fr; }
.db-row-23 { grid-template-columns: 2fr 1fr; }
.db-row-32 { grid-template-columns: 1fr 2fr; }
@media (max-width: 1100px) { .db-row-2, .db-row-3, .db-row-23, .db-row-32 { grid-template-columns: 1fr; } }

/* ===== CARDS ===== */
.dc { background: #fff; border-radius: 16px; padding: 24px; border: 1px solid var(--border); box-shadow: 0 2px 12px rgba(0,0,0,0.04); }
.dc.dc-dark { background: var(--navy); border-color: var(--navy-mid); }
.dc-hdr { display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; padding-bottom: 15px; border-bottom: 1px solid var(--border); }
.dc.dc-dark .dc-hdr { border-bottom-color: rgba(255,255,255,0.07); }
.dc-title { font-size: 14px; font-weight: 800; color: var(--text); display: flex; align-items: center; gap: 8px; }
.dc.dc-dark .dc-title { color: #f1f5f9; }
.dc-icon-pill { width: 32px; height: 32px; border-radius: 9px; background: rgba(249,115,22,0.1); border: 1px solid rgba(249,115,22,0.2); display: flex; align-items: center; justify-content: center; color: var(--orange); }
.dc-icon-pill svg { width: 15px; height: 15px; }
.dc-link { font-size: 12px; font-weight: 700; color: var(--orange); text-decoration: none; display: flex; align-items: center; gap: 4px; background: rgba(249,115,22,0.08); padding: 6px 12px; border-radius: 20px; transition: 0.2s; white-space: nowrap; }
.dc-link:hover { background: rgba(249,115,22,0.16); }
.chart-box { position: relative; }

/* ===== MINI STATS ===== */
.mini-stats { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px; }
.mini-stat { background: var(--bg); border-radius: 12px; padding: 14px; text-align: center; border: 1px solid var(--border); }
.dc.dc-dark .mini-stat { background: rgba(255,255,255,0.05); border-color: rgba(255,255,255,0.07); }
.mini-stat-val { font-size: 22px; font-weight: 900; color: var(--navy); }
.dc.dc-dark .mini-stat-val { color: #fff; }
.mini-stat-lbl { font-size: 11px; color: var(--muted); font-weight: 600; margin-top: 4px; }
.dc.dc-dark .mini-stat-lbl { color: #94a3b8; }

/* ===== PROGRESS BARS ===== */
.prog-bar-wrap { margin-bottom: 10px; }
.prog-label { display: flex; justify-content: space-between; font-size: 12px; font-weight: 700; color: var(--muted); margin-bottom: 5px; }
.prog-bar { height: 6px; background: #e2e8f0; border-radius: 20px; overflow: hidden; }
.prog-fill { height: 100%; border-radius: 20px; transition: width 1.2s cubic-bezier(.4,0,.2,1); background: linear-gradient(90deg, var(--orange), var(--orange-deep)); }

/* ===== LIST ITEMS ===== */
.list-item { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #f1f5f9; }
.list-item:last-child { border-bottom: none; }
.list-item-left { display: flex; align-items: center; gap: 10px; font-size: 13px; font-weight: 600; color: var(--text); }
.list-badge { width: 28px; height: 28px; border-radius: 8px; background: rgba(249,115,22,0.1); display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; color: var(--orange); border: 1px solid rgba(249,115,22,0.2); }
.list-item-right { font-size: 12px; font-weight: 700; color: var(--green); background: var(--green-light); padding: 3px 8px; border-radius: 20px; }

/* ===== ACTIVITY FEED ===== */
.act-item { display: flex; gap: 12px; padding: 10px 0; border-bottom: 1px solid #f1f5f9; }
.act-item:last-child { border-bottom: none; }
.act-dot { width: 8px; height: 8px; min-width: 8px; border-radius: 50%; background: var(--orange); margin-top: 6px; }
.act-text { font-size: 13px; color: var(--text); line-height: 1.4; }
.act-time { font-size: 11px; color: var(--muted); margin-top: 3px; }

/* ===== INFO BANNERS ===== */
.info-banner { background: linear-gradient(135deg, var(--navy), var(--navy-mid)); border-radius: 12px; padding: 18px 20px; color: #fff; }
.info-banner-title { font-size: 11px; color: #94a3b8; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px; }
.info-banner-val { font-size: 22px; font-weight: 900; color: var(--orange); }
.info-banner-sub { font-size: 11px; color: #64748b; margin-top: 6px; font-weight: 600; }

/* ===== SECTION DIVIDER LABEL ===== */
.section-label { font-size: 11px; font-weight: 800; color: var(--muted); text-transform: uppercase; letter-spacing: 1px; margin: 4px 0 14px; display: flex; align-items: center; gap: 8px; }
.section-label::after { content: ''; flex: 1; height: 1px; background: var(--border); }

/* ===== SKELETON ===== */
.sk { background: #e2e8f0; border-radius: 6px; overflow: hidden; position: relative; }
.sk::after { content:''; position:absolute; top:0; left:0; right:0; bottom:0; transform:translateX(-100%); background:linear-gradient(90deg,transparent,rgba(255,255,255,.55),transparent); animation:shimmer 1.5s infinite; }
@keyframes shimmer { 100% { transform: translateX(100%); } }
</style>

<!-- Load Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<div class="db-wrap">

<!-- ============ HERO BANNER ============ -->
<div class="db-hero">
    <div class="db-hero-left" style="position:relative;z-index:1;">
        <h2>BFS Circle — Control Tower</h2>
        <p>Real-time analytics across all business sections. Last refreshed: <span id="lastRefresh">just now</span></p>
    </div>
    <div class="db-hero-right">
        <div class="hero-stat" id="hero-apps"><div class="hero-stat-val">—</div><div class="hero-stat-lbl">Active Loans</div></div>
        <div class="hero-divider"></div>
        <div class="hero-stat" id="hero-vault"><div class="hero-stat-val">—</div><div class="hero-stat-lbl">Vault Clients</div></div>
        <div class="hero-divider"></div>
        <div class="hero-stat" id="hero-staff"><div class="hero-stat-val">—</div><div class="hero-stat-lbl">Staff Online</div></div>
    </div>
</div>


<!-- ============ KPI STRIP ============ -->
<div class="kpi-strip" id="kpiStrip">
    <!-- KPIs rendered by JS -->
    <?php for($i=0;$i<8;$i++): ?>
    <div class="kpi">
        <div><div class="sk" style="width:70px;height:10px;margin-bottom:10px;border-radius:4px;background:rgba(255,255,255,0.1);"></div><div class="sk" style="width:50px;height:30px;margin-bottom:8px;border-radius:4px;background:rgba(255,255,255,0.1);"></div><div class="sk" style="width:100px;height:10px;border-radius:4px;background:rgba(255,255,255,0.1);"></div></div>
    </div>
    <?php endfor; ?>
</div>

<!-- ============ ROW 1: Pre-Leads + Leads Pipeline ============ -->
<div class="db-row db-row-23">

    <!-- Pre-Leads Card -->
    <div class="dc" onclick="window.location.href='pre_leads.php'" style="cursor:pointer;">
        <div class="dc-hdr">
            <div class="dc-title"><span style="background:var(--orange);color:#fff;border-radius:6px;padding:2px 8px;font-size:11px;font-weight:800;">LEADS</span> Pre-Leads (Raw Data)</div>
            <a href="pre_leads.php" class="dc-link">View All <i data-lucide="arrow-right" style="width:12px;"></i></a>
        </div>
        <div class="mini-stats">
            <div class="mini-stat"><div class="mini-stat-val" id="pl-total" >—</div><div class="mini-stat-lbl">Total Leads</div></div>
            <div class="mini-stat"><div class="mini-stat-val" id="pl-new" style="color:var(--orange);">—</div><div class="mini-stat-lbl">New / Uncontacted</div></div>
            <div class="mini-stat"><div class="mini-stat-val" id="pl-followup" style="color:var(--navy);">—</div><div class="mini-stat-lbl">Follow-ups</div></div>
            <div class="mini-stat"><div class="mini-stat-val" id="pl-junk" style="color:#ef4444;">—</div><div class="mini-stat-lbl">Junk / Archived</div></div>
        </div>
        <div style="height:180px;" class="chart-box"><canvas id="chartPreLeadsPie"></canvas></div>
    </div>

    <!-- Leads Pipeline -->
    <div class="dc">
        <div class="dc-hdr">
            <div class="dc-title"><span style="background:var(--navy);color:#fff;border-radius:6px;padding:2px 8px;font-size:11px;font-weight:800;">PIPELINE</span> Lead Pipeline</div>
            <a href="leads.php" class="dc-link">Manage <i data-lucide="arrow-right" style="width:12px;"></i></a>
        </div>
        <div class="mini-stats">
            <div class="mini-stat"><div class="mini-stat-val" id="ld-total" >—</div><div class="mini-stat-lbl">Total Leads</div></div>
            <div class="mini-stat"><div class="mini-stat-val" id="ld-hot" style="color:#ef4444;">—</div><div class="mini-stat-lbl">🔥 Hot Leads</div></div>
        </div>
        <div id="leadStagesBar" style="margin-top:8px;">
            <!-- Progress bars dynamically inserted -->
        </div>
    </div>
</div>

<!-- ============ ROW 2: Loan Applications + Monthly Growth ============ -->
<div class="db-row db-row-32">
    <!-- Monthly Growth Chart -->
    <div class="dc">
        <div class="dc-hdr">
            <div class="dc-title"><span style="background:var(--green);color:#fff;border-radius:6px;padding:2px 8px;font-size:11px;font-weight:800;">GROWTH</span> Loan Applications — 6-Month Growth</div>
            <a href="loan_application.php" class="dc-link">View Files <i data-lucide="arrow-right" style="width:12px;"></i></a>
        </div>
        <div style="height:220px;" class="chart-box"><canvas id="chartMonthlyApps"></canvas></div>
    </div>

    <!-- Loan Type Doughnut -->
    <div class="dc">
        <div class="dc-hdr">
            <div class="dc-title"><span style="background:var(--orange);color:#fff;border-radius:6px;padding:2px 8px;font-size:11px;font-weight:800;">MIX</span> Loan Type Mix</div>
        </div>
        <div class="mini-stats">
            <div class="mini-stat"><div class="mini-stat-val" id="app-active" style="color:var(--orange);">—</div><div class="mini-stat-lbl">Active Files</div></div>
            <div class="mini-stat"><div class="mini-stat-val" id="app-completed" style="color:var(--orange);">—</div><div class="mini-stat-lbl">Completed</div></div>
        </div>
        <div style="height:160px;" class="chart-box"><canvas id="chartLoanTypes"></canvas></div>
    </div>
</div>

<!-- ============ ROW 3: Bankers + Field Visits ============ -->
<div class="db-row db-row-2">
    <!-- Bankers -->
    <div class="dc">
        <div class="dc-hdr">
            <div class="dc-title"><i data-lucide="building" ></i> Banker Network</div>
            <a href="bankers.php" class="dc-link">View Bankers <i data-lucide="arrow-right" style="width:12px;"></i></a>
        </div>
        <div class="mini-stats" style="grid-template-columns:1fr 1fr 1fr 1fr;">
            <div class="mini-stat"><div class="mini-stat-val" id="bk-total" >—</div><div class="mini-stat-lbl">Total Bankers</div></div>
            <div class="mini-stat"><div class="mini-stat-val" id="bk-active" style="color:var(--orange);">—</div><div class="mini-stat-lbl">Active</div></div>
            <div class="mini-stat"><div class="mini-stat-val" id="bk-assign" style="color:var(--orange);">—</div><div class="mini-stat-lbl">Assignments</div></div>
            <div class="mini-stat"><div class="mini-stat-val" id="bk-approved" style="color:var(--orange);">—</div><div class="mini-stat-lbl">Approved</div></div>
        </div>
        <div style="height:160px;" class="chart-box"><canvas id="chartBankWise"></canvas></div>
    </div>

    <!-- Field Visits -->
    <div class="dc">
        <div class="dc-hdr">
            <div class="dc-title"><span style="background:#14b8a6;color:#fff;border-radius:6px;padding:2px 8px;font-size:11px;font-weight:800;">VISITS</span> Field Visits Tracker</div>
            <a href="field_visits.php" class="dc-link">View Visits <i data-lucide="arrow-right" style="width:12px;"></i></a>
        </div>
        <div class="mini-stats" style="grid-template-columns:1fr 1fr 1fr;">
            <div class="mini-stat"><div class="mini-stat-val" id="fv-total" >—</div><div class="mini-stat-lbl">All Time</div></div>
            <div class="mini-stat"><div class="mini-stat-val" id="fv-today" style="color:var(--orange);">—</div><div class="mini-stat-lbl">Today</div></div>
            <div class="mini-stat"><div class="mini-stat-val" id="fv-month" style="color:var(--orange);">—</div><div class="mini-stat-lbl">This Month</div></div>
        </div>
        <div style="height:160px;" class="chart-box"><canvas id="chartFieldVisits"></canvas></div>
    </div>
</div>

<!-- ============ ROW 4: Client Vault + Referrals + Payouts ============ -->
<div class="db-row db-row-3">
    <!-- Client Vault -->
    <div class="dc" onclick="window.location.href='client_vault/index.php'" style="cursor:pointer;">
        <div class="dc-hdr">
            <div class="dc-title"><span style="background:var(--green);color:#fff;border-radius:6px;padding:2px 8px;font-size:11px;font-weight:800;">VAULT</span> Client Vault</div>
            <a href="client_vault/index.php" class="dc-link">Open Vault <i data-lucide="arrow-right" style="width:12px;"></i></a>
        </div>
        <div class="mini-stats">
            <div class="mini-stat"><div class="mini-stat-val" id="cv-total" style="color:var(--orange);">—</div><div class="mini-stat-lbl">Total Clients</div></div>
            <div class="mini-stat"><div class="mini-stat-val" id="cv-prime" style="color:var(--orange);">—</div><div class="mini-stat-lbl">🔥 Cross-Sell Ready</div></div>
        </div>
        <div class="info-banner" style="margin-top:12px; text-align:center;">
            <div style="font-size:12px; color:#94a3b8; margin-bottom:4px;">Verified KYC clients with fully stored documents</div>
            <div style="font-size:11px; color:#f97316; font-weight:700;">Click to cross-sell new products ➜</div>
        </div>
    </div>

    <!-- Referrals -->
    <div class="dc" onclick="window.location.href='referrals.php'" style="cursor:pointer;">
        <div class="dc-hdr">
            <div class="dc-title"><span style="background:#8b5cf6;color:#fff;border-radius:6px;padding:2px 8px;font-size:11px;font-weight:800;">REFS</span> Referral Partners</div>
            <a href="referrals.php" class="dc-link">View All <i data-lucide="arrow-right" style="width:12px;"></i></a>
        </div>
        <div class="mini-stat" style="text-align:center; margin-bottom:14px;"><div class="mini-stat-val" id="rf-total" style="color:var(--orange);">—</div><div class="mini-stat-lbl">Total Referral Partners</div></div>
        <div id="topReferralsList"><!-- inserted by JS --></div>
    </div>

    <!-- Payouts -->
    <div class="dc" onclick="window.location.href='payouts.php'" style="cursor:pointer;">
        <div class="dc-hdr">
            <div class="dc-title"><i data-lucide="indian-rupee" style="color:var(--navy);"></i> Payout Summary</div>
            <a href="payouts.php" class="dc-link">View Payouts <i data-lucide="arrow-right" style="width:12px;"></i></a>
        </div>
        <div class="mini-stats">
            <div class="mini-stat"><div class="mini-stat-val" id="py-total" style="color:var(--amber); font-size:18px;">—</div><div class="mini-stat-lbl">Total Paid Out</div></div>
            <div class="mini-stat"><div class="mini-stat-val" id="py-month" style="color:var(--green); font-size:18px;">—</div><div class="mini-stat-lbl">This Month</div></div>
        </div>
        <div style="background:var(--amber-light); border-radius:12px; padding:16px; margin-top:8px; border:1px solid var(--amber);">
            <div style="font-size:12px; color:#92400e; font-weight:700; display:flex; align-items:center; gap:6px;"><i data-lucide="alert-circle" style="width:14px;"></i> Total disbursed shown below</div>
            <div style="font-size:20px; font-weight:800; color:#78350f; margin-top:6px;" id="py-disbursed">—</div>
        </div>
    </div>
</div>

<!-- ============ ROW 5: Staff + Emails + Reminders ============ -->
<div class="db-row db-row-3">
    <!-- Staff Performance -->
    <div class="dc">
        <div class="dc-hdr">
            <div class="dc-title"><span style="background:var(--navy);color:#fff;border-radius:6px;padding:2px 8px;font-size:11px;font-weight:800;">STAFF</span> Staff Performance</div>
            <a href="staff_productivity.php" class="dc-link">Full Report <i data-lucide="arrow-right" style="width:12px;"></i></a>
        </div>
        <div class="mini-stats">
            <div class="mini-stat"><div class="mini-stat-val" id="st-total" >—</div><div class="mini-stat-lbl">Total Staff</div></div>
            <div class="mini-stat"><div class="mini-stat-val" id="st-online" style="color:var(--orange);">—</div><div class="mini-stat-lbl">Online Now</div></div>
        </div>
        <div style="height:160px;" class="chart-box"><canvas id="chartStaffPerf"></canvas></div>
    </div>

    <!-- Emails / Communications -->
    <div class="dc" onclick="window.location.href='send_email.php'" style="cursor:pointer;">
        <div class="dc-hdr">
            <div class="dc-title"><span style="background:#3b82f6;color:#fff;border-radius:6px;padding:2px 8px;font-size:11px;font-weight:800;">EMAIL</span> Email Activity</div>
            <a href="send_email.php" class="dc-link">Send Email <i data-lucide="arrow-right" style="width:12px;"></i></a>
        </div>
        <div class="mini-stats">
            <div class="mini-stat"><div class="mini-stat-val" id="em-today" style="color:var(--orange);">—</div><div class="mini-stat-lbl">Sent Today</div></div>
            <div class="mini-stat"><div class="mini-stat-val" id="em-month" >—</div><div class="mini-stat-lbl">This Month</div></div>
        </div>
        <div class="info-banner" style="margin-top:12px; text-align:center;">
            <i data-lucide="send" style="width:32px; height:32px; color:#93c5fd; margin-bottom:8px;"></i>
            <div style="font-size:13px; font-weight:700;">Click to compose & send<br>professional emails</div>
        </div>
    </div>

    <!-- Reminders -->
    <div class="dc" onclick="window.location.href='reminders.php'" style="cursor:pointer;">
        <div class="dc-hdr">
            <div class="dc-title"><i data-lucide="bell" style="color:#ef4444;"></i> Reminders & Follow-ups</div>
            <a href="reminders.php" class="dc-link">View All <i data-lucide="arrow-right" style="width:12px;"></i></a>
        </div>
        <div class="mini-stats">
            <div class="mini-stat"><div class="mini-stat-val" id="rm-today" style="color:var(--orange);">—</div><div class="mini-stat-lbl">Due Today</div></div>
            <div class="mini-stat"><div class="mini-stat-val" id="rm-overdue" style="color:#ef4444;">—</div><div class="mini-stat-lbl">⚠️ Overdue</div></div>
        </div>
        <div id="reminderAlert" style="display:none; background:var(--red-light); border:1px solid #fca5a5; border-radius:10px; padding:14px; margin-top:8px;">
            <div style="font-size:13px; font-weight:700; color:#dc2626;">You have overdue reminders!</div>
            <div style="font-size:12px; color:#991b1b; margin-top:4px;">Please action them immediately.</div>
        </div>
    </div>
</div>

<!-- ============ ROW 6: Recent Activity Feed ============ -->
<div class="db-row" style="grid-template-columns:1fr;">
    <div class="dc">
        <div class="dc-hdr">
            <div class="dc-title"><span style="background:var(--orange);color:#fff;border-radius:6px;padding:2px 8px;font-size:11px;font-weight:800;">LIVE</span> Live Activity Feed</div>
            <select id="feedUserFilter" onchange="loadFeed()" style="padding:6px 10px; font-size:12px; border:1px solid var(--border); border-radius:6px; outline:none;">
                <option value="">All Users</option>
                <?php
                $users = $db->query("SELECT username FROM users ORDER BY username ASC")->fetchAll(PDO::FETCH_COLUMN);
                foreach($users as $u) echo "<option value='" . htmlspecialchars($u) . "'>" . htmlspecialchars($u) . "</option>";
                ?>
            </select>
        </div>
        <div id="activityFeed" style="max-height: 280px; overflow-y: auto; padding-right: 4px;">
            <div style="color:var(--muted); text-align:center; padding:20px;">Loading...</div>
        </div>
    </div>
</div>

</div><!-- /db-wrap -->

<script>
const COLORS = ['#f97316','#0f172a','#ea580c','#334155','#fed7aa','#1e293b','#fb923c','#475569'];
let charts = {};

function fmt(n) {
    if (n >= 10000000) return '₹' + (n/10000000).toFixed(2) + ' Cr';
    if (n >= 100000)   return '₹' + (n/100000).toFixed(2) + ' L';
    if (n >= 1000)     return '₹' + (n/1000).toFixed(1) + ' K';
    return '₹' + Math.round(n).toLocaleString('en-IN');
}

function makeChart(id, type, labels, data, opts = {}) {
    if (charts[id]) charts[id].destroy();
    const ctx = document.getElementById(id).getContext('2d');
    charts[id] = new Chart(ctx, {
        type,
        data: {
            labels,
            datasets: [{
                data,
                backgroundColor: opts.singleColor ? opts.singleColor : COLORS,
                borderColor: opts.borderColor || 'transparent',
                borderWidth: opts.borderWidth || 0,
                borderRadius: opts.radius ?? 6,
                fill: opts.fill ?? false,
                tension: 0.4,
                pointBackgroundColor: '#f97316',
                pointRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: opts.legend ?? (type === 'doughnut' || type === 'pie'), position: 'bottom', labels: { font: { size: 11 }, boxWidth: 10, padding: 8 } },
                tooltip: { callbacks: { label: function(c) { return opts.currencyTooltip ? ' ' + fmt(c.raw) : ' ' + c.formattedValue; } } }
            },
            scales: type === 'bar' || type === 'line' ? {
                y: { grid: { color: '#f1f5f9' }, ticks: { color: '#64748b', font: { size: 11 } }, beginAtZero: true },
                x: { grid: { display: false }, ticks: { color: '#64748b', font: { size: 11 } } }
            } : {},
            cutout: type === 'doughnut' ? '65%' : undefined
        }
    });
}

async function loadDashboard() {
    const res  = await fetch('?api=dashboard_analytics');
    const d    = await res.json();


        // Update Hero Banner
        document.getElementById('hero-apps').innerHTML = '<div class="hero-stat-val">' + d.applications.active + '</div><div class="hero-stat-lbl">Active Loans</div>';
        document.getElementById('hero-vault').innerHTML = '<div class="hero-stat-val">' + d.client_vault.total + '</div><div class="hero-stat-lbl">Vault Clients</div>';
        document.getElementById('hero-staff').innerHTML = '<div class="hero-stat-val">' + d.staff.online + '</div><div class="hero-stat-lbl">Staff Online</div>';
        
        // Update last refresh time
        const now = new Date();
        document.getElementById('lastRefresh').innerText = now.toLocaleTimeString('en-IN', {hour:'2-digit', minute:'2-digit'});

    // ===== KPI STRIP =====
    // Navy = dark card, light = white card, accent = orange card
    const kpiStyles = ['', 'light', '', 'light', 'accent', 'light', '', 'light'];
    const kpis = [
        { icon: 'inbox',        val: d.pre_leads.total,        lbl: 'Pre-Leads',         sub: d.pre_leads.new + ' uncontacted',       link: 'pre_leads.php' },
        { icon: 'git-merge',    val: d.leads.total,            lbl: 'Active Leads',       sub: d.leads.hot + ' 🔥 hot leads',          link: 'leads.php' },
        { icon: 'file-text',    val: d.applications.active,    lbl: 'Loan Files Active',  sub: d.applications.total + ' total apps',   link: 'loan_application.php' },
        { icon: 'check-circle', val: d.applications.completed, lbl: 'Completed Loans',    sub: fmt(d.applications.disbursed),          link: 'loan_application.php' },
        { icon: 'map-pin',      val: d.field_visits.today,     lbl: "Today's Visits",     sub: d.field_visits.total + ' all time',     link: 'field_visits.php' },
        { icon: 'building',     val: d.bankers.active,         lbl: 'Active Bankers',     sub: d.bankers.approved + ' approved',       link: 'bankers.php' },
        { icon: 'bell',         val: d.reminders.today,        lbl: "Today's Reminders",  sub: d.reminders.overdue + ' overdue ⚠️',   link: 'reminders.php' },
        { icon: 'shield',       val: d.client_vault.total,     lbl: 'Client Vault',       sub: d.client_vault.prime + ' cross-sell',   link: 'client_vault/index.php' },
    ];

    document.getElementById('kpiStrip').innerHTML = kpis.map((k, i) => `
        <div class="kpi ${kpiStyles[i]}" onclick="window.location.href='${k.link}'" style="cursor:pointer;">
            <div>
                <div class="kpi-lbl">${k.lbl}</div>
                <div class="kpi-top">
                    <div class="kpi-val">${k.val}</div>
                    <div class="kpi-arrow">↗</div>
                </div>
                <div class="kpi-sub">${k.sub}</div>
            </div>
        </div>
    `).join('');

    // ===== PRE-LEADS PIE =====
    document.getElementById('pl-total').innerText    = d.pre_leads.total;
    document.getElementById('pl-new').innerText      = d.pre_leads.new;
    document.getElementById('pl-followup').innerText = d.pre_leads.followup;
    document.getElementById('pl-junk').innerText     = d.pre_leads.junk;
    makeChart('chartPreLeadsPie', 'doughnut',
        ['New/Uncontacted', 'Follow-up', 'Junk'],
        [d.pre_leads.new, d.pre_leads.followup, d.pre_leads.junk],
        { legend: true }
    );

    // ===== LEAD STAGES PROGRESS BARS =====
    document.getElementById('ld-total').innerText = d.leads.total;
    document.getElementById('ld-hot').innerText   = d.leads.hot;
    const total = d.leads.total || 1;
    const stageColors = { 'New Lead': '#3b82f6', 'Contacted': '#f59e0b', 'Interested': '#10b981', 'Converted': '#f97316', 'Disbursed': '#8b5cf6', 'Login': '#14b8a6' };
    document.getElementById('leadStagesBar').innerHTML = (d.leads.stages || []).map(s => `
        <div class="prog-bar-wrap">
            <div class="prog-label"><span>${s.stage}</span><span>${s.cnt}</span></div>
            <div class="prog-bar"><div class="prog-fill" style="width:${(s.cnt/total*100).toFixed(0)}%; background:${stageColors[s.stage]||'#64748b'};"></div></div>
        </div>
    `).join('');

    // ===== MONTHLY APPS CHART =====
    document.getElementById('app-active').innerText    = d.applications.active;
    document.getElementById('app-completed').innerText = d.applications.completed;
    makeChart('chartMonthlyApps', 'line',
        d.applications.monthly_growth.map(x => x.month),
        d.applications.monthly_growth.map(x => x.count),
        { singleColor: 'rgba(249,115,22,0.15)', borderColor: '#f97316', borderWidth: 3, fill: true }
    );

    // ===== LOAN TYPE DOUGHNUT =====
    makeChart('chartLoanTypes', 'doughnut',
        d.applications.loan_types.map(x => x.loan_type),
        d.applications.loan_types.map(x => x.cnt),
        { legend: true }
    );

    // ===== BANK WISE BAR =====
    document.getElementById('bk-total').innerText    = d.bankers.total;
    document.getElementById('bk-active').innerText   = d.bankers.active;
    document.getElementById('bk-assign').innerText   = d.bankers.assignments;
    document.getElementById('bk-approved').innerText = d.bankers.approved;
    makeChart('chartBankWise', 'bar',
        d.bankers.bank_wise.map(x => x.bank_name),
        d.bankers.bank_wise.map(x => x.cnt),
        { singleColor: '#0f172a' }
    );

    // ===== FIELD VISITS WEEKLY BAR =====
    document.getElementById('fv-total').innerText = d.field_visits.total;
    document.getElementById('fv-today').innerText = d.field_visits.today;
    document.getElementById('fv-month').innerText = d.field_visits.this_month;
    makeChart('chartFieldVisits', 'bar',
        d.field_visits.weekly.map(x => x.day),
        d.field_visits.weekly.map(x => x.count),
        { singleColor: '#14b8a6' }
    );

    // ===== CLIENT VAULT =====
    document.getElementById('cv-total').innerText = d.client_vault.total;
    document.getElementById('cv-prime').innerText = d.client_vault.prime;

    // ===== REFERRALS =====
    document.getElementById('rf-total').innerText = d.referrals.total;
    document.getElementById('topReferralsList').innerHTML = (d.referrals.top || []).map((r,i) => `
        <div class="list-item">
            <div class="list-item-left">
                <div class="list-badge" style="background:${COLORS[i % COLORS.length]}22; color:${COLORS[i % COLORS.length]};">${i+1}</div>
                ${r.name}
            </div>
            <i data-lucide="user-check" style="width:14px; color:#10b981;"></i>
        </div>
    `).join('') || '<div style="color:var(--muted); font-size:13px; padding:12px 0;">No active referrals</div>';
    lucide.createIcons();

    // ===== PAYOUTS =====
    document.getElementById('py-total').innerText    = fmt(d.payouts.total);
    document.getElementById('py-month').innerText   = fmt(d.payouts.this_month);
    document.getElementById('py-disbursed').innerText = fmt(d.applications.disbursed) + ' Total Disbursed';

    // ===== STAFF PERFORMANCE =====
    document.getElementById('st-total').innerText  = d.staff.total;
    document.getElementById('st-online').innerText = d.staff.online;
    makeChart('chartStaffPerf', 'bar',
        d.staff.performance.map(x => x.name),
        d.staff.performance.map(x => x.cnt),
        { singleColor: '#3b82f6', radius: 6 }
    );

    // ===== EMAILS =====
    document.getElementById('em-today').innerText = d.emails.today;
    document.getElementById('em-month').innerText = d.emails.this_month;

    // ===== REMINDERS =====
    document.getElementById('rm-today').innerText   = d.reminders.today;
    document.getElementById('rm-overdue').innerText = d.reminders.overdue;
    if (d.reminders.overdue > 0) {
        document.getElementById('reminderAlert').style.display = 'block';
    }
}

async function loadFeed() {
    const user = document.getElementById('feedUserFilter').value;
    const res = await fetch(`?api=recent_activities&user=${user}&days=7`);
    const data = await res.json();
    const el = document.getElementById('activityFeed');
    if (!data.length) { el.innerHTML = '<div style="color:var(--muted); text-align:center; padding:20px;">No recent activity.</div>'; return; }
    el.innerHTML = data.map(a => `
        <div class="act-item">
            <div class="act-dot"></div>
            <div><div class="act-text">${a.description}</div><div class="act-time">${a.time_formatted}</div></div>
        </div>
    `).join('');
}

document.addEventListener('DOMContentLoaded', () => {
    lucide.createIcons();
    loadDashboard();
    loadFeed();
    setInterval(loadFeed, 15000);
    setInterval(loadDashboard, 60000); // refresh stats every 60s
});
</script>

<?php require_once 'footer.php'; ?>
