<?php
require_once 'assets/includes/bootstrap.php';

$featured = db_all(product_select_sql() . ' WHERE p.is_active = 1 AND p.is_featured = 1 ORDER BY p.id LIMIT 4');
if (count($featured) < 4) {
    // Not enough featured picks yet: fill up with the newest magazines
    $featured = db_all(product_select_sql() . ' WHERE p.is_active = 1 ORDER BY p.is_featured DESC, p.id DESC LIMIT 4');
}
$fan = array_slice($featured, 0, 3);
$newest = db_all(product_select_sql() . ' WHERE p.is_active = 1 ORDER BY p.created_at DESC, p.id DESC LIMIT 4');
$cats = array_filter(categories_all(), function ($c) { return (int)$c['product_count'] > 0; });

$pageTitle = 'Personalized magazines';
$active = 'home';
include 'assets/includes/navbar.php';
?>

<section class="hero">
    <div class="wrap hero-grid">
        <div>
            <h1>Your memories, our magazines.</h1>
            <p class="hero-lead">Turn your favourite photos and words into a printed magazine for a friend, a partner or the whole family.</p>
            <div class="hero-actions">
                <a href="shop.php" class="btn btn-primary">Shop magazines</a>
                <a href="#how" class="btn btn-ghost">How it works</a>
            </div>
        </div>
        <?php if ($fan): ?>
            <div class="cover-fan" aria-hidden="true">
                <?php foreach ($fan as $p): ?>
                    <a href="product-details.php?id=<?php echo (int)$p['id']; ?>" tabindex="-1">
                        <img src="<?php echo e(img_url($p['image'])); ?>" alt="">
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<div class="wrap">
    <div class="promises">
        <div class="promise">
            <i class="fa-solid fa-images" aria-hidden="true"></i>
            <div><h3>Made from your photos</h3><p>Upload a cover and up to <?php echo MAX_PAGE_PHOTOS; ?> pages.</p></div>
        </div>
        <div class="promise">
            <i class="fa-solid fa-book-open" aria-hidden="true"></i>
            <div><h3>Premium print</h3><p>24 glossy pages, 8.5" x 11".</p></div>
        </div>
        <div class="promise">
            <i class="fa-solid fa-truck-fast" aria-hidden="true"></i>
            <div><h3>Delivered to your door</h3><p>Flat <?php echo price(SHIPPING_FEE); ?> delivery, cash on delivery available.</p></div>
        </div>
    </div>
</div>

<section class="section">
    <div class="wrap">
        <div class="section-head">
            <div>
                <h2>Featured magazines</h2>
                <p>Our most loved designs right now.</p>
            </div>
            <a href="shop.php">See all</a>
        </div>
        <div class="product-grid">
            <?php foreach ($featured as $p) { echo product_card($p); } ?>
        </div>
    </div>
</section>

<?php if ($cats): ?>
<section class="section" style="padding-top: 0;">
    <div class="wrap">
        <div class="section-head"><h2>Shop by occasion</h2></div>
        <div class="chip-row">
            <?php foreach ($cats as $c): ?>
                <a class="chip" href="shop.php?category=<?php echo e($c['slug']); ?>"><?php echo e($c['name']); ?> <span><?php echo (int)$c['product_count']; ?></span></a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="section" id="how" style="padding-top: 8px;">
    <div class="wrap">
        <div class="band">
            <h2 style="margin-bottom: 26px;">How it works</h2>
            <ol class="steps-list">
                <li><h3>Pick a design</h3><p>Choose a magazine for the occasion: friendship, birthday, love, family and more.</p></li>
                <li><h3>Make it yours</h3><p>Upload your cover photo and page photos, then add a title, names and a message.</p></li>
                <li><h3>We print and deliver</h3><p>We print it on premium glossy paper and track it all the way to your door.</p></li>
            </ol>
        </div>
    </div>
</section>

<?php if ($newest): ?>
<section class="section">
    <div class="wrap">
        <div class="section-head">
            <div><h2>New arrivals</h2><p>The latest designs in the shop.</p></div>
            <a href="shop.php?sort=newest">See newest</a>
        </div>
        <div class="product-grid">
            <?php foreach ($newest as $p) { echo product_card($p); } ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php include 'assets/includes/footer.php'; ?>
