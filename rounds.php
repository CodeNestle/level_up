<?php
session_start();
include 'db.php';

if (!isset($_SESSION['email'])) {
    echo "<script>alert('Please login first.'); window.location='index.php';</script>";
    exit();
}

$company_id = $_GET['company_id'] ?? null;

if (!$company_id) {
    echo "<script>alert('Invalid company ID.'); window.location='dashboard.php';</script>";
    exit();
}

$email = $_SESSION['email'];

// Get user_id
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($user_id);
$stmt->fetch();
$stmt->close();

// Get company name
$stmt = $conn->prepare("SELECT name FROM companies WHERE id = ?");
$stmt->bind_param("i", $company_id);
$stmt->execute();
$stmt->bind_result($company_name);
$stmt->fetch();
$stmt->close();

// Get rounds (ordered by round_number)
$stmt = $conn->prepare("SELECT id, round_number, round_name FROM rounds WHERE company_id = ? ORDER BY round_number ASC");
$stmt->bind_param("i", $company_id);
$stmt->execute();
$result = $stmt->get_result();
$rounds = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($company_name) ?> - Rounds</title>
    <style>
        body { font-family: sans-serif; padding: 30px; background: #f2f2f2; }
        .card {
            background: white;
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            transition: 0.2s;
        }
        .card:hover {
            transform: scale(1.03);
        }
        .progress-bar {
            width: 100%;
            background-color: #f44336;
            height: 16px;
            border-radius: 8px;
            overflow: hidden;
            margin-top: 8px;
        }
        .progress-fill {
            background-color: #4caf50;
            height: 100%;
            transition: width 0.3s ease;
        }
        .card-title {
            font-weight: bold;
        }
        a {
            text-decoration: none;
            color: black;
            display: block;
        }
        .progress-text {
            font-size: 14px;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <h2>🏢 <?= htmlspecialchars($company_name) ?> - Rounds</h2>

    <?php foreach ($rounds as $round): ?>
        <?php
        $round_id = $round['id'];

        // Get total questions
        $stmt = $conn->prepare("SELECT COUNT(*) FROM questions WHERE round_id = ?");
        $stmt->bind_param("i", $round_id);
        $stmt->execute();
        $stmt->bind_result($total_questions);
        $stmt->fetch();
        $stmt->close();

        // Get answered questions
        $stmt = $conn->prepare("SELECT COUNT(*) FROM user_answers WHERE user_id = ? AND round_id = ?");
        $stmt->bind_param("ii", $user_id, $round_id);
        $stmt->execute();
        $stmt->bind_result($answered);
        $stmt->fetch();
        $stmt->close();

        $percent = $total_questions > 0 ? round(($answered / $total_questions) * 100) : 0;
        ?>

        <a href="quiz.php?company_id=<?= $company_id ?>&round_number=<?= $round['round_number'] ?>">
            <div class="card">
                <div class="card-title"><?= htmlspecialchars($round['round_name']) ?></div>
                <div class="progress-bar">  
                    <div class="progress-fill" style="width: <?= $percent ?>%"></div>
                </div>
                <div class="progress-text"><?= $answered ?> / <?= $total_questions ?> answered (<?= $percent ?>%)</div>
            </div>
        </a>
    <?php endforeach; ?>
</body>
</html>
