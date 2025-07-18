<?php
session_start();
include 'db.php';

if (!isset($_SESSION['email']) && isset($_COOKIE['remember_email'])) {
    $_SESSION['email'] = $_COOKIE['remember_email'];
}

if (!isset($_SESSION['email'])) {
    echo "<script>alert('❌ Please login first.'); window.location='index.php';</script>";
    exit();
}

$email = $_SESSION['email'];

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_username = trim($_POST['username']);

    $image = $_FILES['profile_img']['name'];
    if ($image) {
        $target_dir = "images/";
        $new_image_name = uniqid() . "_" . basename($image);
        $target_file = $target_dir . $new_image_name;

        if (move_uploaded_file($_FILES['profile_img']['tmp_name'], $target_file)) {
            $stmt = $conn->prepare("UPDATE users SET username = ?, profile_img = ? WHERE email = ?");
            $stmt->bind_param("sss", $new_username, $new_image_name, $email);
        } else {
            echo "<script>alert('❌ Failed to upload image.');</script>";
        }
    } else {
        $stmt = $conn->prepare("UPDATE users SET username = ? WHERE email = ?");
        $stmt->bind_param("ss", $new_username, $email);
    }

    if (isset($stmt) && $stmt->execute()) {
        echo "<script>alert('✅ Profile updated successfully.'); window.location='profile.php';</script>";
        exit();
    } else {
        echo "<script>alert('❌ Failed to update profile.');</script>";
    }
}

// Fetch user info
$stmt = $conn->prepare("SELECT id, username, profile_img FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($user_id, $username, $profile_img);
$stmt->fetch();
$stmt->close();

$imgPath = $profile_img ? "images/$profile_img" : "images/default.png";
?>

<!DOCTYPE html>
<html>

<head>
    <title>Profile</title>
<style>
    body {
        font-family: 'Segoe UI', sans-serif;
        background: #f4f6f9;
        margin: 0;
        padding: 40px;
        display: flex;
        justify-content: center;
    }

    .profile-card {
        background: white;
        padding: 40px;
        border-radius: 20px;
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1);
        width: 100%;
        max-width: 600px;
    }

    .profile-card img {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #4CAF50;
        margin-bottom: 20px;
    }

    .form-group {
        margin: 20px 0;
        text-align: left;
    }

    .form-group label {
        display: block;
        margin-bottom: 6px;
        font-weight: 600;
        color: #333;
    }

    input[type="text"],
    input[type="file"] {
        width: 100%;
        padding: 10px 12px;
        border-radius: 8px;
        border: 1px solid #ccc;
        transition: border-color 0.3s;
        font-size: 15px;
    }

    input[type="text"]:focus,
    input[type="file"]:focus {
        border-color: #4CAF50;
        outline: none;
    }

    .save-btn {
        background: #4CAF50;
        color: white;
        padding: 12px 20px;
        border: none;
        border-radius: 10px;
        font-size: 16px;
        cursor: pointer;
        transition: background 0.3s;
        width: 100%;
        margin-top: 15px;
    }

    .save-btn:hover {
        background: #45a049;
    }

    .logout-btn {
        background: #f44336;
        color: white;
        padding: 10px 16px;
        border: none;
        border-radius: 10px;
        font-size: 14px;
        cursor: pointer;
        margin-top: 25px;
        transition: background 0.3s;
        width: 100%;
    }

    .logout-btn:hover {
        background: #e53935;
    }

    .back-btn {
        display: block;
        margin-top: 20px;
        color: #3498db;
        text-decoration: none;
        font-size: 15px;
        font-weight: 500;
    }

    .back-btn:hover {
        text-decoration: underline;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 30px;
        font-size: 15px;
    }

    th, td {
        padding: 10px;
        border: 1px solid #ddd;
        text-align: center;
    }

    th {
        background-color: #f8f8f8;
        font-weight: 600;
    }

    .answered-list {
        margin-top: 30px;
        text-align: left;
    }

    .answered-list h3 {
        margin-bottom: 15px;
    }

    .answered-list ul {
        list-style: none;
        padding: 0;
    }

    .answered-list li {
        margin-bottom: 8px;
    }

    .answered-list a {
        color: #2c3e50;
        text-decoration: none;
        font-weight: 500;
    }

    .answered-list a:hover {
        text-decoration: underline;
    }
</style>

</head>

<body>

    <div class="profile-card">
        <form method="POST" enctype="multipart/form-data">
            <img src="<?php echo $imgPath; ?>" alt="Profile">

            <div class="form-group">
                <label>Username:</label>
                <input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
            </div>

            <div class="form-group">
                <label>Change Profile Picture:</label>
                <input type="file" name="profile_img" accept="image/*">
            </div>

            <button class="save-btn" type="submit">💾 Save Changes</button>
        </form>

        <form action="logout.php" method="POST">
            <button class="logout-btn" type="submit">🔓 Logout</button>
        </form>

        <a href="dashboard.php" class="back-btn">← Back to Dashboard</a>

        <!-- XP Points -->
        <?php
        $xp_stmt = $conn->prepare("
        SELECT c.name AS company, r.round_name, SUM(ux.xp_points) AS total_xp
        FROM user_xp ux
        JOIN companies c ON ux.company_id = c.id
        JOIN rounds r ON ux.round_id = r.id
        WHERE ux.user_id = ?
        GROUP BY ux.company_id, ux.round_id
        ORDER BY c.name, r.round_name
    ");
        $xp_stmt->bind_param("i", $user_id);
        $xp_stmt->execute();
        $xp_result = $xp_stmt->get_result();

        if ($xp_result->num_rows > 0) {
            echo "<h3>🏆 XP Points Summary</h3><table>";
            echo "<tr><th>Company</th><th>Round</th><th>XP</th></tr>";
            while ($row = $xp_result->fetch_assoc()) {
                echo "<tr><td>{$row['company']}</td><td>{$row['round_name']}</td><td>{$row['total_xp']}</td></tr>";
            }
            echo "</table>";
        } else {
            echo "<p>📭 No XP data yet.</p>";
        }
        $xp_stmt->close();
        ?>

        <!-- Answered Questions Section with Count -->
        <?php
        $answered_stmt = $conn->prepare("
    SELECT 
        r.id AS round_id, 
        r.round_name, 
        c.name AS company_name,
        COUNT(ua.id) AS total_answered
    FROM user_answers ua
    JOIN rounds r ON ua.round_id = r.id
    JOIN companies c ON r.company_id = c.id
    WHERE ua.user_id = ?
    GROUP BY ua.round_id
    ORDER BY MAX(ua.answered_at) DESC
");
        $answered_stmt->bind_param("i", $user_id);
        $answered_stmt->execute();
        $answered_result = $answered_stmt->get_result();
        ?>

        <div class="answered-list">
            <h3>📘 Answered Questions</h3>
            <?php if ($answered_result->num_rows > 0): ?>
                <ul>
                    <?php while ($row = $answered_result->fetch_assoc()): ?>
                        <li>
                            <a href="answered_questions.php?round_id=<?= $row['round_id'] ?>">
                                🔹 <?= htmlspecialchars($row['company_name']) ?> - <?= htmlspecialchars($row['round_name']) ?>
                                (<?= $row['total_answered'] ?> answered)
                            </a>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p>📭 No questions answered yet.</p>
            <?php endif; ?>
            <?php $answered_stmt->close(); ?>
        </div>

    </div>

</body>

</html>
