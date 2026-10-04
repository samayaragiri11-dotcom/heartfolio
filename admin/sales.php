<?php
require_once __DIR__ . '/../assets/includes/admin-guard.php';

// ---- Date range ----
$presets = [
    '7'    => ['Last 7 days',  date('Y-m-d', strtotime('-6 days')),  date('Y-m-d')],
    '30'   => ['Last 30 days', date('Y-m-d', strtotime('-29 days')), date('Y-m-d')],
    '90'   => ['Last 90 days', date('Y-m-d', strtotime('-89 days')), date('Y-m-d')],
    'month'=> ['This month',   date('Y-m-01'),                       date('Y-m-d')],
    'year' => ['This year',    date('Y-01-01'),                      date('Y-m-d')],
];
$range = $_GET['range'] ?? '30';
if (isset($presets[$range])) {
    [, $from, $to] = $presets[$range];
} else {
    $range = 'custom';
    $from = preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['from'] ?? '') ? $_GET['from'] : date('Y-m-d', strtotime('-29 days'));
    $to   = preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['to'] ?? '') ? $_GET['to'] : date('Y-m-d');
    if ($from > $to) {
        [$from, $to] = [$to, $from];
    }
    // Keep the daily chart readable
    if ((strtotime($to) - strtotime($from)) / 86400 > 366) {
        $from = date('Y-m-d', strtotime($to . ' -365 days'));
    }
}

$inRange = "o.status <> 'cancelled' AND DATE(o.created_at) BETWEEN ? AND ?";
$p = [$from, $to];

$summary = db_one("SELECT COUNT(*) AS orders, COALESCE(SUM(o.total), 0) AS revenue, COALESCE(SUM(o.shipping), 0) AS shipping
                   FROM orders o WHERE $inRange", $p);
$itemsSold = (int)db_value("SELECT COALESCE(SUM(oi.quantity), 0) FROM order_items oi JOIN orders o ON o.id = oi.order_id WHERE $inRange", $p);
$customOrders = (int)db_value("SELECT COUNT(DISTINCT o.id) FROM orders o JOIN order_items oi ON oi.order_id = o.id WHERE oi.is_custom = 1 AND $inRange", $p);
$cancelled = (int)db_value("SELECT COUNT(*) FROM orders o WHERE o.status = 'cancelled' AND DATE(o.created_at) BETWEEN ? AND ?", $p);
$avg = (int)$summary['orders'] ? (float)$summary['revenue'] / (int)$summary['orders'] : 0;

$byProduct = db_all("SELECT oi.product_name, SUM(oi.quantity) AS qty, SUM(oi.quantity * oi.unit_price) AS revenue
                     FROM order_items oi JOIN orders o ON o.id = oi.order_id
                     WHERE $inRange GROUP BY oi.product_name ORDER BY revenue DESC, qty DESC", $p);
$byCategory = db_all("SELECT COALESCE(oi.category_name, 'No category') AS name, SUM(oi.quantity) AS qty, SUM(oi.quantity * oi.unit_price) AS revenue
                      FROM order_items oi JOIN orders o ON o.id = oi.order_id
                      WHERE $inRange GROUP BY name ORDER BY revenue DESC", $p);
$byPayment = db_all("SELECT o.payment_method, COUNT(*) AS orders, SUM(o.total) AS revenue
                     FROM orders o WHERE $inRange GROUP BY o.payment_method ORDER BY revenue DESC", $p);
$series = admin_daily_series($from, $to);

// ---- CSV download of every order in the range ----
if (($_GET['export'] ?? '') === 'csv') {
    $rows = db_all("SELECT o.order_number, o.created_at, o.fullname, o.email, o.phone, o.city, o.payment_method, o.status,
                           o.subtotal, o.shipping, o.total,
                           (SELECT SUM(quantity) FROM order_items WHERE order_id = o.id) AS items
                    FROM orders o WHERE DATE(o.created_at) BETWEEN ? AND ? ORDER BY o.created_at", $p);
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="heartfolio-orders-' . $from . '-to-' . $to . '.csv"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF"); // so Excel reads it as UTF-8
    fputcsv($out, ['Order', 'Date', 'Customer', 'Email', 'Phone', 'City', 'Payment', 'Status', 'Items', 'Subtotal', 'Delivery', 'Total']);
    foreach ($rows as $r) {
        fputcsv($out, [$r['order_number'], $r['created_at'], $r['fullname'], $r['email'], $r['phone'], $r['city'],
            PAYMENT_METHODS[$r['payment_method']] ?? $r['payment_method'], ORDER_STATUSES[$r['status']] ?? $r['status'],
            $r['items'], $r['subtotal'], $r['shipping'], $r['total']]);
    }
    fclose($out);
    exit();
}

admin_header('Sales reports', 'sales');
?>

<form class="toolbar" method="get">
    <nav class="tabs" style="margin: 0; border: 0;" aria-label="Date range">
        <?php foreach ($presets as $key => [$label]): ?>
            <a href="sales.php?range=<?php echo $key; ?>" class="<?php echo $range === (string)$key ? 'active' : ''; ?>"><?php echo $label; ?></a>
        <?php endforeach; ?>
    </nav>
    <span class="spacer"></span>
    <input type="hidden" name="range" value="custom">
    <label for="from" class="small muted">From</label>
    <input type="date" id="from" name="from" value="<?php echo e($from); ?>">
    <label for="to" class="small muted">To</label>
    <input type="date" id="to" name="to" value="<?php echo e($to); ?>">
    <button class="btn btn-ghost" type="submit">Apply</button>
    <a class="btn btn-primary" href="<?php echo e(admin_url('sales.php', ['export' => 'csv', 'range' => $range, 'from' => $from, 'to' => $to])); ?>"><i class="fa-solid fa-download" aria-hidden="true"></i> CSV</a>
</form>

<p class="muted small" style="margin-top: -6px;"><?php echo e(nice_date($from)); ?> to <?php echo e(nice_date($to)); ?>. Revenue counts every order except cancelled ones, including delivery charges.</p>

<div class="kpis">
    <div class="kpi"><div class="label">Revenue</div><div class="value"><?php echo price($summary['revenue']); ?></div><div class="sub"><?php echo price($summary['shipping']); ?> of it delivery</div></div>
    <div class="kpi"><div class="label">Orders</div><div class="value"><?php echo (int)$summary['orders']; ?></div><div class="sub"><?php echo $cancelled; ?> cancelled (not counted)</div></div>
    <div class="kpi"><div class="label">Average order</div><div class="value"><?php echo price(round($avg)); ?></div><div class="sub">Revenue per order</div></div>
    <div class="kpi"><div class="label">Magazines sold</div><div class="value"><?php echo $itemsSold; ?></div><div class="sub"><?php echo $customOrders; ?> <?php echo $customOrders === 1 ? 'order' : 'orders'; ?> with custom photos</div></div>
</div>

<section class="panel" style="margin-bottom: 20px;">
    <h2>Revenue per day</h2>
    <?php echo admin_revenue_chart($series, 'Hover a bar to see the exact amount'); ?>
</section>

<div class="grid-2">
    <section class="panel">
        <h2>By product</h2>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Product</th><th class="num">Sold</th><th class="num">Revenue</th><th class="num">Share</th></tr></thead>
                <tbody>
                <?php $itemRevenue = array_sum(array_column($byProduct, 'revenue')); ?>
                <?php if (!$byProduct): ?><tr><td colspan="4" class="empty-row">No sales in this period.</td></tr><?php endif; ?>
                <?php foreach ($byProduct as $r): ?>
                    <tr>
                        <td><?php echo e($r['product_name']); ?></td>
                        <td class="num"><?php echo (int)$r['qty']; ?></td>
                        <td class="num"><?php echo price($r['revenue']); ?></td>
                        <td class="num"><?php echo $itemRevenue > 0 ? round($r['revenue'] / $itemRevenue * 100) : 0; ?>%</td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <div class="stack">
        <section class="panel">
            <h2>By category</h2>
            <table class="table">
                <thead><tr><th>Category</th><th class="num">Sold</th><th class="num">Revenue</th></tr></thead>
                <tbody>
                <?php if (!$byCategory): ?><tr><td colspan="3" class="empty-row">No sales in this period.</td></tr><?php endif; ?>
                <?php foreach ($byCategory as $r): ?>
                    <tr><td><?php echo e($r['name']); ?></td><td class="num"><?php echo (int)$r['qty']; ?></td><td class="num"><?php echo price($r['revenue']); ?></td></tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </section>
        <section class="panel">
            <h2>By payment method</h2>
            <table class="table">
                <thead><tr><th>Method</th><th class="num">Orders</th><th class="num">Revenue</th></tr></thead>
                <tbody>
                <?php if (!$byPayment): ?><tr><td colspan="3" class="empty-row">No sales in this period.</td></tr><?php endif; ?>
                <?php foreach ($byPayment as $r): ?>
                    <tr><td><?php echo e(PAYMENT_METHODS[$r['payment_method']] ?? $r['payment_method']); ?></td><td class="num"><?php echo (int)$r['orders']; ?></td><td class="num"><?php echo price($r['revenue']); ?></td></tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </div>
</div>

<?php admin_footer(); ?>
