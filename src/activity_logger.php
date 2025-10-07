<?php
// Simple reusable activity logger
// Usage: require_once __DIR__ . '/activity_logger.php'; log_activity($conn, $userId, 'Action text');

if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

function log_activity(mysqli $conn, $userId, string $action): void {
	try {
		if (!$userId && isset($_SESSION['user_ID'])) {
			$userId = (int)$_SESSION['user_ID'];
		}
		if (!$userId) {
			return; // no user context
		}
		$stmt = $conn->prepare("INSERT INTO Activity_log (action, user_ID) VALUES (?, ?)");
		$stmt->bind_param("si", $action, $userId);
		$stmt->execute();
		$stmt->close();
	} catch (Throwable $e) {
		// Do not break primary flow if logging fails
		error_log('Activity log failed: ' . $e->getMessage());
	}
}

?>


