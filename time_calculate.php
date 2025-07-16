<?php
include 'db.php';
date_default_timezone_set('Asia/Kolkata');

$current_time = date("Y-m-d H:i:s");

$stmt = $conn->prepare("
    UPDATE users 
    SET otp = NULL, otp_sent_time = NULL 
    WHERE otp_sent_time IS NOT NULL 
      AND TIMESTAMPDIFF(SECOND, otp_sent_time, ?) > 60
");
$stmt->bind_param("s", $current_time);
$stmt->execute();
$stmt->close();
?>
