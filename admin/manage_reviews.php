<?php
session_start();
include '../db.php';
require_once 'icon_picker_helper.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$msg = '';

// Toggle Publish / Hide Status
if (isset($_GET['toggle_publish']) && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $status = (int) $_GET['toggle_publish'];
    $new_status = ($status == 1) ? 0 : 1;
    $conn->query("UPDATE client_reviews SET is_published = $new_status WHERE id = $id");

    if ((!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || isset($_GET['ajax'])) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'new_status' => $new_status]);
        exit;
    }

    header("Location: manage_reviews.php?msg=updated");
    exit;
}

// Delete Review
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $conn->query("DELETE FROM client_reviews WHERE id=$id");
    header("Location: manage_reviews.php?msg=deleted");
    exit;
}

// Edit Mode Check
$edit_mode = false;
$edit_data = null;
if (isset($_GET['edit'])) {
    $edit_id = (int) $_GET['edit'];
    $res = $conn->query("SELECT * FROM client_reviews WHERE id=$edit_id");
    if ($res && $res->num_rows > 0) {
        $edit_mode = true;
        $edit_data = $res->fetch_assoc();
    }
}

// Add or Update Review
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sector_icon = $conn->real_escape_string($_POST['sector_icon']);
    $sector_name = $conn->real_escape_string($_POST['sector_name']);
    $quote_text = $conn->real_escape_string($_POST['quote_text']);
    $impact_text = $conn->real_escape_string($_POST['impact_text']);
    $author_monogram = $conn->real_escape_string($_POST['author_monogram']);
    $author_name = $conn->real_escape_string($_POST['author_name']);
    $author_company = $conn->real_escape_string($_POST['author_company']);

    if (isset($_POST['update_review'])) {
        $id = (int) $_POST['review_id'];
        $sql = "UPDATE client_reviews SET 
                sector_icon = '$sector_icon', 
                sector_name = '$sector_name', 
                quote_text = '$quote_text', 
                impact_text = '$impact_text', 
                author_monogram = '$author_monogram', 
                author_name = '$author_name', 
                author_company = '$author_company' 
                WHERE id = $id";
        if ($conn->query($sql)) {
            header("Location: manage_reviews.php?msg=updated");
            exit;
        } else {
            $msg = "<div class='alert alert-danger'>Failed to update review.</div>";
        }
    } elseif (isset($_POST['add_review'])) {
        $sql = "INSERT INTO client_reviews (sector_icon, sector_name, quote_text, impact_text, author_monogram, author_name, author_company, is_published) 
                VALUES ('$sector_icon', '$sector_name', '$quote_text', '$impact_text', '$author_monogram', '$author_name', '$author_company', 1)";
        if ($conn->query($sql)) {
            header("Location: manage_reviews.php?msg=added");
            exit;
        } else {
            $msg = "<div class='alert alert-danger'>Failed to add review.</div>";
        }
    }
}

$reviews = $conn->query("SELECT * FROM client_reviews ORDER BY display_order ASC, id DESC");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Reviews - IB Admin</title>

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
                <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                    <div>
                        <h2 style="color: var(--admin-navy); font-weight: 800; margin: 0; font-size: 26px;">Manage
                            Client Reviews</h2>
                        <p class="text-muted m-0" style="font-size: 14px;">Publish, edit, and curate client success
                            testimonials displayed on the homepage.</p>
                    </div>
                </div>

                <?php echo $msg; ?>

                <!-- Form Card -->
                <div class="card-custom mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="m-0" style="font-weight: 700; color: var(--admin-navy);">
                            <?php echo $edit_mode ? '<i class="fa-solid fa-pen-to-square text-primary me-2"></i>Edit Review' : '<i class="fa-solid fa-circle-plus text-gold me-2"></i>Add New Review'; ?>
                        </h5>
                        <?php if ($edit_mode): ?>
                            <a href="manage_reviews.php" class="btn btn-sm btn-outline-secondary">
                                <i class="fa-solid fa-xmark me-1"></i> Cancel Edit
                            </a>
                        <?php endif; ?>
                    </div>

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

                <!-- Reviews Table Card -->
                <div class="card-custom">
                    <h5 class="mb-3" style="font-weight: 700; color: var(--admin-navy);">Current Reviews</h5>
                    <div class="table-responsive">
                        <table class="table admin-table align-middle">
                            <thead style="background-color: #091a32; color: #fff;">
                                <tr>
                                    <th style="width: 170px;">Sector</th>
                                    <th>Review</th>
                                    <th style="width: 200px;">Author</th>
                                    <th style="width: 110px; text-align: center;">Status</th>
                                    <th style="width: 150px; text-align: center;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($reviews && $reviews->num_rows > 0):
                                    while ($row = $reviews->fetch_assoc()):
                                        $is_pub = isset($row['is_published']) ? (int) $row['is_published'] : 1;
                                        ?>
                                        <tr>
                                            <td>
                                                <i
                                                    class="<?php echo htmlspecialchars($row['sector_icon']); ?> me-1 text-gold"></i>
                                                <strong
                                                    style="color: var(--admin-navy);"><?php echo htmlspecialchars($row['sector_name']); ?></strong>
                                            </td>
                                            <td style="font-size: 13px; color: #475569;">
                                                <?php echo nl2br(htmlspecialchars($row['quote_text'])); ?>
                                            </td>
                                            <td>
                                                <strong
                                                    style="color: var(--admin-navy);"><?php echo htmlspecialchars($row['author_name']); ?></strong><br>
                                                <small
                                                    class="text-muted"><?php echo htmlspecialchars($row['author_company']); ?></small>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($is_pub == 1): ?>
                                                    <span class="badge-published">Published</span>
                                                <?php else: ?>
                                                    <span class="badge-hidden">Hidden</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <div class="action-btn-group justify-content-center">
                                                    <!-- Edit -->
                                                    <a href="manage_reviews.php?edit=<?php echo $row['id']; ?>"
                                                        class="btn-action btn-action-edit" title="Edit Review">
                                                        <i class="fa-solid fa-pen-to-square"></i>
                                                    </a>

                                                    <!-- Toggle Publish / Hide (Green) -->
                                                    <a href="manage_reviews.php?toggle_publish=<?php echo $is_pub; ?>&id=<?php echo $row['id']; ?>"
                                                        class="btn-action <?php echo ($is_pub == 1) ? 'btn-action-hide' : 'btn-action-publish'; ?>"
                                                        title="<?php echo ($is_pub == 1) ? 'Hide / Unpublish Review' : 'Show / Publish Review'; ?>">
                                                        <i
                                                            class="fa-solid <?php echo ($is_pub == 1) ? 'fa-eye-slash' : 'fa-eye'; ?>"></i>
                                                    </a>

                                                    <!-- Delete (Red) -->
                                                    <a href="manage_reviews.php?delete=<?php echo $row['id']; ?>"
                                                        class="btn-action btn-action-delete" title="Delete Review"
                                                        data-item-name="Review by <?php echo htmlspecialchars($row['author_name']); ?>">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">No reviews found in database.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
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