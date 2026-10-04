<?php
require_once 'assets/includes/bootstrap.php';
$user = require_login();

$order = order_load((string)($_GET['n'] ?? ''));
if (!$order || ((int)$order['user_id'] !== (int)$user['id'] && !is_admin())) {
    flash('error', 'We could not find that order.');
    redirect('account.php?tab=orders');
}
$placed = !empty($_GET['placed']);
$steps = ['pending', 'processing', 'shipped', 'delivered'];
$reached = [];
foreach ($order['history'] as $h) {
    $reached[$h['status']] = $h['created_at'];
}

$pageTitle = 'Order ' . $order['order_number'];
include 'assets/includes/navbar.php';
?>

<div class="wrap page">
    <?php echo flash_render(); ?>
    <?php if ($placed): ?>
        <div class="order-hero">
            <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
            <h1>Thank you, your order is placed!</h1>
            <p class="muted">Order <strong><?php echo e($order['order_number']); ?></strong>. We'll start on it right away.</p>
        </div>
    <?php else: ?>
        <nav class="crumbs" aria-label="Breadcrumb"><a href="account.php?tab=orders">My orders</a> / <?php echo e($order['order_number']); ?></nav>
        <div class="page-head" style="display: flex; gap: 14px; align-items: center; flex-wrap: wrap;">
            <h1 style="margin: 0;">Order <?php echo e($order['order_number']); ?></h1>
            <?php echo status_badge($order['status']); ?>
        </div>
    <?php endif; ?>

    <div class="two-col">
        <div class="card">
            <h3>Items</h3>
            <div class="order-items">
                <?php foreach ($order['items'] as $it): ?>
                    <?php
                    $cover = null; $pagePhotos = [];
                    foreach ($it['photos'] as $ph) {
                        if ($ph['kind'] === 'cover') { $cover = $ph; } else { $pagePhotos[] = $ph; }
                    }
                    ?>
                    <div class="order-item">
                        <img src="<?php echo e($cover ? 'photo.php?id=' . (int)$cover['id'] : img_url($it['product_image'])); ?>" alt="">
                        <div>
                            <h4><?php echo e($it['product_name']); ?></h4>
                            <div class="muted small"><?php echo price($it['unit_price']); ?> &times; <?php echo (int)$it['quantity']; ?></div>
                            <?php if ((int)$it['is_custom']): ?>
                                <div class="custom-detail">
                                    <div><strong>Title:</strong> <?php echo e($it['custom_title'] ?: $it['product_name']); ?></div>
                                    <?php if ($it['custom_names']): ?><div><strong>Names:</strong> <?php echo e($it['custom_names']); ?></div><?php endif; ?>
                                    <?php if ($it['custom_message']): ?><div><strong>Message:</strong> <?php echo nl2br(e($it['custom_message'])); ?></div><?php endif; ?>
                                </div>
                                <?php if ($pagePhotos): ?>
                                    <div class="photo-strip">
                                        <?php foreach ($pagePhotos as $ph): ?><img src="photo.php?id=<?php echo (int)$ph['id']; ?>" alt="Page photo" loading="lazy"><?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        <strong><?php echo price($it['unit_price'] * $it['quantity']); ?></strong>
                    </div>
                <?php endforeach; ?>
            </div>
            <div style="margin-top: 20px;">
                <div class="summary-row"><span>Subtotal</span><strong><?php echo price($order['subtotal']); ?></strong></div>
                <div class="summary-row"><span>Delivery</span><strong><?php echo price($order['shipping']); ?></strong></div>
                <div class="summary-row summary-total"><span>Total</span><span><?php echo price($order['total']); ?></span></div>
            </div>
        </div>

        <aside style="display: grid; gap: 20px;">
            <div class="card">
                <h3>Status</h3>
                <?php if ($order['status'] === 'cancelled'): ?>
                    <p><?php echo status_badge('cancelled'); ?></p>
                    <p class="muted small">Cancelled on <?php echo e(nice_date($reached['cancelled'] ?? $order['updated_at'], true)); ?>.</p>
                <?php else: ?>
                    <ol class="timeline">
                        <?php foreach ($steps as $s): ?>
                            <li class="<?php echo $order['status'] === $s ? 'current' : ''; ?>" <?php echo isset($reached[$s]) ? '' : 'style="opacity: .45;"'; ?>>
                                <strong><?php echo ORDER_STATUSES[$s]; ?></strong>
                                <span><?php echo isset($reached[$s]) ? e(nice_date($reached[$s], true)) : 'Not yet'; ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                <?php endif; ?>
                <?php if ($order['status'] === 'pending' && (int)$order['user_id'] === (int)$user['id']): ?>
                    <form action="order-action.php" method="post" data-confirm="Cancel this order?">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="n" value="<?php echo e($order['order_number']); ?>">
                        <button class="btn-danger-link" type="submit">Cancel order</button>
                    </form>
                <?php endif; ?>
            </div>
            <div class="card">
                <h3>Delivery</h3>
                <p style="margin: 0;"><strong><?php echo e($order['fullname']); ?></strong><br>
                    <?php echo e($order['address']); ?>, <?php echo e($order['city']); ?><br>
                    <?php echo e($order['phone']); ?><br><?php echo e($order['email']); ?></p>
                <?php if ($order['notes']): ?><p class="muted small" style="margin: 10px 0 0;">Note: <?php echo e($order['notes']); ?></p><?php endif; ?>
                <p class="muted small" style="margin: 12px 0 0;">Payment: <?php echo e(PAYMENT_METHODS[$order['payment_method']] ?? $order['payment_method']); ?> &middot; Placed <?php echo e(nice_date($order['created_at'])); ?></p>
            </div>
            <?php if ($placed): ?>
                <a href="shop.php" class="btn btn-primary btn-block">Continue shopping</a>
                <a href="account.php?tab=orders" class="btn btn-ghost btn-block">View all my orders</a>
            <?php endif; ?>
        </aside>
    </div>
</div>

<?php include 'assets/includes/footer.php'; ?>
