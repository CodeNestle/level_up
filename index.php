<?php
session_start();
include 'db.php';

// 🔁 Auto-login using cookie
if (!isset($_SESSION['email']) && isset($_COOKIE['remember_email'])) {
    $_SESSION['email'] = $_COOKIE['remember_email'];
    header("Location: dashboard.php");
    exit();
}

// If already logged in via session
if (isset($_SESSION['email'])) {
    header("Location: dashboard.php");
    exit();
}

// Handle login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($db_password);
    $stmt->fetch();
    $stmt->close();

    if ($password === $db_password) { // No hashing per your choice
        $_SESSION['email'] = $email;

        // If "Remember Me" checked, set cookie for 7 days
        if (isset($_POST['remember'])) {
            setcookie("remember_email", $email, time() + (7 * 24 * 60 * 60), "/");
        }

        header("Location: dashboard.php");
        exit();
    } else {
        $error = "❌ Invalid email or password.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - LevelUp</title>
</head>
<body>
    <h2>🔐 Login</h2>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>

    <form method="POST">
        <label>Email:</label><br>
        <input type="email" name="email" required><br><br>

        <label>Password:</label><br>
        <input type="password" name="password" required><br><br>

        <input type="checkbox" name="remember" id="remember">
        <label for="remember">Remember Me</label><br><br>

        don't account register here <a href="register.php">click</a>
        forgot in your password <a href="forgot.php">click here</a>

        <button type="submit">Login</button>
    </form>
</body>
</html>
