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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - LevelUp</title>
    <link rel="icon" href="images/LuLogo.jpeg">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #1e1e2f, #2a2a40);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #fff;
        }

        .login-container {
            background-color: #2d2d44;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0px 4px 15px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 400px;
        }

        .login-container h2 {
            text-align: center;
            margin-bottom: 25px;
            font-size: 24px;
            color: #00ffc3;
        }

        form label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        form input[type="email"],
        form input[type="password"] {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 8px;
            background-color: #44445e;
            color: white;
            margin-bottom: 20px;
        }

        form input[type="checkbox"] {
            margin-right: 6px;
        }

        .remember-me {
            margin-bottom: 20px;
        }

        form button {
            width: 100%;
            padding: 12px;
            background-color: #00ffc3;
            color: black;
            font-weight: bold;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.3s ease;
        }

        form button:hover {
            background-color: #00cfa0;
        }

        .links {
            margin-top: 15px;
            text-align: center;
            font-size: 14px;
        }

        .links a {
            color: #00ffc3;
            text-decoration: none;
        }

        .error {
            background-color: #ff4e4e;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            color: white;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>🔐 Login to LevelUp</h2>

        <?php if (isset($error)) echo "<div class='error'>$error</div>"; ?>

        <form method="POST">
            <label for="email">📧 Email</label>
            <input type="email" name="email" id="email" required>

            <label for="password">🔑 Password</label>
            <input type="password" name="password" id="password" required>

            <div class="remember-me">
                <input type="checkbox" name="remember" id="remember">
                <label for="remember">Remember Me</label>
            </div>

            <button type="submit">Login</button>

            <div class="links">
                <p>🆕 Don't have an account? <a href="register.php">Register here</a></p>
                <p>❓Forgot your password? <a href="forgot.php">Reset here</a></p>
            </div>
        </form>
    </div>
</body>
</html>
