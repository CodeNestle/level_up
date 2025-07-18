<?php
include("db.php");

$result = $conn->query("
    SELECT u.id, u.username, u.profile_img, 
           IFNULL(SUM(x.xp_points), 0) AS total_xp
    FROM users u
    LEFT JOIN user_xp x ON u.id = x.user_id
    LEFT JOIN rounds r ON x.round_id = r.id
    WHERE r.round_name LIKE '%LevelUp%' OR r.round_name IS NULL
    GROUP BY u.id, u.username, u.profile_img
    ORDER BY total_xp DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Leaderboard</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f0f0f0;
            padding: 20px;
        }

        h2 {
            text-align: center;
        }

        table {
            width: 80%;
            margin: 20px auto;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        th, td {
            padding: 12px;
            text-align: left;
        }

        th {
            background: #007bff;
            color: white;
        }

        tr:nth-child(even) {
            background: #f9f9f9;
        }

        tr:hover {
            background: #e9f5ff;
            cursor: pointer;
        }

        .profile-img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            vertical-align: middle;
            margin-right: 10px;
        }

        /* Popup */
        #popup {
            display: none;
            position: fixed;
            top: 10%;
            left: 25%;
            width: 50%;
            background: #fff;
            border: 2px solid #007bff;
            padding: 20px;
            z-index: 1000;
            box-shadow: 0 0 15px rgba(0,0,0,0.3);
            border-radius: 10px;
        }

        #popup-close {
            float: right;
            font-weight: bold;
            font-size: 20px;
            cursor: pointer;
        }
    </style>
</head>
<body>

<h2>📋 Overall LevelUp Leaderboard</h2>

<table>
    <tr>
        <th>Username</th>
        <th>XP Points</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()) { ?>
        <tr onclick="showUserPopup(<?php echo $row['id']; ?>)">
            <td>
                <img src="images/<?php echo htmlspecialchars($row['profile_img']); ?>" class="profile-img">
                <?php echo htmlspecialchars($row['username']); ?>
            </td>
            <td><?php echo $row['total_xp']; ?> XP</td>
        </tr>
    <?php } ?>
</table>

<!-- Popup -->
<div id="popup">
    <span id="popup-close" onclick="document.getElementById('popup').style.display='none'">&times;</span>
    <div id="popup-content"></div>
</div>

<script>
function showUserPopup(userId) {
    fetch('search_user_xp.php?user_id=' + userId)
        .then(res => res.text())
        .then(html => {
            document.getElementById('popup-content').innerHTML = html;
            document.getElementById('popup').style.display = 'block';
        });
}
</script>

</body>
</html>
