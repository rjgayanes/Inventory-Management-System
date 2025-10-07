<?php
session_start();
include '../src/db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_ID']) || $_SESSION['role'] !== 'Supply Officer') {
    header("Location: login.php?error=Access+denied");
    exit();
}

$user_ID = $_SESSION['user_ID'];

// ================== Add Task Handler ================== //
function add_task(mysqli $conn, int $userId, string $classification, string $title, ?string $description, string $status, ?string $dueDate, ?string $dueTime): array {
    $allowedClassifications = ['Notes', 'Reminders', 'Checklist'];
    $allowedStatus = ['Pending', 'Completed', 'Cancelled'];

    if (!in_array($classification, $allowedClassifications, true)) {
        return ['success' => false, 'error' => 'Invalid classification'];
    }
    if (!in_array($status, $allowedStatus, true)) {
        return ['success' => false, 'error' => 'Invalid status'];
    }
    if (trim($title) === '') {
        return ['success' => false, 'error' => 'Title is required'];
    }

    // Normalize empty strings to nulls for optional fields
    $description = ($description !== null && trim($description) === '') ? null : $description;
    $dueDate = ($dueDate !== null && trim($dueDate) === '') ? null : $dueDate;
    $dueTime = ($dueTime !== null && trim($dueTime) === '') ? null : $dueTime;

    $sql = "INSERT INTO Task_Manager (user_ID, classification, title, description, status, due_date, due_time) VALUES (?,?,?,?,?,?,?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return ['success' => false, 'error' => 'Prepare failed: ' . $conn->error];
    }

    // Types: i s s s s s s (due_date and due_time can be null)
    $stmt->bind_param(
        'issssss',
        $userId,
        $classification,
        $title,
        $description,
        $status,
        $dueDate,
        $dueTime
    );

    if (!$stmt->execute()) {
        $err = $stmt->error;
        $stmt->close();
        return ['success' => false, 'error' => 'Execute failed: ' . $err];
    }
    $newId = $conn->insert_id;
    $stmt->close();
    return ['success' => true, 'insert_id' => $newId];
}

// Handle POST from Add Task form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_task'])) {
    $classification = isset($_POST['classification']) ? trim($_POST['classification']) : 'Notes';
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : null;
    // Status defaults to Pending on creation
    $status = 'Pending';
    $dueDate = isset($_POST['due_date']) ? trim($_POST['due_date']) : null;
    $dueTime = isset($_POST['due_time']) ? trim($_POST['due_time']) : null;

    $result = add_task($conn, (int)$user_ID, $classification, $title, $description, $status, $dueDate, $dueTime);
    $wantsJson = false;
    if (isset($_SERVER['HTTP_ACCEPT'])) {
        $wantsJson = stripos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;
    }
    // If fetch requested JSON, return JSON payload
    if ($wantsJson) {
        header('Content-Type: application/json');
        if ($result['success']) {
            echo json_encode(['success' => true, 'message' => 'Task added successfully', 'task_id' => (int)$result['insert_id']]);
        } else {
            echo json_encode(['success' => false, 'error' => $result['error']]);
        }
        exit();
    }
    // Fallback for non-AJAX
    if ($result['success']) {
        header('Location: dashboard.php');
        exit();
    } else {
        $taskAddError = $result['error'];
    }
}

// Handle status update via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_task_status'])) {
    header('Content-Type: application/json');
    $taskId = isset($_POST['task_id']) ? (int)$_POST['task_id'] : 0;
    $newStatus = isset($_POST['status']) ? trim($_POST['status']) : '';
    $allowedStatus = ['Pending','Completed','Cancelled'];
    if ($taskId <= 0 || !in_array($newStatus, $allowedStatus, true)) {
        echo json_encode(['success' => false, 'error' => 'Invalid parameters']);
        exit();
    }
    $upd = $conn->prepare("UPDATE Task_Manager SET status = ? WHERE task_ID = ? AND user_ID = ?");
    if (!$upd) {
        echo json_encode(['success' => false, 'error' => 'Prepare failed: '.$conn->error]);
        exit();
    }
    $upd->bind_param('sii', $newStatus, $taskId, $user_ID);
    if (!$upd->execute()) {
        $err = $upd->error;
        $upd->close();
        echo json_encode(['success' => false, 'error' => 'Execute failed: '.$err]);
        exit();
    }
    $upd->close();
    echo json_encode(['success' => true]);
    exit();
}

// Handle delete task via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_task'])) {
    header('Content-Type: application/json');
    $taskId = isset($_POST['task_id']) ? (int)$_POST['task_id'] : 0;
    if ($taskId <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid task id']);
        exit();
    }
    $del = $conn->prepare("DELETE FROM Task_Manager WHERE task_ID = ? AND user_ID = ?");
    if (!$del) {
        echo json_encode(['success' => false, 'error' => 'Prepare failed: '.$conn->error]);
        exit();
    }
    $del->bind_param('ii', $taskId, $user_ID);
    if (!$del->execute()) {
        $err = $del->error;
        $del->close();
        echo json_encode(['success' => false, 'error' => 'Execute failed: '.$err]);
        exit();
    }
    $del->close();
    echo json_encode(['success' => true]);
    exit();
}

// Handle update title/description via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_task_fields'])) {
    header('Content-Type: application/json');
    $taskId = isset($_POST['task_id']) ? (int)$_POST['task_id'] : 0;
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $desc = isset($_POST['description']) ? trim($_POST['description']) : null;
    if ($taskId <= 0 || $title === '') {
        echo json_encode(['success' => false, 'error' => 'Invalid parameters']);
        exit();
    }
    $upd = $conn->prepare("UPDATE Task_Manager SET title = ?, description = ? WHERE task_ID = ? AND user_ID = ?");
    if (!$upd) {
        echo json_encode(['success' => false, 'error' => 'Prepare failed: '.$conn->error]);
        exit();
    }
    $upd->bind_param('ssii', $title, $desc, $taskId, $user_ID);
    if (!$upd->execute()) {
        $err = $upd->error;
        $upd->close();
        echo json_encode(['success' => false, 'error' => 'Execute failed: '.$err]);
        exit();
    }
    $upd->close();
    echo json_encode(['success' => true]);
    exit();
}

// Fetch user details
$sql = "SELECT user_name, user_email, password, first_name, last_name, professional_designation, role, profile_image 
        FROM Users WHERE user_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_ID);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Set profile image
if (!empty($user['profile_image'])) {
    $profileImage = $user['profile_image']; 
} else {
    $profileImage = '/uploads/profile_images/default_profile.jpg';
}

// Fund filter (optional, from GET param)
$fundFilter = $_GET['fund'] ?? null;
$params = [];
$types  = "";

// Month/year range filter (from input[type=month])
$startMonth = $_GET['start_month'] ?? date('Y-m'); // default: current month
$endMonth   = $_GET['end_month'] ?? date('Y-m');   // default: current month

$startDate = $startMonth . "-01";
$endDate   = date("Y-m-t", strtotime($endMonth . "-01")); // last day of end month

$params = [];
$types  = "";

// ================== Total Items ================== //
$sqlItems = "SELECT COUNT(*) AS total_items FROM Items i WHERE i.acquisition_date BETWEEN ? AND ?";
$params[] = $startDate;
$params[] = $endDate;
$types   .= "ss";

if ($fundFilter) {
    $sqlItems .= " AND i.fund_ID = (SELECT fund_ID FROM Fund_sources WHERE fund_name = ?)";
    $params[] = $fundFilter;
    $types   .= "s";
}
$stmt = $conn->prepare($sqlItems);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$totalItems = $row['total_items'] ?? 0;
$stmt->close();

// ================== Total Amount ================== //
$sqlAmount = "SELECT SUM(i.unit_total_cost) AS total_amount FROM Items i WHERE i.acquisition_date BETWEEN ? AND ?";
$params = [$startDate, $endDate];
$types  = "ss";

if ($fundFilter) {
    $sqlAmount .= " AND i.fund_ID = (SELECT fund_ID FROM Fund_sources WHERE fund_name = ?)";
    $params[] = $fundFilter;
    $types   .= "s";
}
$stmt = $conn->prepare($sqlAmount);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$totalAmount = $row['total_amount'] ?? 0;
$stmt->close();

$totalAmountFormatted = number_format($totalAmount, 2);

// ================== Modal Data ================== //
$sql = "SELECT f.fund_name, et.type_name, i.item_classification,
               SUM(i.unit_total_cost) AS total_amount
        FROM Items i
        JOIN Fund_sources f ON i.fund_ID = f.fund_ID
        JOIN Equipment_types et ON i.type_ID = et.type_ID
        GROUP BY f.fund_name, et.type_name, i.item_classification
        ORDER BY f.fund_name, et.type_name, i.item_classification";

$result = $conn->query($sql);

$fundData = [];
$grandTotal = 0;
while ($row = $result->fetch_assoc()) {
    $fund = $row['fund_name'];
    $type = $row['type_name'];
    $classification = $row['item_classification'];
    $amount = $row['total_amount'] ?? 0;

    if (!isset($fundData[$fund])) {
        $fundData[$fund] = ['types' => [], 'total' => 0, 'classifications' => []];
    }
    
    // Store classification data
    if (!isset($fundData[$fund]['classifications'][$classification])) {
        $fundData[$fund]['classifications'][$classification] = ['types' => [], 'total' => 0];
    }
    
    $fundData[$fund]['types'][$type] = $amount;
    $fundData[$fund]['classifications'][$classification]['types'][$type] = $amount;
    $fundData[$fund]['classifications'][$classification]['total'] += $amount;
    $fundData[$fund]['total'] += $amount;
    $grandTotal += $amount;
}
?>

<?php
// ================== Load Tasks ================== //
$tasks = [];
$taskQuery = "SELECT task_ID, classification, title, description, status, due_date, due_time, created_time
              FROM Task_Manager WHERE user_ID = ? ORDER BY created_time DESC";
$taskStmt = $conn->prepare($taskQuery);
if ($taskStmt) {
    $taskStmt->bind_param('i', $user_ID);
    $taskStmt->execute();
    $taskRes = $taskStmt->get_result();
    while ($trow = $taskRes->fetch_assoc()) {
        $tasks[] = $trow;
    }
    $taskStmt->close();
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

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <link rel="stylesheet" href="./css/major.css">
    <link rel="stylesheet" href="./css/dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <title>Inventory_Management_System-Dashboard_Page</title>

     <style>

        .modal-title {
            font-size: 18px;
            color: #121111;
            font-weight: 900;
        }
        .btn-close {
            cursor: pointer;
            font-size: 12px;
            font-weight: 900;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table th, table td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }
        table td.amount {
            text-align: right;
        }
        .total-row {
            font-weight: bold;
        }

        .dnwld {
            font-size: 14px;
            font-weight: 700;
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
            <a href="dashboard.php" class="nav-item active"><i class="fa-solid fa-house-chimney"></i> <span>Dashboard</span></a>
            <a href="barcode_scanner.php" class="nav-item"><i class="fa-solid fa-barcode"></i> <span>Scanner</span></a>
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
        <div class="title">
            <h1>Dashboard</h1>
            <p>Hello <?php echo htmlspecialchars($user['first_name']); ?>, Welcome!!!</p>
        </div>
        <div class="main-content">
            <div class="left-panel">
                <div class="amount-card">
                    <div class="top">
                        <h6>Total Amount</h6>
                        <button data-bs-toggle="modal" data-bs-target="#staticBackdrop"><i class="bi bi-arrows-fullscreen"></i></button>
                    </div>
                    <div class="mid">
                        <h1><i class="fa-solid fa-peso-sign"></i><?php echo $totalAmountFormatted; ?> <i class="fa-solid fa-boxes-packing"></i></h1>
                        <h6><i class="fa-solid fa-boxes-stacked"></i> <?php echo $totalItems; ?> Items</h6>
                    </div>
                    <div class="bot">
                        <div class="date-range">
                            <h6>Month - Year</h6>
                            <form method="GET" id="filterForm" style="display:flex; gap:5px; align-items:center;">
                                <input type="month" name="start_month" id="start_month" 
                                    value="<?php echo htmlspecialchars($startMonth); ?>"
                                    onchange="document.getElementById('filterForm').submit()">

                                <span> - </span>

                                <input type="month" name="end_month" id="end_month" 
                                    value="<?php echo htmlspecialchars($endMonth); ?>"
                                    onchange="document.getElementById('filterForm').submit()">
                            </form>

                        </div>
                        <div class="holder">
                            <h6>Viewer</h6>
                            <h5><?php echo htmlspecialchars($user['last_name']); ?>, <?php echo htmlspecialchars($user['first_name']); ?></h5>
                        </div>
                    </div>
                </div>
                <div class="pie-chart">
                    <h2>Inventory Distribution by Fund Source</h2>
                    <div id="chartContainer">
                        <canvas id="fundChart"></canvas>
                    </div>
                </div>
                <div class="copyright">
                    <i class="fa-regular fa-copyright"></i> All Rights Reserved 2025
                </div>
            </div>
            <div class="right-panel">
                <div class="task-manager">
                    <h1>Task Manager</h1>
                    <?php if (isset($taskAddError)): ?>
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: '<?php echo addslashes($taskAddError); ?>',
                                    toast: true,
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 5000,
                                    timerProgressBar: true
                                });
                            });
                        </script>
                    <?php endif; ?>
                    <div class="search-filter">
                        <div class="search-task">
                            <input type="text" id="taskSearch" placeholder="Search tasks...">
                            <span><i class="fa-solid fa-magnifying-glass"></i></span>
                        </div>
                        <div class="task-filter">
                            <select id="taskFilter" class="form-select form-select-sm" aria-label=".form-select-sm">
                                <option selected>All</option>
                                <option value="Notes">Notes</option>
                                <option value="Reminders">Reminders</option>
                                <option value="Checklist">Checklists</option>
                            </select>
                        </div>
                        <div class="task-status-filter">
                            <select id="taskStatus" class="form-select form-select-sm" aria-label=".form-select-sm">
                                <option selected>Task Status</option>
                                <option value="Pending">Pending</option>
                                <option value="Completed">Completed</option>
                                <option value="Cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>
                    <div class="task-list">
                        <?php if (empty($tasks)): ?>
                        <div class="no-tasks">
                                <p>No tasks yet</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($tasks as $task): ?>
                                <?php
                                    $isUrgent = false;
                                    if (!empty($task['due_date']) && in_array($task['classification'], ['Reminders','Checklist'])) {
                                        $dueDateTime = $task['due_date'] . (!empty($task['due_time']) ? (' ' . $task['due_time']) : ' 00:00:00');
                                        $diffMinutes = (strtotime($dueDateTime) - time()) / 60;
                                        $isUrgent = $diffMinutes >= 0 && $diffMinutes <= 10;
                                    }
                                    $statusLower = strtolower($task['status']);
                                    // Pick icon per status
                                    $statusIcon = '<i class="bi bi-three-dots"></i>';
                                    if ($statusLower === 'completed') { $statusIcon = '<i class="bi bi-check-circle-fill"></i>'; }
                                    if ($statusLower === 'cancelled') { $statusIcon = '<i class="bi bi-x-circle"></i>'; }
                                    // Pick icon per classification
                                    $classIcon = '<i class="bi bi-journal-text"></i>';
                                    if ($task['classification'] === 'Reminders') { $classIcon = '<i class="bi bi-alarm"></i>'; }
                                    if ($task['classification'] === 'Checklist') { $classIcon = '<i class="bi bi-list-check"></i>'; }
                                ?>
                                <div class="tm-card status-<?php echo $statusLower; ?>" data-task-id="<?php echo (int)$task['task_ID']; ?>"
                                data-due-date="<?php echo htmlspecialchars($task['due_date'] ?? ''); ?>"
                                data-due-time="<?php echo htmlspecialchars($task['due_time'] ?? ''); ?>">
                                    <div class="tm-card-top">
                                        <h3><?php echo htmlspecialchars($task['title']); ?></h3>
                                        <div class="d-flex align-items-center" style="gap:10px;">
                                            <button type="button" class="tm-edit btn btn-link p-0 m-0" title="Edit" style="text-decoration:none; color: inherit;"><i class="bi bi-pencil-square"></i></button>
                                            <button type="button" class="tm-delete btn btn-link p-0 m-0" title="Delete" style="text-decoration:none; color: inherit;"><i class="bi bi-trash"></i></button>
                                            <button type="button" class="tm-status btn btn-link p-0 m-0" data-status="<?php echo htmlspecialchars($task['status']); ?>" style="text-decoration:none; color: inherit;font-size: 14px;">
                                                <?php echo $statusIcon; ?> <?php echo htmlspecialchars($task['status']); ?>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="tm-card-bottom">
                                        <span class="tm-date"><i class="bi bi-calendar3"></i> <?php echo !empty($task['due_date']) ? date('m/d/Y', strtotime($task['due_date'])) : 'No due date'; ?></span>
                                        <span class="tm-dot">•</span>
                                        <span class="tm-class"><?php echo $classIcon; ?> <?php echo htmlspecialchars($task['classification']); ?></span>
                                    </div>
                                    <div class="tm-details" style="display:none;">
                                        <div class="tm-desc-label">Description:</div>
                                        <div class="tm-desc-text"><?php echo htmlspecialchars($task['description'] ?? 'No description provided'); ?></div>
                        </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <div class="add-task">
                            <button class="add-task-btn btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">
                                Add Task
                            </button>
            
                        <div class="collapse" id="collapseExample">
                            <div class="card card-body">
                                <form method="POST" id="addTaskForm">
                                    <input type="hidden" name="add_task" value="1">
                                    <input type="hidden" name="classification" id="classificationInput" value="Notes">

                                <div class="task-input-header">
                                        <input type="text" class="task-title-input" name="title" placeholder="Title" required>
                                        <button class="save-task-btn" type="submit" title="Save Task">
                                        <i class="bi bi-file-earmark-plus"></i>
                                    </button>
                                </div>

                                    <textarea class="task-description-input" name="description" placeholder="Write something down..."></textarea>

                                    <div class="row g-2 mt-2">
                                        <div class="col-12">
                                            <div class="classification-buttons d-flex gap-2">
                                                <div class="task-class-btn">
                                                    <button type="button" id="notes" class="btn btn-outline-primary btn-sm classification-btn active" data-classification="Notes">
                                                        <i class="bi bi-journal-text"></i>
                                                    </button>
                                                    <label for="notes">Notes</label>
                                                </div>
                                                <div class="task-class-btn">
                                                    <button type="button" class="btn btn-outline-primary btn-sm classification-btn" data-classification="Reminders">
                                                        <i class="bi bi-alarm"></i>
                                                    </button>
                                                    <label for="reminders">Reminders</label>
                                                </div>
                                                <div class="task-class-btn">
                                                    <button type="button" class="btn btn-outline-primary btn-sm classification-btn" data-classification="Checklist">
                                                        <i class="bi bi-list-check"></i>
                                                    </button>
                                                    <label for="checklist">Checklist</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="task-time-inputs" style="display: none; gap: 10px; margin-top: 10px;">
                                    <div class="time-input-group">
                                        <label>Due Date:</label>
                                            <input type="date" class="task-due-date-input" name="due_date">
                                    </div>
                                    <div class="time-input-group">
                                        <label>Due Time:</label>
                                            <input type="time" class="task-due-time-input" name="due_time">
                                    </div>
                                </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    
    <!-- Simplified Modal with AJAX approach -->
    <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="staticBackdropLabel">Inventory Summary</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
                    <!-- Classification Filter -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="classificationFilter" class="form-label">Filter by Classification:</label>
                            <select class="form-select" id="classificationFilter">
                                <option value="all">All Classifications</option>
                                <option value="Property Plant and Equipment">Property Plant and Equipment</option>
                                <option value="Semi-Expendable Property">Semi-Expendable Property</option>
                            </select>
                        </div>
                    </div>

                    <div id="summaryTable">
                        <!-- Table will be loaded via AJAX -->
                        <div class="text-center">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
        </div>
        <div class="modal-footer">
            <span class="dnwld">Download:</span>
                    <button type="button" class="btn btn-outline-success btn-sm" id="exportExcel"><i class="fa-regular fa-file-excel"></i> Excel File</button>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="exportPDF"><i class="fa-regular fa-file-pdf"></i> PDF</button>
        </div>
        </div>
    </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="./js/pie-chart.js"></script>
    <script src="./js/task-manager.js"></script>
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
        
    // Enhanced live search and filter logic
    document.addEventListener('DOMContentLoaded', function() {
        const taskSearch = document.getElementById('taskSearch');
        const taskFilter = document.getElementById('taskFilter');
        const taskStatus = document.getElementById('taskStatus');
        const taskCards = document.querySelectorAll('.tm-card');
        
        // Enhanced filter function with better performance
        function filterTasks() {
            const searchTerm = taskSearch.value.toLowerCase().trim();
            const classificationFilter = taskFilter.value;
            const statusFilter = taskStatus.value;
            let hasVisibleTasks = false;
            
            taskCards.forEach(card => {
                const title = card.querySelector('h3').textContent.toLowerCase();
                const description = card.querySelector('.tm-desc-text')?.textContent.toLowerCase() || '';
                const classification = card.querySelector('.tm-class').textContent.toLowerCase();
                const status = card.querySelector('.tm-status').getAttribute('data-status').toLowerCase();
                
                // Check if task matches search criteria
                const matchesSearch = searchTerm === '' || 
                                    title.includes(searchTerm) || 
                                    description.includes(searchTerm);
                
                // Check if task matches classification filter
                const matchesClassification = classificationFilter === 'All' || 
                                            classification.includes(classificationFilter.toLowerCase());
                
                // Check if task matches status filter
                const matchesStatus = statusFilter === 'Task Status' || 
                                    status === statusFilter.toLowerCase();
                
                // Show or hide card based on all filters
                const shouldShow = matchesSearch && matchesClassification && matchesStatus;
                card.style.display = shouldShow ? 'block' : 'none';
                
                if (shouldShow) {
                    hasVisibleTasks = true;
                }
            });
            
            // Handle "No tasks" message
            updateNoTasksMessage(hasVisibleTasks);
        }
        
        // Function to update "No tasks" message
        function updateNoTasksMessage(hasVisibleTasks) {
            const taskList = document.querySelector('.task-list');
            let noTasksElement = document.querySelector('.no-tasks');
            
            if (!hasVisibleTasks) {
                if (!noTasksElement) {
                    noTasksElement = document.createElement('div');
                    noTasksElement.className = 'no-tasks';
                    noTasksElement.innerHTML = '<p>No tasks match your search criteria</p>';
                    taskList.appendChild(noTasksElement);
                }
            } else if (noTasksElement) {
                noTasksElement.remove();
            }
        }
        
        // Function to reset all filters
        function resetFilters() {
            taskSearch.value = '';
            taskFilter.value = 'All';
            taskStatus.value = 'Task Status';
            filterTasks();
        }
        
        // Event listeners for live filtering with debouncing for search
        let searchTimeout;
        taskSearch.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(filterTasks, 300); // 300ms delay
        });
        
        taskFilter.addEventListener('change', filterTasks);
        taskStatus.addEventListener('change', filterTasks);
        
        // Add reset functionality (optional - you can add a reset button if needed)
        function addResetButton() {
            const searchContainer = document.querySelector('.search-task');
            const resetBtn = document.createElement('button');
            resetBtn.type = 'button';
            resetBtn.className = 'btn btn-sm btn-outline-secondary ms-2';
            resetBtn.innerHTML = '<i class="bi bi-arrow-clockwise"></i>';
            resetBtn.title = 'Reset filters';
            resetBtn.addEventListener('click', resetFilters);
            
            searchContainer.appendChild(resetBtn);
        }
        
        // Initialize filters
        filterTasks();
        
        // Function to refresh task list after AJAX operations (add/delete/update)
        window.refreshTaskFilters = function() {
            // Re-select task cards in case new ones were added
            const newTaskCards = document.querySelectorAll('.tm-card');
            if (newTaskCards.length !== taskCards.length) {
                // If task count changed, we need to update our reference
                taskCards.forEach(card => card.style.display = 'block');
                // The next filter call will use the newly selected cards
            }
            filterTasks();
        };
    });

    // handle the classification filter logic
    document.addEventListener('DOMContentLoaded', function() {
        const classificationFilter = document.getElementById('classificationFilter');
        const summaryTable = document.getElementById('summaryTable');

        function loadSummaryData(classification = 'all') {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', `../src/get_inventory_summary.php?classification=${encodeURIComponent(classification)}`, true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    summaryTable.innerHTML = xhr.responseText;
                }
            };
            xhr.send();
        }

        // Load initial data
        loadSummaryData();

        if (classificationFilter) {
            classificationFilter.addEventListener('change', function() {
                loadSummaryData(this.value);
            });
        }

        // Handle export buttons
        document.getElementById('exportExcel')?.addEventListener('click', function() {
            const classification = classificationFilter?.value || 'all';
            window.open(`export_inventory_excel.php?classification=${encodeURIComponent(classification)}`, '_blank');
        });

        document.getElementById('exportPDF')?.addEventListener('click', function() {
            const classification = classificationFilter?.value || 'all';
            window.open(`export_inventory_pdf.php?classification=${encodeURIComponent(classification)}`, '_blank');
        });
    });
    </script>


</body>
</html>