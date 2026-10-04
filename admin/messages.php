<?php
require_once __DIR__ . '/../assets/includes/admin-guard.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int)($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';
    if ($action === 'read') {
        db_exec('UPDATE messages SET is_read = 1 WHERE id = ?', [$id]);
    } elseif ($action === 'unread') {
        db_exec('UPDATE messages SET is_read = 0 WHERE id = ?', [$id]);
    } elseif ($action === 'delete') {
        db_exec('DELETE FROM messages WHERE id = ?', [$id]);
        flash('success', 'Message deleted.');
    } elseif ($action === 'read_all') {
        db_exec('UPDATE messages SET is_read = 1 WHERE is_read = 0');
        flash('success', 'All messages marked as read.');
    }
    redirect(back_url('messages.php'));
}

$filter = $_GET['filter'] ?? '';
$where = '';
if ($filter === 'unread') {
    $where = 'WHERE m.is_read = 0';
} elseif ($filter === 'reset') {
    $where = "WHERE m.type = 'password_reset'";
}
$messages = db_all("SELECT m.* FROM messages m $where ORDER BY m.is_read ASC, m.created_at DESC LIMIT 200");
$unread = (int)db_value('SELECT COUNT(*) FROM messages WHERE is_read = 0');

admin_header('Messages', 'messages');
?>

<div class="panel">
    <div class="panel-head">
        <nav class="tabs" style="margin: 0; border: 0;" aria-label="Filter">
            <a href="messages.php" class="<?php echo $filter === '' ? 'active' : ''; ?>">All</a>
            <a href="messages.php?filter=unread" class="<?php echo $filter === 'unread' ? 'active' : ''; ?>">Unread <span><?php echo $unread; ?></span></a>
            <a href="messages.php?filter=reset" class="<?php echo $filter === 'reset' ? 'active' : ''; ?>">Password resets</a>
        </nav>
        <?php if ($unread): ?>
            <form method="post" class="inline">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="read_all">
                <button class="btn btn-ghost btn-sm" type="submit">Mark all read</button>
            </form>
        <?php endif; ?>
    </div>

    <?php if (!$messages): ?>
        <p class="empty-row" style="text-align: center;">No messages here. Contact form messages and password reset requests appear in this list.</p>
    <?php endif; ?>

    <div class="table-wrap">
        <table class="table">
            <tbody>
            <?php foreach ($messages as $m): ?>
                <tr class="<?php echo (int)$m['is_read'] ? '' : 'unread'; ?>">
                    <td>
                        <details class="msg">
                            <summary>
                                <?php if ($m['type'] === 'password_reset'): ?><span class="pill low-stock">Password reset</span> <?php endif; ?>
                                <?php echo e($m['subject']); ?>
                                <span class="muted small" style="font-weight: 400;"> from <?php echo e($m['name']); ?> &middot; <?php echo e(nice_date($m['created_at'], true)); ?></span>
                            </summary>
                            <div class="msg-body"><?php echo e($m['message']); ?></div>
                            <div class="btn-row">
                                <?php if ($m['type'] === 'password_reset' && $m['user_id']): ?>
                                    <a class="btn btn-primary btn-sm" href="customers.php?view=<?php echo (int)$m['user_id']; ?>">Set a new password</a>
                                <?php else: ?>
                                    <a class="btn btn-primary btn-sm" href="mailto:<?php echo e($m['email']); ?>?subject=<?php echo rawurlencode('Re: ' . $m['subject']); ?>">Reply by email</a>
                                <?php endif; ?>
                                <span class="muted small"><?php echo e($m['email']); ?></span>
                                <span style="flex: 1;"></span>
                                <form method="post" class="inline">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="id" value="<?php echo (int)$m['id']; ?>">
                                    <input type="hidden" name="action" value="<?php echo (int)$m['is_read'] ? 'unread' : 'read'; ?>">
                                    <button class="btn btn-ghost btn-sm" type="submit">Mark <?php echo (int)$m['is_read'] ? 'unread' : 'read'; ?></button>
                                </form>
                                <form method="post" class="inline" data-confirm="Delete this message?">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="id" value="<?php echo (int)$m['id']; ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button class="btn btn-danger btn-sm" type="submit">Delete</button>
                                </form>
                            </div>
                        </details>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php admin_footer(); ?>
