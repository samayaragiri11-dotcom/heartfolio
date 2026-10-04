<?php
require_once 'assets/includes/bootstrap.php';
$user = require_login();

$tab = $_GET['tab'] ?? 'orders';
if (!in_array($tab, ['orders', 'profile', 'password'], true)) {
    $tab = 'orders';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'profile') {
        $fullname = trim($_POST['fullname'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $phone    = trim($_POST['phone'] ?? '');
        if ($fullname === '' || mb_strlen($fullname) > 100) {
            flash('error', 'Enter your full name.');
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('error', 'Enter a valid email address.');
        } elseif (!preg_match('/^[0-9+\- ]{7,20}$/', $phone)) {
            flash('error', 'Enter a valid phone number.');
        } elseif (db_value('SELECT COUNT(*) FROM users WHERE email = ? AND id <> ?', [$email, (int)$user['id']])) {
            flash('error', 'Another account already uses that email.');
        } else {
            db_exec('UPDATE users SET fullname = ?, email = ?, phone = ? WHERE id = ?', [$fullname, $email, $phone, (int)$user['id']]);
            $_SESSION['fullname'] = $fullname;
            $_SESSION['email'] = $email;
            flash('success', 'Your details are saved.');
        }
        redirect('account.php?tab=profile');
    }

    if ($action === 'password') {
        $current = (string)($_POST['current_password'] ?? '');
        $new     = (string)($_POST['new_password'] ?? '');
        $confirm = (string)($_POST['confirm_password'] ?? '');
        $hash    = db_value('SELECT password FROM users WHERE id = ?', [(int)$user['id']]);
        if (!password_verify($current, (string)$hash)) {
            flash('error', 'Your current password is not correct.');
        } elseif (strlen($new) < 8) {
            flash('error', 'Your new password needs at least 8 characters.');
        } elseif ($new !== $confirm) {
            flash('error', 'The two new passwords don\'t match.');
        } else {
            db_exec('UPDATE users SET password = ? WHERE id = ?', [password_hash($new, PASSWORD_DEFAULT), (int)$user['id']]);
            flash('success', 'Your password is changed.');
        }
        redirect('account.php?tab=password');
    }
}

$user = db_one('SELECT id, fullname, email, phone, role, created_at FROM users WHERE id = ?', [(int)$user['id']]);
$orders = db_all('SELECT o.order_number, o.created_at, o.total, o.status,
                         (SELECT SUM(quantity) FROM order_items WHERE order_id = o.id) AS item_count
                  FROM orders o WHERE o.user_id = ? ORDER BY o.created_at DESC, o.id DESC', [(int)$user['id']]);

$pageTitle = 'My account';
include 'assets/includes/navbar.php';
?>

<div class="wrap page">
    <div class="page-head">
        <h1>Hi, <?php echo e(strtok($user['fullname'], ' ')); ?></h1>
        <p>Member since <?php echo e(nice_date($user['created_at'])); ?></p>
    </div>
    <?php echo flash_render(); ?>

    <div class="account-layout">
        <nav class="account-nav" aria-label="Account">
            <a href="account.php?tab=orders" class="<?php echo $tab === 'orders' ? 'active' : ''; ?>"><i class="fa-solid fa-receipt" aria-hidden="true"></i>My orders</a>
            <a href="account.php?tab=profile" class="<?php echo $tab === 'profile' ? 'active' : ''; ?>"><i class="fa-regular fa-user" aria-hidden="true"></i>My details</a>
            <a href="account.php?tab=password" class="<?php echo $tab === 'password' ? 'active' : ''; ?>"><i class="fa-solid fa-lock" aria-hidden="true"></i>Password</a>
            <?php if ($user['role'] === 'admin'): ?>
                <a href="admin/index.php"><i class="fa-solid fa-gauge" aria-hidden="true"></i>Admin panel</a>
            <?php endif; ?>
            <a href="logout.php"><i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i>Log out</a>
        </nav>

        <section>
            <?php if ($tab === 'orders'): ?>
                <?php if (!$orders): ?>
                    <div class="empty">
                        <i class="fa-solid fa-receipt" aria-hidden="true"></i>
                        <h3>No orders yet</h3>
                        <p>When you place an order, you can track it here.</p>
                        <a href="shop.php" class="btn btn-primary">Start shopping</a>
                    </div>
                <?php else: ?>
                    <div class="card table-wrap">
                        <table class="table">
                            <thead><tr><th>Order</th><th>Date</th><th>Items</th><th>Total</th><th>Status</th><th><span class="sr-only">View</span></th></tr></thead>
                            <tbody>
                                <?php foreach ($orders as $o): ?>
                                    <tr>
                                        <td><strong><?php echo e($o['order_number']); ?></strong></td>
                                        <td><?php echo e(nice_date($o['created_at'])); ?></td>
                                        <td><?php echo (int)$o['item_count']; ?></td>
                                        <td><?php echo price($o['total']); ?></td>
                                        <td><?php echo status_badge($o['status']); ?></td>
                                        <td><a href="order.php?n=<?php echo urlencode($o['order_number']); ?>" class="btn btn-sm btn-ghost">View</a></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

            <?php elseif ($tab === 'profile'): ?>
                <form class="card" method="post" style="max-width: 560px;">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="action" value="profile">
                    <h2 style="font-size: 1.4rem;">My details</h2>
                    <div class="field">
                        <label for="fullname">Full name</label>
                        <input type="text" id="fullname" name="fullname" value="<?php echo e($user['fullname']); ?>" required maxlength="100">
                    </div>
                    <div class="field">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo e($user['email']); ?>" required>
                    </div>
                    <div class="field">
                        <label for="phone">Phone</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo e($user['phone']); ?>" required>
                    </div>
                    <button class="btn btn-primary" type="submit">Save details</button>
                </form>

            <?php else: ?>
                <form class="card" method="post" style="max-width: 560px;">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="action" value="password">
                    <h2 style="font-size: 1.4rem;">Change password</h2>
                    <div class="field">
                        <label for="current_password">Current password</label>
                        <input type="password" id="current_password" name="current_password" required autocomplete="current-password">
                    </div>
                    <div class="field">
                        <label for="new_password">New password</label>
                        <div class="pw-wrap">
                            <input type="password" id="new_password" name="new_password" required minlength="8" autocomplete="new-password">
                            <button type="button" class="pw-toggle" data-pw-toggle aria-label="Show password"><i class="fa-regular fa-eye" aria-hidden="true"></i></button>
                        </div>
                        <p class="hint">At least 8 characters.</p>
                    </div>
                    <div class="field">
                        <label for="confirm_password">Confirm new password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required minlength="8" autocomplete="new-password">
                    </div>
                    <button class="btn btn-primary" type="submit">Change password</button>
                </form>
            <?php endif; ?>
        </section>
    </div>
</div>

<?php include 'assets/includes/footer.php'; ?>
