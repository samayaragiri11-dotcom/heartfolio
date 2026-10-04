<?php
require_once __DIR__ . '/../assets/includes/admin-guard.php';

function make_slug(string $name): string {
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $name), '-'));
    return $slug !== '' ? substr($slug, 0, 60) : 'category';
}

function unique_slug(string $name, int $exceptId = 0): string {
    $base = make_slug($name);
    $slug = $base;
    $n = 2;
    while (db_value('SELECT COUNT(*) FROM categories WHERE slug = ? AND id <> ?', [$slug, $exceptId])) {
        $slug = $base . '-' . $n++;
    }
    return $slug;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';
    $name = trim($_POST['name'] ?? '');

    if ($action === 'add') {
        if ($name === '' || mb_strlen($name) > 60) {
            flash('error', 'Enter a category name (up to 60 characters).');
        } elseif (db_value('SELECT COUNT(*) FROM categories WHERE name = ?', [$name])) {
            flash('error', 'A category called "' . $name . '" already exists.');
        } else {
            $sort = (int)db_value('SELECT COALESCE(MAX(sort_order), 0) + 1 FROM categories');
            db_exec('INSERT INTO categories (name, slug, sort_order) VALUES (?, ?, ?)', [$name, unique_slug($name), $sort]);
            flash('success', 'Added the ' . $name . ' category.');
        }
    } elseif ($action === 'update') {
        $id = (int)($_POST['id'] ?? 0);
        $sort = (int)($_POST['sort_order'] ?? 0);
        if ($name === '' || mb_strlen($name) > 60) {
            flash('error', 'Enter a category name (up to 60 characters).');
        } elseif (db_value('SELECT COUNT(*) FROM categories WHERE name = ? AND id <> ?', [$name, $id])) {
            flash('error', 'Another category already has that name.');
        } else {
            db_exec('UPDATE categories SET name = ?, slug = ?, sort_order = ? WHERE id = ?', [$name, unique_slug($name, $id), $sort, $id]);
            flash('success', 'Saved ' . $name . '.');
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $cat = db_one('SELECT name FROM categories WHERE id = ?', [$id]);
        if ($cat) {
            // Products in it are kept, just without a category
            db_exec('DELETE FROM categories WHERE id = ?', [$id]);
            flash('success', 'Deleted ' . $cat['name'] . '. Its products are kept with no category.');
        }
    }
    redirect('categories.php');
}

$cats = db_all('SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id) AS total
               FROM categories c ORDER BY c.sort_order, c.name');
$uncategorized = (int)db_value('SELECT COUNT(*) FROM products WHERE category_id IS NULL');

admin_header('Categories', 'categories');
?>

<div class="grid-2">
    <div class="panel">
        <h2>Categories</h2>
        <p class="muted small">These are the occasions customers filter by in the shop. Lower order numbers show first.</p>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Name</th><th style="width: 90px;">Order</th><th class="num">Products</th><th><span class="sr-only">Actions</span></th></tr></thead>
                <tbody>
                <?php foreach ($cats as $c): ?>
                    <tr>
                        <td colspan="2">
                            <form method="post" class="btn-row" id="cat-<?php echo (int)$c['id']; ?>" style="flex-wrap: nowrap;">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="id" value="<?php echo (int)$c['id']; ?>">
                                <label class="sr-only" for="n<?php echo (int)$c['id']; ?>">Name</label>
                                <input type="text" id="n<?php echo (int)$c['id']; ?>" name="name" value="<?php echo e($c['name']); ?>" maxlength="60" required>
                                <label class="sr-only" for="s<?php echo (int)$c['id']; ?>">Order</label>
                                <input type="number" id="s<?php echo (int)$c['id']; ?>" name="sort_order" value="<?php echo (int)$c['sort_order']; ?>" style="width: 80px;">
                                <button class="btn btn-ghost btn-sm" type="submit">Save</button>
                            </form>
                        </td>
                        <td class="num"><a href="products.php?cat=<?php echo (int)$c['id']; ?>"><?php echo (int)$c['total']; ?></a></td>
                        <td>
                            <form method="post" class="inline" data-confirm="Delete <?php echo e($c['name']); ?>?<?php echo (int)$c['total'] ? ' Its ' . (int)$c['total'] . ' products will be kept with no category.' : ''; ?>">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo (int)$c['id']; ?>">
                                <button class="btn btn-danger btn-sm" type="submit" aria-label="Delete <?php echo e($c['name']); ?>"><i class="fa-regular fa-trash-can" aria-hidden="true"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$cats): ?><tr><td colspan="4" class="empty-row">No categories yet. Add one on the right.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if ($uncategorized): ?>
            <p class="muted small" style="margin: 12px 0 0;"><?php echo $uncategorized; ?> <?php echo $uncategorized === 1 ? 'product has' : 'products have'; ?> no category.</p>
        <?php endif; ?>
    </div>

    <form class="panel" method="post">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="action" value="add">
        <h2>Add a category</h2>
        <div class="field">
            <label for="new-name">Name</label>
            <input type="text" id="new-name" name="name" maxlength="60" required placeholder="e.g. Graduation">
        </div>
        <button class="btn btn-primary" type="submit">Add category</button>
    </form>
</div>

<?php admin_footer(); ?>
