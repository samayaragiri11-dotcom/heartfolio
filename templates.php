<?php
require_once 'assets/includes/bootstrap.php';

// Every active magazine, grouped by occasion
$rows = db_all(product_select_sql() . ' WHERE p.is_active = 1 ORDER BY c.sort_order, c.name, p.name');
$groups = [];
foreach ($rows as $p) {
    $groups[$p['category_name'] ?? 'Other'][] = $p;
}

$pageTitle = 'Templates';
$active = 'templates';
include 'assets/includes/navbar.php';
?>

<div class="wrap page">
    <div class="page-head">
        <h1>Magazine templates</h1>
        <p>Every design we print, by occasion. Pick one, then fill it with your own photos and words.</p>
    </div>

    <?php if (!$groups): ?>
        <div class="empty">
            <i class="fa-solid fa-book-open" aria-hidden="true"></i>
            <h3>No templates yet</h3>
            <p>New designs are on their way. Check back soon.</p>
        </div>
    <?php endif; ?>

    <?php foreach ($groups as $name => $list): ?>
        <section class="section" style="padding: 18px 0 40px;">
            <div class="section-head">
                <h2><?php echo e($name); ?></h2>
                <?php if (!empty($list[0]['category_slug'])): ?>
                    <a href="shop.php?category=<?php echo e($list[0]['category_slug']); ?>">Shop <?php echo e($name); ?></a>
                <?php endif; ?>
            </div>
            <div class="product-grid">
                <?php foreach ($list as $p) { echo product_card($p); } ?>
            </div>
        </section>
    <?php endforeach; ?>
</div>

<?php include 'assets/includes/footer.php'; ?>
