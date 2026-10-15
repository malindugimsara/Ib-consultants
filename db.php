<?php
$servername = "localhost";
$username = "root"; 
$password = ""; 
$dbname = "ib_consultants"; 

// connecting to database 
$conn = new mysqli($servername, $username, $password, $dbname);

// connectiong to database check 
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>