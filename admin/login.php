<?php
session_start();
// Connect to database
include '../db.php';

// Redirect to dashboard if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $entered_password = $_POST['password'];

    $sql = "SELECT * FROM admins WHERE username='$username'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $user_row = $result->fetch_assoc();
        
        if (password_verify($entered_password, $user_row['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_user'] = $user_row['username'];
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Invalid Username or Password. Please try again.";
        }
    } else {
        // Username එක වැරදි නම්
        $error = "Invalid Username or Password. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - IB Consultants</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- CSS Dependencies -->
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/font-awesome-pro.min.css">

    <link rel="stylesheet" href="login.css">

    <!-- Place favicon.ico in the root directory -->
    <link rel="shortcut icon" type="image/x-icon" href="../assets/images/logos/logo-2.webp">
</head>

<body>

    <div class="login-wrapper">
        <div class="login-card">

            <!-- Left Side: Company Logo & Branding -->
            <div class="login-left-panel">
                <div class="brand-content">
                    <div class="brand-logo-frame">
                        <img src="../assets/images/logos/logo-2.webp" alt="IB Consultants Logo" class="brand-logo-img">
                    </div>
                    <h1 class="brand-title">IB CONSULTANTS</h1>
                    <p class="brand-tagline">Business &amp; Management Consultants</p>
                    <div class="brand-divider"></div>

                    <div class="brand-features-list">
                        <div class="brand-feature">
                            <i class="fa-solid fa-shield-check"></i>
                            <span>Secure Administrative Portal</span>
                        </div>
                        <div class="brand-feature">
                            <i class="fa-solid fa-chart-pie-simple"></i>
                            <span>Programs &amp; Workshops Control</span>
                        </div>
                        <div class="brand-feature">
                            <i class="fa-solid fa-sliders"></i>
                            <span>Client Reviews &amp; Hero Management</span>
                        </div>
                    </div>
                </div>

                <div class="brand-footer">
                    &copy; <?php echo date('Y'); ?> IB Consultants. Authorized Personnel Only.
                </div>
            </div>

            <!-- Right Side: Login Input Fields -->
            <div class="login-right-panel">
                <div class="login-header-group">
                    <span class="login-badge">
                        <i class="fa-solid fa-lock"></i> Admin Access
                    </span>
                    <h2 class="login-header-title">Welcome Back</h2>
                    <p class="login-header-desc">Please enter your credentials to access the administrative dashboard.
                    </p>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert-custom-danger" role="alert">
                        <i class="fa-solid fa-circle-exclamation fs-5"></i>
                        <div><?php echo htmlspecialchars($error); ?></div>
                    </div>
                <?php endif; ?>

                <form action="login.php" method="POST" autocomplete="off">
                    <!-- Username Input -->
                    <div class="form-group-custom">
                        <label class="form-label-custom" for="username">Username</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-user input-icon-left"></i>
                            <input type="text" id="username" name="username" class="input-custom"
                                placeholder="Enter your username" required autofocus
                                value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                        </div>
                    </div>

                    <!-- Password Input -->
                    <div class="form-group-custom">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label-custom" for="password">Password</label>
                        </div>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-lock-keyhole input-icon-left"></i>
                            <input type="password" id="password" name="password" class="input-custom"
                                placeholder="Enter your password" required>
                            <button type="button" class="btn-pw-toggle" onclick="togglePasswordVisibility()"
                                aria-label="Toggle password visibility" title="Show/Hide password">
                                <i id="pw-toggle-icon" class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Remember Me & Back to Website -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="rememberMe" style="cursor: pointer;">
                            <label class="form-check-label text-muted" for="rememberMe"
                                style="cursor: pointer; font-size: 13px;">
                                Remember this device
                            </label>
                        </div>
                        <a href="../index.php" class="back-home-link" title="Return to public site">
                            <i class="fa-solid fa-house"></i> View Website
                        </a>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn-submit-custom">
                        <span>Log In to Dashboard</span>
                        <i class="fa-solid fa-arrow-right-to-bracket"></i>
                    </button>
                </form>
            </div>

        </div>
    </div>

    <script>
        function togglePasswordVisibility() {
            var pwInput = document.getElementById('password');
            var pwIcon = document.getElementById('pw-toggle-icon');
            if (pwInput.type === 'password') {
                pwInput.type = 'text';
                pwIcon.classList.remove('fa-eye');
                pwIcon.classList.add('fa-eye-slash');
            } else {
                pwInput.type = 'password';
                pwIcon.classList.remove('fa-eye-slash');
                pwIcon.classList.add('fa-eye');
            }
        }
    </script>

</body>

</html>