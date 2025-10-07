<?php
session_start();
require '../vendor/autoload.php';
include 'db.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// Check if user is logged in and is Supply Officer
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
    $type_stmt->close();
}

// Build base SQL query for unserviceable PPE
$ppe_sql = "SELECT 
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
            AND i.item_classification = 'Property Plant and Equipment'";

// Add filters to query
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
    $ppe_sql .= " AND " . implode(" AND ", $where_conditions);
}

$ppe_sql .= " GROUP BY i.item_ID ORDER BY i.acquisition_date DESC";

// Execute query
$ppe_result = false;
try {
    if (!empty($params)) {
        $stmt = $conn->prepare($ppe_sql);
        if ($stmt) {
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $ppe_result = $stmt->get_result();
        }
    } else {
        $ppe_result = $conn->query($ppe_sql);
    }
} catch (Exception $e) {
    die("Database error: " . $e->getMessage());
}

if (!$ppe_result) {
    die("Query failed: " . $conn->error);
}

// Get fund cluster abbreviation for display
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

// Create PhpSpreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle("Unserviceable PPE");

// Styles
$centerStyle = [
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
        'wrapText' => true,
    ],
];
$leftWrap = [
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_LEFT,
        'vertical' => Alignment::VERTICAL_TOP,
        'wrapText' => true,
    ],
];
$borderAll = [
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
];

// Header / Title
$sheet->mergeCells('A2:R2');
$sheet->setCellValue('A2', 'INVENTORY AND INSPECTION REPORT OF UNSERVICEABLE PROPERTY');
$sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
$sheet->getStyle('A2')->applyFromArray($centerStyle);

$sheet->mergeCells('A3:R3');
$sheet->setCellValue('A3', 'As of ' . date('F d, Y'));
$sheet->getStyle('A3')->applyFromArray($centerStyle)->getFont()->setBold(true);

// Entity & Fund
$sheet->mergeCells('A5:E5');
$sheet->setCellValue('A5', 'Entity Name: SORSOGON STATE UNIVERSITY - BULAN CAMPUS');
$sheet->mergeCells('M5:Q5');
$sheet->setCellValue('M5', 'Fund Cluster: ' . $fund_cluster_text);
$sheet->getStyle('A5:R5')->applyFromArray($leftWrap)->getFont()->setBold(true);

// Accountable officer info
$sheet->mergeCells('A7:C7');
$sheet->setCellValue('A7', $personnel_info['first_name'] . ' ' . $personnel_info['last_name']);
$sheet->mergeCells('D7:F7');
$sheet->setCellValue('D7', $personnel_info['role']);
$sheet->mergeCells('G7:K7');
$sheet->setCellValue('G7', 'Sorsogon State University - Bulan Campus');
$sheet->getStyle('A7:R7')->applyFromArray($centerStyle)->getFont()->setBold(true);

$sheet->mergeCells('A8:C8');
$sheet->setCellValue('A8', 'Name of Accountable Officer');
$sheet->mergeCells('D8:F8');
$sheet->setCellValue('D8', 'Designation');
$sheet->mergeCells('G8:K8');
$sheet->setCellValue('G8', 'Station');
$sheet->getStyle('A8:R8')->applyFromArray($centerStyle);

// Table headers
$hdrRow1 = 10;
$sheet->mergeCells("A{$hdrRow1}:F{$hdrRow1}");
$sheet->setCellValue("A{$hdrRow1}", "INVENTORY");
$sheet->mergeCells("G{$hdrRow1}:J{$hdrRow1}");
$sheet->setCellValue("G{$hdrRow1}", "ACCOUNTING");
$sheet->mergeCells("K{$hdrRow1}:O{$hdrRow1}");
$sheet->setCellValue("K{$hdrRow1}", "INSPECTION and DISPOSAL");
$sheet->mergeCells("P{$hdrRow1}:P" . ($hdrRow1+3));
$sheet->setCellValue("P{$hdrRow1}", "Appraised Value");
$sheet->mergeCells("Q{$hdrRow1}:R{$hdrRow1}");
$sheet->setCellValue("Q{$hdrRow1}", "Record of Sales");

$sheet->getStyle("A{$hdrRow1}:R{$hdrRow1}")->applyFromArray($centerStyle);
$sheet->getStyle("A{$hdrRow1}:R{$hdrRow1}")->getFont()->setBold(true);

// second header row 
$hdrRow2 = $hdrRow1 + 1;
$hdrRow3 = $hdrRow2 + 2; 
$headers = [
    'A' => 'Date Acquired',
    'B' => 'Particulars/ Articles',
    'C' => 'Property No.',
    'D' => 'Qnty.',
    'E' => 'Unit Cost',
    'F' => 'Total Cost',
    'G' => 'Accum. Depreciation',
    'H' => 'Accumulated Impairment Losses',
    'I' => 'Carrying Amount',
    'J' => 'Remarks',
    'K' => 'Sales',
    'L' => 'Transfer',
    'M' => 'Destruction',
    'N' => 'Others (Specify)',
    'O' => 'Total',
    'P' => 'Appraised Value',
    'Q' => 'OR No.',
    'R' => 'Amount'
];

// Merge each header cell downward by 3 rows
foreach ($headers as $col => $label) {
    $sheet->mergeCells("{$col}{$hdrRow2}:{$col}{$hdrRow3}");
    $sheet->setCellValue("{$col}{$hdrRow2}", $label);
}

// Style
$sheet->getStyle("A{$hdrRow2}:R{$hdrRow3}")->applyFromArray($borderAll);
$sheet->getStyle("A{$hdrRow2}:R{$hdrRow3}")->applyFromArray($centerStyle);
$sheet->getStyle("A{$hdrRow2}:R{$hdrRow3}")->getFont()->setBold(true);
$sheet->getRowDimension($hdrRow2)->setRowHeight(20);
$sheet->getRowDimension($hdrRow2 + 1)->setRowHeight(20);
$sheet->getRowDimension($hdrRow2 + 2)->setRowHeight(20);

// Adjust next data row start position
$dataStartRow = $hdrRow3 + 1;
$row = $dataStartRow;

if ($ppe_result->num_rows > 0) {
    while ($r = $ppe_result->fetch_assoc()) {
        $sheet->setCellValue("A{$row}", (substr($r['acquisition_date'], 0, 4)));
        $sheet->setCellValue("B{$row}", $r['description'] . ', ' . $r['item_name']);
        $sheet->setCellValue("C{$row}", $r['property_number']);
        $sheet->setCellValue("D{$row}", $r['defective_unserviceable_count']);
        $sheet->setCellValue("E{$row}", number_format($r['unit_cost'], 2));
        $sheet->setCellValue("F{$row}", number_format($r['unit_total_cost'], 2));
        $sheet->setCellValue("J{$row}", $r['item_condition']);
        // Leave other cells empty as placeholders
        $row++;
    }
} else {
    $sheet->mergeCells("A{$row}:R{$row}");
    $sheet->setCellValue("A{$row}", "No Unserviceable Property found");
    $sheet->getStyle("A{$row}")->applyFromArray($centerStyle);
    $row++;
}

// apply borders to the table area
$sheet->getStyle("A{$hdrRow1}:R" . ($row-1))->applyFromArray($borderAll);

// FOOTER 
$row += 1; // some spacing
$startFooter = $row;

// Request statement (full width)
$sheet->mergeCells("A{$row}:F{$row}");
$sheet->setCellValue("A{$row}", "I HEREBY request inspection and disposition, pursuant to Section 79 of PD 1445, of the property enumerated above.");
$sheet->getStyle("A{$row}:F{$row}")->applyFromArray($leftWrap);

// Insert the certification paragraph under 'Certified Correct' and witness paragraph under 'Witnessed by'
$certText = "I CERTIFY that I have inspected each and every article\nenumerated in this report, and that the disposition made\nthereof was, in my judgement, the best for the public\ninterest.";
$witnessMonth = date('F');
$witnessYear = date('Y');
$witnessText = "I CERTIFY that I have witnessed the\ndisposition of the articles\nenumerated in this report this\n________day of {$witnessMonth} {$witnessYear}";

// merge cells vertically across 4 rows
$mergeHeight = 4; // number of rows tall
$endRow = $row + $mergeHeight - 1;

// CERTIFY (left side)
$sheet->mergeCells("K{$row}:O{$endRow}");
$sheet->setCellValue("K{$row}", $certText);
$sheet->getStyle("K{$row}:O{$endRow}")->applyFromArray($leftWrap);
$sheet->getStyle("K{$row}:O{$endRow}")->getAlignment()->setHorizontal(
    \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT
);

// WITNESS (right side)
$sheet->mergeCells("P{$row}:R{$endRow}");
$sheet->setCellValue("P{$row}", $witnessText);
$sheet->getStyle("P{$row}:R{$endRow}")->applyFromArray($leftWrap);
$sheet->getStyle("P{$row}:R{$endRow}")->getAlignment()->setHorizontal(
    \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT
);

$row += 2;

// Labels for signature blocks
$sheet->mergeCells("A{$row}");
$sheet->setCellValue("A{$row}", "Requested by:");
$sheet->mergeCells("F{$row}");
$sheet->setCellValue("F{$row}", "Approved by:");
$sheet->getStyle("A{$row}:R{$row}")->applyFromArray($centerStyle);


// make room for names (skip 2 rows)
$row += 4;

// Names row
$requested_fullname = strtoupper($personnel_info['first_name'] . ' ' . $personnel_info['last_name'])
    . (!empty($personnel_info['professional_designations']) ? ', ' . $personnel_info['professional_designations'] : '');

$sheet->mergeCells("A{$row}:C{$row}");
$sheet->setCellValue("A{$row}", $requested_fullname);

$sheet->mergeCells("F{$row}:H{$row}");
$sheet->setCellValue("F{$row}", "MA. ELENA C. DEMDAM, Ph.D");

$sheet->mergeCells("K{$row}:N{$row}");
$sheet->setCellValue("K{$row}", "MARK ANTHONY D. DIPAD, MIT");

$sheet->mergeCells("O{$row}:R{$row}");
$sheet->setCellValue("O{$row}", "GERALD E. FULAY, JD, MAMPA");

$row++;

// Titles row
$sheet->mergeCells("A{$row}:C{$row}");
$sheet->setCellValue("A{$row}", $personnel_info['role']);
$sheet->mergeCells("F{$row}:H{$row}");
$sheet->setCellValue("F{$row}", "Campus Director");
$sheet->mergeCells("K{$row}:N{$row}");
$sheet->setCellValue("K{$row}", "Inspection Officer");
$sheet->mergeCells("O{$row}:R{$row}");
$sheet->setCellValue("O{$row}", "Witness");

// Center and wrap footer areas
$sheet->getStyle("A" . ($startFooter) . ":R{$row}")->applyFromArray($centerStyle);
$sheet->getStyle("M" . ($startFooter) . ":O{$row}")->getAlignment()->setWrapText(true);
$sheet->getStyle("Q" . ($startFooter) . ":R{$row}")->getAlignment()->setWrapText(true);

// Apply borders to footer area
$sheet->getStyle("A" . ($startFooter) . ":R{$row}")->applyFromArray($borderAll);

// Auto-size columns
foreach (range('A', 'R') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Produce file
$filename = 'Unserviceable_PPE_Report_' . date('Y-m-d') . '.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');

// Log activity
include 'activity_logger.php';
log_activity($conn, $_SESSION['user_ID'], "Exported unserviceable PPE inventory to Excel");

$conn->close();
exit();
?>