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
    <link rel="icon" href="images/LuLogo.jpeg">
</head>
<style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f8bbd0, #ffffff);
            height: 100vh;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }

        h2 {
            color: #880e4f;
            margin-bottom: 20px;
        }

        form {
            background-color: #ffffff;
            padding: 25px 30px;
            border-radius: 15px;
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
            min-width: 320px;
            max-width: 400px;
        }

        input[type="password"] {
            width: 100%;
            padding: 12px 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 10px;
            font-size: 16px;
        }

        button {
            background-color: #ad1457;
            color: white;
            padding: 10px 15px;
            font-size: 16px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            width: 100%;
            transition: background 0.3s ease;
        }

        button:hover {
            background-color: #880e4f;
        }

        p[style*="color:red"] {
            color: #c62828 !important;
            background: #ffcdd2;
            padding: 10px;
            border-radius: 10px;
            margin-bottom: 15px;
            text-align: center;
        }

        label {
            color: #6a1b9a;
            font-weight: bold;
        }
    </style>
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
