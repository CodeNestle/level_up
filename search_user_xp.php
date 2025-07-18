<?php
include("db.php");

if (!isset($_GET['user_id'])) {
    echo "Invalid request.";
    exit;
}

$user_id = (int) $_GET['user_id'];

// Get user details
$user_result = $conn->query("SELECT username, profile_img FROM users WHERE id = $user_id");
$user = $user_result->fetch_assoc();

// Get total XP
$total_result = $conn->query("SELECT IFNULL(SUM(xp_points), 0) AS total_xp FROM user_xp WHERE user_id = $user_id");
$total = $total_result->fetch_assoc();

// Breakdown query with safe aliases
$xp_query = $conn->query("
    SELECT comp.name AS company_name, r.round_name, SUM(ux.xp_points) AS round_xp
    FROM user_xp ux
    JOIN rounds r ON ux.round_id = r.id
    JOIN companies comp ON r.company_id = comp.id
    WHERE ux.user_id = $user_id
    GROUP BY comp.name, r.round_name
    ORDER BY comp.name, r.round_name
");

$xp_data = [];
while ($row = $xp_query->fetch_assoc()) {
    $company = $row['company_name'];
    $round = $row['round_name'];
    $points = $row['round_xp'];

    if (!isset($xp_data[$company])) {
        $xp_data[$company] = [];
    }
    $xp_data[$company][$round] = $points;
}
?>

<div style="text-align: center;">
    <img src="images/<?php echo htmlspecialchars($user['profile_img']); ?>" style="width: 100px; height: 100px; border-radius: 50%; margin-bottom: 10px;">
    <h2><?php echo htmlspecialchars($user['username']); ?></h2>
    <h3>🔥 Total XP: <?php echo $total['total_xp']; ?></h3>
</div>

<hr>

<div style="text-align: left;">
    <?php if (count($xp_data) === 0): ?>
        <p style="text-align:center;">No XP records yet.</p>
    <?php else: ?>
        <?php foreach ($xp_data as $company => $rounds): ?>
            <h4 style="color: #007bff;">🏢 <?php echo htmlspecialchars($company); ?></h4>
            <ul>
                <?php foreach ($rounds as $round => $xp): ?>
                    <li>📘 <?php echo htmlspecialchars($round); ?> — <strong><?php echo $xp; ?> XP</strong></li>
                <?php endforeach; ?>
            </ul>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
