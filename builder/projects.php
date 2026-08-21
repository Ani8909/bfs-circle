<?php
require_once 'includes/header.php';

// Handle adding a new project
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_project') {
    $pname = $_POST['project_name'] ?? '';
    $loc = $_POST['location'] ?? '';
    $rera = $_POST['rera_number'] ?? '';
    
    if ($pname) {
        $stmt_add = $db->prepare("INSERT INTO builder_projects (builder_id, project_name, location, rera_number) VALUES (?, ?, ?, ?)");
        $stmt_add->execute([$builder_id, $pname, $loc, $rera]);
        echo "<script>alert('Project added successfully! Admin will review for APF status.'); window.location.href='projects.php';</script>";
        exit;
    }
}

// Fetch all projects for this builder
$stmt_proj = $db->prepare("SELECT * FROM builder_projects WHERE builder_id = ? ORDER BY created_at DESC");
$stmt_proj->execute([$builder_id]);
$projects = $stmt_proj->fetchAll();
?>

<div style="margin-bottom: 24px; display:flex; justify-content:space-between; align-items:center;">
    <div>
        <h2 style="font-family: 'Outfit'; font-size: 22px; color: var(--text-primary); display: flex; align-items: center; gap: 8px;">
            My Projects
        </h2>
        <p style="color: var(--text-muted); font-size: 13px; margin-top: 4px;">Track APF Status with Banks</p>
    </div>
    <button onclick="document.getElementById('addProjectModal').style.display='block'" style="background: var(--primary); color: white; border: none; padding: 10px 14px; border-radius: 12px; font-weight: 600; font-size: 13px; cursor: pointer; display: flex; align-items: center; gap: 6px;">
        <i data-lucide="plus" style="width:16px;"></i> Add Project
    </button>
</div>

<!-- Project List -->
<?php if (empty($projects)): ?>
    <div style="text-align: center; padding: 40px 20px; background: var(--card-bg); border-radius: 20px; border: 1px dashed var(--border);">
        <div style="width: 50px; height: 50px; background: rgba(30, 41, 59, 0.05); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
            <i data-lucide="building-2" style="width: 24px; color: var(--text-muted);"></i>
        </div>
        <h3 style="font-family: 'Outfit'; font-size: 16px; margin-bottom: 8px;">No projects added</h3>
        <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 16px;">Add your properties to track APF approvals and link home loan applications.</p>
        <button onclick="document.getElementById('addProjectModal').style.display='block'" style="background: var(--accent); color: white; border: none; padding: 8px 16px; border-radius: 8px; font-weight: 600; font-size: 13px; cursor: pointer;">
            Add First Project
        </button>
    </div>
<?php else: ?>
    <div style="display: flex; flex-direction: column; gap: 16px;">
        <?php foreach ($projects as $proj): 
            // Badges logic
            $status = $proj['status'];
            $bg = '#f1f5f9'; $color = '#475569';
            if ($status === 'Approved APF') { $bg = '#dcfce7'; $color = '#166534'; }
            if ($status === 'Pending APF') { $bg = '#fef9c3'; $color = '#a16207'; }
        ?>
            <div class="card" style="margin-bottom: 0;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                    <div>
                        <h3 style="font-family: 'Outfit'; font-size: 18px; margin-bottom: 4px;"><?php echo htmlspecialchars($proj['project_name']); ?></h3>
                        <div style="font-size: 12px; color: var(--text-muted); display:flex; align-items:center; gap:4px;">
                            <i data-lucide="map-pin" style="width:12px;"></i> <?php echo htmlspecialchars($proj['location']); ?>
                        </div>
                    </div>
                    <span style="background: <?php echo $bg; ?>; color: <?php echo $color; ?>; padding: 4px 8px; border-radius: 6px; font-size: 10px; font-weight: 700; text-transform: uppercase;">
                        <?php echo $status; ?>
                    </span>
                </div>
                
                <div style="background: #f8fafc; padding: 12px; border-radius: 12px; font-size: 12px; color: var(--text-primary);">
                    <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                        <span style="color:var(--text-muted);">RERA No:</span>
                        <strong><?php echo htmlspecialchars($proj['rera_number']) ?: 'N/A'; ?></strong>
                    </div>
                    <div style="display:flex; justify-content:space-between;">
                        <span style="color:var(--text-muted);">APF Banks:</span>
                        <?php if ($status === 'Approved APF'): ?>
                            <strong>SBI, HDFC, ICICI</strong>
                        <?php else: ?>
                            <span style="color: #a16207; font-style:italic;">Processing...</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Add Project Modal -->
<div id="addProjectModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.5); z-index: 200; backdrop-filter: blur(4px);">
    <div style="position: absolute; bottom: 0; left: 0; width: 100%; background: white; border-top-left-radius: 24px; border-top-right-radius: 24px; padding: 24px; box-shadow: 0 -10px 40px rgba(0,0,0,0.1); animation: slideUp 0.3s ease-out;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-family: 'Outfit'; font-size: 18px;">Add New Project</h3>
            <i data-lucide="x" style="width: 20px; cursor: pointer; color: var(--text-muted);" onclick="document.getElementById('addProjectModal').style.display='none'"></i>
        </div>
        
        <form method="POST">
            <input type="hidden" name="action" value="add_project">
            
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 12px; font-weight: 600; color: var(--text-muted); margin-bottom: 6px;">Project Name *</label>
                <input type="text" name="project_name" required style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 12px; font-size: 14px; outline: none;">
            </div>
            
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 12px; font-weight: 600; color: var(--text-muted); margin-bottom: 6px;">Location</label>
                <input type="text" name="location" style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 12px; font-size: 14px; outline: none;" placeholder="e.g. Bandra West, Mumbai">
            </div>
            
            <div style="margin-bottom: 24px;">
                <label style="display: block; font-size: 12px; font-weight: 600; color: var(--text-muted); margin-bottom: 6px;">RERA Number</label>
                <input type="text" name="rera_number" style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 12px; font-size: 14px; outline: none;" placeholder="P518000XXXXX">
            </div>
            
            <button type="submit" style="width: 100%; background: var(--primary); color: white; border: none; padding: 14px; border-radius: 12px; font-weight: 700; font-size: 14px; cursor: pointer;">
                Submit for APF Approval
            </button>
        </form>
    </div>
</div>

<style>
@keyframes slideUp {
    from { transform: translateY(100%); }
    to { transform: translateY(0); }
}
</style>

<?php require_once 'includes/footer.php'; ?>
