<?php
session_start();
include './db.php';

// Query fund distribution
$sql = "SELECT f.fund_name, COUNT(i.item_ID) AS total_items
        FROM Fund_sources f
        LEFT JOIN Items i ON f.fund_ID = i.fund_ID
        GROUP BY f.fund_ID";

$result = $conn->query($sql);

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

// Return JSON only
header('Content-Type: application/json');
echo json_encode($data);