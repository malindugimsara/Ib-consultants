<?php
session_start();
include '../db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$msg = '';
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'added') $msg = "<div class='alert alert-success'>Article published successfully!</div>";
    if ($_GET['msg'] == 'updated') $msg = "<div class='alert alert-success'>Article updated successfully!</div>";
    if ($_GET['msg'] == 'trashed') $msg = "<div class='alert alert-warning'>Article moved to Trash! You can restore it anytime from the Trash tab.</div>";
    if ($_GET['msg'] == 'restored') $msg = "<div class='alert alert-success'>Article restored successfully to active list!</div>";
    if ($_GET['msg'] == 'deleted_permanent') $msg = "<div class='alert alert-danger'>Article permanently deleted!</div>";
}

// Toggle Publish / Hide Status
if (isset($_GET['toggle_publish']) && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $status = (int) $_GET['toggle_publish'];
    $new_status = ($status == 1) ? 0 : 1;
    $conn->query("UPDATE blog_posts SET is_published = $new_status WHERE id = $id");

    if ((!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || isset($_GET['ajax'])) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'new_status' => $new_status]);
        exit;
    }

    header("Location: manage_blog.php?msg=updated");
    exit;
}

// Soft Delete (Move to Trash)
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $conn->query("UPDATE blog_posts SET deleted_at = NOW() WHERE id = $id");
    header("Location: manage_blog.php?msg=trashed");
    exit;
}

// Restore from Trash
if (isset($_GET['restore'])) {
    $id = (int) $_GET['restore'];
    $conn->query("UPDATE blog_posts SET deleted_at = NULL WHERE id = $id");
    header("Location: manage_blog.php?view=trash&msg=restored");
    exit;
}

// Force / Permanent Delete
if (isset($_GET['force_delete'])) {
    $id = (int) $_GET['force_delete'];
    $res = $conn->query("SELECT image_path FROM blog_posts WHERE id = $id");
    if ($res && $row = $res->fetch_assoc()) {
        if (!empty($row['image_path']) && file_exists("../" . $row['image_path'])) {
            @unlink("../" . $row['image_path']);
        }
    }
    $conn->query("DELETE FROM blog_posts WHERE id = $id");
    header("Location: manage_blog.php?view=trash&msg=deleted_permanent");
    exit;
}

// Edit Mode Check
$edit_mode = false;
$edit_data = null;
if (isset($_GET['edit'])) {
    $edit_id = (int) $_GET['edit'];
    $res = $conn->query("SELECT * FROM blog_posts WHERE id = $edit_id");
    if ($res && $res->num_rows > 0) {
        $edit_mode = true;
        $edit_data = $res->fetch_assoc();
    }
}

// Category mapping helper
$cat_names = [
    'manufacturing' => 'Manufacturing Excellence',
    'profit' => 'Profit & Cost Control',
    'export' => 'Export & Global Trade',
    'strategy' => 'SME Strategy & KPIs'
];

// Helper slugify function
function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return empty($text) ? 'article-' . time() : $text;
}

// Add or Update Article
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $conn->real_escape_string($_POST['title']);
    $category_slug = $conn->real_escape_string($_POST['category_slug']);
    $category_name = $cat_names[$category_slug] ?? 'Business Strategy';
    $read_time = $conn->real_escape_string($_POST['read_time']);
    $keywords = $conn->real_escape_string($_POST['keywords']);
    $excerpt = $conn->real_escape_string($_POST['excerpt']);
    
    // Slug processing
    $slug_raw = !empty($_POST['slug']) ? trim($_POST['slug']) : trim($_POST['title']);
    $slug = slugify($slug_raw);
    
    // Editor HTML content
    $content = $conn->real_escape_string($_POST['content']);
    
    $target_dir = "../uploads/blog/";

    if (!file_exists($target_dir)) {
        @mkdir($target_dir, 0777, true);
    }

    if (isset($_POST['update_article'])) {
        $id = (int) $_POST['article_id'];
        $image_sql_part = "";

        // Ensure unique slug
        $chk = $conn->query("SELECT id FROM blog_posts WHERE slug = '$slug' AND id != $id");
        if ($chk && $chk->num_rows > 0) {
            $slug .= '-' . $id;
        }

        if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
            $image_name = time() . '_' . basename($_FILES["image"]["name"]);
            $target_file = $target_dir . $image_name;

            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                // Delete old image
                if (!empty($edit_data['image_path']) && file_exists("../" . $edit_data['image_path'])) {
                    @unlink("../" . $edit_data['image_path']);
                }
                $new_image_path = "uploads/blog/" . $image_name;
                $image_sql_part = ", image_path = '$new_image_path'";
            }
        }

        $sql = "UPDATE blog_posts SET 
                title = '$title', 
                slug = '$slug',
                category_slug = '$category_slug', 
                category_name = '$category_name', 
                read_time = '$read_time', 
                keywords = '$keywords', 
                excerpt = '$excerpt', 
                content = '$content' 
                $image_sql_part 
                WHERE id = $id";

        if ($conn->query($sql)) {
            header("Location: manage_blog.php?msg=updated");
            exit;
        } else {
            $msg = "<div class='alert alert-danger'>Failed to update article.</div>";
        }
    } elseif (isset($_POST['add_article'])) {
        // Ensure unique slug
        $chk = $conn->query("SELECT id FROM blog_posts WHERE slug = '$slug'");
        if ($chk && $chk->num_rows > 0) {
            $slug .= '-' . time();
        }

        if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
            $image_name = time() . '_' . basename($_FILES["image"]["name"]);
            $target_file = $target_dir . $image_name;

            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $image_path = "uploads/blog/" . $image_name;
                $sql = "INSERT INTO blog_posts (title, slug, category_slug, category_name, read_time, excerpt, content, image_path, keywords, is_published) 
                        VALUES ('$title', '$slug', '$category_slug', '$category_name', '$read_time', '$excerpt', '$content', '$image_path', '$keywords', 1)";
                if ($conn->query($sql)) {
                    header("Location: manage_blog.php?msg=added");
                    exit;
                } else {
                    $msg = "<div class='alert alert-danger'>Database error: Failed to save article.</div>";
                }
            } else {
                $msg = "<div class='alert alert-danger'>Failed to upload cover image. Please check directory permissions.</div>";
            }
        } else {
            $msg = "<div class='alert alert-danger'>Please select a cover image for the article.</div>";
        }
    }
}

// Tab View Mode
$view = isset($_GET['view']) && $_GET['view'] === 'trash' ? 'trash' : 'active';

// Counts for tabs
$count_active = 0;
$c_res = $conn->query("SELECT COUNT(*) as total FROM blog_posts WHERE deleted_at IS NULL");
if ($c_res && $crow = $c_res->fetch_assoc()) {
    $count_active = (int) $crow['total'];
}

$count_trash = 0;
$t_res = $conn->query("SELECT COUNT(*) as total FROM blog_posts WHERE deleted_at IS NOT NULL");
if ($t_res && $trow = $t_res->fetch_assoc()) {
    $count_trash = (int) $trow['total'];
}

if ($view === 'trash') {
    $articles = $conn->query("SELECT * FROM blog_posts WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC");
} else {
    $articles = $conn->query("SELECT * FROM blog_posts WHERE deleted_at IS NULL ORDER BY id DESC");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Blog & Articles - IB Admin</title>

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
    
    <style>
        /* CKEditor හි උස සහ පෙනුම සකස් කිරීම */
        .ck-editor__editable_inline {
            min-height: 250px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 15px;
        }
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

                <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                    <div>
                        <h2
                            style="color: var(--admin-navy); font-weight: 800; margin: 0; font-size: 26px; letter-spacing: -0.5px;">
                            Manage Blog & Articles</h2>
                        <p class="text-muted m-0" style="font-size: 14px;">Publish, edit, and curate strategic insights
                            on the IB Consultants knowledge base.</p>
                    </div>
                    <a href="../blog.php" target="_blank" class="btn btn-outline-dark btn-sm">
                        <i class="fa-solid fa-globe me-1"></i> View Live Blog
                    </a>
                </div>

                <?php echo $msg; ?>

                <?php if ($view === 'active' || $edit_mode): ?>
                <!-- Article Form Card (Add / Edit) -->
                <div class="card-custom mb-4" id="form-section">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="m-0" style="font-weight: 700; color: var(--admin-navy);">
                            <?php echo $edit_mode ? '<i class="fa-solid fa-pen-to-square text-primary me-2"></i>Edit Article' : '<i class="fa-solid fa-pen-nib text-gold me-2"></i>Write New Article'; ?>
                        </h5>
                        <?php if ($edit_mode): ?>
                            <a href="manage_blog.php" class="btn btn-sm btn-outline-secondary">
                                <i class="fa-solid fa-xmark me-1"></i> Cancel Edit
                            </a>
                        <?php endif; ?>
                    </div>

                    <form action="manage_blog.php" method="POST" enctype="multipart/form-data">
                        <?php if ($edit_mode): ?>
                            <input type="hidden" name="article_id" value="<?php echo $edit_data['id']; ?>">
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" style="font-weight: 600; font-size: 13px;">Article
                                    Title *</label>
                                <input type="text" name="title" class="form-control"
                                    value="<?php echo htmlspecialchars($edit_data['title'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" style="font-weight: 600; font-size: 13px;">Custom SEO URL Slug (Optional)</label>
                                <input type="text" name="slug" class="form-control" placeholder="e.g. practical-ways-reduce-waste"
                                    value="<?php echo htmlspecialchars($edit_data['slug'] ?? ''); ?>">
                                <small class="text-muted">Auto-generated from title if left blank. Used as web address.</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" style="font-weight: 600; font-size: 13px;">Category</label>
                                <select name="category_slug" class="form-control" required>
                                    <?php
                                    $selected_cat = $edit_data['category_slug'] ?? 'manufacturing';
                                    foreach ($cat_names as $slug => $cname):
                                        ?>
                                        <option value="<?php echo $slug; ?>" <?php echo ($selected_cat === $slug) ? 'selected' : ''; ?>>
                                            <?php echo $cname; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label" style="font-weight: 600; font-size: 13px;">Read Time</label>
                                <input type="text" name="read_time" class="form-control" placeholder="E.g. 5 min read"
                                    value="<?php echo htmlspecialchars($edit_data['read_time'] ?? '5 min read'); ?>"
                                    required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label" style="font-weight: 600; font-size: 13px;">Cover Image
                                    (Horizontal)</label>
                                <input type="file" name="image" class="form-control" accept="image/*" <?php echo $edit_mode ? '' : 'required'; ?>>
                                <?php if ($edit_mode && !empty($edit_data['image_path'])): ?>
                                    <div class="mt-2 d-flex align-items-center gap-2">
                                        <img src="../<?php echo $edit_data['image_path']; ?>"
                                            style="width: 80px; height: 50px; object-fit: cover; border-radius: 6px; border: 1px solid #e2e8f0;">
                                        <small class="text-muted">Current cover (leave empty to keep unchanged)</small>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-8 mb-3">
                                <label class="form-label" style="font-weight: 600; font-size: 13px;">Search Keywords
                                    (For website search bar)</label>
                                <input type="text" name="keywords" class="form-control"
                                    placeholder="E.g. cost reduction, manufacturing, profit..."
                                    value="<?php echo htmlspecialchars($edit_data['keywords'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label" style="font-weight: 600; font-size: 13px;">Short Excerpt
                                    (Shows on cards)</label>
                                <textarea name="excerpt" class="form-control" rows="2"
                                    required><?php echo htmlspecialchars($edit_data['excerpt'] ?? ''); ?></textarea>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label" style="font-weight: 600; font-size: 13px;">Full Article
                                    Content (HTML tags supported)</label>
                                    
                                <!-- Editor එක දැමීමට ID එකක් ලබා දීම (මෙහි required ඉවත් කර ඇත) -->
                                <textarea name="content" id="article-editor" class="form-control"><?php echo htmlspecialchars($edit_data['content'] ?? ''); ?></textarea>
                            </div>
                            <div class="col-md-12">
                                <div class="d-flex gap-2">
                                    <?php if ($edit_mode): ?>
                                        <button type="submit" name="update_article" class="btn btn-primary px-4">
                                            <i class="fa-solid fa-floppy-disk me-1"></i> Update Article
                                        </button>
                                        <a href="manage_blog.php" class="btn btn-outline-secondary px-4">Cancel</a>
                                    <?php else: ?>
                                        <button type="submit" name="add_article" class="btn btn-custom px-5">
                                            <i class="fa-solid fa-paper-plane me-1"></i> Publish Article
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <?php endif; ?>

                <?php if ($edit_mode): ?>
                <script>document.getElementById('form-section').scrollIntoView({behavior: 'smooth'});</script>
                <?php endif; ?>

                <!-- List Articles Card -->
                <div class="card-custom">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                        <div class="admin-tab-nav">
                            <a href="manage_blog.php" class="admin-tab-nav-btn <?php echo $view === 'active' ? 'active' : ''; ?>">
                                <i class="fa-solid fa-list-check"></i> Active Articles <span class="tab-count-badge"><?php echo $count_active; ?></span>
                            </a>
                            <a href="manage_blog.php?view=trash" class="admin-tab-nav-btn tab-trash <?php echo $view === 'trash' ? 'active' : ''; ?>">
                                <i class="fa-solid fa-trash-can"></i> Trash <span class="tab-count-badge"><?php echo $count_trash; ?></span>
                            </a>
                        </div>
                        <?php if ($view === 'trash'): ?>
                            <a href="manage_blog.php" class="btn btn-sm btn-outline-secondary">
                                <i class="fa-solid fa-arrow-left me-1"></i> Back to Active Articles
                            </a>
                        <?php endif; ?>
                    </div>

                    <?php if ($view === 'trash'): ?>
                    <!-- Trash Articles Table -->
                    <div class="table-responsive">
                        <table class="table admin-table align-middle">
                            <thead style="background-color: #7f1d1d; color: #fff;">
                                <tr>
                                    <th style="width: 120px;">Image</th>
                                    <th>Title & Category</th>
                                    <th style="width: 190px; text-align: center;">Deleted On</th>
                                    <th style="width: 140px; text-align: center;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($articles && $articles->num_rows > 0):
                                    while ($row = $articles->fetch_assoc()):
                                        ?>
                                        <tr style="background: rgba(239, 68, 68, 0.02);">
                                            <td>
                                                <?php if (!empty($row['image_path'])): ?>
                                                    <img src="../<?php echo $row['image_path']; ?>" class="img-preview"
                                                        style="width: 100px; height: 60px; object-fit: cover; border-radius: 8px; border: 1px solid #e2e8f0; filter: grayscale(80%);">
                                                <?php else: ?>
                                                    <span class="text-muted small">No image</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong style="color: var(--admin-navy); font-size: 15px;"><?php echo htmlspecialchars($row['title']); ?></strong><br>
                                                <span class="text-muted" style="font-size: 12px;"><?php echo htmlspecialchars($row['category_name']); ?></span>
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
                                                    <a href="manage_blog.php?restore=<?php echo $row['id']; ?>"
                                                        class="btn-action btn-action-restore" title="Restore Article to Active List">
                                                        <i class="fa-solid fa-rotate-left"></i>
                                                    </a>
                                                    <!-- Force Delete -->
                                                    <a href="manage_blog.php?force_delete=<?php echo $row['id']; ?>"
                                                        class="btn-action btn-action-force-delete" title="Permanently Delete Article"
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
                    <!-- Active Articles Table -->
                    <div class="table-responsive">
                        <table class="table admin-table align-middle">
                            <thead style="background-color: #091a32; color: #fff;">
                                <tr>
                                    <th style="width: 120px;">Image</th>
                                    <th>Title & Category</th>
                                    <th style="width: 110px; text-align: center;">Status</th>
                                    <th style="width: 150px; text-align: center;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($articles && $articles->num_rows > 0):
                                    while ($row = $articles->fetch_assoc()):
                                        $is_pub = isset($row['is_published']) ? (int) $row['is_published'] : 1;
                                        ?>
                                        <tr>
                                            <td>
                                                <img src="../<?php echo $row['image_path']; ?>" class="img-preview"
                                                    style="width: 100px; height: 60px; object-fit: cover; border-radius: 8px; border: 1px solid #e2e8f0;">
                                            </td>
                                            <td>
                                                <strong
                                                    style="color: var(--admin-navy); font-size: 15px;"><?php echo htmlspecialchars($row['title']); ?></strong><br>
                                                <span class="text-muted"
                                                    style="font-size: 12px;"><?php echo htmlspecialchars($row['category_name']); ?>
                                                    | <?php echo date('d M Y', strtotime($row['created_at'])); ?></span>
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
                                                    <!-- View Live Article -->
                                                    <a href="../blog-details.php?slug=<?php echo urlencode(!empty($row['slug']) ? $row['slug'] : $row['id']); ?>"
                                                        target="_blank" class="btn-action btn-action-view" title="View Article on Live Website">
                                                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                                    </a>

                                                    <!-- Edit -->
                                                    <a href="manage_blog.php?edit=<?php echo $row['id']; ?>"
                                                        class="btn-action btn-action-edit" title="Edit Article">
                                                        <i class="fa-solid fa-pen-to-square"></i>
                                                    </a>

                                                    <!-- Toggle Publish/Hide -->
                                                    <a href="manage_blog.php?toggle_publish=<?php echo $is_pub; ?>&id=<?php echo $row['id']; ?>"
                                                        class="btn-action <?php echo ($is_pub == 1) ? 'btn-action-hide' : 'btn-action-publish'; ?>"
                                                        title="<?php echo ($is_pub == 1) ? 'Hide / Unpublish Article' : 'Show / Publish Article'; ?>">
                                                        <i
                                                            class="fa-solid <?php echo ($is_pub == 1) ? 'fa-eye-slash' : 'fa-eye'; ?>"></i>
                                                    </a>

                                                    <!-- Delete (Soft Delete) -->
                                                    <a href="manage_blog.php?delete=<?php echo $row['id']; ?>"
                                                        class="btn-action btn-action-delete" title="Move to Trash"
                                                        data-item-name="<?php echo htmlspecialchars($row['title']); ?>">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">No articles found in database.
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

    <!-- CKEditor 5 Script -->
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
    <script>
        // start the Editor
        ClassicEditor
            .create(document.querySelector('#article-editor'), {
                toolbar: [ 'heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', '|', 'undo', 'redo' ]
            })
            .catch(error => {
                console.error(error);
            });
    </script>

</body>

</html>