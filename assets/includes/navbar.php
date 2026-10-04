<?php
/*
 * Site header. Pages set these before including it:
 *   $pageTitle  - text for the browser tab
 *   $active     - which nav link to highlight: home, shop, templates, contact
 */
require_once __DIR__ . '/bootstrap.php';

$pageTitle = $pageTitle ?? 'Personalized magazines';
$active    = $active ?? '';
$navUser   = current_user();
$navCount  = cart_count();
$navLinks  = [
    'home'      => ['index.php', 'Home'],
    'shop'      => ['shop.php', 'Shop'],
    'templates' => ['templates.php', 'Templates'],
    'contact'   => ['contact.php', 'Contact'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo e($pageTitle); ?> | Heartfolio</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600&family=Manrope:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/main.js" defer></script>
</head>
<body>
<a href="#main" class="sr-only">Skip to content</a>

<header class="site-header">
    <div class="wrap header-bar">
        <button class="icon-btn nav-toggle" type="button" aria-label="Open menu" aria-expanded="false" aria-controls="main-nav" data-nav-toggle>
            <i class="fa-solid fa-bars" aria-hidden="true"></i>
        </button>

        <a href="index.php" class="brand">
            <img src="assets/images/logo.jpg" alt="" onerror="this.style.display='none'">
            <span>Heartfolio</span>
        </a>

        <nav class="main-nav" id="main-nav" aria-label="Main">
            <?php foreach ($navLinks as $key => [$href, $label]): ?>
                <a href="<?php echo $href; ?>" class="<?php echo $active === $key ? 'active' : ''; ?>"><?php echo $label; ?></a>
            <?php endforeach; ?>
            <?php if ($navUser && $navUser['role'] === 'admin'): ?>
                <a href="admin/index.php">Admin</a>
            <?php endif; ?>
        </nav>

        <div class="header-icons">
            <button class="icon-btn" type="button" aria-label="Search" aria-expanded="false" aria-controls="header-search" data-search-toggle>
                <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
            </button>
            <a href="cart.php" class="icon-btn" aria-label="Cart, <?php echo $navCount; ?> items">
                <i class="fa-solid fa-bag-shopping" aria-hidden="true"></i>
                <span class="cart-count" data-cart-count data-count="<?php echo $navCount; ?>"><?php echo $navCount; ?></span>
            </a>
            <?php if ($navUser): ?>
                <a href="account.php" class="icon-btn" aria-label="My account (<?php echo e($navUser['fullname']); ?>)" title="My account">
                    <i class="fa-solid fa-user" aria-hidden="true"></i>
                </a>
                <a href="logout.php" class="icon-btn" aria-label="Log out" title="Log out">
                    <i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i>
                </a>
            <?php else: ?>
                <a href="login.php" class="icon-btn" aria-label="Log in" title="Log in">
                    <i class="fa-regular fa-user" aria-hidden="true"></i>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="header-search" id="header-search">
        <form class="wrap" action="shop.php" method="get" role="search">
            <label for="site-search" class="sr-only">Search magazines</label>
            <input type="search" id="site-search" name="q" placeholder="Search magazines, e.g. birthday" value="<?php echo e($_GET['q'] ?? ''); ?>">
            <button class="btn btn-primary" type="submit">Search</button>
        </form>
    </div>
</header>

<main id="main">
