<?php
require_once __DIR__ . '/../assets/includes/admin-guard.php';

$monthStart = date('Y-m-01');
$revenueMonth = (float)db_value("SELECT COALESCE(SUM(total), 0) FROM orders WHERE status <> 'cancelled' AND created_at >= ?", [$monthStart]);
$revenueAll   = (float)db_value("SELECT COALESCE(SUM(total), 0) FROM orders WHERE status <> 'cancelled'");
$ordersMonth  = (int)db_value("SELECT COUNT(*) FROM orders WHERE created_at >= ?", [$monthStart]);
$pending      = (int)db_value("SELECT COUNT(*) FROM orders WHERE status = 'pending'");
$processing   = (int)db_value("SELECT COUNT(*) FROM orders WHERE status = 'processing'");
$customers    = (int)db_value("SELECT COUNT(*) FROM users WHERE role = 'customer'");
$newCustomers = (int)db_value("SELECT COUNT(*) FROM users WHERE role = 'customer' AND created_at >= ?", [$monthStart]);

$series = admin_daily_series(date('Y-m-d', strtotime('-13 days')), date('Y-m-d'));

$recent = db_all('SELECT order_number, fullname, total, status, created_at FROM orders ORDER BY created_at DESC, id DESC LIMIT 7');
$lowStock = db_all('SELECT id, name, image, stock FROM products WHERE is_active = 1 AND stock <= ? ORDER BY stock ASC, name LIMIT 6', [LOW_STOCK_LIMIT]);
$top = db_all("SELECT oi.product_name, SUM(oi.quantity) AS qty, SUM(oi.quantity * oi.unit_price) AS revenue
               FROM order_items oi JOIN orders o ON o.id = oi.order_id
               WHERE o.status <> 'cancelled' AND o.created_at >= ?
               GROUP BY oi.product_name ORDER BY qty DESC LIMIT 5", [date('Y-m-d', strtotime('-29 days'))]);
$unread = (int)db_value('SELECT COUNT(*) FROM messages WHERE is_read = 0');

admin_header('Dashboard', 'dashboard');
?>

<?php if ($pending > 0 || $unread > 0): ?>
    <div class="alert alert-info" style="margin-bottom: 20px;">
        <i class="fa-solid fa-bell" aria-hidden="true"></i>
        <span>
            <?php if ($pending): ?><a href="orders.php?status=pending"><?php echo $pending; ?> new <?php echo $pending === 1 ? 'order needs' : 'orders need'; ?> confirming</a><?php endif; ?>
            <?php if ($pending && $unread): ?> &middot; <?php endif; ?>
            <?php if ($unread): ?><a href="messages.php"><?php echo $unread; ?> unread <?php echo $unread === 1 ? 'message' : 'messages'; ?></a><?php endif; ?>
        </span>
    </div>
<?php endif; ?>

<div class="kpis">
    <div class="kpi">
        <div class="label">Revenue this month</div>
        <div class="value"><?php echo price($revenueMonth); ?></div>
        <div class="sub"><?php echo price($revenueAll); ?> all time</div>
    </div>
    <div class="kpi">
        <div class="label">Orders this month</div>
        <div class="value"><?php echo $ordersMonth; ?></div>
        <div class="sub"><?php echo $processing; ?> being prepared</div>
    </div>
    <a class="kpi" href="orders.php?status=pending" style="text-decoration: none;">
        <div class="label">Waiting to confirm</div>
        <div class="value"><?php echo $pending; ?></div>
        <div class="sub">Pending orders</div>
    </a>
    <a class="kpi" href="customers.php" style="text-decoration: none;">
        <div class="label">Customers</div>
        <div class="value"><?php echo $customers; ?></div>
        <div class="sub"><?php echo $newCustomers; ?> joined this month</div>
    </a>
</div>

<div class="grid-2">
    <div class="stack">
        <section class="panel">
            <div class="panel-head">
                <h2>Revenue, last 14 days</h2>
                <a href="sales.php" class="btn btn-ghost btn-sm">Full report</a>
            </div>
            <?php echo admin_revenue_chart($series, 'Daily revenue (excludes cancelled orders)'); ?>
        </section>

        <section class="panel">
            <div class="panel-head">
                <h2>Latest orders</h2>
                <a href="orders.php" class="btn btn-ghost btn-sm">All orders</a>
            </div>
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Order</th><th>Customer</th><th>Date</th><th class="num">Total</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php if (!$recent): ?>
                        <tr><td colspan="5" class="empty-row">No orders yet. They appear here as soon as a customer checks out.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($recent as $o): ?>
                        <tr>
                            <td><a class="row-title" href="order-view.php?n=<?php echo urlencode($o['order_number']); ?>"><?php echo e($o['order_number']); ?></a></td>
                            <td><?php echo e($o['fullname']); ?></td>
                            <td class="muted"><?php echo e(nice_date($o['created_at'])); ?></td>
                            <td class="num"><?php echo price($o['total']); ?></td>
                            <td><?php echo status_badge($o['status']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <div class="stack">
        <section class="panel">
            <div class="panel-head">
                <h2>Low stock</h2>
                <a href="inventory.php?filter=low" class="btn btn-ghost btn-sm">Inventory</a>
            </div>
            <?php if (!$lowStock): ?>
                <p class="muted" style="margin: 0;">Every magazine has more than <?php echo LOW_STOCK_LIMIT; ?> copies in stock.</p>
            <?php else: ?>
                <ul class="list">
                    <?php foreach ($lowStock as $p): ?>
                        <li>
                            <span class="who"><img src="<?php echo e(img_url($p['image'])); ?>" alt=""><span><a href="product-form.php?id=<?php echo (int)$p['id']; ?>"><?php echo e($p['name']); ?></a></span></span>
                            <?php echo admin_stock_badge((int)$p['stock']); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>

        <section class="panel">
            <h2>Best sellers, last 30 days</h2>
            <?php if (!$top): ?>
                <p class="muted" style="margin: 0;">No sales in the last 30 days yet.</p>
            <?php else: ?>
                <ul class="list">
                    <?php foreach ($top as $t): ?>
                        <li><span><?php echo e($t['product_name']); ?></span><span class="muted small"><?php echo (int)$t['qty']; ?> sold &middot; <?php echo price($t['revenue']); ?></span></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>
    </div>
</div>

<?php admin_footer(); ?>
