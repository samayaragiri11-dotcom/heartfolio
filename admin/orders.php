<?php
require_once __DIR__ . '/../assets/includes/admin-guard.php';

$status = $_GET['status'] ?? '';
$q      = trim($_GET['q'] ?? '');
$from   = $_GET['from'] ?? '';
$to     = $_GET['to'] ?? '';
$page   = max(1, (int)($_GET['page'] ?? 1));
$per    = 20;

$where = ['1 = 1'];
$params = [];
if (isset(ORDER_STATUSES[$status])) {
    $where[] = 'o.status = ?';
    $params[] = $status;
}
if ($q !== '') {
    $where[] = '(o.order_number LIKE ? OR o.fullname LIKE ? OR o.email LIKE ? OR o.phone LIKE ?)';
    array_push($params, "%$q%", "%$q%", "%$q%", "%$q%");
}
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) {
    $where[] = 'DATE(o.created_at) >= ?';
    $params[] = $from;
}
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
    $where[] = 'DATE(o.created_at) <= ?';
    $params[] = $to;
}
$whereSql = ' WHERE ' . implode(' AND ', $where);

$total = (int)db_value('SELECT COUNT(*) FROM orders o' . $whereSql, $params);
$pages = max(1, (int)ceil($total / $per));
$page  = min($page, $pages);
$off   = ($page - 1) * $per;

$orders = db_all("SELECT o.*,
                         (SELECT SUM(quantity) FROM order_items WHERE order_id = o.id) AS item_count,
                         (SELECT COUNT(*) FROM order_items WHERE order_id = o.id AND is_custom = 1) AS custom_count
                  FROM orders o $whereSql ORDER BY o.created_at DESC, o.id DESC LIMIT $per OFFSET $off", $params);

$statusCounts = [];
foreach (db_all('SELECT status, COUNT(*) AS n FROM orders GROUP BY status') as $r) {
    $statusCounts[$r['status']] = (int)$r['n'];
}

admin_header('Orders', 'orders');
?>

<div class="panel">
    <nav class="tabs" aria-label="Order status">
        <a href="<?php echo e(admin_url('orders.php', ['status' => '', 'page' => ''])); ?>" class="<?php echo $status === '' ? 'active' : ''; ?>">All <span><?php echo array_sum($statusCounts); ?></span></a>
        <?php foreach (ORDER_STATUSES as $key => $label): ?>
            <a href="<?php echo e(admin_url('orders.php', ['status' => $key, 'page' => ''])); ?>" class="<?php echo $status === $key ? 'active' : ''; ?>"><?php echo $label; ?> <span><?php echo $statusCounts[$key] ?? 0; ?></span></a>
        <?php endforeach; ?>
    </nav>

    <form class="toolbar" method="get">
        <?php if ($status !== ''): ?><input type="hidden" name="status" value="<?php echo e($status); ?>"><?php endif; ?>
        <label class="sr-only" for="q">Search</label>
        <input type="search" id="q" name="q" value="<?php echo e($q); ?>" placeholder="Order no., name, email or phone">
        <label for="from" class="small muted">From</label>
        <input type="date" id="from" name="from" value="<?php echo e($from); ?>">
        <label for="to" class="small muted">To</label>
        <input type="date" id="to" name="to" value="<?php echo e($to); ?>">
        <button class="btn btn-ghost" type="submit">Filter</button>
        <?php if ($q !== '' || $from !== '' || $to !== ''): ?><a href="orders.php<?php echo $status ? '?status=' . e($status) : ''; ?>" class="btn btn-ghost">Clear</a><?php endif; ?>
    </form>

    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Order</th><th>Date</th><th>Customer</th><th class="num">Items</th><th class="num">Total</th><th>Payment</th><th>Status</th><th></th></tr></thead>
            <tbody>
            <?php if (!$orders): ?><tr><td colspan="8" class="empty-row">No orders match these filters.</td></tr><?php endif; ?>
            <?php foreach ($orders as $o): ?>
                <tr>
                    <td>
                        <a class="row-title" href="order-view.php?n=<?php echo urlencode($o['order_number']); ?>"><?php echo e($o['order_number']); ?></a>
                        <?php if ((int)$o['custom_count']): ?><br><span class="pill pill-pink"><i class="fa-solid fa-wand-magic-sparkles" aria-hidden="true"></i> Custom photos</span><?php endif; ?>
                    </td>
                    <td class="muted"><?php echo e(nice_date($o['created_at'], true)); ?></td>
                    <td><?php echo e($o['fullname']); ?><div class="muted small"><?php echo e($o['city']); ?></div></td>
                    <td class="num"><?php echo (int)$o['item_count']; ?></td>
                    <td class="num"><?php echo price($o['total']); ?></td>
                    <td class="small"><?php echo e(PAYMENT_METHODS[$o['payment_method']] ?? $o['payment_method']); ?></td>
                    <td><?php echo status_badge($o['status']); ?></td>
                    <td><a class="btn btn-ghost btn-sm" href="order-view.php?n=<?php echo urlencode($o['order_number']); ?>">Open</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php echo admin_pagination($page, $pages, 'orders.php'); ?>
</div>

<?php admin_footer(); ?>
