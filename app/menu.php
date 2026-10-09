<?php
/**
 * One list of everything staff can do. The sidebar, the Ctrl+K search and the
 * dashboards are all built from it, so a new page only needs adding here.
 */

function app_groups() {
    return [
        'sell'      => ['label' => 'Sell',          'icon' => 'bi-bag-heart',   'hint' => 'Billing, returns and customers'],
        'inventory' => ['label' => 'Inventory',     'icon' => 'bi-box-seam',    'hint' => 'Stock, expiry and medicines'],
        'purchase'  => ['label' => 'Purchasing',    'icon' => 'bi-truck',       'hint' => 'Suppliers, bills and payments'],
        'reports'   => ['label' => 'Reports',       'icon' => 'bi-graph-up',    'hint' => 'What is selling and what it earns'],
        'admin'     => ['label' => 'Store admin',   'icon' => 'bi-shield-lock', 'hint' => 'People, cash, backups and settings'],
    ];
}

function app_operations() {
    $A = 'Administrator'; $P = 'Pharmacist'; $S = 'Staff';
    $all = [$A, $P, $S]; $ap = [$A, $P]; $a = [$A];
    // [group, label, description, icon, path under public/, roles, permission, search keywords]
    return [
        ['sell', 'New sale',            'Bill a customer, apply discount and print the invoice', 'bi-cart-plus',        'sales/sell_medicine.php',                 $all, 'sales.create',      'bill billing invoice checkout pos cart sell'],
        ['sell', 'Sales records',       'Find any invoice and reprint it',                       'bi-receipt',          'sales/sales_records.php',                 $all, null,                'invoices history reprint sold'],
        ['sell', 'Returns and refunds', 'Take back a sold item and refund it',                   'bi-arrow-return-left','sales/returns.php',                       $ap,  null,                'return refund customer back'],
        ['sell', 'Prescriptions',       'Record and track doctor prescriptions',                 'bi-file-earmark-medical','prescription/prescription-management.php', $all, null,                'doctor patient rx'],
        ['sell', 'Customers',           'Customer ledger with visits and spending',              'bi-people',           'settings/customer_ledger.php',            $ap,  null,                'ledger patient mobile'],

        ['inventory', 'Stock overview',       'Search medicines and see what is in stock',       'bi-search',           'check/check-stock.php',                   $all, null,                'check available quantity find medicine'],
        ['inventory', 'Add medicine',         'Create a new medicine with batch and expiry',     'bi-plus-circle',      'add/add-medicine.php',                    $ap,  null,                'new create item'],
        ['inventory', 'Restock',              'Add or remove stock with a reason',               'bi-box-arrow-in-down','update/update-stock.php',                 $ap,  null,                'update adjust quantity receive'],
        ['inventory', 'Expiry management',    'Batches that are expired or expiring soon',       'bi-hourglass-split',  'expiration/expiration-management.php',    $all, null,                'expiration expire dated batch'],
        ['inventory', 'Alerts',               'Low stock and expiry warnings',                   'bi-bell',             'inventory/alerts.php',                    $ap,  null,                'low stock notification warning'],
        ['inventory', 'Alert center',         'Acknowledge and track open alerts',               'bi-bell-fill',        'inventory/alert_center.php',              $ap,  null,                'acknowledge escalation'],
        ['inventory', 'Reorder suggestions',  'What to buy next, based on sales',                'bi-cart-check',       'inventory/reorder_suggestions.php',       $ap,  null,                'order buy replenish need'],
        ['inventory', 'Inventory report',     'Full stock list with values',                     'bi-clipboard-data',   'inventory/inventory_report.php',          $ap,  null,                'valuation list report'],
        ['inventory', 'Categories',           'Organise medicines into categories',              'bi-tags',             'add/categories.php',                      $ap,  null,                'group type'],
        ['inventory', 'Bulk import',          'Add many medicines from a CSV file',              'bi-cloud-upload',     'inventory/bulk_import.php',               $a,   null,                'csv upload excel'],

        ['purchase', 'New purchase',          'Record stock received from a supplier',           'bi-bag-plus',         'purchase/purchase-management.php',        $ap,  'purchase.create',   'grn bill buy supplier invoice'],
        ['purchase', 'Purchase history',      'Every supplier bill so far',                      'bi-clock-history',    'purchase/purchase-history.php',           $ap,  null,                'bills past'],
        ['purchase', 'Supplier payables',     'How much is owed and how old it is',              'bi-hourglass',        'purchase/supplier-payables.php',          $ap,  null,                'due owed aging outstanding'],
        ['purchase', 'Settlements',           'Pay supplier bills',                              'bi-wallet2',          'purchase/settlements.php',                $ap,  'purchase.create',   'payment pay due'],
        ['purchase', 'Purchase returns',      'Send goods back to a supplier',                   'bi-box-arrow-up',     'purchase/purchase-returns.php',           $ap,  'purchase.create',   'return supplier credit'],
        ['purchase', 'Suppliers',             'Supplier contacts and details',                   'bi-truck',            'supplier/supplier-management.php',        $ap,  null,                'vendor distributor contact'],

        ['reports', 'Top selling',            'Best sellers and their profit',                   'bi-trophy',           'top_sales/top-selling.php',               $ap,  null,                'best popular profit'],
        ['reports', 'Analytics',              'Revenue, trends and busy hours',                  'bi-bar-chart-line',   'statistics/statistics.php',               $a,   null,                'statistics charts revenue trend'],
        ['reports', 'Stock analytics',        'Turnover, dead stock and fast movers',            'bi-activity',         'settings/stock_analytics.php',            $ap,  null,                'turnover movement dead fast'],
        ['reports', 'Financial reports',      'Profit and loss, tax and cash flow',              'bi-cash-coin',        'settings/financial_reports.php',          $ap,  'settings.financial','profit loss gst tax expense'],

        ['admin', 'Cash register',            'Open and close the day, count the drawer',        'bi-calculator',       'settings/cash_register.php',              $a,   null,                'cash drawer opening closing balance'],
        ['admin', 'Users',                    'Staff accounts, roles and passwords',             'bi-person-gear',      'users/manage_users.php',                  $a,   'users.manage',      'staff accounts roles password'],
        ['admin', 'Add user',                 'Give someone a login',                            'bi-person-plus',      'users/add_user.php',                      $a,   'users.manage',      'new staff create account'],
        ['admin', 'Activity log',             'Who did what, and when',                          'bi-journal-text',     'users/activity_log.php',                  $a,   null,                'audit history log'],
        ['admin', 'Permissions',              'Decide what each role may do',                    'bi-ui-checks-grid',   'settings/permissions_matrix.php',         $a,   null,                'roles access matrix'],
        ['admin', 'Branches',                 'Store branches',                                  'bi-shop',             'settings/branch_management.php',          $a,   null,                'location shop'],
        ['admin', 'Backup and restore',       'Download or restore a database copy',             'bi-database-down',    'settings/backup_restore.php',             $a,   'backup.restore',    'backup restore database export'],
        ['admin', 'System health',            'Check the database and data consistency',         'bi-heart-pulse',      'settings/health_checks.php',              $a,   null,                'status check diagnose'],
    ];
}

function app_dashboard_path() {
    $role = $_SESSION['currentUser']['role'] ?? '';
    if ($role === 'Pharmacist') return 'dashboard/pharmacist_dashboard.php';
    if ($role === 'Staff')      return 'dashboard/staff_dashboard.php';
    return 'dashboard/dashboard.php';
}

/** Operations the signed-in user may actually open (role and permission). */
function app_visible_operations() {
    static $cache = null;
    if ($cache !== null) return $cache;
    $role = $_SESSION['currentUser']['role'] ?? '';
    $cache = [];
    foreach (app_operations() as $op) {
        list($group, $label, $desc, $icon, $path, $roles, $perm, $keys) = $op;
        if (!in_array($role, $roles, true)) continue;
        if ($perm !== null && !hasPermission($perm)) continue;
        $cache[] = [
            'group' => $group, 'label' => $label, 'desc' => $desc, 'icon' => $icon,
            'path' => $path, 'href' => BASE_URL . '/' . $path, 'keys' => $keys,
        ];
    }
    return $cache;
}

function app_esc($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

/** Sidebar, top bar and the Ctrl+K search. Echo once, right after <body>. */
function render_app_shell() {
    $user   = $_SESSION['currentUser'] ?? [];
    $name   = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
    if ($name === '') $name = $user['email'] ?? 'Account';
    $initial = strtoupper(mb_substr($name, 0, 1));
    $role   = $user['role'] ?? '';
    $ops    = app_visible_operations();
    $groups = app_groups();
    $script = ltrim(str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? ''), '/');
    $current = null;
    foreach ($ops as $op) {
        if ($op['path'] !== '' && substr($script, -strlen($op['path'])) === $op['path']) { $current = $op; break; }
    }
    $dash = BASE_URL . '/' . app_dashboard_path();
    $onDash = substr($script, -strlen(app_dashboard_path())) === app_dashboard_path();
    $crumbGroup = $current ? $groups[$current['group']]['label'] : ($onDash ? 'Overview' : '');
    $crumbPage  = $current ? $current['label'] : ($onDash ? 'Dashboard' : '');
    $quick = null;
    foreach ($ops as $op) { if ($op['path'] === 'sales/sell_medicine.php') $quick = $op; }
    $ids = [];
    foreach ($ops as $op) { $ids[] = ['label' => $op['label'], 'group' => $groups[$op['group']]['label'], 'desc' => $op['desc'], 'icon' => $op['icon'], 'href' => $op['href'], 'keys' => $op['keys']]; }
    $ids[] = ['label' => 'Dashboard', 'group' => 'Overview', 'desc' => 'Your home screen', 'icon' => 'bi-grid-1x2', 'href' => $dash, 'keys' => 'home overview'];
    $ids[] = ['label' => 'My profile', 'group' => 'Account', 'desc' => 'Your details and password', 'icon' => 'bi-person-circle', 'href' => BASE_URL . '/users/profile.php', 'keys' => 'password account me'];
    ?>
<div class="app-shell" id="app-shell"
     data-ops="<?php echo app_esc(json_encode($ids, JSON_UNESCAPED_UNICODE)); ?>"
     data-api="<?php echo app_esc(BASE_URL . '/api/search.php'); ?>">
    <aside class="side" id="side" aria-label="Main menu">
        <div class="side-head">
            <a class="side-brand" href="<?php echo app_esc($dash); ?>" title="<?php echo app_esc(SHOP_NAME); ?>">
                <span class="mark"><i class="bi bi-heart-pulse-fill"></i></span>
                <span class="side-label"><b>om sai baba</b><small>Medical &amp; General</small></span>
            </a>
            <button class="icon-btn side-collapse" id="side-collapse" type="button" aria-label="Collapse menu" title="Collapse menu"><i class="bi bi-layout-sidebar-inset"></i></button>
        </div>

        <button class="side-search" type="button" data-open-palette>
            <i class="bi bi-search"></i><span class="side-label">Find anything</span><kbd class="side-label">Ctrl K</kbd>
        </button>

        <nav class="side-nav">
            <a class="nav-op <?php echo $onDash ? 'is-active' : ''; ?>" href="<?php echo app_esc($dash); ?>" title="Dashboard"><i class="bi bi-grid-1x2"></i><span class="side-label">Dashboard</span></a>
            <?php foreach ($groups as $gid => $g):
                $items = array_filter($ops, function ($o) use ($gid) { return $o['group'] === $gid; });
                if (!$items) continue; ?>
                <div class="nav-group">
                    <div class="nav-title side-label"><?php echo app_esc($g['label']); ?></div>
                    <?php foreach ($items as $op): ?>
                        <a class="nav-op <?php echo ($current && $current['path'] === $op['path']) ? 'is-active' : ''; ?>" href="<?php echo app_esc($op['href']); ?>" title="<?php echo app_esc($op['label']); ?>">
                            <i class="bi <?php echo app_esc($op['icon']); ?>"></i><span class="side-label"><?php echo app_esc($op['label']); ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </nav>

        <div class="side-foot">
            <a class="who" href="<?php echo app_esc(BASE_URL . '/users/profile.php'); ?>" title="My profile">
                <span class="avatar"><?php echo app_esc($initial); ?></span>
                <span class="side-label"><b><?php echo app_esc($name); ?></b><small><?php echo app_esc($role); ?></small></span>
            </a>
            <form method="POST" action="<?php echo app_esc(BASE_URL . '/logout.php'); ?>">
                <input type="hidden" name="csrf_token" value="<?php echo app_esc(generate_csrf_token()); ?>">
                <button class="icon-btn" type="submit" aria-label="Log out" title="Log out"><i class="bi bi-box-arrow-right"></i></button>
            </form>
        </div>
    </aside>
    <div class="side-scrim" id="side-scrim"></div>

    <header class="topbar" id="topbar">
        <button class="icon-btn menu-btn" id="menu-btn" type="button" aria-label="Open menu"><i class="bi bi-list"></i></button>
        <div class="crumb">
            <?php if ($crumbGroup !== ''): ?><span class="mono"><?php echo app_esc($crumbGroup); ?></span><i class="bi bi-chevron-right"></i><?php endif; ?>
            <b><?php echo app_esc($crumbPage !== '' ? $crumbPage : SHOP_SHORT_NAME); ?></b>
        </div>
        <button class="top-search" type="button" data-open-palette><i class="bi bi-search"></i><span>Search operations, medicines, suppliers</span><kbd>Ctrl K</kbd></button>
        <?php if ($quick): ?><a class="pill-btn" href="<?php echo app_esc($quick['href']); ?>"><i class="bi bi-cart-plus"></i><span>New sale</span></a><?php endif; ?>
    </header>

    <div class="palette" id="palette" role="dialog" aria-modal="true" aria-label="Find anything" hidden>
        <div class="palette-box">
            <div class="palette-input"><i class="bi bi-search"></i><input id="palette-q" type="text" placeholder="Type what you want to do, e.g. sale, expiry, supplier, a medicine name" autocomplete="off" spellcheck="false"><kbd>Esc</kbd></div>
            <div class="palette-list" id="palette-list" role="listbox"></div>
            <div class="palette-foot"><span><kbd>&uarr;</kbd><kbd>&darr;</kbd> move</span><span><kbd>Enter</kbd> open</span><span><kbd>Esc</kbd> close</span></div>
        </div>
    </div>
</div>
<script src="<?php echo app_esc(BASE_URL . '/app.js'); ?>" defer></script>
    <?php
}

/** Grouped operation cards for the dashboards (replaces the long flat wall of tiles). */
function render_operation_groups() {
    $ops = app_visible_operations();
    $groups = app_groups();
    $n = 0;
    foreach ($groups as $gid => $g) {
        $items = array_values(array_filter($ops, function ($o) use ($gid) { return $o['group'] === $gid; }));
        if (!$items) continue;
        $n++;
        echo '<section class="ops-group"><header class="ops-head"><span class="mono">' . sprintf('%02d', $n) . '</span><div><h3>' . app_esc($g['label']) . '</h3><p>' . app_esc($g['hint']) . '</p></div></header><div class="ops-grid">';
        foreach ($items as $op) {
            echo '<a class="op" href="' . app_esc($op['href']) . '"><span class="op-ic"><i class="bi ' . app_esc($op['icon']) . '"></i></span><span class="op-tx"><b>' . app_esc($op['label']) . '</b><small>' . app_esc($op['desc']) . '</small></span><i class="bi bi-arrow-up-right op-go"></i></a>';
        }
        echo '</div></section>';
    }
}
