<?php
require './db.php';
session_start();
require_once __DIR__ . '/activity_logger.php';

if (!isset($_SESSION['user_ID'])) {
    die("Unauthorized access.");
}

$user_id = $_SESSION['user_ID'];
$question_ID = intval($_POST['question_ID']);
$answer = trim($_POST['answer']);
$current_password = $_POST['current_password'];

// Step 1: Verify current password
$stmt = $conn->prepare("SELECT password FROM User WHERE user_ID = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($hashed_password);
$stmt->fetch();
$stmt->close();

if (!password_verify($current_password, $hashed_password)) {
    die("Incorrect current password.");
}

// Step 2: Hash the answer
$hashed_answer = password_hash($answer, PASSWORD_DEFAULT);

// Step 3: Check if user already has a security question
$check = $conn->prepare("SELECT answer_ID FROM User_Security_Answer WHERE user_ID = ?");
$check->bind_param("i", $user_id);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    // Update
    $update = $conn->prepare("UPDATE User_Security_Answer SET question_ID = ?, answer_hash = ? WHERE user_ID = ?");
    $update->bind_param("isi", $question_ID, $hashed_answer, $user_id);
    $update->execute();
    $update->close();
    // Log
    log_activity($conn, (int)$user_id, 'Updated security question');
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: 'Security question updated successfully.',
            confirmButtonColor: '#198754'
        }).then(() => {
            window.location.reload();
        });
    </script>";
} else {
    // Insert
    $insert = $conn->prepare("INSERT INTO User_Security_Answer (user_ID, question_ID, answer_hash) VALUES (?, ?, ?)");
    $insert->bind_param("iis", $user_id, $question_ID, $hashed_answer);
    $insert->execute();
    $insert->close();
    // Log
    log_activity($conn, (int)$user_id, 'Set security question');
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: 'Security question set successfully.',
            confirmButtonColor: '#198754'
        }).then(() => {
            window.location.reload();
        });
    </script>";
}

$check->close();
?>