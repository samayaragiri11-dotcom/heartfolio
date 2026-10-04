<?php
require_once 'assets/includes/bootstrap.php';

$perPage  = 12;
$catSlug  = trim($_GET['category'] ?? '');
$search   = trim($_GET['q'] ?? ($_GET['search'] ?? ''));
$sort     = $_GET['sort'] ?? 'featured';
$page     = max(1, (int)($_GET['page'] ?? 1));
$inStock  = !empty($_GET['in_stock']);

$sorts = [
    'featured'   => ['Featured',           'p.is_featured DESC, p.id ASC'],
    'newest'     => ['Newest',             'p.created_at DESC, p.id DESC'],
    'price-low'  => ['Price: low to high', 'p.price ASC, p.name ASC'],
    'price-high' => ['Price: high to low', 'p.price DESC, p.name ASC'],
    'rating'     => ['Top rated',          'avg_rating DESC, review_count DESC, p.name ASC'],
    'name'       => ['Name A to Z',        'p.name ASC'],
];
if (!isset($sorts[$sort])) {
    $sort = 'featured';
}

$categories = categories_all();
$currentCat = null;
foreach ($categories as $c) {
    if ($c['slug'] === $catSlug) {
        $currentCat = $c;
    }
}

// Build the WHERE clause from the filters
$where  = ['p.is_active = 1'];
$params = [];
if ($currentCat) {
    $where[]  = 'p.category_id = ?';
    $params[] = (int)$currentCat['id'];
}
if ($search !== '') {
    $where[]  = '(p.name LIKE ? OR p.description LIKE ? OR c.name LIKE ?)';
    $like     = '%' . $search . '%';
    array_push($params, $like, $like, $like);
}
if ($inStock) {
    $where[] = 'p.stock > 0';
}
$whereSql = ' WHERE ' . implode(' AND ', $where);

$total = (int)db_value('SELECT COUNT(*) FROM products p LEFT JOIN categories c ON c.id = p.category_id' . $whereSql, $params);
$pages = max(1, (int)ceil($total / $perPage));
$page  = min($page, $pages);
$offset = ($page - 1) * $perPage;

$products = db_all(product_select_sql() . $whereSql . ' ORDER BY ' . $sorts[$sort][1] . " LIMIT $perPage OFFSET $offset", $params);
$allCount = (int)db_value('SELECT COUNT(*) FROM products WHERE is_active = 1');

// Link that keeps the current filters and changes only what you pass
function shop_url(array $change): string {
    global $catSlug, $search, $sort, $inStock;
    $q = array_merge(['category' => $catSlug, 'q' => $search, 'sort' => $sort === 'featured' ? '' : $sort, 'in_stock' => $inStock ? '1' : ''], $change);
    $q = array_filter($q, function ($v) { return $v !== '' && $v !== null; });
    return 'shop.php' . ($q ? '?' . http_build_query($q) : '');
}

$heading = $currentCat ? $currentCat['name'] . ' magazines' : 'All magazines';
$pageTitle = $search !== '' ? 'Search: ' . $search : $heading;
$active = 'shop';
include 'assets/includes/navbar.php';
?>

<div class="wrap page">
    <div class="page-head">
        <h1><?php echo e($heading); ?></h1>
        <p><?php echo $total; ?> <?php echo $total === 1 ? 'magazine' : 'magazines'; ?><?php echo $search !== '' ? ' matching "' . e($search) . '"' : ''; ?></p>
    </div>

    <div class="chip-row mobile-cats">
        <a class="chip <?php echo !$currentCat ? 'active' : ''; ?>" href="<?php echo e(shop_url(['category' => '', 'page' => ''])); ?>">All</a>
        <?php foreach ($categories as $c): ?>
            <a class="chip <?php echo $currentCat && $currentCat['id'] === $c['id'] ? 'active' : ''; ?>" href="<?php echo e(shop_url(['category' => $c['slug'], 'page' => ''])); ?>"><?php echo e($c['name']); ?></a>
        <?php endforeach; ?>
    </div>

    <div class="shop-layout">
        <aside class="filters" aria-label="Filters">
            <div class="filter-block">
                <h3>Occasion</h3>
                <ul class="filter-list">
                    <li><a href="<?php echo e(shop_url(['category' => '', 'page' => ''])); ?>" class="<?php echo !$currentCat ? 'active' : ''; ?>">All magazines <span><?php echo $allCount; ?></span></a></li>
                    <?php foreach ($categories as $c): ?>
                        <li><a href="<?php echo e(shop_url(['category' => $c['slug'], 'page' => ''])); ?>" class="<?php echo $currentCat && $currentCat['id'] === $c['id'] ? 'active' : ''; ?>"><?php echo e($c['name']); ?> <span><?php echo (int)$c['product_count']; ?></span></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="filter-block">
                <h3>Availability</h3>
                <ul class="filter-list">
                    <li><a href="<?php echo e(shop_url(['in_stock' => $inStock ? '' : '1', 'page' => ''])); ?>" class="<?php echo $inStock ? 'active' : ''; ?>">In stock only</a></li>
                </ul>
            </div>
        </aside>

        <section>
            <div class="shop-toolbar">
                <form action="shop.php" method="get" role="search" class="search-inline" style="margin: 0; flex: 1; max-width: 420px;">
                    <?php if ($currentCat): ?><input type="hidden" name="category" value="<?php echo e($catSlug); ?>"><?php endif; ?>
                    <label for="shop-q" class="sr-only">Search</label>
                    <input type="search" id="shop-q" name="q" value="<?php echo e($search); ?>" placeholder="Search this shop">
                    <button class="btn btn-ghost" type="submit" aria-label="Search"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i></button>
                </form>
                <form action="shop.php" method="get">
                    <?php foreach (['category' => $catSlug, 'q' => $search, 'in_stock' => $inStock ? '1' : ''] as $k => $v): if ($v !== ''): ?>
                        <input type="hidden" name="<?php echo $k; ?>" value="<?php echo e($v); ?>">
                    <?php endif; endforeach; ?>
                    <label for="sort" class="small muted">Sort by</label>
                    <select id="sort" name="sort" onchange="this.form.submit()">
                        <?php foreach ($sorts as $key => [$label]): ?>
                            <option value="<?php echo $key; ?>" <?php echo $sort === $key ? 'selected' : ''; ?>><?php echo $label; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <noscript><button class="btn btn-sm btn-ghost" type="submit">Apply</button></noscript>
                </form>
            </div>

            <?php if (!$products): ?>
                <div class="empty">
                    <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                    <h3>No magazines found</h3>
                    <p>Try a different search word or occasion.</p>
                    <a href="shop.php" class="btn btn-primary">Show all magazines</a>
                </div>
            <?php else: ?>
                <div class="product-grid">
                    <?php foreach ($products as $p) { echo product_card($p); } ?>
                </div>

                <?php if ($pages > 1): ?>
                    <nav class="pagination" aria-label="Pages">
                        <?php for ($i = 1; $i <= $pages; $i++): ?>
                            <?php if ($i === $page): ?>
                                <span class="current" aria-current="page"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="<?php echo e(shop_url(['page' => $i])); ?>"><?php echo $i; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </section>
    </div>
</div>

<?php include 'assets/includes/footer.php'; ?>
