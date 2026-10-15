<?php
session_start();
// Database එක සම්බන්ධ කිරීම
include '../db.php';

// ලොග් වී නැතිනම් නැවත Login පිටුවට යැවීම (ආරක්ෂාව සඳහා)
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// දත්ත සමුදායෙන් පණිවිඩ ලබා ගැනීම (අලුත්ම පණිවිඩය උඩින්ම පෙන්වීමට DESC යොදා ඇත)
$sql = "SELECT * FROM contact_messages ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - IB Consultants</title>
    <!-- වෙබ් අඩවියේ ඇති Bootstrap කේතය -->
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="admin.css">
</head>

<body>

    <div class="container-fluid">
        <div class="row">
            <!-- වම්පස මෙනුව (Sidebar) -->
            <div class="col-md-2 sidebar">
                <h4>IB Admin</h4>
                <a href="manage_slider.php">🖼️ Manage Slider</a>
                <a href="manage_reviews.php">⭐ Manage Reviews</a>
                <a href="manage_programs.php">🎓 Manage Programs</a>
                <a href="manage_workshops.php">💼 Manage Workshops</a>
                <a href="manage_diplomas.php">🎓 Manage Diploma</a>
                <a href="dashboard.php" class="active">📥 Inquiries</a>
                <a href="../index.php" target="_blank">🌐 View Website</a>

                <a href="logout.php" class="btn-logout">🚪 Logout</a>
            </div>

            <!-- දකුණුපස ප්‍රධාන කොටස (Main Content) -->
            <div class="col-md-10 main-content">
                <h2 class="mb-4" style="color: #091a32; font-weight: 700;">Customer Inquiries</h2>

                <div class="card-custom">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead>
                                <tr>
                                    <th width="15%">Date & Time</th>
                                    <th width="20%">Customer Details</th>
                                    <th width="25%">Service Required</th>
                                    <th width="40%">Message / Requirement</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result->num_rows > 0): ?>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <!-- දිනය සහ වෙලාව -->
                                            <td>
                                                <span class="text-muted" style="font-size: 14px;">
                                                    <?php echo date('d M Y', strtotime($row['created_at'])); ?><br>
                                                    <strong
                                                        style="color: #091a32;"><?php echo date('h:i A', strtotime($row['created_at'])); ?></strong>
                                                </span>
                                            </td>

                                            <!-- පාරිභෝගික විස්තර -->
                                            <td>
                                                <strong
                                                    style="color: #091a32; font-size: 16px;"><?php echo htmlspecialchars($row['name']); ?></strong><br>
                                                <span style="font-size: 14px; color: #555;">
                                                    📧 <?php echo htmlspecialchars($row['email']); ?><br>
                                                    📞 <?php echo htmlspecialchars($row['mobile']); ?><br>
                                                    <?php if (!empty($row['whatsapp'])): ?>
                                                        💬 <?php echo htmlspecialchars($row['whatsapp']); ?>
                                                    <?php endif; ?>
                                                </span>
                                            </td>

                                            <!-- අවශ්‍ය සේවාව -->
                                            <td>
                                                <span
                                                    class="badge-service"><?php echo htmlspecialchars($row['service']); ?></span>
                                            </td>

                                            <!-- පණිවිඩය -->
                                            <td style="font-size: 14px; color: #444; text-align: justify;">
                                                <?php echo nl2br(htmlspecialchars($row['message'])); ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-muted">
                                            <h4>No inquiries received yet.</h4>
                                            <p>When customers fill the contact form, messages will appear here.</p>
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

</body>

</html>