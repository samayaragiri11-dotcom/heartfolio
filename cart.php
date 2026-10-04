<?php
require_once 'assets/includes/bootstrap.php';

$items  = cart_items();
$totals = cart_totals($items);

// Warn about anything that can't be bought as it stands
$problems = [];
foreach ($items as $i) {
    if (!(int)$i['is_active']) {
        $problems[] = $i['name'] . ' is no longer sold. Please remove it.';
    } elseif ((int)$i['stock'] < (int)$i['quantity']) {
        $problems[] = (int)$i['stock'] > 0
            ? 'Only ' . (int)$i['stock'] . ' ' . $i['name'] . ' left. Please lower the quantity.'
            : $i['name'] . ' is sold out. Please remove it.';
    }
}

$pageTitle = 'Your cart';
include 'assets/includes/navbar.php';
?>

<div class="wrap page">
    <div class="page-head">
        <h1>Your cart</h1>
        <?php if ($items): ?><p><?php echo cart_count(); ?> <?php echo cart_count() === 1 ? 'item' : 'items'; ?></p><?php endif; ?>
    </div>
    <?php echo flash_render(); ?>

    <?php if (!$items): ?>
        <div class="empty">
            <i class="fa-solid fa-bag-shopping" aria-hidden="true"></i>
            <h3>Your cart is empty</h3>
            <p>Find a magazine you love, then make it yours with your own photos.</p>
            <a href="shop.php" class="btn btn-primary">Browse magazines</a>
        </div>
    <?php else: ?>
        <?php foreach ($problems as $msg): ?>
            <div class="alert alert-warn" style="margin-bottom: 12px;"><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i><span><?php echo e($msg); ?></span></div>
        <?php endforeach; ?>

        <div class="two-col">
            <div class="card">
                <div class="cart-list">
                    <?php foreach ($items as $i): ?>
                        <?php $img = $i['cover_photo_id'] ? 'photo.php?id=' . (int)$i['cover_photo_id'] : img_url($i['image']); ?>
                        <div class="cart-line">
                            <a class="cart-thumb" href="product-details.php?id=<?php echo (int)$i['product_id']; ?>">
                                <img src="<?php echo e($img); ?>" alt="<?php echo e($i['name']); ?>">
                            </a>
                            <div class="cart-info">
                                <h3><a href="product-details.php?id=<?php echo (int)$i['product_id']; ?>"><?php echo e($i['name']); ?></a></h3>
                                <p class="muted"><?php echo e($i['category_name'] ?? ''); ?> &middot; <?php echo price($i['price']); ?> each</p>
                                <?php if ((int)$i['is_custom']): ?>
                                    <p class="custom-note"><i class="fa-solid fa-wand-magic-sparkles" aria-hidden="true"></i> Customized: <?php echo e(custom_summary($i)); ?>
                                        <a href="customize.php?id=<?php echo (int)$i['product_id']; ?>&amp;edit=<?php echo (int)$i['id']; ?>">Edit</a></p>
                                <?php endif; ?>
                                <div class="cart-controls">
                                    <form action="cart-action.php" method="post" data-autosubmit>
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="item_id" value="<?php echo (int)$i['id']; ?>">
                                        <div class="qty qty-sm">
                                            <button type="button" data-step="-1" aria-label="Decrease quantity of <?php echo e($i['name']); ?>">&minus;</button>
                                            <input type="number" name="quantity" value="<?php echo (int)$i['quantity']; ?>" min="1" max="99" aria-label="Quantity of <?php echo e($i['name']); ?>">
                                            <button type="button" data-step="1" aria-label="Increase quantity of <?php echo e($i['name']); ?>">+</button>
                                        </div>
                                        <noscript><button class="btn btn-sm btn-ghost" type="submit">Update</button></noscript>
                                    </form>
                                    <form action="cart-action.php" method="post"
                                          data-confirm="<?php echo (int)$i['is_custom'] ? 'Remove your customized ' . e($i['name']) . '? Its photos will be deleted.' : 'Remove ' . e($i['name']) . ' from your cart?'; ?>">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="action" value="remove">
                                        <input type="hidden" name="item_id" value="<?php echo (int)$i['id']; ?>">
                                        <button type="submit" class="btn-danger-link">Remove</button>
                                    </form>
                                </div>
                            </div>
                            <div class="cart-price"><?php echo price($i['line_total']); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <aside class="card">
                <h3>Order summary</h3>
                <div class="summary-row"><span>Subtotal</span><strong><?php echo price($totals['subtotal']); ?></strong></div>
                <div class="summary-row"><span>Delivery</span><strong><?php echo price($totals['shipping']); ?></strong></div>
                <div class="summary-row summary-total"><span>Total</span><span><?php echo price($totals['total']); ?></span></div>
                <?php if ($problems): ?>
                    <button class="btn btn-primary btn-block" style="margin-top: 18px;" disabled>Fix the items above to check out</button>
                <?php else: ?>
                    <a href="checkout.php" class="btn btn-primary btn-block" style="margin-top: 18px;">Checkout</a>
                <?php endif; ?>
                <a href="shop.php" class="btn btn-ghost btn-block" style="margin-top: 10px;">Continue shopping</a>
            </aside>
        </div>
    <?php endif; ?>
</div>

<?php include 'assets/includes/footer.php'; ?>
