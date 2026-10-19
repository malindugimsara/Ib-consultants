<?php
include '../db.php';

$username = 'admin';
$password = 'admin123';

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$sql = "INSERT INTO admins (username, password) VALUES ('$username', '$hashed_password')";

if($conn->query($sql)){
    echo "<h2 style='color: green;'>Account created successfully!</h2>";
    echo "<b>Username:</b> admin <br>";
    echo "<b>Password:</b> admin123 <br><br>";
    echo "<a href='login.php'>Click here to Login</a>";
} else {
    echo "Error: " . $conn->error;
}
?>