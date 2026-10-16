<?php
session_start();
include '../db.php';

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

                    <form action="" method="POST">
                        <?php if ($edit_mode): ?>
                            <input type="hidden" name="review_id" value="<?php echo $edit_data['id']; ?>">
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label" style="font-weight: 600; font-size: 13px;">Sector Icon
                                    Class</label>
                                <input type="text" name="sector_icon" class="form-control"
                                    placeholder="e.g. fa-solid fa-industry"
                                    value="<?php echo htmlspecialchars($edit_data['sector_icon'] ?? 'fa-solid fa-industry'); ?>"
                                    required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label" style="font-weight: 600; font-size: 13px;">Sector Name</label>
                                <input type="text" name="sector_name" class="form-control"
                                    placeholder="e.g. Manufacturing SME"
                                    value="<?php echo htmlspecialchars($edit_data['sector_name'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label class="form-label" style="font-weight: 600; font-size: 13px;">Author
                                    Monogram</label>
                                <input type="text" name="author_monogram" class="form-control" placeholder="e.g. MD"
                                    value="<?php echo htmlspecialchars($edit_data['author_monogram'] ?? ''); ?>"
                                    required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label" style="font-weight: 600; font-size: 13px;">Author Title /
                                    Name</label>
                                <input type="text" name="author_name" class="form-control"
                                    placeholder="e.g. Managing Director"
                                    value="<?php echo htmlspecialchars($edit_data['author_name'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" style="font-weight: 600; font-size: 13px;">Company
                                    Details</label>
                                <input type="text" name="author_company" class="form-control"
                                    placeholder="e.g. Leading SME · Western Province"
                                    value="<?php echo htmlspecialchars($edit_data['author_company'] ?? ''); ?>"
                                    required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" style="font-weight: 600; font-size: 13px;">Key Impact</label>
                                <input type="text" name="impact_text" class="form-control"
                                    placeholder="e.g. Plant Operations Streamlined..."
                                    value="<?php echo htmlspecialchars($edit_data['impact_text'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label" style="font-weight: 600; font-size: 13px;">Review /
                                    Quote</label>
                                <textarea name="quote_text" class="form-control" rows="3"
                                    placeholder="Type the client's review here..."
                                    required><?php echo htmlspecialchars($edit_data['quote_text'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <?php if ($edit_mode): ?>
                                <button type="submit" name="update_review" class="btn btn-primary px-4">
                                    <i class="fa-solid fa-floppy-disk me-1"></i> Update Review
                                </button>
                                <a href="manage_reviews.php" class="btn btn-outline-secondary px-4">Cancel</a>
                            <?php else: ?>
                                <button type="submit" name="add_review" class="btn btn-custom px-4">
                                    <i class="fa-solid fa-plus me-1"></i> Add Review
                                </button>
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
                                                <?php echo nl2br(htmlspecialchars($row['quote_text'])); ?></td>
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