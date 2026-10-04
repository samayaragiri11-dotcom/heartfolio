<?php
// Lets a customer cancel their own order while it is still pending
require_once 'assets/includes/bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('account.php?tab=orders');
}
csrf_check();
$user = require_login();

$number = (string)($_POST['n'] ?? '');
$order = db_one('SELECT * FROM orders WHERE order_number = ? AND user_id = ?', [$number, (int)$user['id']]);
if (!$order) {
    flash('error', 'We could not find that order.');
} elseif ($order['status'] !== 'pending') {
    flash('error', 'This order is already being prepared and can no longer be cancelled. Contact us if you need help.');
} else {
    $err = order_set_status($order, 'cancelled', 'Cancelled by customer');
    flash($err ? 'error' : 'success', $err ?: 'Your order is cancelled.');
}
redirect('order.php?n=' . urlencode($number));
