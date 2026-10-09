<?php
// Start session with secure configuration
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Strict',
        'secure' => $isHttps
    ]);
    session_start();
}

// Load Configuration
require_once __DIR__ . '/Config/config.php';

// Security Headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');

// Autoloader
spl_autoload_register(function ($class_name) {
    // Check Core
    $core_file = __DIR__ . '/Core/' . $class_name . '.php';
    if (file_exists($core_file)) {
        require_once $core_file;
        return;
    }

    // Check Models
    $model_file = __DIR__ . '/Models/' . $class_name . '.php';
    if (file_exists($model_file)) {
        require_once $model_file;
        return;
    }
});

// Real client address. CF-Connecting-IP is trusted only when the request
// really arrives from a Cloudflare edge address, so it cannot be spoofed by
// hitting the origin directly. Ranges: https://www.cloudflare.com/ips/ (re-check periodically).
function ip_in_cidr($ip, $cidr) {
    list($subnet, $bits) = explode('/', $cidr);
    $ipBin = @inet_pton($ip);
    $subBin = @inet_pton($subnet);
    if ($ipBin === false || $subBin === false || strlen($ipBin) !== strlen($subBin)) {
        return false;
    }
    $bits = (int)$bits;
    $bytes = intdiv($bits, 8);
    if ($bytes > 0 && substr($ipBin, 0, $bytes) !== substr($subBin, 0, $bytes)) {
        return false;
    }
    $rem = $bits % 8;
    if ($rem === 0) {
        return true;
    }
    $mask = (0xFF << (8 - $rem)) & 0xFF;
    return (ord($ipBin[$bytes]) & $mask) === (ord($subBin[$bytes]) & $mask);
}

function get_client_ip() {
    $remote = $_SERVER['REMOTE_ADDR'] ?? '';
    $cfRanges = [
        '173.245.48.0/20', '103.21.244.0/22', '103.22.200.0/22', '103.31.4.0/22',
        '141.101.64.0/18', '108.162.192.0/18', '190.93.240.0/20', '188.114.96.0/20',
        '197.234.240.0/22', '198.41.128.0/17', '162.158.0.0/15', '104.16.0.0/13',
        '104.24.0.0/14', '172.64.0.0/13', '131.0.72.0/22',
        '2400:cb00::/32', '2606:4700::/32', '2803:f800::/32', '2405:b500::/32',
        '2405:8100::/32', '2a06:98c0::/29', '2c0f:f248::/32'
    ];
    $cfIp = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? '';
    if ($cfIp !== '' && filter_var($cfIp, FILTER_VALIDATE_IP)) {
        foreach ($cfRanges as $range) {
            if (ip_in_cidr($remote, $range)) {
                return $cfIp;
            }
        }
    }
    return $remote;
}

// CSRF Protection Functions
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        try {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        } catch (Exception $e) {
            $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32));
        }
    }
    return $_SESSION['csrf_token'];
}

/* 
 * Verify CSRF token on POST requests.
 * Call this at the start of POST processing blocks.
 * You must call this function manually in sensitive forms.
 */
function verify_csrf_token() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || 
            !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            die('CSRF validation failed. Please refresh the page and try again.');
        }
    }
}

