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
$ws_res = $conn->query("SELECT COUNT(*) AS total FROM business_workshops WHERE is_published = 1 AND deleted_at IS NULL");
if ($ws_res && $row = $ws_res->fetch_assoc()) {
    $stat_workshops = (int) $row['total'];
}

$stat_programs = 0;
$prog_res = $conn->query("SELECT COUNT(*) AS total FROM certificate_programs WHERE is_published = 1 AND deleted_at IS NULL");
if ($prog_res && $row = $prog_res->fetch_assoc()) {
    $stat_programs = (int) $row['total'];
}

$stat_diplomas = 0;
$dip_res = @$conn->query("SELECT COUNT(*) AS total FROM diploma_programs WHERE is_published = 1 AND deleted_at IS NULL");
if ($dip_res && $row = $dip_res->fetch_assoc()) {
    $stat_diplomas = (int) $row['total'];
}

// 2. Fetch last 30 contact inquiries (latest first)
$sql = "SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 30";
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

    <!-- DataTables CSS Dependencies -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">

    <!-- DataTables Polish Styles -->
    <style>
        .dataTables_wrapper {
            width: 100%;
        }

        /* --- Toolbar Container Alignment --- */
        .dt-toolbar-left {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            padding-left: 20px;
        }

        /* --- Length Dropdown (Show X entries) --- */
        .dataTables_wrapper .dataTables_length {
            display: inline-flex;
            align-items: center;
            margin-bottom: 0;
        }

        .dataTables_wrapper .dataTables_length label {
            display: inline-flex !important;
            align-items: center !important;
            gap: 8px !important;
            font-size: 13px !important;
            font-weight: 600 !important;
            color: #64748b !important;
            margin: 0 !important;
            text-transform: none !important;
            padding-left: 20px !important;
        }

        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_length select.form-select,
        .dataTables_wrapper .dataTables_length select.form-select-sm {
            min-height: 34px !important;
            height: 34px !important;
            line-height: 1 !important;
            padding: 4px 28px 4px 10px !important;
            font-size: 13px !important;
            font-weight: 700 !important;
            color: var(--admin-navy) !important;
            background-color: #ffffff !important;
            border: 1.5px solid #cbd5e1 !important;
            border-radius: 8px !important;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04) !important;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .dataTables_wrapper .dataTables_length select:hover {
            border-color: #94a3b8 !important;
        }

        .dataTables_wrapper .dataTables_length select:focus {
            border-color: var(--admin-gold) !important;
            box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.2) !important;
            outline: none !important;
        }

        /* --- Vertical Divider between Length & Buttons --- */
        .dataTables_wrapper .dt-buttons {
            display: inline-flex !important;
            align-items: center !important;
            gap: 6px !important;
            padding-left: 12px !important;
            margin-left: 6px !important;
            border-left: 1.5px solid #e2e8f0 !important;
        }

        .dataTables_wrapper .dt-buttons.btn-group {
            box-shadow: none !important;
        }

        .dataTables_wrapper .dt-buttons.btn-group>.btn {
            border-radius: 8px !important;
            margin-right: 0 !important;
        }

        /* --- Export Buttons (Excel, CSV, Print) --- */
        .dataTables_wrapper .dt-buttons .btn {
            min-height: 34px !important;
            height: 34px !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 6px !important;
            padding: 0 12px !important;
            font-size: 12.5px !important;
            font-weight: 700 !important;
            color: #334155 !important;
            background: #ffffff !important;
            border: 1.5px solid #cbd5e1 !important;
            border-radius: 8px !important;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04) !important;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1) !important;
        }

        .dataTables_wrapper .dt-buttons .btn:hover {
            border-color: var(--admin-gold) !important;
            color: var(--admin-navy) !important;
            background: #fdfbf7 !important;
            box-shadow: 0 4px 10px rgba(9, 26, 50, 0.08) !important;
            transform: translateY(-1.5px);
        }

        .dataTables_wrapper .dt-buttons .btn:active {
            transform: translateY(0);
        }

        .dataTables_wrapper .dt-buttons .btn i {
            font-size: 13px;
        }

        /* --- Search Filter Box --- */
        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 0 !important;
        }

        .dataTables_wrapper .dataTables_filter label {
            position: relative !important;
            display: inline-flex !important;
            align-items: center !important;
            margin: 0 !important;
        }

        .dataTables_wrapper .dataTables_filter label::before {
            content: "\f002";
            font-family: "Font Awesome 6 Pro", "Font Awesome 6 Free", sans-serif;
            font-weight: 900;
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 12.5px;
            pointer-events: none;
            z-index: 2;
        }

        .dataTables_wrapper .dataTables_filter input {
            min-height: 34px !important;
            height: 34px !important;
            padding: 5px 14px 5px 34px !important;
            font-size: 13px !important;
            font-weight: 500 !important;
            border-radius: 8px !important;
            border: 1.5px solid #cbd5e1 !important;
            background-color: #ffffff !important;
            width: 220px !important;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03) !important;
            transition: all 0.25s ease !important;
            margin-left: 0 !important;
        }

        .dataTables_wrapper .dataTables_filter input:focus {
            background-color: #ffffff !important;
            border-color: var(--admin-gold) !important;
            box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.2) !important;
            width: 260px !important;
            outline: none !important;
        }

        @media (max-width: 767px) {

            .dataTables_wrapper .dataTables_filter,
            .dataTables_wrapper .dataTables_filter label,
            .dataTables_wrapper .dataTables_filter input {
                width: 100% !important;
            }

            .dataTables_wrapper .dt-buttons {
                border-left: none !important;
                padding-left: 0 !important;
                margin-left: 0 !important;
            }
        }

        /* --- Pagination Controls --- */
        .dataTables_wrapper .page-item.active .page-link {
            background-color: var(--admin-navy) !important;
            border-color: var(--admin-navy) !important;
            color: var(--admin-gold) !important;
            font-weight: 700;
        }

        .dataTables_wrapper .page-link {
            color: var(--admin-navy);
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            margin: 0 2px;
            font-size: 13px;
            padding: 6px 12px;
            transition: all 0.2s ease;
        }

        .dataTables_wrapper .page-link:hover {
            background-color: rgba(212, 175, 55, 0.15);
            color: #b89326;
            border-color: var(--admin-gold);
        }

        .dataTables_info {
            font-size: 13px;
            font-weight: 600;
            color: var(--admin-text-muted);
            padding-top: 6px;
        }

        table.dataTable thead th {
            font-size: 12px !important;
            letter-spacing: 0.5px;
            color: var(--admin-navy) !important;
            background-color: #f8fafc !important;
            border-bottom: 2px solid var(--admin-border) !important;
            padding: 14px 18px !important;
            user-select: none;
        }
    </style>

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
                            is your business activity overview.
                        </p>
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
                            <span>Customer Inquiries (Recent 30)</span>
                            <span class="count-pill"><?php echo $result ? $result->num_rows : 0; ?></span>
                        </div>
                    </div>

                    <?php if ($result && $result->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table inquiry-table w-100 align-middle" id="inquiriesTable">
                                <thead>
                                    <tr>
                                        <th style="width: 140px;">Received Date</th>
                                        <th style="width: 280px;">Client Profile</th>
                                        <th style="width: 220px;">Service Requested</th>
                                        <th>Requirement / Message</th>
                                        <th style="width: 100px; text-align: center;" class="no-sort">Action</th>
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
                                            <!-- Date & Time (Sortable by unix timestamp) -->
                                            <td data-order="<?php echo strtotime($row['created_at']); ?>">
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

    <!-- Core JS Dependencies (jQuery + Bootstrap) -->
    <script src="../assets/js/jquery.min.js"></script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>

    <!-- DataTables & Extension Scripts -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

    <!-- Core Admin Interaction Engine -->
    <script src="admin.js"></script>

    <!-- DataTables Initialization -->
    <script>
        $(document).ready(function () {
            if ($('#inquiriesTable').length) {
                $('#inquiriesTable').DataTable({
                    responsive: true,
                    order: [[0, 'desc']], // Sort by Date descending (newest first)
                    pageLength: 10,
                    lengthMenu: [[10, 25, 30, -1], [10, 25, 30, "All"]],
                    dom: "<'row mb-3 align-items-center g-2'<'col-md-8 d-flex align-items-center flex-wrap dt-toolbar-left'lB><'col-md-4 d-flex justify-content-md-end'f>>" +
                        "t" +
                        "<'row mt-3 align-items-center'<'col-md-6'i><'col-md-6 d-flex justify-content-md-end'p>>",
                    buttons: [
                        {
                            extend: 'excelHtml5',
                            text: '<i class="fa-solid fa-file-excel me-1 text-success"></i> Excel',
                            className: 'btn btn-sm btn-light border shadow-sm',
                            title: 'Customer_Inquiries_IB_Consultants',
                            exportOptions: { columns: [0, 1, 2, 3] }
                        },
                        {
                            extend: 'csvHtml5',
                            text: '<i class="fa-solid fa-file-csv me-1 text-primary"></i> CSV',
                            className: 'btn btn-sm btn-light border shadow-sm',
                            title: 'Customer_Inquiries_IB_Consultants',
                            exportOptions: { columns: [0, 1, 2, 3] }
                        },
                        {
                            extend: 'print',
                            text: '<i class="fa-solid fa-print me-1 text-secondary"></i> Print',
                            className: 'btn btn-sm btn-light border shadow-sm',
                            title: 'Customer Inquiries - IB Consultants',
                            exportOptions: { columns: [0, 1, 2, 3] }
                        }
                    ],
                    columnDefs: [
                        { orderable: false, targets: 4 } // Disable sorting on Action column
                    ],
                    language: {
                        search: "",
                        searchPlaceholder: "Search inquiries...",
                        lengthMenu: "Show _MENU_ records",
                        info: "Showing _START_ to _END_ of _TOTAL_ inquiries",
                        infoEmpty: "No inquiries to show",
                        infoFiltered: "(filtered from _MAX_ total)",
                        paginate: {
                            previous: '<i class="fa-solid fa-chevron-left"></i>',
                            next: '<i class="fa-solid fa-chevron-right"></i>'
                        }
                    }
                });
            }
        });
    </script>

</body>

</html>