<?php
session_start();
include 'db.php';

if (!isset($_SESSION['email'])) {
    echo "<script>alert('❌ Please login first.'); window.location='index.php';</script>";
    exit();
}

$email = $_SESSION['email'];
$company_id = $_GET['company_id'] ?? null;
$round_number = $_GET['round_number'] ?? null;

if (!$company_id || !$round_number) {
    echo "<script>alert('⚠️ Invalid round access.'); window.location='dashboard.php';</script>";
    exit();
}

// Get user_id
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($user_id);
$stmt->fetch();
$stmt->close();

// Get round_id from company_id and round_number
$stmt = $conn->prepare("SELECT id FROM rounds WHERE company_id = ? AND round_number = ?");
$stmt->bind_param("ii", $company_id, $round_number);
$stmt->execute();
$stmt->bind_result($round_id);
if (!$stmt->fetch()) {
    echo "<script>alert('⚠️ Round not found.'); window.location='dashboard.php';</script>";
    exit();
}
$stmt->close();

// Get answered questions
$answered = [];
$q = $conn->prepare("SELECT question_id FROM user_answers WHERE user_id = ? AND round_id = ?");
$q->bind_param("ii", $user_id, $round_id);
$q->execute();
$q->bind_result($qid);
while ($q->fetch()) {
    $answered[] = $qid;
}
$q->close();

// Get next unanswered question
if (count($answered) > 0) {
    $placeholders = implode(',', array_fill(0, count($answered), '?'));
    $types = str_repeat('i', count($answered));
    $sql = "SELECT id, question_text, option_a, option_b, option_c, option_d, correct_option 
            FROM questions 
            WHERE round_id = ? AND id NOT IN ($placeholders) 
            ORDER BY RAND() LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i$types", ...array_merge([$round_id], $answered));
} else {
    $stmt = $conn->prepare("SELECT id, question_text, option_a, option_b, option_c, option_d, correct_option 
                            FROM questions 
                            WHERE round_id = ? 
                            ORDER BY RAND() LIMIT 1");
    $stmt->bind_param("i", $round_id);
}

$stmt->execute();
$result = $stmt->get_result();
$question = $result->fetch_assoc();
$stmt->close();

if (!$question) {
    echo "<script>alert('🎉 Quiz completed!'); window.location='rounds.php?company_id=$company_id';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Quiz</title>
    <style>
        body {
            font-family: sans-serif;
            background: #f4f7fa;
            padding: 40px;
            text-align: center;
        }
        .question-box {
            background: white;
            padding: 25px;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            max-width: 600px;
            margin: auto;
        }
        .option {
            margin: 10px 0;
            padding: 12px;
            border-radius: 10px;
            background: #e3e8ef;
            cursor: pointer;
            transition: background 0.2s ease;
        }
        .option:hover {
            background: #d0d7e2;
        }
        .correct {
            background: #a0e7a0 !important;
        }
        .wrong {
            background: #f7b7b7 !important;
        }
        .emoji {
            font-size: 24px;
            margin-left: 8px;
        }
    </style>
    <script>
        function checkAnswer(selectedOption, correctOption, questionId) {
            let options = document.querySelectorAll('.option');
            options.forEach(opt => opt.onclick = null);

            const selectedEl = document.getElementById(selectedOption);
            const correctEl = document.getElementById(correctOption);

            if (selectedOption === correctOption) {
                selectedEl.classList.add('correct');
                selectedEl.innerHTML += ' ✅';
                fetch('update_xp.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `question_id=${questionId}&is_correct=1&user_option=${selectedOption}`
                });
            } else {
                selectedEl.classList.add('wrong');
                correctEl.classList.add('correct');
                selectedEl.innerHTML += ' 😕';
                fetch('update_xp.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `question_id=${questionId}&is_correct=0&user_option=${selectedOption}`
                });
            }

            setTimeout(() => window.location.reload(), 2000);
        }
    </script>
</head>
<body>
    <div class="question-box">
        <h3>Q. <?= htmlspecialchars($question['question_text']); ?></h3>
        <div id="A" class="option" onclick="checkAnswer('A', '<?= $question['correct_option']; ?>', <?= $question['id']; ?>)">
            A. <?= htmlspecialchars($question['option_a']); ?>
        </div>
        <div id="B" class="option" onclick="checkAnswer('B', '<?= $question['correct_option']; ?>', <?= $question['id']; ?>)">
            B. <?= htmlspecialchars($question['option_b']); ?>
        </div>
        <div id="C" class="option" onclick="checkAnswer('C', '<?= $question['correct_option']; ?>', <?= $question['id']; ?>)">
            C. <?= htmlspecialchars($question['option_c']); ?>
        </div>
        <div id="D" class="option" onclick="checkAnswer('D', '<?= $question['correct_option']; ?>', <?= $question['id']; ?>)">
            D. <?= htmlspecialchars($question['option_d']); ?>
        </div>
    </div>
</body>
</html>
