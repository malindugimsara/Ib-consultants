<?php
session_start();
include '../db.php';

// Check if admin user already exists
$check = $conn->query("SELECT id FROM admins LIMIT 1");
$admin_exists = ($check && $check->num_rows > 0);

// If an admin already exists and requester is not logged in as admin, deny access for security
if ($admin_exists && (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true)) {
    http_response_code(403);
    die("<div style='font-family:sans-serif;max-width:520px;margin:60px auto;padding:25px;border:1px solid #dc3545;border-radius:8px;background:#fff5f5;color:#721c24;box-shadow:0 4px 12px rgba(0,0,0,0.08);'>
        <h3 style='margin-top:0;color:#c82333;'>Access Restricted (Security Protection)</h3>
        <p>An administrator account is already configured. For security reasons, setup/reset via this public script is permanently locked.</p>
        <p style='margin-bottom:0;'><a href='login.php' style='display:inline-block;margin-top:10px;padding:8px 16px;background:#091a32;color:#fff;text-decoration:none;border-radius:4px;'>Go to Admin Login</a></p>
    </div>");
}

$username = 'admin';
$password = 'admin123';
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$check_user = $conn->prepare("SELECT id FROM admins WHERE username = ?");
$check_user->bind_param("s", $username);
$check_user->execute();
$check_result = $check_user->get_result();

if ($check_result && $check_result->num_rows > 0) {
    // Update existing admin password to ensure login works (only possible if logged in)
    $update = $conn->prepare("UPDATE admins SET password = ? WHERE username = ?");
    $update->bind_param("ss", $hashed_password, $username);
    if ($update->execute()) {
        echo "<h2 style='color: green;'>Admin password refreshed successfully!</h2>";
        echo "<b>Username:</b> admin <br>";
        echo "<b>Password:</b> admin123 <br><br>";
        echo "<a href='login.php'>Click here to Login</a>";
    } else {
        echo "Error: " . $conn->error;
    }
} else {
    // Create new initial admin
    $insert = $conn->prepare("INSERT INTO admins (username, password) VALUES (?, ?)");
    $insert->bind_param("ss", $username, $hashed_password);
    if ($insert->execute()) {
        echo "<h2 style='color: green;'>Account created successfully!</h2>";
        echo "<b>Username:</b> admin <br>";
        echo "<b>Password:</b> admin123 <br><br>";
        echo "<a href='login.php'>Click here to Login</a>";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>