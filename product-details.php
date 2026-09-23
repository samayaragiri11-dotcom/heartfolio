<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'assets/includes/products-data.php';

$product = hf_product($_GET['id'] ?? 0);

include 'assets/includes/navbar.php';
?>

<?php if (!$product): ?>
<div class="container" style="text-align: center; padding: 60px 20px;">
    <h2>Magazine not found</h2>
    <p style="margin: 10px 0 20px;">This magazine does not exist or has been removed.</p>
    <a href="shop.php" class="btn btn-primary">Back to Shop</a>
</div>
<?php else: ?>
<div class="container product-details">
    <div class="product-details-content">
        <!-- Product Image -->
        <div class="product-details-image">
            <img src="<?php echo hf_e($product['image']); ?>" alt="<?php echo hf_e($product['name']); ?> Magazine" id="main-image">
            <?php if (count($product['thumbs']) > 1): ?>
                <div class="product-thumbnails">
                    <?php foreach ($product['thumbs'] as $i => $thumb): ?>
                        <img src="<?php echo hf_e($thumb); ?>" alt="<?php echo hf_e($product['name']); ?> view <?php echo $i + 1; ?>"
                             class="<?php echo $i === 0 ? 'active' : ''; ?>" onclick="changeImage(this)">
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Product Info -->
        <div class="product-details-info">
            <h1><?php echo hf_e($product['name']); ?></h1>
            <p class="price"><?php echo hf_price($product['price']); ?></p>
            <div class="rating">
                <?php
                $full = (int)floor($product['rating']);
                $half = ($product['rating'] - $full) >= 0.5;
                for ($i = 1; $i <= 5; $i++) {
                    if ($i <= $full) {
                        echo '<i class="fas fa-star"></i>';
                    } elseif ($half && $i === $full + 1) {
                        echo '<i class="fas fa-star-half-alt"></i>';
                    } else {
                        echo '<i class="far fa-star"></i>';
                    }
                }
                ?>
                <span>(<?php echo (int)$product['reviews']; ?> reviews)</span>
            </div>
            <p class="description"><?php echo hf_e($product['description']); ?></p>
            <div class="product-info">
                <p><strong>Pages:</strong> <?php echo (int)$product['pages']; ?></p>
                <p><strong>Size:</strong> <?php echo hf_e($product['size']); ?></p>
                <p><strong>Paper:</strong> <?php echo hf_e($product['paper']); ?></p>
                <p><strong>Category:</strong> <?php echo hf_e(hf_category_label($product['category'])); ?></p>
            </div>
            <div class="quantity-selector">
                <button type="button" onclick="changeQuantity(-1)">-</button>
                <input type="number" id="quantity" value="1" min="1" max="99" onchange="changeQuantity(0)">
                <button type="button" onclick="changeQuantity(1)">+</button>
            </div>
            <div class="product-details-buttons">
                <button type="button" class="btn btn-primary" onclick="addToCart()">Add to Cart</button>
                <button type="button" class="btn btn-pink" onclick="buyNow()">Buy Now</button>
            </div>
        </div>
    </div>
</div>

<script>
var PRODUCT = <?php echo json_encode([
    'id'       => $product['id'],
    'name'     => $product['name'],
    'price'    => $product['price'],
    'image'    => $product['image'],
    'category' => hf_category_label($product['category']),
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;

function changeImage(thumbnail) {
    document.getElementById('main-image').src = thumbnail.src;
    document.querySelectorAll('.product-thumbnails img').forEach(function (img) {
        img.classList.remove('active');
    });
    thumbnail.classList.add('active');
}

function getQuantity() {
    var q = parseInt(document.getElementById('quantity').value, 10);
    return isNaN(q) ? 1 : Math.min(99, Math.max(1, q));
}

function changeQuantity(step) {
    document.getElementById('quantity').value = Math.min(99, Math.max(1, getQuantity() + step));
}

function addToCart() {
    var qty = getQuantity();
    HFCart.add(PRODUCT, qty);
    HFCart.toast('Added ' + qty + ' x ' + PRODUCT.name + ' to cart');
}

function buyNow() {
    HFCart.add(PRODUCT, getQuantity());
    window.location.href = 'checkout.php';
}
</script>
<?php endif; ?>

<?php include 'assets/includes/footer.php'; ?>