<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once 'assets/includes/products-data.php';
include 'assets/includes/navbar.php';
?>

<div class="container products-section">
    <div class="section-title">
        <h2>Magazine Templates</h2>
        <p>Browse our available magazine templates</p>
    </div>
    <div class="product-grid">
        <?php foreach ($PRODUCTS as $p) { hf_product_card($p, 'View Template'); } ?>
    </div>
</div>

<?php include 'assets/includes/footer.php'; ?>
