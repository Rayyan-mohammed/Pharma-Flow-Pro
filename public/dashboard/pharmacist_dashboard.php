<?php
require_once '../../app/auth.php';

// Ensure only Pharmacist can see this page
if (!isset($_SESSION['currentUser']) || $_SESSION['currentUser']['role'] !== 'Pharmacist') {
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

// Get Statistics
$medicine = new Medicine($db);
$stats = $medicine->getDashboardStats();
$salesChartData = $medicine->getSalesChartData();
$lowStockItems = $medicine->getLowStockItems();

// Format chart data
$chartLabels = [];
$chartData = [];
foreach($salesChartData as $data) {
    $chartLabels[] = date('d M', strtotime($data['date']));
    $chartData[] = $data['total'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacist Dashboard - <?php echo htmlspecialchars(SHOP_SHORT_NAME); ?></title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 16 16%27%3E%3Crect width=%2716%27 height=%2716%27 rx=%273%27 fill=%27%230d6efd%27/%3E%3Cpath fill=%27white%27 d=%27M8 3.5c-1.2-1.3-3.6-1-4.5.8-.7 1.5-.1 3 .9 4.1L8 12l3.6-3.6c1-1.1 1.6-2.6.9-4.1C11.6 2.5 9.2 2.2 8 3.5z%27/%3E%3C/svg%3E">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../styles.css" rel="stylesheet">
    <link href="../app.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="has-shell">
<?php render_app_shell(); ?>
    <nav class="navbar navbar-dark bg-primary">
        <div class="container d-flex justify-content-between align-items-center">
            <a class="navbar-brand fw-bold" href="#">
                <i class="bi bi-heart-pulse-fill me-2"></i><?php echo htmlspecialchars(SHOP_SHORT_NAME); ?>
            </a>
            <div class="d-flex align-items-center gap-2">
                <div class="position-relative" id="globalSearchWrap">
                    <input type="text" class="form-control form-control-sm bg-white bg-opacity-25 text-white border-0" 
                           id="globalSearch" placeholder="Search..." style="width: 200px;" autocomplete="off">
                    <div class="dropdown-menu shadow-lg p-0" id="searchDropdown" style="width: 380px; max-height: 400px; overflow-y: auto;"></div>
                </div>
                <a class="btn btn-outline-light btn-sm" href="../users/profile.php"><i class="bi bi-person-circle"></i></a>
                <form method="POST" action="../logout.php" class="d-inline"><input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>"><button type="submit" class="btn btn-light btn-sm"><i class="bi bi-box-arrow-right me-1"></i>Logout</button></form>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <!-- Welcome Banner -->
        <div class="card bg-primary text-white mb-4">
            <div class="card-body p-4 position-relative">
                <h4 class="mb-1 fw-bold">Welcome back, <?php echo $userNameDisplay; ?></h4>
                <p class="mb-0 opacity-75" style="font-size:0.875rem;">Pharmacist &middot; <?php echo date('l, d M Y'); ?></p>
            </div>
        </div>

        <!-- Statistics Row -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <a href="../inventory/inventory_report.php" class="text-decoration-none">
                    <div class="card stat-card bg-primary p-3 h-100 transition-hover">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">Total Medicines</h6>
                                <h2 class="mb-0"><?php echo number_format($stats['total_medicines']); ?></h2>
                            </div>
                            <div class="fs-1" style="color:var(--primary);"><i class="bi bi-capsule"></i></div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="../check/check-stock.php" class="text-decoration-none">
                    <div class="card stat-card bg-warning p-3 h-100 transition-hover">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">Low Stock Items</h6>
                                <h2 class="mb-0"><?php echo number_format($stats['low_stock']); ?></h2>
                            </div>
                            <div class="fs-1" style="color:var(--warning);"><i class="bi bi-exclamation-triangle"></i></div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="../expiration/expiration-management.php" class="text-decoration-none">
                    <div class="card stat-card bg-danger p-3 h-100 transition-hover">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">Expired Medicines</h6>
                                <h2 class="mb-0"><?php echo number_format($stats['expired']); ?></h2>
                            </div>
                            <div class="fs-1" style="color:var(--danger);"><i class="bi bi-calendar-x"></i></div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <div class="row g-4">
            <!-- Main Actions Column -->
            <div class="col-lg-8">
                <?php render_role_home($db); ?>
                <?php render_operation_groups(); ?>

                <!-- Sales Chart -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-graph-up me-2" style="color:var(--primary);"></i>Revenue &mdash; Last 7 Days</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="salesChart" height="150"></canvas>
                    </div>
                </div>
            </div>

            <!-- Side Widgets Column -->
            <div class="col-lg-4">
                <!-- Low Stock Widget -->
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
                        <a href="../update/update-stock.php" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-arrow-repeat me-1"></i>Restock Now
                        </a>
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

    <!-- Chart Config -->
    <script>
        const ctx = document.getElementById('salesChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($chartLabels); ?>,
                    datasets: [{
                        label: 'Revenue',
                        data: <?php echo json_encode($chartData); ?>,
                        borderColor: '#0f766e',
                        backgroundColor: 'rgba(15, 118, 110, 0.08)',
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#0f766e',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        borderWidth: 2.5
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#18181B',
                            titleFont: { family: 'Inter', weight: '600' },
                            bodyFont: { family: 'Inter' },
                            padding: 12,
                            cornerRadius: 8,
                            displayColors: false,
                            callbacks: {
                                label: function(context) {
                                    return '₹' + context.parsed.y.toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(0,0,0,0.04)', drawBorder: false },
                            ticks: { font: { family: 'Inter', size: 11 }, color: '#71717A' }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { font: { family: 'Inter', size: 11 }, color: '#71717A' }
                        }
                    }
                }
            });
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    (function() {
        const input = document.getElementById('globalSearch');
        const dropdown = document.getElementById('searchDropdown');
        if (!input || !dropdown) return;
        let timer;
        input.addEventListener('input', function() {
            clearTimeout(timer);
            const q = this.value.trim();
            if (q.length < 2) { dropdown.classList.remove('show'); return; }
            timer = setTimeout(() => {
                fetch('../api/search.php?q=' + encodeURIComponent(q))
                    .then(r => r.json())
                    .then(data => {
                        if (!data.results || data.results.length === 0) {
                            dropdown.innerHTML = '<div class="p-3 text-muted text-center small">No results found</div>';
                        } else {
                            dropdown.innerHTML = data.results.map(r => 
                                `<a href="${r.url}" class="dropdown-item d-flex align-items-start py-2 px-3 border-bottom">
                                    <i class="bi ${r.icon} me-2 mt-1 text-primary"></i>
                                    <div><div class="fw-bold small">${r.title}</div><div class="text-muted" style="font-size:0.75rem">${r.detail}</div></div>
                                    <span class="badge bg-light text-secondary ms-auto" style="font-size:0.65rem">${r.type}</span>
                                </a>`
                            ).join('');
                        }
                        dropdown.classList.add('show');
                    });
            }, 300);
        });
        document.addEventListener('click', function(e) {
            if (!e.target.closest('#globalSearchWrap')) dropdown.classList.remove('show');
        });
    })();
    </script>
</body>
</html>

