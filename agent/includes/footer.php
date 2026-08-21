</div> <!-- End Container -->

<!-- Bottom Navigation Bar -->
<div class="bottom-nav">
    <a href="index.php" class="nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>">
        <i data-lucide="home"></i>
        <span>Home</span>
    </a>
    
    <a href="leads.php" class="nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'leads.php') ? 'active' : ''; ?>">
        <i data-lucide="list-todo"></i>
        <span>Leads</span>
    </a>
    
    <div class="fab-wrapper">
        <a href="add_lead.php" class="fab">
            <i data-lucide="plus"></i>
        </a>
    </div>
    
    <a href="calculators.php" class="nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'calculators.php') ? 'active' : ''; ?>">
        <i data-lucide="calculator"></i>
        <span>Calcs</span>
    </a>
    
    <a href="leaderboard.php" class="nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'leaderboard.php') ? 'active' : ''; ?>">
        <i data-lucide="trophy"></i>
        <span>Rank</span>
    </a>
</div>

<script>
    // Initialize Lucide icons
    lucide.createIcons();
    
    // Global function for showing notifications via SweetAlert2
    function showNotification(message, type = 'success') {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
        Toast.fire({
            icon: type,
            title: message
        });
    }
</script>
</body>
</html>
