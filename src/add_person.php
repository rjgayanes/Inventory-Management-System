<?php
include 'db.php';
session_start();
require_once __DIR__ . '/activity_logger.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "error" => "Invalid request method"]);
    exit();
}

// Check if user is logged in
if (!isset($_SESSION['user_ID'])) {
    echo json_encode(["success" => false, "error" => "Not authenticated"]);
    exit();
}

// Get form data
$first_name = trim($_POST['first_name']);
$last_name = trim($_POST['last_name']);
$professional_designations = isset($_POST['professional_designations']) ? trim($_POST['professional_designations']) : '';
$office_name = trim($_POST['office_name']);
$role = trim($_POST['role']);
$created_by = $_SESSION['user_ID'];

// Validate required fields
if (empty($first_name) || empty($last_name) || empty($office_name) || empty($role)) {
    echo json_encode(["success" => false, "error" => "All required fields must be filled"]);
    exit();
}

try {
    // Handle file upload
    $profile_image_path = null;
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../public/uploads/persons_images/';
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Generate unique filename
        $file_extension = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $file_extension;
        $target_path = $upload_dir . $filename;
        
        // Move uploaded file
        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_path)) {
        $profile_image_path = $filename; // Store only filename
        }
    }
    
    // Insert person into database
    $sql = "INSERT INTO Persons (first_name, last_name, professional_designations, office_name, role, profile_image, created_by) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssi", $first_name, $last_name, $professional_designations, $office_name, $role, $profile_image_path, $created_by);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        $newId = $conn->insert_id;
        // Log activity
        log_activity($conn, (int)$created_by, 'Added person #' . $newId . ' - ' . $first_name . ' ' . $last_name);
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => "Failed to add person"]);
    }
    
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => "Database error: " . $e->getMessage()]);
}
?>