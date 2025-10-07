<?php
include '../src/db.php';
session_start();

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


require __DIR__ . '/../vendor/autoload.php';  
use Picqer\Barcode\BarcodeGeneratorPNG;

// ---------- FETCH DROPDOWN OPTIONS ----------
if (isset($_GET['action']) && $_GET['action'] === 'fetch_options') {
    header('Content-Type: application/json');
    $response = ["fund_sources" => [], "equipment_types" => []];

    // Fund sources
    $fund_sql = "SELECT fund_ID, fund_name FROM Fund_sources ORDER BY fund_name";
    $fund_result = $conn->query($fund_sql);
    while ($row = $fund_result->fetch_assoc()) {
        $response["fund_sources"][] = $row;
    }

    // Equipment types grouped by classification
    $type_sql = "SELECT type_ID, type_name, classification 
                FROM Equipment_types 
                ORDER BY classification, type_name";
    $type_result = $conn->query($type_sql);

    while ($row = $type_result->fetch_assoc()) {
        $response["equipment_types"][] = $row;
    }
    echo json_encode($response);
    exit;
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="./css/major.css">
    <link rel="stylesheet" href="./css/barcode_scanner.css">
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>

    <title>Inventory_Management_System-Barcode_Scanner_Page</title>

    <style>
    #barcodeModal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        align-items: center;
        justify-content: center;
        z-index: 1050;
    }

    .modal-content {
        background: #fff;
        padding: 20px;
        border-radius: 10px;
        width: 600px;
        max-height: 80vh;
        overflow-y: auto;
        text-align: center;
    }

    .success-message {
        width: 100%;
        text-align: center;
        margin-bottom: 15px;
        font-weight: bold;
        color: green;
    }

    .barcode-container {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        justify-content: center;
        margin: 15px 0;
    }

    .barcode-item {
        text-align: center;
        margin-bottom: 10px;
    }

    .text-end {
        text-align: right !important;
    }
    </style>
    
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
            <a href="barcode_scanner.php" class="nav-item active"><i class="fa-solid fa-barcode"></i> <span>Scanner</span></a>
            <a href="inventories.php" class="nav-item"><i class="fa-solid fa-warehouse"></i> <span>Inventories</span></a>
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
        <div class="title"><h1>Barcode Scanner</h1></div>
        <div class="main-content">
             <div class="top-panel">
                <div class="top-left">
                    <div class="search-box">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" id="searchInput" placeholder="Type item's barcode to search...">
                        <div id="results"></div>
                    </div>
                    <div class="scanner-section">
                        <div id="reader"><img src="./image/barcode-7000118_1280.jpg"></div>
                    </div>
                </div>
                <div class="top-right">
                    <h3>Reminder</h3>
                    <li>Ensure the barcode is clean and undamaged before scanning.</li>
                    <li>Confirm the item is registered in the system.</li>
                    <li>If the item is not yet registered, input the item data manually first, then generate a barcode.</li>
                    <li>After generating barcode for the item, scan, then assign to respective personnel.</li>
                    <li>If registered item’s barcode is unreadable, use search box.</li>
                </div>
             </div>
            <div class="bottom-panel">
                <div class="table-container-header">
                    <button class="collapse-btn btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#addItem" aria-expanded="false" aria-controls="addItem">
                        ADD NEW ITEM
                    </button>
                    
                    <div class="collapse" id="addItem">
                        <form id="itemForm" method="POST" enctype="multipart/form-data">
                            <div class="card card-body">

                                <div class="top-sec">
                                    <img id="profilePreview" class="item-img" src="./image/default_image.png" alt="Item Image" width="100px" height="100px">
                                </div>

                                <div class="mid-sec">
                                    <div class="form-group-left">
                                        <div class="form-group">
                                            <label for="item_image">Upload a Photo:</label>
                                            <input type="file" id="item_image" name="item_image" accept="image/*">
                                        </div>

                                        <div class="form-group">
                                            <label id="for-item_classification" for="item_classification">Item Classification</label>
                                            <select class="form-select" name="item_classification" required>
                                                <option selected disabled>Select Item Classification</option>
                                                <option value="Semi-Expendable Property">Semi-Expendable Property</option>
                                                <option value="Property Plant and Equipment">Property Plant and Equipment</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label id="for-item_name" for="item_name">Item name</label>
                                            <input type="text" name="item_name" placeholder="" required>
                                        </div>

                                        <div class="form-group">
                                            <label id="for-fund_ID" for="fund_ID">Fund Type</label>
                                            <select class="form-select" name="fund_ID" required>
                                                <option selected disabled>Select Fund Source</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label id="for-type_ID" for="type_ID">Equipment Type</label>
                                            <select class="form-select" name="type_ID" required>
                                                <option selected disabled>Select Equipment Type</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label id="for-description" for="description">Description</label>
                                            <textarea name="description" placeholder="" required></textarea>
                                        </div>
                                    </div>

                                    <div class="form-group-right">
                                        <div class="form-group">
                                            <label id="for-property_number" for="property_number">Property number</label>
                                            <input type="text" name="property_number" placeholder="" required>
                                        </div>

                                        <div class="form-group">
                                            <label id="for-unit_quantity" for="unit_quantity">Quantity</label>
                                            <input type="number" step="0.01" min="0" class="form-control" id="unit_quantity" name="unit_quantity" required>
                                        </div>

                                        <div class="form-group"> 
                                            <label id="for-unit_of_measure" for="unit_of_measure">Unit of measure</label>
                                                <select class="form-select" id="unit_of_measure" name="unit_of_measure" required>
                                                <option value="">Select Unit of Measure</option>

                                                <optgroup label="General">
                                                    <option value="pc">Piece (pc)</option>
                                                    <option value="doz">Dozen (doz)</option>
                                                    <option value="pr">Pair (pr)</option>
                                                    <option value="set">Set (set)</option>
                                                    <option value="pk">Pack (pk)</option>
                                                    <option value="box">Box (box)</option>
                                                    <option value="bdl">Bundle (bdl)</option>
                                                    <option value="kit">Kit (kit)</option>
                                                </optgroup>

                                                <optgroup label="Office Supplies">
                                                    <option value="ream">Ream (ream)</option>
                                                    <option value="roll">Roll (roll)</option>
                                                    <option value="pad">Pad (pad)</option>
                                                    <option value="btl">Bottle (btl)</option>
                                                    <option value="tube">Tube (tube)</option>
                                                    <option value="cart">Cartridge (cart)</option>
                                                </optgroup>

                                                <optgroup label="Equipment / Tools">
                                                    <option value="unit">Unit (unit)</option>
                                                    <option value="lot">Lot (lot)</option>
                                                    <option value="ln">Length (ln)</option>
                                                </optgroup>

                                                <optgroup label="Weight-Based">
                                                    <option value="kg">Kilogram (kg)</option>
                                                    <option value="g">Gram (g)</option>
                                                    <option value="MT">Metric Ton (MT)</option>
                                                    <option value="lb">Pound (lb)</option>
                                                </optgroup>

                                                <optgroup label="Volume-Based">
                                                    <option value="L">Liter (L)</option>
                                                    <option value="mL">Milliliter (mL)</option>
                                                    <option value="gal">Gallon (gal)</option>
                                                    <option value="m³">Cubic Meter (m³)</option>
                                                </optgroup>

                                                <optgroup label="Length / Dimension-Based">
                                                    <option value="m">Meter (m)</option>
                                                    <option value="cm">Centimeter (cm)</option>
                                                    <option value="mm">Millimeter (mm)</option>
                                                    <option value="in">Inch (in)</option>
                                                    <option value="ft">Foot (ft)</option>
                                                    <option value="yd">Yard (yd)</option>
                                                </optgroup>

                                                <optgroup label="Area-Based">
                                                    <option value="sq. m">Square Meter (sq. m)</option>
                                                    <option value="sq. ft">Square Foot (sq. ft)</option>
                                                    <option value="sq. yd">Square Yard (sq. yd)</option>
                                                </optgroup>

                                                <optgroup label="Construction / Volume Extra">
                                                    <option value="ft³">Cubic Foot (ft³)</option>
                                                    <option value="yd³">Cubic Yard (yd³)</option>
                                                </optgroup>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label id="for-unit_cost" for="unit_cost">Unit cost</label>
                                            <input type="text" name="unit_cost" placeholder="" required class="currency-input">
                                        </div>

                                        <div class="form-group">
                                            <label id="for-unit_total_cost" for="unit_total_cost">Total cost</label>
                                            <input type="text" name="unit_total_cost" placeholder="" required class="currency-input" readonly>
                                        </div>

                                        <div class="form-group">
                                            <label id="for-estimated_useful_life" for="estimated_useful_life">Estimated useful life</label>
                                            <input type="text" name="estimated_useful_life" placeholder="">
                                        </div>

                                        <div>
                                            <label id="for-acquisition_date" for="acquisition_date">Acquisition date</label>
                                            <input type="date" name="acquisition_date" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="bot-sec">
                                    <div class="form-actions">
                                        <button class="save-btn btn-outline-secondary" id="add-btn">Add Item to Inventory</button>
                                        <button type="reset" class="cancel-btn btn-outline-danger" data-bs-toggle="collapse" data-bs-target="#addItem" aria-expanded="false" aria-controls="addItem">Cancel</button>
                                    </div>
                                </div>

                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="main-content2">
            <div class="mt-4" style="margin-top: 0;">
                <h5 style="margin-top:0;margin-bottom:10px;font-weight:700;">Recently Added Item Units</h5>
                <div class="search-box" style="max-width: 480px;">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="unitsSearchInput" placeholder="Search by item name...">
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-striped align-middle" id="unitsTable" >
                        <thead class="table-dark" style="text-align: center;font-size: 12px;">
                            <tr>
                                <th style="width: 15%;">Item Name</th>
                                <th style="width: 12%;">Barcode</th>
                                <th style="width: 12%;">Unit Image</th>
                                <th style="width: 12%;">Assigned To</th>
                                <th style="width: 8%;">Status</th>
                                <th style="width: 8%;">Condition</th>
                                <th style="width: 15%;">Whereabouts</th>
                                <th style="width: 18%;">Actions</th>
                            </tr>
                        </thead>
                        <tbody style="text-align: center;font-size: 12px;">
                            <!-- Rows will be injected via JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- Barcode Modal -->
        <div id="barcodeModal">
            <div class="modal-content">
                <h3>Generate Barcodes?</h3>
                <p>The item has been saved. Do you want to generate barcodes now?</p>
                <button id="generateBarcodeBtn" class="btn btn-primary" style="margin-bottom: 10px;">Generate</button>
                <button id="closeModalBtn" class="btn btn-secondary">Cancel</button>
                <div id="barcodeResults">
                    <!-- Success message and barcodes will be inserted here -->
                </div>
            </div>
        </div>

    </main>
   Assign Item Modal
    <div class="modal fade" id="assignItemModal" tabindex="-1" aria-labelledby="assignItemModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="assignItemModalLabel">Assign Item to:</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="item-details-section">
                                <h6 class="section-title">Item Details</h6>
                                <div class="detail-group">
                                    <label>Item Name:</label>
                                    <span id="modal-item-name"></span>
                                </div>
                                <div class="detail-group">
                                    <label>Fund Type:</label>
                                    <span id="modal-fund-type"></span>
                                </div>
                                <div class="detail-group">
                                    <label>Equipment Type:</label>
                                    <span id="modal-equipment-type"></span>
                                </div>
                                <div class="detail-group">
                                    <label>Item Classification:</label>
                                    <span id="modal-classification"></span>
                                </div>
                                <div class="detail-group">
                                    <label>Description:</label>
                                    <span id="modal-description"></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="item-specs-section">
                                <h6 class="section-title">Specifications</h6>
                                <div class="detail-group">
                                    <label>Property Number:</label>
                                    <span id="modal-property-number"></span>
                                </div>
                                <div class="detail-group">
                                    <label>Quantity:</label>
                                    <span id="modal-quantity"></span>
                                </div>
                                <div class="detail-group">
                                    <label>Unit of Measure:</label>
                                    <span id="modal-uom"></span>
                                </div>
                                <div class="detail-group">
                                    <label>Unit Cost:</label>
                                    <span id="modal-unit-cost"></span>
                                </div>
                                <div class="detail-group">
                                    <label>Total Cost:</label>
                                    <span id="modal-total-cost"></span>
                                </div>
                                <div class="detail-group">
                                    <label>Estimated Useful Life:</label>
                                    <span id="modal-useful-life"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="assignment-section mt-4">
                        <h6 class="section-title">Assignment Details</h6>
                        <form id="assignmentForm">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="assign-to" class="form-label">Assign To:</label>
                                        <select class="form-select" id="assign-to" name="assign_to" required>
                                            <option value="" selected disabled>Select Person</option>
                                            <!-- Options will be populated via JavaScript -->
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="assign-date" class="form-label">Assignment Date:</label>
                                        <input type="date" class="form-control" id="assign-date" name="assign_date" required>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Additional Unit Details -->
                            <div class="row mt-3">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="unit_image" class="form-label">Unit Image (Optional)</label>
                                        <input type="file" class="form-control" id="unit_image" name="unit_image" accept="image/*">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="item_condition" class="form-label">Item Condition</label>
                                        <select class="form-select" id="item_condition" name="item_condition" required>
                                            <option value="Good Condition" selected>Good Condition</option>
                                            <option value="Defective">Defective</option>
                                            <option value="Unserviceable">Unserviceable</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="item_whereabouts" class="form-label">Item Whereabouts</label>
                                        <input type="text" class="form-control" id="item_whereabouts" name="item_whereabouts" placeholder="e.g., Storage Room A, Office 101" required>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirm-assignment">Confirm Assignment</button>
                </div>
            </div>
        </div>
    </div>

    <script src="./js/search_item.js"></script>
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

    //preview item_image
    document.getElementById('item_image').addEventListener('change', function(event) {
    const file = event.target.files[0];
    if (file) {
        document.getElementById('profilePreview').src = URL.createObjectURL(file);
    }
    });

  // Fetch dropdowns
    document.addEventListener("DOMContentLoaded", () => {
        fetch("barcode_scanner.php?action=fetch_options")
            .then(response => response.json())
            .then(data => {
                // ---------------- FUND SOURCES ----------------
                const fundSelect = document.querySelector("select[name='fund_ID']");
                data.fund_sources.forEach(fund => {
                    const option = document.createElement("option");
                    option.value = fund.fund_ID;
                    option.textContent = fund.fund_name;
                    fundSelect.appendChild(option);
                });

                // ---------------- EQUIPMENT TYPES ----------------
                const classificationSelect = document.querySelector("select[name='item_classification']");
                const typeSelect = document.querySelector("select[name='type_ID']");
                let allTypes = data.equipment_types; // store all equipment types

                // Function to populate equipment types
                function populateEquipmentTypes() {
                    const selectedClass = classificationSelect.value.trim();
                    typeSelect.innerHTML = '<option selected disabled>Select Equipment Type</option>';

                    // Group by classification
                    const groups = {};
                    allTypes.forEach(type => {
                        if (!groups[type.classification]) {
                            groups[type.classification] = [];
                        }
                        groups[type.classification].push(type);
                    });

                    if (selectedClass && groups[selectedClass]) {
                        // Show only relevant group
                        const optgroup = document.createElement("optgroup");
                        optgroup.label = selectedClass;
                        groups[selectedClass].forEach(type => {
                            const option = document.createElement("option");
                            option.value = type.type_ID;
                            option.textContent = type.type_name;
                            optgroup.appendChild(option);
                        });
                        typeSelect.appendChild(optgroup);
                        typeSelect.disabled = false;
                    } else {
                        // Disable dropdown if no classification selected
                        typeSelect.disabled = true;
                    }
                }

                // Re-populate equipment types when item classification changes
                classificationSelect.addEventListener("change", populateEquipmentTypes);

                // Disable type dropdown by default
                typeSelect.disabled = true;
            })
            .catch(error => console.error("Error fetching dropdown options:", error));
    });


    // modal
    document.getElementById("generateBarcodeBtn").addEventListener("click", function() {
        fetch("generate_barcode.php", {
            method: "POST",
            headers: {"Content-Type": "application/json"},
            body: JSON.stringify({ item_ID: window.savedItemID, quantity: window.savedQuantity })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                let resultsDiv = document.getElementById("barcodeResults");
                resultsDiv.innerHTML = "";
                
                // Add success message
                let successMsg = document.createElement("p");
                successMsg.className = "success-message";
                successMsg.textContent = data.message;
                resultsDiv.appendChild(successMsg);
                
                // Create container for barcodes
                let barcodeContainer = document.createElement("div");
                barcodeContainer.className = "barcode-container";
                
                // Add barcodes with item information
                data.barcodes.forEach(bc => {
                    let barcodeItem = document.createElement("div");
                    barcodeItem.className = "barcode-item";
                    barcodeItem.style.border = "1px solid #ddd";
                    barcodeItem.style.padding = "10px";
                    barcodeItem.style.borderRadius = "5px";
                    barcodeItem.style.backgroundColor = "#f9f9f9";
                    
                    // Item name
                    let itemName = document.createElement("p");
                    itemName.textContent = bc.item_name;
                    itemName.style.fontWeight = "bold";
                    itemName.style.margin = "0 0 5px 0";
                    itemName.style.fontSize = "14px";
                    
                    // Property number
                    let propNumber = document.createElement("p");
                    propNumber.textContent = "Property #: " + bc.property_number;
                    propNumber.style.margin = "0 0 10px 0";
                    propNumber.style.fontSize = "12px";
                    propNumber.style.color = "#666";
                    
                    // Barcode image
                    let img = document.createElement("img");
                    img.src = bc.image;
                    img.style.maxWidth = "200px";
                    img.style.display = "block";
                    img.style.margin = "0 auto 5px";
                    
                    // Barcode string
                    let text = document.createElement("p");
                    text.textContent = bc.string;
                    text.style.fontWeight = "bold";
                    text.style.margin = "0";
                    text.style.color = "#333";
                    text.style.fontSize = "12px";
                    
                    barcodeItem.appendChild(itemName);
                    barcodeItem.appendChild(propNumber);
                    barcodeItem.appendChild(img);
                    barcodeItem.appendChild(text);
                    barcodeContainer.appendChild(barcodeItem);
                });
                
                resultsDiv.appendChild(barcodeContainer);
				
				// Add action buttons container
				let actionsDiv = document.createElement("div");
				actionsDiv.style.marginTop = "15px";
				actionsDiv.style.display = "flex";
				actionsDiv.style.justifyContent = "center";
				actionsDiv.style.gap = "10px";

				// Download button (shown only if download_zip is provided)
				let downloadBtn = document.createElement("a");
				downloadBtn.id = "barcodeDownloadBtn";
				downloadBtn.className = "btn btn-success";
				downloadBtn.textContent = "Download";
				downloadBtn.style.display = data.download_zip ? "inline-block" : "none";
				if (data.download_zip) {
					downloadBtn.href = data.download_zip;
					downloadBtn.setAttribute("download", "");
				}

				// Close button
				let closeBtn = document.createElement("button");
				closeBtn.textContent = "Close";
				closeBtn.className = "btn btn-primary";
				closeBtn.onclick = function() {
					closeModal();
					resetForm();
				};

				actionsDiv.appendChild(downloadBtn);
				actionsDiv.appendChild(closeBtn);
				resultsDiv.appendChild(actionsDiv);
                
                // Hide the initial buttons
                document.getElementById("generateBarcodeBtn").style.display = "none";
                document.getElementById("closeModalBtn").style.display = "none";
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.error,
                    confirmButtonColor: '#dc3545'
                });
            }
        });
    });

    // Close modal function
    function closeModal() {
        document.getElementById("barcodeModal").style.display = "none";
        document.getElementById("barcodeResults").innerHTML = "";
        
        // Reset buttons visibility
        document.getElementById("generateBarcodeBtn").style.display = "inline-block";
        document.getElementById("closeModalBtn").style.display = "inline-block";
    }

    // Reset form function
    function resetForm() {
        // Reset the form
        document.getElementById("itemForm").reset();
        
        // Reset the image preview
        document.getElementById("profilePreview").src = "./image/default_image.png";
        
        // Collapse the form
        const collapseElement = document.getElementById('addItem');
        const bsCollapse = new bootstrap.Collapse(collapseElement, {
            toggle: false
        });
        bsCollapse.hide();
    }

    // Add event listener to the cancel button in the modal
    document.getElementById("closeModalBtn").addEventListener("click", function() {
        closeModal();
        resetForm();
    });

    // Handle item form submission via AJAX
    document.getElementById("itemForm").addEventListener("submit", function(e) {
        e.preventDefault();
        console.log("Form submission started");
        
        // Parse formatted currency values back to numbers
        const unitCostInput = document.querySelector('input[name="unit_cost"]');
        const totalCostInput = document.querySelector('input[name="unit_total_cost"]');
        const quantityInput = document.querySelector('input[name="unit_quantity"]');
        
        // Remove commas and convert to numbers
        const unitCost = parseCurrencyValue(unitCostInput.value);
        const totalCost = parseCurrencyValue(totalCostInput.value);
        const quantity = parseFloat(quantityInput.value);
        
        // Update the form values
        unitCostInput.value = unitCost;
        totalCostInput.value = totalCost;
        quantityInput.value = quantity;
        
        const formData = new FormData(this);
        console.log("Sending form data:", formData);

        fetch("../src/insert_item_sep.php", {
            method: "POST",
            body: formData
        })
        .then(res => {
            console.log("Response received");
            return res.json();
        })
        .then(data => {
            console.log("Response data:", data);
            if (data.success) {
                window.savedItemID = data.item_ID;
                window.savedQuantity = data.quantity;
                document.getElementById("barcodeModal").style.display = "flex"; // open modal
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error Saving Item',
                    text: data.error,
                    confirmButtonColor: '#dc3545'
                });
            }
        })
        .catch(error => {
            console.error("Error:", error);
            Swal.fire({
                icon: 'error',
                title: 'Network Error',
                text: 'An error occurred while saving the item.',
                confirmButtonColor: '#dc3545'
            });
        });
    });

    // Currency formatting functionality
    document.addEventListener("DOMContentLoaded", function() {
        // Format currency inputs
        const currencyInputs = document.querySelectorAll('.currency-input');
        
        currencyInputs.forEach(input => {
            // Format on blur (when leaving the field)
            input.addEventListener('blur', function() {
                if (this.value) {
                    formatCurrencyInput(this);
                }
            });
            
            // Remove commas on focus (for editing)
            input.addEventListener('focus', function() {
                this.value = this.value.replace(/,/g, '');
            });
        });
        
        // Auto-calculate total cost when unit cost, quantity, or UOM changes
        document.querySelector('input[name="unit_cost"]').addEventListener('input', calculateTotalCost);
        document.querySelector('input[name="unit_quantity"]').addEventListener('input', calculateTotalCost);
        document.getElementById('unit_of_measure').addEventListener('change', calculateTotalCost);
        
        // Before form submission, remove commas from all currency fields
        document.getElementById('itemForm').addEventListener('submit', function(e) {
            const currencyInputs = document.querySelectorAll('.currency-input');
            currencyInputs.forEach(input => {
                input.value = input.value.replace(/,/g, '');
            });
        });
    });

    // Format currency input with commas
    function formatCurrencyInput(input) {
        let value = input.value.replace(/[^\d.]/g, '');
        
        if (value) {
            // Handle decimal places
            let parts = value.split('.');
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            
            // Limit to 2 decimal places
            if (parts.length > 1) {
                parts[1] = parts[1].slice(0, 2);
            }
            
            input.value = parts.join('.');
        }
    }

    // Calculate total cost automatically with UOM logic
    function calculateTotalCost() {
        const unitCostInput = document.querySelector('input[name="unit_cost"]');
        const quantityInput = document.querySelector('input[name="unit_quantity"]');
        const totalCostInput = document.querySelector('input[name="unit_total_cost"]');
        const uomSelect = document.getElementById('unit_of_measure');
        
        const unitCost = parseCurrencyValue(unitCostInput.value) || 0;
        const quantity = parseFloat(quantityInput.value) || 0;
        const selectedUOM = uomSelect.value.trim().toLowerCase();

        // UOMs where quantity should be ignored (total cost = unit cost)
        const nonMultiplyingUOMs = [ "sq. m", "sq. ft", "sq. yd",
                "m³", "ft³", "yd³", "kg", "g", "MT", "lb", "L", "mL", "gal",
                "m", "cm", "mm", "in", "ft", "yd"];

        let totalCost = 0;

        if (!unitCost) {
            totalCostInput.value = ""; // reset if cost is empty
            return;
        }

        if (nonMultiplyingUOMs.includes(selectedUOM)) {
            totalCost = unitCost;
        } else {
            if (!quantity) {
                totalCostInput.value = ""; // reset if quantity is empty
                return;
            }
            totalCost = quantity * unitCost;
        }
        
        if (!isNaN(totalCost)) {
            // Format the total cost with commas
            totalCostInput.value = totalCost.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }
    }

    // Parse currency value (remove commas)
    function parseCurrencyValue(formattedValue) {
        if (!formattedValue) return 0;
        return parseFloat(formattedValue.toString().replace(/,/g, ''));
    }

    // ================= Item Units Searchable Table =================
    // State for live search
    let unitsSearchAbortController = null;
    let unitsSearchDebounceTimer = null;

    function renderUnitsRows(items) {
        const tbody = document.querySelector('#unitsTable tbody');
        tbody.innerHTML = '';
        if (!items || items.length === 0) {
            const tr = document.createElement('tr');
            const td = document.createElement('td');
            td.colSpan = 8;
            td.textContent = 'No results';
            td.className = 'text-center';
            tr.appendChild(td);
            tbody.appendChild(tr);
            return;
        }
        items.forEach(row => {
            const tr = document.createElement('tr');
            
            // Item Name
            const tdName = document.createElement('td');
            tdName.textContent = row.item_name;
            
            // Barcode
            const tdBarcode = document.createElement('td');
            tdBarcode.textContent = row.barcode;
            
            // Unit Image
            const tdUnitImage = document.createElement('td');
            if (row.unit_image) {
                const img = document.createElement('img');
                img.src = row.unit_image;
                img.style.width = '40px';
                img.style.height = '40px';
                img.style.objectFit = 'cover';
                img.style.borderRadius = '4px';
                tdUnitImage.appendChild(img);
            } else {
                tdUnitImage.textContent = 'No image';
                tdUnitImage.className = 'text-muted';
            }
            
            // Assigned To
            const tdAssignedTo = document.createElement('td');
            tdAssignedTo.textContent = row.assign_to_name || 'Unassigned';
            
            // Status
            const tdStatus = document.createElement('td');
            const statusBadge = document.createElement('span');
            statusBadge.textContent = row.status;
            statusBadge.className = row.status === 'Available' ? 'badge bg-success' : 'badge bg-warning';
            tdStatus.appendChild(statusBadge);
            
            // Condition
            const tdCondition = document.createElement('td');
            const conditionBadge = document.createElement('span');
            conditionBadge.textContent = row.item_condition;
            if (row.item_condition === 'Good Condition') {
                conditionBadge.className = 'badge bg-success';
            } else if (row.item_condition === 'Defective') {
                conditionBadge.className = 'badge bg-warning';
            } else {
                conditionBadge.className = 'badge bg-danger';
            }
            tdCondition.appendChild(conditionBadge);
            
            // Whereabouts
            const tdWhereabouts = document.createElement('td');
            tdWhereabouts.textContent = row.item_whereabouts || 'Not specified';
            tdWhereabouts.className = 'text-muted';
            
            // Actions
            const tdActions = document.createElement('td');
            tdActions.className = 'text-end';

            // Download button
            const dl = document.createElement('a');
            dl.className = 'btn btn-sm btn-success me-1';
            dl.textContent = 'Download';
            dl.href = `../src/download_barcode_image.php?unit_id=${encodeURIComponent(row.unit_ID)}`;
            dl.setAttribute('download', '');

            // Assign button (only show if not assigned)
            const assignBtn = document.createElement('button');
            assignBtn.className = 'btn btn-sm btn-primary';
            assignBtn.textContent = 'Assign';
            assignBtn.onclick = function() {
                window.currentUnitId = row.unit_ID;
                // Populate modal with item details
                document.getElementById('modal-item-name').textContent = row.item_name;
                document.getElementById('modal-property-number').textContent = row.property_number || 'N/A';
                // Show the modal
                const modal = new bootstrap.Modal(document.getElementById('assignItemModal'));
                modal.show();
            };

            tdActions.appendChild(dl);
            if (row.status === 'Available') {
                tdActions.appendChild(assignBtn);
            }
            
            // Append all cells to row
            tr.appendChild(tdName);
            tr.appendChild(tdBarcode);
            tr.appendChild(tdUnitImage);
            tr.appendChild(tdAssignedTo);
            tr.appendChild(tdStatus);
            tr.appendChild(tdCondition);
            tr.appendChild(tdWhereabouts);
            tr.appendChild(tdActions);
            tbody.appendChild(tr);
        });
    }

    async function searchItemUnits() {
        const q = document.getElementById('unitsSearchInput').value.trim();
        if (unitsSearchAbortController) {
            unitsSearchAbortController.abort();
        }
        
        unitsSearchAbortController = new AbortController();
        const signal = unitsSearchAbortController.signal;
        try {
            // Always fetch data - either search results or default recent items
            const url = q ? `../src/search_item_units.php?q=${encodeURIComponent(q)}` : '../src/search_item_units.php';
            console.log('Fetching URL:', url);
            const res = await fetch(url, { signal });
            const data = await res.json();
            console.log('API Response:', data);
            if (data.success) {
                renderUnitsRows(data.items);
            } else {
                console.error('API Error:', data.error);
                renderUnitsRows([]);
            }
        } catch (e) {
            if (e.name === 'AbortError') {
                return; // ignore aborted requests
            }
            console.error('Units search failed', e);
            renderUnitsRows([]);
        }
    }

    document.getElementById('unitsSearchInput').addEventListener('keydown', function(e){
        if (e.key === 'Enter') { e.preventDefault(); searchItemUnits(); }
    });
    document.getElementById('unitsSearchInput').addEventListener('input', function(){
        if (unitsSearchDebounceTimer) {
            clearTimeout(unitsSearchDebounceTimer);
        }
        unitsSearchDebounceTimer = setTimeout(() => {
            searchItemUnits();
        }, 300);
    });

    // Load initial data (10 most recent items) when page loads
    document.addEventListener('DOMContentLoaded', function() {
        searchItemUnits();
    });

    // Handle assignment form submission
    document.getElementById('confirm-assignment').addEventListener('click', function() {
        const form = document.getElementById('assignmentForm');
        const formData = new FormData(form);
        
        // Add the unit_ID from the modal data
        const unitId = window.currentUnitId;
        if (!unitId) {
            Swal.fire({
                icon: 'warning',
                title: 'No Unit Selected',
                text: 'No unit selected for assignment',
                confirmButtonColor: '#dc3545'
            });
            return;
        }
        
        formData.append('unit_ID', unitId);
        
        fetch('../src/assign_item.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: 'Item assigned successfully!',
                    confirmButtonColor: '#198754'
                });
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('assignItemModal'));
                modal.hide();
                // Refresh the units table
                searchItemUnits();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Assignment Error',
                    text: data.error,
                    confirmButtonColor: '#dc3545'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Network Error',
                text: 'An error occurred while assigning the item',
                confirmButtonColor: '#dc3545'
            });
        });
    });

    </script>
</body>
</html>