<?php
// Determine current active page for sidebar navigation
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!-- Left Sidebar Navigation Component -->
<div class="col-lg-2 col-md-3 sidebar">
    <!-- Brand / Logo Area -->
    <a href="dashboard.php" class="sidebar-brand">
        <div class="sidebar-logo-frame">
            <img src="../assets/images/logos/logo-2.webp" alt="IB Logo">
        </div>
        <div class="sidebar-brand-text">
            <h4>IB ADMIN</h4>
            <span>Management Portal</span>
        </div>
    </a>

    <!-- Navigation Menu Items -->
    <div class="sidebar-nav">
        <a href="dashboard.php" class="<?php echo ($currentPage === 'dashboard.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-inbox"></i>
            <span>Customer Inquiries</span>
        </a>
        <a href="manage_workshops.php" class="<?php echo ($currentPage === 'manage_workshops.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-briefcase"></i>
            <span>Manage Workshops</span>
        </a>
        <a href="manage_programs.php" class="<?php echo ($currentPage === 'manage_programs.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-graduation-cap"></i>
            <span>Manage Programs</span>
        </a>
        <a href="manage_diplomas.php" class="<?php echo ($currentPage === 'manage_diplomas.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-award"></i>
            <span>Manage Diplomas</span>
        </a>
        <a href="manage_services.php" class="<?php echo ($currentPage === 'manage_services.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-layer-group"></i>
            <span>Core Services</span>
        </a>
        <a href="manage_reviews.php" class="<?php echo ($currentPage === 'manage_reviews.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-star"></i>
            <span>Manage Reviews</span>
        </a>
        <a href="manage_slider.php" class="<?php echo ($currentPage === 'manage_slider.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-images"></i>
            <span>Manage Slider</span>
        </a>
        <a href="manage_blog.php" class="<?php echo ($currentPage === 'manage_blog.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-newspaper"></i>
            <span>Blog & Articles</span>
        </a>
        <a href="../index.php" target="_blank">
            <i class="fa-solid fa-globe"></i>
            <span>View Website</span>
        </a>
    </div>

    <!-- Sidebar Bottom Footer -->
    <div class="sidebar-footer">
        <a href="logout.php" class="btn-logout" onclick="return confirm('Are you sure you want to log out?');">
            <i class="fa-solid fa-arrow-right-from-bracket me-2"></i> Log Out
        </a>
    </div>
</div>
