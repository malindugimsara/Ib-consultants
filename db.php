<?php
$servername = "127.0.0.1";
$username = "root"; 
$password = ""; 
$dbname = "ib_consultants"; 

// connecting to database with timeout protection
$conn = mysqli_init();
$conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 3);

$connected = false;
$db_error_msg = '';

try {
    $connected = @$conn->real_connect($servername, $username, $password, $dbname);
} catch (mysqli_sql_exception $e) {
    $connected = false;
    $db_error_msg = $e->getMessage();
}

if (!$connected) {
    if (!empty($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Database connection failed. Please ensure MySQL is running.']);
        exit();
    }
    die("<div style='font-family: sans-serif; padding: 25px; max-width: 600px; margin: 40px auto; background: #fff3cd; border: 1px solid #ffeeba; border-radius: 8px; color: #856404;'>"
        . "<h3 style='margin-top: 0;'>Database Connection Error</h3>"
        . "<p>Could not connect to the database. Please ensure your MySQL service is started in XAMPP.</p>"
        . "<p><small>Details: " . htmlspecialchars($conn->connect_error ?: ($db_error_msg ?: 'Connection refused')) . "</small></p>"
        . "</div>");
}
?>