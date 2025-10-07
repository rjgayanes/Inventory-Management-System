<?php
session_start();
include './db.php';

header('Content-Type: application/json');

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(["error" => "No person ID provided"]);
    exit;
}

$personID = intval($_GET['id']);

// Fetch person info
$sqlPerson = "SELECT first_name, last_name, professional_designations, office_name, role, profile_image 
              FROM Persons WHERE person_ID = ?";
$stmt = $conn->prepare($sqlPerson);
$stmt->bind_param("i", $personID);
$stmt->execute();
$resultPerson = $stmt->get_result();

if ($resultPerson->num_rows === 0) {
    echo json_encode(["error" => "Person not found"]);
    exit;
}

$person = $resultPerson->fetch_assoc();
$personName = $person['first_name'] . ' ' . $person['last_name'];
if (!empty($person['professional_designations'])) {
    $personName .= ', ' . $person['professional_designations'];
}
$officeName = $person['office_name'];
$role = $person['role'];
$profileImage = !empty($person['profile_image'])
    ? '../public/uploads/persons_images/' . $person['profile_image']
    : './uploads/profile_images/default_profile.jpg';

// Function to format date as "Month Year"
function formatDate($dateString) {
    if (empty($dateString)) {
        return 'No date';
    }
    
    $date = DateTime::createFromFormat('Y-m-d', $dateString);
    if ($date === false) {
        return 'Invalid date';
    }
    
    return $date->format('F Y');
}

// Fetch assigned items for this person with count of units
$sqlItems = "SELECT 
                i.item_ID,
                i.item_name, 
                i.description, 
                i.unit_of_measure, 
                i.unit_cost, 
                i.property_number, 
                i.estimated_useful_life,
                i.acquisition_date,
                COUNT(iu.unit_ID) as unit_count,
                SUM(i.unit_cost) as total_cost,
                iu.item_condition
             FROM Item_Units iu
             JOIN Items i ON iu.item_ID = i.item_ID
             WHERE iu.assign_to = ?
             GROUP BY i.item_ID, i.item_name, i.description, i.unit_of_measure, 
                      i.unit_cost, i.property_number, i.estimated_useful_life, i.acquisition_date
             ORDER BY i.item_name";
$stmtItems = $conn->prepare($sqlItems);
$stmtItems->bind_param("i", $personID);
$stmtItems->execute();
$resultItems = $stmtItems->get_result();

$rowsHtml = '';
if ($resultItems->num_rows > 0) {
    while ($row = $resultItems->fetch_assoc()) {
        $totalCost = $row['unit_count'] * $row['unit_cost'];
        
        $rowsHtml .= "<tr>
            <td>{$row['unit_count']}</td>
            <td>{$row['unit_of_measure']}</td>
            <td>" . number_format($row['unit_cost'], 2) . "</td>
            <td>" . number_format($totalCost, 2) . "</td>
            <td style='text-align:left;'>{$row['item_name']}, (" . formatDate($row['acquisition_date']) . ")<br>";
        
        // Add description if it exists
        if (!empty($row['description'])) {
            $rowsHtml .= "- {$row['description']}, ({$row['item_condition']})";
        }
        
        $rowsHtml .= "</td>
            <td>{$row['property_number']}</td>
            <td>{$row['estimated_useful_life']}</td>
        </tr>";
    }
} else {
    $rowsHtml = "<tr><td colspan='7' class='text-center'>No items assigned</td></tr>";
}

echo json_encode([
    "personName" => $personName,
    "officeName" => $officeName,
    "role" => $role,
    "profileImage" => $profileImage,
    "rows" => $rowsHtml
]);

exit;