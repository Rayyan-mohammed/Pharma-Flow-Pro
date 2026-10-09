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
    <title>Staff login - <?php echo htmlspecialchars(SHOP_SHORT_NAME); ?></title>
    <meta name="robots" content="noindex">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 32 32%27%3E%3Crect width=%2732%27 height=%2732%27 rx=%278%27 fill=%27%23040a09%27/%3E%3Cpath fill=%27%232dd4bf%27 d=%27M16 24.5 8.4 17c-2.4-2.5-2.2-6.4.5-8.2 2.1-1.4 5-.9 7.1 1.3 2.1-2.2 5-2.7 7.1-1.3 2.7 1.8 2.9 5.7.5 8.2z%27/%3E%3C/svg%3E">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wdth,wght@12..96,75..100,200..800&amp;family=JetBrains+Mono:wght@400;500;600&amp;display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="login.css" rel="stylesheet">
</head>
<body class="login">
    <aside class="login-side">
        <span class="orbit o1"><i></i></span>
        <span class="orbit o2"><i></i></span>
        <a class="brand" href="index.php"><i class="bi bi-heart-pulse-fill"></i>om sai baba</a>
        <h1>Welcome<span>back.</span></h1>
        <div class="meta"><span><?php echo htmlspecialchars(SHOP_NAME); ?></span><span>Wishing you a speedy recovery</span></div>
    </aside>

    <main class="login-main">
        <div class="login-box">
            <span class="kicker rise">Staff area</span>
            <h2 class="rise d1">Sign in.</h2>
            <p class="sub rise d2">Use the account the store gave you.</p>

            <?php if (!empty($loginError)): ?>
                <div class="err" role="alert"><i class="bi bi-exclamation-circle"></i><span><?php echo htmlspecialchars($loginError); ?></span></div>
            <?php endif; ?>

            <form id="login-form" method="POST" class="rise d3">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                <div class="field">
                    <label for="email">Email address</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" autocomplete="username" required autofocus>
                </div>
                <div class="field">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" autocomplete="current-password" required>
                </div>
                <button type="submit" class="go">Sign in <i class="bi bi-arrow-right"></i></button>
            </form>

            <div class="foot-links rise d4">
                <span>Need access? Contact your system administrator.</span>
                <a href="index.php"><i class="bi bi-arrow-left me-1"></i>Back to the store website</a>
            </div>
        </div>
    </main>
</body>
</html>
