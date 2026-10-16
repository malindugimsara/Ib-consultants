<?php
session_start();
// Connect to database
include '../db.php';

// Security check: redirect to login if not authenticated
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// Delete inquiry message action
$msg = '';
if (isset($_GET['delete'])) {
    $del_id = (int) $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM contact_messages WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $del_id);
        $stmt->execute();
    } else {
        $conn->query("DELETE FROM contact_messages WHERE id = $del_id");
    }
    header("Location: dashboard.php?msg=deleted");
    exit;
}

if (isset($_GET['msg']) && $_GET['msg'] === 'deleted') {
    $msg = "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                <i class='fa-solid fa-check-circle me-2'></i> Customer inquiry deleted successfully.
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
            </div>";
}

// 1. KPI Statistics Queries
$stat_inquiries = 0;
$inq_res = $conn->query("SELECT COUNT(*) AS total FROM contact_messages");
if ($inq_res && $row = $inq_res->fetch_assoc()) {
    $stat_inquiries = (int) $row['total'];
}

$stat_workshops = 0;
$ws_res = $conn->query("SELECT COUNT(*) AS total FROM business_workshops WHERE is_published = 1");
if ($ws_res && $row = $ws_res->fetch_assoc()) {
    $stat_workshops = (int) $row['total'];
}

$stat_programs = 0;
$prog_res = $conn->query("SELECT COUNT(*) AS total FROM certificate_programs WHERE is_published = 1");
if ($prog_res && $row = $prog_res->fetch_assoc()) {
    $stat_programs = (int) $row['total'];
}

$stat_diplomas = 0;
$dip_res = @$conn->query("SELECT COUNT(*) AS total FROM diploma_programs WHERE is_published = 1");
if ($dip_res && $row = $dip_res->fetch_assoc()) {
    $stat_diplomas = (int) $row['total'];
}

// 2. Fetch all contact inquiries (latest first)
$sql = "SELECT * FROM contact_messages ORDER BY created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - IB Consultants</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- CSS Dependencies -->
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/font-awesome-pro.min.css">
    <link rel="stylesheet" href="admin.css">

    <!-- Place favicon.ico in the root directory -->
    <link rel="shortcut icon" type="image/x-icon" href="../assets/images/logos/logo-2.webp">
</head>

<body>

    <div class="container-fluid p-0">
        <div class="row g-0">
            <!-- Left Sidebar Navigation Component -->
            <?php include 'sidebar.php'; ?>

            <!-- Right Main Content Area -->
            <div class="col-lg-10 col-md-9 main-content">

                <!-- Common Top Bar Component -->
                <?php include 'topbar.php'; ?>

                <!-- Page Heading -->
                <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                    <div>
                        <h2
                            style="color: var(--admin-navy); font-weight: 800; margin: 0; font-size: 26px; letter-spacing: -0.5px;">
                            Dashboard Overview</h2>
                        <p class="text-muted m-0" style="font-size: 14px;">Welcome back,
                            <strong><?php echo htmlspecialchars($_SESSION['admin_user'] ?? 'Admin'); ?></strong>! Here
                            is your business activity overview.</p>
                    </div>
                </div>

                <?php echo $msg; ?>

                <!-- KPI Metric Stat Cards -->
                <div class="stats-grid">
                    <!-- Inquiries Card -->
                    <a href="#inquiries-card" class="stat-card active-kpi">
                        <div class="stat-info">
                            <h3><?php echo number_format($stat_inquiries); ?></h3>
                            <p>Total Inquiries</p>
                        </div>
                        <div class="stat-icon-wrap stat-icon-gold">
                            <i class="fa-solid fa-envelope-open-text"></i>
                        </div>
                    </a>

                    <!-- Workshops Card -->
                    <a href="manage_workshops.php" class="stat-card">
                        <div class="stat-info">
                            <h3><?php echo number_format($stat_workshops); ?></h3>
                            <p>Active Workshops</p>
                        </div>
                        <div class="stat-icon-wrap stat-icon-navy">
                            <i class="fa-solid fa-briefcase"></i>
                        </div>
                    </a>

                    <!-- Programs Card -->
                    <a href="manage_programs.php" class="stat-card">
                        <div class="stat-info">
                            <h3><?php echo number_format($stat_programs); ?></h3>
                            <p>Certificate Programs</p>
                        </div>
                        <div class="stat-icon-wrap stat-icon-blue">
                            <i class="fa-solid fa-graduation-cap"></i>
                        </div>
                    </a>

                    <!-- Diplomas Card -->
                    <a href="manage_diplomas.php" class="stat-card">
                        <div class="stat-info">
                            <h3><?php echo number_format($stat_diplomas); ?></h3>
                            <p>Diploma Courses</p>
                        </div>
                        <div class="stat-icon-wrap stat-icon-green">
                            <i class="fa-solid fa-award"></i>
                        </div>
                    </a>
                </div>

                <!-- Customer Inquiries Table Card -->
                <div class="card-custom" id="inquiries-card">
                    <div class="card-custom-header">
                        <div class="card-custom-title">
                            <i class="fa-solid fa-inbox text-muted"></i>
                            <span>Customer Inquiries</span>
                            <span class="count-pill"><?php echo $result ? $result->num_rows : 0; ?></span>
                        </div>

                        <!-- Live Search Input -->
                        <?php if ($result && $result->num_rows > 0): ?>
                            <div class="table-search-box">
                                <i class="fa-solid fa-magnifying-glass"></i>
                                <input type="text" id="inquirySearch" placeholder="Search inquiries..."
                                    onkeyup="filterInquiries()">
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if ($result && $result->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table inquiry-table" id="inquiriesTable">
                                <thead>
                                    <tr>
                                        <th style="width: 140px;">Received Date</th>
                                        <th style="width: 280px;">Client Profile</th>
                                        <th style="width: 220px;">Service Requested</th>
                                        <th>Requirement / Message</th>
                                        <th style="width: 100px; text-align: center;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $result->fetch_assoc()):
                                        $initial = strtoupper(substr($row['name'] ?? 'C', 0, 1));
                                        $clean_mobile = preg_replace('/[^0-9]/', '', $row['mobile'] ?? '');
                                        $clean_wa = !empty($row['whatsapp']) ? preg_replace('/[^0-9]/', '', $row['whatsapp']) : $clean_mobile;
                                        if (strlen($clean_wa) == 10 && substr($clean_wa, 0, 1) == '0') {
                                            $clean_wa = '94' . substr($clean_wa, 1);
                                        }
                                        ?>
                                        <tr class="inquiry-row">
                                            <!-- Date & Time -->
                                            <td>
                                                <div class="date-meta-cell">
                                                    <strong><?php echo date('d M Y', strtotime($row['created_at'])); ?></strong>
                                                    <span><i
                                                            class="fa-regular fa-clock me-1"></i><?php echo date('h:i A', strtotime($row['created_at'])); ?></span>
                                                </div>
                                            </td>

                                            <!-- Customer Profile -->
                                            <td>
                                                <div class="customer-profile">
                                                    <div class="customer-avatar">
                                                        <?php echo $initial; ?>
                                                    </div>
                                                    <div class="customer-meta">
                                                        <strong
                                                            class="searchable-name"><?php echo htmlspecialchars($row['name']); ?></strong>

                                                        <div class="customer-links">
                                                            <!-- Email Pill -->
                                                            <?php if (!empty($row['email'])): ?>
                                                                <a href="mailto:<?php echo htmlspecialchars($row['email']); ?>"
                                                                    class="contact-pill contact-pill-email searchable-email"
                                                                    title="Send Email">
                                                                    <i class="fa-solid fa-envelope"></i>
                                                                    <?php echo htmlspecialchars($row['email']); ?>
                                                                </a>
                                                            <?php endif; ?>

                                                            <!-- Phone Call Pill -->
                                                            <?php if (!empty($row['mobile'])): ?>
                                                                <a href="tel:<?php echo htmlspecialchars($row['mobile']); ?>"
                                                                    class="contact-pill contact-pill-phone" title="Call Customer">
                                                                    <i class="fa-solid fa-phone"></i>
                                                                    <?php echo htmlspecialchars($row['mobile']); ?>
                                                                </a>
                                                            <?php endif; ?>

                                                            <!-- Direct WhatsApp Pill -->
                                                            <?php if (!empty($row['whatsapp']) || !empty($clean_wa)): ?>
                                                                <a href="https://wa.me/<?php echo $clean_wa; ?>" target="_blank"
                                                                    class="contact-pill contact-pill-whatsapp"
                                                                    title="Chat on WhatsApp">
                                                                    <i class="fa-brands fa-whatsapp"></i> WhatsApp
                                                                </a>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>

                                            <!-- Service Required -->
                                            <td>
                                                <span class="badge-service searchable-service">
                                                    <i class="fa-solid fa-tag me-1"></i>
                                                    <?php echo htmlspecialchars($row['service']); ?>
                                                </span>
                                            </td>

                                            <!-- Message / Requirement -->
                                            <td>
                                                <div class="inquiry-message-bubble searchable-message">
                                                    <?php echo nl2br(htmlspecialchars($row['message'])); ?>
                                                </div>
                                            </td>

                                            <!-- Action -->
                                            <td class="text-center">
                                                <div class="action-btn-group justify-content-center">
                                                    <!-- Reply Email -->
                                                    <?php if (!empty($row['email'])): ?>
                                                        <a href="mailto:<?php echo htmlspecialchars($row['email']); ?>?subject=Inquiry%20Response%20-%20IB%20Consultants"
                                                            class="btn-action btn-action-edit" title="Reply via Email">
                                                            <i class="fa-solid fa-reply"></i>
                                                        </a>
                                                    <?php endif; ?>

                                                    <!-- Delete Button -->
                                                    <a href="dashboard.php?delete=<?php echo $row['id']; ?>"
                                                        class="btn-action btn-action-delete" title="Delete Inquiry"
                                                        data-item-name="Inquiry from <?php echo htmlspecialchars($row['name']); ?>">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <!-- Empty State -->
                        <div class="empty-state-card">
                            <div class="empty-state-icon">
                                <i class="fa-solid fa-inbox"></i>
                            </div>
                            <h4 style="color: var(--admin-navy); font-weight: 800; margin-bottom: 8px;">No Customer
                                Inquiries Yet</h4>
                            <p class="text-muted" style="max-width: 450px; margin: 0 auto 20px auto;">
                                When visitors submit questions or workshop booking requests through the website's contact
                                form, they will appear here instantly.
                            </p>
                            <a href="../contact.php" target="_blank" class="btn btn-outline-dark btn-sm">
                                <i class="fa-solid fa-paper-plane me-1"></i> Test Contact Form on Website
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <!-- Core Admin Interaction Engine -->
    <script src="admin.js"></script>

    <!-- Client-side Instant Filter Script -->
    <script>
        function filterInquiries() {
            var input = document.getElementById('inquirySearch');
            var filter = input.value.toLowerCase().trim();
            var rows = document.querySelectorAll('.inquiry-row');

            rows.forEach(function (row) {
                var name = row.querySelector('.searchable-name') ? row.querySelector('.searchable-name').textContent.toLowerCase() : '';
                var email = row.querySelector('.searchable-email') ? row.querySelector('.searchable-email').textContent.toLowerCase() : '';
                var service = row.querySelector('.searchable-service') ? row.querySelector('.searchable-service').textContent.toLowerCase() : '';
                var message = row.querySelector('.searchable-message') ? row.querySelector('.searchable-message').textContent.toLowerCase() : '';

                if (name.includes(filter) || email.includes(filter) || service.includes(filter) || message.includes(filter)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
    </script>

</body>

</html>