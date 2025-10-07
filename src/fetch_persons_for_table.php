<?php
include 'db.php';
header('Content-Type: application/json');

try {
    $sql = "SELECT person_ID, first_name, last_name, professional_designations, office_name, role, status, profile_image 
            FROM Persons 
            ORDER BY last_name, first_name";
    $result = $conn->query($sql);
    
    // Modify your SQL query or process the results
    $persons = [];
    while ($row = $result->fetch_assoc()) {
        // Add full path to image
        $row['profile_image_url'] = $row['profile_image'] 
            ? '../public/uploads/persons_images/' . $row['profile_image'] 
            : './uploads/profile_images/default_profile.jpg';
        $persons[] = $row;
    }
    
    echo json_encode(["success" => true, "persons" => $persons]);
    
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => "Database error: " . $e->getMessage()]);
}
?>