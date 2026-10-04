<?php
/*
 * Shows a customer's uploaded photo, but only to:
 *   - the customer who uploaded it (from their cart or their own order)
 *   - an admin
 */
require_once 'assets/includes/bootstrap.php';

$id = (int)($_GET['id'] ?? 0);
$photo = db_one('SELECT cp.*, ci.user_id AS cart_user, ci.session_key, o.user_id AS order_user
                 FROM custom_photos cp
                 LEFT JOIN cart_items ci ON ci.id = cp.cart_item_id
                 LEFT JOIN order_items oi ON oi.id = cp.order_item_id
                 LEFT JOIN orders o ON o.id = oi.order_id
                 WHERE cp.id = ?', [$id]);

$allowed = false;
if ($photo) {
    $me = !empty($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
    $allowed = is_admin()
        || ($me && ((int)$photo['cart_user'] === $me || (int)$photo['order_user'] === $me))
        || (!$me && !empty($_SESSION['cart_key']) && $photo['session_key'] === $_SESSION['cart_key']);
}

$path = $photo ? HF_ROOT . '/' . CUSTOM_PHOTO_DIR . '/' . basename($photo['file_name']) : '';
if (!$allowed || !is_file($path)) {
    http_response_code(404);
    exit('Photo not found');
}

$types = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'];
$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
header('Content-Type: ' . ($types[$ext] ?? 'application/octet-stream'));
header('Content-Length: ' . filesize($path));
header('Cache-Control: private, max-age=86400');
header('X-Content-Type-Options: nosniff');
if (!empty($_GET['download'])) {
    header('Content-Disposition: attachment; filename="heartfolio-photo-' . $id . '.' . $ext . '"');
}
readfile($path);
