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
    if ($_GET['msg'] == 'added') $msg = "<div class='alert alert-success alert-dismissible fade show'><i class='fa-solid fa-circle-check me-2'></i> Program added successfully!</div>";
    if ($_GET['msg'] == 'updated') $msg = "<div class='alert alert-success alert-dismissible fade show'><i class='fa-solid fa-circle-check me-2'></i> Program updated successfully!</div>";
    if ($_GET['msg'] == 'trashed') $msg = "<div class='alert alert-warning alert-dismissible fade show'><i class='fa-solid fa-trash-can me-2'></i> Program moved to Trash! You can restore it anytime from the Trash tab.</div>";
    if ($_GET['msg'] == 'restored') $msg = "<div class='alert alert-success alert-dismissible fade show'><i class='fa-solid fa-rotate-left me-2'></i> Program restored successfully to active list!</div>";
    if ($_GET['msg'] == 'deleted_permanent') $msg = "<div class='alert alert-danger alert-dismissible fade show'><i class='fa-solid fa-triangle-exclamation me-2'></i> Program permanently deleted!</div>";
}

// 1. Add New Program
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_program'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);
    $icon_class = $conn->real_escape_string($_POST['icon_class']);

    // Image Upload
    $target_dir = "../uploads/programs/";
    if (!file_exists($target_dir)) {
        @mkdir($target_dir, 0777, true);
    }
    $image_name = time() . '_' . basename($_FILES["image"]["name"]);
    $target_file = $target_dir . $image_name;

    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        $image_path = "uploads/programs/" . $image_name;
        $sql = "INSERT INTO certificate_programs (title, description, image_path, icon_class, is_published) 
                VALUES ('$title', '$description', '$image_path', '$icon_class', 1)";
        if ($conn->query($sql)) {
            header("Location: manage_programs.php?msg=added");
            exit;
        } else {
            $msg = "<div class='alert alert-danger'>Failed to save program to database.</div>";
        }
    } else {
        $msg = "<div class='alert alert-danger'>Failed to upload image.</div>";
    }
}

// 2. Edit / Update Program
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_program'])) {
    $id = (int) $_POST['program_id'];
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);
    $icon_class = $conn->real_escape_string($_POST['icon_class']);

    $update_sql = "UPDATE certificate_programs SET title='$title', description='$description', icon_class='$icon_class'";

    // Update image only if a new file is uploaded
    if (!empty($_FILES["image"]["name"])) {
        $target_dir = "../uploads/programs/";
        if (!file_exists($target_dir)) {
            @mkdir($target_dir, 0777, true);
        }
        $image_name = time() . '_' . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $image_name;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_path = "uploads/programs/" . $image_name;

            // Delete old image from server
            $old_img_res = $conn->query("SELECT image_path FROM certificate_programs WHERE id=$id");
            if ($row = $old_img_res->fetch_assoc()) {
                if (!empty($row['image_path']) && file_exists("../" . $row['image_path'])) {
                    @unlink("../" . $row['image_path']);
                }
            }
            // Append new image path to SQL update query
            $update_sql .= ", image_path='$image_path'";
        }
    }

    $update_sql .= " WHERE id=$id";
    $conn->query($update_sql);
    header("Location: manage_programs.php?msg=updated");
    exit;
}

// 3. Publish / Unpublish Toggle
if (isset($_GET['toggle_publish']) && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $status = (int) $_GET['toggle_publish'];
    $new_status = ($status == 1) ? 0 : 1;
    $conn->query("UPDATE certificate_programs SET is_published = $new_status WHERE id=$id");

    if ((!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || isset($_GET['ajax'])) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'new_status' => $new_status]);
        exit;
    }

    header("Location: manage_programs.php?msg=updated");
    exit;
}

// 4. Soft Delete Program (Move to Trash)
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $conn->query("UPDATE certificate_programs SET deleted_at = NOW() WHERE id=$id");
    header("Location: manage_programs.php?msg=trashed");
    exit;
}

// 5. Restore Program from Trash
if (isset($_GET['restore'])) {
    $id = (int) $_GET['restore'];
    $conn->query("UPDATE certificate_programs SET deleted_at = NULL WHERE id=$id");
    header("Location: manage_programs.php?view=trash&msg=restored");
    exit;
}

// 6. Permanent / Force Delete Program (Row + Image Unlink)
if (isset($_GET['force_delete'])) {
    $id = (int) $_GET['force_delete'];
    $res = $conn->query("SELECT image_path FROM certificate_programs WHERE id=$id");
    if ($row = $res->fetch_assoc()) {
        if (!empty($row['image_path']) && file_exists("../" . $row['image_path'])) {
            @unlink("../" . $row['image_path']);
        }
    }
    $conn->query("DELETE FROM certificate_programs WHERE id=$id");
    header("Location: manage_programs.php?view=trash&msg=deleted_permanent");
    exit;
}

// Edit Mode Check
$edit_mode = false;
$edit_data = null;
if (isset($_GET['edit'])) {
    $edit_mode = true;
    $edit_id = (int) $_GET['edit'];
    $edit_res = $conn->query("SELECT * FROM certificate_programs WHERE id=$edit_id");
    $edit_data = $edit_res->fetch_assoc();
}

// Tab View Mode
$view = isset($_GET['view']) && $_GET['view'] === 'trash' ? 'trash' : 'active';

// Counts for tabs
$count_active = 0;
$c_res = $conn->query("SELECT COUNT(*) as total FROM certificate_programs WHERE deleted_at IS NULL");
if ($c_res && $crow = $c_res->fetch_assoc()) {
    $count_active = (int) $crow['total'];
}

$count_trash = 0;
$t_res = $conn->query("SELECT COUNT(*) as total FROM certificate_programs WHERE deleted_at IS NOT NULL");
if ($t_res && $trow = $t_res->fetch_assoc()) {
    $count_trash = (int) $trow['total'];
}

// Fetch programs based on active/trash view
if ($view === 'trash') {
    $programs = $conn->query("SELECT * FROM certificate_programs WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC");
} else {
    $programs = $conn->query("SELECT * FROM certificate_programs WHERE deleted_at IS NULL ORDER BY display_order ASC, id DESC");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Programs - IB Admin</title>

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
                <h2 class="mb-4" style="color: #091a32; font-weight: 700;">Manage Certificate Programs</h2>
                <?php echo $msg; ?>

                <?php if ($view !== 'trash'): ?>
                <!-- Add / Edit Form -->
                <div class="card-custom" style="<?php echo $edit_mode ? 'border: 2px solid #d4af37;' : ''; ?>">
                    <h5 class="mb-3"><?php echo $edit_mode ? 'Edit Program Details' : 'Add New Program'; ?></h5>

                    <form action="manage_programs.php" method="POST" enctype="multipart/form-data" class="modern-form">
                        <?php if ($edit_mode): ?>
                            <input type="hidden" name="program_id" value="<?php echo $edit_data['id']; ?>">
                        <?php endif; ?>

                        <!-- Section 1: Basic Information -->
                        <div class="form-section mb-4">
                            <h6 class="section-title"><i class="fa-solid fa-image me-2"></i>1. Basic Information</h6>
                            <div class="row">
                                <div class="col-md-7 mb-3">
                                    <label class="form-label fw-bold">Program Title *</label>
                                    <input type="text" name="title" class="form-control"
                                        placeholder="E.g. Certificate in Business Management & Entrepreneurship"
                                        value="<?php echo $edit_mode ? htmlspecialchars($edit_data['title']) : ''; ?>"
                                        required>
                                </div>
                                <div class="col-md-5 mb-3">
                                    <label class="form-label fw-bold">Program Image
                                        <?php echo $edit_mode ? '(Leave empty to keep current)' : '*'; ?></label>
                                    <input type="file" name="image" class="form-control" accept="image/*" <?php echo $edit_mode ? '' : 'required'; ?>>

                                    <!-- Current Image Preview in Edit Mode -->
                                    <?php if ($edit_mode && !empty($edit_data['image_path'])): ?>
                                        <div class="mt-3 p-2 border rounded bg-light d-flex align-items-center gap-3">
                                            <img src="../<?php echo $edit_data['image_path']; ?>"
                                                class="img-thumbnail shadow-sm"
                                                style="height: 60px; width: auto; object-fit: cover;">
                                            <span class="text-muted small fw-bold">Current Banner Image</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Section 2: Visuals & Content -->
                        <div class="form-section mb-4">
                            <h6 class="section-title"><i class="fa-solid fa-file-lines me-2"></i>2. Visuals & Content
                            </h6>
                            <div class="row">
                                <!-- Icon Picker (Full width to avoid grid layout issues) -->
                                <div class="col-md-12 mb-4">
                                    <?php
                                    $curr_prog_icon = ($edit_mode && !empty($edit_data['icon_class'])) ? $edit_data['icon_class'] : 'tji-service-4';
                                    echo renderAdminIconPicker('icon_class', $curr_prog_icon, 'Program Icon Class', ['default' => 'tji-service-4']);
                                    ?>
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label class="form-label fw-bold">Short Description *</label>
                                    <textarea name="description" class="form-control" rows="4"
                                        placeholder="Write an engaging executive summary of this certificate program..."
                                        required><?php echo $edit_mode ? htmlspecialchars($edit_data['description']) : ''; ?></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-end align-items-center mt-4 pt-3 border-top">
                            <?php if ($edit_mode): ?>
                                <a href="manage_programs.php" class="btn btn-secondary px-4 py-2 me-3">Cancel</a>
                                <button type="submit" name="update_program" class="btn btn-custom px-5 py-2"><i
                                        class="fa-solid fa-floppy-disk me-2"></i> Update Program</button>
                            <?php else: ?>
                                <button type="submit" name="add_program" class="btn btn-custom px-5 py-2"><i
                                        class="fa-solid fa-plus me-2"></i> Add Program</button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
                <?php endif; ?>

                <!-- Programs Table Card -->
                <div class="card-custom">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                        <div class="admin-tab-nav">
                            <a href="manage_programs.php" class="admin-tab-nav-btn <?php echo $view === 'active' ? 'active' : ''; ?>">
                                <i class="fa-solid fa-list-check"></i> Active Programs <span class="tab-count-badge"><?php echo $count_active; ?></span>
                            </a>
                            <a href="manage_programs.php?view=trash" class="admin-tab-nav-btn tab-trash <?php echo $view === 'trash' ? 'active' : ''; ?>">
                                <i class="fa-solid fa-trash-can"></i> Trash <span class="tab-count-badge"><?php echo $count_trash; ?></span>
                            </a>
                        </div>
                        <?php if ($view === 'trash'): ?>
                            <a href="manage_programs.php" class="btn btn-sm btn-outline-secondary">
                                <i class="fa-solid fa-arrow-left me-1"></i> Back to Active Programs
                            </a>
                        <?php endif; ?>
                    </div>

                    <?php if ($view === 'trash'): ?>
                    <!-- Trash Programs Table -->
                    <div class="table-responsive">
                        <table class="table admin-table align-middle">
                            <thead style="background-color: #7f1d1d; color: #fff;">
                                <tr>
                                    <th style="width: 140px;">Image</th>
                                    <th>Title & Description</th>
                                    <th style="width: 190px; text-align: center;">Deleted On</th>
                                    <th style="width: 130px; text-align: center;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($programs && $programs->num_rows > 0):
                                    while ($row = $programs->fetch_assoc()): ?>
                                        <tr style="background: rgba(239, 68, 68, 0.02);">
                                            <td>
                                                <img src="../<?php echo !empty($row['image_path']) ? htmlspecialchars($row['image_path']) : 'assets/images/default.jpg'; ?>" 
                                                     class="img-preview"
                                                     style="width: 110px; height: 70px; object-fit: cover; border-radius: 8px; opacity: 0.75;">
                                            </td>
                                            <td>
                                                <strong style="color: var(--admin-navy); font-size: 15px;"><?php echo htmlspecialchars($row['title']); ?></strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($row['description']); ?></small>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                                    <i class="fa-regular fa-clock me-1"></i>
                                                    <?php echo !empty($row['deleted_at']) ? date('M d, Y h:i A', strtotime($row['deleted_at'])) : 'Unknown'; ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="action-btn-group justify-content-center">
                                                    <!-- Restore -->
                                                    <a href="manage_programs.php?restore=<?php echo $row['id']; ?>"
                                                        class="btn-action btn-action-restore" title="Restore Program to Active List">
                                                        <i class="fa-solid fa-rotate-left"></i>
                                                    </a>
                                                    <!-- Permanent Delete -->
                                                    <a href="manage_programs.php?force_delete=<?php echo $row['id']; ?>"
                                                        class="btn-action btn-action-force-delete" title="Permanently Delete Program"
                                                        data-item-name="<?php echo htmlspecialchars($row['title']); ?>">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">
                                            <i class="fa-regular fa-trash-can me-2"></i> Trash is empty.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <!-- Active Programs Table -->
                    <div class="table-responsive">
                        <table class="table admin-table align-middle">
                            <thead style="background-color: #091a32; color: #fff;">
                                <tr>
                                    <th style="width: 140px;">Image</th>
                                    <th>Title & Description</th>
                                    <th style="width: 110px; text-align: center;">Status</th>
                                    <th style="width: 140px; text-align: center;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($programs && $programs->num_rows > 0):
                                    while ($row = $programs->fetch_assoc()): ?>
                                        <tr>
                                            <td><img src="../<?php echo htmlspecialchars($row['image_path']); ?>" class="img-preview"
                                                    style="width: 110px; height: 70px; object-fit: cover; border-radius: 8px;">
                                            </td>
                                            <td>
                                                <strong
                                                    style="color: var(--admin-navy); font-size: 15px;"><?php echo htmlspecialchars($row['title']); ?></strong><br>
                                                <small
                                                    class="text-muted"><?php echo htmlspecialchars($row['description']); ?></small>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($row['is_published'] == 1): ?>
                                                    <span class="badge-published">Published</span>
                                                <?php else: ?>
                                                    <span class="badge-hidden">Hidden</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <div class="action-btn-group justify-content-center">
                                                    <!-- Edit Button -->
                                                    <a href="manage_programs.php?edit=<?php echo $row['id']; ?>"
                                                        class="btn-action btn-action-edit" title="Edit Program">
                                                        <i class="fa-solid fa-pen-to-square"></i>
                                                    </a>

                                                    <!-- Toggle Publish Button -->
                                                    <a href="manage_programs.php?toggle_publish=<?php echo $row['is_published']; ?>&id=<?php echo $row['id']; ?>"
                                                        class="btn-action <?php echo ($row['is_published'] == 1) ? 'btn-action-hide' : 'btn-action-publish'; ?>"
                                                        title="<?php echo ($row['is_published'] == 1) ? 'Hide / Unpublish Program' : 'Show / Publish Program'; ?>">
                                                        <i
                                                            class="fa-solid <?php echo ($row['is_published'] == 1) ? 'fa-eye-slash' : 'fa-eye'; ?>"></i>
                                                    </a>

                                                    <!-- Delete Button (Move to Trash) -->
                                                    <a href="manage_programs.php?delete=<?php echo $row['id']; ?>"
                                                        class="btn-action btn-action-delete" title="Move to Trash"
                                                        data-item-name="<?php echo htmlspecialchars($row['title']); ?>">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">No programs found in database.
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