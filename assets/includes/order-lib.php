<?php
/*
 * Order helpers shared by the customer pages and the admin panel.
 */

function order_load(string $number): ?array {
    $order = db_one('SELECT * FROM orders WHERE order_number = ?', [$number]);
    if (!$order) {
        return null;
    }
    $order['items'] = db_all('SELECT * FROM order_items WHERE order_id = ? ORDER BY id', [(int)$order['id']]);
    foreach ($order['items'] as &$item) {
        $item['photos'] = (int)$item['is_custom']
            ? db_all('SELECT id, kind FROM custom_photos WHERE order_item_id = ? ORDER BY kind, position, id', [(int)$item['id']])
            : [];
    }
    unset($item);
    $order['history'] = db_all('SELECT status, note, created_at FROM order_status_history WHERE order_id = ? ORDER BY id', [(int)$order['id']]);
    return $order;
}

/*
 * Moves an order to a new status and records it in the history.
 * Cancelling puts the stock back. Returns an error message or null.
 */
function order_set_status(array $order, string $status, string $note = ''): ?string {
    if (!isset(ORDER_STATUSES[$status])) {
        return 'Unknown status.';
    }
    if ($order['status'] === $status) {
        return null;
    }
    if ($order['status'] === 'cancelled') {
        return 'This order was cancelled and can no longer change.';
    }
    $conn = db();
    $conn->begin_transaction();
    try {
        db_exec('UPDATE orders SET status = ? WHERE id = ?', [$status, (int)$order['id']]);
        if ($status === 'cancelled') {
            foreach (db_all('SELECT product_id, quantity FROM order_items WHERE order_id = ? AND product_id IS NOT NULL', [(int)$order['id']]) as $it) {
                db_exec('UPDATE products SET stock = stock + ? WHERE id = ?', [(int)$it['quantity'], (int)$it['product_id']]);
            }
        }
        db_exec('INSERT INTO order_status_history (order_id, status, note) VALUES (?, ?, ?)',
            [(int)$order['id'], $status, $note !== '' ? mb_substr($note, 0, 255) : null]);
        $conn->commit();
    } catch (mysqli_sql_exception $e) {
        $conn->rollback();
        error_log('Status change failed: ' . $e->getMessage());
        return 'The status could not be changed. Please try again.';
    }
    return null;
}
