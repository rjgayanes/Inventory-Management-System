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
            <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="profileDropdown" >
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
        <!-- Main Personnel Section -->
        <div id="personnelSection">
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
                            <li><a href="inventories.php" class="li-item active"><i class="bi bi-people"></i> Personnels</a></li>
                            <li><a href="inventories_ppe.php" class="li-item"><i class="bi bi-inboxes"></i> Property Plant And Equipment</a></li>
                            <li><a href="inventories_sep.php" class="li-item"><i class="bi bi-inbox"></i> Semi-Expendable Properties</a></li>
                            <li><a href="inventories_ue.php" class="li-item"><i class="bi bi-archive"></i> Unserviceable Equipmnets</a></li>
                        </ul>
                    </div>
                    <!-- Personnel / Office Content -->
                    <div class="table-container-header">
                        <h3><i class="bi bi-people-fill"></i> Inventory per Persons / Office</h3>
                        <div class="row-filter" style="display: flex; align-items: center; gap: 8px;">
                            <label for="rowsPerPage" style="font-size: 14px; font-weight: 500; white-space: nowrap;">Rows:</label>
                            <select id="rowsPerPage" class="form-select form-select-sm" style="width: auto; font-size: 14px; min-width: 80px;">
                                <option value="10" selected>10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                                <option value="all">All</option>
                            </select>
                        </div>
                        <div class="header-actions">
                            <button class="collapse-btn btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#addPerson" aria-expanded="false" aria-controls="addPerson">
                                Add Person
                            </button>
                            <div class="search-box">
                                <i class="fa-solid fa-magnifying-glass"></i>
                                <input type="text" id="searchPersons" placeholder="Search persons...">
                            </div>
                        </div>
                            
                            <div class="collapse" id="addPerson">
                                <div class="card card-body">
                                    <form id="personForm">
                                        <div class="top-sec">
                                            <img id="profilePreview" class="profile-img" src="./uploads/profile_images/default_profile.jpg" alt="Profile Image" width="100px" height="100px">
                                        </div>

                                        <div class="mid-sec">
                                            <div class="form-group-left">
                                                <div class="form-group">
                                                    <label for="profile_image">Upload a Photo:</label>
                                                    <input type="file" id="profile_image" name="profile_image" accept="image/*">
                                                </div>

                                                <div class="form-group">
                                                    <label for="first_name">First Name:</label>
                                                    <input type="text" name="first_name" placeholder="Enter personnel's first name" required>
                                                </div>

                                                <div class="form-group">
                                                    <label for="last_name">Last Name:</label>
                                                    <input type="text" name="last_name" placeholder="Enter personnel's last name" required>
                                                </div>
                                            </div>

                                            <div class="form-group-right">
                                                <div class="form-group">
                                                    <label for="professional_designations">Professional Designation:</label>
                                                    <input type="text" name="professional_designations" placeholder="Enter personnel's professional designation (e.g. MIT, MSIT)">
                                                </div>

                                                <div class="form-group">
                                                    <label for="office_name">Office Name:</label>
                                                    <select name="office_name" id="office_name" required>
                                                        <option value="">Select Office Name</option>
                                                        <option value="Accounting Office">Accounting Office</option>
                                                        <option value="Admission's Office">Admission's Office</option>
                                                        <option value="BAC Office">Bids and Awards Committee Office</option>
                                                        <option value="BME Dean's Office">BME Dean's Office</option>
                                                        <option value="Budget Office">Budget Office</option>
                                                        <option value="Campus Director's Office">Campus Director's Office</option>
                                                        <option value="Cashier">Cashier's Office</option>
                                                        <option value="Com. Lab. A">Computer Laboratory A</option>
                                                        <option value="Com. Lab. B">Computer Laboratory B</option>
                                                        <option value="Com. Lab. C">Computer Laboratory C</option>
                                                        <option value="Com. Lab. D">Computer Laboratory D</option>
                                                        <option value="Com. Lab. E">Computer Laboratory E</option>
                                                        <option value="DRRM Office">Disaster Risk Reduction and Management Office</option>
                                                        <option value="Extension Office">Extension Office</option>
                                                        <option value="General Services and Management Office">General Services and Management Office</option>
                                                        <option value="Guidance Office">Guidance Office</option>
                                                        <option value="Health Services Unit">Health Services Unit</option>
                                                        <option value="HR Office">Human Resource and Management Office</option>
                                                        <option value="ICT Dean's Office">ICT Dean's Office</option>
                                                        <option value="IGP Coordinator's Office">IGP Coordinator's Office</option>
                                                        <option value="ILDO">ILDO</option>
                                                        <option value="Library">Library</option>
                                                        <option value="Medical and Dental Office">Medical and Dental Office</option>
                                                        <option value="Motorpool">Motorpool</option>
                                                        <option value="Network Lab. A">Network Laboratory A</option>
                                                        <option value="Network Lab. B">Network Laboratory B</option>
                                                        <option value="NSTP Coordinator Office">NSTP Office</option>
                                                        <option value="QA Office">Quality Assurance Office</option>
                                                        <option value="Records' Office">Records' Office</option>
                                                        <option value="Registrar">Office of the Registrar</option>
                                                        <option value="Research and Development Office">Research and Development Office</option>
                                                        <option value="Safety and Security Office">Safety and Security Office</option>
                                                        <option value="SDS Office">Student Development Services Office</option>
                                                        <option value="Sports">Sports</option>
                                                        <option value="Supply and Property Office">Supply and Property Office</option>
                                                    </select>
                                                </div>

                                                <div class="form-group">
                                                    <label for="role">Role:</label>
                                                    <input type="text" name="role" id="role" placeholder="Enter personnel's role" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="bot-sec">
                                            <button type="button" class="save-btn btn-outline-secondary" id="save-person-btn">Save Person</button>
                                            <button type="reset" class="cancel-btn btn-outline-danger" data-bs-toggle="collapse" data-bs-target="#addPerson" aria-expanded="false" aria-controls="addPerson">Cancel</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Table -->
                        <table class="table table-bordered table-hover">
                            <thead class="table-dark" style="text-align: center;font-size: 12px;">
                            <tr>
                                <th>Profile Image</th>
                                <th>Full Name</th>
                                <th>Office Name</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody id="personnelTableBody" style="text-align: center;font-size: 12px;">
                            <!-- Rows inserted dynamically -->
                            </tbody>
                        </table>
                        
                        <!-- Pagination Controls -->
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <nav aria-label="Personnel pagination">
                                <ul class="pagination pagination-sm mb-0" id="paginationControls">
                                    <!-- Pagination buttons inserted dynamically -->
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
                <div class="bottom-panel"></div>
            </div>
        </div>
        
        <!-- Hidden items section -->
        <div id="itemsSection" class="hidden">
            <div class="title">
                <h1>Inventory Custodian Slip</h1>
            </div>
            <div class="main-content">
                <div class="top-panel">
                    <button class="back-btn" onclick="goBack()"><i class="bi bi-arrow-left-circle"></i></button>
                    <div class="top-hid">
                        <div class="top-left">
                            <img id="personProfile" src="./image/default_profile.jpg" alt="Profile" width="50px" height="50px" style="border-radius: 25%;">
                            <div>
                                <h3 id="personName"></h3>
                                <p id="personOfficeRole"></p>
                            </div>
                        </div>
                        <div class="top-right">
                            <button class="editPerson btn-outline-danger"><i class="fa-solid fa-pen-to-square"></i></button>
                        </div>
                    </div>
                    <!-- Item value filtering -->
                    <div class="filter-value" style="display: flex;flex-direction:row;gap:10px;justify-content:flex-end;align-items:center;margin-top:10px;">
                        <label for="filter-select" style="font-size: 14px;font-weight:500;">Filter by:</label>
                        <select name="filter-select" id="filter-select" class="form-select form-select-sm" style="width: 20%;font-size: 14px;font-weight:500;">
                            <option value="all">All Items</option>
                            <option value="lowValue">Low Value (≤ ₱5,000)</option>
                            <option value="highValue">High Value (≥ ₱5,000)</option>
                        </select>
                    </div>
                    <table id="itemsTable" class="table table-bordered table-hover">
                        <thead class="table-dark" style="text-align: center;font-size: 12px;">
                            <tr>
                                <th rowspan="2">Quantity</th>
                                <th rowspan="2">Unit</th>
                                <th colspan="2">Amount</th>
                                <th rowspan="2">DESCRIPTION</th>
                                <th rowspan="2">Inventory Item</th>
                                <th rowspan="2">Estimated Useful Life</th>
                            </tr>
                            <tr>
                                <th>Unit Cost</th>
                                <th>Total Cost</th>
                            </tr>
                        </thead>
                        <tbody id="itemsTableBody" style="text-align: center;font-size: 12px;"></tbody>
                    </table>
                    <div class="file-export">
                        <span class="dnwld">Download:</span>
                        <button type="button" class="btn btn-outline-success btn-sm"><i class="fa-regular fa-file-excel"></i> Excel File</button>
                        <button type="button" class="btn btn-outline-primary btn-sm"><i class="fa-regular fa-file-pdf"></i> PDF</button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Update Person Modal -->
    <div id="updatePersonModal" class="modal">
        <div class="modal-content">
            <div class="modal-content-header">
                <button type="button" class="modal-cancel-btn" id="cancelUpdateBtn"><i class="fa-regular fa-circle-xmark"></i></button>
                <h2>Update Person</h2>
            </div>
            <form id="updatePersonForm" enctype="multipart/form-data">
                <!-- Profile image preview -->
                <img id="updateProfilePreview" class="profile-img" src="./uploads/profile_images/default_profile.jpg" alt="Profile Image">

                <div class="form-group">
                    <label for="update_profile_image">Upload a Photo</label>
                    <input type="file" id="update_profile_image" name="profile_image" accept="image/*">
                </div>

                <div class="form-group">
                    <label for="update_first_name">First Name:</label>
                    <input type="text" id="update_first_name" name="first_name" placeholder="Enter personnel's first name" required>
                </div>

                <div class="form-group">
                    <label for="update_last_name">Last Name:</label>
                    <input type="text" id="update_last_name" name="last_name" placeholder="Enter personnel's last name" required>
                </div>

                <div class="form-group">
                    <label for="update_professional_designations">Professional Designation:</label>
                    <input type="text" id="update_professional_designations" name="professional_designations" placeholder="Enter personnel's professional designation (e.g. MIT, MSIT)">
                </div>

                <div class="form-group">
                    <label for="update_office_name">Office Name:</label>
                    <select name="office_name" id="update_office_name" required>
                        <option value="">Select Office Name</option>
                        <option value="Accounting Office">Accounting Office</option>
                        <option value="Admission's Office">Admission's Office</option>
                        <option value="BAC Office">Bids and Awards Committee Office</option>
                        <option value="BME Dean's Office">BME Dean's Office</option>
                        <option value="Budget Office">Budget Office</option>
                        <option value="Campus Director's Office">Campus Director's Office</option>
                        <option value="Cashier">Cashier's Office</option>
                        <option value="Com. Lab. A">Computer Laboratory A</option>
                        <option value="Com. Lab. B">Computer Laboratory B</option>
                        <option value="Com. Lab. C">Computer Laboratory C</option>
                        <option value="Com. Lab. D">Computer Laboratory D</option>
                        <option value="Com. Lab. E">Computer Laboratory E</option>
                        <option value="DRRM Office">Disaster Risk Reduction and Management Office</option>
                        <option value="Extension Office">Extension Office</option>
                        <option value="General Services and Management Office">General Services and Management Office</option>
                        <option value="Guidance Office">Guidance Office</option>
                        <option value="Health Services Unit">Health Services Unit</option>
                        <option value="HR Office">Human Resource and Management Office</option>
                        <option value="ICT Dean's Office">ICT Dean's Office</option>
                        <option value="IGP Coordinator's Office">IGP Coordinator's Office</option>
                        <option value="ILDO">ILDO</option>
                        <option value="Library">Library</option>
                        <option value="Medical and Dental Office">Medical and Dental Office</option>
                        <option value="Motorpool">Motorpool</option>
                        <option value="Network Lab. A">Network Laboratory A</option>
                        <option value="Network Lab. B">Network Laboratory B</option>
                        <option value="NSTP Coordinator Office">NSTP Office</option>
                        <option value="QA Office">Quality Assurance Office</option>
                        <option value="Records' Office">Records' Office</option>
                        <option value="Registrar">Office of the Registrar</option>
                        <option value="Research and Development Office">Research and Development Office</option>
                        <option value="Safety and Security Office">Safety and Security Office</option>
                        <option value="SDS Office">Student Development Services Office</option>
                        <option value="Sports">Sports</option>
                        <option value="Supply and Property Office">Supply and Property Office</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="update_role">Role:</label>
                    <input type="text" id="update_role" name="role" placeholder="Enter personnel's role" required>
                </div>

                <div class="button-group">
                    <button type="submit" class="update-btn btn-outline-primary">Update Person</button>
                </div>
            </form>
        </div>
    </div>

    <script src="./js/day_date.js"></script>
    <script src="./js/add-fetch_person.js"></script>
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

    // Update Person Modal functionality
    let currentPersonId = null;

    // Function to show update modal
    function showUpdateModal(personId, personData) {
        currentPersonId = personId;
        
        // Populate form with existing data
        document.getElementById('update_first_name').value = personData.first_name || '';
        document.getElementById('update_last_name').value = personData.last_name || '';
        document.getElementById('update_professional_designations').value = personData.professional_designations || '';
        document.getElementById('update_office_name').value = personData.office_name || '';
        document.getElementById('update_role').value = personData.role || '';
        
        // Set profile image
        if (personData.profile_image) {
            document.getElementById('updateProfilePreview').src = personData.profile_image;
        } else {
            document.getElementById('updateProfilePreview').src = './uploads/profile_images/default_profile.jpg';
        }
        
        // Show modal
        document.getElementById('updatePersonModal').style.display = 'flex';
    }

    // Function to hide update modal
    function hideUpdateModal() {
        document.getElementById('updatePersonModal').style.display = 'none';
        currentPersonId = null;
    }

    // Event listeners for modal
    document.getElementById('cancelUpdateBtn').addEventListener('click', hideUpdateModal);
    
    // Close modal when clicking outside
    document.getElementById('updatePersonModal').addEventListener('click', function(e) {
        if (e.target === this) {
            hideUpdateModal();
        }
    });

    // Profile image preview
    document.getElementById('update_profile_image').addEventListener('change', function(event) {
        const file = event.target.files[0];
        if (file) {
            document.getElementById('updateProfilePreview').src = URL.createObjectURL(file);
        }
    });

    // Handle form submission
    document.getElementById('updatePersonForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (!currentPersonId) {
            Swal.fire({
                icon: 'warning',
                title: 'No Person Selected',
                text: 'No person selected for update',
                confirmButtonColor: '#dc3545'
            });
            return;
        }

        const formData = new FormData(this);
        formData.append('person_id', currentPersonId);

        fetch('../src/update_person.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: 'Person updated successfully!',
                    confirmButtonColor: '#198754'
                });
                hideUpdateModal();
                // Refresh the current person's items to show updated profile image
                if (window.currentPersonId) {
                    loadPersonItems(window.currentPersonId);
                }
                // Also refresh the persons table
                if (typeof loadPersons === 'function') {
                    loadPersons();
                }
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error: ' + data.error,
                    confirmButtonColor: '#dc3545'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred while updating the person',
                confirmButtonColor: '#dc3545'
            });
        });
    });

    // Make showUpdateModal globally available
    window.showUpdateModal = showUpdateModal;

    // Pagination variables
    let currentPage = 1;
    let rowsPerPage = 10;
    let allPersonnelData = [];
    let filteredPersonnelData = [];

    // Function to update pagination info and controls
    function updatePagination() {
        const totalRows = filteredPersonnelData.length;
        const totalPages = Math.ceil(totalRows / rowsPerPage);
        const startRow = (currentPage - 1) * rowsPerPage + 1;
        const endRow = Math.min(currentPage * rowsPerPage, totalRows);

        // Update pagination info
        document.getElementById('paginationInfo').textContent = 
            `Showing ${totalRows > 0 ? startRow : 0} to ${endRow} of ${totalRows} entries`;

        // Update pagination controls
        const paginationControls = document.getElementById('paginationControls');
        paginationControls.innerHTML = '';

        if (totalPages <= 1) return;

        // Previous button
        const prevLi = document.createElement('li');
        prevLi.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
        prevLi.innerHTML = `<a class="page-link" href="#" onclick="changePage(${currentPage - 1})">Previous</a>`;
        paginationControls.appendChild(prevLi);

        // Page numbers
        const startPage = Math.max(1, currentPage - 2);
        const endPage = Math.min(totalPages, currentPage + 2);

        if (startPage > 1) {
            const firstLi = document.createElement('li');
            firstLi.className = 'page-item';
            firstLi.innerHTML = `<a class="page-link" href="#" onclick="changePage(1)">1</a>`;
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
            pageLi.className = `page-item ${i === currentPage ? 'active' : ''}`;
            pageLi.innerHTML = `<a class="page-link" href="#" onclick="changePage(${i})">${i}</a>`;
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
            lastLi.innerHTML = `<a class="page-link" href="#" onclick="changePage(${totalPages})">${totalPages}</a>`;
            paginationControls.appendChild(lastLi);
        }

        // Next button
        const nextLi = document.createElement('li');
        nextLi.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
        nextLi.innerHTML = `<a class="page-link" href="#" onclick="changePage(${currentPage + 1})">Next</a>`;
        paginationControls.appendChild(nextLi);
    }

    // Function to change page
    function changePage(page) {
        const totalPages = Math.ceil(filteredPersonnelData.length / rowsPerPage);
        if (page < 1 || page > totalPages) return;
        
        currentPage = page;
        displayPersonnelPage();
        updatePagination();
    }

    // Function to display current page of personnel
    function displayPersonnelPage() {
        const startIndex = (currentPage - 1) * rowsPerPage;
        const endIndex = rowsPerPage === 'all' ? filteredPersonnelData.length : startIndex + parseInt(rowsPerPage);
        const pageData = filteredPersonnelData.slice(startIndex, endIndex);
        
        // Use existing displayPersons function but with page data
        if (typeof window.displayPersonsWithData === 'function') {
            window.displayPersonsWithData(pageData);
        }
    }

    // Function to filter personnel data
    function filterPersonnelData(searchTerm = '') {
        if (!allPersonnelData.length) return;
        
        filteredPersonnelData = allPersonnelData.filter(person => {
            const fullName = `${person.first_name} ${person.last_name}`.toLowerCase();
            const officeName = person.office_name.toLowerCase();
            const role = person.role.toLowerCase();
            const search = searchTerm.toLowerCase();
            
            return fullName.includes(search) || 
                   officeName.includes(search) || 
                   role.includes(search);
        });
        
        currentPage = 1; // Reset to first page when filtering
        displayPersonnelPage();
        updatePagination();
    }

    // Event listeners for pagination controls
    document.getElementById('rowsPerPage').addEventListener('change', function() {
        rowsPerPage = this.value === 'all' ? 'all' : parseInt(this.value);
        currentPage = 1;
        displayPersonnelPage();
        updatePagination();
    });

    document.getElementById('searchPersons').addEventListener('input', function() {
        filterPersonnelData(this.value);
    });

    // Make functions globally available
    window.changePage = changePage;
    window.updatePersonnelPagination = function(data) {
        allPersonnelData = data;
        filteredPersonnelData = [...data];
        currentPage = 1;
        displayPersonnelPage();
        updatePagination();
    };

    // Handle edit button in top-right div (items section)
    document.addEventListener('click', function(e) {
        if (e.target.closest('.editPerson')) {
            e.preventDefault();
            
            // Get the current person's data from the displayed information
            const personName = document.getElementById('personName').textContent;
            const personOfficeRole = document.getElementById('personOfficeRole').textContent;
            const personProfile = document.getElementById('personProfile').src;
            
            // Extract data from the displayed information
            const nameParts = personName.split(',');
            const fullName = nameParts[0].trim();
            const nameArray = fullName.split(' ');
            const firstName = nameArray[0];
            const lastName = nameArray.slice(1).join(' ');
            
            const officeRoleParts = personOfficeRole.split(',');
            const officeName = officeRoleParts[0].trim();
            const role = officeRoleParts[1] ? officeRoleParts[1].trim() : '';
            
            // Create person data object
            const personData = {
                first_name: firstName,
                last_name: lastName,
                professional_designations: nameParts[1] ? nameParts[1].trim() : '',
                office_name: officeName,
                role: role,
                profile_image: personProfile
            };
            
            // Get person ID from the current URL or stored data
            const urlParams = new URLSearchParams(window.location.search);
            const personId = urlParams.get('id') || window.currentPersonId;
            
            if (personId) {
                showUpdateModal(personId, personData);
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Person Not Found',
                    text: 'Person ID not found',
                    confirmButtonColor: '#dc3545'
                });
            }
        }
    });
    </script>
</body>
</html>