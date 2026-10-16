<?php
session_start();
include '../db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$msg = '';
if (isset($_GET['msg']) && $_GET['msg'] == 'updated') {
    $msg = "<div class='alert alert-success'>Diploma updated successfully!</div>";
}

// Add New Diploma
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_diploma'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $duration = $conn->real_escape_string($_POST['duration']);

    $sql = "INSERT INTO diploma_programs (title, duration) VALUES ('$title', '$duration')";
    if ($conn->query($sql)) {
        $msg = "<div class='alert alert-success'>Diploma added successfully!</div>";
    } else {
        $msg = "<div class='alert alert-danger'>Failed to add diploma.</div>";
    }
}

// Edit / Update Diploma
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_diploma'])) {
    $id = (int) $_POST['diploma_id'];
    $title = $conn->real_escape_string($_POST['title']);
    $duration = $conn->real_escape_string($_POST['duration']);

    $conn->query("UPDATE diploma_programs SET title='$title', duration='$duration' WHERE id=$id");
    header("Location: manage_diplomas.php?msg=updated");
    exit;
}

// Publish / Unpublish Toggle
if (isset($_GET['toggle_publish']) && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $status = (int) $_GET['toggle_publish'];
    $new_status = ($status == 1) ? 0 : 1;
    $conn->query("UPDATE diploma_programs SET is_published = $new_status WHERE id=$id");
    header("Location: manage_diplomas.php?msg=updated");
    exit;
}

// Delete Diploma
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $conn->query("DELETE FROM diploma_programs WHERE id=$id");
    header("Location: manage_diplomas.php?msg=deleted");
    exit;
}

// Edit Mode Check
$edit_mode = false;
$edit_data = null;
if (isset($_GET['edit'])) {
    $edit_mode = true;
    $edit_id = (int) $_GET['edit'];
    $edit_res = $conn->query("SELECT * FROM diploma_programs WHERE id=$edit_id");
    $edit_data = $edit_res->fetch_assoc();
}

$diplomas = $conn->query("SELECT * FROM diploma_programs ORDER BY display_order ASC, id DESC");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Diplomas - IB Admin</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- CSS Dependencies -->
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/font-awesome-pro.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="admin.css"> <!-- Common Admin CSS -->
    <style>
        .badge-published {
            background-color: #198754;
            color: #fff;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
        }

        .badge-hidden {
            background-color: #6c757d;
            color: #fff;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
        }
    </style>

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
                <h2 class="mb-4" style="color: #091a32; font-weight: 700;">Manage Advanced Diplomas</h2>
                <?php echo $msg; ?>

                <!-- Add / Edit Form -->
                <div class="card-custom" style="<?php echo $edit_mode ? 'border: 2px solid #d4af37;' : ''; ?>">
                    <h5 class="mb-3"><?php echo $edit_mode ? 'Edit Diploma Details' : 'Add New Diploma'; ?></h5>
                    <form action="manage_diplomas.php" method="POST">
                        <?php if ($edit_mode): ?>
                            <input type="hidden" name="diploma_id" value="<?php echo $edit_data['id']; ?>">
                        <?php endif; ?>
                        <div class="row">
                            <div class="col-md-7 mb-3">
                                <label>Diploma Title (Use &lt;br&gt; for line breaks)</label>
                                <input type="text" name="title" class="form-control"
                                    placeholder="E.g. Diploma in Business Management"
                                    value="<?php echo $edit_mode ? htmlspecialchars($edit_data['title']) : ''; ?>"
                                    required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label>Duration</label>
                                <input type="text" name="duration" class="form-control"
                                    value="<?php echo $edit_mode ? htmlspecialchars($edit_data['duration']) : '3 - 6 Months'; ?>"
                                    required>
                            </div>
                            <div class="col-md-2 mb-3 d-flex align-items-end">
                                <?php if ($edit_mode): ?>
                                    <button type="submit" name="update_diploma"
                                        class="btn btn-custom w-100 p-2 mb-1">Update</button>
                                    <a href="manage_diplomas.php" class="btn btn-secondary w-100 p-2 ms-2 mb-1">Cancel</a>
                                <?php else: ?>
                                    <button type="submit" name="add_diploma" class="btn btn-custom w-100 p-2">Add</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- List Diplomas -->
                <div class="card-custom">
                    <h5 class="mb-3">Current Diplomas</h5>
                    <div class="table-responsive">
                        <table class="table admin-table align-middle">
                            <thead style="background-color: #091a32; color: #fff;">
                                <tr>
                                    <th style="width: 60px; text-align: center;">No.</th>
                                    <th>Title</th>
                                    <th style="width: 160px;">Duration</th>
                                    <th style="width: 110px; text-align: center;">Status</th>
                                    <th style="width: 140px; text-align: center;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($diplomas->num_rows > 0):
                                    $count = 1;
                                    while ($row = $diplomas->fetch_assoc()):
                                        ?>
                                        <tr>
                                            <td class="text-center" style="font-weight: 700; color: var(--admin-navy);">
                                                <?php echo str_pad($count, 2, '0', STR_PAD_LEFT) . '.'; ?></td>
                                            <td><strong style="color: var(--admin-navy);"><?php echo $row['title']; ?></strong>
                                            </td>
                                            <td><span
                                                    class="badge bg-light text-dark border"><?php echo htmlspecialchars($row['duration']); ?></span>
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
                                                    <!-- Edit -->
                                                    <a href="manage_diplomas.php?edit=<?php echo $row['id']; ?>"
                                                        class="btn-action btn-action-edit" title="Edit Diploma">
                                                        <i class="fa-solid fa-pen-to-square"></i>
                                                    </a>

                                                    <!-- Toggle Publish/Hide -->
                                                    <a href="manage_diplomas.php?toggle_publish=<?php echo $row['is_published']; ?>&id=<?php echo $row['id']; ?>"
                                                        class="btn-action <?php echo ($row['is_published'] == 1) ? 'btn-action-hide' : 'btn-action-publish'; ?>"
                                                        title="<?php echo ($row['is_published'] == 1) ? 'Hide / Unpublish Diploma' : 'Show / Publish Diploma'; ?>">
                                                        <i
                                                            class="fa-solid <?php echo ($row['is_published'] == 1) ? 'fa-eye-slash' : 'fa-eye'; ?>"></i>
                                                    </a>

                                                    <!-- Delete -->
                                                    <a href="manage_diplomas.php?delete=<?php echo $row['id']; ?>"
                                                        class="btn-action btn-action-delete" title="Delete Diploma"
                                                        data-item-name="<?php echo htmlspecialchars(strip_tags($row['title'])); ?>">
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
                                        <td colspan="5" class="text-center py-4 text-muted">No diplomas found in database.
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