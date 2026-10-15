<?php
session_start();
include '../db.php';

// ලොග් වී නැතිනම් ආපසු යැවීම
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$msg = '';

// අලුත් Slider එකක් දත්ත ගබඩාවට එකතු කිරීම
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_slide'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);

    // පින්තූරය Upload කිරීම
    $target_dir = "../uploads/slider/";
    $image_name = time() . '_' . basename($_FILES["slide_image"]["name"]);
    $target_file = $target_dir . $image_name;

    if (move_uploaded_file($_FILES["slide_image"]["tmp_name"], $target_file)) {
        $image_path = "uploads/slider/" . $image_name;
        $sql = "INSERT INTO hero_slider (image_path, title, description) VALUES ('$image_path', '$title', '$description')";
        $conn->query($sql);
        $msg = "<div class='alert alert-success'>Slide added successfully!</div>";
    } else {
        $msg = "<div class='alert alert-danger'>Failed to upload image. Please try again.</div>";
    }
}

// පරණ Slider එකක් මකා දැමීම (Delete)
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $res = $conn->query("SELECT image_path FROM hero_slider WHERE id=$id");
    if ($row = $res->fetch_assoc()) {
        // Folder එකෙන් පින්තූරය මකා දැමීම
        if (file_exists("../" . $row['image_path'])) {
            unlink("../" . $row['image_path']);
        }
    }
    $conn->query("DELETE FROM hero_slider WHERE id=$id");
    header("Location: manage_slider.php");
}

// දත්ත සමුදායෙන් Sliders ලබා ගැනීම
$slides = $conn->query("SELECT * FROM hero_slider ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">

<head>
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
                <h2 class="mb-4" style="color: #091a32; font-weight: 700;">Manage Hero Slider</h2>

                <?php echo $msg; ?>

                <!-- Add New Slide Form -->
                <div class="card-custom">
                    <h5 class="mb-3">Add New Slide</h5>
                    <form action="" method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Slider Image (1920x1080px)</label>
                                <input type="file" name="slide_image" class="form-control" accept="image/*" required>
                            </div>
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Main Title</label>
                                <input type="text" name="title" class="form-control"
                                    placeholder="E.g. Expertise For Your Growth." required>
                            </div>
                            <div class="col-md-10 mb-3">
                                <label class="form-label">Short Description</label>
                                <textarea name="description" class="form-control" rows="2"
                                    placeholder="E.g. Practical Business & Profit Improvement..." required></textarea>
                            </div>
                            <div class="col-md-2 mb-3 d-flex align-items-end">
                                <button type="submit" name="add_slide" class="btn btn-custom w-100 p-2">Add
                                    Slide</button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Display Existing Slides -->
                <div class="card-custom">
                    <h5 class="mb-3">Current Slides</h5>
                    <table class="table table-bordered align-middle">
                        <thead style="background-color: #091a32; color: #fff;">
                            <tr>
                                <th>Image</th>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($slides->num_rows > 0): ?>
                                <?php while ($row = $slides->fetch_assoc()): ?>
                                    <tr>
                                        <td><img src="../<?php echo $row['image_path']; ?>" class="img-preview"></td>
                                        <td><strong><?php echo htmlspecialchars($row['title']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($row['description']); ?></td>
                                        <td>
                                            <a href="manage_slider.php?delete=<?php echo $row['id']; ?>"
                                                class="btn btn-sm btn-danger"
                                                onclick="return confirm('Are you sure you want to delete this slide?');">Delete</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center">No slides found. Add a new slide above!</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

</body>

</html>