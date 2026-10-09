<?php
require_once '../app/init.php';

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

$loginError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (!empty($email) && !empty($password)) {
        // Rate limiting: failed attempts are stored in the database, keyed by client
        // address and by account, so dropping the session cookie does not reset them.
        $maxAttempts = 5;
        $lockoutMinutes = 15;
        $clientIp = get_client_ip();
        $emailKey = strtolower(trim($email));
        $failedCount = 0;
        try {
            // Normally created by migrate.php; the app user may lack CREATE privilege
            try {
            $db->exec("CREATE TABLE IF NOT EXISTS login_attempts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                ip_address VARCHAR(45) NOT NULL,
                email VARCHAR(255) NOT NULL,
                attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_login_attempts_ip (ip_address, attempted_at),
                INDEX idx_login_attempts_email (email, attempted_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            } catch (Exception $e) {}
            $db->exec("DELETE FROM login_attempts WHERE attempted_at < (NOW() - INTERVAL 1 DAY)");
            $cnt = $db->prepare("SELECT COUNT(*) FROM login_attempts WHERE attempted_at > (NOW() - INTERVAL $lockoutMinutes MINUTE) AND (ip_address = :ip OR email = :email)");
            $cnt->execute([':ip' => $clientIp, ':email' => $emailKey]);
            $failedCount = (int)$cnt->fetchColumn();
        } catch (Exception $e) {
            error_log('Login throttle unavailable: ' . $e->getMessage());
        }

        if ($failedCount >= $maxAttempts) {
            $loginError = "Too many failed attempts. Please try again in $lockoutMinutes minutes.";
        } else {
            $userData = $user->login($email, $password);

            if ($userData) {
                try {
                    $clr = $db->prepare("DELETE FROM login_attempts WHERE ip_address = :ip OR email = :email");
                    $clr->execute([':ip' => $clientIp, ':email' => $emailKey]);
                } catch (Exception $e) {}

                // Regenerate session ID to prevent session fixation
                session_regenerate_id(true);

                // Keep the password hash out of the session; remember its fingerprint
                // so a later password change ends this session.
                $_SESSION['pw_fingerprint'] = hash('sha256', $userData['password_hash']);
                unset($userData['password_hash']);
                $_SESSION['currentUser'] = $userData;
            
            // Log login activity
            try {
                $activityLog = new ActivityLog($db);
                $activityLog->log('LOGIN', 'User logged in: ' . $userData['email'], 'user', $userData['user_id']);
            } catch (Exception $e) {
                // activity_logs table may not exist yet
            }
            
            if ($userData['role'] === 'Staff') {
                header('Location: dashboard/staff_dashboard.php');
            } elseif ($userData['role'] === 'Pharmacist') {
                header('Location: dashboard/pharmacist_dashboard.php');
            } else {
                header('Location: dashboard/dashboard.php');
            }
            exit();
        } else {
            // Track failed attempt
            try {
                $rec = $db->prepare("INSERT INTO login_attempts (ip_address, email) VALUES (:ip, :email)");
                $rec->execute([':ip' => $clientIp, ':email' => $emailKey]);
            } catch (Exception $e) {}
            $loginError = 'Invalid email or password.';
        }
        }
    } else {
        $loginError = 'Please enter both email and password.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo htmlspecialchars(SHOP_SHORT_NAME); ?></title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 16 16%27%3E%3Crect width=%2716%27 height=%2716%27 rx=%273%27 fill=%27%230d6efd%27/%3E%3Cpath fill=%27white%27 d=%27M8 3.5c-1.2-1.3-3.6-1-4.5.8-.7 1.5-.1 3 .9 4.1L8 12l3.6-3.6c1-1.1 1.6-2.6.9-4.1C11.6 2.5 9.2 2.2 8 3.5z%27/%3E%3C/svg%3E">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
</head>
<body class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-header">
            <div style="display:inline-flex;align-items:center;justify-content:center;width:48px;height:48px;border-radius:12px;background:var(--primary-50);margin-bottom:1rem;">
                <i class="bi bi-heart-pulse-fill" style="font-size:1.5rem;color:var(--primary);"></i>
            </div>
            <h3><?php echo htmlspecialchars(SHOP_NAME); ?></h3>
            <p>Staff sign in</p>
        </div>
        
        <div class="card">
            <div class="card-body">
                <?php if (!empty($loginError)): ?>
                    <div class="alert alert-danger d-flex align-items-center gap-2 mb-4">
                        <i class="bi bi-exclamation-circle"></i>
                        <span><?php echo htmlspecialchars($loginError); ?></span>
                    </div>
                <?php endif; ?>

                <form id="login-form" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email address</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required autofocus>
                    </div>
                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            Sign in
                            <i class="bi bi-arrow-right ms-1"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <p class="text-center mt-4" style="color:var(--text-tertiary);font-size:0.8125rem;">
            Need access? Contact your system administrator.
        </p>
        <p class="text-center mt-2" style="font-size:0.8125rem;">
            <a href="index.php" style="color:var(--primary);text-decoration:none;"><i class="bi bi-arrow-left me-1"></i>Back to Om Sai Baba Medical Store</a>
        </p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
