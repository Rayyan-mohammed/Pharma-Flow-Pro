<?php
require_once __DIR__ . '/init.php';

// Check if user is logged in
if (!isset($_SESSION['currentUser'])) {
    // Redirect to login page
    // Adjust path if necessary or use absolute URL
    header('Location: ' . BASE_URL . '/login.php');
    exit();
}

// Re-check the account on every request so deactivation, role changes and
// password resets take effect immediately instead of when the session expires.
try {
    $authDb = (new Database())->getConnection();
    $authStmt = $authDb->prepare("SELECT role, is_active, branch_id, password_hash FROM users WHERE user_id = :id LIMIT 1");
    $authStmt->bindValue(':id', (int)($_SESSION['currentUser']['user_id'] ?? 0), PDO::PARAM_INT);
    $authStmt->execute();
    $authRow = $authStmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $authRow = false;
}
if (!$authRow || (int)$authRow['is_active'] !== 1) {
    $_SESSION = [];
    session_destroy();
    header('Location: ' . BASE_URL . '/login.php');
    exit();
}
// A changed password ends sessions that were opened with the old one.
$sessionPwFingerprint = $_SESSION['pw_fingerprint'] ?? null;
$currentPwFingerprint = hash('sha256', $authRow['password_hash']);
if ($sessionPwFingerprint === null) {
    $_SESSION['pw_fingerprint'] = $currentPwFingerprint;
} elseif (!hash_equals($sessionPwFingerprint, $currentPwFingerprint)) {
    $_SESSION = [];
    session_destroy();
    header('Location: ' . BASE_URL . '/login.php');
    exit();
}
$_SESSION['currentUser']['role'] = $authRow['role'];
$_SESSION['currentUser']['branch_id'] = $authRow['branch_id'];
unset($_SESSION['currentUser']['password_hash']);

function hasRole($allowedRoles) {
    if (!is_array($allowedRoles)) {
        $allowedRoles = [$allowedRoles];
    }
    return isset($_SESSION['currentUser']['role']) && in_array($_SESSION['currentUser']['role'], $allowedRoles);
}

function checkRole($allowedRoles) {
    if (!hasRole($allowedRoles)) {
        // Redirect to dashboard with error or just access denied
        header('Location: ' . BASE_URL . '/dashboard/dashboard.php');
        exit();
    }
}

function getCurrentBranchId() {
    return (int)($_SESSION['currentUser']['branch_id'] ?? 1);
}

function hasPermission($permissionKey) {
    if (!isset($_SESSION['currentUser']['role'])) {
        return false;
    }

    $role = $_SESSION['currentUser']['role'];
    if ($role === 'Administrator') {
        return true;
    }

    try {
        $database = new Database();
        $db = $database->getConnection();
        $stmt = $db->prepare("SELECT is_allowed FROM role_permissions WHERE role_name = :role AND permission_key = :perm LIMIT 1");
        $stmt->bindValue(':role', $role);
        $stmt->bindValue(':perm', $permissionKey);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? ((int)$row['is_allowed'] === 1) : false;
    } catch (Exception $e) {
        return false;
    }
}

function checkPermission($permissionKey) {
    if (!hasPermission($permissionKey)) {
        header('Location: ' . BASE_URL . '/dashboard/dashboard.php');
        exit();
    }
}

require_once __DIR__ . '/menu.php';
