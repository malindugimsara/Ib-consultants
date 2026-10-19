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
        $msg = "<div class='alert alert-success alert-dismissible fade show'>Workshop added successfully!</div>";
    } elseif ($_GET['msg'] == 'updated') {
        $msg = "<div class='alert alert-success alert-dismissible fade show'>Workshop updated and display orders re-sequenced successfully!</div>";
    } elseif ($_GET['msg'] == 'trashed') {
        $msg = "<div class='alert alert-warning alert-dismissible fade show'>Workshop moved to Trash! You can restore it anytime from the Trash tab.</div>";
    } elseif ($_GET['msg'] == 'restored') {
        $msg = "<div class='alert alert-success alert-dismissible fade show'>Workshop restored successfully to active list!</div>";
    } elseif ($_GET['msg'] == 'deleted_permanent') {
        $msg = "<div class='alert alert-danger alert-dismissible fade show'>Workshop permanently deleted!</div>";
    } elseif ($_GET['msg'] == 'reordered') {
        $msg = "<div class='alert alert-success alert-dismissible fade show'>Workshop display order updated successfully!</div>";
    }
}

/**
 * Reorders all workshops sequentially without duplicates when a workshop's order is set or changed.
 *
 * @param mysqli $conn
 * @param int $target_id Workshop being inserted or updated
 * @param int $new_order Desired 1-based display order position
 */
function reorderWorkshops($conn, $target_id, $new_order) {
    // 1. Fetch all active workshop IDs ordered by current display_order ASC, id ASC
    $res = $conn->query("SELECT id FROM business_workshops WHERE deleted_at IS NULL ORDER BY display_order ASC, id ASC");
    $list = [];
    while ($r = $res->fetch_assoc()) {
        $list[] = (int) $r['id'];
    }

    $target_id = (int) $target_id;
    // Remove target_id if it's already in the list
    $key = array_search($target_id, $list);
    if ($key !== false) {
        unset($list[$key]);
        $list = array_values($list);
    }

    // Clamp new_order between 1 and (count($list) + 1)
    $max_pos = count($list) + 1;
    $new_pos = (int) $new_order;
    if ($new_pos < 1) {
        $new_pos = 1;
    } elseif ($new_pos > $max_pos) {
        $new_pos = $max_pos;
    }

    // Insert target_id at new_pos - 1
    array_splice($list, $new_pos - 1, 0, [$target_id]);

    // Update every item with its clean 1-based sequential position
    $stmt = $conn->prepare("UPDATE business_workshops SET display_order = ? WHERE id = ?");
    foreach ($list as $index => $ws_id) {
        $order_num = $index + 1;
        $stmt->bind_param("ii", $order_num, $ws_id);
        $stmt->execute();
    }
    $stmt->close();
}

/**
 * Normalizes all active workshop display orders to be strictly sequential (1, 2, 3, ...) without gaps or duplicates.
 *
 * @param mysqli $conn
 */
function normalizeWorkshopOrders($conn) {
    $res = $conn->query("SELECT id FROM business_workshops WHERE deleted_at IS NULL ORDER BY display_order ASC, id ASC");
    if ($res && $res->num_rows > 0) {
        $stmt = $conn->prepare("UPDATE business_workshops SET display_order = ? WHERE id = ?");
        $pos = 1;
        while ($r = $res->fetch_assoc()) {
            $ws_id = (int) $r['id'];
            $stmt->bind_param("ii", $pos, $ws_id);
            $stmt->execute();
            $pos++;
        }
        $stmt->close();
    }
}

// 1. Move Workshop Position (Up / Down)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = (int) $_GET['id'];

    if ($action === 'move_up' || $action === 'move_down') {
        $res = $conn->query("SELECT id FROM business_workshops ORDER BY display_order ASC, id ASC");
        $list = [];
        $cur_pos = 1;
        $i = 1;
        while ($r = $res->fetch_assoc()) {
            $ws_id = (int) $r['id'];
            $list[] = $ws_id;
            if ($ws_id === $id) {
                $cur_pos = $i;
            }
            $i++;
        }

        $new_pos = $cur_pos;
        if ($action === 'move_up' && $cur_pos > 1) {
            $new_pos = $cur_pos - 1;
        } elseif ($action === 'move_down' && $cur_pos < count($list)) {
            $new_pos = $cur_pos + 1;
        }

        if ($new_pos !== $cur_pos) {
            reorderWorkshops($conn, $id, $new_pos);
        }

        header("Location: manage_workshops.php?msg=reordered");
        exit;
    }
}

// 2. Add Workshop
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_workshop'])) {
    $title = trim($_POST['title']);
    $category_slug = trim($_POST['category_slug']);
    $category_name = trim($_POST['category_name']);
    $category_icon = trim($_POST['category_icon']);
    $duration = trim($_POST['duration']);
    $target_audience = trim($_POST['target_audience']);
    $key_topics = trim($_POST['key_topics']);
    $expected_outcome = trim($_POST['expected_outcome']);
    $display_order = (int) ($_POST['display_order'] ?? 0);

    if (empty($duration)) {
        $duration = '1-Day Practical Workshop';
    }
    if (empty($category_icon)) {
        $category_icon = 'fa-solid fa-layer-group';
    }

    $stmt = $conn->prepare("INSERT INTO business_workshops (title, category_slug, category_name, category_icon, duration, target_audience, key_topics, expected_outcome, display_order, is_published) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
    $stmt->bind_param("ssssssssi", $title, $category_slug, $category_name, $category_icon, $duration, $target_audience, $key_topics, $expected_outcome, $display_order);

    if ($stmt->execute()) {
        $new_id = $conn->insert_id;
        $stmt->close();
        reorderWorkshops($conn, $new_id, $display_order > 0 ? $display_order : 999);
        header("Location: manage_workshops.php?msg=added");
        exit;
    } else {
        $msg = "<div class='alert alert-danger'>Error adding workshop: " . htmlspecialchars($conn->error) . "</div>";
    }
}

// 3. Update Workshop
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_workshop'])) {
    $id = (int) $_POST['workshop_id'];
    $title = trim($_POST['title']);
    $category_slug = trim($_POST['category_slug']);
    $category_name = trim($_POST['category_name']);
    $category_icon = trim($_POST['category_icon']);
    $duration = trim($_POST['duration']);
    $target_audience = trim($_POST['target_audience']);
    $key_topics = trim($_POST['key_topics']);
    $expected_outcome = trim($_POST['expected_outcome']);
    $display_order = (int) ($_POST['display_order'] ?? 0);

    if (empty($duration)) {
        $duration = '1-Day Practical Workshop';
    }
    if (empty($category_icon)) {
        $category_icon = 'fa-solid fa-layer-group';
    }

    $stmt = $conn->prepare("UPDATE business_workshops SET title = ?, category_slug = ?, category_name = ?, category_icon = ?, duration = ?, target_audience = ?, key_topics = ?, expected_outcome = ? WHERE id = ?");
    $stmt->bind_param("ssssssssi", $title, $category_slug, $category_name, $category_icon, $duration, $target_audience, $key_topics, $expected_outcome, $id);

    if ($stmt->execute()) {
        $stmt->close();
        reorderWorkshops($conn, $id, $display_order > 0 ? $display_order : 1);
        header("Location: manage_workshops.php?msg=updated");
        exit;
    } else {
        $msg = "<div class='alert alert-danger'>Error updating workshop: " . htmlspecialchars($conn->error) . "</div>";
    }
}

// 4. Toggle Publish
if (isset($_GET['toggle_publish']) && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $status = (int) $_GET['toggle_publish'];
    $new_status = ($status == 1) ? 0 : 1;
    $stmt = $conn->prepare("UPDATE business_workshops SET is_published = ? WHERE id = ?");
    $stmt->bind_param("ii", $new_status, $id);
    $stmt->execute();

    if ((!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || isset($_GET['ajax'])) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'new_status' => $new_status]);
        exit;
    }

    header("Location: manage_workshops.php?msg=updated");
    exit;
}

// 5. Soft Delete Workshop (Move to Trash)
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $conn->query("UPDATE business_workshops SET deleted_at = NOW() WHERE id = $id");
    normalizeWorkshopOrders($conn);
    header("Location: manage_workshops.php?msg=trashed");
    exit;
}

// 6. Restore from Trash
if (isset($_GET['restore'])) {
    $id = (int) $_GET['restore'];
    $conn->query("UPDATE business_workshops SET deleted_at = NULL WHERE id = $id");
    // Place restored item at the end of active workshops sequence
    $cnt_res = $conn->query("SELECT COUNT(*) as total FROM business_workshops WHERE deleted_at IS NULL");
    $new_pos = $cnt_res ? (int)$cnt_res->fetch_assoc()['total'] : 1;
    reorderWorkshops($conn, $id, $new_pos);
    header("Location: manage_workshops.php?view=trash&msg=restored");
    exit;
}

// 7. Force / Permanent Delete
if (isset($_GET['force_delete'])) {
    $id = (int) $_GET['force_delete'];
    $conn->query("DELETE FROM business_workshops WHERE id = $id");
    header("Location: manage_workshops.php?view=trash&msg=deleted_permanent");
    exit;
}

// Edit Mode Check
$edit_mode = false;
$edit_data = null;
if (isset($_GET['edit'])) {
    $edit_id = (int) $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM business_workshops WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $edit_res = $stmt->get_result();
    if ($edit_res && $edit_res->num_rows > 0) {
        $edit_mode = true;
        $edit_data = $edit_res->fetch_assoc();
    }
}

// Tab View Mode
$view = isset($_GET['view']) && $_GET['view'] === 'trash' ? 'trash' : 'active';

// Counts for tabs
$count_active = 0;
$c_res = $conn->query("SELECT COUNT(*) as total FROM business_workshops WHERE deleted_at IS NULL");
if ($c_res && $crow = $c_res->fetch_assoc()) {
    $count_active = (int) $crow['total'];
}

$count_trash = 0;
$t_res = $conn->query("SELECT COUNT(*) as total FROM business_workshops WHERE deleted_at IS NOT NULL");
if ($t_res && $trow = $t_res->fetch_assoc()) {
    $count_trash = (int) $trow['total'];
}

// Auto-heal duplicate or non-sequential orders if present
$dup_check = $conn->query("SELECT display_order, COUNT(*) as cnt FROM business_workshops WHERE deleted_at IS NULL GROUP BY display_order HAVING cnt > 1");
if ($dup_check && $dup_check->num_rows > 0) {
    normalizeWorkshopOrders($conn);
}

// Fetch workshops according to view mode
if ($view === 'trash') {
    $workshops = $conn->query("SELECT * FROM business_workshops WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC");
} else {
    $workshops = $conn->query("SELECT * FROM business_workshops WHERE deleted_at IS NULL ORDER BY display_order ASC, id ASC");
}
$total_workshops = $workshops ? $workshops->num_rows : 0;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Practical Workshops - IB Admin</title>

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
        .category-preset-badge {
            cursor: pointer;
            display: inline-block;
            margin-right: 5px;
            margin-bottom: 5px;
            padding: 4px 10px;
            font-size: 12px;
            background: #e9ecef;
            border-radius: 20px;
            transition: all 0.2s;
            border: 1px solid #ced4da;
            text-decoration: none;
            color: #495057;
        }

        .category-preset-badge:hover {
            background: #d4af37;
            color: #091a32;
            border-color: #d4af37;
        }

        .workshop-preview-num {
            display: inline-flex;
            width: 34px;
            height: 34px;
            background: #091a32;
            color: #d4af37;
            font-weight: 700;
            border-radius: 50%;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }

        .topics-preview-list {
            margin: 0;
            padding-left: 18px;
            font-size: 12px;
            color: #555;
        }

        .topics-preview-list li {
            margin-bottom: 3px;
        }

        .badge-cat {
            background-color: rgba(9, 26, 50, 0.08);
            color: #091a32;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 12px;
            display: inline-block;
        }

        .btn-order-arrow {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 20px;
            height: 15px;
            font-size: 10px;
            background: #f1f4f8;
            color: #091a32;
            border-radius: 3px;
            border: 1px solid #d0d7de;
            text-decoration: none;
            transition: all 0.15s ease;
            line-height: 1;
        }

        .btn-order-arrow:hover {
            background: #d4af37;
            color: #091a32;
            border-color: #d4af37;
        }

        .btn-order-arrow.disabled {
            opacity: 0.25;
            cursor: not-allowed;
            pointer-events: none;
            background: #e9ecef;
            border-color: #dee2e6;
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

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 style="color: #091a32; font-weight: 700; margin: 0;">Practical Business Workshops</h2>
                    <a href="../training-programs.php#services" target="_blank" class="btn btn-outline-dark btn-sm">
                        👁️ View Live Section
                    </a>
                </div>

                <?php echo $msg; ?>

                <?php if ($view === 'active' || $edit_mode): ?>
                <!-- Add / Edit Form Card -->
                <div class="card-custom" style="<?php echo $edit_mode ? 'border: 2px solid #d4af37;' : ''; ?>">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="m-0" style="color: #091a32; font-weight: 700;">
                            <?php echo $edit_mode ? '✏️ Edit Workshop Details (ID #' . $edit_data['id'] . ')' : '➕ Add New Workshop'; ?>
                        </h5>
                        <?php if ($edit_mode): ?>
                            <a href="manage_workshops.php" class="btn btn-sm btn-outline-secondary">Clear Edit Mode</a>
                        <?php endif; ?>
                    </div>

                    <form action="manage_workshops.php" method="POST" class="modern-form">
                        <?php if ($edit_mode): ?>
                            <input type="hidden" name="workshop_id" value="<?php echo (int) $edit_data['id']; ?>">
                        <?php endif; ?>

                        <!-- Quick Presets Box -->
                        <div class="preset-box mb-4">
                            <label class="form-label text-muted small fw-bold mb-2 d-block"><i
                                    class="fa-solid fa-wand-magic-sparkles text-warning"></i> Quick Category Presets
                                (Click to autofill):</label>
                            <div class="d-flex flex-wrap gap-2">
                                <span class="category-preset-badge"
                                    onclick="applyPreset('Strategy & Management', 'strategy', 'fa-solid fa-layer-group')"><i
                                        class="fa-solid fa-layer-group"></i> Strategy & Management</span>
                                <span class="category-preset-badge"
                                    onclick="applyPreset('Finance & Costing', 'finance', 'fa-solid fa-chart-pie')"><i
                                        class="fa-solid fa-chart-pie"></i> Finance & Costing</span>
                                <span class="category-preset-badge"
                                    onclick="applyPreset('Manufacturing', 'manufacturing', 'fa-solid fa-industry')"><i
                                        class="fa-solid fa-industry"></i> Manufacturing</span>
                                <span class="category-preset-badge"
                                    onclick="applyPreset('Sales & Marketing', 'sales', 'fa-solid fa-chart-line')"><i
                                        class="fa-solid fa-chart-line"></i> Sales & Marketing</span>
                                <span class="category-preset-badge"
                                    onclick="applyPreset('Export & Trade', 'export', 'fa-solid fa-globe')"><i
                                        class="fa-solid fa-globe"></i> Export & Trade</span>
                                <span class="category-preset-badge"
                                    onclick="applyPreset('Digital & AI', 'digital', 'fa-solid fa-robot')"><i
                                        class="fa-solid fa-robot"></i> Digital & AI</span>
                            </div>
                        </div>

                        <!-- Section 1: Basic Information -->
                        <div class="form-section mb-4">
                            <h6 class="section-title"><i class="fa-solid fa-circle-info me-2"></i>1. Basic Information
                            </h6>
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label fw-bold">Workshop Title *</label>
                                    <input type="text" name="title" class="form-control"
                                        placeholder="E.g. Business Health Check & Performance Improvement"
                                        value="<?php echo $edit_mode ? htmlspecialchars($edit_data['title']) : ''; ?>"
                                        required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Category Display Name *</label>
                                    <input type="text" id="category_name" name="category_name" class="form-control"
                                        placeholder="E.g. Strategy & Management"
                                        value="<?php echo $edit_mode ? htmlspecialchars($edit_data['category_name']) : 'Strategy & Management'; ?>"
                                        required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Category Filter Slug *</label>
                                    <select id="category_slug" name="category_slug" class="form-select" required>
                                        <option value="strategy" <?php echo ($edit_mode && $edit_data['category_slug'] == 'strategy') ? 'selected' : ''; ?>>strategy
                                            (Strategy & Management)</option>
                                        <option value="finance" <?php echo ($edit_mode && $edit_data['category_slug'] == 'finance') ? 'selected' : ''; ?>>finance
                                            (Finance & Costing)</option>
                                        <option value="manufacturing" <?php echo ($edit_mode && $edit_data['category_slug'] == 'manufacturing') ? 'selected' : ''; ?>>
                                            manufacturing (Manufacturing)</option>
                                        <option value="sales" <?php echo ($edit_mode && $edit_data['category_slug'] == 'sales') ? 'selected' : ''; ?>>sales (Sales &
                                            Marketing)</option>
                                        <option value="export" <?php echo ($edit_mode && $edit_data['category_slug'] == 'export') ? 'selected' : ''; ?>>export (Export
                                            & Trade)</option>
                                        <option value="digital" <?php echo ($edit_mode && $edit_data['category_slug'] == 'digital') ? 'selected' : ''; ?>>digital
                                            (Digital & AI)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Section 2: Visuals & Settings -->
                        <div class="form-section mb-4">
                            <h6 class="section-title"><i class="fa-solid fa-palette me-2"></i>2. Visuals & Settings</h6>
                            <div class="row">
                                <div class="col-md-12 mb-4">
                                    <?php
                                    $curr_cat_icon = $edit_mode ? $edit_data['category_icon'] : 'fa-solid fa-layer-group';
                                    echo renderAdminIconPicker('category_icon', $curr_cat_icon, 'Category Icon Class', ['default' => 'fa-solid fa-layer-group']);
                                    ?>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Duration Badge</label>
                                    <input type="text" name="duration" class="form-control"
                                        placeholder="1-Day Practical Workshop"
                                        value="<?php echo $edit_mode ? htmlspecialchars($edit_data['duration']) : '1-Day Practical Workshop'; ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Display Order</label>
                                    <input type="number" name="display_order" class="form-control" min="1" max="99"
                                        value="<?php echo $edit_mode ? (int) $edit_data['display_order'] : (int) ($total_workshops + 1); ?>">
                                    <small class="text-muted d-block mt-1"><i class="fa-solid fa-circle-info me-1 text-primary"></i> Setting this order will automatically shift other workshops without creating duplicates.</small>
                                </div>
                            </div>
                        </div>

                        <!-- Section 3: Workshop Content -->
                        <div class="form-section mb-4">
                            <h6 class="section-title"><i class="fa-solid fa-file-lines me-2"></i>3. Workshop Content
                            </h6>
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label fw-bold">Target Audience *</label>
                                    <textarea name="target_audience" class="form-control" rows="2"
                                        placeholder="E.g. Business owners, managing directors..."
                                        required><?php echo $edit_mode ? htmlspecialchars($edit_data['target_audience']) : ''; ?></textarea>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Key Topics Covered *</label>
                                    <textarea name="key_topics" class="form-control" rows="4"
                                        placeholder="Topic 1&#10;Topic 2&#10;Topic 3"
                                        required><?php echo $edit_mode ? htmlspecialchars($edit_data['key_topics']) : ''; ?></textarea>
                                    <small class="text-muted">One topic per line.</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Expected Outcome *</label>
                                    <textarea name="expected_outcome" class="form-control" rows="4"
                                        placeholder="E.g. Diagnose business problems..."
                                        required><?php echo $edit_mode ? htmlspecialchars($edit_data['expected_outcome']) : ''; ?></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-end align-items-center mt-4 pt-3 border-top">
                            <?php if ($edit_mode): ?>
                                <a href="manage_workshops.php" class="btn btn-secondary px-4 py-2 me-3">Cancel</a>
                                <button type="submit" name="update_workshop" class="btn btn-custom px-5 py-2"><i
                                        class="fa-solid fa-floppy-disk me-2"></i> Update Workshop</button>
                            <?php else: ?>
                                <button type="submit" name="add_workshop" class="btn btn-custom px-5 py-2"><i
                                        class="fa-solid fa-plus me-2"></i> Add Workshop</button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
                <?php endif; ?>

                <!-- List of Workshops -->
                <div class="card-custom">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                        <div class="admin-tab-nav">
                            <a href="manage_workshops.php" class="admin-tab-nav-btn <?php echo $view === 'active' ? 'active' : ''; ?>">
                                <i class="fa-solid fa-list-check"></i> Active Workshops <span class="tab-count-badge"><?php echo $count_active; ?></span>
                            </a>
                            <a href="manage_workshops.php?view=trash" class="admin-tab-nav-btn tab-trash <?php echo $view === 'trash' ? 'active' : ''; ?>">
                                <i class="fa-solid fa-trash-can"></i> Trash <span class="tab-count-badge"><?php echo $count_trash; ?></span>
                            </a>
                        </div>
                        <?php if ($view === 'trash'): ?>
                            <a href="manage_workshops.php" class="btn btn-sm btn-outline-secondary">
                                <i class="fa-solid fa-arrow-left me-1"></i> Back to Active Workshops
                            </a>
                        <?php endif; ?>
                    </div>

                    <?php if ($view === 'trash'): ?>
                    <!-- Trash Workshops Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead style="background-color: #7f1d1d; color: #fff;">
                                <tr>
                                    <th style="width: 60px; text-align: center;">No.</th>
                                    <th style="width: 280px;">Workshop Title & Details</th>
                                    <th style="width: 220px;">Target Audience</th>
                                    <th>Expected Outcome</th>
                                    <th style="width: 190px; text-align: center;">Deleted On</th>
                                    <th style="width: 140px; text-align: center;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($workshops->num_rows > 0):
                                    $t_counter = 1;
                                    while ($row = $workshops->fetch_assoc()):
                                        ?>
                                        <tr style="background: rgba(239, 68, 68, 0.02);">
                                            <td class="text-center fw-bold text-muted">
                                                <?php echo str_pad($t_counter, 2, '0', STR_PAD_LEFT); ?>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-dark" style="font-size: 14px; margin-bottom: 4px;">
                                                    <?php echo htmlspecialchars($row['title']); ?>
                                                </div>
                                                <div class="d-flex flex-wrap gap-1 align-items-center">
                                                    <span class="badge-cat">
                                                        <i class="<?php echo htmlspecialchars($row['category_icon']); ?> text-muted me-1"></i>
                                                        <?php echo htmlspecialchars($row['category_name']); ?>
                                                    </span>
                                                    <span class="badge bg-light text-secondary border">
                                                        <i class="fa-regular fa-clock me-1"></i><?php echo htmlspecialchars($row['duration']); ?>
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                <small class="text-muted"><?php echo nl2br(htmlspecialchars($row['target_audience'])); ?></small>
                                            </td>
                                            <td>
                                                <small class="text-muted"><?php echo nl2br(htmlspecialchars($row['expected_outcome'])); ?></small>
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
                                                    <a href="manage_workshops.php?restore=<?php echo $row['id']; ?>"
                                                        class="btn-action btn-action-restore" title="Restore Workshop to Active List">
                                                        <i class="fa-solid fa-rotate-left"></i>
                                                    </a>
                                                    <!-- Force Delete -->
                                                    <a href="manage_workshops.php?force_delete=<?php echo $row['id']; ?>"
                                                        class="btn-action btn-action-force-delete" title="Permanently Delete Workshop"
                                                        data-item-name="<?php echo htmlspecialchars($row['title']); ?>">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php
                                        $t_counter++;
                                    endwhile;
                                else:
                                    ?>
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
                    <!-- Active Workshops Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead style="background-color: #091a32; color: #fff;">
                                <tr>
                                    <th style="width: 85px; text-align: center;">Order</th>
                                    <th style="width: 280px;">Workshop Title & Details</th>
                                    <th style="width: 220px;">Target Audience</th>
                                    <th style="width: 250px;">Key Topics Covered</th>
                                    <th>Expected Outcome</th>
                                    <th style="width: 100px; text-align: center;">Status</th>
                                    <th style="width: 150px; text-align: center;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($workshops->num_rows > 0):
                                    $counter = 1;
                                    while ($row = $workshops->fetch_assoc()):
                                        $topics_arr = preg_split('/\r\n|\r|\n/', trim($row['key_topics']));
                                        ?>
                                        <tr>
                                            <!-- Order Num & Quick Move Arrows -->
                                            <td class="text-center" style="white-space: nowrap;">
                                                <div class="d-inline-flex align-items-center gap-1">
                                                    <span class="workshop-preview-num"><?php echo str_pad($row['display_order'] > 0 ? $row['display_order'] : $counter, 2, '0', STR_PAD_LEFT); ?></span>
                                                    <div class="d-inline-flex flex-column gap-1 ms-1">
                                                        <?php if ($counter > 1): ?>
                                                            <a href="manage_workshops.php?action=move_up&id=<?php echo $row['id']; ?>" class="btn-order-arrow" title="Move Up (to position <?php echo $counter - 1; ?>)">
                                                                <i class="fa-solid fa-chevron-up"></i>
                                                            </a>
                                                        <?php else: ?>
                                                            <span class="btn-order-arrow disabled" title="Already at top"><i class="fa-solid fa-chevron-up"></i></span>
                                                        <?php endif; ?>

                                                        <?php if ($counter < $total_workshops): ?>
                                                            <a href="manage_workshops.php?action=move_down&id=<?php echo $row['id']; ?>" class="btn-order-arrow" title="Move Down (to position <?php echo $counter + 1; ?>)">
                                                                <i class="fa-solid fa-chevron-down"></i>
                                                            </a>
                                                        <?php else: ?>
                                                            <span class="btn-order-arrow disabled" title="Already at bottom"><i class="fa-solid fa-chevron-down"></i></span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>

                                            <!-- Title & Meta -->
                                            <td>
                                                <div class="fw-bold text-dark" style="font-size: 14px; margin-bottom: 4px;">
                                                    <?php echo htmlspecialchars($row['title']); ?>
                                                </div>
                                                <div class="d-flex flex-wrap gap-1 align-items-center">
                                                    <span class="badge-cat">
                                                        <i
                                                            class="<?php echo htmlspecialchars($row['category_icon']); ?> text-muted me-1"></i>
                                                        <?php echo htmlspecialchars($row['category_name']); ?>
                                                    </span>
                                                    <span class="badge bg-light text-secondary border">
                                                        <i
                                                            class="fa-regular fa-clock me-1"></i><?php echo htmlspecialchars($row['duration']); ?>
                                                    </span>
                                                    <span class="badge bg-secondary-subtle text-dark border">
                                                        slug: <?php echo htmlspecialchars($row['category_slug']); ?>
                                                    </span>
                                                </div>
                                            </td>

                                            <!-- Audience -->
                                            <td>
                                                <small
                                                    class="text-muted"><?php echo nl2br(htmlspecialchars($row['target_audience'])); ?></small>
                                            </td>

                                            <!-- Key Topics -->
                                            <td>
                                                <ul class="topics-preview-list">
                                                    <?php
                                                    foreach ($topics_arr as $topic_line):
                                                        $topic_line = trim($topic_line);
                                                        if (!empty($topic_line)):
                                                            $topic_line = ltrim($topic_line, "-*• \t");
                                                            ?>
                                                             <li><?php echo htmlspecialchars($topic_line); ?></li>
                                                            <?php
                                                        endif;
                                                    endforeach;
                                                    ?>
                                                </ul>
                                            </td>

                                            <!-- Outcome -->
                                            <td>
                                                <small
                                                    class="text-muted"><?php echo nl2br(htmlspecialchars($row['expected_outcome'])); ?></small>
                                            </td>

                                            <!-- Status -->
                                            <td class="text-center">
                                                <?php if ($row['is_published'] == 1): ?>
                                                    <span class="badge-published">Published</span>
                                                <?php else: ?>
                                                    <span class="badge-hidden">Hidden</span>
                                                <?php endif; ?>
                                            </td>

                                            <!-- Actions (Icon Only) -->
                                            <td class="text-center">
                                                <div class="action-btn-group justify-content-center">
                                                    <!-- Edit -->
                                                    <a href="manage_workshops.php?edit=<?php echo $row['id']; ?>"
                                                        class="btn-action btn-action-edit" title="Edit Workshop">
                                                        <i class="fa-solid fa-pen-to-square"></i>
                                                    </a>

                                                    <!-- Toggle Publish/Hide -->
                                                    <a href="manage_workshops.php?toggle_publish=<?php echo $row['is_published']; ?>&id=<?php echo $row['id']; ?>"
                                                        class="btn-action <?php echo ($row['is_published'] == 1) ? 'btn-action-hide' : 'btn-action-publish'; ?>"
                                                        title="<?php echo ($row['is_published'] == 1) ? 'Hide / Unpublish Workshop' : 'Show / Publish Workshop'; ?>">
                                                        <i
                                                            class="fa-solid <?php echo ($row['is_published'] == 1) ? 'fa-eye-slash' : 'fa-eye'; ?>"></i>
                                                    </a>

                                                    <!-- Delete (Soft Delete) -->
                                                    <a href="manage_workshops.php?delete=<?php echo $row['id']; ?>"
                                                        class="btn-action btn-action-delete" title="Move to Trash"
                                                        data-item-name="<?php echo htmlspecialchars($row['title']); ?>">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php
                                        $counter++;
                                    endwhile;
                                else:
                                    ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">
                                            No workshops found in database. Add a new workshop above.
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
        function applyPreset(name, slug, icon) {
            document.getElementById('category_name').value = name;
            document.getElementById('category_slug').value = slug;
            if (typeof updateIconPickerUI === 'function') {
                updateIconPickerUI('category_icon', icon);
            } else {
                document.getElementById('category_icon').value = icon;
            }
        }
    </script>

</body>

</html>