<?php
session_start();
include '../src/db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_ID']) || $_SESSION['role'] !== 'Supply Officer') {
    header("Location: login.php?error=Access+denied");
    exit();
}

$user_ID = $_SESSION['user_ID'];

// Fetch user details
$sql = "SELECT user_name, user_email, password, first_name, last_name, professional_designation, role, profile_image FROM Users WHERE user_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_ID);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// If profile image is empty, set default
if (!empty($user['profile_image'])) {
    $profileImage = $user['profile_image']; // use path directly
} else {
    $profileImage = '/uploads/profile_images/default_profile.jpg'; // Path to default image
}

// Get filter parameters
$fund_filter = isset($_GET['fund_filter']) ? $_GET['fund_filter'] : '';
$type_filter = isset($_GET['type_filter']) ? $_GET['type_filter'] : '';
$personnel_filter = isset($_GET['personnel_filter']) ? $_GET['personnel_filter'] : '';
$condition_filter = isset($_GET['condition_filter']) ? $_GET['condition_filter'] : '';

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
                COUNT(iu.unit_ID) as defective_unserviceable_count,
                iu.item_condition
            FROM Items i
            INNER JOIN Item_Units iu ON i.item_ID = iu.item_ID
            LEFT JOIN Fund_sources fs ON i.fund_ID = fs.fund_ID
            LEFT JOIN Equipment_types et ON i.type_ID = et.type_ID
            LEFT JOIN Persons p ON iu.assign_to = p.person_ID
            WHERE (iu.item_condition = 'Unserviceable' OR iu.item_condition = 'Defective')
            AND i.item_classification = 'Property Plant and Equipment'";

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
    $filter_condition = " AND " . implode(" AND ", $where_conditions);
    $ppe_sql .= $filter_condition;
    $sep_sql .= $filter_condition;
}

// Add GROUP BY and ORDER BY to both queries
$ppe_sql .= " GROUP BY i.item_ID, iu.assign_to ORDER BY i.acquisition_date DESC";
$sep_sql .= " GROUP BY i.item_ID, iu.assign_to ORDER BY i.acquisition_date DESC";

// Fetch defective/unserviceable PPE (Property Plant and Equipment)
$ppe_items = [];
$ppe_stmt = $conn->prepare($ppe_sql);
if (!empty($params)) {
    $ppe_stmt->bind_param($types, ...$params);
}
$ppe_stmt->execute();
$ppe_result = $ppe_stmt->get_result();
$ppe_items = $ppe_result->fetch_all(MYSQLI_ASSOC);

// Fetch defective/unserviceable SEP (Semi-Expendable Property)
$sep_items = [];
$sep_stmt = $conn->prepare($sep_sql);
if (!empty($params)) {
    $sep_stmt->bind_param($types, ...$params);
}
$sep_stmt->execute();
$sep_result = $sep_stmt->get_result();
$sep_items = $sep_result->fetch_all(MYSQLI_ASSOC);

// Fetch filter options
$fund_sources_sql = "SELECT fund_ID, fund_name FROM Fund_sources ORDER BY fund_name";
$fund_sources_result = $conn->query($fund_sources_sql);

$equipment_types_sql = "SELECT type_ID, type_name FROM Equipment_types ORDER BY type_name";
$equipment_types_result = $conn->query($equipment_types_sql);

// Fetch personnel options (only those with defective/unserviceable items)
$personnel_sql = "SELECT DISTINCT p.person_ID, p.first_name, p.last_name, p.professional_designations, p.office_name 
                  FROM Persons p 
                  INNER JOIN Item_Units iu ON p.person_ID = iu.assign_to 
                  WHERE iu.item_condition IN ('Defective', 'Unserviceable')
                  AND p.status = 'Active'
                  ORDER BY p.last_name, p.first_name";
$personnel_result = $conn->query($personnel_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

     <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css" integrity="sha512-DxV+EoADOkOygM4IR9yXP8Sb2qwgidEmeqAEmDKIOfPRQZOWbXCzLC6vjbZyy0vPisbH2SyW27+ddLVCN+OMzQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="./css/major.css">
    <link rel="stylesheet" href="./css/inventories.css">

    <title>Inventory_Management_System-Inventories_Page</title>
</head>
<body>
        <header>
         <div class="IMS-logo">
            <img src="./image/IMS_logo1.png"><span>Inventory Management System</span>
        </div>
        <button class="notification-btn">
            <span><i class="fa-solid fa-bell"></i></span>
        </button>
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" 
               id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="<?php echo htmlspecialchars($profileImage); ?>" 
                     alt="profile" class="rounded-circle" width="35" height="35">
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="profileDropdown">
                <li style="display: flex; flex-direction: row; align-items: center; padding: 10px 20px;">
                    <img src="<?php echo htmlspecialchars($profileImage); ?>" class="rounded-square" width="35" height="35" style="border-radius: 10px;">
                    <h6 class="dropdown-header" style="color: #252525; font-weight:700;"><?php echo htmlspecialchars($user['first_name']. ' ' . $user['last_name']); ?>, <?php echo htmlspecialchars($user['professional_designation']); ?><br><?php echo htmlspecialchars($user['user_email']); ?></h6>
                </li>
                <li>
                    <a class="dropdown-item" href="profile.php">
                        <i class="fa-solid fa-circle-user me-2"></i> Profile
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="setting.php">
                        <i class="fa-solid fa-gear me-2"></i> Settings
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item text-danger" href="../src/log_out.php">
                        <i class="bi bi-box-arrow-left me-2"></i> Log out
                    </a>
                </li>
            </ul>
        </div>
        
       
    </header>

    <aside>
        <button id="sidebarToggle" class="sidebar-toggle-btn">
            <i class="fa-solid fa-circle-arrow-left"></i>
        </button>
        <div class="sorsu-logo-container">
            <img src="./image/sorsu_logo.png" alt="sorsu_logo" class="sorsu-logo">
            <div class="sorsu-text">
                <div><h1>SorSU-BC</h1></div>
                <div><h3>Supply Office</h3></div>
            </div>
        </div>
        <nav>
            <a href="dashboard.php" class="nav-item"><i class="fa-solid fa-house-chimney"></i> <span>Dashboard</span></a>
            <a href="barcode_scanner.php" class="nav-item"><i class="fa-solid fa-barcode"></i> <span>Scanner</span></a>
            <a href="inventories.php" class="nav-item active"><i class="fa-solid fa-warehouse"></i> <span>Inventories</span></a>
            <a href="profile.php" class="nav-item"><i class="fa-solid fa-circle-user"></i> <span>Profile</span></a>
            <a href="setting.php" class="nav-item"><i class="fa-solid fa-gear"></i> <span>Settings</span></a>
        </nav>
        <footer>
           <nav>
                <a href="../src/log_out.php" class="nav-item"><i class="bi bi-box-arrow-left"></i> <span>Log out</span></a>
           </nav>
        </footer>
    </aside>
    <main>
        <div class="title">
            <h1>Inventories</h1>
        </div>
        <div class="main-content" style="overflow-y: hidden;">
            <div class="top-panel">
                <div class="date-time">
                    <span id="date"></span>
                    <span id="time"></span>
                </div>
                <div class="inventory-tabs">
                    <ul>
                        <li><a href="inventories.php" class="li-item"><i class="bi bi-people"></i> Personnels</a></li>
                        <li><a href="inventories_ppe.php" class="li-item"><i class="bi bi-inboxes"></i> Property Plant And Equipment</a></li>
                        <li><a href="inventories_sep.php" class="li-item"><i class="bi bi-inbox"></i> Semi-Expendable Properties</a></li>
                        <li><a href="inventories_ue.php" class="li-item active"><i class="bi bi-archive"></i> Unserviceable Equipments</a></li>
                    </ul>
                </div>
                <div class="container table-container" style="margin-top: 0;">
                    <div class="table-header-container">
                    <h3><i class="bi bi-archive-fill"></i> Inventory for Defective and Unserviceable Equipments</h3>
                    
                    <!-- Row Filter -->
                    <div class="d-flex justify-content-end mb-3">
                        <div class="row-filter" style="display: flex; align-items: center; gap: 8px;">
                            <label for="rowsPerPageUE" style="font-size: 14px; font-weight: 500; white-space: nowrap;">Rows per page:</label>
                            <select id="rowsPerPageUE" class="form-select form-select-sm" style="width: auto; font-size: 14px; min-width: 80px;">
                                <option value="10" selected>10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                                <option value="all">All</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Filter Form -->
                    <form method="GET" class="filter-form mb-3">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-2">
                                <label for="fund_filter" class="form-label">Fund Source</label>
                                <select class="form-select" id="fund_filter" name="fund_filter">
                                    <option value="">All Fund Sources</option>
                                    <?php while ($fund = $fund_sources_result->fetch_assoc()): ?>
                                        <option value="<?= $fund['fund_ID'] ?>" <?= ($fund_filter == $fund['fund_ID']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($fund['fund_name']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-2">
                                <label for="type_filter" class="form-label">Equipment Type</label>
                                <select class="form-select" id="type_filter" name="type_filter">
                                    <option value="">All Equipment Types</option>
                                    <?php 
                                    // Reset pointer and loop through equipment types
                                    $equipment_types_result->data_seek(0);
                                    while ($type = $equipment_types_result->fetch_assoc()): ?>
                                        <option value="<?= $type['type_ID'] ?>" <?= ($type_filter == $type['type_ID']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($type['type_name']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-2">
                                <label for="personnel_filter" class="form-label">Assigned Personnel</label>
                                <select class="form-select" id="personnel_filter" name="personnel_filter">
                                    <option value="">All Personnel</option>
                                    <?php while ($person = $personnel_result->fetch_assoc()): ?>
                                        <option value="<?= $person['person_ID'] ?>" <?= ($personnel_filter == $person['person_ID']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($person['first_name'] . ' ' . $person['last_name']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-2">
                                <label for="condition_filter" class="form-label">Item Condition</label>
                                <select class="form-select" id="condition_filter" name="condition_filter">
                                    <option value="">All Conditions</option>
                                    <option value="Defective" <?= ($condition_filter == 'Defective') ? 'selected' : '' ?>>Defective</option>
                                    <option value="Unserviceable" <?= ($condition_filter == 'Unserviceable') ? 'selected' : '' ?>>Unserviceable</option>
                                </select>
                            </div>
                            
                            <div class="col-md-2">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary flex-fill" style="font-size: 14px; font-weight:500;">Apply</button>
                                    <a href="inventories_ue.php" class="btn btn-outline-secondary flex-fill" style="font-size: 14px; font-weight:500;">Clear</a>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="inventory-ue">
                        <div class="ue-tabs">
                            <select name="tabs" id="tabs" class="form-select">
                                <option value="inventory-ppe" selected>Inventory and Inspection Report of Defective/Unserviceable Property</option>
                                <option value="inventory-sep">Inventory and Inspection Report of Defective/Unserviceable Semi-Expendable Property</option>
                            </select>
                        </div>
                        
                        <!-- Rest of your existing code remains the same -->
                        <div class="table-responsive-wrapper">
                            <!-- Inventory for Defective/Unserviceable Property Content -->
                            <div id="inventory-ppe" class="tab-content active">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <!-- Your existing table content -->
                                        <thead class="table-dark" style="text-align: center;font-size: 12px;">
                                            <tr>
                                                <th colspan="7">INVENTORY</th> 
                                                <th colspan="4">ACCOUNTING</th>
                                                <th colspan="5">INSPECTION and DISPOSAL</th>
                                                <th rowspan="2">Appraised<br>Value</th>
                                                <th colspan="2">Record of Sales</th>
                                            </tr>
                                            <tr>
                                                <!-- inventory -->
                                                <th style="width: 5%;">Date<br>Acquired</th>
                                                <th style="width: 12%;">Particulars/ Articles</th>
                                                <th style="width: 8%;">Property No.</th>
                                                <th style="width: 4%;">Qnty.</th>
                                                <th style="width: 7%;">Unit Cost</th>
                                                <th style="width: 7%;">Total Cost</th>
                                                <th style="width: 8%;">Assigned To</th>

                                                <!-- accounting -->
                                                <th style="width: 5%;">Accum.<br>Decepreciation</th>
                                                <th style="width: 4%;">Accumulated<br>Impairment<br>Losses</th>
                                                <th style="width: 4%;">Carrying<br>Amount</th>
                                                <th style="width: 8%;">Remarks</th>

                                                <!-- inspection and disposal -->
                                                <th style="width: 3%;">Sales</th>
                                                <th style="width: 3%;">Transfer</th>
                                                <th style="width: 3%;">Destruction</th>
                                                <th style="width: 5%;">Others<br>(Specify)</th>
                                                <th style="width: 3%;">Total</th>

                                                <!-- record of sales -->
                                                <th style="width: 3%;">OR NO.</th>
                                                <th style="width: 3%;">Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody style="text-align: start;font-size: 12px;">
                                            <?php if (empty($ppe_items)): ?>
                                                <tr><td colspan='20'>No Defective/Unserviceable Property found</td></tr>
                                            <?php else: ?>
                                                <?php foreach ($ppe_items as $item): ?>
                                                <tr>
                                                    <!-- Inventory -->
                                                    <td style="text-align: center;"><?php echo htmlspecialchars($item['acquisition_date']); ?></td>
                                                    <td><?php echo htmlspecialchars($item['description']); ?>, <?php echo htmlspecialchars($item['item_name']); ?></td>
                                                    <td style="text-align: center;"><?php echo htmlspecialchars($item['property_number']); ?></td>
                                                    <td style="text-align: center;"><?php echo htmlspecialchars($item['defective_unserviceable_count']); ?></td>
                                                    <td style="text-align: right;"><?php echo number_format($item['unit_cost'], 2); ?></td>
                                                    <td style="text-align: right;"><?php echo number_format($item['unit_total_cost'], 2); ?></td>
                                                    <td style="text-align: center;">
                                                        <?php if (!empty($item['first_name'])): ?>
                                                            <?php echo htmlspecialchars($item['first_name'] . ' ' . $item['last_name']); ?>
                                                        <?php else: ?>
                                                            Unassigned
                                                        <?php endif; ?>
                                                    </td>
                                                    
                                                    <!-- Accounting -->
                                                    <td style="text-align: center;"></td>
                                                    <td style="text-align: center;"></td>
                                                    <td style="text-align: center;"></td>
                                                    <td style="text-align: center;"><?php echo htmlspecialchars($item['item_condition']); ?></td>
                                                    
                                                    <!-- Inspection and Disposal -->
                                                    <td style="text-align: center;"></td>
                                                    <td style="text-align: center;"></td>
                                                    <td style="text-align: center;"></td>
                                                    <td style="text-align: center;"></td>
                                                    <td style="text-align: center;"></td>
                                                    
                                                    <!-- Appraised Value -->
                                                    <td style="text-align: center;"></td>
                                                    
                                                    <!-- Record of Sales -->
                                                    <td style="text-align: center;"></td>
                                                    <td style="text-align: center;"></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <!-- Pagination Controls for PPE -->
                                <div class="d-flex justify-content-between align-items-center mt-3 mb-3">
                                    <nav aria-label="PPE defective/unserviceable pagination">
                                        <ul class="pagination pagination-sm mb-0" id="paginationControlsPPE">
                                            <!-- Pagination buttons inserted dynamically -->
                                        </ul>
                                    </nav>
                                </div>
                                
                                <!-- File Export Section - Only show when all filters are applied -->
                                <?php 
                                $allFiltersApplied = !empty($fund_filter) && !empty($personnel_filter) && !empty($condition_filter);
                                ?>
                                
                                <?php if ($allFiltersApplied): ?>
                                <div class="file-export">
                                    <span class="dnwld">Download:</span>
                                    <form method="GET" action="../src/export_ue_ppe_excel.php" style="display: inline;" target="_blank">
                                        <?php if (!empty($fund_filter)): ?>
                                            <input type="hidden" name="fund_filter" value="<?php echo htmlspecialchars($fund_filter); ?>">
                                        <?php endif; ?>
                                        <?php if (!empty($type_filter)): ?>
                                            <input type="hidden" name="type_filter" value="<?php echo htmlspecialchars($type_filter); ?>">
                                        <?php endif; ?>
                                        <?php if (!empty($personnel_filter)): ?>
                                            <input type="hidden" name="personnel_filter" value="<?php echo htmlspecialchars($personnel_filter); ?>">
                                        <?php endif; ?>
                                        <?php if (!empty($condition_filter)): ?>
                                            <input type="hidden" name="condition_filter" value="<?php echo htmlspecialchars($condition_filter); ?>">
                                        <?php endif; ?>
                                        <button type="submit" class="btn btn-outline-success btn-sm">
                                            <i class="fa-regular fa-file-excel"></i> Excel File
                                        </button>
                                    </form>
                                    
                                    <form method="GET" action="../src/export_ue_ppe_pdf.php" style="display: inline;" target="_blank">
                                        <?php if (!empty($fund_filter)): ?>
                                            <input type="hidden" name="fund_filter" value="<?php echo htmlspecialchars($fund_filter); ?>">
                                        <?php endif; ?>
                                        <?php if (!empty($type_filter)): ?>
                                            <input type="hidden" name="type_filter" value="<?php echo htmlspecialchars($type_filter); ?>">
                                        <?php endif; ?>
                                        <?php if (!empty($personnel_filter)): ?>
                                            <input type="hidden" name="personnel_filter" value="<?php echo htmlspecialchars($personnel_filter); ?>">
                                        <?php endif; ?>
                                        <?php if (!empty($condition_filter)): ?>
                                            <input type="hidden" name="condition_filter" value="<?php echo htmlspecialchars($condition_filter); ?>">
                                        <?php endif; ?>
                                        <button type="submit" class="btn btn-outline-primary btn-sm">
                                            <i class="fa-regular fa-file-pdf"></i> PDF
                                        </button>
                                    </form>
                                </div>
                                <?php endif; ?>
                            </div>

                            <!-- Inventory for Defective/Unserviceable Semi-Expendable Property Content -->
                            <div id="inventory-sep" class="tab-content">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <!-- Your existing table content -->
                                        <thead class="table-dark" style="text-align: center;font-size: 12px;">
                                            <tr>
                                                <th colspan="10">INVENTORY</th> 
                                                <th colspan="5">INSPECTION and DISPOSAL</th>
                                                <th rowspan="2">Appraised<br>Value</th>
                                                <th colspan="2">Record of Sales</th>
                                            </tr>
                                            <tr>
                                                <!-- inventory -->
                                                <th style="width:5%;">Year<br>Acquired</th>
                                                <th style="width:18%;">Particulars/ Articles</th>
                                                <th style="width:8%;">Account Code</th>
                                                <th style="width:4%;">Qnty.</th>
                                                <th style="width:6%;">Unit Cost</th>
                                                <th style="width:7%;">Total Cost</th>
                                                <th style="width:8%;">Accumulated<br>Impairment<br>Losses</th>
                                                <th style="width:7%;">Carrying<br>Amount</th>
                                                <th style="width:8%;">Assigned To</th>
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
                                        <tbody style="text-align: start;font-size: 12px;">
                                            <?php if (empty($sep_items)): ?>
                                                <tr><td colspan='19'>No Defective/Unserviceable Semi-Expendable Property found</td></tr>
                                            <?php else: ?>
                                                <?php foreach ($sep_items as $item): ?>
                                                <tr>
                                                    <!-- Inventory -->
                                                    <td style="text-align: center;"><?php echo htmlspecialchars(substr($item['acquisition_date'], 0, 4)); ?></td>
                                                    <td><?php echo htmlspecialchars($item['description']); ?>, <?php echo htmlspecialchars($item['item_name']); ?></td>
                                                    <td style="text-align: center;"><?php echo htmlspecialchars($item['property_number']); ?></td>
                                                    <td style="text-align: center;"><?php echo htmlspecialchars($item['defective_unserviceable_count']); ?></td>
                                                    <td style="text-align: right;"><?php echo number_format($item['unit_cost'], 2); ?></td>
                                                    <td style="text-align: right;"><?php echo number_format($item['unit_total_cost'], 2); ?></td>
                                                    <td style="text-align: center;"></td>
                                                    <td style="text-align: center;"></td>
                                                    <td style="text-align: center;">
                                                        <?php if (!empty($item['first_name'])): ?>
                                                            <?php echo htmlspecialchars($item['first_name'] . ' ' . $item['last_name']); ?>
                                                        <?php else: ?>
                                                            Unassigned
                                                        <?php endif; ?>
                                                    </td>
                                                    <td style="text-align: center;"><?php echo htmlspecialchars($item['item_condition']); ?></td>
                                                    
                                                    <!-- Inspection and Disposal -->
                                                    <td style="text-align: center;"></td>
                                                    <td style="text-align: center;"></td>
                                                    <td style="text-align: center;"></td>
                                                    <td style="text-align: center;"></td>
                                                    <td style="text-align: center;"></td>
                                                    
                                                    <!-- Appraised Value -->
                                                    <td style="text-align: center;"></td>
                                                    
                                                    <!-- Record of Sales -->
                                                    <td style="text-align: center;"></td>
                                                    <td style="text-align: center;"></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <!-- Pagination Controls for SEP -->
                                <div class="d-flex justify-content-between align-items-center mt-3 mb-3">
                                    <nav aria-label="SEP defective/unserviceable pagination">
                                        <ul class="pagination pagination-sm mb-0" id="paginationControlsSEP">
                                            <!-- Pagination buttons inserted dynamically -->
                                        </ul>
                                    </nav>
                                </div>
                                
                                <!-- File Export Section - Only show when all filters are applied -->
                                <?php if ($allFiltersApplied): ?>
                                <div class="file-export">
                                    <span class="dnwld">Download:</span>
                                    <form method="GET" action="../src/export_ue_sep_excel.php" style="display: inline;" target="_blank">
                                        <?php if (!empty($fund_filter)): ?>
                                            <input type="hidden" name="fund_filter" value="<?php echo htmlspecialchars($fund_filter); ?>">
                                        <?php endif; ?>
                                        <?php if (!empty($type_filter)): ?>
                                            <input type="hidden" name="type_filter" value="<?php echo htmlspecialchars($type_filter); ?>">
                                        <?php endif; ?>
                                        <?php if (!empty($personnel_filter)): ?>
                                            <input type="hidden" name="personnel_filter" value="<?php echo htmlspecialchars($personnel_filter); ?>">
                                        <?php endif; ?>
                                        <?php if (!empty($condition_filter)): ?>
                                            <input type="hidden" name="condition_filter" value="<?php echo htmlspecialchars($condition_filter); ?>">
                                        <?php endif; ?>
                                        <button type="submit" class="btn btn-outline-success btn-sm">
                                            <i class="fa-regular fa-file-excel"></i> Excel File
                                        </button>
                                    </form>
                                    
                                    <form method="GET" action="../src/export_ue_sep_pdf.php" style="display: inline;" target="_blank">
                                        <?php if (!empty($fund_filter)): ?>
                                            <input type="hidden" name="fund_filter" value="<?php echo htmlspecialchars($fund_filter); ?>">
                                        <?php endif; ?>
                                        <?php if (!empty($type_filter)): ?>
                                            <input type="hidden" name="type_filter" value="<?php echo htmlspecialchars($type_filter); ?>">
                                        <?php endif; ?>
                                        <?php if (!empty($personnel_filter)): ?>
                                            <input type="hidden" name="personnel_filter" value="<?php echo htmlspecialchars($personnel_filter); ?>">
                                        <?php endif; ?>
                                        <?php if (!empty($condition_filter)): ?>
                                            <input type="hidden" name="condition_filter" value="<?php echo htmlspecialchars($condition_filter); ?>">
                                        <?php endif; ?>
                                        <button type="submit" class="btn btn-outline-primary btn-sm">
                                            <i class="fa-regular fa-file-pdf"></i> PDF
                                        </button>
                                    </form>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                </div>
            </div>
        </div>
        <div class="copyright">
            <i class="fa-regular fa-copyright"></i> All Rights Reserved 2025
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="./js/day_date.js"></script>
    <script>
    document.getElementById('sidebarToggle').addEventListener('click', function() {
        const aside = document.querySelector('aside');
        const body = document.body;
        const icon = this.querySelector('i'); // get the <i> inside button

        // Toggle collapsed state
        aside.classList.toggle('collapsed');
        body.classList.toggle('sidebar-collapsed');

        // Toggle icon
        if (aside.classList.contains('collapsed')) {
            icon.classList.remove('fa-circle-arrow-left');
            icon.classList.add('fa-circle-arrow-right');
        } else {
            icon.classList.remove('fa-circle-arrow-right');
            icon.classList.add('fa-circle-arrow-left');
        }
    });

    // Tab switching for UE tabs using select dropdown
    const tabSelect = document.getElementById("tabs");
    const tabContents = document.querySelectorAll(".tab-content");

    // Function to switch tabs
    function switchTab(selectedValue) {
        // Remove active class from all contents
        tabContents.forEach(content => content.classList.remove("active"));
        
        // Add active class to selected content
        document.getElementById(selectedValue).classList.add("active");
    }

    // Initial setup - show the default selected tab
    switchTab(tabSelect.value);

    // Event listener for select change
    tabSelect.addEventListener("change", function() {
        switchTab(this.value);
    });

    // Enhance filter form functionality
    document.addEventListener('DOMContentLoaded', function() {
        // Show active filter count
        const urlParams = new URLSearchParams(window.location.search);
        let activeFilters = 0;
        if (urlParams.get('fund_filter')) activeFilters++;
        if (urlParams.get('type_filter')) activeFilters++;
        if (urlParams.get('personnel_filter')) activeFilters++;
        if (urlParams.get('condition_filter')) activeFilters++; // NEW: condition filter
        
        if (activeFilters > 0) {
            const filterHeader = document.querySelector('.table-header-container h3');
            if (filterHeader) {
                filterHeader.innerHTML += ` <h5><span class="badge bg-dark">${activeFilters} active filter(s)</span></h5>`;
            }
        }
    });

    // swal functionality
    document.addEventListener('DOMContentLoaded', function() {
        const totalItems = <?= count($ppe_items) + count($sep_items) ?>;
        const ppeCount = <?= count($ppe_items) ?>;
        const sepCount = <?= count($sep_items) ?>;
        const hasFilters = <?= ($fund_filter || $type_filter || $personnel_filter || $condition_filter) ? 'true' : 'false' ?>;

        if (totalItems > 0) {
            Swal.fire({
                icon: 'info',
                title: 'Inventory Results',
                html: `Showing <strong>${totalItems}</strong> defective/unserviceable item(s)
                    ${hasFilters ? 'matching your filters' : ''}
                    (${ppeCount} PPE, ${sepCount} SEP)`,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 4000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'No Results Found',
                text: 'No defective/unserviceable items match your search criteria.',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        }
    });

    // Pagination functionality for UE inventory
    let currentPagePPE = 1;
    let currentPageSEP = 1;
    let rowsPerPageUE = 10;
    let allRowsPPE = [];
    let allRowsSEP = [];
    let filteredRowsPPE = [];
    let filteredRowsSEP = [];

    // Initialize pagination on page load
    document.addEventListener('DOMContentLoaded', function() {
        initializeUEPagination();
    });

    function initializeUEPagination() {
        // Get all PPE table rows (excluding header)
        const ppeTableBody = document.querySelector('#inventory-ppe table tbody');
        if (ppeTableBody) {
            const ppeRows = Array.from(ppeTableBody.querySelectorAll('tr'));
            allRowsPPE = ppeRows.filter(row => !row.textContent.includes('No Defective/Unserviceable Property found'));
            filteredRowsPPE = [...allRowsPPE];
        }
        
        // Get all SEP table rows (excluding header)
        const sepTableBody = document.querySelector('#inventory-sep table tbody');
        if (sepTableBody) {
            const sepRows = Array.from(sepTableBody.querySelectorAll('tr'));
            allRowsSEP = sepRows.filter(row => !row.textContent.includes('No Defective/Unserviceable Semi-Expendable Property found'));
            filteredRowsSEP = [...allRowsSEP];
        }
        
        // Initialize pagination for both tables
        displayUEPage();
        updateUEPagination();
    }

    function displayUEPage() {
        // Display PPE page
        if (allRowsPPE.length > 0) {
            const startIndexPPE = (currentPagePPE - 1) * rowsPerPageUE;
            const endIndexPPE = rowsPerPageUE === 'all' ? filteredRowsPPE.length : startIndexPPE + rowsPerPageUE;
            
            // Hide all PPE rows
            allRowsPPE.forEach(row => row.style.display = 'none');
            
            // Show rows for current page
            filteredRowsPPE.slice(startIndexPPE, endIndexPPE).forEach(row => row.style.display = 'table-row');
        }
        
        // Display SEP page
        if (allRowsSEP.length > 0) {
            const startIndexSEP = (currentPageSEP - 1) * rowsPerPageUE;
            const endIndexSEP = rowsPerPageUE === 'all' ? filteredRowsSEP.length : startIndexSEP + rowsPerPageUE;
            
            // Hide all SEP rows
            allRowsSEP.forEach(row => row.style.display = 'none');
            
            // Show rows for current page
            filteredRowsSEP.slice(startIndexSEP, endIndexSEP).forEach(row => row.style.display = 'table-row');
        }
    }

    function updateUEPagination() {
        const paginationControlsPPE = document.getElementById('paginationControlsPPE');
        const paginationControlsSEP = document.getElementById('paginationControlsSEP');
        
        // Update PPE pagination
        if (paginationControlsPPE && filteredRowsPPE.length > 0) {
            const totalPagesPPE = rowsPerPageUE === 'all' ? 1 : Math.ceil(filteredRowsPPE.length / rowsPerPageUE);
            updatePaginationControls(paginationControlsPPE, currentPagePPE, totalPagesPPE, 'PPE');
        }
        
        // Update SEP pagination
        if (paginationControlsSEP && filteredRowsSEP.length > 0) {
            const totalPagesSEP = rowsPerPageUE === 'all' ? 1 : Math.ceil(filteredRowsSEP.length / rowsPerPageUE);
            updatePaginationControls(paginationControlsSEP, currentPageSEP, totalPagesSEP, 'SEP');
        }
    }

    function updatePaginationControls(container, currentPage, totalPages, type) {
        container.innerHTML = '';
        
        if (totalPages <= 1) return;
        
        // Previous button
        const prevLi = document.createElement('li');
        prevLi.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
        prevLi.innerHTML = `<a class="page-link" href="#" data-page="${currentPage - 1}" data-type="${type}">Previous</a>`;
        container.appendChild(prevLi);
        
        // Page numbers
        for (let i = 1; i <= totalPages; i++) {
            const pageLi = document.createElement('li');
            pageLi.className = `page-item ${i === currentPage ? 'active' : ''}`;
            pageLi.innerHTML = `<a class="page-link" href="#" data-page="${i}" data-type="${type}">${i}</a>`;
            container.appendChild(pageLi);
        }
        
        // Next button
        const nextLi = document.createElement('li');
        nextLi.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
        nextLi.innerHTML = `<a class="page-link" href="#" data-page="${currentPage + 1}" data-type="${type}">Next</a>`;
        container.appendChild(nextLi);
    }

    // Event delegation for pagination controls
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('page-link')) {
            e.preventDefault();
            const page = parseInt(e.target.getAttribute('data-page'));
            const type = e.target.getAttribute('data-type');
            
            if (type === 'PPE') {
                currentPagePPE = page;
            } else if (type === 'SEP') {
                currentPageSEP = page;
            }
            
            displayUEPage();
            updateUEPagination();
        }
    });

    // Rows per page change handler
    const rowsPerPageSelectUE = document.getElementById('rowsPerPageUE');
    if (rowsPerPageSelectUE) {
        rowsPerPageSelectUE.addEventListener('change', function() {
            rowsPerPageUE = this.value === 'all' ? 'all' : parseInt(this.value);
            currentPagePPE = 1;
            currentPageSEP = 1;
            displayUEPage();
            updateUEPagination();
        });
    }
    </script>
</body>
</html>