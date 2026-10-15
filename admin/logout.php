<?php
session_start();
// ලොගින් වී ඇති Session දත්ත සියල්ල මකා දැමීම
session_destroy();
// නැවත ලොගින් පිටුවට යැවීම
header("Location: login.php");
exit;
?>