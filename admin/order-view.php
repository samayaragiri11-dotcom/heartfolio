<?php
require_once __DIR__ . '/../assets/includes/admin-guard.php';

$order = order_load((string)($_GET['n'] ?? ''));
if (!$order) {
    flash('error', 'That order does not exist.');
    redirect('orders.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $new = $_POST['status'] ?? '';
    $note = trim($_POST['note'] ?? '');
    if ($new === $order['status'] && $note === '') {
        flash('info', 'Nothing changed.');
    } elseif ($new === $order['status']) {
        db_exec('INSERT INTO order_status_history (order_id, status, note) VALUES (?, ?, ?)', [(int)$order['id'], $new, mb_substr($note, 0, 255)]);
        flash('success', 'Note added.');
    } else {
        $err = order_set_status($order, $new, $note);
        flash($err ? 'error' : 'success', $err ?: 'Order ' . $order['order_number'] . ' is now ' . strtolower(ORDER_STATUSES[$new]) . '.'
            . ($new === 'cancelled' ? ' Stock has been returned.' : ''));
    }
    redirect('order-view.php?n=' . urlencode($order['order_number']));
}

$customer = $order['user_id'] ? db_one('SELECT id, fullname, email, created_at,
        (SELECT COUNT(*) FROM orders WHERE user_id = users.id) AS order_count
        FROM users WHERE id = ?', [(int)$order['user_id']]) : null;

// The usual next step, offered as a one-click button
$nextStep = ['pending' => 'processing', 'processing' => 'shipped', 'shipped' => 'delivered'][$order['status']] ?? null;
$nextLabel = ['processing' => 'Confirm and start preparing', 'shipped' => 'Mark as shipped', 'delivered' => 'Mark as delivered'];

admin_header('Order ' . $order['order_number'], 'orders');
?>

<div class="btn-row no-print" style="margin-bottom: 16px;">
    <a href="orders.php">&larr; All orders</a>
    <span style="flex: 1;"></span>
    <button class="btn btn-ghost btn-sm" type="button" onclick="window.print()"><i class="fa-solid fa-print" aria-hidden="true"></i> Print packing slip</button>
</div>

<div class="grid-2">
    <div class="stack">
        <section class="panel">
            <div class="panel-head">
                <h2>Items</h2>
                <?php echo status_badge($order['status']); ?>
            </div>
            <?php foreach ($order['items'] as $it): ?>
                <?php
                $cover = null; $pagePhotos = [];
                foreach ($it['photos'] as $ph) {
                    if ($ph['kind'] === 'cover') { $cover = $ph; } else { $pagePhotos[] = $ph; }
                }
                ?>
                <div class="order-line">
                    <img src="<?php echo e(img_url($it['product_image'])); ?>" alt="">
                    <div>
                        <strong><?php echo e($it['product_name']); ?></strong>
                        <?php if ($it['product_id']): ?><a class="small no-print" href="product-form.php?id=<?php echo (int)$it['product_id']; ?>">product</a><?php endif; ?>
                        <div class="muted small"><?php echo e($it['category_name'] ?? ''); ?> &middot; <?php echo price($it['unit_price']); ?> &times; <?php echo (int)$it['quantity']; ?></div>
                        <?php if ((int)$it['is_custom']): ?>
                            <div class="custom-box">
                                <dl>
                                    <dt>Title</dt><dd><?php echo e($it['custom_title'] ?: $it['product_name'] . ' (default)'); ?></dd>
                                    <dt>Names</dt><dd><?php echo e($it['custom_names'] ?: '-'); ?></dd>
                                    <dt>Message</dt><dd><?php echo $it['custom_message'] ? nl2br(e($it['custom_message'])) : '-'; ?></dd>
                                </dl>
                                <div class="photo-grid">
                                    <?php if ($cover): ?>
                                        <a href="../photo.php?id=<?php echo (int)$cover['id']; ?>" target="_blank" rel="noopener" title="Open full size"><img src="../photo.php?id=<?php echo (int)$cover['id']; ?>" alt="Cover photo"><span>Cover</span></a>
                                    <?php endif; ?>
                                    <?php foreach ($pagePhotos as $n => $ph): ?>
                                        <a href="../photo.php?id=<?php echo (int)$ph['id']; ?>" target="_blank" rel="noopener" title="Open full size"><img src="../photo.php?id=<?php echo (int)$ph['id']; ?>" alt="Page <?php echo $n + 1; ?>" loading="lazy"><span>p<?php echo $n + 1; ?></span></a>
                                    <?php endforeach; ?>
                                </div>
                                <p class="small no-print" style="margin: 10px 0 0;">
                                    <?php echo 1 + count($pagePhotos); ?> photos.
                                    <?php if ($cover): ?><a href="../photo.php?id=<?php echo (int)$cover['id']; ?>&amp;download=1">Download cover</a><?php endif; ?>
                                    &middot; Click a photo to open it full size.
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <strong><?php echo price($it['unit_price'] * $it['quantity']); ?></strong>
                </div>
            <?php endforeach; ?>
            <div class="totals" style="margin-top: 14px; max-width: 320px; margin-left: auto;">
                <div><span>Subtotal</span><span><?php echo price($order['subtotal']); ?></span></div>
                <div><span>Delivery</span><span><?php echo price($order['shipping']); ?></span></div>
                <div class="grand"><span>Total</span><span><?php echo price($order['total']); ?></span></div>
            </div>
        </section>

        <section class="panel">
            <h2>History</h2>
            <ul class="timeline">
                <?php foreach ($order['history'] as $h): ?>
                    <li>
                        <?php echo status_badge($h['status']); ?>
                        <?php if ($h['note']): ?> <?php echo e($h['note']); ?><?php endif; ?>
                        <small><?php echo e(nice_date($h['created_at'], true)); ?></small>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    </div>

    <div class="stack">
        <?php if ($order['status'] !== 'cancelled'): ?>
            <section class="panel no-print">
                <h2>Update status</h2>
                <?php if ($nextStep): ?>
                    <form method="post" style="margin-bottom: 16px;">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="status" value="<?php echo $nextStep; ?>">
                        <button class="btn btn-primary" type="submit" style="width: 100%;"><?php echo $nextLabel[$nextStep]; ?></button>
                    </form>
                <?php endif; ?>
                <form method="post">
                    <?php echo csrf_field(); ?>
                    <div class="field">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <?php foreach (ORDER_STATUSES as $k => $label): ?>
                                <option value="<?php echo $k; ?>" <?php echo $order['status'] === $k ? 'selected' : ''; ?>><?php echo $label; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p class="hint">Cancelling returns the items to stock and can't be undone.</p>
                    </div>
                    <div class="field">
                        <label for="note">Note <span class="muted">(optional, the customer sees it)</span></label>
                        <input type="text" id="note" name="note" maxlength="255" placeholder="e.g. Sent with Pathao, tracking 1234">
                    </div>
                    <button class="btn btn-ghost" type="submit">Save</button>
                </form>
            </section>
        <?php endif; ?>

        <section class="panel">
            <h2>Deliver to</h2>
            <dl class="kv">
                <dt>Name</dt><dd><?php echo e($order['fullname']); ?></dd>
                <dt>Phone</dt><dd><a href="tel:<?php echo e($order['phone']); ?>"><?php echo e($order['phone']); ?></a></dd>
                <dt>Email</dt><dd><a href="mailto:<?php echo e($order['email']); ?>"><?php echo e($order['email']); ?></a></dd>
                <dt>Address</dt><dd><?php echo e($order['address']); ?>, <?php echo e($order['city']); ?></dd>
                <?php if ($order['notes']): ?><dt>Notes</dt><dd><?php echo e($order['notes']); ?></dd><?php endif; ?>
                <dt>Payment</dt><dd><?php echo e(PAYMENT_METHODS[$order['payment_method']] ?? $order['payment_method']); ?></dd>
                <dt>Placed</dt><dd><?php echo e(nice_date($order['created_at'], true)); ?></dd>
            </dl>
        </section>

        <?php if ($customer): ?>
            <section class="panel no-print">
                <h2>Customer</h2>
                <p style="margin: 0;"><strong><?php echo e($customer['fullname']); ?></strong><br>
                    <span class="muted small"><?php echo (int)$customer['order_count']; ?> <?php echo (int)$customer['order_count'] === 1 ? 'order' : 'orders'; ?> &middot; joined <?php echo e(nice_date($customer['created_at'])); ?></span></p>
                <a class="btn btn-ghost btn-sm" style="margin-top: 10px;" href="customers.php?view=<?php echo (int)$customer['id']; ?>">View customer</a>
            </section>
        <?php endif; ?>
    </div>
</div>

<?php admin_footer(); ?>
