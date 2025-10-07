<?php
include 'db.php';
session_start();
require_once __DIR__ . '/activity_logger.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "error" => "Invalid request method"]);
    exit();
}

// Validate required fields
$required = ['unit_ID', 'assign_to', 'assign_date'];
foreach ($required as $field) {
    if (!isset($_POST[$field]) || empty($_POST[$field])) {
        echo json_encode(["success" => false, "error" => "Missing required field: $field"]);
        exit();
    }
}

// Get and sanitize form data
$unit_ID = intval($_POST['unit_ID']);
$assign_to = intval($_POST['assign_to']);
$assign_date = $_POST['assign_date'];
$item_condition = isset($_POST['item_condition']) ? $_POST['item_condition'] : 'Good Condition';
$item_whereabouts = isset($_POST['item_whereabouts']) ? trim($_POST['item_whereabouts']) : '';

// Handle unit image upload
$unit_image_path = null;
if (isset($_FILES['unit_image']) && $_FILES['unit_image']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = '../public/uploads/unit_images/';
    
    // Create directory if it doesn't exist
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Generate unique filename
    $file_extension = pathinfo($_FILES['unit_image']['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_unit.' . $file_extension;
    $target_path = $upload_dir . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($_FILES['unit_image']['tmp_name'], $target_path)) {
        $unit_image_path = './uploads/unit_images/' . $filename; // Web accessible path
    }
}

// Validate ENUM values
$valid_conditions = ['Good Condition', 'Defective', 'Unserviceable'];
if (!in_array($item_condition, $valid_conditions)) {
    $item_condition = 'Good Condition'; // Default to safe value
}

try {
    // Check if the unit exists and get current status
    $check_sql = "SELECT status, assign_to FROM Item_Units WHERE unit_ID = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $unit_ID);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 0) {
        echo json_encode(["success" => false, "error" => "Item unit not found"]);
        exit();
    }
    
    $unit = $check_result->fetch_assoc();
    $check_stmt->close();
    
    // Update item unit - only set status to 'Assigned' (not 'Unserviceable')
    $sql = "UPDATE Item_Units 
            SET assign_to = ?, status = 'Assigned', status_updated_at = NOW(), 
                item_condition = ?, item_whereabouts = ?, condition_updated_at = NOW()";
    
    // Add unit_image to update if provided
    if ($unit_image_path) {
        $sql .= ", unit_image = ?";
    }
    
    $sql .= " WHERE unit_ID = ?";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(["success" => false, "error" => "Prepare failed: " . $conn->error]);
        exit();
    }
    
    // Parameter binding based on whether unit_image is provided
    if ($unit_image_path) {
        $stmt->bind_param("isssi", $assign_to, $item_condition, $item_whereabouts, $unit_image_path, $unit_ID);
    } else {
        $stmt->bind_param("issi", $assign_to, $item_condition, $item_whereabouts, $unit_ID);
    }
    
    if ($stmt->execute()) {
        $action = ($unit['status'] === 'Assigned') ? 'transferred' : 'assigned';
        
        // Log assignment action
        $userId = isset($_SESSION['user_ID']) ? (int)$_SESSION['user_ID'] : 0;
        log_activity($conn, $userId, ucfirst($action) . ' unit #' . $unit_ID . ' to person #' . $assign_to);
        
        echo json_encode(["success" => true, "action" => $action, "previous_status" => $unit['status']]);
    } else {
        echo json_encode(["success" => false, "error" => "Database update failed: " . $stmt->error]);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => "Database error: " . $e->getMessage()]);
}

$conn->close();
?>