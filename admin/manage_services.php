<?php
session_start();
include '../db.php';
require_once 'icon_picker_helper.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}


$msg = '';
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'updated') $msg = "<div class='alert alert-success'>Service updated successfully!</div>";
    if ($_GET['msg'] == 'trashed') $msg = "<div class='alert alert-warning'>Service moved to Trash! You can restore it anytime from the Trash tab.</div>";
    if ($_GET['msg'] == 'restored') $msg = "<div class='alert alert-success'>Service restored successfully to active list!</div>";
    if ($_GET['msg'] == 'deleted_permanent') $msg = "<div class='alert alert-danger'>Service permanently deleted!</div>";
}

// Edit / Update Service 
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_service'])) {
    $id = (int) $_POST['service_id'];
    $title = $conn->real_escape_string($_POST['title']);
    $page_link = $conn->real_escape_string($_POST['page_link']);
    $icon_class = $conn->real_escape_string($_POST['icon_class']);
    $description = $conn->real_escape_string($_POST['description']);
    $list_items = $conn->real_escape_string($_POST['list_items']);

    $conn->query("UPDATE core_services SET title='$title', page_link='$page_link', icon_class='$icon_class', description='$description', list_items='$list_items' WHERE id=$id");
    header("Location: manage_services.php?msg=updated");
    exit;
}

// Publish / Unpublish Toggle
if (isset($_GET['toggle_publish']) && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $status = (int) $_GET['toggle_publish'];
    $new_status = ($status == 1) ? 0 : 1;
    $conn->query("UPDATE core_services SET is_published = $new_status WHERE id=$id");

    if ((!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || isset($_GET['ajax'])) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'new_status' => $new_status]);
        exit;
    }

    header("Location: manage_services.php?msg=updated");
    exit;
}

// Soft Delete (Move to Trash)
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $conn->query("UPDATE core_services SET deleted_at = NOW() WHERE id=$id");
    header("Location: manage_services.php?msg=trashed");
    exit;
}

// Restore from Trash
if (isset($_GET['restore'])) {
    $id = (int) $_GET['restore'];
    $conn->query("UPDATE core_services SET deleted_at = NULL WHERE id=$id");
    header("Location: manage_services.php?view=trash&msg=restored");
    exit;
}

// Permanent Delete
if (isset($_GET['force_delete'])) {
    $id = (int) $_GET['force_delete'];
    $conn->query("DELETE FROM core_services WHERE id=$id");
    header("Location: manage_services.php?view=trash&msg=deleted_permanent");
    exit;
}

// Edit Mode Check
$edit_mode = false;
$edit_data = null;
if (isset($_GET['edit'])) {
    $edit_mode = true;
    $edit_id = (int) $_GET['edit'];
    $edit_res = $conn->query("SELECT * FROM core_services WHERE id=$edit_id");
    $edit_data = $edit_res->fetch_assoc();
}

// Tab View Mode
$view = isset($_GET['view']) && $_GET['view'] === 'trash' ? 'trash' : 'active';

// Counts for tabs
$count_active = 0;
$c_res = $conn->query("SELECT COUNT(*) as total FROM core_services WHERE deleted_at IS NULL");
if ($c_res && $crow = $c_res->fetch_assoc()) {
    $count_active = (int) $crow['total'];
}

$count_trash = 0;
$t_res = $conn->query("SELECT COUNT(*) as total FROM core_services WHERE deleted_at IS NOT NULL");
if ($t_res && $trow = $t_res->fetch_assoc()) {
    $count_trash = (int) $trow['total'];
}

if ($view === 'trash') {
    $services = $conn->query("SELECT * FROM core_services WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC");
} else {
    $services = $conn->query("SELECT * FROM core_services WHERE deleted_at IS NULL ORDER BY display_order ASC, id ASC");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Core Services - IB Admin</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- CSS Dependencies -->
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/font-awesome-pro.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="admin.css">

    <!-- Place favicon.ico in the root directory -->
    <link rel="shortcut icon" type="image/x-icon" href="../assets/images/logos/logo-2.webp">
</head>

<body>

    <div class="container-fluid p-0">
        <div class="row g-0">
            <!-- Sidebar Component -->
            <?php include 'sidebar.php'; ?>

            <!-- Main Content Area -->
            <div class="col-lg-10 col-md-9 main-content">
                <?php include 'topbar.php'; ?>
                <h2 class="mb-4" style="color: #091a32; font-weight: 700;">Manage Core Services (10 Services)</h2>
                <?php echo $msg; ?>

                <!-- Edit Form  -->
                <?php if ($edit_mode): ?>
                    <div class="card-custom" style="border: 2px solid #d4af37;">
                        <h5 class="mb-3">Edit Service Details</h5>
                        <form action="manage_services.php" method="POST">
                            <input type="hidden" name="service_id" value="<?php echo $edit_data['id']; ?>">

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Service Title (Do not add numbers like 01., it will
                                        auto-generate)</label>
                                    <input type="text" name="title" class="form-control"
                                        value="<?php echo htmlspecialchars($edit_data['title']); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Page Link</label>
                                    <input type="text" name="page_link" class="form-control"
                                        value="<?php echo htmlspecialchars($edit_data['page_link']); ?>"
                                        placeholder="e.g. business-transformation-kpi.php" required>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <?php
                                    $curr_svc_icon = $edit_data['icon_class'] ?? 'tji-service-1';
                                    echo renderAdminIconPicker('icon_class', $curr_svc_icon, 'Service Icon Class', ['default' => 'tji-service-1']);
                                    ?>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label>Description</label>
                                    <textarea name="description" class="form-control" rows="4"
                                        required><?php echo htmlspecialchars($edit_data['description']); ?></textarea>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>List Items (Enter one item per line)</label>
                                    <textarea name="list_items" class="form-control" rows="4"
                                        required><?php echo htmlspecialchars($edit_data['list_items']); ?></textarea>
                                </div>
                                <div class="col-md-12 text-end">
                                    <a href="manage_services.php" class="btn btn-secondary px-4 me-2">Cancel</a>
                                    <button type="submit" name="update_service" class="btn btn-custom px-4">Update
                                        Service</button>
                                </div>
                            </div>
                        </form>
                    </div>
                <?php else: ?>
                    <!-- Message (When Not in Edit Mode) -->
                    <div class="card-custom text-center py-4 mb-4">
                        <h5 class="text-muted mb-0"><i class="fa-solid fa-hand-pointer me-2"></i> Please select a service
                            from the list below to edit its details.</h5>
                    </div>
                <?php endif; ?>

                <!-- List Services Card -->
                <div class="card-custom">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                        <div class="admin-tab-nav">
                            <a href="manage_services.php" class="admin-tab-nav-btn <?php echo $view === 'active' ? 'active' : ''; ?>">
                                <i class="fa-solid fa-list-check"></i> Active Services <span class="tab-count-badge"><?php echo $count_active; ?></span>
                            </a>
                            <a href="manage_services.php?view=trash" class="admin-tab-nav-btn tab-trash <?php echo $view === 'trash' ? 'active' : ''; ?>">
                                <i class="fa-solid fa-trash-can"></i> Trash <span class="tab-count-badge"><?php echo $count_trash; ?></span>
                            </a>
                        </div>
                        <?php if ($view === 'trash'): ?>
                            <a href="manage_services.php" class="btn btn-sm btn-outline-secondary">
                                <i class="fa-solid fa-arrow-left me-1"></i> Back to Active Services
                            </a>
                        <?php endif; ?>
                    </div>

                    <?php if ($view === 'trash'): ?>
                    <!-- Trash Services Table -->
                    <div class="table-responsive">
                        <table class="table admin-table align-middle">
                            <thead style="background-color: #7f1d1d; color: #fff;">
                                <tr>
                                    <th style="width: 60px; text-align: center;">No.</th>
                                    <th style="width: 320px;">Title & Page Link</th>
                                    <th>Key List Items</th>
                                    <th style="width: 190px; text-align: center;">Deleted On</th>
                                    <th style="width: 130px; text-align: center;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($services->num_rows > 0):
                                    $t_count = 1;
                                    while ($row = $services->fetch_assoc()):
                                        $formatted_list = nl2br(htmlspecialchars($row['list_items']));
                                        ?>
                                        <tr style="background: rgba(239, 68, 68, 0.02);">
                                            <td class="text-center fw-bold text-muted">
                                                <?php echo str_pad($t_count, 2, '0', STR_PAD_LEFT); ?>
                                            </td>
                                            <td>
                                                <strong style="color: var(--admin-navy); font-size: 15px;"><?php echo htmlspecialchars($row['title']); ?></strong>
                                            </td>
                                            <td style="font-size: 13px; color: #475569;"><?php echo $formatted_list; ?></td>
                                            <td class="text-center">
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                                    <i class="fa-regular fa-clock me-1"></i>
                                                    <?php echo !empty($row['deleted_at']) ? date('M d, Y h:i A', strtotime($row['deleted_at'])) : 'Unknown'; ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="action-btn-group justify-content-center">
                                                    <!-- Restore -->
                                                    <a href="manage_services.php?restore=<?php echo $row['id']; ?>"
                                                        class="btn-action btn-action-restore" title="Restore Service to Active List">
                                                        <i class="fa-solid fa-rotate-left"></i>
                                                    </a>
                                                    <!-- Permanent Delete -->
                                                    <a href="manage_services.php?force_delete=<?php echo $row['id']; ?>"
                                                        class="btn-action btn-action-force-delete" title="Permanently Delete Service"
                                                        data-item-name="<?php echo htmlspecialchars($row['title']); ?>">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php
                                        $t_count++;
                                    endwhile;
                                else:
                                    ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">
                                            <i class="fa-regular fa-trash-can me-2"></i> Trash is empty.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <!-- Active Services Table -->
                    <div class="table-responsive">
                        <table class="table admin-table align-middle">
                            <thead style="background-color: #091a32; color: #fff;">
                                <tr>
                                    <th style="width: 60px; text-align: center;">No.</th>
                                    <th style="width: 260px;">Title & Page Link</th>
                                    <th>Key List Items</th>
                                    <th style="width: 110px; text-align: center;">Status</th>
                                    <th style="width: 140px; text-align: center;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($services->num_rows > 0):
                                    $count = 1;
                                    while ($row = $services->fetch_assoc()):
                                        $formatted_list = nl2br(htmlspecialchars($row['list_items']));
                                        ?>
                                        <tr>
                                            <td class="text-center" style="font-weight: 700; color: var(--admin-navy);">
                                                <?php echo str_pad($count, 2, '0', STR_PAD_LEFT) . '.'; ?>
                                            </td>
                                            <td>
                                                <strong
                                                    style="color: var(--admin-navy); font-size: 15px;"><?php echo htmlspecialchars($row['title']); ?></strong><br>
                                            </td>
                                            <td style="font-size: 13px; color: #475569;"><?php echo $formatted_list; ?></td>

                                            <td class="text-center" id="status-badge-<?php echo $row['id']; ?>">
                                                <?php if ($row['is_published'] == 1): ?>
                                                    <span class="badge-published">Published</span>
                                                <?php else: ?>
                                                    <span class="badge-hidden">Hidden</span>
                                                <?php endif; ?>
                                            </td>

                                            <td class="text-center">
                                                <div class="action-btn-group justify-content-center">
                                                    <!-- Edit -->
                                                    <a href="manage_services.php?edit=<?php echo $row['id']; ?>"
                                                        class="btn-action btn-action-edit" title="Edit Service">
                                                        <i class="fa-solid fa-pen-to-square"></i>
                                                    </a>

                                                    <!-- Toggle Publish/Hide -->
                                                    <a href="manage_services.php?toggle_publish=<?php echo $row['is_published']; ?>&id=<?php echo $row['id']; ?>"
                                                        class="btn-action <?php echo ($row['is_published'] == 1) ? 'btn-action-hide' : 'btn-action-publish'; ?>"
                                                        title="<?php echo ($row['is_published'] == 1) ? 'Hide / Unpublish Service' : 'Show / Publish Service'; ?>">
                                                        <i class="fa-solid <?php echo ($row['is_published'] == 1) ? 'fa-eye-slash' : 'fa-eye'; ?>"></i>
                                                    </a>

                                                    <!-- Delete (Soft Delete) -->
                                                    <a href="manage_services.php?delete=<?php echo $row['id']; ?>"
                                                        class="btn-action btn-action-delete" title="Move to Trash"
                                                        data-item-name="<?php echo htmlspecialchars($row['title']); ?>">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php
                                        $count++;
                                    endwhile;
                                else:
                                    ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">No services found in database.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle JS -->
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <!-- Core Admin Interaction Engine -->
    <script src="admin.js"></script>

</body>

</html>