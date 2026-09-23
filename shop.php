<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once 'assets/includes/products-data.php';

$category = $_GET['category'] ?? '';
$sort     = $_GET['sort'] ?? '';
$search   = trim($_GET['search'] ?? '');

if (!isset($CATEGORIES[$category])) {
    $category = '';
}

// Builds a shop link that keeps the current filters and changes only what you pass
$shopUrl = function (array $changes) use ($category, $sort, $search) {
    $params = array_merge(['category' => $category, 'sort' => $sort, 'search' => $search], $changes);
    $params = array_filter($params, function ($v) { return $v !== '' && $v !== null; });
    return 'shop.php' . ($params ? '?' . http_build_query($params) : '');
};

// 1. Filter by category
$list = array_values($PRODUCTS);
if ($category !== '') {
    $list = array_values(array_filter($list, function ($p) use ($category) {
        return $p['category'] === $category;
    }));
}

// 2. Filter by search text
if ($search !== '') {
    $list = array_values(array_filter($list, function ($p) use ($search) {
        $haystack = $p['name'] . ' ' . $p['description'] . ' ' . hf_category_label($p['category']);
        return stripos($haystack, $search) !== false;
    }));
}

// 3. Sort
switch ($sort) {
    case 'price-low':
        usort($list, function ($a, $b) { return $a['price'] <=> $b['price']; });
        break;
    case 'price-high':
        usort($list, function ($a, $b) { return $b['price'] <=> $a['price']; });
        break;
    case 'name':
        usort($list, function ($a, $b) { return strcasecmp($a['name'], $b['name']); });
        break;
    case 'newest':
        usort($list, function ($a, $b) { return $b['id'] <=> $a['id']; });
        break;
    default:
        $sort = '';
}

$title = $category !== '' ? hf_category_label($category) . ' Magazines' : 'All Magazines';

include 'assets/includes/navbar.php';
?>

<div class="container shop-container">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-section">
            <h3>Categories</h3>
            <ul>
                <li><a href="<?php echo hf_e($shopUrl(['category' => ''])); ?>" class="<?php echo $category === '' ? 'active' : ''; ?>">All Magazines</a></li>
                <?php foreach ($CATEGORIES as $slug => $label): ?>
                    <li><a href="<?php echo hf_e($shopUrl(['category' => $slug])); ?>" class="<?php echo $category === $slug ? 'active' : ''; ?>"><?php echo hf_e($label); ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="sidebar-section">
            <h3>Sort By</h3>
            <ul>
                <?php
                $sorts = ['price-low' => 'Price: Low to High', 'price-high' => 'Price: High to Low', 'name' => 'Name', 'newest' => 'Newest'];
                foreach ($sorts as $key => $label): ?>
                    <li><a href="<?php echo hf_e($shopUrl(['sort' => $key])); ?>" class="<?php echo $sort === $key ? 'active' : ''; ?>"><?php echo hf_e($label); ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </aside>

    <!-- Products -->
    <main class="shop-products">
        <div class="section-title">
            <h2><?php echo hf_e($title); ?></h2>
            <?php if ($search !== ''): ?>
                <p>Results for "<?php echo hf_e($search); ?>" &middot; <a href="<?php echo hf_e($shopUrl(['search' => ''])); ?>">Clear search</a></p>
            <?php else: ?>
                <p>Browse our collection of personalized magazines</p>
            <?php endif; ?>
        </div>

        <?php if (empty($list)): ?>
            <div style="text-align: center; padding: 40px 20px;">
                <p style="margin-bottom: 20px;">No magazines match your filters.</p>
                <a href="shop.php" class="btn btn-primary">Show all magazines</a>
            </div>
        <?php else: ?>
            <div class="product-grid">
                <?php foreach ($list as $p) { hf_product_card($p); } ?>
            </div>
        <?php endif; ?>
    </main>
</div>

<?php include 'assets/includes/footer.php'; ?>
