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

// Set headers for HTML that will be converted to PDF by browser
header('Content-Type: text/html; charset=utf-8');

// Start output buffering
ob_start();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Unserviceable Semi-Expendable Properties Inventory</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 10px;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
        }
        .header h2 {
            margin: 3px 0;
            font-size: 14px;
        }
        .top-left, .top-left h4 {
            margin-bottom: 5px;
        }
        .top-mid {
            display: flex;
            flex-direction: row;
            justify-content: space-between;
            margin-left: 100px;
            margin-right: 450px;
            margin-bottom: 10px;
            text-align: center;
        }
        .top-mid h4 {
            margin-bottom: 5px;
            text-decoration: underline;
            font-size: 10px;
        }
        .top-right {
            display: flex;
            justify-content: end;
            margin-right: 300px;
            margin-top: 0;
        }
        .top-right h4 {
            margin-top: 0;
            margin-bottom: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        th, td {
            border: 1px solid #000;
            padding: 3px;
            text-align: left;
            font-size: 8px;
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
        .footer {
            margin-left: 5px;
            margin-top: 15px;
            margin-right: 50px;
            display: flex;
            flex-direction: row;
            justify-content: space-between;
        }
        .requested h4,
        .certified h4,
        .approved h4,
        .witnessed h4 {
            text-align: center;
            margin-top: 30px;
            margin-bottom: 0;
            font-size: 10px;
        }
        .requested h6,
        .certified h6,
        .approved h6,
        .witnessed h6 {
            margin-top: 0;
            text-align: center;
            font-size: 8px;
            font-weight: normal;
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
    <div class="no-print" style="background: #f0f0f0; padding: 8px; margin-bottom: 15px; border: 1px solid #ccc;">
        <p><strong>Instructions:</strong> Use your browser's print function (Ctrl+P) to save this page as PDF. The print dialog should open automatically.</p>
    </div>
    
    <div class="header">
        <h2>Inventory and Inspection Report of Unserviceable Semi-Expendable Property</h2>
        <h3>As of <?php echo date('F d, Y'); ?></h3>
    </div>
    <div class="top-left">
        <div class="agency">
            <h4 style="text-decoration: none;">Entity Name: SORSOGON STATE UNIVERSITY - BULAN CAMPUS</h4>
        </div>
    </div>
    <div class="top-right">
        <div class="fund">
            <?php
            // Determine fund cluster display text
            $fund_cluster_text = "All Funds";
            
            if (!empty($fund_filter)) {
                // Get fund abbreviation if filter is applied
                $fund_stmt = $conn->prepare("SELECT fund_abbreviation FROM Fund_sources WHERE fund_ID = ?");
                $fund_stmt->bind_param("i", $fund_filter);
                $fund_stmt->execute();
                $fund_result = $fund_stmt->get_result();
                
                if ($fund_row = $fund_result->fetch_assoc()) {
                    $fund_cluster_text = $fund_row['fund_abbreviation'];
                }
                $fund_stmt->close();
            }
            ?>
            <h4>Fund Cluster: <?= htmlspecialchars($fund_cluster_text) ?></h4>
        </div>
    </div>
    <div class="top-mid">
        <div class="accountable-officer">
            <h4><?= htmlspecialchars($personnel_info['first_name'] . ' ' . $personnel_info['last_name']) ?></h4>
            <span>Name of Accountable Officer</span>
        </div>
        <div class="designation">
            <h4><?= htmlspecialchars($personnel_info['role']) ?></h4>
            <span>Designation</span>
        </div>
        <div class="station">
            <h4>Sorsogon State University - Bulan Campus</h4>
            <span>Station</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th colspan="9">INVENTORY</th> 
                <th colspan="5">INSPECTION and DISPOSAL</th>
                <th rowspan="2">Appraised<br>Value</th>
                <th colspan="2">Record of Sales</th>
            </tr>
            <tr>
                <!-- inventory -->
                <th style="width:5%;">Year<br>Acquired</th>
                <th style="width:20%;">Particulars/ Articles</th>
                <th style="width:8%;">Account Code</th>
                <th style="width:4%;">Qnty.</th>
                <th style="width:6%;">Unit Cost</th>
                <th style="width:7%;">Total Cost</th>
                <th style="width:8%;">Accumulated<br>Impairment<br>Losses</th>
                <th style="width:7%;">Carrying<br>Amount</th>
                <th style="width:8%;">Remarks</th>

                <!-- inspection and disposal -->
                <th style="width:4%;">Sales</th>
                <th style="width:4%;">Transfer</th>
                <th style="width:4%;">Destruction</th>
                <th style="width:6%;">Others<br>(Specify)</th>
                <th style="width:4%;">Total</th>

                <!-- record of sales -->
                <th style="width:4%;">OR NO.</th>
                <th style="width:5%;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($sep_result->num_rows > 0) {
                while ($row = $sep_result->fetch_assoc()) {
                    $formatted_unit_cost = number_format($row['unit_cost'], 2);
                    $formatted_total_cost = number_format($row['unit_total_cost'], 2);
                    ?>
                    <tr>
                        <!-- Inventory -->
                        <td class="text-center"><?= htmlspecialchars(substr($row['acquisition_date'], 0, 4)) ?></td>
                        <td><?= htmlspecialchars($row['description'] . ', ' . $row['item_name']) ?></td>
                        <td class="text-center"><?= htmlspecialchars($row['property_number']) ?></td>
                        <td class="text-center"><?= htmlspecialchars($row['defective_unserviceable_count']) ?></td>
                        <td class="text-end"><?= $formatted_unit_cost ?></td>
                        <td class="text-end"><?= $formatted_total_cost ?></td>
                        <td class="text-center"></td>
                        <td class="text-center"></td>
                        <td class="text-center"><?= htmlspecialchars($row['item_condition']) ?></td>
                        
                        <!-- Inspection and Disposal -->
                        <td class="text-center"></td>
                        <td class="text-center"></td>
                        <td class="text-center"></td>
                        <td class="text-center"></td>
                        <td class="text-center"></td>
                        
                        <!-- Appraised Value -->
                        <td class="text-center"></td>
                        
                        <!-- Record of Sales -->
                        <td class="text-center"></td>
                        <td class="text-center"></td>
                    </tr>
                    <?php
                }
            } else {
                ?>
                <tr><td colspan="17" class="text-center">No Unserviceable Semi-Expendable Properties found</td></tr>
                <?php
            }
            ?>
            <tr>
                <td colspan="17">
                    <div class="footer">
                        <div class="requested">
                            <span>I HEREBY request inspection and disposition, pursuant to Section 79 of PD 1445, of the property enumerated above.</span><br>
                            <br>
                            <br>
                            <span>Requested by:</span>
                            <h4 style="margin-top:35px;"><?= htmlspecialchars($personnel_info['first_name'] . ' ' . $personnel_info['last_name']) ?><?= !empty($personnel_info['professional_designations']) ? ', ' . htmlspecialchars($personnel_info['professional_designations']) : '' ?></h4>
                            <h6><?= htmlspecialchars($personnel_info['role']) ?></h6>
                        </div>
                        <div class="approved">
                            <h6 style="margin-top: 30px;text-align:left;">Approve by:</h6>
                            <h4 style="margin-top: 35px;">MA. ELENA C. DEMDAM, Ph.D</h4>
                            <h6>Campus Director</h6>
                        </div>
                        <div class="certified">
                            <span>
                                I CERTIFY that I have inspected each and every article<br>
                                enumerated in this report, and that the disposition made<br>
                                thereof was, in my judgement, the best for the public<br>interest.
                            </span>
                            <h4>MARK ANTHONY D. DIPAD, MIT</h4>
                            <h6>Inspection Officer</h6>
                        </div>
                        <div class="witnessed">
                            <span>
                                I CERTIFY that I have witnessed the<br>
                                disposition of the articles<br>
                                enumerated in this report this<br>
                                ________day of <span style="text-decoration: underline;"><?php echo date('F'); ?></span> <?php echo date('Y'); ?>
                            </span>
                            <h4>GERALD E. FULAY, JD, MAMPA</h4>
                            <h6>Witness</h6>
                        </div>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</body>
</html>
<?php
$html = ob_get_clean();
echo $html;

// Log activity
include 'activity_logger.php';
log_activity($conn, $_SESSION['user_ID'], "Exported unserviceable SEP inventory to PDF");

$conn->close();
?>