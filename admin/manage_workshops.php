<?php
session_start();
include '../db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$msg = '';
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'added') {
        $msg = "<div class='alert alert-success alert-dismissible fade show'>Workshop added successfully!</div>";
    } elseif ($_GET['msg'] == 'updated') {
        $msg = "<div class='alert alert-success alert-dismissible fade show'>Workshop updated successfully!</div>";
    } elseif ($_GET['msg'] == 'deleted') {
        $msg = "<div class='alert alert-success alert-dismissible fade show'>Workshop deleted successfully!</div>";
    }
}

// 1. Add Workshop
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
        header("Location: manage_workshops.php?msg=added");
        exit;
    } else {
        $msg = "<div class='alert alert-danger'>Error adding workshop: " . htmlspecialchars($conn->error) . "</div>";
    }
}

// 2. Update Workshop
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

    $stmt = $conn->prepare("UPDATE business_workshops SET title = ?, category_slug = ?, category_name = ?, category_icon = ?, duration = ?, target_audience = ?, key_topics = ?, expected_outcome = ?, display_order = ? WHERE id = ?");
    $stmt->bind_param("ssssssssii", $title, $category_slug, $category_name, $category_icon, $duration, $target_audience, $key_topics, $expected_outcome, $display_order, $id);

    if ($stmt->execute()) {
        header("Location: manage_workshops.php?msg=updated");
        exit;
    } else {
        $msg = "<div class='alert alert-danger'>Error updating workshop: " . htmlspecialchars($conn->error) . "</div>";
    }
}

// 3. Toggle Publish
if (isset($_GET['toggle_publish']) && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $status = (int) $_GET['toggle_publish'];
    $new_status = ($status == 1) ? 0 : 1;
    $stmt = $conn->prepare("UPDATE business_workshops SET is_published = ? WHERE id = ?");
    $stmt->bind_param("ii", $new_status, $id);
    $stmt->execute();
    header("Location: manage_workshops.php?msg=updated");
    exit;
}

// 4. Delete Workshop
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM business_workshops WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: manage_workshops.php?msg=deleted");
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

// Fetch all workshops
$workshops = $conn->query("SELECT * FROM business_workshops ORDER BY display_order ASC, id ASC");
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

                    <form action="manage_workshops.php" method="POST">
                        <?php if ($edit_mode): ?>
                            <input type="hidden" name="workshop_id" value="<?php echo (int) $edit_data['id']; ?>">
                        <?php endif; ?>

                        <!-- Presets Helper -->
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold">Quick Category Presets (Click to autofill
                                category info):</label>
                            <div>
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
                                    onclick="applyPreset('Sales & Distribution', 'sales', 'fa-solid fa-map-location-dot')"><i
                                        class="fa-solid fa-map-location-dot"></i> Sales & Distribution</span>
                                <span class="category-preset-badge"
                                    onclick="applyPreset('Export & Trade', 'export', 'fa-solid fa-globe')"><i
                                        class="fa-solid fa-globe"></i> Export & Trade</span>
                                <span class="category-preset-badge"
                                    onclick="applyPreset('Digital & AI', 'digital', 'fa-solid fa-robot')"><i
                                        class="fa-solid fa-robot"></i> Digital & AI</span>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Workshop Title -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Workshop Title *</label>
                                <input type="text" name="title" class="form-control"
                                    placeholder="E.g. Business Health Check & Performance Improvement"
                                    value="<?php echo $edit_mode ? htmlspecialchars($edit_data['title']) : ''; ?>"
                                    required>
                            </div>

                            <!-- Category Name -->
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-bold">Category Display Name *</label>
                                <input type="text" id="category_name" name="category_name" class="form-control"
                                    placeholder="E.g. Strategy & Management"
                                    value="<?php echo $edit_mode ? htmlspecialchars($edit_data['category_name']) : 'Strategy & Management'; ?>"
                                    required>
                            </div>

                            <!-- Category Slug -->
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-bold">Category Filter Slug *</label>
                                <select id="category_slug" name="category_slug" class="form-select" required>
                                    <option value="strategy" <?php echo ($edit_mode && $edit_data['category_slug'] == 'strategy') ? 'selected' : ''; ?>>strategy
                                        (Strategy & Management)</option>
                                    <option value="finance" <?php echo ($edit_mode && $edit_data['category_slug'] == 'finance') ? 'selected' : ''; ?>>finance (Finance &
                                        Costing)</option>
                                    <option value="manufacturing" <?php echo ($edit_mode && $edit_data['category_slug'] == 'manufacturing') ? 'selected' : ''; ?>>
                                        manufacturing (Manufacturing)</option>
                                    <option value="sales" <?php echo ($edit_mode && $edit_data['category_slug'] == 'sales') ? 'selected' : ''; ?>>sales (Sales &
                                        Distribution / Marketing)</option>
                                    <option value="export" <?php echo ($edit_mode && $edit_data['category_slug'] == 'export') ? 'selected' : ''; ?>>export (Export &
                                        Trade)</option>
                                    <option value="digital" <?php echo ($edit_mode && $edit_data['category_slug'] == 'digital') ? 'selected' : ''; ?>>digital (Digital &
                                        AI)</option>
                                </select>
                                <small class="text-muted" style="font-size: 11px;">Used by interactive filter
                                    tabs</small>
                            </div>

                            <!-- Category Icon -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Category Icon Class (FontAwesome)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i id="icon-preview"
                                            class="<?php echo $edit_mode ? htmlspecialchars($edit_data['category_icon']) : 'fa-solid fa-layer-group'; ?>"></i></span>
                                    <input type="text" id="category_icon" name="category_icon" class="form-control"
                                        placeholder="E.g. fa-solid fa-layer-group"
                                        value="<?php echo $edit_mode ? htmlspecialchars($edit_data['category_icon']) : 'fa-solid fa-layer-group'; ?>"
                                        oninput="updateIconPreview(this.value)">
                                </div>
                            </div>

                            <!-- Duration -->
                            <div class="col-md-5 mb-3">
                                <label class="form-label fw-bold">Duration Badge</label>
                                <input type="text" name="duration" class="form-control"
                                    placeholder="1-Day Practical Workshop"
                                    value="<?php echo $edit_mode ? htmlspecialchars($edit_data['duration']) : '1-Day Practical Workshop'; ?>">
                            </div>

                            <!-- Display Order -->
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-bold">Display Order</label>
                                <input type="number" name="display_order" class="form-control" min="1" max="99"
                                    value="<?php echo $edit_mode ? (int) $edit_data['display_order'] : (int) ($workshops->num_rows + 1); ?>">
                                <small class="text-muted" style="font-size: 11px;">Determines drawer position (01, 02,
                                    etc.)</small>
                            </div>

                            <!-- Target Audience -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Target Audience *</label>
                                <textarea name="target_audience" class="form-control" rows="4"
                                    placeholder="E.g. Business owners, managing directors, senior managers, and cross-functional leadership teams."
                                    required><?php echo $edit_mode ? htmlspecialchars($edit_data['target_audience']) : ''; ?></textarea>
                            </div>

                            <!-- Key Topics -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Key Topics Covered * (One topic per line)</label>
                                <textarea name="key_topics" class="form-control" rows="4"
                                    placeholder="Business performance assessment&#10;Sales and profit analysis&#10;90-day improvement planning"
                                    required><?php echo $edit_mode ? htmlspecialchars($edit_data['key_topics']) : ''; ?></textarea>
                                <small class="text-muted" style="font-size: 11px;">Each line will automatically be
                                    formatted with a green checkmark bullet point</small>
                            </div>

                            <!-- Expected Outcome -->
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-bold">Expected Outcome *</label>
                                <textarea name="expected_outcome" class="form-control" rows="2"
                                    placeholder="E.g. Diagnose business problems and create a prioritized 90-day actionable improvement plan."
                                    required><?php echo $edit_mode ? htmlspecialchars($edit_data['expected_outcome']) : ''; ?></textarea>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="col-md-12 text-end">
                                <?php if ($edit_mode): ?>
                                    <button type="submit" name="update_workshop" class="btn btn-custom px-4 py-2">💾 Update
                                        Workshop</button>
                                    <a href="manage_workshops.php" class="btn btn-secondary px-4 py-2 ms-2">Cancel</a>
                                <?php else: ?>
                                    <button type="submit" name="add_workshop" class="btn btn-custom px-4 py-2">➕ Add
                                        Workshop</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- List of Workshops -->
                <div class="card-custom">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="m-0" style="color: #091a32; font-weight: 700;">Existing Workshops
                            (<?php echo $workshops->num_rows; ?>)</h5>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead style="background-color: #091a32; color: #fff;">
                                <tr>
                                    <th style="width: 60px; text-align: center;">#</th>
                                    <th style="width: 280px;">Workshop Title & Details</th>
                                    <th style="width: 220px;">Target Audience</th>
                                    <th style="width: 250px;">Key Topics Covered</th>
                                    <th>Expected Outcome</th>
                                    <th style="width: 100px; text-align: center;">Status</th>
                                    <th style="width: 190px; text-align: center;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($workshops->num_rows > 0):
                                    $counter = 1;
                                    while ($row = $workshops->fetch_assoc()):
                                        $topics_arr = preg_split('/\r\n|\r|\n/', trim($row['key_topics']));
                                        ?>
                                        <tr>
                                            <!-- Order Num -->
                                            <td class="text-center">
                                                <span
                                                    class="workshop-preview-num"><?php echo str_pad($row['display_order'] > 0 ? $row['display_order'] : $counter, 2, '0', STR_PAD_LEFT); ?></span>
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

                                                    <!-- Delete -->
                                                    <a href="manage_workshops.php?delete=<?php echo $row['id']; ?>"
                                                        class="btn-action btn-action-delete" title="Delete Workshop"
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
            document.getElementById('category_icon').value = icon;
            updateIconPreview(icon);
        }

        function updateIconPreview(iconClass) {
            var preview = document.getElementById('icon-preview');
            if (preview) {
                preview.className = iconClass ? iconClass : 'fa-solid fa-layer-group';
            }
        }
    </script>
</body>

</html>