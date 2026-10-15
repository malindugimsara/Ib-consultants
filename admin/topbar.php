<?php
/**
 * admin/topbar.php
 * Common Admin Top Navigation Bar for IB Consultants Management Portal
 */
if (!isset($_SESSION)) {
    session_start();
}
$current_admin_user = htmlspecialchars($_SESSION['admin_user'] ?? 'Admin');
$admin_initial = strtoupper(substr($current_admin_user, 0, 1));
?>

<!-- Common Admin Top Bar -->
<div class="admin-topbar-card">
    <!-- Left Side: Company Logo & Brand -->
    <div class="topbar-left">
        <a href="dashboard.php" class="topbar-brand">
            <div class="topbar-logo-frame">
                <img src="../assets/images/logos/logo-2.webp" alt="IB Consultants Logo" class="topbar-logo-img" style="width: 36px; height: 36px; object-fit: contain;">
            </div>
            <div class="topbar-brand-info">
                <span class="topbar-brand-name">IB CONSULTANTS</span>
                <span class="topbar-brand-sub">Management Portal</span>
            </div>
        </a>
    </div>

    <!-- Right Side: Date, Live Time, Live Site, Profile & Logout Button -->
    <div class="topbar-right">
        <!-- Today's Date -->
        <div class="topbar-item topbar-date" title="Today's Date">
            <i class="fa-regular fa-calendar-days text-gold"></i>
            <span><?php echo date('l, d M Y'); ?></span>
        </div>

        <!-- Vertical Divider Bar -->
        <div class="topbar-vr"></div>

        <!-- Today's Real-time Live Clock -->
        <div class="topbar-item topbar-time" title="Current Sri Lanka Standard Time">
            <i class="fa-regular fa-clock text-gold"></i>
            <span id="adminLiveClockDisplay"><?php echo date('h:i:s A'); ?></span>
        </div>

        <!-- Vertical Divider Bar -->
        <div class="topbar-vr d-none d-md-inline-block"></div>

        <!-- Live Website Button -->
        <a href="../index.php" target="_blank" class="topbar-link-btn d-none d-md-inline-flex" title="Open Public Website in New Tab">
            <i class="fa-solid fa-globe"></i>
            <span>View Website</span>
        </a>

        <!-- Vertical Divider Bar -->
        <div class="topbar-vr d-none d-sm-inline-block"></div>

        <!-- Admin Profile Info -->
        <div class="topbar-item topbar-profile">
            <div class="topbar-avatar" title="<?php echo $current_admin_user; ?> (Administrator)">
                <?php echo $admin_initial; ?>
                <span class="online-dot" title="Online Status"></span>
            </div>
            <div class="topbar-user-info d-none d-lg-flex">
                <span class="topbar-username"><?php echo $current_admin_user; ?></span>
                <span class="topbar-role">Administrator</span>
            </div>
        </div>

        <!-- Vertical Divider Bar -->
        <div class="topbar-vr"></div>

        <!-- Logout Button on Right Side -->
        <a href="logout.php" class="topbar-logout-btn" onclick="return confirm('Are you sure you want to log out?');" title="Log Out from Admin Portal">
            <i class="fa-solid fa-arrow-right-from-bracket"></i>
            <span>Logout</span>
        </a>
    </div>
</div>

<!-- Live Real-Time Clock Script -->
<script>
(function() {
    function updateAdminClock() {
        var clockElement = document.getElementById('adminLiveClockDisplay');
        if (!clockElement) return;
        var now = new Date();
        var hours = now.getHours();
        var minutes = now.getMinutes();
        var seconds = now.getSeconds();
        var ampm = hours >= 12 ? 'PM' : 'AM';
        
        hours = hours % 12;
        hours = hours ? hours : 12; // 0 becomes 12
        var hoursStr = hours < 10 ? '0' + hours : hours;
        var minutesStr = minutes < 10 ? '0' + minutes : minutes;
        var secondsStr = seconds < 10 ? '0' + seconds : seconds;
        
        clockElement.textContent = hoursStr + ':' + minutesStr + ':' + secondsStr + ' ' + ampm;
    }
    setInterval(updateAdminClock, 1000);
})();
</script>
