<?php
session_start();
include './db.php'; // you are already inside /src/
require_once __DIR__ . '/activity_logger.php';

// Check if logged in and is Supply Officer
if (!isset($_SESSION['user_ID']) || $_SESSION['role'] !== 'Supply Officer') {
    header("Location: ../public/login.php?error=Access+denied");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['log_ID'])) {
    $log_ID = intval($_POST['log_ID']);
    $user_ID = $_SESSION['user_ID'];

    // Delete only logs that belong to this user
    $deleteSql = "DELETE FROM Activity_Log WHERE log_ID = ? AND user_ID = ?";
    $stmt = $conn->prepare($deleteSql);
    $stmt->bind_param("ii", $log_ID, $user_ID);

    if ($stmt->execute()) {
        // Log
        log_activity($conn, (int)$user_ID, 'Deleted activity log #' . $log_ID);
        // Redirect back to profile.php with query string
        header("Location: ../public/profile.php?success=Log+deleted");
        exit();
    } else {
        header("Location: ../public/profile.php?error=Delete+failed");
        exit();
    }
}