<?php
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('Run this script from the command line.'); }
require_once __DIR__ . '/../../app/init.php';

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

$email = $argv[2] ?? 'admin1@pharmacy.com'; // optional second argument: an existing admin's email
$password = $argv[1] ?? bin2hex(random_bytes(6)); // pass a password as the first argument, or one is generated
$role = 'Administrator';

// Check if admin exists
$user->email = $email;
if($user->emailExists()) {
    echo "Default Administrator already exists. Updating password...\n";
    $query = "UPDATE users SET password_hash = :password_hash, role = :role, is_active = 1 WHERE email = :email";
    $stmt = $db->prepare($query);
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt->bindParam(":password_hash", $password_hash);
    $stmt->bindParam(":role", $role);
    $stmt->bindParam(":email", $email);
    $stmt->execute();
    echo "Administrator updated successfully!\n";
    echo "Email: $email\n";
    echo "Password: $password\n";
} else {
    $user->email = $email;
    $user->password_hash = password_hash($password, PASSWORD_DEFAULT);
    $user->first_name = 'System';
    $user->last_name = 'Admin';
    $user->role = $role;
    $user->phone = '0000000000';
    $user->address = 'System';
    
    if($user->create()) {
        echo "Default Administrator created successfully!\n";
        echo "Email: $email\n";
        echo "Password: $password\n";
    } else {
        echo "Failed to create Administrator.\n";
    }
}
?>