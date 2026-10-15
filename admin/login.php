<?php
session_start();
// Connect to database
include '../db.php'; 

// Redirect to dashboard if already logged in
if(isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true){
    header("Location: dashboard.php");
    exit;
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $password = md5($_POST['password']); 

    // Query admin database
    $sql = "SELECT * FROM admins WHERE username='$username' AND password='$password'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user'] = $username;
        header("Location: dashboard.php");
        exit;
    } else {
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
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- CSS Dependencies -->
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/font-awesome-pro.min.css">
    
    <style>
        :root {
            --primary-navy: #091a32;
            --secondary-navy: #0e2444;
            --accent-gold: #d4af37;
            --gold-hover: #b89326;
            --gold-subtle: rgba(212, 175, 55, 0.15);
            --text-dark: #1e293b;
            --text-muted: #64748b;
            --border-light: #e2e8f0;
            --card-shadow: 0 25px 60px -15px rgba(9, 26, 50, 0.18), 0 0 0 1px rgba(9, 26, 50, 0.05);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: var(--text-dark);
        }

        .login-wrapper {
            width: 100%;
            max-width: 1020px;
            margin: auto;
        }

        .login-card {
            display: flex;
            background: #ffffff;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: var(--card-shadow);
            min-height: 600px;
            position: relative;
        }

        /* ==========================================================================
           Left Side: Company Branding & Logo
           ========================================================================== */
        .login-left-panel {
            flex: 0 0 46%;
            max-width: 46%;
            background: linear-gradient(145deg, #071529 0%, #091a32 50%, #0d284f 100%);
            color: #ffffff;
            padding: 48px 40px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }

        /* Decorative glowing backdrop shapes */
        .login-left-panel::before {
            content: '';
            position: absolute;
            top: -120px;
            left: -120px;
            width: 320px;
            height: 320px;
            background: radial-gradient(circle, rgba(212, 175, 55, 0.22) 0%, rgba(212, 175, 55, 0) 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .login-left-panel::after {
            content: '';
            position: absolute;
            bottom: -100px;
            right: -100px;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(9, 26, 50, 0.9) 0%, rgba(13, 40, 79, 0) 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .brand-content {
            position: relative;
            z-index: 2;
            text-align: center;
            margin-top: auto;
            margin-bottom: auto;
        }

        .brand-logo-frame {
            width: 140px;
            height: 140px;
            background: #ffffff;
            border-radius: 50%;
            margin: 0 auto 24px auto;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 12px;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.35), 0 0 0 4px rgba(212, 175, 55, 0.35);
            border: 2px solid var(--accent-gold);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .brand-logo-frame:hover {
            transform: scale(1.03);
            box-shadow: 0 16px 36px rgba(0, 0, 0, 0.45), 0 0 0 5px rgba(212, 175, 55, 0.55);
        }

        .brand-logo-img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .brand-title {
            font-size: 24px;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: 1.5px;
            margin-bottom: 6px;
        }

        .brand-tagline {
            font-size: 13px;
            color: var(--accent-gold);
            font-weight: 600;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            margin-bottom: 24px;
        }

        .brand-divider {
            width: 60px;
            height: 3px;
            background: var(--accent-gold);
            margin: 0 auto 24px auto;
            border-radius: 3px;
        }

        .brand-features-list {
            text-align: left;
            margin-top: 10px;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .brand-feature {
            display: flex;
            align-items: center;
            gap: 12px;
            background: rgba(255, 255, 255, 0.06);
            padding: 10px 16px;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(5px);
        }

        .brand-feature i {
            color: var(--accent-gold);
            font-size: 16px;
            width: 20px;
            text-align: center;
        }

        .brand-feature span {
            font-size: 13px;
            color: #e2e8f0;
            font-weight: 500;
        }

        .brand-footer {
            position: relative;
            z-index: 2;
            text-align: center;
            font-size: 11px;
            color: rgba(255, 255, 255, 0.5);
            margin-top: 24px;
            letter-spacing: 0.3px;
        }

        /* ==========================================================================
           Right Side: Login Form
           ========================================================================== */
        .login-right-panel {
            flex: 0 0 54%;
            max-width: 54%;
            padding: 56px 48px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: #ffffff;
        }

        .login-header-group {
            margin-bottom: 28px;
        }

        .login-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: var(--gold-subtle);
            color: #8c6d16;
            font-size: 12px;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: 20px;
            margin-bottom: 12px;
            border: 1px solid rgba(212, 175, 55, 0.3);
        }

        .login-header-title {
            font-size: 28px;
            font-weight: 800;
            color: var(--primary-navy);
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }

        .login-header-desc {
            font-size: 14px;
            color: var(--text-muted);
            line-height: 1.5;
            margin-bottom: 0;
        }

        /* Custom Input Groups */
        .form-group-custom {
            margin-bottom: 20px;
        }

        .form-label-custom {
            font-size: 13px;
            font-weight: 700;
            color: var(--primary-navy);
            margin-bottom: 8px;
            display: block;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon-left {
            position: absolute;
            left: 16px;
            color: var(--text-muted);
            font-size: 15px;
            pointer-events: none;
            transition: color 0.2s ease;
            z-index: 3;
        }

        .input-custom {
            width: 100%;
            height: 52px;
            padding: 12px 16px 12px 46px;
            font-size: 14px;
            font-weight: 500;
            color: var(--text-dark);
            background-color: #f8fafc;
            border: 1.5px solid var(--border-light);
            border-radius: 12px;
            transition: all 0.25s ease;
        }

        .input-custom:focus {
            outline: none;
            background-color: #ffffff;
            border-color: var(--accent-gold);
            box-shadow: 0 0 0 4px rgba(212, 175, 55, 0.18);
        }

        .input-wrapper:focus-within .input-icon-left {
            color: var(--primary-navy);
        }

        /* Password Toggle Button */
        .btn-pw-toggle {
            position: absolute;
            right: 14px;
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            padding: 6px;
            font-size: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.2s ease;
            z-index: 3;
        }

        .btn-pw-toggle:hover {
            color: var(--primary-navy);
        }

        /* Submit Button */
        .btn-submit-custom {
            height: 52px;
            width: 100%;
            background: linear-gradient(135deg, #d4af37 0%, #b89326 100%);
            color: #091a32;
            font-size: 15px;
            font-weight: 700;
            border: none;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            cursor: pointer;
            box-shadow: 0 6px 20px rgba(212, 175, 55, 0.35);
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .btn-submit-custom:hover {
            background: linear-gradient(135deg, #091a32 0%, #0e2444 100%);
            color: #d4af37;
            box-shadow: 0 8px 24px rgba(9, 26, 50, 0.25);
            transform: translateY(-1px);
        }

        .btn-submit-custom:active {
            transform: translateY(1px);
        }

        /* Custom Alert */
        .alert-custom-danger {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 22px;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-6px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .back-home-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s ease;
        }

        .back-home-link:hover {
            color: var(--primary-navy);
            text-decoration: underline;
        }

        /* ==========================================================================
           Mobile & Tablet Responsive Styles
           ========================================================================== */
        @media (max-width: 991px) {
            body {
                padding: 15px 10px;
                background: #f1f5f9;
            }

            .login-card {
                flex-direction: column;
                max-width: 480px;
                min-height: auto;
                border-radius: 20px;
            }

            .login-left-panel {
                flex: 0 0 100%;
                max-width: 100%;
                padding: 36px 24px 28px 24px;
                text-align: center;
            }

            .brand-logo-frame {
                width: 96px;
                height: 96px;
                padding: 10px;
                margin-bottom: 16px;
            }

            .brand-title {
                font-size: 20px;
                margin-bottom: 4px;
            }

            .brand-tagline {
                font-size: 12px;
                margin-bottom: 16px;
            }

            .brand-divider {
                display: none;
            }

            .brand-features-list {
                display: none;
            }

            .brand-footer {
                display: none;
            }

            .login-right-panel {
                flex: 0 0 100%;
                max-width: 100%;
                padding: 32px 24px 36px 24px;
            }

            .login-header-title {
                font-size: 24px;
            }

            .input-custom, .btn-submit-custom {
                height: 48px;
            }
        }

        @media (max-width: 480px) {
            .login-card {
                border-radius: 16px;
            }

            .login-left-panel {
                padding: 28px 18px 22px 18px;
            }

            .brand-logo-frame {
                width: 80px;
                height: 80px;
                padding: 8px;
            }

            .brand-title {
                font-size: 18px;
            }

            .login-right-panel {
                padding: 24px 18px 28px 18px;
            }

            .login-header-title {
                font-size: 22px;
            }
        }
    </style>
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
                <p class="login-header-desc">Please enter your credentials to access the administrative dashboard.</p>
            </div>

            <?php if(!empty($error)): ?>
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
                        <input type="text" id="username" name="username" class="input-custom" placeholder="Enter your username" required autofocus value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                    </div>
                </div>

                <!-- Password Input -->
                <div class="form-group-custom">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label-custom" for="password">Password</label>
                    </div>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-lock-keyhole input-icon-left"></i>
                        <input type="password" id="password" name="password" class="input-custom" placeholder="Enter your password" required>
                        <button type="button" class="btn-pw-toggle" onclick="togglePasswordVisibility()" aria-label="Toggle password visibility" title="Show/Hide password">
                            <i id="pw-toggle-icon" class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- Remember Me & Back to Website -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="rememberMe" style="cursor: pointer;">
                        <label class="form-check-label text-muted" for="rememberMe" style="cursor: pointer; font-size: 13px;">
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
