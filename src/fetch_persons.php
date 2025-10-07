<?php
include 'db.php';
header('Content-Type: application/json');

try {
    $sql = "SELECT person_ID, first_name, last_name, professional_designations, office_name 
            FROM Persons 
            WHERE status = 'Active' 
            ORDER BY last_name, first_name";
    $result = $conn->query($sql);
    
    $persons = [];
    while ($row = $result->fetch_assoc()) {
        $persons[] = $row;
    }
    
    echo json_encode(["success" => true, "persons" => $persons]);
    
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => "Database error: " . $e->getMessage()]);
}
?>