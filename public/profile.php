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
    $profileImage = './uploads/profile_images/default_profile.jpg'; // Path to default image

}

// Get the number of rows to display (default 10)
$rowsToShow = isset($_GET['rows']) ? (int)$_GET['rows'] : 10;
$rowsToShow = max(5, min(100, $rowsToShow)); // Limit between 5 and 100

// Fetch all activity logs (no LIMIT) - frontend will handle row limiting
$logSql = "SELECT log_ID, action, timestamp FROM Activity_Log WHERE user_ID = ? ORDER BY timestamp DESC";
$logStmt = $conn->prepare($logSql);
$logStmt->bind_param("i", $user_ID);
$logStmt->execute();
$activityResult = $logStmt->get_result();

// Fetch distinct actions from Activity_Log for dropdown
$actionSql = "SELECT DISTINCT action FROM Activity_Log WHERE user_ID = ?";
$actionStmt = $conn->prepare($actionSql);
$actionStmt->bind_param("i", $user_ID);
$actionStmt->execute();
$actionResult = $actionStmt->get_result();

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
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <link rel="stylesheet" href="./css/major.css">
    <link rel="stylesheet" href="./css/profile.css">
    
    <title>Inventory_Management_System-Profile_Page</title>
   
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
                     alt="profile" class="rounded-circle" width="35" height="35" >
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
            <a href="inventories.php" class="nav-item"><i class="fa-solid fa-warehouse"></i> <span>Inventories</span></a>
            <a href="profile.php" class="nav-item active"><i class="fa-solid fa-circle-user"></i> <span>Profile</span></a>
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
            <h1>Profile</h1>
        </div>
       <div class="main-content">
            <div class="left-panels">
                <div class="profile-card">
                    <div class="profile-image">
                        <h2>General</h2>
                        <img src="<?php echo htmlspecialchars($profileImage); ?>" class="rounded-circle" width="100" height="100">
                        <div class="edit">
                            <a class="edit-btn btn-danger" id="editProfileBtn"><i class="fa-solid fa-pen-to-square"></i></a>
                        </div>
                    </div>
                    <div class="profile-details">
                        <div class="uname">
                            <h6>Username:</h6>
                            <p><?php echo htmlspecialchars($user['user_name']); ?></p>
                        </div>
                        <div class="name">
                            <h6>Name:</h6>
                            <p><?php echo htmlspecialchars($user['first_name'] . ' ' .$user['last_name']); ?>, <?php echo htmlspecialchars($user['professional_designation']); ?></p>
                        </div>
                        <div class="email">
                            <h6>Email:</h6>
                            <p><?php echo htmlspecialchars($user['user_email']); ?></p>
                        </div>
                        <div class="role">
                            <h6>Role:</h6>
                            <p><?php echo htmlspecialchars($user['role']); ?></p>
                        </div>
                    </div>
                </div>
                <div class="activity-log">
                    <div class="top">
                        <h3><i class="fa-solid fa-newspaper"></i> Activity Log</h3>
                        <div class="filter-box">
                            <h6 class="filter-label">Filter by:</h6>
                            <select class="form-select form-select-sm" aria-label="Filter by action">
                                <option value="" selected>All Actions</option>
                                <?php
                                if ($actionResult->num_rows > 0) {
                                    while ($actionRow = $actionResult->fetch_assoc()) {
                                        echo "<option value='" . htmlspecialchars($actionRow['action']) . "'>" 
                                            . htmlspecialchars($actionRow['action']) . "</option>";
                                    }
                                }
                                ?>
                            </select>
                            <input type="date" id="filterDate">
                            <select class="form-select form-select-sm" id="rowsFilter" aria-label="Number of rows">
                                <option value="5" <?php echo $rowsToShow == 5 ? 'selected' : ''; ?>>5 rows</option>
                                <option value="10" <?php echo $rowsToShow == 10 ? 'selected' : ''; ?>>10 rows</option>
                                <option value="25" <?php echo $rowsToShow == 25 ? 'selected' : ''; ?>>25 rows</option>
                                <option value="50" <?php echo $rowsToShow == 50 ? 'selected' : ''; ?>>50 rows</option>
                                <option value="100" <?php echo $rowsToShow == 100 ? 'selected' : ''; ?>>100 rows</option>
                            </select>
                            <button class="refresh-btn btn-danger" id="refreshBtn"><i class="fa-solid fa-arrows-rotate"></i></button>
                        </div>
                    </div>
                    <div class="log-table-wrapper">
                        <table class="custom-log-table">
                            <thead>
                                <tr>
                                    <th>Log ID</th>
                                    <th>Action</th>
                                    <th>Date-Time</th>
                                    <th>Delete</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($activityResult->num_rows > 0) {
                                    while ($row = $activityResult->fetch_assoc()) {
                                        echo "<tr>
                                                <td>{$row['log_ID']}</td>
                                                <td>{$row['action']}</td>
                                                <td>{$row['timestamp']}</td>
                                                <td>
                                                    <form action='../src/delete_log.php' method='POST' onsubmit=\"return confirm('Are you sure you want to delete this log?');\">
                                                        <input type='hidden' name='log_ID' value='{$row['log_ID']}'>
                                                        <button type='submit' class='btn btn-sm btn-danger' style='font-size:14px; padding:3px;'>
                                                            <i class='fa-solid fa-trash'></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='4'>No activity found</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="copyright">
                    <i class="fa-regular fa-copyright"></i> All Rights Reserved 2025
                </div>
            </div>
            <div class="right-panel">
                    <h2>Current Location</h2>
                    <div id="map"></div>
                    <div id="location-info">📍 Location: Detecting...</div>
            </div>
       </div>

        <div id="editProfileModal" class="modal">
            <div class="modal-content">
                <div class="modal-content-header">
                    <button type="button" class="cancel-btn" id="cancelBtn"><i class="fa-regular fa-circle-xmark"></i></button>
                    <h2>Edit Profile</h2>
                </div>
                <form id="editProfileForm" enctype="multipart/form-data">
                    
                    <!-- Profile image preview -->
                    <img id="profilePreview" class="profile-img" src="<?php echo htmlspecialchars($profileImage); ?>" alt="Profile Image">

                    <div class="form-group">
                        <label for="profile_image">Upload a Photo</label>
                        <input type="file" id="profile_image" name="profile_image" accept="image/*">
                    </div>

                    <div class="form-group">
                        <label for="user_name">Username:</label>
                        <input type="text" id="user_name" name="user_name" placeholder="Enter a Username" required>
                    </div>

                    <div class="form-group">
                        <label for="user_email">Email:</label>
                        <input type="text" id="user_email" name="user_email" placeholder="Enter your Email" required>
                    </div>

                    <div class="form-group">
                        <label for="first_name">First Name:</label>
                        <input type="text" id="first_name" name="first_name" placeholder="Enter your First Name" required>
                    </div>

                    <div class="form-group">
                        <label for="last_name">Last Name:</label>
                        <input type="text" id="last_name" name="last_name" placeholder="Enter your Last Name" required>
                    </div>

                    <div class="form-group">
                        <label for="professional_designation">Professional Designation:</label>
                        <input type="text" id="professional_designation" name="professional_designation" placeholder="Enter your Professional Designation (e.g. MIT, ENGR., MSIT, etc.)">
                    </div>

                    <div class="form-group">
                        <label for="role">Role:</label>
                        <input type="text" id="role" name="role" value="Supply Officer" readonly>
                    </div>

                    <div class="button-group">
                        <button type="submit" class="update-btn btn-outline-primary">Update Profile</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
    

    <script src="./js/update_profile.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script src="./js/map.js"></script>
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

        function showQuestion() {
        document.getElementById('password-form').classList.add('hidden');
        document.getElementById('question-form').classList.remove('hidden');
        }

        function showPassword() {
        document.getElementById('question-form').classList.add('hidden');
        document.getElementById('password-form').classList.remove('hidden');
        }
        
        // Activity log filter
        const actionFilter = document.querySelector(".filter-box select");
        const dateInput = document.getElementById("filterDate");
        const rowsFilter = document.getElementById("rowsFilter");
        const refreshBtn = document.getElementById("refreshBtn");
        const rows = document.querySelectorAll(".custom-log-table tbody tr");

        // Initialize: Hide rows beyond the default limit
        function initializeRowDisplay() {
            const maxRows = parseInt(rowsFilter.value);
            rows.forEach((row, index) => {
                if (index >= maxRows) {
                    row.style.display = "none";
                } else {
                    row.style.display = "";
                }
            });
        }

        // Initialize on page load
        initializeRowDisplay();

        // Function to filter table
        function filterTable() {
            const selectedAction = actionFilter.value.toLowerCase();
            const selectedDate = dateInput.value; // format: yyyy-mm-dd
            const maxRows = parseInt(rowsFilter.value);

            let visibleCount = 0;

            rows.forEach((row, index) => {
                const action = row.cells[1].textContent.toLowerCase();
                const timestamp = row.cells[2].textContent; // e.g. 2025-09-05 18:22:30
                const rowDate = timestamp.split(" ")[0];   // take only yyyy-mm-dd

                let show = true;

                // Check action filter
                if (selectedAction && !action.includes(selectedAction)) {
                    show = false;
                }

                // Check date filter
                if (selectedDate && rowDate !== selectedDate) {
                    show = false;
                }

                // Check rows limit - only apply if row passes other filters
                if (show && visibleCount >= maxRows) {
                    show = false;
                }

                if (show) {
                    visibleCount++;
                }

                row.style.display = show ? "" : "none";
            });
        }

        // Event listeners
        actionFilter.addEventListener("change", filterTable);
        dateInput.addEventListener("change", filterTable);
        rowsFilter.addEventListener("change", filterTable);

        refreshBtn.addEventListener("click", () => {
            // Rotate icon animation
            refreshBtn.classList.add("rotate");

            // Reset filters
            actionFilter.value = "";
            dateInput.value = "";
            rowsFilter.value = "10"; // Reset to default 10 rows

            // Apply the filter to show only 10 rows
            filterTable();

            // Stop rotation after animation ends
            setTimeout(() => {
                refreshBtn.classList.remove("rotate");
            }, 500);
        });

    </script>
</body>
</html>