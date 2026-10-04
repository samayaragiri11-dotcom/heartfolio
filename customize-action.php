<?php
/*
 * Saves a customized magazine (photos + text) into the cart.
 * Called by customize.php. Photos are stored in uploads/custom and can only
 * be viewed through photo.php by their owner or an admin.
 */
require_once 'assets/includes/bootstrap.php';

function fail_custom(string $msg, string $back): void {
    if (is_ajax()) {
        json_out(['ok' => false, 'error' => $msg], 422);
    }
    flash('error', $msg);
    redirect($back);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('shop.php');
}

// If the upload was bigger than PHP allows, PHP throws away the whole form
if (empty($_POST) && (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    fail_custom('Those photos are too large to upload together. Try fewer or smaller photos.', 'shop.php');
}

csrf_check();

$pid     = (int)($_POST['product_id'] ?? 0);
$itemId  = (int)($_POST['item_id'] ?? 0);
$qty     = max(1, min(99, (int)($_POST['quantity'] ?? 1)));
$title   = mb_substr(trim($_POST['title'] ?? ''), 0, 40);
$names   = mb_substr(trim($_POST['names'] ?? ''), 0, 60);
$message = mb_substr(trim($_POST['message'] ?? ''), 0, 300);
$back    = 'customize.php?id=' . $pid . ($itemId ? '&edit=' . $itemId : '');

$product = product_find($pid);
if (!$product) {
    fail_custom('This magazine is no longer available.', 'shop.php');
}

$item = null;
if ($itemId) {
    $item = cart_find($itemId);
    if (!$item || !(int)$item['is_custom'] || (int)$item['product_id'] !== $pid) {
        fail_custom('That customized magazine is no longer in your cart.', 'cart.php');
    }
}

$available = (int)$product['stock'] - cart_qty_of_product($pid, $itemId);
if ($available < 1) {
    fail_custom('There are no more copies of ' . $product['name'] . ' available.', 'product-details.php?id=' . $pid);
}
if ($qty > $available) {
    fail_custom('Only ' . $available . ' ' . ($available === 1 ? 'copy is' : 'copies are') . ' available.', $back);
}

// Photos already saved on this cart item (when editing)
$owned = [];
if ($item) {
    foreach (db_all('SELECT id, kind FROM custom_photos WHERE cart_item_id = ?', [$itemId]) as $ph) {
        $owned[(int)$ph['id']] = $ph['kind'];
    }
}

$coverFile = files_list($_FILES['cover'] ?? null)[0] ?? null;
$keepCover = (int)($_POST['keep_cover'] ?? 0);
if (!$coverFile && !($keepCover && isset($owned[$keepCover]))) {
    fail_custom('Add a cover photo to continue.', $back);
}

// Work out the page order: "e:12" = keep saved photo 12, "n:0" = first new upload
$newPages = files_list($_FILES['pages'] ?? null);
$order = $_POST['page_order'] ?? null;
if (!is_array($order)) {
    $order = [];
    foreach ($newPages as $i => $_) {
        $order[] = 'n:' . $i;
    }
}
if (count($order) > MAX_PAGE_PHOTOS) {
    fail_custom('You can add up to ' . MAX_PAGE_PHOTOS . ' page photos.', $back);
}

$inTx = false;
$saved = []; // files written in this request, removed again if anything fails
try {
    $coverName = $coverFile ? store_uploaded_image($coverFile, CUSTOM_PHOTO_DIR, true) : null;
    if ($coverName) {
        $saved[] = $coverName;
    }

    $plan = []; // list of ['keep' => id] or ['file' => name]
    foreach ($order as $entry) {
        [$type, $ref] = array_pad(explode(':', (string)$entry, 2), 2, '');
        if ($type === 'e' && isset($owned[(int)$ref])) {
            $plan[] = ['keep' => (int)$ref];
        } elseif ($type === 'n' && isset($newPages[(int)$ref])) {
            $name = store_uploaded_image($newPages[(int)$ref], CUSTOM_PHOTO_DIR, true);
            $saved[] = $name;
            $plan[] = ['file' => $name];
        }
    }

    db()->begin_transaction();
    $inTx = true;

    if ($item) {
        db_exec('UPDATE cart_items SET quantity = ?, custom_title = ?, custom_names = ?, custom_message = ? WHERE id = ?',
            [$qty, $title, $names, $message, $itemId]);
    } else {
        [$where, $params] = cart_owner(); // makes sure a guest has a cart key
        $userId = !empty($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
        $itemId = db_exec('INSERT INTO cart_items (user_id, session_key, product_id, quantity, is_custom, custom_title, custom_names, custom_message)
                           VALUES (?, ?, ?, ?, 1, ?, ?, ?)',
            [$userId, $userId ? null : $_SESSION['cart_key'], $pid, $qty, $title, $names, $message]);
    }

    // Remove saved photos that the customer took out
    $keepIds = [];
    if (!$coverName) {
        $keepIds[] = $keepCover;
    }
    foreach ($plan as $step) {
        if (isset($step['keep'])) {
            $keepIds[] = $step['keep'];
        }
    }
    cart_delete_photos($itemId, $keepIds);

    if ($coverName) {
        db_exec("INSERT INTO custom_photos (cart_item_id, kind, position, file_name) VALUES (?, 'cover', 0, ?)", [$itemId, $coverName]);
    }
    foreach ($plan as $pos => $step) {
        if (isset($step['keep'])) {
            db_exec('UPDATE custom_photos SET position = ? WHERE id = ?', [$pos + 1, $step['keep']]);
        } else {
            db_exec("INSERT INTO custom_photos (cart_item_id, kind, position, file_name) VALUES (?, 'page', ?, ?)", [$itemId, $pos + 1, $step['file']]);
        }
    }

    db()->commit();
} catch (Throwable $e) {
    if ($inTx) {
        db()->rollback();
    }
    foreach ($saved as $name) {
        delete_upload(CUSTOM_PHOTO_DIR . '/' . $name);
    }
    if (!$e instanceof RuntimeException) {
        error_log('Customize failed: ' . $e->getMessage());
    }
    fail_custom($e instanceof RuntimeException ? $e->getMessage() : 'Your magazine could not be saved. Please try again.', $back);
}

flash('success', $item ? 'Your changes are saved.' : 'Your customized ' . $product['name'] . ' is in your cart.');
if (is_ajax()) {
    json_out(['ok' => true, 'redirect' => 'cart.php']);
}
redirect('cart.php');
