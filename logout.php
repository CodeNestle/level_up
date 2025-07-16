<?php
session_start();
session_destroy();

// Clear cookie
setcookie("remember_email", "", time() - 3600, "/");

echo "<script>alert('✅ Logged out successfully.'); window.location='index.php';</script>";
exit();
