<?php
require_once '../../app/auth.php';
checkRole(['Administrator']);

$database = new Database();
$db = $database->getConnection();
$pageError = '';

$roles = ['Administrator', 'Pharmacist', 'Staff'];
$permissions = [
    'users.manage',
    'backup.restore',
    'returns.approve',
    'settings.financial',
    'purchase.create',
    'sales.create'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    try {
        foreach ($roles as $role) {
            foreach ($permissions as $perm) {
                $key = 'perm_' . md5($role . '_' . $perm);
                $allowed = isset($_POST[$key]) ? 1 : 0;
                $up = $db->prepare("INSERT INTO role_permissions (role_name, permission_key, is_allowed) VALUES (:role, :perm, :allowed) ON DUPLICATE KEY UPDATE is_allowed = VALUES(is_allowed)");
                $up->bindValue(':role', $role);
                $up->bindValue(':perm', $perm);
                $up->bindValue(':allowed', $allowed, PDO::PARAM_INT);
                $up->execute();
            }
        }
        $saved = true;
    } catch (Exception $e) {
        $pageError = 'Unable to save permissions: ' . $e->getMessage();
    }
}

$map = [];
try {
    $stmt = $db->query("SELECT role_name, permission_key, is_allowed FROM role_permissions");
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $map[$r['role_name'] . '|' . $r['permission_key']] = (int)$r['is_allowed'] === 1;
    }
} catch (Exception $e) {
    $pageError = 'Permissions table is unavailable. Please run migration and reload this page.';
}

function checked_perm($map, $role, $perm) {
    return !empty($map[$role . '|' . $perm]) ? 'checked' : '';
}
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Permissions Matrix - <?php echo htmlspecialchars(SHOP_SHORT_NAME); ?></title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 16 16%27%3E%3Crect width=%2716%27 height=%2716%27 rx=%273%27 fill=%27%230d6efd%27/%3E%3Cpath fill=%27white%27 d=%27M8 3.5c-1.2-1.3-3.6-1-4.5.8-.7 1.5-.1 3 .9 4.1L8 12l3.6-3.6c1-1.1 1.6-2.6.9-4.1C11.6 2.5 9.2 2.2 8 3.5z%27/%3E%3C/svg%3E"><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"><link href="../styles.css" rel="stylesheet"></head>
<body>
<nav class="navbar navbar-dark bg-primary"><div class="container"><a class="navbar-brand fw-bold" href="../dashboard/dashboard.php"><i class="bi bi-heart-pulse-fill me-2"></i><?php echo htmlspecialchars(SHOP_SHORT_NAME); ?></a></div></nav>
<div class="container py-4">
    <h2 class="fw-bold text-primary mb-3">User Permissions Matrix</h2>
    <?php if (!empty($saved)): ?><div class="alert alert-success">Permissions updated.</div><?php endif; ?>
    <?php if (!empty($pageError)): ?><div class="alert alert-danger"><?php echo htmlspecialchars($pageError); ?></div><?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light"><tr><th>Permission</th><?php foreach ($roles as $role): ?><th class="text-center"><?php echo htmlspecialchars($role); ?></th><?php endforeach; ?></tr></thead>
                        <tbody>
                            <?php foreach ($permissions as $perm): ?>
                                <tr>
                                    <td class="fw-semibold"><?php echo htmlspecialchars($perm); ?></td>
                                    <?php foreach ($roles as $role): $name = 'perm_' . md5($role . '_' . $perm); ?>
                                        <td class="text-center"><input type="checkbox" name="<?php echo $name; ?>" <?php echo checked_perm($map, $role, $perm); ?>></td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <button class="btn btn-primary">Save Matrix</button>
            </form>
        </div>
    </div>
</div>
</body></html>

