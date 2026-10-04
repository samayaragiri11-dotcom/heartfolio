<?php
$footerCats = array_slice(categories_all(), 0, 5);
?>
</main>

<footer class="site-footer">
    <div class="wrap footer-grid">
        <div>
            <div class="footer-brand">Heartfolio</div>
            <p>Personalized magazines filled with your own photos and words, printed on premium paper and delivered to your door.</p>
        </div>
        <div>
            <h4>Shop</h4>
            <ul>
                <li><a href="shop.php">All magazines</a></li>
                <?php foreach ($footerCats as $c): ?>
                    <li><a href="shop.php?category=<?php echo e($c['slug']); ?>"><?php echo e($c['name']); ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div>
            <h4>Help</h4>
            <ul>
                <li><a href="account.php?tab=orders">Track an order</a></li>
                <li><a href="account.php">My account</a></li>
                <li><a href="cart.php">Cart</a></li>
                <li><a href="contact.php">Contact us</a></li>
            </ul>
        </div>
    </div>
    <div class="wrap footer-bottom">&copy; <?php echo date('Y'); ?> Heartfolio. Made with love in Nepal.</div>
</footer>

<div class="toast" id="toast" role="status" aria-live="polite"></div>
</body>
</html>
