<?php
include 'db.php';
require_once __DIR__ . '/activity_logger.php';
header('Content-Type: application/json');

// Check if query parameter is provided
if (!isset($_GET['q']) || empty(trim($_GET['q']))) {
    echo json_encode(["success" => false, "error" => "No search query provided"]);
    exit();
}

$searchQuery = '%' . trim($_GET['q']) . '%';

try {
    // Search for item units by barcode only - with detailed debugging info
    $sql = "SELECT 
                iu.unit_ID,
                iu.barcode,
                iu.status,
                iu.assign_to,
                p.person_ID as assigned_person_id,
                p.first_name as assigned_first_name,
                p.last_name as assigned_last_name,
                CONCAT(COALESCE(p.first_name, ''), ' ', COALESCE(p.last_name, '')) as assign_to_name,
                p.office_name,
                p.professional_designations,
                i.item_ID, 
                i.item_name, 
                i.property_number, 
                i.description,
                i.unit_of_measure,
                i.unit_cost,
                i.unit_quantity,
                i.unit_total_cost,
                i.estimated_useful_life,
                i.item_classification,
                f.fund_name,
                et.type_name
            FROM Item_Units iu
            JOIN Items i ON iu.item_ID = i.item_ID
            LEFT JOIN Fund_sources f ON i.fund_ID = f.fund_ID
            LEFT JOIN Equipment_types et ON i.type_ID = et.type_ID
            LEFT JOIN Persons p ON iu.assign_to = p.person_ID
            WHERE iu.barcode LIKE ?
            ORDER BY i.item_name, iu.unit_ID
            LIMIT 15";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $searchQuery);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
    
    echo json_encode(["success" => true, "items" => $items]);
    // Log search action (barcode search)
    if (isset($_SESSION) && isset($_SESSION['user_ID'])) {
        $qlen = strlen(trim($_GET['q']));
        log_activity($conn, (int)$_SESSION['user_ID'], 'Searched items by barcode (len=' . $qlen . ')');
    }
    
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => "Database error: " . $e->getMessage()]);
}
?>