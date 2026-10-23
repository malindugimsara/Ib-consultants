<?php
$servername = "127.0.0.1";
$username = "root"; 
$password = ""; 
$dbname = "ib_consultants"; 

// connecting to database with timeout protection
$conn = mysqli_init();
$conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 3);
if (!@$conn->real_connect($servername, $username, $password, $dbname)) {
    if (!empty($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
        exit();
    }
    die("Connection failed: " . $conn->connect_error);
}
?>