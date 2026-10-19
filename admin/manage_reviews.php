<?php
session_start();
include '../db.php';
require_once 'icon_picker_helper.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$msg = '';
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'added') {
        $msg = "<div class='alert alert-success alert-dismissible fade show'><i class='fa-solid fa-circle-check me-2'></i> Client review added successfully!</div>";
    } elseif ($_GET['msg'] == 'updated') {
        $msg = "<div class='alert alert-success alert-dismissible fade show'><i class='fa-solid fa-circle-check me-2'></i> Client review updated successfully!</div>";
    } elseif ($_GET['msg'] == 'trashed') {
        $msg = "<div class='alert alert-warning alert-dismissible fade show'><i class='fa-solid fa-trash-can me-2'></i> Review moved to Trash! You can restore it anytime from the Trash tab.</div>";
    } elseif ($_GET['msg'] == 'restored') {
        $msg = "<div class='alert alert-success alert-dismissible fade show'><i class='fa-solid fa-rotate-left me-2'></i> Review restored successfully to active list!</div>";
    } elseif ($_GET['msg'] == 'deleted_permanent') {
        $msg = "<div class='alert alert-danger alert-dismissible fade show'><i class='fa-solid fa-triangle-exclamation me-2'></i> Review permanently deleted!</div>";
    }
}

// Toggle Publish / Hide Status
if (isset($_GET['toggle_publish']) && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $status = (int) $_GET['toggle_publish'];
    $new_status = ($status == 1) ? 0 : 1;
    $conn->query("UPDATE client_reviews SET is_published = $new_status WHERE id = $id");

    if ((!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || isset($_GET['ajax'])) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'new_status' => $new_status]);
        exit;
    }

    header("Location: manage_reviews.php?msg=updated");
    exit;
}

// Soft Delete (Move to Trash)
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $conn->query("UPDATE client_reviews SET deleted_at = NOW() WHERE id = $id");
    header("Location: manage_reviews.php?msg=trashed");
    exit;
}

// Restore from Trash
if (isset($_GET['restore'])) {
    $id = (int) $_GET['restore'];
    $conn->query("UPDATE client_reviews SET deleted_at = NULL WHERE id = $id");
    header("Location: manage_reviews.php?view=trash&msg=restored");
    exit;
}

// Permanent / Force Delete
if (isset($_GET['force_delete'])) {
    $id = (int) $_GET['force_delete'];
    $conn->query("DELETE FROM client_reviews WHERE id = $id");
    header("Location: manage_reviews.php?view=trash&msg=deleted_permanent");
    exit;
}

// Edit Mode Check
$edit_mode = false;
$edit_data = null;
if (isset($_GET['edit'])) {
    $edit_id = (int) $_GET['edit'];
    $res = $conn->query("SELECT * FROM client_reviews WHERE id = $edit_id");
    if ($res && $res->num_rows > 0) {
        $edit_mode = true;
        $edit_data = $res->fetch_assoc();
    }
}

// Add or Update Review
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sector_icon = $conn->real_escape_string(trim($_POST['sector_icon'] ?? 'fa-solid fa-briefcase'));
    $sector_name = $conn->real_escape_string(trim($_POST['sector_name'] ?? ''));
    $stars = isset($_POST['stars']) ? max(1, min(5, (int) $_POST['stars'])) : 5;
    $quote_text = $conn->real_escape_string(trim($_POST['quote_text'] ?? ''));
    $impact_text = $conn->real_escape_string(trim($_POST['impact_text'] ?? ''));
    $author_name = $conn->real_escape_string(trim($_POST['author_name'] ?? ''));
    $author_company = $conn->real_escape_string(trim($_POST['author_company'] ?? ''));
    $display_order = isset($_POST['display_order']) ? (int) $_POST['display_order'] : 1;

    // Monogram auto-calculation if left empty
    $author_monogram = trim($_POST['author_monogram'] ?? '');
    if (empty($author_monogram) && !empty($author_name)) {
        $words = preg_split('/\s+/', trim($author_name));
        if (count($words) >= 2) {
            $author_monogram = strtoupper(substr($words[0], 0, 1) . substr($words[count($words) - 1], 0, 1));
        } else {
            $author_monogram = strtoupper(substr($author_name, 0, 2));
        }
    }
    $author_monogram = $conn->real_escape_string($author_monogram);

    if (isset($_POST['update_review'])) {
        $id = (int) $_POST['review_id'];
        $sql = "UPDATE client_reviews SET 
                sector_icon = '$sector_icon', 
                sector_name = '$sector_name', 
                stars = $stars,
                quote_text = '$quote_text', 
                impact_text = '$impact_text', 
                author_monogram = '$author_monogram', 
                author_name = '$author_name', 
                author_company = '$author_company',
                display_order = $display_order
                WHERE id = $id";
        if ($conn->query($sql)) {
            header("Location: manage_reviews.php?msg=updated");
            exit;
        } else {
            $msg = "<div class='alert alert-danger'>Failed to update review: " . htmlspecialchars($conn->error) . "</div>";
        }
    } elseif (isset($_POST['add_review'])) {
        $sql = "INSERT INTO client_reviews (sector_icon, sector_name, stars, quote_text, impact_text, author_monogram, author_name, author_company, display_order, is_published) 
                VALUES ('$sector_icon', '$sector_name', $stars, '$quote_text', '$impact_text', '$author_monogram', '$author_name', '$author_company', $display_order, 1)";
        if ($conn->query($sql)) {
            header("Location: manage_reviews.php?msg=added");
            exit;
        } else {
            $msg = "<div class='alert alert-danger'>Failed to add review: " . htmlspecialchars($conn->error) . "</div>";
        }
    }
}

// Tab View Mode
$view = isset($_GET['view']) && $_GET['view'] === 'trash' ? 'trash' : 'active';

// Counts for tabs
$count_active = 0;
$c_res = $conn->query("SELECT COUNT(*) as total FROM client_reviews WHERE deleted_at IS NULL");
if ($c_res && $crow = $c_res->fetch_assoc()) {
    $count_active = (int) $crow['total'];
}

$count_trash = 0;
$t_res = $conn->query("SELECT COUNT(*) as total FROM client_reviews WHERE deleted_at IS NOT NULL");
if ($t_res && $trow = $t_res->fetch_assoc()) {
    $count_trash = (int) $trow['total'];
}

if ($view === 'trash') {
    $reviews = $conn->query("SELECT * FROM client_reviews WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC");
} else {
    $reviews = $conn->query("SELECT * FROM client_reviews WHERE deleted_at IS NULL ORDER BY display_order ASC, id DESC");
}
$total_reviews = $reviews ? $reviews->num_rows : 0;
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

    <style>
        .review-avatar-preview {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: linear-gradient(135deg, #091a32 0%, #1e3a8a 100%);
            color: #d4af37;
            font-weight: 800;
            font-size: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #d4af37;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 10px rgba(9, 26, 50, 0.15);
        }

        .sector-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 6px;
            background: rgba(9, 26, 50, 0.05);
            color: #091a32;
            font-size: 12.5px;
            font-weight: 700;
            border: 1px solid rgba(9, 26, 50, 0.1);
        }

        .stars-display {
            color: #d4af37;
            font-size: 13px;
        }

        .quick-sector-chip {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            font-size: 12px;
            font-weight: 600;
            color: #334155;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 999px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .quick-sector-chip:hover {
            background: #091a32;
            color: #ffffff;
            border-color: #091a32;
            transform: translateY(-1px);
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

                <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                    <div>
                        <h2 style="color: var(--admin-navy); font-weight: 800; margin: 0; font-size: 26px;">Manage Client Reviews & Testimonials</h2>
                        <p class="text-muted m-0" style="font-size: 14px;">Edit client success stories, industry tags, verified author designations, and homepage testimonials.</p>
                    </div>
                    <a href="../index.php#testimonials" target="_blank" class="btn btn-outline-dark btn-sm">
                        <i class="fa-solid fa-globe me-1"></i> View Live Testimonials
                    </a>
                </div>

                <?php echo $msg; ?>

                <?php if ($view === 'active' || $edit_mode): ?>
                <!-- Form Card -->
                <div class="card-custom mb-4" id="review-form-card" style="<?php echo $edit_mode ? 'border: 2px solid #d4af37;' : ''; ?>">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="m-0" style="font-weight: 700; color: var(--admin-navy);">
                            <?php echo $edit_mode ? '<i class="fa-solid fa-pen-to-square text-primary me-2"></i>Edit Review (ID #' . $edit_data['id'] . ')' : '<i class="fa-solid fa-circle-plus text-gold me-2"></i>Add New Client Testimonial'; ?>
                        </h5>
                        <?php if ($edit_mode): ?>
                            <a href="manage_reviews.php" class="btn btn-sm btn-outline-secondary">
                                <i class="fa-solid fa-xmark me-1"></i> Cancel Edit Mode
                            </a>
                        <?php endif; ?>
                    </div>

                    <form action="manage_reviews.php" method="POST" class="modern-form">
                        <?php if ($edit_mode): ?>
                            <input type="hidden" name="review_id" value="<?php echo (int) $edit_data['id']; ?>">
                        <?php endif; ?>

                        <!-- Section 1: Sector & Classification -->
                        <div class="form-section mb-4">
                            <h6 class="section-title"><i class="fa-solid fa-industry me-2"></i>1. Sector & Rating</h6>
                            
                            <!-- Quick autofill sector chips -->
                            <div class="mb-3 p-2 bg-light rounded border">
                                <span class="small fw-bold text-muted me-2"><i class="fa-solid fa-bolt me-1 text-warning"></i>Quick Sector Presets:</span>
                                <div class="d-inline-flex flex-wrap gap-1 mt-1">
                                    <span class="quick-sector-chip" onclick="setSector('Manufacturing SME', 'fa-solid fa-industry')">
                                        <i class="fa-solid fa-industry"></i> Manufacturing
                                    </span>
                                    <span class="quick-sector-chip" onclick="setSector('Hospitality & Leisure', 'fa-solid fa-hotel')">
                                        <i class="fa-solid fa-hotel"></i> Hospitality
                                    </span>
                                    <span class="quick-sector-chip" onclick="setSector('Export & Trade', 'fa-solid fa-globe')">
                                        <i class="fa-solid fa-globe"></i> Export & Trade
                                    </span>
                                    <span class="quick-sector-chip" onclick="setSector('Financial Services', 'fa-solid fa-chart-line')">
                                        <i class="fa-solid fa-chart-line"></i> Financial Services
                                    </span>
                                    <span class="quick-sector-chip" onclick="setSector('Retail & FMCG', 'fa-solid fa-shop')">
                                        <i class="fa-solid fa-shop"></i> Retail & FMCG
                                    </span>
                                    <span class="quick-sector-chip" onclick="setSector('Corporate Strategy', 'fa-solid fa-briefcase')">
                                        <i class="fa-solid fa-briefcase"></i> Corporate Strategy
                                    </span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-7 mb-3">
                                    <label class="form-label fw-bold">Sector / Industry Name *</label>
                                    <input type="text" name="sector_name" id="sector_name_input" class="form-control"
                                        placeholder="E.g. Manufacturing SME, Hospitality & Leisure"
                                        value="<?php echo $edit_mode ? htmlspecialchars($edit_data['sector_name']) : ''; ?>"
                                        required>
                                </div>
                                <div class="col-md-5 mb-3">
                                    <label class="form-label fw-bold">Rating Stars *</label>
                                    <select name="stars" class="form-select">
                                        <?php
                                        $curr_stars = $edit_mode ? (int) $edit_data['stars'] : 5;
                                        for ($s = 5; $s >= 1; $s--):
                                            ?>
                                            <option value="<?php echo $s; ?>" <?php echo ($curr_stars == $s) ? 'selected' : ''; ?>>
                                                <?php echo $s; ?> Stars (<?php echo str_repeat('★', $s); ?>)
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>

                                <!-- Rich Visual Icon Picker & Live Preview Component -->
                                <div class="col-md-12 mb-2">
                                    <?php
                                    $curr_sector_icon = ($edit_mode && !empty($edit_data['sector_icon'])) ? $edit_data['sector_icon'] : 'fa-solid fa-briefcase';
                                    echo renderAdminIconPicker('sector_icon', $curr_sector_icon, 'Sector / Industry Icon Class', ['default' => 'fa-solid fa-briefcase']);
                                    ?>
                                </div>
                            </div>
                        </div>

                        <!-- Section 2: Author Details (Name, Company, Monogram) -->
                        <div class="form-section mb-4">
                            <h6 class="section-title"><i class="fa-solid fa-user-tie me-2"></i>2. Client & Author Details</h6>
                            <div class="row align-items-center">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold">Author Name / Designation *</label>
                                    <input type="text" name="author_name" id="author_name_input" class="form-control"
                                        placeholder="E.g. Managing Director or Dr. Rohan De Silva"
                                        value="<?php echo $edit_mode ? htmlspecialchars($edit_data['author_name']) : ''; ?>"
                                        oninput="updateMonogramLive(this.value)"
                                        required>
                                    <small class="text-muted">Client or executive who gave the review</small>
                                </div>
                                <div class="col-md-5 mb-3">
                                    <label class="form-label fw-bold">Author Company / Organization *</label>
                                    <input type="text" name="author_company" class="form-control"
                                        placeholder="E.g. Leading Manufacturing SME · Western Province"
                                        value="<?php echo $edit_mode ? htmlspecialchars($edit_data['author_company']) : ''; ?>"
                                        required>
                                    <small class="text-muted">Company, firm, or location details</small>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label fw-bold">Monogram (Avatar Initials)</label>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="review-avatar-preview" id="monogram-preview">
                                            <?php echo $edit_mode && !empty($edit_data['author_monogram']) ? htmlspecialchars($edit_data['author_monogram']) : 'MD'; ?>
                                        </div>
                                        <div class="flex-grow-1">
                                            <input type="text" name="author_monogram" id="author_monogram_input" class="form-control text-uppercase"
                                                placeholder="E.g. MD" maxlength="4"
                                                value="<?php echo $edit_mode ? htmlspecialchars($edit_data['author_monogram']) : ''; ?>"
                                                oninput="setManualMonogram(this.value)">
                                            <small class="text-muted d-block" style="font-size: 11px;">Auto-calculated if empty</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section 3: Testimonial & Impact -->
                        <div class="form-section mb-4">
                            <h6 class="section-title"><i class="fa-solid fa-quote-left me-2"></i>3. Testimonial Quote & Impact</h6>
                            <div class="row">
                                <div class="col-md-9 mb-3">
                                    <label class="form-label fw-bold">Key Impact Statement *</label>
                                    <input type="text" name="impact_text" class="form-control"
                                        placeholder="E.g. Plant Operations Streamlined & Wastage Minimized"
                                        value="<?php echo $edit_mode ? htmlspecialchars($edit_data['impact_text']) : ''; ?>"
                                        required>
                                    <small class="text-muted">Highlighted outcome shown in the testimonial card tag</small>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label fw-bold">Display Order</label>
                                    <input type="number" name="display_order" class="form-control" min="1" max="99"
                                        value="<?php echo $edit_mode ? (int) $edit_data['display_order'] : (int) ($count_active + 1); ?>">
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label fw-bold">Client Review / Testimonial Quote *</label>
                                    <textarea name="quote_text" class="form-control" rows="4"
                                        placeholder="Write the full executive testimonial given by the client..."
                                        required><?php echo $edit_mode ? htmlspecialchars($edit_data['quote_text']) : ''; ?></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-end align-items-center mt-3 pt-3 border-top">
                            <?php if ($edit_mode): ?>
                                <a href="manage_reviews.php" class="btn btn-secondary px-4 py-2 me-3">Cancel</a>
                                <button type="submit" name="update_review" class="btn btn-custom px-5 py-2">
                                    <i class="fa-solid fa-floppy-disk me-2"></i> Update Review
                                </button>
                            <?php else: ?>
                                <button type="submit" name="add_review" class="btn btn-custom px-5 py-2">
                                    <i class="fa-solid fa-plus me-2"></i> Add Review
                                </button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
                <?php endif; ?>

                <?php if ($edit_mode): ?>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const formEl = document.getElementById('review-form-card');
                            if (formEl) formEl.scrollIntoView({ behavior: 'smooth' });
                        });
                    </script>
                <?php endif; ?>

                <!-- Reviews Table Card -->
                <div class="card-custom">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                        <div class="admin-tab-nav">
                            <a href="manage_reviews.php" class="admin-tab-nav-btn <?php echo $view === 'active' ? 'active' : ''; ?>">
                                <i class="fa-solid fa-list-check"></i> Active Reviews <span class="tab-count-badge"><?php echo $count_active; ?></span>
                            </a>
                            <a href="manage_reviews.php?view=trash" class="admin-tab-nav-btn tab-trash <?php echo $view === 'trash' ? 'active' : ''; ?>">
                                <i class="fa-solid fa-trash-can"></i> Trash <span class="tab-count-badge"><?php echo $count_trash; ?></span>
                            </a>
                        </div>
                        <?php if ($view === 'trash'): ?>
                            <a href="manage_reviews.php" class="btn btn-sm btn-outline-secondary">
                                <i class="fa-solid fa-arrow-left me-1"></i> Back to Active Reviews
                            </a>
                        <?php endif; ?>
                    </div>

                    <?php if ($view === 'trash'): ?>
                    <!-- Trash Reviews Table -->
                    <div class="table-responsive">
                        <table class="table admin-table align-middle">
                            <thead style="background-color: #7f1d1d; color: #fff;">
                                <tr>
                                    <th style="width: 60px; text-align: center;">No.</th>
                                    <th style="width: 200px;">Sector & Rating</th>
                                    <th>Review Quote & Impact</th>
                                    <th style="width: 240px;">Author Details</th>
                                    <th style="width: 180px; text-align: center;">Deleted On</th>
                                    <th style="width: 130px; text-align: center;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($reviews && $reviews->num_rows > 0):
                                    $t_cnt = 1;
                                    while ($row = $reviews->fetch_assoc()):
                                        ?>
                                        <tr style="background: rgba(239, 68, 68, 0.02);">
                                            <td class="text-center fw-bold text-muted">
                                                <?php echo str_pad($t_cnt, 2, '0', STR_PAD_LEFT); ?>
                                            </td>
                                            <td>
                                                <span class="sector-badge mb-1">
                                                    <i class="<?php echo htmlspecialchars($row['sector_icon']); ?> text-gold"></i>
                                                    <?php echo htmlspecialchars($row['sector_name']); ?>
                                                </span>
                                                <div class="stars-display mt-1">
                                                    <?php for ($i = 0; $i < (int) $row['stars']; $i++): ?>
                                                        <i class="fa-solid fa-star"></i>
                                                    <?php endfor; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <p class="mb-1 text-dark" style="font-size: 13px;">“<?php echo nl2br(htmlspecialchars($row['quote_text'])); ?>”</p>
                                                <span class="badge bg-light text-primary border">
                                                    <i class="fa-solid fa-chart-line me-1 text-gold"></i> <?php echo htmlspecialchars($row['impact_text']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="review-avatar-preview" style="width: 38px; height: 38px; font-size: 14px;">
                                                        <?php echo htmlspecialchars($row['author_monogram']); ?>
                                                    </div>
                                                    <div>
                                                        <strong style="color: var(--admin-navy); font-size: 14px;"><?php echo htmlspecialchars($row['author_name']); ?></strong><br>
                                                        <small class="text-muted" style="font-size: 12px;"><?php echo htmlspecialchars($row['author_company']); ?></small>
                                                    </div>
                                                </div>
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
                                                    <a href="manage_reviews.php?restore=<?php echo $row['id']; ?>"
                                                        class="btn-action btn-action-restore" title="Restore Review to Active List">
                                                        <i class="fa-solid fa-rotate-left"></i>
                                                    </a>
                                                    <!-- Force Delete -->
                                                    <a href="manage_reviews.php?force_delete=<?php echo $row['id']; ?>"
                                                        class="btn-action btn-action-force-delete" title="Permanently Delete Review"
                                                        data-item-name="Review by <?php echo htmlspecialchars($row['author_name']); ?>">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php 
                                        $t_cnt++;
                                    endwhile; else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            <i class="fa-regular fa-trash-can me-2"></i> Trash is empty.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <!-- Active Reviews Table -->
                    <div class="table-responsive">
                        <table class="table admin-table align-middle">
                            <thead style="background-color: #091a32; color: #fff;">
                                <tr>
                                    <th style="width: 55px; text-align: center;">Order</th>
                                    <th style="width: 190px;">Sector & Rating</th>
                                    <th>Review Quote & Impact</th>
                                    <th style="width: 250px;">Author Details</th>
                                    <th style="width: 100px; text-align: center;">Status</th>
                                    <th style="width: 140px; text-align: center;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($reviews && $reviews->num_rows > 0):
                                    $counter = 1;
                                    while ($row = $reviews->fetch_assoc()):
                                        $is_pub = isset($row['is_published']) ? (int) $row['is_published'] : 1;
                                        ?>
                                        <tr>
                                            <td class="text-center fw-bold" style="color: var(--admin-navy);">
                                                #<?php echo (int) $row['display_order']; ?>
                                            </td>
                                            <td>
                                                <span class="sector-badge mb-1">
                                                    <i class="<?php echo htmlspecialchars($row['sector_icon']); ?> text-gold"></i>
                                                    <?php echo htmlspecialchars($row['sector_name']); ?>
                                                </span>
                                                <div class="stars-display mt-1">
                                                    <?php for ($i = 0; $i < (int) $row['stars']; $i++): ?>
                                                        <i class="fa-solid fa-star"></i>
                                                    <?php endfor; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <p class="mb-1 text-dark" style="font-size: 13.5px; line-height: 1.45;">“<?php echo nl2br(htmlspecialchars($row['quote_text'])); ?>”</p>
                                                <span class="badge bg-light text-primary border">
                                                    <i class="fa-solid fa-chart-line me-1 text-gold"></i> <strong>Key Impact:</strong> <?php echo htmlspecialchars($row['impact_text']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="review-avatar-preview" style="width: 42px; height: 42px; font-size: 15px;">
                                                        <?php echo htmlspecialchars($row['author_monogram']); ?>
                                                    </div>
                                                    <div>
                                                        <strong style="color: var(--admin-navy); font-size: 14px;"><?php echo htmlspecialchars($row['author_name']); ?></strong><br>
                                                        <small class="text-muted" style="font-size: 12px;"><?php echo htmlspecialchars($row['author_company']); ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center" id="status-badge-<?php echo $row['id']; ?>">
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
                                                        <i class="fa-solid <?php echo ($is_pub == 1) ? 'fa-eye-slash' : 'fa-eye'; ?>"></i>
                                                    </a>

                                                    <!-- Delete (Soft Delete) -->
                                                    <a href="manage_reviews.php?delete=<?php echo $row['id']; ?>"
                                                        class="btn-action btn-action-delete" title="Move to Trash"
                                                        data-item-name="Review by <?php echo htmlspecialchars($row['author_name']); ?>">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php 
                                        $counter++;
                                    endwhile; else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            No client reviews found in database. Add a new testimonial above.
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

    <script>
        function updateIconPreview(val) {
            const el = document.getElementById('sector-icon-live');
            if (el) {
                el.className = (val.trim() || 'fa-solid fa-briefcase') + ' text-gold';
            }
        }

        function setSector(name, icon) {
            const nameInp = document.getElementById('sector_name_input');
            if (nameInp) nameInp.value = name;
            if (typeof updateIconPickerUI === 'function') {
                updateIconPickerUI('sector_icon', icon, true);
            } else if (typeof window.updateIconPickerUI === 'function') {
                window.updateIconPickerUI('sector_icon', icon, true);
            } else {
                const iconInp = document.getElementById('sector_icon');
                if (iconInp) iconInp.value = icon;
            }
        }

        let isManualMonogram = false;
        function setManualMonogram(val) {
            isManualMonogram = (val.trim().length > 0);
            const preview = document.getElementById('monogram-preview');
            if (preview) {
                preview.textContent = val.toUpperCase().trim() || 'MD';
            }
        }

        function updateMonogramLive(authorName) {
            if (isManualMonogram) return;
            const preview = document.getElementById('monogram-preview');
            const input = document.getElementById('author_monogram_input');
            const trimmed = authorName.trim();
            if (!trimmed) {
                if (preview) preview.textContent = 'MD';
                return;
            }
            const parts = trimmed.split(/\s+/);
            let code = '';
            if (parts.length >= 2) {
                code = parts[0][0] + parts[parts.length - 1][0];
            } else {
                code = parts[0].substring(0, 2);
            }
            code = code.toUpperCase();
            if (preview) preview.textContent = code;
            if (input && !input.value) {
                input.placeholder = 'E.g. ' + code;
            }
        }
    </script>

</body>

</html>