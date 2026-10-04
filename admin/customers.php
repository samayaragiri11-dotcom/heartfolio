<?php
require_once __DIR__ . '/../assets/includes/admin-guard.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int)($_POST['id'] ?? 0);
    $u = db_one('SELECT id, fullname, role FROM users WHERE id = ?', [$id]);
    $action = $_POST['action'] ?? '';

    if (!$u) {
        flash('error', 'That account no longer exists.');
        redirect('customers.php');
    }
    if ($action === 'set_password') {
        $pw = (string)($_POST['password'] ?? '');
        if (strlen($pw) < 8) {
            flash('error', 'Temporary passwords need at least 8 characters.');
        } else {
            db_exec('UPDATE users SET password = ? WHERE id = ?', [password_hash($pw, PASSWORD_DEFAULT), $id]);
            // Close any open reset requests from this customer
            db_exec("UPDATE messages SET is_read = 1 WHERE type = 'password_reset' AND user_id = ?", [$id]);
            flash('success', 'New password set for ' . $u['fullname'] . '. Share it with them privately and ask them to change it after logging in.');
        }
    } elseif ($action === 'toggle_admin') {
        if ($id === (int)$adminUser['id']) {
            flash('error', 'You can\'t remove your own admin access.');
        } else {
            $newRole = $u['role'] === 'admin' ? 'customer' : 'admin';
            db_exec('UPDATE users SET role = ? WHERE id = ?', [$newRole, $id]);
            flash('success', $u['fullname'] . ($newRole === 'admin' ? ' is now an admin.' : ' is no longer an admin.'));
        }
    }
    redirect('customers.php?view=' . $id);
}

// ---- Single customer ----
if (!empty($_GET['view'])) {
    $c = db_one('SELECT id, fullname, email, phone, role, created_at FROM users WHERE id = ?', [(int)$_GET['view']]);
    if (!$c) {
        flash('error', 'That account no longer exists.');
        redirect('customers.php');
    }
    $orders = db_all('SELECT order_number, created_at, total, status FROM orders WHERE user_id = ? ORDER BY created_at DESC', [(int)$c['id']]);
    $spent = 0;
    foreach ($orders as $o) {
        if ($o['status'] !== 'cancelled') {
            $spent += (float)$o['total'];
        }
    }
    $resetOpen = (int)db_value("SELECT COUNT(*) FROM messages WHERE type = 'password_reset' AND user_id = ? AND is_read = 0", [(int)$c['id']]);

    admin_header($c['fullname'], 'customers');
    ?>
    <p><a href="customers.php">&larr; All customers</a></p>
    <?php if ($resetOpen): ?>
        <div class="alert alert-warn" style="margin-bottom: 16px;"><i class="fa-solid fa-key" aria-hidden="true"></i><span>This customer asked for a password reset. Set a temporary password below, then tell them by phone or email.</span></div>
    <?php endif; ?>
    <div class="grid-2">
        <section class="panel">
            <h2>Orders</h2>
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Order</th><th>Date</th><th class="num">Total</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php if (!$orders): ?><tr><td colspan="4" class="empty-row">No orders yet.</td></tr><?php endif; ?>
                    <?php foreach ($orders as $o): ?>
                        <tr>
                            <td><a class="row-title" href="order-view.php?n=<?php echo urlencode($o['order_number']); ?>"><?php echo e($o['order_number']); ?></a></td>
                            <td class="muted"><?php echo e(nice_date($o['created_at'])); ?></td>
                            <td class="num"><?php echo price($o['total']); ?></td>
                            <td><?php echo status_badge($o['status']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
        <div class="stack">
            <section class="panel">
                <h2>Details</h2>
                <dl class="kv">
                    <dt>Email</dt><dd><a href="mailto:<?php echo e($c['email']); ?>"><?php echo e($c['email']); ?></a></dd>
                    <dt>Phone</dt><dd><?php echo e($c['phone']); ?></dd>
                    <dt>Joined</dt><dd><?php echo e(nice_date($c['created_at'])); ?></dd>
                    <dt>Orders</dt><dd><?php echo count($orders); ?></dd>
                    <dt>Spent</dt><dd><?php echo price($spent); ?></dd>
                    <dt>Role</dt><dd><?php echo $c['role'] === 'admin' ? '<span class="pill pill-pink">Admin</span>' : 'Customer'; ?></dd>
                </dl>
            </section>
            <form class="panel" method="post">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="id" value="<?php echo (int)$c['id']; ?>">
                <input type="hidden" name="action" value="set_password">
                <h2>Set a new password</h2>
                <p class="muted small">For customers who forgot theirs. They can change it from My account after logging in.</p>
                <div class="field">
                    <label for="password">Temporary password</label>
                    <input type="text" id="password" name="password" minlength="8" required value="<?php echo e('Heart' . random_int(1000, 9999) . '!'); ?>" autocomplete="off">
                </div>
                <button class="btn btn-primary" type="submit">Set password</button>
            </form>
            <?php if ((int)$c['id'] !== (int)$adminUser['id']): ?>
                <form class="panel" method="post" data-confirm="<?php echo $c['role'] === 'admin' ? 'Remove admin access from ' . e($c['fullname']) . '?' : 'Give ' . e($c['fullname']) . ' full admin access?'; ?>">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="id" value="<?php echo (int)$c['id']; ?>">
                    <input type="hidden" name="action" value="toggle_admin">
                    <h2>Admin access</h2>
                    <p class="muted small">Admins can see every order and change the shop.</p>
                    <button class="btn <?php echo $c['role'] === 'admin' ? 'btn-danger' : 'btn-ghost'; ?>" type="submit"><?php echo $c['role'] === 'admin' ? 'Remove admin access' : 'Make admin'; ?></button>
                </form>
            <?php endif; ?>
        </div>
    </div>
    <?php
    admin_footer();
    exit();
}

// ---- List ----
$q = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$per = 25;
$where = '';
$params = [];
if ($q !== '') {
    $where = 'WHERE u.fullname LIKE ? OR u.email LIKE ? OR u.phone LIKE ?';
    $params = ["%$q%", "%$q%", "%$q%"];
}
$total = (int)db_value("SELECT COUNT(*) FROM users u $where", $params);
$pages = max(1, (int)ceil($total / $per));
$page = min($page, $pages);
$off = ($page - 1) * $per;
$users = db_all("SELECT u.id, u.fullname, u.email, u.phone, u.role, u.created_at,
                        (SELECT COUNT(*) FROM orders o WHERE o.user_id = u.id) AS orders,
                        (SELECT COALESCE(SUM(total), 0) FROM orders o WHERE o.user_id = u.id AND o.status <> 'cancelled') AS spent,
                        (SELECT COUNT(*) FROM messages m WHERE m.user_id = u.id AND m.type = 'password_reset' AND m.is_read = 0) AS reset_open
                 FROM users u $where ORDER BY u.created_at DESC LIMIT $per OFFSET $off", $params);

admin_header('Customers', 'customers');
?>

<div class="panel">
    <form class="toolbar" method="get">
        <label class="sr-only" for="q">Search</label>
        <input type="search" id="q" name="q" value="<?php echo e($q); ?>" placeholder="Name, email or phone">
        <button class="btn btn-ghost" type="submit">Search</button>
        <span class="spacer"></span>
        <span class="muted small"><?php echo $total; ?> <?php echo $total === 1 ? 'account' : 'accounts'; ?></span>
    </form>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Joined</th><th class="num">Orders</th><th class="num">Spent</th><th></th></tr></thead>
            <tbody>
            <?php if (!$users): ?><tr><td colspan="7" class="empty-row">No accounts found.</td></tr><?php endif; ?>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td>
                        <a class="row-title" href="customers.php?view=<?php echo (int)$u['id']; ?>"><?php echo e($u['fullname']); ?></a>
                        <?php if ($u['role'] === 'admin'): ?> <span class="pill pill-pink">Admin</span><?php endif; ?>
                        <?php if ((int)$u['reset_open']): ?> <span class="pill low-stock">Reset requested</span><?php endif; ?>
                    </td>
                    <td><?php echo e($u['email']); ?></td>
                    <td class="muted"><?php echo e($u['phone']); ?></td>
                    <td class="muted"><?php echo e(nice_date($u['created_at'])); ?></td>
                    <td class="num"><?php echo (int)$u['orders']; ?></td>
                    <td class="num"><?php echo price($u['spent']); ?></td>
                    <td><a class="btn btn-ghost btn-sm" href="customers.php?view=<?php echo (int)$u['id']; ?>">View</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php echo admin_pagination($page, $pages, 'customers.php'); ?>
</div>

<?php admin_footer(); ?>
