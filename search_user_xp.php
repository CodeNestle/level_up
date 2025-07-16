<?php
include 'db.php';

$username = $_GET['username'] ?? '';
$username = trim($username);

if (!$username) {
    echo "<p>❌ Invalid search.</p>";
    exit();
}

// Get user ID and name
$stmt = $conn->prepare("SELECT id, username FROM users WHERE username LIKE ?");
$like = "%" . $username . "%";
$stmt->bind_param("s", $like);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<p>❌ User not found.</p>";
    exit();
}

while ($user = $result->fetch_assoc()) {
    $user_id = $user['id'];
    $user_name = $user['username'];

    echo "<h3>👤 {$user_name}'s XP Details</h3>";

    // LevelUp total XP
    $stmt2 = $conn->prepare("
        SELECT SUM(ux.xp_points)
        FROM user_xp ux
        JOIN rounds r ON ux.round_id = r.id
        WHERE ux.user_id = ? AND r.round_name = 'LevelUp Ooda Questions'
    ");
    $stmt2->bind_param("i", $user_id);
    $stmt2->execute();
    $stmt2->bind_result($levelup_xp);
    $stmt2->fetch();
    $stmt2->close();

    echo "<p>🌟 LevelUp Ooda Questions XP: <strong>" . ($levelup_xp ?: 0) . "</strong></p>";

    // Detailed company/round XP
    $stmt3 = $conn->prepare("
        SELECT c.name AS company, r.round_name, ux.xp_points
        FROM user_xp ux
        JOIN rounds r ON ux.round_id = r.id
        JOIN companies c ON ux.company_id = c.id
        WHERE ux.user_id = ? AND r.round_name != 'LevelUp Ooda Questions'
        ORDER BY c.name, r.round_name
    ");
    $stmt3->bind_param("i", $user_id);
    $stmt3->execute();
    $res3 = $stmt3->get_result();

    if ($res3->num_rows > 0) {
        echo "<table><tr><th>🏢 Company</th><th>🧩 Round</th><th>⚡ XP</th></tr>";
        while ($row = $res3->fetch_assoc()) {
            echo "<tr><td>{$row['company']}</td><td>{$row['round_name']}</td><td>{$row['xp_points']}</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<p>📭 No company round XP yet.</p>";
    }
}
?>
