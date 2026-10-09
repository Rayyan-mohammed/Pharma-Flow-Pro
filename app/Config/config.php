<?php
// Machine-specific secrets live in config.local.php (not committed). Copy
// config.local.example.php to config.local.php and edit it. Environment
// variables override both.
if (file_exists(__DIR__ . '/config.local.php')) {
    require_once __DIR__ . '/config.local.php';
}

function _cfg_default($name, $default) {
    if (defined($name)) {
        return;
    }
    $env = getenv($name);
    define($name, $env !== false ? $env : $default);
}

// Database Configuration
_cfg_default('DB_HOST', 'localhost');
_cfg_default('DB_PORT', '3306');
_cfg_default('DB_NAME', 'medical_management');
_cfg_default('DB_USER', 'root');
_cfg_default('DB_PASS', '');

// Application Configuration
_cfg_default('APP_NAME', 'PharmaFlow Pro');
_cfg_default('BASE_URL', '/pharmaflow_pro/public');

// Security: must be overridden in config.local.php; backup/restore refuses the default
_cfg_default('BACKUP_RESTORE_PASSWORD', 'ChangeThis@123');

// Shop details shown on invoices, the login page and navigation
_cfg_default('SHOP_NAME', 'Om Sai Baba Medical and General Store');
_cfg_default('SHOP_SHORT_NAME', 'Om Sai Baba Medical Store');
_cfg_default('SHOP_ADDRESS', 'Yenugonda, Mahabubnagar');
_cfg_default('SHOP_PHONE', '+91 98851 44064');
_cfg_default('SHOP_EMAIL', 'omsaibaba@mystore.com');
_cfg_default('SHOP_PROPRIETOR', 'Rajesh Kumar');
