<?php
include 'db.php';
require_once __DIR__ . '/activity_logger.php';
header('Content-Type: application/json');

// Handle both search and default load
$searchQuery = '';
$whereClause = '';

if (isset($_GET['q']) && trim($_GET['q']) !== '') {
	$searchQuery = '%' . trim($_GET['q']) . '%';
	$whereClause = 'WHERE i.item_name LIKE ? ORDER BY i.item_name ASC, iu.unit_ID ASC LIMIT 100';
} else {
	// Default: show 10 most recently added units
	$whereClause = 'ORDER BY iu.unit_ID DESC LIMIT 10';
}

try {
	$sql = "SELECT 
	            iu.unit_ID,
	            iu.barcode,
	            iu.barcode_image,
	            iu.unit_image,
	            iu.assign_to,
	            iu.status,
	            iu.item_condition,
	            iu.item_whereabouts,
	            i.item_ID,
	            i.item_name,
	            i.property_number,
	            p.first_name as assigned_first_name,
	            p.last_name as assigned_last_name,
	            CONCAT(COALESCE(p.first_name, ''), ' ', COALESCE(p.last_name, '')) as assign_to_name
	        FROM Item_Units iu
	        JOIN Items i ON iu.item_ID = i.item_ID
	        LEFT JOIN Persons p ON iu.assign_to = p.person_ID
	        " . $whereClause;

	$stmt = $conn->prepare($sql);
	if (!empty($searchQuery)) {
		$stmt->bind_param("s", $searchQuery);
	}
	$stmt->execute();
	$result = $stmt->get_result();

	$rows = [];
	while ($row = $result->fetch_assoc()) {
		$rows[] = $row;
	}

    echo json_encode(["success" => true, "items" => $rows, "count" => count($rows), "sql" => $sql]);
    // Log search action (anonymize query length)
    if (isset($_SESSION) && isset($_SESSION['user_ID'])) {
        $qlen = isset($_GET['q']) ? strlen(trim($_GET['q'])) : 0;
        log_activity($conn, (int)$_SESSION['user_ID'], 'Searched item units by name (len=' . $qlen . ')');
    }
} catch (Exception $e) {
	echo json_encode(["success" => false, "error" => "Database error: " . $e->getMessage()]);
}

?>


