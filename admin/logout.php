<?php
session_start();
// Clear and destroy all active admin session data
session_destroy();
// Redirect safely back to the login page
header("Location: login.php");
exit;
?>