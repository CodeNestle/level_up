<?php
session_start();
include 'db.php';

// Auto login using cookie
if (!isset($_SESSION['email']) && isset($_COOKIE['remember_email'])) {
    $_SESSION['email'] = $_COOKIE['remember_email'];
}

if (!isset($_SESSION['email'])) {
    echo "<script>alert('❌ Please login first.'); window.location='index.php';</script>";
    exit();
}

$email = $_SESSION['email'];

// Fetch user details
$stmt = $conn->prepare("SELECT username, profile_img FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($username, $profile_img);
$stmt->fetch();
$stmt->close();

$imgPath = $profile_img ? "images/$profile_img" : "images/default.png";

// Fetch companies from DB
$companies = [];
$result = $conn->query("SELECT id, name FROM companies");
while ($row = $result->fetch_assoc()) {
    $companies[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <style>
        body { font-family: sans-serif; background: #f2f6fc; padding: 40px; }
        .header { display: flex; justify-content: space-between; align-items: center; }
        .profile { display: flex; align-items: center; cursor: pointer; }
        .profile img { width: 45px; height: 45px; border-radius: 50%; object-fit: cover; margin-right: 10px; }
        .grid { margin-top: 40px; display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 20px; }
        .card {
            background-color: white; border-radius: 16px; padding: 25px; box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            text-align: center; transition: transform 0.2s ease;
        }
        .card:hover { transform: scale(1.05); cursor: pointer; }
        a { text-decoration: none; color: inherit; }
    </style>
</head>
<body>
    <div class="header">
        <h2>👋 Welcome, <?php echo htmlspecialchars($username); ?></h2>
        <a href="leaderboard.php" style="text-decoration:none; color:#2980b9;">🏆 View Leaderboard</a>
        <a href="profile.php" class="profile-link">
            <div class="profile">
                <img src="<?php echo $imgPath; ?>" alt="Profile">
                <span><?php echo htmlspecialchars($username); ?></span>
            </div>
        </a>
    </div>

    <h3>🏢 Select a Company</h3>
    <div class="grid">
        <?php foreach ($companies as $company): ?>
            <a href="rounds.php?company_id=<?= $company['id'] ?>">
                <div class="card">
                    <h3><?= htmlspecialchars($company['name']) ?></h3>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</body>
</html>
