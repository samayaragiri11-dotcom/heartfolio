<?php 
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include 'assets/includes/navbar.php'; 
?>

<div class="container shop-container">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-section">
            <h3>Categories</h3>
            <ul>
                <li><a href="shop.php" class="active">All Magazines</a></li>
                <li><a href="shop.php?category=friendship">Friendship</a></li>
                <li><a href="shop.php?category=birthday">Birthday</a></li>
                <li><a href="shop.php?category=love">Love & Couple</a></li>
                <li><a href="shop.php?category=family">Family</a></li>
                <li><a href="shop.php?category=anniversary">Anniversary</a></li>
                <li><a href="shop.php?category=travel">Travel</a></li>
                <li><a href="shop.php?category=others">Others</a></li>
            </ul>
        </div>
        <div class="sidebar-section">
            <h3>Sort By</h3>
            <ul>
                <li><a href="shop.php?sort=price-low">Price: Low to High</a></li>
                <li><a href="shop.php?sort=price-high">Price: High to Low</a></li>
                <li><a href="shop.php?sort=name">Name</a></li>
                <li><a href="shop.php?sort=newest">Newest</a></li>
            </ul>
        </div>
    </aside>

    <!-- Products -->
    <main class="shop-products">
        <div class="section-title">
            <h2>All Magazines</h2>
            <p>Browse our collection of personalized magazines</p>
        </div>
        <div class="product-grid">
            <div class="product-card">
                <img src="assets\images\shopbsf.jpeg" alt="Best Friends Magazine">
                <div class="product-card-body">
                    <h3>Best Friends</h3>
                    <p class="price">Rs. 699</p>
                    <a href="product-details.php?id=1" class="btn btn-secondary">View Details</a>
                </div>
            </div>
            <div class="product-card">
                <img src="images\shopff.jpg" alt="Friendship Forever Magazine">
                <div class="product-card-body">
                    <h3>Friendship Forever</h3>
                    <p class="price">Rs. 699</p>
                    <a href="product-details.php?id=2" class="btn btn-secondary">View Details</a>
                </div>
            </div>
            <div class="product-card">
                <img src="assets\images\mtshop.jpeg" alt="Memories Together Magazine">
                <div class="product-card-body">
                    <h3>Memories Together</h3>
                    <p class="price">Rs. 699</p>
                    <a href="product-details.php?id=3" class="btn btn-secondary">View Details</a>
                </div>
            </div>
            <div class="product-card">
                <img src="images\btshop.jpeg" alt="Better Together Magazine">
                <div class="product-card-body">
                    <h3>Better Together</h3>
                    <p class="price">Rs. 699</p>
                    <a href="product-details.php?id=4" class="btn btn-secondary">View Details</a>
                </div>
            </div>
            <div class="product-card">
                <img src="images\sisshop.jpeg" alt="Soul Sisters Magazine">
                <div class="product-card-body">
                    <h3>Soul Sisters</h3>
                    <p class="price">Rs. 699</p>
                    <a href="product-details.php?id=5" class="btn btn-secondary">View Details</a>
                </div>
            </div>
            <div class="product-card">
                <img src="images\ourstory.jpeg" alt="Our Story Magazine">
                <div class="product-card-body">
                    <h3>Our Story</h3>
                    <p class="price">Rs. 699</p>
                    <a href="product-details.php?id=6" class="btn btn-secondary">View Details</a>
                </div>
            </div>
            
            
        </div>
    </main>
</div>

<?php include 'assets/includes/footer.php'; ?>
