<?php
session_start();
include '../src/db.php'; 

// Auto-login if remember_token cookie exists and session not set
if (!isset($_SESSION['user_ID']) && isset($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];
    $stmt = $conn->prepare("SELECT user_ID, user_name, user_email, password, first_name, last_name, professional_designation, role, profile_image 
                            FROM Users WHERE remember_token = ? LIMIT 1");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $_SESSION['user_ID'] = $row['user_ID'];
        $_SESSION['full_name'] = $row['first_name'] . ' ' . $row['last_name'];
        $_SESSION['role'] = $row['role'];
        $_SESSION['professional_designation'] = $row['professional_designation'];
        $_SESSION['profile_image'] = $row['profile_image'];

        header("Location: dashboard.php");
        exit();
    }
    $stmt->close();
}

$error = "";
$success = "";
$reset_step = 1; // default: ask username
$security_question = "";
$user_id = null;

// === LOGIN PROCESS ===
if (isset($_POST['action']) && $_POST['action'] === "login") {
    $user_name = trim($_POST['user_name']);
    $password = trim($_POST['password']);
    $remember = isset($_POST['remember']); // check if "remember me" is ticked

    $stmt = $conn->prepare("SELECT user_ID, user_name, password, first_name, last_name, professional_designation, role, profile_image 
                            FROM Users WHERE user_name = ?");
    $stmt->bind_param("s", $user_name);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            // set session
            $_SESSION['user_ID'] = $row['user_ID'];
            $_SESSION['user_name'] = $row['user_name'];
            $_SESSION['full_name'] = $row['first_name'] . ' ' . $row['last_name'];
            $_SESSION['professional_designation'] = $row['professional_designation'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['profile_image'] = $row['profile_image'];

            if ($remember) {
                // safer: store a random token instead of raw user_id
                $token = bin2hex(random_bytes(16));
                setcookie("remember_token", $token, time() + (86400 * 30), "/", "", false, true);

                // save token in DB for validation later
                $update = $conn->prepare("UPDATE Users SET remember_token = ? WHERE user_ID = ?");
                $update->bind_param("si", $token, $row['user_ID']);
                $update->execute();
                $update->close();
            }

            $log = $conn->prepare("INSERT INTO Activity_log (action, user_ID) VALUES (?, ?)");
            $action = "User logged in";
            $log->bind_param("si", $action, $row['user_ID']);
            $log->execute();
            $log->close();

            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid username or password.";
        }
    } else {
        $error = "Invalid username or password.";
    }
    $stmt->close();
}

// === RESET STEP 1: ENTER USERNAME ===
if (isset($_POST['action']) && $_POST['action'] === "reset_username") {
    $user_name = trim($_POST['user_name']);
    $stmt = $conn->prepare("SELECT u.user_ID, sq.question_text
        FROM Users u
        INNER JOIN User_Security_Answer usa ON u.user_ID = usa.user_ID
        INNER JOIN Security_Question sq ON usa.question_ID = sq.question_ID
        WHERE u.user_name = ?
        LIMIT 1
    ");
    $stmt->bind_param("s", $user_name);
    $stmt->execute();
    $stmt->bind_result($user_id, $security_question);
    if ($stmt->fetch()) {
        $reset_step = 2; // show question
        $_SESSION['reset_user_id'] = $user_id;
        $_SESSION['reset_security_question'] = $security_question; 
    } else {
        $error = "No security question found for this account.";
        $reset_step = 1;
    }
    $stmt->close();
}

// === RESET STEP 2: VERIFY ANSWER ===
if (isset($_POST['action']) && $_POST['action'] === "reset_answer") {
    $user_id = $_SESSION['reset_user_id'] ?? null;
    $security_question = $_SESSION['reset_security_question'] ?? ""; 
    $answer = trim($_POST['answer']);

    if ($user_id) {
        $stmt = $conn->prepare("SELECT answer_hash FROM User_Security_Answer WHERE user_ID = ? LIMIT 1");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($answer_hash);
        $stmt->fetch();
        $stmt->close();

        if ($answer_hash && password_verify($answer, $answer_hash)) {
            $reset_step = 3; // allow new password
        } else {
            $error = "Incorrect answer.";
            $reset_step = 2; 
        }
    }
}

// === RESET STEP 3: SET NEW PASSWORD ===
if (isset($_POST['action']) && $_POST['action'] === "reset_password") {
    $user_id = $_SESSION['reset_user_id'] ?? null;
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    //  Password policy: At least 8 chars, 1 number, 1 special char
    $password_policy = '/^(?=.*[0-9])(?=.*[!@#$%^&*(),.?":{}|<>]).{8,}$/';

    if ($new_password === $confirm_password && preg_match($password_policy, $new_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE Users SET password = ? WHERE user_ID = ?");
        $stmt->bind_param("si", $hashed_password, $user_id);
        if ($stmt->execute()) {
            unset($_SESSION['reset_user_id']);
            unset($_SESSION['reset_security_question']);
            $success = "Password reset successful! You can now log in.";
            $reset_step = 1;
        } else {
            $error = "Error updating password.";
            $reset_step = 3;
        }
        $stmt->close();
    } else {
        $error = "Password must be at least 8 characters long, include a number, a special character, and both fields must match.";
        $reset_step = 3;
    }
}

$conn->close();

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
    <link rel="stylesheet" href="./css/login.css">

    <title>Inventory_Management_System-Login_Page</title>
  
</head>
<body>
    <header>
        <img src="./image/IMS_logo2.png" alt="IMSLogo" class="IMS-logo">
    </header>
    <main>
        <div class="left-panel">
            <!-- LOGIN FORM -->
            <div id="login-form" <?php if ($reset_step > 1) echo 'class="hidden"'; ?>>
                <h2>Log in</h2>
        
                <form method="POST" novalidate>
                    <input type="hidden" name="action" value="login">
                    <input class="username" type="text" name="user_name" placeholder="" required>
                    <label id="for-username" for="user_name">Enter your Username</label>
                    <div class="password-container">
                        <input class="password" type="password" name="password" placeholder="" required>
                        <label id="for-password" for="password">Enter your Password</label>
                        <span><i class="fa-solid fa-eye"></i></span>
                    </div>
                    <div class="login-remember">
                        <input type="checkbox" id="remember" name="remember">
                        <label id="for-remember" for="remember">Remember Me</label>
                    </div>

                    <div class="forgot" onclick="showReset()">Forgot Password?<i class="fa-solid fa-circle-exclamation"></i></div>
                    <div class="d-grid">
                    <button type="submit" id="Login-btn" class="btn btn-outline-danger" type="button">Log in</button>
                    </div>
                </form>
            </div>

            <!-- RESET FORM -->
            <div id="resetPassword-form" <?php if ($reset_step === 1) echo 'class="hidden"'; ?>>
                <h2>Reset Password</h2>
               

                <!-- STEP 1: USERNAME -->
                <?php if ($reset_step === 1): ?>
                    <form method="POST" novalidate>
                        <input type="hidden" name="action" value="reset_username">
                        <input type="text" class="username" name="user_name" placeholder="" required>
                        <label id="for-username" for="user_name">Enter your Username</label>
                        <div class="d-grid">
                        <button type="submit" id="next-btn" class="btn btn-outline-danger" type="button">Next</button>
                        </div>
                    </form>
                <?php endif; ?>

                <!-- STEP 2: SECURITY QUESTION -->
                <?php if ($reset_step === 2): ?>
                    <form method="POST" novalidate>
                        <input type="hidden" name="action" value="reset_answer">
                        <p><?php echo htmlspecialchars($security_question); ?></p>
                        <input class="answer" type="text" name="answer" placeholder="" required>
                        <label id="for-answer" for="answer">Enter your Answer</label>
                        <div class="d-grid">
                            <button type="submit" id="verify-btn" class="btn btn-outline-info" type="button">Verify</button>
                        </div>
                    </form>
                <?php endif; ?>

                <!-- STEP 3: NEW PASSWORD -->
                <?php if ($reset_step === 3): ?>
                    <form method="POST" novalidate>
                        <input type="hidden" name="action" value="reset_password">
                        <input class="newpassword" type="password" name="new_password" id="new_password" placeholder="" required>
                        <label id="for-newpassword" for="new_password">Enter your New Password</label>
                        <input class="confirmpassword" type="password" name="confirm_password" id="confirm_password" placeholder="" required>
                        <label id="for-confirmpassword" for="confirm_password">Confirm your New Password</label>

                        <div class="show-password">
                            <input type="checkbox" id="show_passwords">
                            <label id="for-show_passwords" for="show_passwords">Show Passwords</label>
                        </div>

                        <div id="password-rules">
                            <p>Password must contain:</p>
                            <ul>
                            <li id="length"><i class="fa-regular fa-circle-xmark"></i> At least 8 characters</li>
                            <li id="number"><i class="fa-regular fa-circle-xmark"></i> At least 1 number</li>
                            <li id="special"><i class="fa-regular fa-circle-xmark"></i> At least 1 special character</li>
                            <li id="match"><i class="fa-regular fa-circle-xmark"></i> Passwords must match</li>
                            </ul>
                        </div>


                        <div class="d-grid">
                            <button type="submit" id="reset-btn" class="btn btn-outline-danger" type="button">Reset Password</button>
                        </div>
                    </form>
                <?php endif; ?>

                <div class="d-grid">
                    <button type="submit" id="go-back-btn" class="btn btn-outline-dark" type="button" onclick="goBack()">Go Back</button>
                </div>
            </div>
            <div class="copyright"><i class="fa-regular fa-copyright"></i> All Rights Reserved 2025</div>
        </div>

        <div class="right-panel">
            <div>
                <img src="./image/sorsu_logo.png" alt="sorsu_logo" class="sorsu-logo">
            </div>
            <div class="sorsu-text">
                <h4>Welcome to</h4>
                <h3>SORSU - BULAN CAMPUS</h3>
                <h1>SUPPLY OFFICE</h1>
                <h5>Inventory Management System</h5>
            </div>
        </div>
    </main>
    <footer></footer>


    <script src="./js/login.js"></script>
    <?php if (!empty($error)): ?>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: '<?php echo addslashes(htmlspecialchars($error)); ?>',
            confirmButtonColor: '#dc3545'
        });
    });
    </script>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: '<?php echo addslashes(htmlspecialchars($success)); ?>',
            confirmButtonColor: '#198754'
        });
    });
    </script>
    <?php endif; ?>


    <script>
    document.querySelector("form").addEventListener("submit", function(e) {
    let inputs = this.querySelectorAll("input, textarea, select");
    let hasError = false;

    inputs.forEach(input => {
        if (!input.checkValidity()) {
        input.classList.add("invalid-shake");
        hasError = true;

        // Remove animation class after it plays once, then re-apply if needed
        input.addEventListener("animationend", () => {
            input.classList.remove("invalid-shake");
        }, { once: true });
        }
    });

    if (hasError) {
        e.preventDefault(); // stop form from submitting if there are invalid fields
    }
    });

    // Password rules validation
    const newPass = document.getElementById("new_password");
    const confirmPass = document.getElementById("confirm_password");
    const showPasswords = document.getElementById("show_passwords");

    function updateRule(ruleElement, condition, text) {
        if (condition) {
        ruleElement.innerHTML = `<i class="fa-regular fa-circle-check" style="color:green;"></i> ${text}`;
        } else {
        ruleElement.innerHTML = `<i class="fa-regular fa-circle-xmark" style="color:red;"></i> ${text}`;
        }
    }

    function checkPassword() {
        const value = newPass.value;

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
        value && value === confirmPass.value,
        "Passwords must match"
        );
    }

    newPass.addEventListener("input", checkPassword);
    confirmPass.addEventListener("input", checkPassword);

    // Toggle password visibility
    showPasswords.addEventListener("change", function () {
    const type = this.checked ? "text" : "password";
    newPass.type = type;
    confirmPass.type = type;
    });
    </script>
</body>
</html>