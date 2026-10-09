<?php
require_once '../app/init.php';

// Logout must be a POST with a valid CSRF token so other sites cannot sign users out
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit();
}
verify_csrf_token();

// Log logout before destroying session
if (isset($_SESSION['currentUser'])) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        $activityLog = new ActivityLog($db);
        $activityLog->log('LOGOUT', 'User logged out: ' . ($_SESSION['currentUser']['email'] ?? ''), 'user', $_SESSION['currentUser']['user_id'] ?? 0);
    } catch (Exception $e) {
        // activity_logs table may not exist yet
    }
}

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $cp = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $cp['path'], $cp['domain'], $cp['secure'], $cp['httponly']);
}
session_destroy();
header('Location: login.php');
exit();
