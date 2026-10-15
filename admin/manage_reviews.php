<?php
session_start();
include '../db.php'; 

if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true){
    header("Location: login.php");
    exit;
}

$msg = '';

// අලුත් Review එකක් එකතු කිරීම
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_review'])) {
    $sector_icon = $conn->real_escape_string($_POST['sector_icon']);
    $sector_name = $conn->real_escape_string($_POST['sector_name']);
    $quote_text = $conn->real_escape_string($_POST['quote_text']);
    $impact_text = $conn->real_escape_string($_POST['impact_text']);
    $author_monogram = $conn->real_escape_string($_POST['author_monogram']);
    $author_name = $conn->real_escape_string($_POST['author_name']);
    $author_company = $conn->real_escape_string($_POST['author_company']);
    
    $sql = "INSERT INTO client_reviews (sector_icon, sector_name, quote_text, impact_text, author_monogram, author_name, author_company) 
            VALUES ('$sector_icon', '$sector_name', '$quote_text', '$impact_text', '$author_monogram', '$author_name', '$author_company')";
    
    if($conn->query($sql)){
        $msg = "<div class='alert alert-success'>Review added successfully!</div>";
    } else {
        $msg = "<div class='alert alert-danger'>Failed to add review.</div>";
    }
}

// Review එකක් මකා දැමීම
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM client_reviews WHERE id=$id");
    header("Location: manage_reviews.php");
}

$reviews = $conn->query("SELECT * FROM client_reviews ORDER BY display_order ASC, id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Reviews - IB Admin</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="admin.css">
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-2 sidebar">
            <h4>IB Admin</h4>
            <a href="manage_slider.php">🖼️ Manage Slider</a>
            <a href="manage_reviews.php" class="active">⭐ Manage Reviews</a>
            <a href="manage_programs.php">🎓 Manage Programs</a>
            <a href="manage_workshops.php">💼 Manage Workshops</a>
                            <a href="manage_diplomas.php">🎓 Manage Diploma</a>

            <a href="dashboard.php">📥 Inquiries</a>
            <a href="../index.php" target="_blank">🌐 View Website</a>
            <a href="logout.php" class="btn btn-danger w-100 mt-5">🚪 Logout</a>
        </div>
        
        <!-- Main Content -->
        <div class="col-md-10 main-content">
            <h2 class="mb-4" style="color: #091a32; font-weight: 700;">Manage Client Reviews</h2>
            <?php echo $msg; ?>

            <div class="card-custom">
                <h5 class="mb-3">Add New Review</h5>
                <form action="" method="POST">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label>Sector Icon Class</label>
                            <input type="text" name="sector_icon" class="form-control" placeholder="e.g. fa-solid fa-industry" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label>Sector Name</label>
                            <input type="text" name="sector_name" class="form-control" placeholder="e.g. Manufacturing SME" required>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label>Author Monogram</label>
                            <input type="text" name="author_monogram" class="form-control" placeholder="e.g. MD" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Author Title / Name</label>
                            <input type="text" name="author_name" class="form-control" placeholder="e.g. Managing Director" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Company Details</label>
                            <input type="text" name="author_company" class="form-control" placeholder="e.g. Leading SME · Western Province" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Key Impact</label>
                            <input type="text" name="impact_text" class="form-control" placeholder="e.g. Plant Operations Streamlined..." required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label>Review / Quote</label>
                            <textarea name="quote_text" class="form-control" rows="3" placeholder="Type the client's review here..." required></textarea>
                        </div>
                    </div>
                    <button type="submit" name="add_review" class="btn btn-custom px-4">Add Review</button>
                </form>
            </div>

            <div class="card-custom">
                <h5 class="mb-3">Current Reviews</h5>
                <table class="table table-bordered">
                    <thead style="background-color: #091a32; color: #fff;">
                        <tr>
                            <th>Sector</th>
                            <th>Review</th>
                            <th>Author</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($reviews->num_rows > 0): while($row = $reviews->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?php echo $row['sector_name']; ?></strong></td>
                                <td style="font-size: 13px;"><?php echo $row['quote_text']; ?></td>
                                <td><?php echo $row['author_name']; ?><br><small><?php echo $row['author_company']; ?></small></td>
                                <td><a href="manage_reviews.php?delete=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?');">Delete</a></td>
                            </tr>
                        <?php endwhile; else: ?>
                            <tr><td colspan="4" class="text-center">No reviews found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>
</body>
</html>