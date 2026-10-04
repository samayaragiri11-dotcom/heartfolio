<?php
/*
 * Shared helpers used by every page.
 */

const SHIPPING_FEE    = 100;   // flat delivery charge in Rs.
const LOW_STOCK_LIMIT = 5;     // at or below this, stock counts as "low"
const MAX_PAGE_PHOTOS = 12;    // inside-page photos a customer can upload
const MAX_UPLOAD_MB   = 8;     // per image, after the browser has resized it

const ORDER_STATUSES = [
    'pending'    => 'Pending',
    'processing' => 'Processing',
    'shipped'    => 'Shipped',
    'delivered'  => 'Delivered',
    'cancelled'  => 'Cancelled',
];

const PAYMENT_METHODS = [
    'cod'    => 'Cash on Delivery',
    'esewa'  => 'eSewa',
    'khalti' => 'Khalti',
];

/* ---------------------------------------------------------------
 * Output
 * ------------------------------------------------------------- */

function e($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function price($amount): string {
    $amount = (float)$amount;
    $decimals = (floor($amount) == $amount) ? 0 : 2;
    return 'Rs. ' . number_format($amount, $decimals);
}

function nice_date($datetime, bool $withTime = false): string {
    $ts = strtotime((string)$datetime);
    return $ts ? date($withTime ? 'M j, Y \a\t g:i A' : 'M j, Y', $ts) : '';
}

function status_badge(string $status): string {
    $label = ORDER_STATUSES[$status] ?? ucfirst($status);
    return '<span class="badge status-' . e($status) . '">' . e($label) . '</span>';
}

function stars(float $rating): string {
    $html = '<span class="stars" aria-label="' . e(number_format($rating, 1)) . ' out of 5 stars">';
    for ($i = 1; $i <= 5; $i++) {
        if ($rating >= $i) {
            $html .= '<i class="fa-solid fa-star" aria-hidden="true"></i>';
        } elseif ($rating >= $i - 0.5) {
            $html .= '<i class="fa-solid fa-star-half-stroke" aria-hidden="true"></i>';
        } else {
            $html .= '<i class="fa-regular fa-star" aria-hidden="true"></i>';
        }
    }
    return $html . '</span>';
}

/* ---------------------------------------------------------------
 * Paths
 * ------------------------------------------------------------- */

// '' on customer pages, '../' on admin pages
function hf_base(): string {
    $dir = basename(dirname($_SERVER['SCRIPT_FILENAME'] ?? ''));
    return $dir === 'admin' ? '../' : '';
}

// Turns a stored image path into a URL that works from the current page
function img_url(?string $path): string {
    $path = trim((string)$path);
    if ($path === '') {
        return hf_base() . 'assets/images/placeholder.svg';
    }
    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }
    return hf_base() . str_replace('\\', '/', ltrim($path, '/'));
}

function redirect(string $url): void {
    header('Location: ' . $url);
    exit();
}

// Only allow redirects back into this site (stops "open redirect" tricks)
function safe_next(?string $next, string $fallback = 'index.php'): string {
    $next = (string)$next;
    if ($next === '' || preg_match('#^(https?:)?//#i', $next) || strpos($next, '\\') !== false
        || preg_match('#[\r\n]#', $next)) {
        return $fallback;
    }
    return ltrim($next, '/');
}

// The page the visitor came from, reduced to a same-site link
function back_url(string $fallback): string {
    $ref = (string)($_SERVER['HTTP_REFERER'] ?? '');
    if ($ref === '') {
        return $fallback;
    }
    $path  = basename((string)parse_url($ref, PHP_URL_PATH));
    $query = (string)parse_url($ref, PHP_URL_QUERY);
    if ($path === '' || !preg_match('/^[a-z0-9_-]+\.php$/i', $path)) {
        return $fallback;
    }
    return $path . ($query !== '' ? '?' . $query : '');
}

/* ---------------------------------------------------------------
 * Database shortcuts (always prepared statements)
 * ------------------------------------------------------------- */

function db(): mysqli {
    global $conn;
    return $conn;
}

function db_run(string $sql, array $params = []): mysqli_stmt {
    $stmt = db()->prepare($sql);
    if ($params) {
        $types = '';
        foreach ($params as $p) {
            $types .= is_int($p) ? 'i' : (is_float($p) ? 'd' : 's');
        }
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    return $stmt;
}

function db_all(string $sql, array $params = []): array {
    $result = db_run($sql, $params)->get_result();
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function db_one(string $sql, array $params = []): ?array {
    $result = db_run($sql, $params)->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    return $row ?: null;
}

function db_value(string $sql, array $params = []) {
    $result = db_run($sql, $params)->get_result();
    $row = $result ? $result->fetch_row() : null;
    return $row ? $row[0] : null;
}

// For INSERT/UPDATE/DELETE. Returns the new id for inserts, otherwise affected rows.
function db_exec(string $sql, array $params = []): int {
    $stmt = db_run($sql, $params);
    return $stmt->insert_id ?: $stmt->affected_rows;
}

function hf_is_installed(): bool {
    static $installed = null;
    if ($installed === null) {
        try {
            $installed = (bool)db()->query("SHOW TABLES LIKE 'order_status_history'")->num_rows;
        } catch (mysqli_sql_exception $e) {
            $installed = false;
        }
    }
    return $installed;
}

/* ---------------------------------------------------------------
 * Security: CSRF tokens stop other websites submitting forms as you
 * ------------------------------------------------------------- */

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check(): void {
    $sent = $_POST['csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if (!is_string($sent) || !hash_equals(csrf_token(), $sent)) {
        if (is_ajax()) {
            json_out(['ok' => false, 'error' => 'Your session expired. Refresh the page and try again.'], 419);
        }
        flash('error', 'Your session expired. Please try again.');
        redirect(back_url('index.php'));
    }
}

function is_ajax(): bool {
    return ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'fetch';
}

function json_out(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

/* ---------------------------------------------------------------
 * Flash messages: shown once on the next page
 * ------------------------------------------------------------- */

function flash(string $type, string $message): void {
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function flash_render(): string {
    if (empty($_SESSION['flash'])) {
        return '';
    }
    $html = '';
    foreach ($_SESSION['flash'] as $f) {
        $icon = $f['type'] === 'success' ? 'fa-circle-check' : ($f['type'] === 'error' ? 'fa-circle-exclamation' : 'fa-circle-info');
        $html .= '<div class="alert alert-' . e($f['type']) . '" role="status">'
               . '<i class="fa-solid ' . $icon . '" aria-hidden="true"></i><span>' . e($f['message']) . '</span></div>';
    }
    unset($_SESSION['flash']);
    return '<div class="flash-stack">' . $html . '</div>';
}

/* ---------------------------------------------------------------
 * Users
 * ------------------------------------------------------------- */

function current_user(): ?array {
    static $user = false;
    if ($user === false) {
        $user = null;
        if (!empty($_SESSION['user_id'])) {
            $user = db_one('SELECT id, fullname, email, phone, role, created_at FROM users WHERE id = ?', [(int)$_SESSION['user_id']]);
            if (!$user) {
                // Account was deleted: log them out
                unset($_SESSION['user_id'], $_SESSION['role'], $_SESSION['fullname'], $_SESSION['email']);
            }
        }
    }
    return $user;
}

function is_admin(): bool {
    $u = current_user();
    return $u && $u['role'] === 'admin';
}

function require_login(): array {
    $user = current_user();
    if (!$user) {
        $here = basename($_SERVER['SCRIPT_NAME']) . (empty($_SERVER['QUERY_STRING']) ? '' : '?' . $_SERVER['QUERY_STRING']);
        flash('info', 'Please log in to continue.');
        redirect(hf_base() . 'login.php?next=' . urlencode($here));
    }
    return $user;
}

function log_user_in(array $user): void {
    $guestKey = $_SESSION['cart_key'] ?? null;
    session_regenerate_id(true);
    $_SESSION['user_id']  = (int)$user['id'];
    $_SESSION['role']     = $user['role'];
    $_SESSION['fullname'] = $user['fullname'];
    $_SESSION['email']    = $user['email'];
    if ($guestKey) {
        cart_merge_guest($guestKey, (int)$user['id']);
    }
    unset($_SESSION['cart_key']);
}

/* ---------------------------------------------------------------
 * Catalogue
 * ------------------------------------------------------------- */

function categories_all(): array {
    static $cats = null;
    if ($cats === null) {
        $cats = db_all('SELECT c.id, c.name, c.slug, c.sort_order,
                               (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id AND p.is_active = 1) AS product_count
                        FROM categories c ORDER BY c.sort_order, c.name');
    }
    return $cats;
}

// Base SELECT for products shown to customers, with average rating
function product_select_sql(): string {
    return 'SELECT p.*, c.name AS category_name, c.slug AS category_slug,
                   COALESCE(r.avg_rating, 0) AS avg_rating, COALESCE(r.review_count, 0) AS review_count
            FROM products p
            LEFT JOIN categories c ON c.id = p.category_id
            LEFT JOIN (SELECT product_id, AVG(rating) AS avg_rating, COUNT(*) AS review_count
                       FROM reviews GROUP BY product_id) r ON r.product_id = p.id';
}

function product_find(int $id, bool $activeOnly = true): ?array {
    return db_one(product_select_sql() . ' WHERE p.id = ?' . ($activeOnly ? ' AND p.is_active = 1' : ''), [$id]);
}

function stock_label(int $stock): array {
    if ($stock <= 0)               return ['out-of-stock', 'Out of stock'];
    if ($stock <= LOW_STOCK_LIMIT) return ['low-stock', 'Only ' . $stock . ' left'];
    return ['in-stock', 'In stock'];
}

// One product card, used on home, shop, templates and search
function product_card(array $p): string {
    [$stockClass, $stockText] = stock_label((int)$p['stock']);
    $html  = '<article class="product-card">';
    $html .= '<a href="product-details.php?id=' . (int)$p['id'] . '" class="product-cover" aria-label="' . e($p['name']) . '">';
    $html .= '<img src="' . e(img_url($p['image'])) . '" alt="" loading="lazy">';
    if ((int)$p['stock'] <= 0) {
        $html .= '<span class="cover-tag">Sold out</span>';
    }
    $html .= '</a><div class="product-card-body">';
    $html .= '<p class="product-cat">' . e($p['category_name'] ?? 'Magazine') . '</p>';
    $html .= '<h3><a href="product-details.php?id=' . (int)$p['id'] . '">' . e($p['name']) . '</a></h3>';
    $html .= '<div class="product-meta"><span class="price">' . price($p['price']) . '</span>';
    if ((int)$p['review_count'] > 0) {
        $html .= '<span class="mini-rating"><i class="fa-solid fa-star" aria-hidden="true"></i> '
               . e(number_format((float)$p['avg_rating'], 1)) . ' <span>(' . (int)$p['review_count'] . ')</span></span>';
    }
    $html .= '</div>';
    if ($stockClass === 'low-stock') {
        $html .= '<p class="stock-note low-stock">' . e($stockText) . '</p>';
    }
    $html .= '</div></article>';
    return $html;
}

/* ---------------------------------------------------------------
 * Orders
 * ------------------------------------------------------------- */

function new_order_number(): string {
    do {
        $number = 'HF' . date('ymd') . '-' . str_pad((string)random_int(0, 9999), 4, '0', STR_PAD_LEFT);
    } while (db_value('SELECT COUNT(*) FROM orders WHERE order_number = ?', [$number]));
    return $number;
}

/* ---------------------------------------------------------------
 * File uploads
 * ------------------------------------------------------------- */

// Creates an upload folder and protects it from running scripts
function ensure_upload_dir(string $relDir, bool $private = false): string {
    $abs = HF_ROOT . '/' . $relDir;
    if (!is_dir($abs)) {
        mkdir($abs, 0775, true);
    }
    $htaccess = $abs . '/.htaccess';
    if (!file_exists($htaccess)) {
        $rules = $private
            ? "# Customer photos: only served through photo.php\n<IfModule mod_authz_core.c>\n    Require all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\n    Deny from all\n</IfModule>\n"
            : "# Images only: never run uploaded files as code\nOptions -Indexes\n<FilesMatch \"\\.(php|phtml|phar|pl|py|cgi|sh|html?)$\">\n    <IfModule mod_authz_core.c>\n        Require all denied\n    </IfModule>\n    <IfModule !mod_authz_core.c>\n        Deny from all\n    </IfModule>\n</FilesMatch>\n";
        file_put_contents($htaccess, $rules);
    }
    return $abs;
}

/*
 * Checks an uploaded image and saves it with a random name.
 * Returns the saved file name, or throws RuntimeException with a
 * message that is safe to show the user.
 */
function store_uploaded_image(array $file, string $relDir, bool $private = false): string {
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        $code = $file['error'] ?? UPLOAD_ERR_NO_FILE;
        if ($code === UPLOAD_ERR_INI_SIZE || $code === UPLOAD_ERR_FORM_SIZE) {
            throw new RuntimeException('That image is too large.');
        }
        throw new RuntimeException('The image did not upload. Please try again.');
    }
    if ($file['size'] > MAX_UPLOAD_MB * 1024 * 1024) {
        throw new RuntimeException('Images must be smaller than ' . MAX_UPLOAD_MB . ' MB.');
    }
    if (!is_uploaded_file($file['tmp_name'])) {
        throw new RuntimeException('The image did not upload. Please try again.');
    }

    // Check the real file contents, not the name the browser sent
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']);
    $types = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($types[$mime]) || @getimagesize($file['tmp_name']) === false) {
        throw new RuntimeException('Only JPG, PNG or WebP images are allowed.');
    }

    $dir  = ensure_upload_dir($relDir, $private);
    $name = bin2hex(random_bytes(12)) . '.' . $types[$mime];
    if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $name)) {
        throw new RuntimeException('The image could not be saved. Please try again.');
    }
    return $name;
}

// Turns PHP's $_FILES['x'] for a multi-file input into a normal list
function files_list(?array $field): array {
    if (!$field || !isset($field['name'])) {
        return [];
    }
    if (!is_array($field['name'])) {
        return ($field['error'] === UPLOAD_ERR_NO_FILE) ? [] : [$field];
    }
    $list = [];
    foreach ($field['name'] as $i => $name) {
        if ($field['error'][$i] === UPLOAD_ERR_NO_FILE) {
            continue;
        }
        $list[] = [
            'name'     => $name,
            'type'     => $field['type'][$i],
            'tmp_name' => $field['tmp_name'][$i],
            'error'    => $field['error'][$i],
            'size'     => $field['size'][$i],
        ];
    }
    return $list;
}

// Deletes an uploaded file, but never one of the original images that ship with the site
function delete_upload(string $relPath): void {
    $relPath = str_replace('\\', '/', $relPath);
    if (strpos($relPath, 'uploads/') !== 0 || strpos($relPath, '..') !== false) {
        return;
    }
    $abs = HF_ROOT . '/' . $relPath;
    if (is_file($abs)) {
        @unlink($abs);
    }
}
