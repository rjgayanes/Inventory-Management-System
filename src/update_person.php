<?php
include 'db.php';
session_start();
require_once __DIR__ . '/activity_logger.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "error" => "Invalid request method"]);
    exit();
}

// Validate required fields
$required = ['person_id', 'first_name', 'last_name', 'office_name', 'role'];
foreach ($required as $field) {
    if (!isset($_POST[$field]) || empty($_POST[$field])) {
        echo json_encode(["success" => false, "error" => "Missing required field: $field"]);
        exit();
    }
}

// Get and sanitize form data
$person_id = intval($_POST['person_id']);
$first_name = trim($_POST['first_name']);
$last_name = trim($_POST['last_name']);
$professional_designations = isset($_POST['professional_designations']) ? trim($_POST['professional_designations']) : '';
$office_name = trim($_POST['office_name']);
$role = trim($_POST['role']);

// Handle profile image upload
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
        $profile_image_path = $filename; // Store only filename, same as add_person.php
    }
}

try {
    // Check if the person exists
    $check_sql = "SELECT person_ID FROM Persons WHERE person_ID = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $person_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 0) {
        echo json_encode(["success" => false, "error" => "Person not found"]);
        exit();
    }
    $check_stmt->close();
    
    // Update person - include profile_image if provided
    $sql = "UPDATE Persons 
            SET first_name = ?, last_name = ?, professional_designations = ?, 
                office_name = ?, role = ?";
    
    // Add profile_image to update if provided
    if ($profile_image_path) {
        $sql .= ", profile_image = ?";
    }
    
    $sql .= " WHERE person_ID = ?";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(["success" => false, "error" => "Prepare failed: " . $conn->error]);
        exit();
    }
    
    // Parameter binding based on whether profile_image is provided
    if ($profile_image_path) {
        $stmt->bind_param("ssssssi", $first_name, $last_name, $professional_designations, 
                         $office_name, $role, $profile_image_path, $person_id);
    } else {
        $stmt->bind_param("sssssi", $first_name, $last_name, $professional_designations, 
                         $office_name, $role, $person_id);
    }
    
    if ($stmt->execute()) {
        // Log update action
        $userId = isset($_SESSION['user_ID']) ? (int)$_SESSION['user_ID'] : 0;
        log_activity($conn, $userId, "Updated person #" . $person_id . " (" . $first_name . " " . $last_name . ")");
        
        echo json_encode(["success" => true, "message" => "Person updated successfully"]);
    } else {
        echo json_encode(["success" => false, "error" => "Database update failed: " . $stmt->error]);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => "Database error: " . $e->getMessage()]);
}

$conn->close();
?>
