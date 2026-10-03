<?php
/*
 * Shared admin layout, so the sidebar lives in ONE place.
 *
 * Usage in an admin page:
 *     admin_header('Dashboard', 'dashboard');
 *     ... page content ...
 *     admin_footer();
 */

function admin_e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function admin_header(string $title, string $active): void {
    $links = [
        'dashboard' => ['index.php',     'fa-gauge',          'Dashboard'],
        'products'  => ['products.php',  'fa-book-open',      'Products'],
        'orders'    => ['orders.php',    'fa-receipt',        'Orders'],
        'inventory' => ['inventory.php', 'fa-boxes-stacked',  'Inventory'],
        'sales'     => ['sales.php',     'fa-chart-column',   'Sales Records'],
    ];
    $adminName = $_SESSION['fullname'] ?? 'Admin';
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo admin_e($title); ?> - Heartfolio Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .admin-sidebar a i { width: 22px; margin-right: 8px; text-align: center; }
        .admin-sidebar .admin-divider { border-top: 1px solid rgba(255,255,255,0.15); margin: 18px 0; }
        .admin-sidebar .admin-user { font-size: 13px; opacity: 0.75; margin: -18px 0 24px; }
        .admin-header p { color: var(--text-light, #7a6f66); margin-top: 4px; }
        .admin-content .dashboard-card .number { line-height: 1.1; }
        .admin-content .dashboard-card a { display: inline-block; margin-top: 10px; font-size: 13px; }
        .admin-empty { text-align: center; padding: 30px 10px; color: var(--text-light, #7a6f66); }
    </style>
</head>
<body>
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <h2>Heartfolio Admin</h2>
            <p class="admin-user">Logged in as <?php echo admin_e($adminName); ?></p>
            <ul>
                <?php foreach ($links as $key => [$href, $icon, $label]): ?>
                    <li>
                        <a href="<?php echo $href; ?>" class="<?php echo $key === $active ? 'active' : ''; ?>">
                            <i class="fa-solid <?php echo $icon; ?>" aria-hidden="true"></i><?php echo $label; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="admin-divider"></div>
            <ul>
                <li><a href="../index.php"><i class="fa-solid fa-store" aria-hidden="true"></i>View Website</a></li>
                <li><a href="../logout.php"><i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i>Logout</a></li>
            </ul>
        </aside>

        <main class="admin-content">
    <?php
}

function admin_footer(): void {
    ?>
        </main>
    </div>
</body>
</html>
    <?php
}
