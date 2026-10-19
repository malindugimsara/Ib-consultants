<?php
// Determine current active page for sidebar navigation
$currentPage = basename($_SERVER['PHP_SELF']);

// Check if training sub-menu should be open (අදාළ පිටුවලදී මෙනුව ස්වයංක්‍රීයව විවෘතව තැබීම)
$isTrainingActive = in_array($currentPage, ['manage_workshops.php', 'manage_programs.php', 'manage_diplomas.php']);
?>

<style>
    /* Sidebar Dropdown & Submenu Styles */
    .sidebar-nav a {
        display: flex !important;
        align-items: center;
    }
    .sidebar-nav a span {
        flex-grow: 1;
    }
    /* පහළට හැරෙන ඊතලයේ ඇනිමේෂන් එක */
    .sidebar-nav a[aria-expanded="true"] .toggle-icon {
        transform: rotate(180deg);
    }
    /* Sub-menu ඩිසයින් එක */
    .sub-menu-wrap {
        margin-left: 22px;
        padding-left: 10px;
        border-left: 2px solid rgba(212, 175, 55, 0.3); /* රන්වන් පැහැති ඉරක් */
        margin-bottom: 15px;
        margin-top: -5px;
    }
    .sub-menu-wrap a {
        font-size: 14px !important;
        padding: 8px 12px !important;
        margin-bottom: 4px !important;
        background: transparent !important;
    }
    .sub-menu-wrap a i {
        font-size: 12px !important;
    }
    .sub-menu-wrap a:hover, 
    .sub-menu-wrap a.active {
        background: rgba(212, 175, 55, 0.15) !important;
        color: #d4af37 !important;
    }
</style>

<!-- Left Sidebar Navigation Component -->
<div class="col-lg-2 col-md-3 sidebar">
    <!-- Brand / Logo Area -->
    <div class="d-flex align-items-center justify-content-between mb-3 pb-3" style="border-bottom: 1px solid rgba(255, 255, 255, 0.1);">
        <a href="dashboard.php" class="sidebar-brand mb-0 pb-0 border-0 flex-grow-1">
            <div class="sidebar-logo-frame">
                <img src="../assets/images/logos/logo-2.webp" alt="IB Logo">
            </div>
            <div class="sidebar-brand-text">
                <h4>IB ADMIN</h4>
                <span>Management Portal</span>
            </div>
        </a>
        <button type="button" class="sidebar-close-btn d-lg-none" id="sidebarCloseBtn" aria-label="Close Sidebar">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <!-- Navigation Menu Items -->
    <div class="sidebar-nav">
        <a href="dashboard.php" class="<?php echo ($currentPage === 'dashboard.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-inbox"></i>
            <span>Customer Inquiries</span>
        </a>

        <!-- ============================================= -->
        <!-- Training & Programs Main Category (Dropdown)  -->
        <!-- ============================================= -->
        <a href="#trainingSubMenu" data-bs-toggle="collapse" role="button" 
           aria-expanded="<?php echo $isTrainingActive ? 'true' : 'false'; ?>" 
           aria-controls="trainingSubMenu" 
           class="<?php echo $isTrainingActive ? 'active' : ''; ?>">
            <i class="fa-solid fa-chalkboard-user"></i>
            <span>Training & Programs</span>
            <i class="fa-solid fa-chevron-down ms-auto toggle-icon" style="transition: transform 0.3s; font-size: 12px;"></i>
        </a>

        <!-- Submenu Items -->
        <div class="collapse <?php echo $isTrainingActive ? 'show' : ''; ?>" id="trainingSubMenu">
            <div class="sub-menu-wrap">
                <a href="manage_workshops.php" class="<?php echo ($currentPage === 'manage_workshops.php') ? 'active' : ''; ?>">
                    <i class="fa-solid fa-briefcase"></i>
                    <span>Manage Workshops</span>
                </a>
                <a href="manage_programs.php" class="<?php echo ($currentPage === 'manage_programs.php') ? 'active' : ''; ?>">
                    <i class="fa-solid fa-graduation-cap"></i>
                    <span>Certificate Programs</span>
                </a>
                <a href="manage_diplomas.php" class="<?php echo ($currentPage === 'manage_diplomas.php') ? 'active' : ''; ?>">
                    <i class="fa-solid fa-award"></i>
                    <span>Advanced Diplomas</span>
                </a>
            </div>
        </div>
        <!-- ============================================= -->

        <!-- <a href="manage_services.php" class="<?php echo ($currentPage === 'manage_services.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-layer-group"></i>
            <span>Manage Core Services</span>
        </a> -->
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
            <span>Manage Blog & Articles</span>
        </a>
        <a href="../index.php" target="_blank">
            <i class="fa-solid fa-globe"></i>
            <span>View Website</span>
        </a>
    </div>

    <!-- Sidebar Bottom Footer -->
    <div class="sidebar-footer">
        <a href="logout.php" class="btn-logout" title="Log Out from Admin Portal">
            <i class="fa-solid fa-arrow-right-from-bracket me-2"></i> Log Out
        </a>
    </div>
</div>