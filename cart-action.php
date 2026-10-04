<?php
/*
 * Handles every change to the cart: add, update quantity, remove.
 * Works as a normal form post, or via fetch() from main.js (returns JSON).
 */
require_once 'assets/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('cart.php');
}
csrf_check();

$action = $_POST['action'] ?? '';
$error = null;
$message = '';

switch ($action) {
    case 'add':
        $productId = (int)($_POST['product_id'] ?? 0);
        $qty = (int)($_POST['quantity'] ?? 1);
        $error = cart_add($productId, $qty);
        if (!$error) {
            $p = product_find($productId);
            $message = 'Added ' . max(1, $qty) . ' x ' . $p['name'] . ' to your cart';
            if (!empty($_POST['buy_now'])) {
                redirect('checkout.php');
            }
        }
        break;

    case 'update':
        $error = cart_set_qty((int)($_POST['item_id'] ?? 0), (int)($_POST['quantity'] ?? 1));
        $message = 'Cart updated';
        break;

    case 'remove':
        $message = cart_remove((int)($_POST['item_id'] ?? 0)) ? 'Item removed from your cart' : '';
        break;

    default:
        $error = 'Unknown cart action.';
}

if (is_ajax()) {
    json_out($error
        ? ['ok' => false, 'error' => $error, 'count' => cart_count()]
        : ['ok' => true, 'message' => $message, 'count' => cart_count()]);
}

if ($error) {
    flash('error', $error);
} elseif ($message) {
    flash('success', $message);
}

redirect($action === 'add' ? back_url('cart.php') : 'cart.php');
