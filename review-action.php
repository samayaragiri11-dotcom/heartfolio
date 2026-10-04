<?php
require_once 'assets/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('shop.php');
}
csrf_check();
$user = require_login();

$productId = (int)($_POST['product_id'] ?? 0);
$rating    = (int)($_POST['rating'] ?? 0);
$comment   = trim($_POST['comment'] ?? '');
$back      = 'product-details.php?id=' . $productId . '#reviews';

$received = (int)db_value("SELECT COUNT(*) FROM order_items oi JOIN orders o ON o.id = oi.order_id
                           WHERE o.user_id = ? AND oi.product_id = ? AND o.status = 'delivered'", [(int)$user['id'], $productId]);

if (!$received) {
    flash('error', 'You can review a magazine once it has been delivered to you.');
} elseif ($rating < 1 || $rating > 5) {
    flash('error', 'Choose a rating from 1 to 5 stars.');
} elseif ($comment === '' || mb_strlen($comment) > 1000) {
    flash('error', 'Write a review of up to 1000 characters.');
} else {
    db_exec('INSERT INTO reviews (product_id, user_id, rating, comment) VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE rating = VALUES(rating), comment = VALUES(comment), created_at = NOW()',
        [$productId, (int)$user['id'], $rating, $comment]);
    flash('success', 'Thanks! Your review is posted.');
}
redirect($back);
