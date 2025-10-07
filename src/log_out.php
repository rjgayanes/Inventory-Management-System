<?php
session_start();

// Unset all session variables
$_SESSION = array();

// If sessions use cookies, remove the session cookie too
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// --- CLEAR remember_token cookie and DB entry ---
if (isset($_COOKIE['remember_token'])) {
    // Delete cookie
    setcookie("remember_token", "", time() - 3600, "/");

    // Also clear token in database
    if (isset($_SESSION['user_ID'])) {
        include '../src/db.php';
        $stmt = $conn->prepare("UPDATE User SET remember_token = NULL WHERE user_ID = ?");
        $stmt->bind_param("i", $_SESSION['user_ID']);
        $stmt->execute();
        $stmt->close();
        $conn->close();
    }
}

// Destroy the session
session_destroy();

// Redirect to login page
header("Location: ../public/login.php");
exit();