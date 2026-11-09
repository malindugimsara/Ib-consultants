<?php
include '../db.php';

$username = 'admin';
$password = 'admin123';
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Check if admin user already exists
$check = $conn->prepare("SELECT id FROM admins WHERE username = ?");
$check->bind_param("s", $username);
$check->execute();
$check_result = $check->get_result();

if ($check_result && $check_result->num_rows > 0) {
    // Update existing admin password to ensure login works
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
    // Create new admin
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