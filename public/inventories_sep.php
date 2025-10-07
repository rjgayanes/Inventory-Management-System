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
$year_start = isset($_GET['year_start']) ? $_GET['year_start'] : '';
$year_end = isset($_GET['year_end']) ? $_GET['year_end'] : '';

// Build base SQL query
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
WHERE i.item_classification = 'Semi-Expendable Property'";

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
         fs.fund_name, et.type_name, fs.fund_ID, et.type_ID, i.acquisition_date
ORDER BY i.acquisition_date DESC";

// Prepare and execute the query with filters
$inventory_result = false;
$error_message = '';

try {
    if (!empty($params)) {
        $stmt = $conn->prepare($inventory_sql);
        if ($stmt) {
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $inventory_result = $stmt->get_result();
        } else {
            $error_message = "Failed to prepare query: " . $conn->error;
        }
    } else {
        $inventory_result = $conn->query($inventory_sql);
    }
    
    // Check if query was successful
    if ($inventory_result === false) {
        $error_message = "Query failed: " . $conn->error;
    }
} catch (Exception $e) {
    $error_message = "Database error: " . $e->getMessage();
    $inventory_result = false;
}

// If there was an error, display it and set empty result
if ($inventory_result === false) {
    error_log("Inventory query error: " . $error_message . " | SQL: " . $inventory_sql);
    // Create empty result set to prevent fatal errors
    $inventory_result = new class {
        public $num_rows = 0;
        public function fetch_assoc() { return false; }
    };
}

// Fetch filter options
$fund_sources_sql = "SELECT fund_ID, fund_name FROM Fund_sources ORDER BY fund_name";
$fund_sources_result = $conn->query($fund_sources_sql);

$equipment_types_sql = "SELECT type_ID, type_name FROM Equipment_types 
                       WHERE classification = 'Semi-Expendable Property' ORDER BY type_name";
$equipment_types_result = $conn->query($equipment_types_sql);

// Get min and max years for year range
$year_range_sql = "SELECT MIN(YEAR(acquisition_date)) as min_year, 
                          MAX(YEAR(acquisition_date)) as max_year 
                   FROM Items 
                   WHERE item_classification = 'Semi-Expendable Property'";
$year_range_result = $conn->query($year_range_sql);
$year_range = $year_range_result->fetch_assoc();
$min_year = $year_range['min_year'] ?: date('Y') - 5;
$max_year = $year_range['max_year'] ?: date('Y');

if (!$inventory_result) {
    die("Query failed: " . $conn->error . " | SQL: " . $inventory_sql);
}
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
        <div class="main-content">
            <div class="top-panel">
                    <div class="date-time">
                        <span id="date"></span>
                        <span id="time"></span>
                    </div>
                    <div class="inventory-tabs">
                        <ul>
                            <li><a href="inventories.php" class="li-item"><i class="bi bi-people"></i> Personnels</a></li>
                            <li><a href="inventories_ppe.php" class="li-item"><i class="bi bi-inboxes"></i> Property Plant And Equipment</a></li>
                            <li><a href="inventories_sep.php" class="li-item active"><i class="bi bi-inbox"></i> Semi-Expendable Properties</a></li>
                            <li><a href="inventories_ue.php" class="li-item"><i class="bi bi-archive"></i> Unserviceable Equipmnets</a></li>
                        </ul>
                    </div>
                   <!-- inventory for Semi-Expendable Properties Content -->
                    <div class="container table-container">
                        <div class="table-container-header">
                            <h3><i class="bi bi-inbox-fill"></i> Inventory for Semi-Expendable Properties</h3>
                            
                            <!-- Row Filter -->
                            <div class="d-flex justify-content-end mb-3">
                                <div class="row-filter" style="display: flex; align-items: center; gap: 8px;">
                                    <label for="rowsPerPageSEP" style="font-size: 14px; font-weight: 500; white-space: nowrap;">Rows per page:</label>
                                    <select id="rowsPerPageSEP" class="form-select form-select-sm" style="width: auto; font-size: 14px; min-width: 80px;">
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
                                    <div class="col-md-3">
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
                                    
                                    <div class="col-md-3">
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
                                    
                                    <div class="col-md-4">
                                        <label class="form-label">Year Range</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" name="year_start" placeholder="Start Year" 
                                                min="<?= $min_year ?>" max="<?= $max_year ?>" value="<?= $year_start ?>">
                                            <span class="input-group-text">to</span>
                                            <input type="number" class="form-control" name="year_end" placeholder="End Year" 
                                                min="<?= $min_year ?>" max="<?= $max_year ?>" value="<?= $year_end ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-2">
                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-primary flex-fill" style="font-size: 14px; font-weight:500;">Apply</button>
                                            <a href="inventories_sep.php" class="btn btn-outline-secondary flex-fill" style="font-size: 14px; font-weight:500;">Clear</a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Table -->
                        <table class="table table-bordered table-hover">
                            <thead class="table-dark" style="text-align: center;font-size: 12px;">
                                <tr>
                                    <th rowspan="2" style="width:10%;">Article</th> 
                                    <th rowspan="2" style="width:20%;">Description</th>
                                    <th rowspan="2" style="width:10%;">Semi-Expendable<br>Property No.</th>
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
                            <tbody style="text-align: start;font-size: 12px;">
                                <?php
                                // Display error message if query failed
                                if (isset($error_message) && !empty($error_message)): ?>
                                    <tr>
                                        <td colspan="15" class="text-center text-danger">
                                            <i class="bi bi-exclamation-triangle"></i> 
                                            Error loading data. Please try again or contact support.
                                        </td>
                                    </tr>
                                <?php elseif ($inventory_result->num_rows > 0): 
                                    $row_num = 1;
                                    while ($row = $inventory_result->fetch_assoc()):
                                        // Balance (original quantity & total cost from Items)
                                        $balance_qty   = $row['unit_quantity'];
                                        $balance_value = $row['unit_total_cost'];
                                        
                                        // Format currency values with commas
                                        $formatted_unit_cost = number_format($row['unit_cost'], 2);
                                        $formatted_balance_value = number_format($balance_value, 2);
                                        
                                        // Onhand = units not yet assigned
                                        $onhand_qty   = $row['unit_quantity'];
                                        $onhand_value = $row['unit_total_cost'];
                                        $formatted_onhand_value = number_format($onhand_value, 2);

                                        // Defaults
                                        $over_qty   = 0;
                                        $over_value = 0;
                                        $short_qty  = 0;
                                        $short_value= 0;

                                        // Rule: if item_units are unassigned → count as "over"
                                        if ($row['available_units'] > 0) {
                                            $over_qty   = $row['available_units'];
                                            $over_value = $row['available_units'] * $row['unit_cost'];
                                            $formatted_over_value = number_format($over_value, 2);
                                        } else {
                                            $formatted_over_value = number_format(0, 2);
                                        }
                                        
                                        $formatted_short_value = number_format(0, 2);
                                        ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['article']) ?>, <?php echo htmlspecialchars(substr($row['acquisition_date'], 0, 4)); ?></td>
                                            <td><?= htmlspecialchars($row['description']) ?></td>
                                            <td><?= htmlspecialchars($row['property_number']) ?></td>
                                            <td style="text-align: center;"><?= htmlspecialchars($row['unit_of_measure']) ?></td>
                                            <td class='text-end'><?= $formatted_unit_cost ?></td>

                                            <td class='text-center'><?= $balance_qty ?></td>
                                            <td class='text-end'><?= $formatted_balance_value ?></td>

                                            <td class='text-center'><?= $onhand_qty ?></td>
                                            <td class='text-end'><?= $formatted_onhand_value ?></td>

                                            <td class='text-center'><?= $short_qty ?></td>
                                            <td class='text-end'><?= $formatted_short_value ?></td>

                                            <td class='text-center'><?= $over_qty ?></td>
                                            <td class='text-end'><?= $formatted_over_value ?></td>

                                            <td><?= htmlspecialchars($row['remarks']) ?></td>
                                        </tr>
                                        <?php
                                        $row_num++;
                                    endwhile;
                                else: ?>
                                    <tr><td colspan='15'>No Semi-Expendable Property found</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        
                        <!-- Pagination Controls -->
                        <div class="d-flex justify-content-between align-items-center mt-3 mb-3">
                            <nav aria-label="SEP inventory pagination">
                                <ul class="pagination pagination-sm mb-0" id="paginationControlsSEP">
                                    <!-- Pagination buttons inserted dynamically -->
                                </ul>
                            </nav>
                        </div>
                        
                        <div class="file-export">
                            <span class="dnwld">Download:</span>
                            <button type="button" class="btn btn-outline-success btn-sm" onclick="exportToExcel()"><i class="fa-regular fa-file-excel"></i> Excel File</button>
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="exportToPDF()"><i class="fa-regular fa-file-pdf"></i> PDF</button>
                        </div>
                    </div>
            </div>
            <div class="bottom-panel"></div>
        </div>
    </main>

    <script src="./js/day_date.js"></script>
    <script src="./js/currency_format.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

    // Enhance filter form functionality
    document.addEventListener('DOMContentLoaded', function() {
        const yearStartInput = document.querySelector('input[name="year_start"]');
        const yearEndInput = document.querySelector('input[name="year_end"]');
        
        // Validate year range
        if (yearStartInput && yearEndInput) {
            yearStartInput.addEventListener('change', function() {
                if (this.value && yearEndInput.value && parseInt(this.value) > parseInt(yearEndInput.value)) {
                    yearEndInput.value = this.value;
                }
            });
            
            yearEndInput.addEventListener('change', function() {
                if (this.value && yearStartInput.value && parseInt(this.value) < parseInt(yearStartInput.value)) {
                    yearStartInput.value = this.value;
                }
            });
        }
        
        // Show active filter count
        const urlParams = new URLSearchParams(window.location.search);
        let activeFilters = 0;
        if (urlParams.get('fund_filter')) activeFilters++;
        if (urlParams.get('type_filter')) activeFilters++;
        if (urlParams.get('year_start') || urlParams.get('year_end')) activeFilters++;
        
        if (activeFilters > 0) {
            const filterHeader = document.querySelector('.table-container-header h3');
            if (filterHeader) {
                filterHeader.innerHTML += ` <h5><span class="badge bg-dark">${activeFilters} active filter(s)</span></h5>`;
            }
        }
    });

    //SWAL Functionality
    document.addEventListener('DOMContentLoaded', function() {
        const totalItems = <?= $inventory_result->num_rows ?>;
        const hasError = <?= (isset($error_message) && !empty($error_message)) ? 'true' : 'false' ?>;
        const hasFilters = <?= ($fund_filter || $type_filter || $year_start || $year_end) ? 'true' : 'false' ?>;

        if (hasError) {
            Swal.fire({
                icon: 'error',
                title: 'Data Loading Error',
                text: 'Error loading data. Please try again.',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 4000,
                timerProgressBar: true,
                background: '#f8d7da',
                color: '#721c24',
                iconColor: '#721c24'
            });
        } else if (totalItems > 0) {
            Swal.fire({
                icon: 'info',
                title: 'Inventory Results',
                html: `Showing <strong>${totalItems}</strong> item(s)
                    ${hasFilters ? 'matching your filters' : ''}`,
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
                text: 'No Semi-Expendable Properties match your search criteria.',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        }
    });

    // Export functions
    function exportToExcel() {
        const urlParams = new URLSearchParams(window.location.search);
        const exportUrl = '../src/export_sep_excel.php?' + urlParams.toString();
        window.open(exportUrl, '_blank');
    }

    function exportToPDF() {
        const urlParams = new URLSearchParams(window.location.search);
        const exportUrl = '../src/export_sep_pdf.php?' + urlParams.toString();
        window.open(exportUrl, '_blank');
    }

    // Pagination functionality for SEP inventory
    let currentPageSEP = 1;
    let rowsPerPageSEP = 10;
    let allRowsSEP = [];
    let filteredRowsSEP = [];

    // Initialize pagination on page load
    document.addEventListener('DOMContentLoaded', function() {
        initializeSEPPagination();
    });

    function initializeSEPPagination() {
        // Get all table rows (excluding header)
        const tableBody = document.querySelector('table tbody');
        const rows = Array.from(tableBody.querySelectorAll('tr'));
        
        // Filter out "no data" rows
        allRowsSEP = rows.filter(row => !row.textContent.includes('No Semi-Expendable Property found') && 
                                      !row.textContent.includes('Error loading data'));
        filteredRowsSEP = [...allRowsSEP];
        
        if (allRowsSEP.length > 0) {
            displaySEPPage();
            updateSEPPagination();
        } else {
            // Update pagination info for empty state
            document.getElementById('paginationInfoSEP').textContent = 'Showing 0 to 0 of 0 entries';
        }
    }

    function displaySEPPage() {
        const startIndex = (currentPageSEP - 1) * rowsPerPageSEP;
        const endIndex = rowsPerPageSEP === 'all' ? filteredRowsSEP.length : startIndex + parseInt(rowsPerPageSEP);
        
        // Hide all rows first
        allRowsSEP.forEach(row => row.style.display = 'none');
        
        // Show only the rows for current page
        const pageRows = filteredRowsSEP.slice(startIndex, endIndex);
        pageRows.forEach(row => row.style.display = '');
    }

    function updateSEPPagination() {
        const totalRows = filteredRowsSEP.length;
        const totalPages = rowsPerPageSEP === 'all' ? 1 : Math.ceil(totalRows / rowsPerPageSEP);
        const startRow = totalRows > 0 ? (currentPageSEP - 1) * (rowsPerPageSEP === 'all' ? totalRows : rowsPerPageSEP) + 1 : 0;
        const endRow = rowsPerPageSEP === 'all' ? totalRows : Math.min(currentPageSEP * rowsPerPageSEP, totalRows);

        // Update pagination info
        document.getElementById('paginationInfoSEP').textContent = 
            `Showing ${startRow} to ${endRow} of ${totalRows} entries`;

        // Update pagination controls
        const paginationControls = document.getElementById('paginationControlsSEP');
        paginationControls.innerHTML = '';

        if (totalPages <= 1) return;

        // Previous button
        const prevLi = document.createElement('li');
        prevLi.className = `page-item ${currentPageSEP === 1 ? 'disabled' : ''}`;
        prevLi.innerHTML = `<a class="page-link" href="#" onclick="changePageSEP(${currentPageSEP - 1})">Previous</a>`;
        paginationControls.appendChild(prevLi);

        // Page numbers
        const startPage = Math.max(1, currentPageSEP - 2);
        const endPage = Math.min(totalPages, currentPageSEP + 2);

        if (startPage > 1) {
            const firstLi = document.createElement('li');
            firstLi.className = 'page-item';
            firstLi.innerHTML = `<a class="page-link" href="#" onclick="changePageSEP(1)">1</a>`;
            paginationControls.appendChild(firstLi);

            if (startPage > 2) {
                const ellipsisLi = document.createElement('li');
                ellipsisLi.className = 'page-item disabled';
                ellipsisLi.innerHTML = `<span class="page-link">...</span>`;
                paginationControls.appendChild(ellipsisLi);
            }
        }

        for (let i = startPage; i <= endPage; i++) {
            const pageLi = document.createElement('li');
            pageLi.className = `page-item ${i === currentPageSEP ? 'active' : ''}`;
            pageLi.innerHTML = `<a class="page-link" href="#" onclick="changePageSEP(${i})">${i}</a>`;
            paginationControls.appendChild(pageLi);
        }

        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                const ellipsisLi = document.createElement('li');
                ellipsisLi.className = 'page-item disabled';
                ellipsisLi.innerHTML = `<span class="page-link">...</span>`;
                paginationControls.appendChild(ellipsisLi);
            }

            const lastLi = document.createElement('li');
            lastLi.className = 'page-item';
            lastLi.innerHTML = `<a class="page-link" href="#" onclick="changePageSEP(${totalPages})">${totalPages}</a>`;
            paginationControls.appendChild(lastLi);
        }

        // Next button
        const nextLi = document.createElement('li');
        nextLi.className = `page-item ${currentPageSEP === totalPages ? 'disabled' : ''}`;
        nextLi.innerHTML = `<a class="page-link" href="#" onclick="changePageSEP(${currentPageSEP + 1})">Next</a>`;
        paginationControls.appendChild(nextLi);
    }

    function changePageSEP(page) {
        const totalPages = rowsPerPageSEP === 'all' ? 1 : Math.ceil(filteredRowsSEP.length / rowsPerPageSEP);
        if (page < 1 || page > totalPages) return;
        
        currentPageSEP = page;
        displaySEPPage();
        updateSEPPagination();
    }

    // Event listener for rows per page change
    document.getElementById('rowsPerPageSEP').addEventListener('change', function() {
        rowsPerPageSEP = this.value === 'all' ? 'all' : parseInt(this.value);
        currentPageSEP = 1;
        displaySEPPage();
        updateSEPPagination();
    });

    // Make function globally available
    window.changePageSEP = changePageSEP;
    </script>


</body>
</html>