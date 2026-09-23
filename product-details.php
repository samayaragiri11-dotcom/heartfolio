<?php 
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include 'assets/includes/navbar.php'; 
?>

<div class="container product-details">
    <div class="product-details-content">
        <!-- Product Image -->
        <div class="product-details-image">
            <img src="assets\images\shopbsf.jpeg" alt="Best Friends Magazine" id="main-image">
            <div class="product-thumbnails">
                <img src="images\thumb1bsf.png" alt="Thumbnail 1" class="active" onclick="changeImage(this)">
                <img src="images\thumb2bsf.png" alt="Thumbnail 2" onclick="changeImage(this)">
                <img src="images\thumb3bsf.jpeg" alt="Thumbnail 3" onclick="changeImage(this)">
            </div>
        </div>

        <!-- Product Info -->
        <div class="product-details-info">
            <h1>Best Friends</h1>
            <p class="price">Rs. 699</p>
            <div class="rating">
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star-half-alt"></i>
                <span>(24 reviews)</span>
            </div>
            <p class="description">
                Celebrate your friendship with this beautiful personalized magazine. 
                Perfect for best friends who want to cherish their memories together. 
                Fill it with your favorite photos, stories, and moments that define your friendship.
            </p>
            <div class="product-info">
                <p><strong>Pages:</strong> 24</p>
                <p><strong>Size:</strong> 8.5" x 11"</p>
                <p><strong>Paper:</strong> Premium Glossy</p>
                <p><strong>Category:</strong> Friendship</p>
            </div>
            <div class="quantity-selector">
                <button onclick="decreaseQuantity()">-</button>
                <input type="number" id="quantity" value="1" min="1">
                <button onclick="increaseQuantity()">+</button>
            </div>
            <div class="product-details-buttons">
                <button class="btn btn-primary" onclick="addToCart()">Add to Cart</button>
                <button class="btn btn-pink" onclick="buyNow()">Buy Now</button>
            </div>
        </div>
    </div>
</div>

<script>
function changeImage(thumbnail) {
    document.getElementById('main-image').src = thumbnail.src;
    document.querySelectorAll('.product-thumbnails img').forEach(img => img.classList.remove('active'));
    thumbnail.classList.add('active');
}

function increaseQuantity() {
    const input = document.getElementById('quantity');
    input.value = parseInt(input.value) + 1;
}

function decreaseQuantity() {
    const input = document.getElementById('quantity');
    if (parseInt(input.value) > 1) {
        input.value = parseInt(input.value) - 1;
    }
}

function addToCart() {
    alert('Product added to cart!');
}

function buyNow() {
    window.location.href = 'checkout.php';
}
</script>

<?php include 'assets/includes/footer.php'; ?>
