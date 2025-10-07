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

// Set headers for HTML that will be converted to PDF by browser
header('Content-Type: text/html; charset=utf-8');

// Start output buffering
ob_start();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Property Plant and Equipment Inventory</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h2 {
            margin: 5px 0;
            font-size: 16px;
        }
        .top {
            display: flex;
            flex-direction: row;
            justify-content: space-between;
            margin-left: 20px;
            margin-right: 200px;
            margin-bottom: 20px;
            text-align: center;
        }
        .top h4 {
            margin-bottom: 0;
            text-decoration: underline;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #000;
            padding: 4px;
            text-align: left;
            font-size: 10px;
        }
        th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        .text-end {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .fund-row {
            background-color: #e6f3ff;
            font-weight: bold;
        }
        .total-row {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .footer {
            margin-top: 20px;
            display: flex;
            flex-direction: row;
            justify-content: space-evenly;
        }
        .certified, .approved, .witnessed {
            text-align: center;
        }
        .certified h4,
        .approved h4,
        .witnessed h4 {
            margin-top: 40px;
            margin-bottom: 0;
        }
        @media print {
            body {
                margin: 0;
            }
            .no-print {
                display: none;
            }
        }
    </style>
    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</head>
<body>
    <div class="no-print" style="background: #f0f0f0; padding: 10px; margin-bottom: 20px; border: 1px solid #ccc;">
        <p><strong>Instructions:</strong> Use your browser's print function (Ctrl+P) to save this page as PDF. The print dialog should open automatically.</p>
    </div>
    
    <div class="header">
        <?php if (!empty($equipment_type_name)): ?>
            <h2>INVENTORY - <?= strtoupper(htmlspecialchars($equipment_type_name)) ?></h2>
        <?php else: ?>
            <h2>INVENTORY FOR PROPERTY PLANT AND EQUIPMENT</h2>
        <?php endif; ?>
        <h3>As of <?php echo date('F d, Y'); ?></h3>
    </div>
    <div class="top">
        <div class="agency">
            <h4>SORSOGON STATE UNIVERSITY - BULAN CAMPUS</h4>
            <span>(Agency)</span>
        </div>
        <div class="accountable-officer">
            <h4>MA. ELENA C. DEMDAM</h4>
            <span>(Name of Accountable Officer)</span>
        </div>
        <div class="designation">
            <h4>Campus Director</h4>
            <span>Designation</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="2" style="width:10%;">Article</th> 
                <th rowspan="2" style="width:20%;">Description</th>
                <th rowspan="2" style="width:10%;">Property No.</th>
                <th rowspan="2" style="width:3%;">Unit<br>of<br>Measurement</th>
                <th rowspan="2" style="width:7%;">Unit Value</th>
                <th colspan="2">Balance per Stock card</th>
                <th colspan="2">On Hand per Count</th>
                <th colspan="2">Short</th>
                <th colspan="2">Over</th>
                <th rowspan="2" style="width:20%;">Remarks<br>
                    State Whereabouts<br>
                    Conditions, etc.
                </th>
            </tr>
            <tr>
                <th style="width:3%;">Qnty</th>
                <th style="width:5%;">Value</th>
                <th style="width:3%;">Qnty</th>
                <th style="width:5%;">Value</th>
                <th style="width:3%;">Qnty</th>
                <th style="width:3%;">Value</th>
                <th style="width:3%;">Qnty</th>
                <th style="width:5%;">Value</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Add fund row as the first row (matching SEP PDF)
            if ($inventory_result->num_rows > 0): 
                // Determine what to display for fund
                $fund_display = empty($fund_filter) ? "Fund Cluster" : $fund_code;
            ?>
                <tr class="fund-row">
                    <td colspan="14" style="text-align: start; font-weight: bold;">
                        <?= htmlspecialchars($fund_display) ?>
                    </td>
                </tr>
            <?php endif; ?>

            <?php
            // Initialize totals (matching SEP PDF)
            $total_balance_qty = 0;
            $total_balance_value = 0;
            $total_onhand_qty = 0;
            $total_onhand_value = 0;
            $total_short_qty = 0;
            $total_short_value = 0;
            $total_over_qty = 0;
            $total_over_value = 0;

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
                    
                    $formatted_unit_cost = number_format($row['unit_cost'], 2);
                    $formatted_balance_value = number_format($balance_value, 2);
                    $formatted_onhand_value = number_format($onhand_value, 2);
                    $formatted_short_value = number_format($short_value, 2);
                    $formatted_over_value = number_format($over_value, 2);
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($row['article']) ?>, <?php echo htmlspecialchars(substr($row['acquisition_date'], 0, 4)); ?></td>
                        <td><?= htmlspecialchars($row['description']) ?></td>
                        <td><?= htmlspecialchars($row['property_number']) ?></td>
                        <td class="text-center"><?= htmlspecialchars($row['unit_of_measure']) ?></td>
                        <td class="text-end"><?= $formatted_unit_cost ?></td>
                        <td class="text-center"><?= $balance_qty ?></td>
                        <td class="text-end"><?= $formatted_balance_value ?></td>
                        <td class="text-center"><?= $onhand_qty ?></td>
                        <td class="text-end"><?= $formatted_onhand_value ?></td>
                        <td class="text-center"><?= $short_qty ?></td>
                        <td class="text-end"><?= $formatted_short_value ?></td>
                        <td class="text-center"><?= $over_qty ?></td>
                        <td class="text-end"><?= $formatted_over_value ?></td>
                        <td><?= htmlspecialchars($row['remarks']) ?></td>
                    </tr>
                    <?php
                }
                
                // Format totals
                $formatted_total_balance_value = number_format($total_balance_value, 2);
                $formatted_total_onhand_value = number_format($total_onhand_value, 2);
                $formatted_total_short_value = number_format($total_short_value, 2);
                $formatted_total_over_value = number_format($total_over_value, 2);
                
                // Add total row (matching SEP PDF)
                ?>
                <tr class="total-row">
                    <td colspan="5" style="text-align: center; font-weight: bold;">TOTAL</td>
                    <td class="text-center" style="font-weight: bold;"></td>
                    <td class="text-end" style="font-weight: bold;"><?= $formatted_total_balance_value ?></td>
                    <td class="text-center" style="font-weight: bold;"></td>
                    <td class="text-end" style="font-weight: bold;"><?= $formatted_total_onhand_value ?></td>
                    <td class="text-center" style="font-weight: bold;"></td>
                    <td class="text-end" style="font-weight: bold;"></td>
                    <td class="text-center" style="font-weight: bold;"></td>
                    <td class="text-end" style="font-weight: bold;"><?= $formatted_total_over_value ?></td>
                    <td></td>
                </tr>
                <?php
            } else {
                ?>
                <tr><td colspan="14" class="text-center">No Property Plant and Equipment found</td></tr>
                <?php
            }
            ?>
        </tbody>
    </table>

    <div class="footer">
        <div class="certified">
            <span>Certified Correct by:</span>
            <h4><?= htmlspecialchars($_SESSION['full_name']) ?></h4>
            <span><?= htmlspecialchars($_SESSION['role']) ?></span>
        </div>
        <div class="approved">
            <span>Approve by:</span>
            <h4>MA. ELENA C. DEMDAM, Ph.D</h4>
            <span>Campus Director</span>
        </div>
        <div class="witnessed">
            <span>Witnessed by:</span>
            <h4>NERISA E. GUARDIAN</h4>
            <span>State Auditor III</span>
        </div>
    </div>
</body>
</html>
<?php
$html = ob_get_clean();
echo $html;

// Log activity
include 'activity_logger.php';
log_activity($conn, $_SESSION['user_ID'], "Exported PPE inventory to PDF");

$conn->close();
?>