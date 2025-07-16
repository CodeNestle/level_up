<?php
session_start();
include 'db.php';

$email = $_SESSION['email'] ?? '';
if (!$email) {
    echo "<script>alert('❌ Unauthorized access.'); window.location='forgot.php';</script>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    $pattern = "/^(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*[\W_]).{8,}$/";

    if ($new === $confirm) {
        if (preg_match($pattern, $new)) {
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
            $stmt->bind_param("ss", $new, $email);
            if ($stmt->execute()) {
                // ✅ Save email temporarily before destroying session
                $userEmail = $_SESSION['email'];
                session_destroy();
                
                // Start new session to pass email to send1.php
                session_start();
                $_SESSION['email'] = $userEmail;

                header("Location: send1.php");
                exit();
            } else {
                $error = "❌ Password update failed.";
            }
        } else {
            $error = "❗ Password must be strong: 8+ chars, uppercase, lowercase, number, special.";
        }
    } else {
        $error = "❌ Passwords do not match.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
</head>
<body>
    <h2>🔄 Reset Your Password</h2>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>

    <form method="POST">
        <label>New Password:</label><br>
        <input type="password" name="new_password" required><br><br>

        <label>Confirm Password:</label><br>
        <input type="password" name="confirm_password" required><br><br>

        <button type="submit">Reset Password</button>
    </form>
</body>
</html>
