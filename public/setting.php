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

// Fetch data from Security_Question
$sql = "SELECT question_ID, question_text FROM Security_Question";
$securityResult = $conn->query($sql);
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
    <link rel="stylesheet" href="./css/setting.css">

    <title>Inventory_Management_System-Settings_Page</title>
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
            <a href="inventories.php" class="nav-item"><i class="fa-solid fa-warehouse"></i> <span>Inventories</span></a>
            <a href="profile.php" class="nav-item"><i class="fa-solid fa-circle-user"></i> <span>Profile</span></a>
            <a href="setting.php" class="nav-item active"><i class="fa-solid fa-gear"></i> <span>Settings</span></a>
        </nav>
        <footer>
           <nav>
                <a href="../src/log_out.php" class="nav-item"><i class="bi bi-box-arrow-left"></i> <span>Log out</span></a>
           </nav>
        </footer>
    </aside>
    <main>
        <div class="title">
            <h1>Settings</h1>
        </div>
        
        <?php if (isset($_SESSION['message'])): ?>
            <?php
                $type = $_SESSION['message_type'] ?? "info";
                $swalIcon = [
                    "success" => "success",
                    "danger"  => "error",
                    "warning" => "warning",
                    "info"    => "info"
                ][$type];
            ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: '<?php echo $swalIcon; ?>',
                        title: '<?php echo ucfirst($type); ?>',
                        text: '<?php echo addslashes($_SESSION['message']); ?>',
                        confirmButtonColor: '#3085d6',
                        timer: 3000,
                        timerProgressBar: true
                    });
                });
            </script>
            <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
        <?php endif; ?>
        
        <div class="main-content">
            <div class="left-panel">
                <div class="security-card">
                    <div class="tabs">
                        <div class="tab active" data-tab="password"><i class="bi bi-file-lock2"></i> Password</div>
                        <div class="tab" data-tab="security_question"><i class="bi bi-shield-lock"></i> Security Question</div>
                    </div>
                    <!-- Password Content -->
                    <div id="password" class="tab-content active">
                        <h3><i class="bi bi-file-lock2-fill"></i> Password</h3>
                        <div id="password-rules" style="display: none; margin-left: 10px; font-size: 0.8rem;">
                            <p style="margin-bottom: 5px; font-weight: bold;">Password must contain:</p>
                            <ul style="list-style: none; padding-left: 0;display:flex;flex-direction:row;gap:5px;font-size: 0.7rem;">
                                <li id="length"><i class="fa-regular fa-circle-xmark"></i> At least 8 characters</li>
                                <li id="number"><i class="fa-regular fa-circle-xmark"></i> At least 1 number</li>
                                <li id="special"><i class="fa-regular fa-circle-xmark"></i> At least 1 special character</li>
                                <li id="match"><i class="fa-regular fa-circle-xmark"></i> Passwords must match</li>
                            </ul>
                        </div>
                        <div class="inputs">
                            <input type="password" name="current_password" class="current_password" id="currentPassword" placeholder="Current Password">
                            <input type="password" name="new_password" class="new_password" id="newPassword" placeholder="New Password">
                            <input type="password" name="confirm_password" class="confirm_password" id="confirmPassword" placeholder="Confirm New Password">
                            <span><input type="checkbox" id="showPasswords">Show Passwords</span>
                        </div>
                        <div class="btn-container">
                            <button class="btn-reset btn-outline-secondary">Reset</button>
                            <button class="btn-update btn-primary">Update Password</button>
                        </div>
                    </div>

                    <!-- Security Question Content -->
                    <div id="security_question" class="tab-content">
                        <h3><i class="bi bi-shield-lock-fill"></i> Security Question</h3>
                        <div class="inputs">
                            <select name="security_question" id="sequrity_question" required>
                                <option value="">Choose a Security Question</option>
                                <?php
                                $qres = $conn->query("SELECT question_ID, question_text FROM Security_Question");
                                while ($q = $qres->fetch_assoc()) {
                                    echo "<option value='{$q['question_ID']}'>{$q['question_text']}</option>";
                                }
                                ?>
                            </select>
                            <input type="password" name="security_answer" class="security_answer" id="security_answer" placeholder="Set Security Answer">
                            <span><input type="checkbox" id="showAnswer">Show Answer</span>
                        </div>
                        <div class="btn-container">
                            <button class="btn-reset btn-outline-secondary">Reset</button>
                            <button class="btn-update btn-primary">Update</button>
                        </div>
                    </div>
                </div>
                <div class="copyright">
                    <i class="fa-regular fa-copyright"></i> All Rights Reserved 2025
                </div>
            </div>
            <div class="right-panel">
                <div class="data-backup-card">
                    <!-- Data Backup Content -->
                        <h3>Backup your data in case of errors or unexpected loss.</h3>

                        <!-- Backup Form -->
                        <form action="backup.php" method="post">
                            <button type="submit" class="btn-backup btn-primary" name="backup">Create Backup</button>
                        </form>
                        <!-- Restore Form -->
                        <form action="restore.php" method="post">
                            <select name="backup_file" required>
                                <option value="">Select Backup File</option>
                                <?php
                                $files = glob(__DIR__ . "/backups/*.sql");
                                usort($files, function($a, $b) {
                                    return filemtime($b) - filemtime($a); // sort by latest first
                                });
                                foreach ($files as $file) {
                                    $filename = basename($file);
                                    $date = date("Y-m-d H:i:s", filemtime($file));
                                    echo "<option value=\"$filename\">$filename (created $date)</option>";
                                }
                                ?>
                            </select>
                            <button type="submit" class="btn-restore btn-secondary" name="restore">Restore Backup</button>
                        </form>
                </div>
            </div>
        </div>
    </main>


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

    // Tab switching
    const tabs = document.querySelectorAll(".tab");
    const contents = document.querySelectorAll(".tab-content");

    tabs.forEach(tab => {
      tab.addEventListener("click", () => {
        tabs.forEach(t => t.classList.remove("active"));
        contents.forEach(c => c.classList.remove("active"));

        tab.classList.add("active");
        document.getElementById(tab.dataset.tab).classList.add("active");
      });
    });

    // Show/Hide Passwords
    document.getElementById("showPasswords").addEventListener("change", function() {
        const fields = ["currentPassword", "newPassword", "confirmPassword"];
        fields.forEach(id => {
            document.getElementById(id).type = this.checked ? "text" : "password";
        });
    });

    // Show/Hide Security Answer
    document.getElementById("showAnswer").addEventListener("change", function() {
        document.getElementById("security_answer").type = this.checked ? "text" : "password";
    });

    // Reset buttons
    document.querySelectorAll(".btn-reset").forEach(btn => {
        btn.addEventListener("click", function () {
            this.closest(".tab-content").querySelectorAll("input").forEach(i => i.value = "");
        });
    });

    // Update Password
    document.querySelector("#password .btn-update").addEventListener("click", function () {
        const current = document.getElementById("currentPassword").value.trim();
        const newPass = document.getElementById("newPassword").value.trim();
        const confirm = document.getElementById("confirmPassword").value.trim();

        // Password policy: At least 8 chars, 1 number, 1 special char
        const password_policy = /^(?=.*[0-9])(?=.*[!@#$%^&*(),.?":{}|<>]).{8,}$/;

        if (!current || !newPass || !confirm) {
            showSwalAlert("All password fields are required.", "error");
            return;
        }
        if (newPass !== confirm) {
            showSwalAlert("New password and confirmation do not match.", "error");
            return;
        }
        if (!password_policy.test(newPass)) {
            showSwalAlert("Password must be at least 8 characters long and include at least one number and one special character.", "error");
            return;
        }

        fetch("../src/update_password.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ current_password: current, new_password: newPass })
        })
        .then(res => res.json())
        .then(data => {
            showSwalAlert(data.message, data.status === "success" ? "success" : "error");

            if (data.status === "success") {
                document.getElementById("currentPassword").value = "";
                document.getElementById("newPassword").value = "";
                document.getElementById("confirmPassword").value = "";
                document.getElementById("showPasswords").checked = false;
                ["currentPassword","newPassword","confirmPassword"].forEach(id => {
                    document.getElementById(id).type = "password";
                });
            }
        });
    });

    // Real-time password validation for settings page
    const newPassword = document.getElementById("newPassword");
    const confirmPassword = document.getElementById("confirmPassword");
    const passwordRules = document.getElementById("password-rules");

    function updateRule(ruleElement, condition, text) {
        if (condition) {
            ruleElement.innerHTML = `<i class="fa-regular fa-circle-check" style="color:green;"></i> ${text}`;
        } else {
            ruleElement.innerHTML = `<i class="fa-regular fa-circle-xmark" style="color:red;"></i> ${text}`;
        }
    }

    function checkPasswordPolicy() {
        const value = newPassword.value;

        updateRule(
            document.getElementById("length"),
            value.length >= 8,
            "At least 8 characters"
        );

        updateRule(
            document.getElementById("number"),
            /\d/.test(value),
            "At least 1 number"
        );

        updateRule(
            document.getElementById("special"),
            /[!@#$%^&*(),.?":{}|<>]/.test(value),
            "At least 1 special character"
        );

        updateRule(
            document.getElementById("match"),
            value && value === confirmPassword.value,
            "Passwords must match"
        );
    }

    // Show password rules when user focuses on new password field
    newPassword.addEventListener("focus", function() {
        passwordRules.style.display = "block";
    });

    newPassword.addEventListener("input", checkPasswordPolicy);
    confirmPassword.addEventListener("input", checkPasswordPolicy);

    // Update Security Question
    document.querySelector("#security_question .btn-update").addEventListener("click", function () {
        const question = document.getElementById("sequrity_question").value;
        const answer = document.getElementById("security_answer").value.trim();

        if (!question || !answer) {
            Swal.fire({
                icon: 'warning',
                title: 'Missing Information',
                text: 'Select a question and provide an answer.',
                confirmButtonColor: '#dc3545'
            });
            return;
        }

        fetch("../src/update_security_question.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ question_id: question, answer: answer })
        })
        .then(res => res.json())
        .then(data => {
            showSwalAlert(data.message, data.status === "success" ? "success" : "error");

            if (data.status === "success") {
                document.getElementById("sequrity_question").value = "";
                document.getElementById("security_answer").value = "";
                document.getElementById("showAnswer").checked = false;
                document.getElementById("security_answer").type = "password";
            }
        });
    });

    function showSwalAlert(message, icon = "success") {
        Swal.fire({
            icon: icon,
            title: icon === "success" ? "Success" : "Error",
            text: message,
            confirmButtonColor: '#3085d6',
            timer: 3000,
            timerProgressBar: true
        });
    }
    </script>

</body>
</html>