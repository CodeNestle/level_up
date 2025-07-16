<?php
session_start();
include 'db.php';

if (!isset($_SESSION['email'])) {
    echo "<script>alert('❌ Please login first'); window.location='index.php';</script>";
    exit();
}

$email = $_SESSION['email'];

// Get logged-in user ID and username
$stmt = $conn->prepare("SELECT id, username FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($user_id, $username);
$stmt->fetch();
$stmt->close();

// Get all XP entries for 'LevelUp Ooda Questions'
$sql = "
    SELECT ux.user_id, u.username, SUM(ux.xp_points) AS total_xp
    FROM user_xp ux
    JOIN users u ON ux.user_id = u.id
    JOIN rounds r ON ux.round_id = r.id
    WHERE r.round_name = 'LevelUp Ooda Questions'
    GROUP BY ux.user_id
    ORDER BY total_xp DESC
";
$result = $conn->query($sql);

// Build full leaderboard array
$leaderboard = [];
$logged_user_rank = null;
$rank = 1;

while ($row = $result->fetch_assoc()) {
    $row['rank'] = $rank;
    $leaderboard[] = $row;

    if ($row['user_id'] == $user_id) {
        $logged_user_rank = $rank;
    }

    $rank++;
}

// Slice Top 10
$top10 = array_slice($leaderboard, 0, 10);
?>

<!DOCTYPE html>
<html>
<head>
    <title>🏆 Leaderboard - LevelUp Ooda Questions</title>
    <style>
        body { font-family: sans-serif; background: #f5f5f5; padding: 30px; text-align: center; }
        h2 { margin-bottom: 20px; }
        table {
            margin: auto;
            border-collapse: collapse;
            width: 80%;
            background: white;
            border-radius: 10px;
            overflow: hidden;
        }
        th, td {
            padding: 12px;
            border: 1px solid #ddd;
        }
        th { background: #007bff; color: white; }
        .highlight { background-color: #ffefb0; font-weight: bold; }
        .back-btn {
            margin-top: 20px;
            display: inline-block;
            text-decoration: none;
            color: #007bff;
        }
        .search-box {
            margin-bottom: 20px;
        }
        #search-result {
            background: white;
            border: 1px solid #ccc;
            padding: 20px;
            width: 60%;
            margin: 20px auto;
            display: none;
            border-radius: 10px;
        }
    </style>
</head>
<body>

    <h2>🏆 LevelUp Leaderboard</h2>

    <div class="search-box">
        <input type="text" id="searchUser" placeholder="🔍 Search username..." />
        <button onclick="searchUser()">Search</button>
    </div>

    <table>
        <tr>
            <th>🏅 Rank</th>
            <th>👤 Username</th>
            <th>⚡ XP Points</th>
        </tr>

        <?php foreach ($top10 as $row): ?>
            <tr class="<?= $row['user_id'] == $user_id ? 'highlight' : '' ?>">
                <td><?= $row['rank'] ?></td>
                <td><?= htmlspecialchars($row['username']) ?></td>
                <td><?= $row['total_xp'] ?></td>
            </tr>
        <?php endforeach; ?>

        <?php if ($logged_user_rank > 10): ?>
            <tr class="highlight">
                <td><?= $logged_user_rank ?></td>
                <td><?= htmlspecialchars($username) ?></td>
                <td>
                    <?php
                    $stmt = $conn->prepare("
                        SELECT SUM(ux.xp_points)
                        FROM user_xp ux
                        JOIN rounds r ON ux.round_id = r.id
                        WHERE ux.user_id = ? AND r.round_name = 'LevelUp Ooda Questions'
                    ");
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $stmt->bind_result($user_xp);
                    $stmt->fetch();
                    $stmt->close();

                    echo $user_xp ?: 0;
                    ?>
                </td>
            </tr>
        <?php endif; ?>
    </table>

    <div id="search-result"></div>

    <a href="dashboard.php" class="back-btn">← Back to Dashboard</a>

    <script>
        function searchUser() {
            const username = document.getElementById("searchUser").value;
            if (!username.trim()) return;

            fetch(`search_user_xp.php?username=${encodeURIComponent(username)}`)
                .then(res => res.text())
                .then(html => {
                    document.getElementById("search-result").innerHTML = html;
                    document.getElementById("search-result").style.display = 'block';
                });
        }
    </script>
</body>
</html>
