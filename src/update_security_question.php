<?php
session_start();
include './db.php';
require_once __DIR__ . '/activity_logger.php';

header("Content-Type: application/json");

if (!isset($_SESSION['user_ID'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$question_id = intval($data['question_id'] ?? 0);
$answer = trim($data['answer'] ?? '');
$user_ID = $_SESSION['user_ID'];

if ($question_id <= 0 || empty($answer)) {
    echo json_encode(["status" => "error", "message" => "Invalid input"]);
    exit;
}

// Hash the answer
$answer_hash = password_hash($answer, PASSWORD_DEFAULT);

// Check if user already has a security question
$sql = "SELECT answer_ID FROM User_Security_Answer WHERE user_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_ID);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    // Update existing
    $update = $conn->prepare("UPDATE User_Security_Answer SET question_ID = ?, answer_hash = ? WHERE user_ID = ?");
    $update->bind_param("isi", $question_id, $answer_hash, $user_ID);
    $success = $update->execute();
    $update->close();
} else {
    // Insert new
    $insert = $conn->prepare("INSERT INTO User_Security_Answer (user_ID, question_ID, answer_hash) VALUES (?, ?, ?)");
    $insert->bind_param("iis", $user_ID, $question_id, $answer_hash);
    $success = $insert->execute();
    $insert->close();
}

if ($success) {
    echo json_encode(["status" => "success", "message" => "Security question updated successfully"]);
    log_activity($conn, (int)$user_ID, 'Updated security question');
} else {
    echo json_encode(["status" => "error", "message" => "Failed to update security question"]);
}

$stmt->close();
$conn->close();