<?php
session_start();
include 'db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_ID']) || $_SESSION['role'] !== 'Supply Officer') {
    header("Location: ../public/login.php?error=Access+denied");
    exit();
}

// Get filter parameters
$fund_filter = isset($_GET['fund_filter']) ? $_GET['fund_filter'] : '';
$type_filter = isset($_GET['type_filter']) ? $_GET['type_filter'] : '';
$personnel_filter = isset($_GET['personnel_filter']) ? $_GET['personnel_filter'] : '';
$condition_filter = isset($_GET['condition_filter']) ? $_GET['condition_filter'] : '';

// Get personnel information if personnel filter is applied
$personnel_info = [
    'first_name' => '',
    'last_name' => '',
    'professional_designations' => '',
    'role' => ''
];

if (!empty($personnel_filter)) {
    $person_sql = "SELECT first_name, last_name, professional_designations, role FROM Persons WHERE person_ID = ?";
    $person_stmt = $conn->prepare($person_sql);
    $person_stmt->bind_param("i", $personnel_filter);
    $person_stmt->execute();
    $person_result = $person_stmt->get_result();
    
    if ($person_row = $person_result->fetch_assoc()) {
        $personnel_info = [
            'first_name' => $person_row['first_name'] ?? '',
            'last_name' => $person_row['last_name'] ?? '',
            'professional_designations' => $person_row['professional_designations'] ?? '',
            'role' => $person_row['role'] ?? ''
        ];
    }
    $person_stmt->close();
}

// Build base SQL query for unserviceable SEP
$sep_sql = "SELECT 
                i.item_ID,
                i.item_name,
                i.description,
                i.property_number,
                i.acquisition_date,
                i.unit_cost,
                i.unit_quantity,
                i.unit_total_cost,
                i.item_classification,
                i.fund_ID,
                i.type_ID,
                fs.fund_name,
                et.type_name,
                p.person_ID,
                p.first_name,
                p.last_name,
                p.professional_designations,
                p.office_name,
                p.role,
                COUNT(iu.unit_ID) as defective_unserviceable_count,
                iu.item_condition
            FROM Items i
            INNER JOIN Item_Units iu ON i.item_ID = iu.item_ID
            LEFT JOIN Fund_sources fs ON i.fund_ID = fs.fund_ID
            LEFT JOIN Equipment_types et ON i.type_ID = et.type_ID
            LEFT JOIN Persons p ON iu.assign_to = p.person_ID
            WHERE (iu.item_condition = 'Unserviceable' OR iu.item_condition = 'Defective')
            AND i.item_classification = 'Semi-Expendable Property'";

// Add filters to both queries
$where_conditions = [];
$params = [];
$types = '';

if (!empty($fund_filter)) {
    $where_conditions[] = "i.fund_ID = ?";
    $params[] = $fund_filter;
    $types .= 'i';
}

if (!empty($type_filter)) {
    $where_conditions[] = "i.type_ID = ?";
    $params[] = $type_filter;
    $types .= 'i';
}

if (!empty($personnel_filter)) {
    $where_conditions[] = "iu.assign_to = ?";
    $params[] = $personnel_filter;
    $types .= 'i';
}

if (!empty($condition_filter)) {
    $where_conditions[] = "iu.item_condition = ?";
    $params[] = $condition_filter;
    $types .= 's';
}

if (!empty($where_conditions)) {
    $sep_sql .= " AND " . implode(" AND ", $where_conditions);
}

$sep_sql .= " GROUP BY i.item_ID ORDER BY i.acquisition_date DESC";

// Execute query
$sep_result = false;
try {
    if (!empty($params)) {
        $stmt = $conn->prepare($sep_sql);
        if ($stmt) {
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $sep_result = $stmt->get_result();
        }
    } else {
        $sep_result = $conn->query($sep_sql);
    }
} catch (Exception $e) {
    die("Database error: " . $e->getMessage());
}

if (!$sep_result) {
    die("Query failed: " . $conn->error);
}

// Set headers for Excel download
$filename = 'Unserviceable_SEP_Inventory_' . date('Y-m-d_H-i-s') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

// Create file handle
$output = fopen('php://output', 'w');

// Add BOM for UTF-8 Excel compatibility
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Write header information matching PDF structure
fputcsv($output, ['SORSOGON STATE UNIVERSITY - BULAN CAMPUS']);
fputcsv($output, ['Inventory and Inspection Report of Unserviceable Semi-Expendable Property']);
fputcsv($output, ['As of ' . date('F d, Y')]);
fputcsv($output, []); // Empty row

// Entity Name
fputcsv($output, ['Entity Name:', 'SORSOGON STATE UNIVERSITY - BULAN CAMPUS']);

// Fund Cluster
$fund_cluster_text = "All Funds";
if (!empty($fund_filter)) {
    $fund_stmt = $conn->prepare("SELECT fund_abbreviation FROM Fund_sources WHERE fund_ID = ?");
    $fund_stmt->bind_param("i", $fund_filter);
    $fund_stmt->execute();
    $fund_result = $fund_stmt->get_result();
    
    if ($fund_row = $fund_result->fetch_assoc()) {
        $fund_cluster_text = $fund_row['fund_abbreviation'];
    }
    $fund_stmt->close();
}
fputcsv($output, ['Fund Cluster:', $fund_cluster_text]);
fputcsv($output, []); // Empty row

// Accountable Officer Information
fputcsv($output, [
    'Name of Accountable Officer:', 
    $personnel_info['first_name'] . ' ' . $personnel_info['last_name'],
    'Designation:',
    $personnel_info['role'],
    'Station:',
    'Sorsogon State University - Bulan Campus'
]);
fputcsv($output, []); // Empty row

// Main table headers - matching PDF structure exactly
fputcsv($output, [
    // INVENTORY section (9 columns)
    'Year Acquired',
    'Particulars/ Articles',
    'Account Code',
    'Qnty.',
    'Unit Cost',
    'Total Cost',
    'Accumulated Impairment Losses',
    'Carrying Amount',
    'Remarks',
    
    // INSPECTION and DISPOSAL section (5 columns)
    'Sales',
    'Transfer',
    'Destruction',
    'Others (Specify)',
    'Total',
    
    // Appraised Value
    'Appraised Value',
    
    // Record of Sales (2 columns)
    'OR NO.',
    'Amount'
]);

// Write data rows
if ($sep_result->num_rows > 0) {
    while ($row = $sep_result->fetch_assoc()) {
        fputcsv($output, [
            // INVENTORY data
            substr($row['acquisition_date'], 0, 4), // Year Acquired
            $row['description'] . ', ' . $row['item_name'], // Particulars/ Articles
            $row['property_number'], // Account Code
            $row['defective_unserviceable_count'], // Qnty.
            number_format($row['unit_cost'], 2), // Unit Cost
            number_format($row['unit_total_cost'], 2), // Total Cost
            '', // Accumulated Impairment Losses (empty)
            '', // Carrying Amount (empty)
            $row['item_condition'], // Remarks
            
            // INSPECTION and DISPOSAL data (empty)
            '', // Sales
            '', // Transfer
            '', // Destruction
            '', // Others (Specify)
            '', // Total
            
            // Appraised Value (empty)
            '',
            
            // Record of Sales (empty)
            '', // OR NO.
            ''  // Amount
        ]);
    }
} else {
    fputcsv($output, ['No Unserviceable Semi-Expendable Properties found']);
}

// Add footer section matching PDF structure
fputcsv($output, []); // Empty row
fputcsv($output, ['REQUESTED BY:', '', 'APPROVED BY:', '', 'CERTIFIED BY:', '', 'WITNESSED BY:']);
fputcsv($output, [
    $personnel_info['first_name'] . ' ' . $personnel_info['last_name'],
    '',
    'MA. ELENA C. DEMDAM, Ph.D',
    '',
    'MARK ANTHONY D. DIPAD, MIT',
    '',
    'GERALD E. FULAY, JD, MAMPA'
]);
fputcsv($output, [
    $personnel_info['role'],
    '',
    'Campus Director',
    '',
    'Inspection Officer',
    '',
    'Witness'
]);
fputcsv($output, []); // Empty row
fputcsv($output, [
    'I HEREBY request inspection and disposition, pursuant to Section 79 of PD 1445, of the property enumerated above.',
    '',
    '',
    '',
    'I CERTIFY that I have inspected each and every article enumerated in this report, and that the disposition made thereof was, in my judgement, the best for the public interest.',
    '',
    'I CERTIFY that I have witnessed the disposition of the articles enumerated in this report this ________day of ' . date('F') . ' ' . date('Y')
]);

fclose($output);

// Log activity
include 'activity_logger.php';
log_activity($conn, $_SESSION['user_ID'], "Exported unserviceable SEP inventory to Excel");

$conn->close();
exit();
?>