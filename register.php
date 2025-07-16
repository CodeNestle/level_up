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
</head>
<body>
    <h2>Register - LevelUp</h2>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <form method="POST">
        Username: <input type="text" name="username" required><br><br>
        Email: <input type="email" name="email" required><br><br>
        Password: <input type="password" name="password" required><br><br>
        <button type="submit">Register</button>
    </form>
    <p>Already a user? <a href="index.php">Login</a></p>
</body>
</html>
