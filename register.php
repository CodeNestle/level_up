<?php
include 'db.php';
session_start();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email    = $_POST['email'];
    $password = $_POST['password']; // no hashing

    // Strong password validation
    $passwordPattern = '/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,}$/';

    if (!preg_match($passwordPattern, $password)) {
        $error = "❌ Password must be at least 8 characters with uppercase, lowercase, number & special character.";
    } else {
        $profile_img = 'default.png';
        
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, profile_img) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $email, $password, $profile_img); // no hashed password

        if ($stmt->execute()) {
            $_SESSION['email'] = $email; // For send.php
            header("Location: send.php");
            exit();
        } else {
            $error = "❌ Email already used or error occurred.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register - LevelUp</title>
        <link rel="icon" href="images/LuLogo.jpeg">

</head>
<style>
    body {
        font-family: 'Segoe UI', sans-serif;
        background: linear-gradient(to right, #6a11cb, #2575fc);
        color: #fff;
        text-align: center;
        padding: 50px;
        margin: 0;
    }

    h2 {
        font-size: 28px;
        margin-bottom: 20px;
    }

    form {
        background: rgba(255, 255, 255, 0.1);
        padding: 25px;
        border-radius: 12px;
        max-width: 380px;
        margin: auto;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }

    input[type="text"],
    input[type="email"],
    input[type="password"] {
        width: 85%;
        padding: 10px;
        margin: 10px 0;
        border: none;
        border-radius: 6px;
        font-size: 16px;
    }

    button {
        background: #fff;
        color: #2575fc;
        padding: 10px 25px;
        border: none;
        border-radius: 6px;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        margin-top: 10px;
        transition: background 0.3s ease;
    }

    button:hover {
        background: #e0e0e0;
    }

    a {
        color: #ffccff;
        text-decoration: none;
    }

    a:hover {
        text-decoration: underline;
    }

    p {
        margin-top: 20px;
        font-size: 14px;
    }

    label {
        font-weight: bold;
    }

    input[type="checkbox"] {
        transform: scale(1.2);
        margin-right: 5px;
    }

    .error {
        color: #ff4d4d;
        font-weight: bold;
        margin-bottom: 10px;
    }
</style>

<body>
    <div class="register-container">
        <h2>Register - LevelUp</h2>
        <?php if (isset($error)) echo "<div class='error-msg'>$error</div>"; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="👤 Username" required>
            <input type="email" name="email" placeholder="📧 Email" required>
            <input type="password" name="password" placeholder="🔒 Password" required>
            <button type="submit">Register</button>
        </form>
        <p style="margin-top: 15px;">Already a user? <a href="index.php">Login</a></p>
    </div>
</body>

</html>



