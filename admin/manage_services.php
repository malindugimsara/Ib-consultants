<?php
session_start();
include '../db.php'; 

if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true){
    header("Location: login.php");
    exit;
}

$msg = '';
if(isset($_GET['msg']) && $_GET['msg'] == 'updated'){
    $msg = "<div class='alert alert-success'>Service updated successfully!</div>";
}

// Add New Service
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_service'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $page_link = $conn->real_escape_string($_POST['page_link']);
    $icon_class = $conn->real_escape_string($_POST['icon_class']);
    $description = $conn->real_escape_string($_POST['description']);
    $list_items = $conn->real_escape_string($_POST['list_items']);
    
    $sql = "INSERT INTO core_services (title, page_link, icon_class, description, list_items) 
            VALUES ('$title', '$page_link', '$icon_class', '$description', '$list_items')";
    if($conn->query($sql)){
        $msg = "<div class='alert alert-success'>Service added successfully!</div>";
    } else {
        $msg = "<div class='alert alert-danger'>Failed to add service.</div>";
    }
}

// Edit / Update Service
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_service'])) {
    $id = (int)$_POST['service_id'];
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
    $id = (int)$_GET['id'];
    $status = (int)$_GET['toggle_publish'];
    $new_status = ($status == 1) ? 0 : 1;
    $conn->query("UPDATE core_services SET is_published = $new_status WHERE id=$id");
    header("Location: manage_services.php?msg=updated");
    exit;
}

// Delete Service
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM core_services WHERE id=$id");
    header("Location: manage_services.php?msg=deleted");
    exit;
}

// Edit Mode Check
$edit_mode = false;
$edit_data = null;
if(isset($_GET['edit'])){
    $edit_mode = true;
    $edit_id = (int)$_GET['edit'];
    $edit_res = $conn->query("SELECT * FROM core_services WHERE id=$edit_id");
    $edit_data = $edit_res->fetch_assoc();
}

$services = $conn->query("SELECT * FROM core_services ORDER BY display_order ASC, id ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Core Services - IB Admin</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- CSS Dependencies -->
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/font-awesome-pro.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="admin.css">
    <style>
        .badge-published { background-color: #198754; color: #fff; padding: 5px 10px; border-radius: 4px; font-size: 12px;}
        .badge-hidden { background-color: #6c757d; color: #fff; padding: 5px 10px; border-radius: 4px; font-size: 12px;}
    </style>
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

            <!-- Add / Edit Form -->
            <div class="card-custom" style="<?php echo $edit_mode ? 'border: 2px solid #d4af37;' : ''; ?>">
                <h5 class="mb-3"><?php echo $edit_mode ? 'Edit Service Details' : 'Add New Service'; ?></h5>
                <form action="manage_services.php" method="POST">
                    <?php if($edit_mode): ?>
                        <input type="hidden" name="service_id" value="<?php echo $edit_data['id']; ?>">
                    <?php endif; ?>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Service Title (Do not add numbers like 01., it will auto-generate)</label>
                            <input type="text" name="title" class="form-control" placeholder="E.g. Business Health Check" value="<?php echo $edit_mode ? htmlspecialchars($edit_data['title']) : ''; ?>" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label>Page Link</label>
                            <input type="text" name="page_link" class="form-control" placeholder="e.g. business-health-check.php" value="<?php echo $edit_mode ? htmlspecialchars($edit_data['page_link']) : ''; ?>" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label>Icon Class</label>
                            <select name="icon_class" class="form-control">
                                <option value="tji-service-1" <?php echo ($edit_mode && $edit_data['icon_class'] == 'tji-service-1') ? 'selected' : ''; ?>>Chart Icon</option>
                                <option value="tji-service-2" <?php echo ($edit_mode && $edit_data['icon_class'] == 'tji-service-2') ? 'selected' : ''; ?>>Globe/Network Icon</option>
                                <option value="tji-service-3" <?php echo ($edit_mode && $edit_data['icon_class'] == 'tji-service-3') ? 'selected' : ''; ?>>Settings/Gear Icon</option>
                                <option value="tji-service-4" <?php echo ($edit_mode && $edit_data['icon_class'] == 'tji-service-4') ? 'selected' : ''; ?>>Ribbon/Star Icon</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="4" placeholder="Brief description of the service..." required><?php echo $edit_mode ? htmlspecialchars($edit_data['description']) : ''; ?></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>List Items (Enter one item per line)</label>
                            <textarea name="list_items" class="form-control" rows="4" placeholder="Item 1&#10;Item 2&#10;Item 3" required><?php echo $edit_mode ? htmlspecialchars($edit_data['list_items']) : ''; ?></textarea>
                        </div>
                        <div class="col-md-12 text-end">
                            <?php if($edit_mode): ?>
                                <a href="manage_services.php" class="btn btn-secondary px-4 me-2">Cancel</a>
                                <button type="submit" name="update_service" class="btn btn-custom px-4">Update Service</button>
                            <?php else: ?>
                                <button type="submit" name="add_service" class="btn btn-custom px-4">Add Service</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>

            <!-- List Services -->
            <div class="card-custom">
                <h5 class="mb-3">Current Services</h5>
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
                            if($services->num_rows > 0): 
                                $count = 1;
                                while($row = $services->fetch_assoc()): 
                                    $formatted_list = nl2br(htmlspecialchars($row['list_items']));
                            ?>
                                <tr>
                                    <td class="text-center" style="font-weight: 700; color: var(--admin-navy);"><?php echo str_pad($count, 2, '0', STR_PAD_LEFT) . '.'; ?></td>
                                    <td>
                                        <strong style="color: var(--admin-navy); font-size: 15px;"><?php echo htmlspecialchars($row['title']); ?></strong><br>
                                        <a href="../<?php echo htmlspecialchars($row['page_link']); ?>" target="_blank" class="text-decoration-none" style="font-size: 12px; color: #0284c7;">
                                            <i class="fa-solid fa-arrow-up-right-from-square me-1"></i><?php echo htmlspecialchars($row['page_link']); ?>
                                        </a>
                                    </td>
                                    <td style="font-size: 13px; color: #475569;"><?php echo $formatted_list; ?></td>
                                    <td class="text-center">
                                        <?php if($row['is_published'] == 1): ?>
                                            <span class="badge-published">Published</span>
                                        <?php else: ?>
                                            <span class="badge-hidden">Hidden</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="action-btn-group justify-content-center">
                                            <!-- Edit -->
                                            <a href="manage_services.php?edit=<?php echo $row['id']; ?>" class="btn-action btn-action-edit" title="Edit Service">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>

                                            <!-- Toggle Publish/Hide -->
                                            <a href="manage_services.php?toggle_publish=<?php echo $row['is_published']; ?>&id=<?php echo $row['id']; ?>" 
                                               class="btn-action <?php echo ($row['is_published']==1) ? 'btn-action-hide' : 'btn-action-publish'; ?>"
                                               title="<?php echo ($row['is_published']==1) ? 'Hide / Unpublish Service' : 'Show / Publish Service'; ?>">
                                                <i class="fa-solid <?php echo ($row['is_published']==1) ? 'fa-eye-slash' : 'fa-eye'; ?>"></i>
                                            </a>

                                            <!-- Delete -->
                                            <a href="manage_services.php?delete=<?php echo $row['id']; ?>" 
                                               class="btn-action btn-action-delete" 
                                               title="Delete Service"
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
                                <tr><td colspan="5" class="text-center py-4 text-muted">No services found in database.</td></tr>
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