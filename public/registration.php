<?php 
session_start();
require_once "../src/db.php"; // DB connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_name = trim($_POST['user_name']);
    $user_email = trim($_POST['user_email']);
    $raw_password = $_POST['password']; // keep raw for validation
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $professional_designation = trim($_POST['professional_designation']);
    $role = $_POST['role'];

    // ✅ Password validation (backend)
    $pattern = "/^(?=.*[0-9])(?=.*[\W_]).{8,}$/";
    if (!preg_match($pattern, $raw_password)) {
        $message = "Password must be at least 8 characters long, include a number, and a special character.";
    } else {
        $password = password_hash($raw_password, PASSWORD_DEFAULT);

        // Folder for uploads
        $uploadDir = __DIR__ . "/uploads/profile_images/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Default profile image
        $profile_image_path = "uploads/profile_images/default_profile.jpg";

        // If user uploaded a file
        if (!empty($_FILES['profile_image']['tmp_name'])) {
            $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
            $ext = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));

            if (!in_array($ext, $allowed_ext)) {
                die("Error: Only JPG, PNG, and GIF files are allowed.");
            }

            if ($_FILES['profile_image']['size'] > 2 * 1024 * 1024) {
                die("Error: Profile image must be less than 2MB.");
            }

            $newFileName = uniqid("user_", true) . "." . $ext;
            $targetPath = $uploadDir . $newFileName;

            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $targetPath)) {
                $profile_image_path = "uploads/profile_images/" . $newFileName;
            } else {
                die("Error: Failed to upload image.");
            }
        }

        // Insert into DB
        $sql = "INSERT INTO `Users` 
                (user_name, user_email, password, first_name, last_name, professional_designation, role, profile_image) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssss", 
            $user_name, 
            $user_email, 
            $password, 
            $first_name, 
            $last_name, 
            $professional_designation, 
            $role, 
            $profile_image_path
        );

        if ($stmt->execute()) {
            $message = "Registration successful!";

            // Log activity
            $log_sql = "INSERT INTO Activity_log (action, user_ID) VALUES (?, ?)";
            $log_stmt = $conn->prepare($log_sql);
            $action = "New user registered: $first_name $last_name";
            $new_user_id = $stmt->insert_id;
            $log_stmt->bind_param("si", $action, $new_user_id);
            $log_stmt->execute();
            $log_stmt->close();
        } else {
            $message = "Error: " . $stmt->error;
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration - Inventory System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
        }
        .register-container {
            width: 380px;
            background: #fff;
            padding: 20px;
            margin: 50px auto;
            box-shadow: 0px 0px 10px rgba(0,0,0,0.2);
            border-radius: 8px;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }
        input, select {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }
        button {
            background: maroon;
            color: white;
            border: none;
            padding: 10px;
            width: 100%;
            cursor: pointer;
            border-radius: 5px;
            font-size: 16px;
        }
        button:hover {
            background: #a00000;
        }
        #msg {
            text-align: center;
            margin-top: 10px;
            font-weight: bold;
        }
        .image-preview {
            display: block;
            margin: 0 auto 10px;
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #ccc;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h2>Create Account</h2>

        <?php if (!empty($message)) : ?>
            <p id="msg" style="color:<?= strpos($message, 'successful') !== false ? 'green' : 'red' ?>;">
                <?= htmlspecialchars($message) ?>
            </p>
        <?php endif; ?>

        <form id="registerForm" method="POST" enctype="multipart/form-data">
            <img src="uploads/profile_images/default_profile.jpg" alt="Profile Preview" id="profilePreview" class="image-preview">

            <div class="form-group">
                <label>Username</label>
                <input type="text" name="user_name" required>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="user_email" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
                <small>Password must be at least 8 characters, include a number and a special character.</small>
            </div>

            <div class="form-group">
                <label>First Name</label>
                <input type="text" name="first_name" required>
            </div>

            <div class="form-group">
                <label>Last Name</label>
                <input type="text" name="last_name" required>
            </div>

            <div class="form-group">
                <label>Professional Designation</label>
                <input type="text" name="professional_designation" placeholder="e.g. MIT, ENGR.">
            </div>

            <div class="form-group">
                <label>Role</label>
                <select name="role" required>
                    <option value="Supply Officer">Supply Officer</option>
                </select>
            </div>

            <div class="form-group">
                <label>Profile Image</label>
                <input type="file" name="profile_image" accept="image/*" onchange="previewImage(event)">
            </div>

            <button type="submit">Register</button>
        </form>
    </div>

    <script>
        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function(){
                document.getElementById('profilePreview').src = reader.result;
            }
            reader.readAsDataURL(event.target.files[0]);
        }

        document.getElementById("registerForm").addEventListener("submit", function(e) {
            const password = document.querySelector("input[name='password']").value;
            const msg = document.getElementById("msg");
            const pattern = /^(?=.*[0-9])(?=.*[\W_]).{8,}$/;

            if (!pattern.test(password)) {
                e.preventDefault();
                msg.style.color = "red";
                msg.textContent = "Password must be at least 8 characters long, include a number, and a special character.";
            }
        });
    </script>
</body>
</html>