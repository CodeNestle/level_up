<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

session_start();

if (isset($_SESSION['email'])) {
    $email = $_SESSION['email'];
    unset($_SESSION['email']); // Clear session after sending

    $mail = new PHPMailer(true);
    try {
        // SMTP Config
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'codenestle13@gmail.com';  // Your Gmail ID
        $mail->Password = 'udyd gnlg oqdc tgok';      // App password
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;

        // Email Headers
        $mail->setFrom('codenestle13@gmail.com', 'LevelUp Support');
        $mail->addAddress($email);

        // Email Content
        $mail->isHTML(true);
        $mail->Subject = "Welcome to LevelUp!";
        $mail->Body = "
            Hi there! 👋<br><br>
            You have <strong>successfully registered</strong> with <strong>LevelUp</strong>.<br>
            We’re excited to help you prepare for your dream placement! 🎯<br><br>
            If you have any questions or need help, feel free to reply to this email.<br><br>
            Best regards,<br>
            <strong>LevelUp Team</strong><br><br>
            <small>This is an automated message. Please do not reply.</small>
        ";

        $mail->send();

        echo "<script>alert('✅ Registration successful! Please check your email for confirmation.And You Can Login');</script>";
        echo "<script>document.location.href='index.php';</script>";
    } catch (Exception $e) {
        echo "<script>alert('❌ Mailer Error: {$mail->ErrorInfo}');</script>";
    }
} else {
    echo "<script>alert('❌ Error: No email found in session.');</script>";
    echo "<script>document.location.href='index.php';</script>";
}
?>
