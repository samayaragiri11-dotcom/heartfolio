<?php 
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Heartfolio - Custom Magazines</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="container navbar-content">
            <a href="index.php" class="logo">
                <img src="assets/images/logo.jpg" alt="Heartfolio" style="height: 50px; border-radius: 50%;">
            </a>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="shop.php">Shop</a></li>
                    <li><a href="templates.php">Templates</a></li>
                    <li><a href="contact.php">Contact</a></li>
                <?php else: ?>
                    <li><a href="login.php">Shop</a></li>
                    <li><a href="login.php">Templates</a></li>
                    <li><a href="contact.php">Contact</a></li>
                <?php endif; ?>
            </ul>
            <div class="nav-icons">
                <a href="search.php"><i class="fas fa-search"></i></a>
                <a href="cart.php">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-badge" id="cart-count">0</span>
                </a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="account.php" title="My Account"><i class="fas fa-user"></i></a>
                    <a href="logout.php" title="Logout" style="margin-left: 10px;"><i class="fas fa-sign-out-alt"></i></a>
                <?php else: ?>
                    <a href="login.php" title="Login"><i class="fas fa-user"></i></a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
