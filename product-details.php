<?php
require_once 'assets/includes/bootstrap.php';

$product = product_find((int)($_GET['id'] ?? 0));

if (!$product) {
    http_response_code(404);
    $pageTitle = 'Magazine not found';
    include 'assets/includes/navbar.php';
    echo '<div class="wrap page"><div class="empty"><i class="fa-solid fa-book" aria-hidden="true"></i>'
       . '<h3>Magazine not found</h3><p>It may have been removed from the shop.</p>'
       . '<a href="shop.php" class="btn btn-primary">Back to the shop</a></div></div>';
    include 'assets/includes/footer.php';
    exit();
}

$pid     = (int)$product['id'];
$gallery = array_merge([$product['image']], array_column(
    db_all('SELECT image FROM product_images WHERE product_id = ? ORDER BY sort_order, id', [$pid]), 'image'));
$stock   = (int)$product['stock'];
[$stockClass, $stockText] = stock_label($stock);
$inCart  = cart_qty_of_product($pid);
$maxQty  = max(1, min(99, $stock - $inCart));

$reviews = db_all('SELECT r.rating, r.comment, r.created_at, u.fullname
                   FROM reviews r JOIN users u ON u.id = r.user_id
                   WHERE r.product_id = ? ORDER BY r.created_at DESC', [$pid]);

// A customer can review once they have received this magazine
$user = current_user();
$canReview = false;
$myReview = null;
if ($user) {
    $canReview = (bool)db_value("SELECT COUNT(*) FROM order_items oi JOIN orders o ON o.id = oi.order_id
                                 WHERE o.user_id = ? AND oi.product_id = ? AND o.status = 'delivered'", [(int)$user['id'], $pid]);
    $myReview = db_one('SELECT rating, comment FROM reviews WHERE product_id = ? AND user_id = ?', [$pid, (int)$user['id']]);
}

$related = db_all(product_select_sql() . ' WHERE p.is_active = 1 AND p.id <> ? AND p.category_id <=> ? ORDER BY RAND() LIMIT 4',
    [$pid, $product['category_id'] === null ? null : (int)$product['category_id']]);

$pageTitle = $product['name'];
$active = 'shop';
include 'assets/includes/navbar.php';
?>

<div class="wrap page">
    <?php echo flash_render(); ?>
    <nav class="crumbs" aria-label="Breadcrumb">
        <a href="shop.php">Shop</a>
        <?php if (!empty($product['category_slug'])): ?>
            / <a href="shop.php?category=<?php echo e($product['category_slug']); ?>"><?php echo e($product['category_name']); ?></a>
        <?php endif; ?>
        / <?php echo e($product['name']); ?>
    </nav>

    <div class="pd-grid">
        <div class="pd-gallery">
            <div class="pd-main">
                <img id="pd-main-img" src="<?php echo e(img_url($gallery[0])); ?>" alt="<?php echo e($product['name']); ?> magazine cover">
            </div>
            <?php if (count($gallery) > 1): ?>
                <div class="pd-thumbs">
                    <?php foreach ($gallery as $i => $img): ?>
                        <button type="button" class="<?php echo $i === 0 ? 'active' : ''; ?>" data-thumb="<?php echo e(img_url($img)); ?>" aria-label="Show image <?php echo $i + 1; ?>">
                            <img src="<?php echo e(img_url($img)); ?>" alt="">
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="pd-info">
            <h1><?php echo e($product['name']); ?></h1>
            <div class="pd-rating">
                <?php if ((int)$product['review_count'] > 0): ?>
                    <?php echo stars((float)$product['avg_rating']); ?>
                    <a href="#reviews"><?php echo e(number_format((float)$product['avg_rating'], 1)); ?> from <?php echo (int)$product['review_count']; ?> <?php echo (int)$product['review_count'] === 1 ? 'review' : 'reviews'; ?></a>
                <?php else: ?>
                    <span>No reviews yet</span>
                <?php endif; ?>
            </div>
            <div class="pd-price"><?php echo price($product['price']); ?></div>
            <p class="pd-desc"><?php echo nl2br(e($product['description'])); ?></p>

            <dl class="spec-list">
                <div><dt>Pages</dt><dd><?php echo (int)$product['pages']; ?></dd></div>
                <div><dt>Size</dt><dd><?php echo e($product['size']); ?></dd></div>
                <div><dt>Paper</dt><dd><?php echo e($product['paper']); ?></dd></div>
                <div><dt>Availability</dt><dd class="<?php echo $stockClass; ?>"><?php echo e($stockText); ?></dd></div>
            </dl>

            <?php if ($stock > 0 && $inCart < $stock): ?>
                <form action="cart-action.php" method="post" data-ajax-cart>
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="product_id" value="<?php echo $pid; ?>">
                    <div class="buy-row">
                        <div class="qty">
                            <button type="button" data-step="-1" aria-label="Decrease quantity">&minus;</button>
                            <input type="number" name="quantity" value="1" min="1" max="<?php echo $maxQty; ?>" aria-label="Quantity">
                            <button type="button" data-step="1" aria-label="Increase quantity">+</button>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-bag-shopping" aria-hidden="true"></i> Add to cart</button>
                        <button type="submit" name="buy_now" value="1" class="btn btn-outline">Buy now</button>
                    </div>
                </form>
            <?php elseif ($stock > 0): ?>
                <p class="alert alert-info">You have all available copies in your cart. <a href="cart.php">View cart</a></p>
            <?php else: ?>
                <p class="alert alert-warn">This magazine is sold out right now. Check back soon.</p>
            <?php endif; ?>

            <?php if ($stock > $inCart): ?>
                <div class="customize-card">
                    <div>
                        <h3>Make it yours</h3>
                        <p>Add your own cover photo, page photos, a title and a message.</p>
                    </div>
                    <a href="customize.php?id=<?php echo $pid; ?>" class="btn btn-accent"><i class="fa-solid fa-wand-magic-sparkles" aria-hidden="true"></i> Customize</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <section class="reviews" id="reviews">
        <h2>Reviews</h2>
        <?php if ($reviews): ?>
            <div class="review-summary">
                <span class="big"><?php echo e(number_format((float)$product['avg_rating'], 1)); ?></span>
                <div><?php echo stars((float)$product['avg_rating']); ?><div class="muted small"><?php echo count($reviews); ?> <?php echo count($reviews) === 1 ? 'review' : 'reviews'; ?></div></div>
            </div>
            <div class="review-list">
                <?php foreach ($reviews as $r): ?>
                    <article class="review">
                        <div class="review-head">
                            <strong><?php echo e($r['fullname']); ?></strong>
                            <span class="muted"><?php echo e(nice_date($r['created_at'])); ?></span>
                        </div>
                        <?php echo stars((float)$r['rating']); ?>
                        <p style="margin-top: 6px;"><?php echo nl2br(e($r['comment'])); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="muted">No reviews yet. Customers can review a magazine after it has been delivered.</p>
        <?php endif; ?>

        <?php if ($canReview): ?>
            <form class="review-form" action="review-action.php" method="post">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="product_id" value="<?php echo $pid; ?>">
                <h3><?php echo $myReview ? 'Update your review' : 'Write a review'; ?></h3>
                <fieldset class="field" style="border: 0; padding: 0; margin: 0 0 14px;">
                    <legend class="label">Your rating</legend>
                    <div class="star-input">
                        <?php for ($s = 5; $s >= 1; $s--): ?>
                            <input type="radio" id="star<?php echo $s; ?>" name="rating" value="<?php echo $s; ?>" <?php echo (int)($myReview['rating'] ?? 0) === $s ? 'checked' : ''; ?> required>
                            <label for="star<?php echo $s; ?>" title="<?php echo $s; ?> stars"><span aria-hidden="true">&#9733;</span><span class="sr-only"><?php echo $s; ?> stars</span></label>
                        <?php endfor; ?>
                    </div>
                </fieldset>
                <div class="field">
                    <label for="comment">Your review</label>
                    <textarea id="comment" name="comment" rows="4" maxlength="1000" required><?php echo e($myReview['comment'] ?? ''); ?></textarea>
                </div>
                <button class="btn btn-primary" type="submit"><?php echo $myReview ? 'Update review' : 'Post review'; ?></button>
            </form>
        <?php endif; ?>
    </section>

    <?php if ($related): ?>
        <section class="section" style="padding-bottom: 0;">
            <div class="section-head"><h2>You may also like</h2></div>
            <div class="product-grid">
                <?php foreach ($related as $p) { echo product_card($p); } ?>
            </div>
        </section>
    <?php endif; ?>
</div>

<?php include 'assets/includes/footer.php'; ?>
