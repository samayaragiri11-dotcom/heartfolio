<?php
require_once __DIR__ . '/../assets/includes/admin-guard.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    db_exec('DELETE FROM reviews WHERE id = ?', [(int)($_POST['id'] ?? 0)]);
    flash('success', 'Review removed.');
    redirect('reviews.php');
}

$reviews = db_all('SELECT r.*, u.fullname, u.email, p.name AS product_name
                   FROM reviews r JOIN users u ON u.id = r.user_id JOIN products p ON p.id = r.product_id
                   ORDER BY r.created_at DESC LIMIT 300');
$avg = db_value('SELECT AVG(rating) FROM reviews');

admin_header('Reviews', 'reviews');
?>

<div class="panel">
    <p class="muted small">Customers can review a magazine after their order is marked delivered. Average rating: <strong><?php echo $avg ? e(number_format((float)$avg, 1)) . ' / 5' : 'none yet'; ?></strong>.</p>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Product</th><th>Rating</th><th>Review</th><th>Customer</th><th>Date</th><th></th></tr></thead>
            <tbody>
            <?php if (!$reviews): ?><tr><td colspan="6" class="empty-row">No reviews yet.</td></tr><?php endif; ?>
            <?php foreach ($reviews as $r): ?>
                <tr>
                    <td><a href="../product-details.php?id=<?php echo (int)$r['product_id']; ?>#reviews" target="_blank" rel="noopener"><?php echo e($r['product_name']); ?></a></td>
                    <td style="white-space: nowrap; color: #c98a2b;" aria-label="<?php echo (int)$r['rating']; ?> stars"><?php echo str_repeat('&#9733;', (int)$r['rating']) . '<span style="color: var(--sand);">' . str_repeat('&#9733;', 5 - (int)$r['rating']) . '</span>'; ?></td>
                    <td style="max-width: 420px;"><?php echo nl2br(e($r['comment'])); ?></td>
                    <td><?php echo e($r['fullname']); ?></td>
                    <td class="muted"><?php echo e(nice_date($r['created_at'])); ?></td>
                    <td>
                        <form method="post" class="inline" data-confirm="Remove this review from the shop?">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">
                            <button class="btn btn-danger btn-sm" type="submit">Remove</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php admin_footer(); ?>
