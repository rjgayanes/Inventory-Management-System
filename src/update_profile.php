<?php
session_start();
include './db.php';
require_once __DIR__ . '/activity_logger.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_ID'])) {
    echo json_encode(["success" => false, "error" => "Not logged in"]);
    exit();
}

$user_id = $_SESSION['user_ID'];
$user_name = $_POST['user_name'] ?? '';
$user_email = $_POST['user_email'] ?? '';
$first_name = $_POST['first_name'] ?? '';
$last_name = $_POST['last_name'] ?? '';
$professional_designation = $_POST['professional_designation'] ?? '';
$role = $_POST['role'] ?? '';

// Get current image from DB
$sql = "SELECT profile_image FROM users WHERE user_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$currentImage = $result->fetch_assoc()['profile_image'] ?? null;

// Default profile image
$defaultImage = '../public/uploads/profile_images/default_profile.jpg';

// Handle profile image upload
if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['profile_image']['tmp_name'];
    $fileName = $_FILES['profile_image']['name'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $newFileName = uniqid('profile_', true) . '.' . $fileExtension;
    $uploadFileDir = '../public/uploads/profile_images/';

    $allowedExt = ['jpg','jpeg','png','gif'];

    if (in_array($fileExtension, $allowedExt)) {
        if (!is_dir($uploadFileDir)) {
            mkdir($uploadFileDir, 0777, true);
        }

        $dest_path = $uploadFileDir . $newFileName;

        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            $profileImagePath = $dest_path;
        } else {
            $profileImagePath = $currentImage ?: $defaultImage;
        }
    } else {
        $profileImagePath = $currentImage ?: $defaultImage;
    }
} else {
    $profileImagePath = $currentImage ?: $defaultImage;
}

// Update user info
$sql = "UPDATE users 
        SET user_name = ?, user_email = ?, first_name = ?, last_name = ?, professional_designation = ?, role = ?, profile_image = ? 
        WHERE user_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssssi", $user_name, $user_email, $first_name, $last_name, $professional_designation, $role, $profileImagePath, $user_id);

if ($stmt->execute()) {
    log_activity($conn, (int)$user_id, 'Updated profile information');
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => $stmt->error]);
}

$conn->close();