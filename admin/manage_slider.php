<?php
session_start();
include '../db.php';

// Check admin authentication
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$msg = '';

// Toggle Publish / Hide Status
if (isset($_GET['toggle_publish']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $status = (int)$_GET['toggle_publish'];
    $new_status = ($status == 1) ? 0 : 1;
    $conn->query("UPDATE hero_slider SET is_published = $new_status WHERE id = $id");
    header("Location: manage_slider.php?msg=updated");
    exit;
}

// Delete Slide
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $res = $conn->query("SELECT image_path FROM hero_slider WHERE id = $id");
    if ($res && $row = $res->fetch_assoc()) {
        if (!empty($row['image_path']) && file_exists("../" . $row['image_path'])) {
            @unlink("../" . $row['image_path']);
        }
    }
    $conn->query("DELETE FROM hero_slider WHERE id = $id");
    header("Location: manage_slider.php?msg=deleted");
    exit;
}

// Edit Mode Check
$edit_mode = false;
$edit_data = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $res = $conn->query("SELECT * FROM hero_slider WHERE id = $edit_id");
    if ($res && $res->num_rows > 0) {
        $edit_mode = true;
        $edit_data = $res->fetch_assoc();
    }
}

// Add or Update Slide
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);
    $target_dir = "../uploads/slider/";

    if (!file_exists($target_dir)) {
        @mkdir($target_dir, 0777, true);
    }

    if (isset($_POST['update_slide'])) {
        $id = (int)$_POST['slide_id'];
        $image_sql_part = "";

        if (isset($_FILES["slide_image"]) && $_FILES["slide_image"]["error"] == 0) {
            $image_name = time() . '_' . basename($_FILES["slide_image"]["name"]);
            $target_file = $target_dir . $image_name;

            if (move_uploaded_file($_FILES["slide_image"]["tmp_name"], $target_file)) {
                // Remove old image
                if (!empty($edit_data['image_path']) && file_exists("../" . $edit_data['image_path'])) {
                    @unlink("../" . $edit_data['image_path']);
                }
                $new_image_path = "uploads/slider/" . $image_name;
                $image_sql_part = ", image_path = '$new_image_path'";
            }
        }

        $sql = "UPDATE hero_slider SET title = '$title', description = '$description' $image_sql_part WHERE id = $id";
        if ($conn->query($sql)) {
            header("Location: manage_slider.php?msg=updated");
            exit;
        } else {
            $msg = "<div class='alert alert-danger'>Failed to update slide.</div>";
        }
    } elseif (isset($_POST['add_slide'])) {
        if (isset($_FILES["slide_image"]) && $_FILES["slide_image"]["error"] == 0) {
            $image_name = time() . '_' . basename($_FILES["slide_image"]["name"]);
            $target_file = $target_dir . $image_name;

            if (move_uploaded_file($_FILES["slide_image"]["tmp_name"], $target_file)) {
                $image_path = "uploads/slider/" . $image_name;
                $sql = "INSERT INTO hero_slider (image_path, title, description, is_published) VALUES ('$image_path', '$title', '$description', 1)";
                if ($conn->query($sql)) {
                    header("Location: manage_slider.php?msg=added");
                    exit;
                } else {
                    $msg = "<div class='alert alert-danger'>Database error: Could not save slide.</div>";
                }
            } else {
                $msg = "<div class='alert alert-danger'>Failed to upload image. Please check file permissions.</div>";
            }
        } else {
            $msg = "<div class='alert alert-danger'>Please select a slide image to upload.</div>";
        }
    }
}

// Fetch all slides
$slides = $conn->query("SELECT * FROM hero_slider ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Slider - IB Consultants</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- CSS Dependencies -->
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/font-awesome-pro.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="admin.css">
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
                        <h2 style="color: var(--admin-navy); font-weight: 800; margin: 0; font-size: 26px;">Manage Hero Slider</h2>
                        <p class="text-muted m-0" style="font-size: 14px;">Upload and control the promotional slider graphics shown on your main landing page.</p>
                    </div>
                </div>

                <?php echo $msg; ?>

                <!-- Slide Form Card (Add / Edit) -->
                <div class="card-custom mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="m-0" style="font-weight: 700; color: var(--admin-navy);">
                            <?php echo $edit_mode ? '<i class="fa-solid fa-pen-to-square text-primary me-2"></i>Edit Slide' : '<i class="fa-solid fa-circle-plus text-gold me-2"></i>Add New Slide'; ?>
                        </h5>
                        <?php if ($edit_mode): ?>
                            <a href="manage_slider.php" class="btn btn-sm btn-outline-secondary">
                                <i class="fa-solid fa-xmark me-1"></i> Cancel Edit
                            </a>
                        <?php endif; ?>
                    </div>

                    <form action="" method="POST" enctype="multipart/form-data">
                        <?php if ($edit_mode): ?>
                            <input type="hidden" name="slide_id" value="<?php echo $edit_data['id']; ?>">
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label" style="font-weight: 600; font-size: 13px;">Slider Image (1920x1080px recommended)</label>
                                <input type="file" name="slide_image" class="form-control" accept="image/*" <?php echo $edit_mode ? '' : 'required'; ?>>
                                <?php if ($edit_mode && !empty($edit_data['image_path'])): ?>
                                    <div class="mt-2 d-flex align-items-center gap-2">
                                        <img src="../<?php echo $edit_data['image_path']; ?>" style="width: 80px; height: 45px; object-fit: cover; border-radius: 6px; border: 1px solid #e2e8f0;">
                                        <small class="text-muted">Current image (leave blank to keep unchanged)</small>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-8 mb-3">
                                <label class="form-label" style="font-weight: 600; font-size: 13px;">Main Title</label>
                                <input type="text" name="title" class="form-control"
                                    placeholder="E.g. Expertise For Your Growth." 
                                    value="<?php echo htmlspecialchars($edit_data['title'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label" style="font-weight: 600; font-size: 13px;">Short Description</label>
                                <textarea name="description" class="form-control" rows="2"
                                    placeholder="E.g. Practical Business & Profit Improvement Solutions..." required><?php echo htmlspecialchars($edit_data['description'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <?php if ($edit_mode): ?>
                                <button type="submit" name="update_slide" class="btn btn-primary px-4">
                                    <i class="fa-solid fa-floppy-disk me-1"></i> Update Slide
                                </button>
                                <a href="manage_slider.php" class="btn btn-outline-secondary px-4">Cancel</a>
                            <?php else: ?>
                                <button type="submit" name="add_slide" class="btn btn-custom px-4">
                                    <i class="fa-solid fa-plus me-1"></i> Add Slide
                                </button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <!-- Display Existing Slides -->
                <div class="card-custom">
                    <h5 class="mb-3" style="font-weight: 700; color: var(--admin-navy);">Current Slides</h5>
                    <div class="table-responsive">
                        <table class="table admin-table align-middle">
                            <thead style="background-color: #091a32; color: #fff;">
                                <tr>
                                    <th style="width: 140px;">Slide Image</th>
                                    <th style="width: 250px;">Title</th>
                                    <th>Description</th>
                                    <th style="width: 110px; text-align: center;">Status</th>
                                    <th style="width: 150px; text-align: center;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($slides && $slides->num_rows > 0): ?>
                                    <?php while ($row = $slides->fetch_assoc()): 
                                        $is_pub = isset($row['is_published']) ? (int)$row['is_published'] : 1;
                                    ?>
                                        <tr>
                                            <td>
                                                <img src="../<?php echo $row['image_path']; ?>" class="img-preview" style="width: 120px; height: 68px; object-fit: cover; border-radius: 8px; border: 1px solid #e2e8f0;">
                                            </td>
                                            <td><strong style="color: var(--admin-navy);"><?php echo htmlspecialchars($row['title']); ?></strong></td>
                                            <td style="font-size: 13px; color: #475569;"><?php echo htmlspecialchars($row['description']); ?></td>
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
                                                    <a href="manage_slider.php?edit=<?php echo $row['id']; ?>"
                                                        class="btn-action btn-action-edit"
                                                        title="Edit Slide">
                                                        <i class="fa-solid fa-pen-to-square"></i>
                                                    </a>

                                                    <!-- Toggle Publish / Hide (Green) -->
                                                    <a href="manage_slider.php?toggle_publish=<?php echo $is_pub; ?>&id=<?php echo $row['id']; ?>"
                                                        class="btn-action <?php echo ($is_pub == 1) ? 'btn-action-hide' : 'btn-action-publish'; ?>"
                                                        title="<?php echo ($is_pub == 1) ? 'Hide / Unpublish Slide' : 'Show / Publish Slide'; ?>">
                                                        <i class="fa-solid <?php echo ($is_pub == 1) ? 'fa-eye-slash' : 'fa-eye'; ?>"></i>
                                                    </a>

                                                    <!-- Delete (Red) -->
                                                    <a href="manage_slider.php?delete=<?php echo $row['id']; ?>"
                                                        class="btn-action btn-action-delete"
                                                        title="Delete Slide"
                                                        data-item-name="<?php echo htmlspecialchars($row['title']); ?>">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">No slides found in database. Add a new slide above!</td>
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