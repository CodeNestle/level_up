<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer files
require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

// Start session
session_start();

if (isset($_SESSION['email'])) {
    $email = $_SESSION['email'];
    unset($_SESSION['email']); // Remove session after use

    $mail = new PHPMailer(true);
    try {
        // SMTP server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';         // Gmail SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = 'codenestle13@gmail.com'; // Your Gmail
        $mail->Password = 'udyd gnlg oqdc tgok';     // App password
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;

        // Sender and receiver
        $mail->setFrom('codenestle13@gmail.com', 'LevelUp Support');
        $mail->addAddress($email);

        // Mail content
        $mail->isHTML(true);
        $mail->Subject = "Password Changed Successfully";
        $mail->Body = "
            Dear user,<br><br>
            Your password has been <strong>successfully updated</strong> for your <strong>LevelUp</strong> account.<br>
            The email associated with this account is: <strong>{$email}</strong>.<br><br>
            If you did not request this change, please <a href='#'>contact support</a> immediately.<br><br>
            Best regards,<br>
            <strong>LevelUp Team</strong><br><br>
            <small>This is an automated message. Please do not reply.</small>
        ";

        // Send email
        $mail->send();

        echo "<script>alert('✅ Password reset successful. Please check your email for confirmation.');</script>";
        echo "<script>document.location.href='index.php';</script>";
    } catch (Exception $e) {
        echo "<script>alert('❌ Mailer Error: {$mail->ErrorInfo}');</script>";
        echo "<script>document.location.href='index.php';</script>";
    }
} else {
    echo "<script>alert('❌ Error: No email found in session.');</script>";
    echo "<script>document.location.href='index.php';</script>";
}
?>
