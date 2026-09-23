<?php 
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include 'assets/includes/navbar.php'; 
?>

<div class="container products-section">
    <div class="section-title">
        <h2>Magazine Templates</h2>
        <p>Browse our available magazine templates</p>
    </div>
    <div class="product-grid">
        <div class="product-card">
            <img src="assets\images\beatfriends.jpeg" alt="Best Friends Template">
            <div class="product-card-body">
                <h3>Best Friends</h3>
                <p class="price">Rs. 699</p>
                <a href="product-details.php?id=1" class="btn btn-secondary">View Template</a>
            </div>
        </div>
        <div class="product-card">
            <img src="images\btshop.jpeg" alt="Friendship Forever Template">
            <div class="product-card-body">
                <h3>Friendship Forever</h3>
                <p class="price">Rs. 699</p>
                <a href="product-details.php?id=2" class="btn btn-secondary">View Template</a>
            </div>
        </div>
        <div class="product-card">
            <img src="images\ourstory.jpeg" alt="Our Story Template">
            <div class="product-card-body">
                <h3>Our Story</h3>
                <p class="price">Rs. 699</p>
                <a href="product-details.php?id=6" class="btn btn-secondary">View Template</a>
            </div>
        </div>
        <div class="product-card">
            <img src="assets\images\birthday.jpeg" alt="Birthday Special Template">
            <div class="product-card-body">
                <h3>Birthday Special</h3>
                <p class="price">Rs. 699</p>
                <a href="product-details.php?id=3" class="btn btn-secondary">View Template</a>
            </div>
        </div>
        <div class="product-card">
            <img src="assets\images\family.jpeg" alt="Family Memories Template">
            <div class="product-card-body">
                <h3>Family Memories</h3>
                <p class="price">Rs. 699</p>
                <a href="product-details.php?id=4" class="btn btn-secondary">View Template</a>
            </div>
        </div>
        <div class="product-card">
            <img src="assets\images\ann.jpeg" alt="Anniversary Love Template">
            <div class="product-card-body">
                <h3>Anniversary Love</h3>
                <p class="price">Rs. 699</p>
                <a href="product-details.php?id=9" class="btn btn-secondary">View Template</a>
            </div>
        </div>
        <div class="product-card">
            <img src="assets\images\travel.jpeg" alt="Travel Adventures Template">
            <div class="product-card-body">
                <h3>Travel Adventures</h3>
                <p class="price">Rs. 699</p>
                <a href="product-details.php?id=10" class="btn btn-secondary">View Template</a>
            </div>
        </div>
        <div class="product-card">
            <img src="images\sisshop.jpeg" alt="Special Moments Template">
            <div class="product-card-body">
                <h3>Special Moments</h3>
                <p class="price">Rs. 699</p>
                <a href="product-details.php?id=11" class="btn btn-secondary">View Template</a>
            </div>
        </div>
    </div>
</div>

<?php include 'assets/includes/footer.php'; ?>
