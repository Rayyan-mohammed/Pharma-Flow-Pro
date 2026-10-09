<?php
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('Run this script from the command line.'); }
/**
 * Creates a limited MySQL user for the app and writes its credentials to
 * app/Config/config.local.php (git-ignored).
 *
 * Usage:  DB_ADMIN_PASS="<root password>" php database/setup/create_app_user.php [admin-user]
 * The admin password is read from the environment so it never lands in shell history
 * arguments or in the repository. A random password is generated for the new user.
 */
$adminUser = $argv[1] ?? 'root';
$adminPass = getenv('DB_ADMIN_PASS');
if ($adminPass === false) {
    fwrite(STDERR, "Set DB_ADMIN_PASS to the database administrator password first.\n");
    exit(1);
}

require_once __DIR__ . '/../../app/Config/config.php';
$appUser = 'pharmaflow_app';
$appPass = rtrim(strtr(base64_encode(random_bytes(24)), '+/', '-_'), '=');

try {
    $pdo = new PDO('mysql:host=' . DB_HOST, $adminUser, $adminPass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $db = preg_replace('/[^A-Za-z0-9_]/', '', DB_NAME);
    $quotedPass = $pdo->quote($appPass);
    $pdo->exec("CREATE USER IF NOT EXISTS '{$appUser}'@'localhost' IDENTIFIED BY {$quotedPass}");
    $pdo->exec("ALTER USER '{$appUser}'@'localhost' IDENTIFIED BY {$quotedPass}");
    // Day-to-day access plus the table changes the app makes itself. No DROP, GRANT, FILE or other schemas.
    $pdo->exec("GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX ON `{$db}`.* TO '{$appUser}'@'localhost'");
    $pdo->exec('FLUSH PRIVILEGES');
} catch (Exception $e) {
    fwrite(STDERR, 'Failed: ' . $e->getMessage() . "\n");
    exit(1);
}

$file = __DIR__ . '/../../app/Config/config.local.php';
$existing = file_exists($file) ? file_get_contents($file) : "<?php\n";
$existing = preg_replace("/^define\('DB_(USER|PASS)'.*\n/m", '', $existing);
$existing = rtrim($existing) . "\ndefine('DB_USER', " . var_export($appUser, true) . ");\ndefine('DB_PASS', " . var_export($appPass, true) . ");\n";
file_put_contents($file, $existing);

echo "Created '{$appUser}' and updated app/Config/config.local.php.\n";
echo "Note: restore (DROP TABLE) and database/setup/migrate.php on a fresh database need an admin account.\n";
