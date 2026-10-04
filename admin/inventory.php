<?php
require_once __DIR__ . '/../assets/includes/admin-guard.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int)($_POST['id'] ?? 0);
    $p = db_one('SELECT name, stock FROM products WHERE id = ?', [$id]);
    $mode = $_POST['mode'] ?? 'set';
    $amount = (int)($_POST['amount'] ?? 0);

    if (!$p) {
        flash('error', 'That product no longer exists.');
    } else {
        $new = $mode === 'add' ? (int)$p['stock'] + $amount : $amount;
        if ($new < 0 || $new > 100000) {
            flash('error', 'Stock must be between 0 and 100,000.');
        } else {
            db_exec('UPDATE products SET stock = ? WHERE id = ?', [$new, $id]);
            flash('success', $p['name'] . ': stock changed from ' . (int)$p['stock'] . ' to ' . $new . '.');
        }
    }
    redirect(back_url('inventory.php'));
}

$filter = $_GET['filter'] ?? '';
$where = 'WHERE 1 = 1';
if ($filter === 'low') {
    $where .= ' AND p.stock > 0 AND p.stock <= ' . LOW_STOCK_LIMIT;
} elseif ($filter === 'out') {
    $where .= ' AND p.stock <= 0';
}

$rows = db_all("SELECT p.id, p.name, p.image, p.stock, p.is_active, c.name AS category_name,
                       (SELECT COALESCE(SUM(oi.quantity), 0) FROM order_items oi JOIN orders o ON o.id = oi.order_id
                        WHERE oi.product_id = p.id AND o.status <> 'cancelled' AND o.created_at >= ?) AS sold30
                FROM products p LEFT JOIN categories c ON c.id = p.category_id
                $where ORDER BY p.stock ASC, p.name", [date('Y-m-d', strtotime('-29 days'))]);

$counts = db_one('SELECT SUM(stock > ' . LOW_STOCK_LIMIT . ') AS ok, SUM(stock > 0 AND stock <= ' . LOW_STOCK_LIMIT . ') AS low,
                         SUM(stock <= 0) AS out_count, COALESCE(SUM(stock), 0) AS units FROM products');

admin_header('Inventory', 'inventory');
?>

<div class="kpis">
    <div class="kpi"><div class="label">Copies in stock</div><div class="value"><?php echo (int)$counts['units']; ?></div><div class="sub">Across all products</div></div>
    <div class="kpi"><div class="label">Well stocked</div><div class="value"><?php echo (int)$counts['ok']; ?></div><div class="sub">More than <?php echo LOW_STOCK_LIMIT; ?> copies</div></div>
    <a class="kpi" href="inventory.php?filter=low" style="text-decoration: none;"><div class="label">Low stock</div><div class="value"><?php echo (int)$counts['low']; ?></div><div class="sub"><?php echo LOW_STOCK_LIMIT; ?> or fewer left</div></a>
    <a class="kpi" href="inventory.php?filter=out" style="text-decoration: none;"><div class="label">Sold out</div><div class="value"><?php echo (int)$counts['out_count']; ?></div><div class="sub">Customers can't order these</div></a>
</div>

<div class="panel">
    <nav class="tabs" aria-label="Filter">
        <a href="inventory.php" class="<?php echo $filter === '' ? 'active' : ''; ?>">All</a>
        <a href="inventory.php?filter=low" class="<?php echo $filter === 'low' ? 'active' : ''; ?>">Low stock <span><?php echo (int)$counts['low']; ?></span></a>
        <a href="inventory.php?filter=out" class="<?php echo $filter === 'out' ? 'active' : ''; ?>">Sold out <span><?php echo (int)$counts['out_count']; ?></span></a>
    </nav>
    <p class="muted small">Stock goes down automatically when an order is placed and back up if it is cancelled.</p>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th></th><th>Product</th><th>Status</th><th class="num">In stock</th><th class="num">Sold (30 days)</th><th>Restock</th><th>Set exact</th></tr></thead>
            <tbody>
            <?php if (!$rows): ?><tr><td colspan="7" class="empty-row">Nothing here. All good!</td></tr><?php endif; ?>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><img class="thumb" src="<?php echo e(img_url($r['image'])); ?>" alt=""></td>
                    <td>
                        <a class="row-title" href="product-form.php?id=<?php echo (int)$r['id']; ?>"><?php echo e($r['name']); ?></a>
                        <div class="muted small"><?php echo e($r['category_name'] ?? 'No category'); ?><?php echo (int)$r['is_active'] ? '' : ' &middot; hidden'; ?></div>
                    </td>
                    <td><?php echo admin_stock_badge((int)$r['stock']); ?></td>
                    <td class="num"><strong><?php echo (int)$r['stock']; ?></strong></td>
                    <td class="num"><?php echo (int)$r['sold30']; ?></td>
                    <td>
                        <form method="post" class="btn-row" style="flex-wrap: nowrap;">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">
                            <input type="hidden" name="mode" value="add">
                            <label class="sr-only" for="add<?php echo (int)$r['id']; ?>">Copies to add to <?php echo e($r['name']); ?></label>
                            <input type="number" id="add<?php echo (int)$r['id']; ?>" name="amount" value="10" min="1" max="10000" style="width: 80px;">
                            <button class="btn btn-ghost btn-sm" type="submit">+ Add</button>
                        </form>
                    </td>
                    <td>
                        <form method="post" class="btn-row" style="flex-wrap: nowrap;">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">
                            <input type="hidden" name="mode" value="set">
                            <label class="sr-only" for="set<?php echo (int)$r['id']; ?>">Exact stock for <?php echo e($r['name']); ?></label>
                            <input type="number" id="set<?php echo (int)$r['id']; ?>" name="amount" value="<?php echo (int)$r['stock']; ?>" min="0" max="100000" style="width: 80px;">
                            <button class="btn btn-ghost btn-sm" type="submit">Set</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php admin_footer(); ?>
