<?php
/*
 * Shopping cart stored in the database (cart_items table).
 *
 * - Logged-in customers: rows belong to their user_id, so the cart follows
 *   them to any device.
 * - Guests: rows belong to a random key kept in their session. When they
 *   log in, those rows move to their account (cart_merge_guest).
 *
 * Customized magazines are always their own row, with photos in custom_photos.
 */

const CUSTOM_PHOTO_DIR = 'uploads/custom';

// Returns [SQL condition, params] that selects the current visitor's cart rows
function cart_owner(): array {
    if (!empty($_SESSION['user_id'])) {
        return ['ci.user_id = ?', [(int)$_SESSION['user_id']]];
    }
    if (empty($_SESSION['cart_key'])) {
        $_SESSION['cart_key'] = bin2hex(random_bytes(16));
    }
    return ['ci.session_key = ?', [$_SESSION['cart_key']]];
}

function cart_items(): array {
    [$where, $params] = cart_owner();
    $items = db_all(
        "SELECT ci.*, p.name, p.price, p.image, p.stock, p.is_active,
                c.name AS category_name,
                (SELECT cp.id FROM custom_photos cp WHERE cp.cart_item_id = ci.id AND cp.kind = 'cover' LIMIT 1) AS cover_photo_id,
                (SELECT COUNT(*) FROM custom_photos cp WHERE cp.cart_item_id = ci.id AND cp.kind = 'page') AS page_photo_count
         FROM cart_items ci
         JOIN products p ON p.id = ci.product_id
         LEFT JOIN categories c ON c.id = p.category_id
         WHERE $where
         ORDER BY ci.id",
        $params
    );
    foreach ($items as &$item) {
        $item['line_total'] = (float)$item['price'] * (int)$item['quantity'];
    }
    return $items;
}

function cart_find(int $itemId): ?array {
    [$where, $params] = cart_owner();
    $params[] = $itemId;
    return db_one("SELECT ci.* FROM cart_items ci WHERE $where AND ci.id = ?", $params);
}

function cart_count(): int {
    if (empty($_SESSION['user_id']) && empty($_SESSION['cart_key'])) {
        return 0; // a brand-new visitor has no cart yet
    }
    [$where, $params] = cart_owner();
    return (int)db_value("SELECT COALESCE(SUM(ci.quantity), 0) FROM cart_items ci WHERE $where", $params);
}

function cart_totals(array $items): array {
    $subtotal = 0.0;
    foreach ($items as $i) {
        $subtotal += $i['line_total'];
    }
    $shipping = $items ? SHIPPING_FEE : 0;
    return ['subtotal' => $subtotal, 'shipping' => $shipping, 'total' => $subtotal + $shipping];
}

// How many of this product are already in the cart (all rows together)
function cart_qty_of_product(int $productId, int $exceptItemId = 0): int {
    [$where, $params] = cart_owner();
    $params[] = $productId;
    $params[] = $exceptItemId;
    return (int)db_value("SELECT COALESCE(SUM(ci.quantity), 0) FROM cart_items ci WHERE $where AND ci.product_id = ? AND ci.id <> ?", $params);
}

/*
 * Adds a plain (not customized) product. Returns an error message, or null on success.
 */
function cart_add(int $productId, int $qty): ?string {
    $product = product_find($productId);
    if (!$product) {
        return 'This magazine is no longer available.';
    }
    $qty = max(1, min(99, $qty));
    $already = cart_qty_of_product($productId);
    if ((int)$product['stock'] <= 0) {
        return $product['name'] . ' is out of stock.';
    }
    if ($already + $qty > (int)$product['stock']) {
        $left = (int)$product['stock'] - $already;
        return $left > 0
            ? 'Only ' . $left . ' more ' . $product['name'] . ' can be added (limited stock).'
            : 'You already have all available copies of ' . $product['name'] . ' in your cart.';
    }

    [$where, $params] = cart_owner();
    $params[] = $productId;
    $existing = db_one("SELECT ci.id FROM cart_items ci WHERE $where AND ci.product_id = ? AND ci.is_custom = 0", $params);

    if ($existing) {
        db_exec('UPDATE cart_items SET quantity = quantity + ? WHERE id = ?', [$qty, (int)$existing['id']]);
    } else {
        $userId = !empty($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
        $key    = $userId ? null : $_SESSION['cart_key'];
        db_exec('INSERT INTO cart_items (user_id, session_key, product_id, quantity) VALUES (?, ?, ?, ?)',
            [$userId, $key, $productId, $qty]);
    }
    return null;
}

// Changes a row's quantity. Returns an error message, or null on success.
function cart_set_qty(int $itemId, int $qty): ?string {
    $item = cart_find($itemId);
    if (!$item) {
        return 'That item is no longer in your cart.';
    }
    $qty = max(1, min(99, $qty));
    $product = product_find((int)$item['product_id'], false);
    $available = (int)($product['stock'] ?? 0) - cart_qty_of_product((int)$item['product_id'], $itemId);
    if ($qty > $available) {
        if ($available < 1) {
            return 'No more copies are available.';
        }
        $qty = $available;
        db_exec('UPDATE cart_items SET quantity = ? WHERE id = ?', [$qty, $itemId]);
        return 'Only ' . $available . ' available, so the quantity was set to ' . $available . '.';
    }
    db_exec('UPDATE cart_items SET quantity = ? WHERE id = ?', [$qty, $itemId]);
    return null;
}

function cart_remove(int $itemId): bool {
    $item = cart_find($itemId);
    if (!$item) {
        return false;
    }
    cart_delete_photos($itemId);
    db_exec('DELETE FROM cart_items WHERE id = ?', [$itemId]);
    return true;
}

function cart_delete_photos(int $itemId, array $keepIds = []): void {
    $photos = db_all('SELECT id, file_name FROM custom_photos WHERE cart_item_id = ?', [$itemId]);
    foreach ($photos as $ph) {
        if (in_array((int)$ph['id'], $keepIds, true)) {
            continue;
        }
        delete_upload(CUSTOM_PHOTO_DIR . '/' . $ph['file_name']);
        db_exec('DELETE FROM custom_photos WHERE id = ?', [(int)$ph['id']]);
    }
}

// Moves a guest's cart into their account after they log in or sign up
function cart_merge_guest(string $guestKey, int $userId): void {
    $rows = db_all('SELECT * FROM cart_items WHERE session_key = ?', [$guestKey]);
    foreach ($rows as $row) {
        if (!(int)$row['is_custom']) {
            $mine = db_one('SELECT id FROM cart_items WHERE user_id = ? AND product_id = ? AND is_custom = 0',
                [$userId, (int)$row['product_id']]);
            if ($mine) {
                db_exec('UPDATE cart_items SET quantity = quantity + ? WHERE id = ?', [(int)$row['quantity'], (int)$mine['id']]);
                db_exec('DELETE FROM cart_items WHERE id = ?', [(int)$row['id']]);
                continue;
            }
        }
        db_exec('UPDATE cart_items SET user_id = ?, session_key = NULL WHERE id = ?', [$userId, (int)$row['id']]);
    }
}

// Short description of a customization, e.g. "Our Trip" · 1 cover + 6 pages
function custom_summary(array $item): string {
    $parts = [];
    if (!empty($item['custom_title'])) {
        $parts[] = '"' . $item['custom_title'] . '"';
    }
    $pages = (int)($item['page_photo_count'] ?? 0);
    $parts[] = '1 cover + ' . $pages . ($pages === 1 ? ' page photo' : ' page photos');
    return implode(', ', $parts);
}
