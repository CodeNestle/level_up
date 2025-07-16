<?php
session_start();
include 'db.php';

if (!isset($_SESSION['email'])) exit();

$email = $_SESSION['email'];
$question_id = $_POST['question_id'] ?? null;
$is_correct = $_POST['is_correct'] ?? 0;
$user_option = $_POST['user_option'] ?? '';

if (!$question_id) exit();

// Get user_id and round_id
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($user_id);
$stmt->fetch();
$stmt->close();

$stmt = $conn->prepare("SELECT round_id FROM questions WHERE id = ?");
$stmt->bind_param("i", $question_id);
$stmt->execute();
$stmt->bind_result($round_id);
$stmt->fetch();
$stmt->close();

// Get company_id
$stmt = $conn->prepare("SELECT company_id FROM rounds WHERE id = ?");
$stmt->bind_param("i", $round_id);
$stmt->execute();
$stmt->bind_result($company_id);
$stmt->fetch();
$stmt->close();

// Insert into user_answers
$stmt = $conn->prepare("INSERT INTO user_answers (user_id, round_id, question_id, is_correct, user_option) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("iiiis", $user_id, $round_id, $question_id, $is_correct, $user_option);
$stmt->execute();
$stmt->close();

// Award XP if correct
if ($is_correct) {
    $xp = 2;
    $stmt = $conn->prepare("INSERT INTO user_xp (user_id, company_id, round_id, xp_points)
        VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE xp_points = xp_points + VALUES(xp_points)");
    $stmt->bind_param("iiii", $user_id, $company_id, $round_id, $xp);
    $stmt->execute();
    $stmt->close();
}
