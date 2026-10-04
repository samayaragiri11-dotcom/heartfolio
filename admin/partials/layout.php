<?php
/*
 * Shared admin page frame (sidebar + top bar).
 *     admin_header('Products', 'products');
 *     ...page content...
 *     admin_footer();
 */

function admin_header(string $title, string $active): void {
    global $adminUser;
    $pending  = (int)db_value("SELECT COUNT(*) FROM orders WHERE status = 'pending'");
    $unread   = (int)db_value('SELECT COUNT(*) FROM messages WHERE is_read = 0');
    $lowStock = (int)db_value('SELECT COUNT(*) FROM products WHERE is_active = 1 AND stock <= ?', [LOW_STOCK_LIMIT]);

    $groups = [
        'Overview' => [
            'dashboard' => ['index.php', 'fa-chart-pie', 'Dashboard', 0],
            'sales'     => ['sales.php', 'fa-chart-column', 'Sales reports', 0],
        ],
        'Store' => [
            'orders'     => ['orders.php', 'fa-receipt', 'Orders', $pending],
            'products'   => ['products.php', 'fa-book-open', 'Products', 0],
            'categories' => ['categories.php', 'fa-tags', 'Categories', 0],
            'inventory'  => ['inventory.php', 'fa-boxes-stacked', 'Inventory', $lowStock],
        ],
        'People' => [
            'customers' => ['customers.php', 'fa-users', 'Customers', 0],
            'messages'  => ['messages.php', 'fa-envelope', 'Messages', $unread],
            'reviews'   => ['reviews.php', 'fa-star', 'Reviews', 0],
        ],
    ];
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($title); ?> | Heartfolio Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600&family=Manrope:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="admin.css">
</head>
<body>
<div class="admin-shell">
    <aside class="admin-side" id="admin-side">
        <a href="index.php" class="admin-brand">Heartfolio <span>Admin</span></a>
        <nav aria-label="Admin">
            <?php foreach ($groups as $groupName => $links): ?>
                <div class="side-group"><?php echo e($groupName); ?></div>
                <?php foreach ($links as $key => [$href, $icon, $label, $count]): ?>
                    <a href="<?php echo $href; ?>" class="side-link <?php echo $key === $active ? 'active' : ''; ?>">
                        <i class="fa-solid <?php echo $icon; ?>" aria-hidden="true"></i>
                        <span><?php echo $label; ?></span>
                        <?php if ($count > 0): ?><em class="side-count"><?php echo $count; ?></em><?php endif; ?>
                    </a>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </nav>
        <div class="side-foot">
            <a href="../index.php" class="side-link"><i class="fa-solid fa-store" aria-hidden="true"></i><span>View shop</span></a>
            <a href="../account.php?tab=password" class="side-link"><i class="fa-solid fa-key" aria-hidden="true"></i><span>My password</span></a>
            <a href="../logout.php" class="side-link"><i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i><span>Log out</span></a>
        </div>
    </aside>

    <div class="admin-main">
        <header class="admin-top">
            <button type="button" class="menu-btn" aria-label="Menu" onclick="document.getElementById('admin-side').classList.toggle('open')">
                <i class="fa-solid fa-bars" aria-hidden="true"></i>
            </button>
            <h1><?php echo e($title); ?></h1>
            <div class="admin-who"><i class="fa-regular fa-circle-user" aria-hidden="true"></i> <?php echo e($adminUser['fullname']); ?></div>
        </header>
        <div class="admin-body">
            <?php echo flash_render(); ?>
    <?php
}

function admin_footer(): void {
    ?>
        </div>
    </div>
</div>
<script>
// Ask before destructive actions
document.addEventListener('submit', function (e) {
    var msg = e.target.getAttribute('data-confirm');
    if (msg && !confirm(msg)) e.preventDefault();
});
// Auto-submit filter dropdowns
document.querySelectorAll('[data-autosubmit] select').forEach(function (s) {
    s.addEventListener('change', function () { s.form.submit(); });
});
</script>
</body>
</html>
    <?php
}

// Small helper for the stock badge
function admin_stock_badge(int $stock): string {
    [$cls, $text] = stock_label($stock);
    if ($cls === 'low-stock') {
        $text = 'Low: ' . $stock;
    }
    return '<span class="pill ' . $cls . '">' . e($text) . '</span>';
}

// Builds a link that keeps the current filters and changes only what you pass
function admin_url(string $page, array $change): string {
    $q = array_merge($_GET, $change);
    $q = array_filter($q, function ($v) { return $v !== '' && $v !== null; });
    return $page . ($q ? '?' . http_build_query($q) : '');
}

function admin_pagination(int $page, int $pages, string $file): string {
    if ($pages <= 1) {
        return '';
    }
    $html = '<nav class="pager" aria-label="Pages">';
    for ($i = 1; $i <= $pages; $i++) {
        $html .= $i === $page
            ? '<span class="current">' . $i . '</span>'
            : '<a href="' . e(admin_url($file, ['page' => $i])) . '">' . $i . '</a>';
    }
    return $html . '</nav>';
}

/*
 * Bar chart of revenue per day. $series is ['2026-10-01' => 1398.0, ...]
 * in date order. Hover or focus a bar to see its exact value.
 */
function admin_revenue_chart(array $series, string $caption): string {
    $max = max(1, max($series ?: [0]));
    $count = count($series);
    $labelEvery = $count > 31 ? 7 : ($count > 14 ? 3 : 1);

    $bars = '';
    $axis = '';
    $i = 0;
    foreach ($series as $date => $value) {
        $h = $value > 0 ? max(2, round($value / $max * 100, 1)) : 0;
        $label = date('M j', strtotime($date));
        $bars .= '<div class="bar-slot" tabindex="0" aria-label="' . e($label . ': ' . price($value)) . '">'
               . '<div class="bar" style="height:' . $h . '%"></div>'
               . '<div class="tip"><strong>' . e(price($value)) . '</strong>' . e(date('D, M j', strtotime($date))) . '</div></div>';
        $axis .= '<span>' . ($i % $labelEvery === 0 ? e($label) : '') . '</span>';
        $i++;
    }

    return '<figure class="chart" style="margin:0">'
         . '<div class="chart-y"><span>' . e($caption) . '</span><span>Top: ' . e(price($max)) . '</span></div>'
         . '<div class="chart-plot">' . $bars . '</div>'
         . '<div class="chart-axis" aria-hidden="true">' . $axis . '</div></figure>';
}

// Fills in the days with no sales so the chart has no gaps
function admin_daily_series(string $from, string $to): array {
    $rows = db_all("SELECT DATE(created_at) AS d, SUM(total) AS revenue
                    FROM orders WHERE status <> 'cancelled' AND DATE(created_at) BETWEEN ? AND ?
                    GROUP BY DATE(created_at)", [$from, $to]);
    $byDay = [];
    foreach ($rows as $r) {
        $byDay[$r['d']] = (float)$r['revenue'];
    }
    $series = [];
    for ($d = strtotime($from); $d <= strtotime($to); $d = strtotime('+1 day', $d)) {
        $key = date('Y-m-d', $d);
        $series[$key] = $byDay[$key] ?? 0.0;
    }
    return $series;
}
