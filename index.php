<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'assets/includes/products-data.php';
$featuredIds = [1, 12, 7, 8]; // Best Friends, Love Forever, Birthday Special, Family Memories
include 'assets/includes/navbar.php';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="container hero-content">
        <div class="hero-text">
            <h1>YOUR MEMORIES,<br>OUR MAGAZINES.</h1>
            <p>Create a personalized magazine<br>that tells your story beautifully.</p>
            <a href="shop.php" class="btn btn-primary">Shop Now</a>
        </div>
        <div class="hero-image">
            <img src="assets/images/hero.png.jpg" alt="Heartfolio Magazines">
        </div>
    </div>
</section>

<!-- Feature Boxes -->
<section class="features">
    <div class="container">
        <div class="features-grid">
            <div class="feature-box">
                <h3>Custom Made</h3>
                <p>Just for You</p>
            </div>
            <div class="feature-box">
                <h3>Premium Quality</h3>
                <p>Magazines</p>
            </div>
            <div class="feature-box">
                <h3>Fast & Safe</h3>
                <p>Delivery</p>
            </div>
        </div>
    </div>
</section>

<!-- Featured Products -->
<section class="products-section">
    <div class="container">
        <div class="section-title">
            <h2>Featured Magazines</h2>
            <p>Our most popular personalized magazines</p>
        </div>
        <div class="product-grid">
            <?php foreach ($featuredIds as $id) {
                $p = hf_product($id);
                if ($p) { hf_product_card($p); }
            } ?>
        </div>
    </div>
</section>

<?php include 'assets/includes/footer.php'; ?>
