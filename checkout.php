<?php
require_once 'assets/includes/bootstrap.php';
$user = require_login();

$items = cart_items();
if (!$items) {
    flash('info', 'Your cart is empty.');
    redirect('cart.php');
}

// Pre-fill from the account and the last order's address
$last = db_one('SELECT phone, address, city FROM orders WHERE user_id = ? ORDER BY id DESC LIMIT 1', [(int)$user['id']]);
$form = [
    'fullname' => $user['fullname'],
    'email'    => $user['email'],
    'phone'    => $last['phone'] ?? $user['phone'],
    'address'  => $last['address'] ?? '',
    'city'     => $last['city'] ?? '',
    'notes'    => '',
    'payment'  => 'cod',
];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    foreach (['fullname', 'email', 'phone', 'address', 'city', 'notes'] as $f) {
        $form[$f] = trim($_POST[$f] ?? '');
    }
    $form['payment'] = $_POST['payment'] ?? 'cod';

    if ($form['fullname'] === '' || mb_strlen($form['fullname']) > 100) $errors['fullname'] = 'Enter your full name.';
    if (!filter_var($form['email'], FILTER_VALIDATE_EMAIL))               $errors['email'] = 'Enter a valid email address.';
    if (!preg_match('/^[0-9+\- ]{7,20}$/', $form['phone']))                $errors['phone'] = 'Enter a valid phone number.';
    if ($form['address'] === '' || mb_strlen($form['address']) > 255)     $errors['address'] = 'Enter your delivery address.';
    if ($form['city'] === '' || mb_strlen($form['city']) > 80)            $errors['city'] = 'Enter your city.';
    if (mb_strlen($form['notes']) > 500)                                  $errors['notes'] = 'Keep delivery notes under 500 characters.';
    if (!isset(PAYMENT_METHODS[$form['payment']]))                        $errors['payment'] = 'Choose a payment method.';

    if (!$errors) {
        $conn = db();
        try {
            $conn->begin_transaction();

            // Lock the products so two people can't buy the last copy at once
            $needed = [];
            foreach ($items as $i) {
                $needed[(int)$i['product_id']] = ($needed[(int)$i['product_id']] ?? 0) + (int)$i['quantity'];
            }
            $ids = implode(',', array_map('intval', array_keys($needed)));
            $live = [];
            foreach ($conn->query("SELECT id, name, price, image, stock, is_active FROM products WHERE id IN ($ids) FOR UPDATE")->fetch_all(MYSQLI_ASSOC) as $p) {
                $live[(int)$p['id']] = $p;
            }
            foreach ($needed as $productId => $qty) {
                $p = $live[$productId] ?? null;
                if (!$p || !(int)$p['is_active']) {
                    throw new RuntimeException('A magazine in your cart is no longer sold. Please remove it from your cart.');
                }
                if ((int)$p['stock'] < $qty) {
                    throw new RuntimeException('Only ' . (int)$p['stock'] . ' ' . $p['name'] . ' left. Please update your cart.');
                }
            }

            // Prices always come from the database, never from the browser
            $subtotal = 0.0;
            foreach ($items as $i) {
                $subtotal += (float)$live[(int)$i['product_id']]['price'] * (int)$i['quantity'];
            }
            $shipping = SHIPPING_FEE;
            $total    = $subtotal + $shipping;
            $number   = new_order_number();

            $orderId = db_exec('INSERT INTO orders (order_number, user_id, fullname, email, phone, address, city, notes, payment_method, subtotal, shipping, total)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                [$number, (int)$user['id'], $form['fullname'], $form['email'], $form['phone'], $form['address'], $form['city'],
                 $form['notes'] !== '' ? $form['notes'] : null, $form['payment'], $subtotal, (float)$shipping, $total]);

            foreach ($items as $i) {
                $p = $live[(int)$i['product_id']];
                $orderItemId = db_exec('INSERT INTO order_items (order_id, product_id, product_name, product_image, category_name, unit_price, quantity, is_custom, custom_title, custom_names, custom_message)
                                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                    [$orderId, (int)$p['id'], $p['name'], $p['image'], $i['category_name'], (float)$p['price'], (int)$i['quantity'],
                     (int)$i['is_custom'], $i['custom_title'], $i['custom_names'], $i['custom_message']]);
                // The customer's photos now belong to the order
                db_exec('UPDATE custom_photos SET order_item_id = ?, cart_item_id = NULL WHERE cart_item_id = ?', [$orderItemId, (int)$i['id']]);
            }

            foreach ($needed as $productId => $qty) {
                db_exec('UPDATE products SET stock = stock - ? WHERE id = ?', [$qty, $productId]);
            }

            db_exec("INSERT INTO order_status_history (order_id, status, note) VALUES (?, 'pending', 'Order placed')", [$orderId]);
            db_exec('DELETE FROM cart_items WHERE user_id = ?', [(int)$user['id']]);

            $conn->commit();
            redirect('order.php?n=' . urlencode($number) . '&placed=1');
        } catch (RuntimeException $e) {
            $conn->rollback();
            flash('error', $e->getMessage());
            redirect('cart.php');
        } catch (mysqli_sql_exception $e) {
            $conn->rollback();
            error_log('Checkout failed: ' . $e->getMessage());
            $errors['general'] = 'Your order could not be placed. Please try again.';
        }
    }
}

$totals = cart_totals($items);
$pageTitle = 'Checkout';
include 'assets/includes/navbar.php';

function field_error(array $errors, string $key): string {
    return isset($errors[$key]) ? '<p class="hint" style="color: var(--bad-ink);">' . e($errors[$key]) . '</p>' : '';
}
?>

<div class="wrap page">
    <nav class="crumbs" aria-label="Breadcrumb"><a href="cart.php">Cart</a> / Checkout</nav>
    <div class="page-head"><h1>Checkout</h1></div>
    <?php echo flash_render(); ?>
    <?php if ($errors): ?>
        <div class="alert alert-error" style="margin-bottom: 20px;"><i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
            <span><?php echo e($errors['general'] ?? 'Please fix the highlighted fields.'); ?></span></div>
    <?php endif; ?>

    <form method="post" class="two-col" novalidate>
        <?php echo csrf_field(); ?>
        <div class="card">
            <h2 style="font-size: 1.4rem;">Delivery details</h2>
            <div class="field-row">
                <div class="field">
                    <label for="fullname">Full name</label>
                    <input type="text" id="fullname" name="fullname" value="<?php echo e($form['fullname']); ?>" required autocomplete="name">
                    <?php echo field_error($errors, 'fullname'); ?>
                </div>
                <div class="field">
                    <label for="phone">Phone</label>
                    <input type="tel" id="phone" name="phone" value="<?php echo e($form['phone']); ?>" required autocomplete="tel" placeholder="98XXXXXXXX">
                    <?php echo field_error($errors, 'phone'); ?>
                </div>
            </div>
            <div class="field">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo e($form['email']); ?>" required autocomplete="email">
                <?php echo field_error($errors, 'email'); ?>
            </div>
            <div class="field">
                <label for="address">Street address</label>
                <input type="text" id="address" name="address" value="<?php echo e($form['address']); ?>" required autocomplete="street-address" placeholder="House no., street, area">
                <?php echo field_error($errors, 'address'); ?>
            </div>
            <div class="field-row">
                <div class="field">
                    <label for="city">City</label>
                    <input type="text" id="city" name="city" value="<?php echo e($form['city']); ?>" required autocomplete="address-level2">
                    <?php echo field_error($errors, 'city'); ?>
                </div>
                <div class="field">
                    <label for="notes">Delivery notes <span class="muted">(optional)</span></label>
                    <input type="text" id="notes" name="notes" value="<?php echo e($form['notes']); ?>" maxlength="500" placeholder="e.g. call before arriving">
                    <?php echo field_error($errors, 'notes'); ?>
                </div>
            </div>

            <h2 style="font-size: 1.4rem; margin-top: 12px;">Payment</h2>
            <?php foreach (PAYMENT_METHODS as $key => $label): ?>
                <label class="choice">
                    <input type="radio" name="payment" value="<?php echo $key; ?>" <?php echo $form['payment'] === $key ? 'checked' : ''; ?>>
                    <span><?php echo e($label); ?>
                        <?php if ($key !== 'cod'): ?><span class="muted small" style="display: block; font-weight: 400;">We'll send payment details after confirming your order.</span><?php endif; ?>
                    </span>
                </label>
            <?php endforeach; ?>
            <?php echo field_error($errors, 'payment'); ?>
        </div>

        <aside class="card">
            <h3>Your order</h3>
            <div class="mini-items">
                <?php foreach ($items as $i): ?>
                    <div class="mini-item">
                        <img src="<?php echo e($i['cover_photo_id'] ? 'photo.php?id=' . (int)$i['cover_photo_id'] : img_url($i['image'])); ?>" alt="">
                        <div>
                            <?php echo e($i['name']); ?> &times; <?php echo (int)$i['quantity']; ?>
                            <?php if ((int)$i['is_custom']): ?><small>Customized: <?php echo e(custom_summary($i)); ?></small><?php endif; ?>
                        </div>
                        <strong><?php echo price($i['line_total']); ?></strong>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="summary-row"><span>Subtotal</span><strong><?php echo price($totals['subtotal']); ?></strong></div>
            <div class="summary-row"><span>Delivery</span><strong><?php echo price($totals['shipping']); ?></strong></div>
            <div class="summary-row summary-total"><span>Total</span><span><?php echo price($totals['total']); ?></span></div>
            <button type="submit" class="btn btn-primary btn-block" style="margin-top: 18px;">Place order</button>
            <p class="muted small" style="margin: 12px 0 0; text-align: center;">You can cancel while the order is still pending.</p>
        </aside>
    </form>
</div>

<?php include 'assets/includes/footer.php'; ?>
