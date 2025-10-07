<?php 
session_start();

if (isset($_POST['backup'])) {
    $host = "localhost";
    $user = "root";
    $pass = "";
    $dbname = "sorsu-bc_ims";

    $backupDir = __DIR__ . "/backups/";
    if (!is_dir($backupDir)) {
        mkdir($backupDir, 0777, true);
    }

    $backupFile = $backupDir . $dbname . "_backup_" . date("Y-m-d_H-i-s") . ".sql";
    $mysqldump = "C:/xampp/mysql/bin/mysqldump.exe";
    $command = "\"{$mysqldump}\" --user=\"{$user}\" --password=\"{$pass}\" --host=\"{$host}\" {$dbname} > \"{$backupFile}\"";

    system($command, $output);

    if (file_exists($backupFile) && filesize($backupFile) > 0) {
    $_SESSION['message'] = "Backup created: " . basename($backupFile);
    $_SESSION['message_type'] = "success"; // success
    } else {
        $_SESSION['message'] = "Backup failed or empty file created!";
        $_SESSION['message_type'] = "danger"; // error
    }
    header("Location: setting.php");
    exit();

}