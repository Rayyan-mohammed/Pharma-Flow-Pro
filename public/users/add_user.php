<?php
require_once '../../app/auth.php';
checkRole(['Administrator']);
checkPermission('users.manage');

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

$registrationError = '';
$registrationSuccess = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $role = $_POST['role'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';

    // Validate input
    if (empty($email) || empty($password) || empty($first_name) || empty($last_name) || empty($role)) {
        $registrationError = 'Please fill in all required fields.';
    } elseif ($password !== $confirm_password) {
        $registrationError = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $registrationError = 'Password must be at least 6 characters long.';
    } else {
        // Check if email already exists
        $user->email = $email;
        if ($user->emailExists()) {
            $registrationError = 'Email already exists.';
        } else {
            // Create new user
            $user->email = $email;
            $user->password_hash = password_hash($password, PASSWORD_DEFAULT);
            $user->first_name = $first_name;
            $user->last_name = $last_name;
            $user->role = $role;
            $user->phone = $phone;
            $user->address = $address;

            if ($user->create()) {
                $registrationSuccess = 'User created successfully.';
                try { $al = new ActivityLog($db); $al->log('CREATE', "Created user: {$first_name} {$last_name} ({$role})", 'user', null); } catch(Exception $e) {}
            } else {
                $registrationError = 'Failed to create user. Please try again.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New User - <?php echo htmlspecialchars(SHOP_SHORT_NAME); ?></title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 16 16%27%3E%3Crect width=%2716%27 height=%2716%27 rx=%273%27 fill=%27%230d6efd%27/%3E%3Cpath fill=%27white%27 d=%27M8 3.5c-1.2-1.3-3.6-1-4.5.8-.7 1.5-.1 3 .9 4.1L8 12l3.6-3.6c1-1.1 1.6-2.6.9-4.1C11.6 2.5 9.2 2.2 8 3.5z%27/%3E%3C/svg%3E">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../styles.css" rel="stylesheet">
    <style>
        #register-container {
            max-width: 600px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand fw-bold" href="../dashboard/dashboard.php">
                <i class="bi bi-heart-pulse-fill me-2"></i><?php echo htmlspecialchars(SHOP_SHORT_NAME); ?>
            </a>
        </div>
    </nav>
    <div id="register-container" class="card">
        <div class="card-header bg-primary text-white">
            <h3 class="text-center">User Management</h3>
            <p class="text-center">Add New User</p>
        </div>
        <div class="card-body">
            <?php if (!empty($registrationError)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($registrationError); ?></div>
            <?php endif; ?>
            
            <?php if (!empty($registrationSuccess)): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($registrationSuccess); ?></div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="first_name" class="form-label">First Name *</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="last_name" class="form-label">Last Name *</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email Address *</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password *</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>

                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm Password *</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                </div>

                <div class="mb-3">
                    <label for="role" class="form-label">Role *</label>
                    <select class="form-select" id="role" name="role" required>
                        <option value="">Select Role</option>
                        <option value="Administrator">Administrator</option>
                        <option value="Pharmacist">Pharmacist</option>
                        <option value="Staff">Staff</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label">Phone Number</label>
                    <input type="tel" class="form-control" id="phone" name="phone">
                </div>

                <div class="mb-3">
                    <label for="address" class="form-label">Address</label>
                    <textarea class="form-control" id="address" name="address" rows="3"></textarea>
                </div>

                <button type="submit" class="btn btn-primary w-100">Create User</button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
