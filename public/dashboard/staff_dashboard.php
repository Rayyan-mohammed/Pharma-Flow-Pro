<?php
require_once '../../app/auth.php';

// Ensure only Staff can see this page
if (!isset($_SESSION['currentUser']) || $_SESSION['currentUser']['role'] !== 'Staff') {
    header('Location: dashboard.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$user = $_SESSION['currentUser'];
if (!empty($user['first_name'])) {
    $userName = $user['first_name'] . ' ' . ($user['last_name'] ?? '');
} else {
    $userName = $user['username'] ?? 'Guest';
}
$userNameDisplay = htmlspecialchars($userName);

// Get basic stats staff can see
$medicine = new Medicine($db);
$stats = $medicine->getDashboardStats();
$lowStockItems = $medicine->getLowStockItems();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard - <?php echo htmlspecialchars(SHOP_SHORT_NAME); ?></title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 16 16%27%3E%3Crect width=%2716%27 height=%2716%27 rx=%273%27 fill=%27%230d6efd%27/%3E%3Cpath fill=%27white%27 d=%27M8 3.5c-1.2-1.3-3.6-1-4.5.8-.7 1.5-.1 3 .9 4.1L8 12l3.6-3.6c1-1.1 1.6-2.6.9-4.1C11.6 2.5 9.2 2.2 8 3.5z%27/%3E%3C/svg%3E">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../app.css" rel="stylesheet">
</head>
<body class="has-shell">
<?php render_app_shell(); ?>

    <div class="container py-4">
        <!-- Welcome Banner -->
        <div class="card bg-primary text-white mb-4">
            <div class="card-body p-4 position-relative">
                <h4 class="mb-1 fw-bold">Welcome back, <?php echo $userNameDisplay; ?></h4>
                <p class="mb-0 opacity-75" style="font-size:0.875rem;">Staff &middot; <?php echo date('l, d M Y'); ?></p>
            </div>
        </div>

        <!-- Stats Row -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card stat-card bg-primary p-3 h-100">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Total Medicines</h6>
                            <h2 class="mb-0"><?php echo number_format($stats['total_medicines']); ?></h2>
                        </div>
                        <div class="fs-1" style="color:var(--primary);"><i class="bi bi-capsule"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card bg-warning p-3 h-100">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Low Stock Items</h6>
                            <h2 class="mb-0"><?php echo number_format($stats['low_stock']); ?></h2>
                        </div>
                        <div class="fs-1" style="color:var(--warning);"><i class="bi bi-exclamation-triangle"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card bg-danger p-3 h-100">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Expired Medicines</h6>
                            <h2 class="mb-0"><?php echo number_format($stats['expired']); ?></h2>
                        </div>
                        <div class="fs-1" style="color:var(--danger);"><i class="bi bi-calendar-x"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Quick Actions -->
            <div class="col-lg-8">
                <?php render_role_home($db); ?>
                <?php render_operation_groups(); ?>
            </div>

            <!-- Side Widgets -->
            <div class="col-lg-4">
                <!-- Low Stock Alerts -->
                <div class="card mb-4">
                    <div class="card-header d-flex align-items-center gap-2">
                        <i class="bi bi-exclamation-triangle-fill" style="color:var(--danger);"></i>
                        <h5 class="mb-0">Low Stock Alerts</h5>
                    </div>
                    <ul class="list-group list-group-flush">
                        <?php if (empty($lowStockItems)): ?>
                            <li class="list-group-item text-center py-4" style="color:var(--text-tertiary);">
                                <i class="bi bi-check-circle me-1" style="color:var(--success);"></i>All stock levels healthy
                            </li>
                        <?php else: ?>
                            <?php foreach($lowStockItems as $item): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span style="font-size:0.875rem;"><?php echo htmlspecialchars($item['name']); ?></span>
                                    <span class="badge bg-danger rounded-pill"><?php echo $item['stock']; ?></span>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                    <div class="card-footer text-center">
                        <small style="color:var(--text-tertiary);">Contact a pharmacist to restock</small>
                    </div>
                </div>

                <!-- System Info -->
                <div class="card">
                    <div class="card-header d-flex align-items-center gap-2">
                        <i class="bi bi-info-circle" style="color:var(--primary);"></i>
                        <h5 class="mb-0">System Info</h5>
                    </div>
                    <div class="card-body" style="font-size:0.875rem;">
                        <div class="d-flex justify-content-between mb-2">
                            <span style="color:var(--text-secondary);">Date</span>
                            <span class="fw-600"><?php echo date('d M Y'); ?></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span style="color:var(--text-secondary);">Server</span>
                            <span class="fw-600" style="color:var(--success);"><i class="bi bi-circle-fill me-1" style="font-size:0.5rem;"></i>Online</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

