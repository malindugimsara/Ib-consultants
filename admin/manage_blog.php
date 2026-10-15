<?php
session_start();
include '../db.php'; 

if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true){
    header("Location: login.php");
    exit;
}

$msg = '';
if(isset($_GET['msg']) && $_GET['msg'] == 'updated'){
    $msg = "<div class='alert alert-success'>Article updated successfully!</div>";
}

// Add New Article
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_article'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $category_slug = $conn->real_escape_string($_POST['category_slug']);
    
    // Set category name based on slug
    $cat_names = [
        'manufacturing' => 'Manufacturing Excellence',
        'profit' => 'Profit & Cost Control',
        'export' => 'Export & Global Trade',
        'strategy' => 'SME Strategy & KPIs'
    ];
    $category_name = $cat_names[$category_slug];
    
    $read_time = $conn->real_escape_string($_POST['read_time']);
    $keywords = $conn->real_escape_string($_POST['keywords']);
    $excerpt = $conn->real_escape_string($_POST['excerpt']);
    $content = $conn->real_escape_string($_POST['content']);
    
    $target_dir = "../uploads/blog/";
    $image_name = time() . '_' . basename($_FILES["image"]["name"]);
    $target_file = $target_dir . $image_name;
    
    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        $image_path = "uploads/blog/" . $image_name;
        $sql = "INSERT INTO blog_posts (title, category_slug, category_name, read_time, excerpt, content, image_path, keywords) 
                VALUES ('$title', '$category_slug', '$category_name', '$read_time', '$excerpt', '$content', '$image_path', '$keywords')";
        $conn->query($sql);
        $msg = "<div class='alert alert-success'>Article added successfully!</div>";
    } else {
        $msg = "<div class='alert alert-danger'>Failed to upload image.</div>";
    }
}

// Delete Article
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $res = $conn->query("SELECT image_path FROM blog_posts WHERE id=$id");
    if($row = $res->fetch_assoc()){
        if(file_exists("../" . $row['image_path'])){
            unlink("../" . $row['image_path']);
        }
    }
    $conn->query("DELETE FROM blog_posts WHERE id=$id");
    header("Location: manage_blog.php");
}

// Toggle Publish
if (isset($_GET['toggle_publish']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $status = (int)$_GET['toggle_publish'];
    $new_status = ($status == 1) ? 0 : 1;
    $conn->query("UPDATE blog_posts SET is_published = $new_status WHERE id=$id");
    header("Location: manage_blog.php");
}

$articles = $conn->query("SELECT * FROM blog_posts ORDER BY id DESC");
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
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- CSS Dependencies -->
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/font-awesome-pro.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="admin.css">
    <style>
        .badge-published { background-color: #198754; color: #fff; padding: 5px 10px; border-radius: 4px; font-size: 12px;}
        .badge-hidden { background-color: #6c757d; color: #fff; padding: 5px 10px; border-radius: 4px; font-size: 12px;}
        .img-preview { object-fit: cover; border-radius: 6px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); }
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
                    <h2 style="color: var(--admin-navy); font-weight: 800; margin: 0; font-size: 26px; letter-spacing: -0.5px;">Manage Blog & Articles</h2>
                    <p class="text-muted m-0" style="font-size: 14px;">Publish, manage, and feature expert articles on the IB Consultants knowledge base.</p>
                </div>
                <a href="../blog.php" target="_blank" class="btn btn-outline-dark btn-sm">
                    <i class="fa-solid fa-globe me-1"></i> View Live Blog
                </a>
            </div>

            <?php echo $msg; ?>

            <!-- Add Form -->
            <div class="card-custom">
                <h5 class="mb-3">Write New Article</h5>
                <form action="manage_blog.php" method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Article Title</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label>Category</label>
                            <select name="category_slug" class="form-control" required>
                                <option value="manufacturing">Manufacturing Excellence</option>
                                <option value="profit">Profit & Cost Control</option>
                                <option value="export">Export & Global Trade</option>
                                <option value="strategy">SME Strategy & KPIs</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label>Read Time</label>
                            <input type="text" name="read_time" class="form-control" placeholder="E.g. 5 min read" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Cover Image (Horizontal)</label>
                            <input type="file" name="image" class="form-control" accept="image/*" required>
                        </div>
                        <div class="col-md-8 mb-3">
                            <label>Search Keywords (For website search bar)</label>
                            <input type="text" name="keywords" class="form-control" placeholder="E.g. cost reduction, manufacturing, profit..." required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label>Short Excerpt (Shows on the card)</label>
                            <textarea name="excerpt" class="form-control" rows="2" required></textarea>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label>Full Article Content (You can use HTML tags like &lt;p&gt;, &lt;h4&gt;, &lt;ul&gt;)</label>
                            <textarea name="content" class="form-control" rows="8" required></textarea>
                        </div>
                        <div class="col-md-12 text-end">
                            <button type="submit" name="add_article" class="btn btn-custom px-5">Publish Article</button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- List Articles -->
            <div class="card-custom">
                <h5 class="mb-3">Published Articles</h5>
                <table class="table table-bordered align-middle">
                    <thead style="background-color: #091a32; color: #fff;">
                        <tr>
                            <th>Image</th>
                            <th>Title & Category</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($articles->num_rows > 0): while($row = $articles->fetch_assoc()): ?>
                            <tr>
                                <td><img src="../<?php echo $row['image_path']; ?>" class="img-preview" style="width: 100px; height: 60px;"></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($row['title']); ?></strong><br>
                                    <span class="text-muted" style="font-size: 12px;"><?php echo htmlspecialchars($row['category_name']); ?> | <?php echo date('d M Y', strtotime($row['created_at'])); ?></span>
                                </td>
                                <td>
                                    <?php if($row['is_published'] == 1): ?>
                                        <span class="badge-published">Published</span>
                                    <?php else: ?>
                                        <span class="badge-hidden">Hidden</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="manage_blog.php?toggle_publish=<?php echo $row['is_published']; ?>&id=<?php echo $row['id']; ?>" class="btn btn-sm <?php echo ($row['is_published']==1) ? 'btn-secondary' : 'btn-success'; ?> mb-1">
                                        <?php echo ($row['is_published']==1) ? 'Unpublish' : 'Publish'; ?>
                                    </a>
                                    <a href="manage_blog.php?delete=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger mb-1" onclick="return confirm('Are you sure?');">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; else: ?>
                            <tr><td colspan="4" class="text-center">No articles found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>