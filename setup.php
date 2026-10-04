<?php
/*
 * Heartfolio one-click setup.
 * Open http://localhost/<your-folder>/setup.php once. It:
 *   1. creates the "heartfolio" database if it is missing
 *   2. creates every table the site needs (safe to run again)
 *   3. adds the categories and 12 magazines if the shop is empty
 *   4. makes sure an admin account exists
 * Existing customer accounts and orders are kept.
 */
define('HF_SETUP', true);
define('HF_ROOT', __DIR__);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/assets/includes/functions.php';
require_once __DIR__ . '/assets/includes/cart-lib.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$log = [];
$problem = null;
$adminNote = '';
$adminEmail = 'admin@heartfolio.com';

function setup_page(string $title, string $body): void {
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
       . '<title>Heartfolio setup</title><style>'
       . 'body{font-family:system-ui,"Segoe UI",sans-serif;background:#FAF5ED;color:#3F352D;margin:0;padding:48px 16px}'
       . '.box{max-width:640px;margin:0 auto;background:#fff;border:1px solid #DCC7AD;border-radius:14px;padding:32px}'
       . 'h1{font-size:26px;margin:0 0 8px}ul{padding-left:20px;line-height:1.8}li.warn{color:#8a5a00}'
       . '.btn{display:inline-block;background:#3F352D;color:#fff;padding:12px 20px;border-radius:10px;text-decoration:none;margin:6px 8px 0 0;font-weight:600}'
       . '.btn.alt{background:#D98291}.err{background:#F8D7DA;color:#721C24;padding:12px 14px;border-radius:8px}'
       . 'code{background:#F1E6D6;padding:2px 6px;border-radius:4px}'
       . '</style></head><body><div class="box"><h1>' . e($title) . '</h1>' . $body . '</div></body></html>';
    exit();
}

/* ---------- 1. Connect and create the database ---------- */
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
    $conn->set_charset('utf8mb4');
    $conn->query('CREATE DATABASE IF NOT EXISTS `' . DB_NAME . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $conn->select_db(DB_NAME);
} catch (mysqli_sql_exception $e) {
    setup_page('MySQL is not reachable',
        '<p class="err">' . e($e->getMessage()) . '</p>'
        . '<p>Start <strong>MySQL</strong> in the XAMPP Control Panel, then refresh this page.</p>');
}

/* ---------- 2. Only an admin may re-run setup once the site is installed ---------- */
$installed = hf_is_installed();
$adminCount = 0;
if ($installed) {
    $adminCount = (int)db_value("SELECT COUNT(*) FROM users WHERE role = 'admin'");
}
if ($installed && $adminCount > 0) {
    $me = !empty($_SESSION['user_id'])
        ? db_one('SELECT role FROM users WHERE id = ?', [(int)$_SESSION['user_id']])
        : null;
    if (!$me || $me['role'] !== 'admin') {
        setup_page('Heartfolio is already set up',
            '<p>The database is ready. To run setup again, log in as an admin first.</p>'
            . '<a class="btn" href="index.php">Go to the shop</a><a class="btn alt" href="login.php?next=setup.php">Admin login</a>');
    }
}

/* ---------- 3. Old tables from earlier versions get set aside ---------- */
// If a table exists but has a different shape, rename it (kept as a backup) so the new one can be created.
$expected = [
    'categories'           => 'slug',
    'products'             => 'category_id',
    'product_images'       => 'sort_order',
    'cart_items'           => 'session_key',
    'orders'               => 'order_number',
    'order_items'          => 'unit_price',
    'order_status_history' => 'status',
    'custom_photos'        => 'order_item_id',
    'reviews'              => 'rating',
    'messages'             => 'is_read',
];
foreach ($expected as $table => $column) {
    $exists = $conn->query("SHOW TABLES LIKE '$table'")->num_rows > 0;
    if (!$exists) {
        continue;
    }
    $hasColumn = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'")->num_rows > 0;
    if (!$hasColumn) {
        $backup = $table . '_old_' . date('Ymd_His');
        $conn->query('SET FOREIGN_KEY_CHECKS = 0');
        $conn->query("RENAME TABLE `$table` TO `$backup`");
        $conn->query('SET FOREIGN_KEY_CHECKS = 1');
        $log[] = ['warn', "Found an older \"$table\" table and kept it as \"$backup\"."];
    }
}

/* ---------- 4. Create tables ---------- */
$schema = [
'users' => "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('customer','admin') NOT NULL DEFAULT 'customer',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

'categories' => "CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(60) NOT NULL,
    slug VARCHAR(60) NOT NULL UNIQUE,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

'products' => "CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NULL,
    name VARCHAR(120) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    pages INT NOT NULL DEFAULT 24,
    size VARCHAR(40) NOT NULL DEFAULT '8.5\" x 11\"',
    paper VARCHAR(60) NOT NULL DEFAULT 'Premium Glossy',
    image VARCHAR(255) NOT NULL DEFAULT '',
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (is_active),
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

'product_images' => "CREATE TABLE IF NOT EXISTS product_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    image VARCHAR(255) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

'cart_items' => "CREATE TABLE IF NOT EXISTS cart_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    session_key VARCHAR(64) NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    is_custom TINYINT(1) NOT NULL DEFAULT 0,
    custom_title VARCHAR(60) NULL,
    custom_names VARCHAR(80) NULL,
    custom_message VARCHAR(300) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX (session_key),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

'orders' => "CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(20) NOT NULL UNIQUE,
    user_id INT NULL,
    fullname VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address VARCHAR(255) NOT NULL,
    city VARCHAR(80) NOT NULL,
    notes VARCHAR(500) NULL,
    payment_method VARCHAR(20) NOT NULL DEFAULT 'cod',
    status ENUM('pending','processing','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
    subtotal DECIMAL(10,2) NOT NULL,
    shipping DECIMAL(10,2) NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (status),
    INDEX (created_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

'order_items' => "CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NULL,
    product_name VARCHAR(120) NOT NULL,
    product_image VARCHAR(255) NOT NULL DEFAULT '',
    category_name VARCHAR(60) NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL,
    is_custom TINYINT(1) NOT NULL DEFAULT 0,
    custom_title VARCHAR(60) NULL,
    custom_names VARCHAR(80) NULL,
    custom_message VARCHAR(300) NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

'order_status_history' => "CREATE TABLE IF NOT EXISTS order_status_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    status VARCHAR(20) NOT NULL,
    note VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

'custom_photos' => "CREATE TABLE IF NOT EXISTS custom_photos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cart_item_id INT NULL,
    order_item_id INT NULL,
    kind ENUM('cover','page') NOT NULL,
    position INT NOT NULL DEFAULT 0,
    file_name VARCHAR(100) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cart_item_id) REFERENCES cart_items(id) ON DELETE CASCADE,
    FOREIGN KEY (order_item_id) REFERENCES order_items(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

'reviews' => "CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    rating TINYINT NOT NULL,
    comment TEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY one_review_each (product_id, user_id),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

'messages' => "CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('contact','password_reset') NOT NULL DEFAULT 'contact',
    user_id INT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    subject VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
];

try {
    foreach ($schema as $table => $sql) {
        $existed = $conn->query("SHOW TABLES LIKE '$table'")->num_rows > 0;
        $conn->query($sql);
        if (!$existed) {
            $log[] = ['ok', "Created table \"$table\"."];
        }
    }

    // Older users tables (before the admin update) have no role column
    if ($conn->query("SHOW COLUMNS FROM users LIKE 'role'")->num_rows === 0) {
        $conn->query("ALTER TABLE users ADD COLUMN role ENUM('customer','admin') NOT NULL DEFAULT 'customer'");
        $log[] = ['ok', 'Added the role column to users.'];
    }

    /* ---------- 5. Starter data ---------- */
    if ((int)db_value('SELECT COUNT(*) FROM categories') === 0) {
        $cats = [
            ['Friendship', 'friendship'], ['Birthday', 'birthday'], ['Love & Couple', 'love'],
            ['Family', 'family'], ['Anniversary', 'anniversary'], ['Travel', 'travel'], ['Others', 'others'],
        ];
        foreach ($cats as $i => [$name, $slug]) {
            db_exec('INSERT INTO categories (name, slug, sort_order) VALUES (?, ?, ?)', [$name, $slug, $i + 1]);
        }
        $log[] = ['ok', 'Added 7 categories.'];
    }

    if ((int)db_value('SELECT COUNT(*) FROM products') === 0) {
        $catId = [];
        foreach (db_all('SELECT id, slug FROM categories') as $c) {
            $catId[$c['slug']] = (int)$c['id'];
        }
        // name, category, price, stock, image, featured, description
        $products = [
            ['Best Friends', 'friendship', 699, 30, 'assets/images/shopbsf.jpeg', 1,
             'Celebrate your friendship with this beautiful personalized magazine. Perfect for best friends who want to cherish their memories together. Fill it with your favorite photos, stories, and moments that define your friendship.'],
            ['Friendship Forever', 'friendship', 699, 25, 'images/shopff.jpg', 0,
             'A keepsake for friendships that have lasted through the years. Collect your trips, inside jokes and milestones in one printed magazine.'],
            ['Memories Together', 'friendship', 699, 25, 'assets/images/mtshop.jpeg', 0,
             'Bring together the laughs, adventures and quiet days you have shared. A magazine made for looking back and smiling.'],
            ['Better Together', 'friendship', 699, 20, 'images/btshop.jpeg', 0,
             'For the people who make everything better. Celebrate your group with photos, stories and the moments only you understand.'],
            ['Soul Sisters', 'friendship', 699, 20, 'images/sisshop.jpeg', 0,
             'A tribute to the sister, by blood or by heart, who knows you best. Fill it with the memories that made your bond.'],
            ['Our Story', 'love', 699, 25, 'images/ourstory.jpeg', 0,
             'Tell your love story from the first hello. Dates, trips and little moments, printed in a magazine made just for the two of you.'],
            ['Birthday Special', 'birthday', 699, 30, 'assets/images/birthday.jpeg', 1,
             'A birthday gift they will keep forever. Fill it with photos, wishes and messages from the people who love them.'],
            ['Family Memories', 'family', 699, 25, 'assets/images/family.jpeg', 1,
             'Holidays, celebrations and everyday moments with the people who matter most, gathered into one family keepsake.'],
            ['Anniversary Love', 'anniversary', 699, 20, 'assets/images/ann.jpeg', 0,
             'Celebrate every year you have spent together. Perfect for anniversaries big and small.'],
            ['Travel Adventures', 'travel', 699, 4, 'assets/images/travel.jpeg', 0,
             'Turn your trip photos into a travel magazine. Places, people and stories from the road, all in one place.'],
            ['Special Moments', 'others', 699, 15, 'images/sisshop.jpeg', 0,
             'Graduations, farewells, new babies or anything worth remembering. A magazine for the moments that do not fit a category.'],
            ['Love Forever', 'love', 699, 30, 'assets/images/loveforever.jpeg', 1,
             'A romantic keepsake for couples. Share your favorite photos and the words you want to say forever.'],
        ];
        foreach ($products as [$name, $cat, $price, $stock, $image, $featured, $desc]) {
            $pid = db_exec('INSERT INTO products (category_id, name, description, price, stock, image, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?)',
                [$catId[$cat] ?? null, $name, $desc, (float)$price, $stock, $image, $featured]);
            if ($name === 'Best Friends') {
                foreach (['images/thumb1bsf.png', 'images/thumb2bsf.png', 'images/thumb3bsf.jpeg'] as $i => $thumb) {
                    db_exec('INSERT INTO product_images (product_id, image, sort_order) VALUES (?, ?, ?)', [$pid, $thumb, $i + 1]);
                }
            }
        }
        $log[] = ['ok', 'Added the 12 magazines with stock levels.'];
    }

    /* ---------- 6. Admin account ---------- */
    if ((int)db_value("SELECT COUNT(*) FROM users WHERE role = 'admin'") === 0) {
        $existing = db_one('SELECT id FROM users WHERE email = ?', [$adminEmail]);
        if ($existing) {
            db_exec("UPDATE users SET role = 'admin' WHERE id = ?", [(int)$existing['id']]);
            $log[] = ['ok', "Made $adminEmail an admin (password unchanged)."];
        } else {
            db_exec("INSERT INTO users (fullname, email, phone, password, role) VALUES ('Heartfolio Admin', ?, '9800000000', ?, 'admin')",
                [$adminEmail, password_hash('password123', PASSWORD_DEFAULT)]);
            $log[] = ['ok', "Created the admin account $adminEmail with password password123."];
            $adminNote = '<p><strong>Change the admin password</strong> after your first login (Admin &rarr; My account).</p>';
        }
    }

    /* ---------- 7. Upload folders ---------- */
    ensure_upload_dir('uploads/products');
    ensure_upload_dir('uploads/custom', true);

} catch (mysqli_sql_exception $e) {
    $problem = $e->getMessage();
}

if ($problem) {
    setup_page('Setup stopped', '<p class="err">' . e($problem) . '</p><p>Fix the problem above and refresh this page.</p>');
}

if (!$log) {
    $log[] = ['ok', 'Everything was already up to date.'];
}

$items = '';
foreach ($log as [$type, $text]) {
    $items .= '<li class="' . $type . '">' . e($text) . '</li>';
}

setup_page('Heartfolio is ready',
    '<ul>' . $items . '</ul>' . ($adminNote ?? '')
    . '<p>Admin login: <code>' . e($adminEmail) . '</code></p>'
    . '<a class="btn" href="index.php">Open the shop</a><a class="btn alt" href="login.php?next=admin/index.php">Go to admin</a>');
