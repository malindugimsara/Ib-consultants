<?php
session_start();
include '../db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$msg = '';
if (isset($_GET['msg']) && $_GET['msg'] == 'updated') {
    $msg = "<div class='alert alert-success'>Program updated successfully!</div>";
}

// 1. Add New Program
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_program'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);
    $icon_class = $conn->real_escape_string($_POST['icon_class']);

    // Image Upload
    $target_dir = "../uploads/programs/";
    $image_name = time() . '_' . basename($_FILES["image"]["name"]);
    $target_file = $target_dir . $image_name;

    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        $image_path = "uploads/programs/" . $image_name;
        $sql = "INSERT INTO certificate_programs (title, description, image_path, icon_class) 
                VALUES ('$title', '$description', '$image_path', '$icon_class')";
        $conn->query($sql);
        $msg = "<div class='alert alert-success'>Program added successfully!</div>";
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
        $image_name = time() . '_' . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $image_name;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_path = "uploads/programs/" . $image_name;

            // Delete old image from server
            $old_img_res = $conn->query("SELECT image_path FROM certificate_programs WHERE id=$id");
            if ($row = $old_img_res->fetch_assoc()) {
                if (file_exists("../" . $row['image_path'])) {
                    unlink("../" . $row['image_path']);
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
    header("Location: manage_programs.php?msg=updated");
    exit;
}

// 4. Delete Program
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $res = $conn->query("SELECT image_path FROM certificate_programs WHERE id=$id");
    if ($row = $res->fetch_assoc()) {
        if (!empty($row['image_path']) && file_exists("../" . $row['image_path'])) {
            @unlink("../" . $row['image_path']);
        }
    }
    $conn->query("DELETE FROM certificate_programs WHERE id=$id");
    header("Location: manage_programs.php?msg=deleted");
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

// Fetch all programs
$programs = $conn->query("SELECT * FROM certificate_programs ORDER BY display_order ASC, id DESC");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Programs - IB Admin</title>

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
                <h2 class="mb-4" style="color: #091a32; font-weight: 700;">Manage Certificate Programs</h2>
                <?php echo $msg; ?>

                <!-- Add / Edit Form -->
                <div class="card-custom" style="<?php echo $edit_mode ? 'border: 2px solid #d4af37;' : ''; ?>">
                    <h5 class="mb-3"><?php echo $edit_mode ? 'Edit Program Details' : 'Add New Program'; ?></h5>

                    <form action="manage_programs.php" method="POST" enctype="multipart/form-data">
                        <?php if ($edit_mode): ?>
                            <input type="hidden" name="program_id" value="<?php echo $edit_data['id']; ?>">
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label>Program Image
                                    <?php echo $edit_mode ? '(Leave empty to keep current)' : ''; ?></label>
                                <input type="file" name="image" class="form-control" accept="image/*" <?php echo $edit_mode ? '' : 'required'; ?>>
                                <?php if ($edit_mode): ?>
                                    <img src="../<?php echo $edit_data['image_path']; ?>" class="mt-2 img-preview"
                                        style="height: 60px;">
                                <?php endif; ?>
                            </div>
                            <div class="col-md-5 mb-3">
                                <label>Program Title</label>
                                <input type="text" name="title" class="form-control"
                                    placeholder="E.g. Certificate in Business..."
                                    value="<?php echo $edit_mode ? htmlspecialchars($edit_data['title']) : ''; ?>"
                                    required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label>Default Icon</label>
                                <select name="icon_class" class="form-control">
                                    <option value="tji-service-4" <?php echo ($edit_mode && $edit_data['icon_class'] == 'tji-service-4') ? 'selected' : ''; ?>>Icon 1
                                        (Star/Ribbon)</option>
                                    <option value="tji-service-1" <?php echo ($edit_mode && $edit_data['icon_class'] == 'tji-service-1') ? 'selected' : ''; ?>>Icon 2 (Chart)
                                    </option>
                                    <option value="tji-service-3" <?php echo ($edit_mode && $edit_data['icon_class'] == 'tji-service-3') ? 'selected' : ''; ?>>Icon 3
                                        (Settings)</option>
                                </select>
                            </div>
                            <div class="col-md-10 mb-3">
                                <label>Short Description</label>
                                <textarea name="description" class="form-control" rows="2"
                                    placeholder="Write a short description..."
                                    required><?php echo $edit_mode ? htmlspecialchars($edit_data['description']) : ''; ?></textarea>
                            </div>
                            <div class="col-md-2 mb-3 d-flex align-items-end">
                                <?php if ($edit_mode): ?>
                                    <button type="submit" name="update_program"
                                        class="btn btn-custom w-100 p-2 mb-1">Update</button>
                                    <a href="manage_programs.php" class="btn btn-secondary w-100 p-2 ms-2 mb-1">Cancel</a>
                                <?php else: ?>
                                    <button type="submit" name="add_program" class="btn btn-custom w-100 p-2">Add
                                        Program</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- List Programs -->
                <div class="card-custom">
                    <h5 class="mb-3">Current Programs</h5>
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
                                <?php if ($programs->num_rows > 0):
                                    while ($row = $programs->fetch_assoc()): ?>
                                        <tr>
                                            <td><img src="../<?php echo $row['image_path']; ?>" class="img-preview"
                                                    style="width: 110px; height: 70px; object-fit: cover; border-radius: 8px;"></td>
                                            <td>
                                                <strong style="color: var(--admin-navy); font-size: 15px;"><?php echo htmlspecialchars($row['title']); ?></strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($row['description']); ?></small>
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
                                                        <i class="fa-solid <?php echo ($row['is_published'] == 1) ? 'fa-eye-slash' : 'fa-eye'; ?>"></i>
                                                    </a>

                                                    <!-- Delete Button -->
                                                    <a href="manage_programs.php?delete=<?php echo $row['id']; ?>"
                                                        class="btn-action btn-action-delete"
                                                        title="Delete Program"
                                                        data-item-name="<?php echo htmlspecialchars($row['title']); ?>">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">No programs found in database.</td>
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