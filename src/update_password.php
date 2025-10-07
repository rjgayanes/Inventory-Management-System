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
$current = $data['current_password'] ?? '';
$newPass = $data['new_password'] ?? '';
$user_ID = $_SESSION['user_ID'];

// Fetch current password hash
$sql = "SELECT password FROM Users WHERE user_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_ID);
$stmt->execute();
$stmt->bind_result($hash);
$stmt->fetch();
$stmt->close();

// Verify current password
if (!password_verify($current, $hash)) {
    echo json_encode(["status" => "error", "message" => "Current password is incorrect"]);
    exit;
}

// Update new password
$newHash = password_hash($newPass, PASSWORD_DEFAULT);
$update = $conn->prepare("UPDATE Users SET password = ? WHERE user_ID = ?");
$update->bind_param("si", $newHash, $user_ID);

if ($update->execute()) {
    echo json_encode(["status" => "success", "message" => "Password updated successfully"]);
    // Log
    log_activity($conn, (int)$user_ID, 'Updated password');
} else {
    echo json_encode(["status" => "error", "message" => "Failed to update password"]);
}
$update->close();
$conn->close();