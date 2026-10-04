<?php
require_once __DIR__ . '/../assets/includes/admin-guard.php';

// ---- Actions: show/hide, feature, delete ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int)($_POST['id'] ?? 0);
    $product = db_one('SELECT * FROM products WHERE id = ?', [$id]);
    $action = $_POST['action'] ?? '';

    if (!$product) {
        flash('error', 'That product no longer exists.');
    } elseif ($action === 'toggle_active') {
        db_exec('UPDATE products SET is_active = 1 - is_active WHERE id = ?', [$id]);
        flash('success', $product['name'] . ((int)$product['is_active'] ? ' is now hidden from the shop.' : ' is now visible in the shop.'));
    } elseif ($action === 'toggle_featured') {
        db_exec('UPDATE products SET is_featured = 1 - is_featured WHERE id = ?', [$id]);
        flash('success', $product['name'] . ((int)$product['is_featured'] ? ' removed from featured.' : ' added to featured on the home page.'));
    } elseif ($action === 'delete') {
        // Past orders keep their own copy of the name and price, so deleting is safe
        $images = array_column(db_all('SELECT image FROM product_images WHERE product_id = ?', [$id]), 'image');
        $images[] = $product['image'];
        db_exec('DELETE FROM products WHERE id = ?', [$id]);
        foreach ($images as $img) {
            delete_upload($img);
        }
        flash('success', $product['name'] . ' was deleted.');
    }
    redirect(back_url('products.php'));
}

// ---- Filters ----
$q      = trim($_GET['q'] ?? '');
$cat    = (int)($_GET['cat'] ?? 0);
$status = $_GET['status'] ?? '';
$page   = max(1, (int)($_GET['page'] ?? 1));
$per    = 20;

$where = ['1 = 1'];
$params = [];
if ($q !== '') {
    $where[] = '(p.name LIKE ? OR p.description LIKE ?)';
    $params[] = "%$q%";
    $params[] = "%$q%";
}
if ($cat) {
    $where[] = 'p.category_id = ?';
    $params[] = $cat;
}
$statusFilters = [
    'active'   => 'p.is_active = 1',
    'hidden'   => 'p.is_active = 0',
    'featured' => 'p.is_featured = 1',
    'low'      => 'p.stock > 0 AND p.stock <= ' . LOW_STOCK_LIMIT,
    'out'      => 'p.stock <= 0',
];
if (isset($statusFilters[$status])) {
    $where[] = $statusFilters[$status];
}
$whereSql = ' WHERE ' . implode(' AND ', $where);

$total = (int)db_value('SELECT COUNT(*) FROM products p' . $whereSql, $params);
$pages = max(1, (int)ceil($total / $per));
$page  = min($page, $pages);
$off   = ($page - 1) * $per;

$products = db_all("SELECT p.*, c.name AS category_name,
                           (SELECT COALESCE(SUM(oi.quantity), 0) FROM order_items oi JOIN orders o ON o.id = oi.order_id
                            WHERE oi.product_id = p.id AND o.status <> 'cancelled') AS sold
                    FROM products p LEFT JOIN categories c ON c.id = p.category_id
                    $whereSql ORDER BY p.created_at DESC, p.id DESC LIMIT $per OFFSET $off", $params);
$categories = categories_all();

admin_header('Products', 'products');
?>

<div class="panel">
    <form class="toolbar" method="get" data-autosubmit>
        <label class="sr-only" for="q">Search</label>
        <input type="search" id="q" name="q" value="<?php echo e($q); ?>" placeholder="Search products">
        <label class="sr-only" for="cat">Category</label>
        <select id="cat" name="cat">
            <option value="">All categories</option>
            <?php foreach ($categories as $c): ?>
                <option value="<?php echo (int)$c['id']; ?>" <?php echo $cat === (int)$c['id'] ? 'selected' : ''; ?>><?php echo e($c['name']); ?></option>
            <?php endforeach; ?>
        </select>
        <label class="sr-only" for="status">Show</label>
        <select id="status" name="status">
            <option value="">Any status</option>
            <?php foreach (['active' => 'Visible in shop', 'hidden' => 'Hidden', 'featured' => 'Featured', 'low' => 'Low stock', 'out' => 'Out of stock'] as $k => $label): ?>
                <option value="<?php echo $k; ?>" <?php echo $status === $k ? 'selected' : ''; ?>><?php echo $label; ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn btn-ghost" type="submit">Filter</button>
        <span class="spacer"></span>
        <a href="product-form.php" class="btn btn-primary"><i class="fa-solid fa-plus" aria-hidden="true"></i> Add product</a>
    </form>

    <div class="table-wrap">
        <table class="table">
            <thead><tr><th></th><th>Product</th><th>Category</th><th class="num">Price</th><th>Stock</th><th class="num">Sold</th><th>Shop</th><th><span class="sr-only">Actions</span></th></tr></thead>
            <tbody>
            <?php if (!$products): ?>
                <tr><td colspan="8" class="empty-row">No products match. <a href="products.php">Clear filters</a> or <a href="product-form.php">add a product</a>.</td></tr>
            <?php endif; ?>
            <?php foreach ($products as $p): ?>
                <tr>
                    <td><img class="thumb" src="<?php echo e(img_url($p['image'])); ?>" alt=""></td>
                    <td>
                        <a class="row-title" href="product-form.php?id=<?php echo (int)$p['id']; ?>"><?php echo e($p['name']); ?></a>
                        <?php if ((int)$p['is_featured']): ?> <span class="pill pill-pink">Featured</span><?php endif; ?>
                    </td>
                    <td class="muted"><?php echo e($p['category_name'] ?? 'None'); ?></td>
                    <td class="num"><?php echo price($p['price']); ?></td>
                    <td><?php echo admin_stock_badge((int)$p['stock']); ?> <span class="muted small"><?php echo (int)$p['stock']; ?></span></td>
                    <td class="num"><?php echo (int)$p['sold']; ?></td>
                    <td><?php echo (int)$p['is_active'] ? '<span class="pill in-stock">Visible</span>' : '<span class="pill pill-muted">Hidden</span>'; ?></td>
                    <td>
                        <div class="btn-row" style="justify-content: flex-end;">
                            <a class="btn btn-ghost btn-sm" href="product-form.php?id=<?php echo (int)$p['id']; ?>">Edit</a>
                            <form class="inline" method="post">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="id" value="<?php echo (int)$p['id']; ?>">
                                <input type="hidden" name="action" value="toggle_featured">
                                <button class="btn btn-ghost btn-sm" type="submit" title="<?php echo (int)$p['is_featured'] ? 'Remove from featured' : 'Feature on home page'; ?>" aria-label="<?php echo (int)$p['is_featured'] ? 'Unfeature' : 'Feature'; ?> <?php echo e($p['name']); ?>">
                                    <i class="fa-<?php echo (int)$p['is_featured'] ? 'solid' : 'regular'; ?> fa-star" aria-hidden="true"></i>
                                </button>
                            </form>
                            <form class="inline" method="post">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="id" value="<?php echo (int)$p['id']; ?>">
                                <input type="hidden" name="action" value="toggle_active">
                                <button class="btn btn-ghost btn-sm" type="submit"><?php echo (int)$p['is_active'] ? 'Hide' : 'Show'; ?></button>
                            </form>
                            <form class="inline" method="post" data-confirm="Delete <?php echo e($p['name']); ?> for good? Past orders keep their details. To just stop selling it, use Hide instead.">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="id" value="<?php echo (int)$p['id']; ?>">
                                <input type="hidden" name="action" value="delete">
                                <button class="btn btn-danger btn-sm" type="submit" aria-label="Delete <?php echo e($p['name']); ?>"><i class="fa-regular fa-trash-can" aria-hidden="true"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php echo admin_pagination($page, $pages, 'products.php'); ?>
    <p class="muted small" style="margin: 14px 0 0;"><?php echo $total; ?> <?php echo $total === 1 ? 'product' : 'products'; ?></p>
</div>

<?php admin_footer(); ?>
