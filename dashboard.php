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

// Fetch companies
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
    <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" href="images/LuLogo.jpeg">

    <style>
        body {
            font-family: sans-serif;
            background: #f2f6fc;
            margin: 0;
            padding: 0;
        }

        .navbar {
            background: #fff;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 25px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .brand {
            font-size: 20px;
            font-weight: bold;
            color: #2c3e50;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .nav-links a {
            text-decoration: none;
            color: #2980b9;
            font-weight: bold;
        }

        .profile {
            display: flex;
            align-items: center;
        }

        .profile img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 8px;
        }

        .hamburger {
            display: none;
            flex-direction: column;
            cursor: pointer;
            gap: 5px;
        }

        .hamburger div {
            width: 25px;
            height: 3px;
            background-color: #333;
        }

        .mobile-menu {
            display: none;
            flex-direction: column;
            background: #fff;
            position: absolute;
            top: 65px;
            right: 25px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            padding: 15px;
            border-radius: 10px;
            width: 200px;
        }

        .mobile-menu a {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
            text-decoration: none;
            color: #2980b9;
        }

        .mobile-menu .profile {
            margin-top: 10px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }

        .main-content {
            padding: 30px;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .card {
            background-color: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            text-align: center;
            transition: transform 0.2s ease;
        }

        .card:hover {
            transform: scale(1.05);
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }

            .hamburger {
                display: flex;
            }

            .mobile-menu.show {
                display: flex;
            }
        }
    </style>
</head>
<body>

<div class="navbar">
    <div class="brand">📱 LevelUp</div>

    <div class="nav-links">
        <a href="leaderboard.php">🏆 Leaderboard</a>
        <a href="profile.php" class="profile">
            <img src="<?php echo $imgPath; ?>" alt="Profile">
            <span><?php echo htmlspecialchars($username); ?></span>
        </a>
    </div>

    <div class="hamburger" onclick="toggleMenu()">
        <div></div><div></div><div></div>
    </div>

    <div class="mobile-menu" id="mobileMenu">
        <a href="leaderboard.php">🏆 Leaderboard</a>
        <a href="profile.php" class="profile">
            <img src="<?php echo $imgPath; ?>" alt="Profile">
            <span><?php echo htmlspecialchars($username); ?></span>
        </a>
    </div>
</div>

<div class="main-content">
    <h2>👋 Welcome, <?php echo htmlspecialchars($username); ?>!</h2>
    <h3>🏢 Select a Company</h3>

    <div class="grid">
        <?php foreach ($companies as $company): ?>
            <a href="rounds.php?company_id=<?= $company['id'] ?>" style="text-decoration:none; color:inherit;">
                <div class="card">
                    <h3><?= htmlspecialchars($company['name']) ?></h3>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<script>
    function toggleMenu() {
        document.getElementById("mobileMenu").classList.toggle("show");
    }
</script>

</body>
</html>
