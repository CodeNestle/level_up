<?php
session_start();
include 'db.php';

if (!isset($_SESSION['email'])) {
    echo "<script>alert('❌ Please login first.'); window.location='index.php';</script>";
    exit();
}

$email = $_SESSION['email'];
$round_id = $_GET['round_id'] ?? null;

if (!$round_id) {
    echo "<script>alert('⚠️ Round ID missing.'); window.location='dashboard.php';</script>";
    exit();
}

// Get user_id
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($user_id);
$stmt->fetch();
$stmt->close();

// Fetch answered questions for this round
$sql = "SELECT q.question_text, q.option_a, q.option_b, q.option_c, q.option_d, q.correct_option,
               ua.user_option, ua.is_correct, ua.answered_at
        FROM user_answers ua
        JOIN questions q ON ua.question_id = q.id
        WHERE ua.user_id = ? AND ua.round_id = ?
        ORDER BY ua.answered_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $round_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Answered Questions</title>
        <link rel="icon" href="images/LuLogo.jpeg">

    <style>
        body {
            font-family: sans-serif;
            background-color: #f4f7fa;
            padding: 30px;
        }
        .question-box {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .correct {
            color: green;
            font-weight: bold;
        }
        .wrong {
            color: red;
            font-weight: bold;
        }
        .option {
            margin: 4px 0;
        }
    </style>
</head>
<body>

    <h2>📋 Answered Questions</h2>

    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="question-box">
                <p><strong>Q:</strong> <?= htmlspecialchars($row['question_text']) ?></p>
                <div class="option">A. <?= htmlspecialchars($row['option_a']) ?></div>
                <div class="option">B. <?= htmlspecialchars($row['option_b']) ?></div>
                <div class="option">C. <?= htmlspecialchars($row['option_c']) ?></div>
                <div class="option">D. <?= htmlspecialchars($row['option_d']) ?></div>
                <p>
                    <strong>Your Answer:</strong>
                    <?= $row['user_option'] ?> -
                    <?php if ($row['is_correct']): ?>
                        <span class="correct">✅ Correct</span>
                    <?php else: ?>
                        <span class="wrong">❌ Wrong (Correct: <?= $row['correct_option'] ?>)</span>
                    <?php endif; ?>
                </p>
                <small>Answered at: <?= $row['answered_at'] ?></small>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No questions answered yet for this round.</p>
    <?php endif; ?>

    <br><a href="profile.php" style="text-decoration: none; color: #007bff;">← Back to Profile</a>

</body>
</html>
