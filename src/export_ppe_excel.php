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
$year_start = isset($_GET['year_start']) ? $_GET['year_start'] : '';
$year_end = isset($_GET['year_end']) ? $_GET['year_end'] : '';

// Get equipment type name if filter is used
$equipment_type_name = '';
if (!empty($type_filter)) {
    $type_stmt = $conn->prepare("SELECT type_name FROM Equipment_types WHERE type_ID = ?");
    $type_stmt->bind_param("i", $type_filter);
    $type_stmt->execute();
    $type_result = $type_stmt->get_result();
    if ($type_row = $type_result->fetch_assoc()) {
        $equipment_type_name = $type_row['type_name'];
    }
}

// Get fund code if fund filter is applied
$fund_code = '';
if (!empty($fund_filter)) {
    $fund_stmt = $conn->prepare("SELECT fund_code FROM Fund_sources WHERE fund_ID = ?");
    $fund_stmt->bind_param("i", $fund_filter);
    $fund_stmt->execute();
    $fund_result = $fund_stmt->get_result();
    if ($fund_row = $fund_result->fetch_assoc()) {
        $fund_code = $fund_row['fund_code'];
    }
}

// Build base SQL query (same as in inventories_ppe.php)
$inventory_sql = "SELECT 
    i.item_ID,
    i.item_name AS article,
    i.description,
    i.property_number,
    i.unit_of_measure,
    i.unit_cost,
    i.unit_quantity,
    i.unit_total_cost,
    i.acquisition_date,
    fs.fund_ID,
    fs.fund_name,
    fs.fund_code,
    et.type_ID,
    et.type_name,
    COUNT(u.unit_ID) AS total_units,
    SUM(CASE WHEN u.assign_to IS NULL THEN 1 ELSE 0 END) AS available_units,
    SUM(CASE WHEN u.assign_to IS NOT NULL THEN 1 ELSE 0 END) AS assigned_units,
    GROUP_CONCAT(DISTINCT 
        CONCAT(
            COALESCE(CONCAT(p.last_name), 'Unassigned'),
            ' / ',
            CASE 
                WHEN u.item_whereabouts IS NOT NULL THEN u.item_whereabouts
                WHEN p.office_name IS NOT NULL THEN p.office_name
                ELSE 'Supply Office'
            END,
            ' (',
            COALESCE(u.item_condition, 'Unknown condition'),
            ')'
        )
        SEPARATOR ', '
    ) AS remarks
FROM Items i
LEFT JOIN Fund_sources fs ON i.fund_ID = fs.fund_ID
LEFT JOIN Equipment_types et ON i.type_ID = et.type_ID
LEFT JOIN Item_Units u ON i.item_ID = u.item_ID
LEFT JOIN Persons p ON u.assign_to = p.person_ID
WHERE i.item_classification = 'Property Plant and Equipment'";

// Add filters
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

if (!empty($year_start) && !empty($year_end)) {
    $where_conditions[] = "YEAR(i.acquisition_date) BETWEEN ? AND ?";
    $params[] = $year_start;
    $params[] = $year_end;
    $types .= 'ii';
} elseif (!empty($year_start)) {
    $where_conditions[] = "YEAR(i.acquisition_date) >= ?";
    $params[] = $year_start;
    $types .= 'i';
} elseif (!empty($year_end)) {
    $where_conditions[] = "YEAR(i.acquisition_date) <= ?";
    $params[] = $year_end;
    $types .= 'i';
}

if (!empty($where_conditions)) {
    $inventory_sql .= " AND " . implode(" AND ", $where_conditions);
}

$inventory_sql .= " GROUP BY i.item_ID, i.item_name, i.description, i.property_number, 
         i.unit_of_measure, i.unit_cost, i.unit_quantity, i.unit_total_cost, 
         fs.fund_name, et.type_name, fs.fund_ID, et.type_ID, i.acquisition_date, fs.fund_code
ORDER BY i.acquisition_date DESC";

// Execute query
$inventory_result = false;
try {
    if (!empty($params)) {
        $stmt = $conn->prepare($inventory_sql);
        if ($stmt) {
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $inventory_result = $stmt->get_result();
        }
    } else {
        $inventory_result = $conn->query($inventory_sql);
    }
} catch (Exception $e) {
    die("Database error: " . $e->getMessage());
}

if (!$inventory_result) {
    die("Query failed: " . $conn->error);
}

// Set headers for Excel download
$filename = 'PPE_Inventory_' . date('Y-m-d_H-i-s') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

// Create file handle
$output = fopen('php://output', 'w');

// Add BOM for UTF-8 Excel compatibility
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Write header information (matching SEP PDF structure)
fputcsv($output, ['SORSOGON STATE UNIVERSITY - BULAN CAMPUS', 'MA. ELENA C. DEMDAM', 'Campus Director']);
fputcsv($output, ['(Agency)', '(Name of Accountable Officer)', 'Designation']);
fputcsv($output, []); // Empty row

// Write report title (matching SEP PDF)
if (!empty($equipment_type_name)) {
    fputcsv($output, ['INVENTORY - ' . strtoupper($equipment_type_name)]);
} else {
    fputcsv($output, ['INVENTORY FOR PROPERTY PLANT AND EQUIPMENT']);
}
fputcsv($output, ['As of ' . date('F d, Y')]);
fputcsv($output, []); // Empty row

// Write fund row if applicable (matching SEP PDF)
if ($inventory_result->num_rows > 0) {
    $fund_display = empty($fund_filter) ? "Fund Cluster" : $fund_code;
    fputcsv($output, [$fund_display, '', '', '', '', '', '', '', '', '', '', '', '', '']);
}

// Write CSV headers - matching SEP PDF table structure exactly
fputcsv($output, [
    'Article',
    'Description', 
    'Property No.',
    'Unit of Measurement',
    'Unit Value',
    'Balance per Stock card Qnty',
    'Balance per Stock card Value',
    'On Hand per Count Qnty',
    'On Hand per Count Value',
    'Short Qnty',
    'Short Value',
    'Over Qnty',
    'Over Value',
    'Remarks (State Whereabouts Conditions, etc.)'
]);

// Initialize totals (matching SEP PDF)
$total_balance_qty = 0;
$total_balance_value = 0;
$total_onhand_qty = 0;
$total_onhand_value = 0;
$total_short_qty = 0;
$total_short_value = 0;
$total_over_qty = 0;
$total_over_value = 0;

// Write data rows (matching SEP PDF data structure)
if ($inventory_result->num_rows > 0) {
    // Reset pointer to beginning of result set
    $inventory_result->data_seek(0);
    
    while ($row = $inventory_result->fetch_assoc()) {
        // Balance (original quantity & total cost from Items)
        $balance_qty = $row['unit_quantity'];
        $balance_value = $row['unit_total_cost'];
        
        // Onhand = units not yet assigned
        $onhand_qty = $row['unit_quantity'];
        $onhand_value = $row['unit_total_cost'];

        // Defaults
        $over_qty = 0;
        $over_value = 0;
        $short_qty = 0;
        $short_value = 0;

        // Rule: if item_units are unassigned → count as "over"
        if ($row['available_units'] > 0) {
            $over_qty = $row['available_units'];
            $over_value = $row['available_units'] * $row['unit_cost'];
        }
        
        // Add to totals
        $total_balance_qty += $balance_qty;
        $total_balance_value += $balance_value;
        $total_onhand_qty += $onhand_qty;
        $total_onhand_value += $onhand_value;
        $total_short_qty += $short_qty;
        $total_short_value += $short_value;
        $total_over_qty += $over_qty;
        $total_over_value += $over_value;
        
        // Format article with year (matching SEP PDF)
        $article_with_year = $row['article'] . ', ' . substr($row['acquisition_date'], 0, 4);
        
        fputcsv($output, [
            $article_with_year,
            $row['description'],
            $row['property_number'],
            $row['unit_of_measure'],
            number_format($row['unit_cost'], 2),
            $balance_qty,
            number_format($balance_value, 2),
            $onhand_qty,
            number_format($onhand_value, 2),
            $short_qty,
            number_format($short_value, 2),
            $over_qty,
            number_format($over_value, 2),
            $row['remarks']
        ]);
    }
    
    // Add total row (matching SEP PDF)
    fputcsv($output, [
        'TOTAL', '', '', '', '',
        '', // Balance Qnty (empty as in SEP PDF)
        number_format($total_balance_value, 2),
        '', // On Hand Qnty (empty as in SEP PDF)
        number_format($total_onhand_value, 2),
        '', // Short Qnty (empty as in SEP PDF)
        '', // Short Value (empty as in SEP PDF)
        '', // Over Qnty (empty as in SEP PDF)
        number_format($total_over_value, 2),
        ''
    ]);
} else {
    fputcsv($output, ['No Property Plant and Equipment found']);
}

// Add footer information (matching SEP PDF)
fputcsv($output, []); // Empty row
fputcsv($output, ['Certified Correct by:', 'Approve by:', 'Witnessed by:']);
fputcsv($output, [$_SESSION['full_name'], 'MA. ELENA C. DEMDAM, Ph.D', 'NERISA E. GUARDIAN']);
fputcsv($output, [$_SESSION['role'], 'Campus Director', 'State Auditor III']);

fclose($output);

// Log activity
include 'activity_logger.php';
log_activity($conn, $_SESSION['user_ID'], "Exported PPE inventory to Excel");

$conn->close();
exit();
?>