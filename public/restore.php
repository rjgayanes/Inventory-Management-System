<?php 
session_start();

if (isset($_POST['restore'])) {
    $host = "localhost";
    $user = "root";
    $pass = "";
    $dbname = "sorsu-bc_ims";

    $backupDir = __DIR__ . "/backups/";
    $file = $_POST['backup_file'];
    $filePath = $backupDir . $file;

    if (file_exists($filePath)) {
        $conn = new mysqli($host, $user, $pass, $dbname);
        if ($conn->connect_error) {
            $_SESSION['message'] = "Connection failed: " . $conn->connect_error;
            header("Location: setting.php");
            exit();
        }

        $sql = file_get_contents($filePath);

        if ($conn->multi_query($sql)) {
            $_SESSION['message'] = "Database restored successfully from: " . basename($file);
            $_SESSION['message_type'] = "success"; // success
        } else {
            $_SESSION['message'] = "Restore failed: " . $conn->error;
            $_SESSION['message_type'] = "danger"; // error
        }

        $conn->close();
    } else {
        $_SESSION['message'] = "Selected backup file does not exist!";
        $_SESSION['message_type'] = "warning"; // warning

    }

    header("Location: setting.php");
    exit();
}